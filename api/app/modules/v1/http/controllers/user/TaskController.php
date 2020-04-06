<?php


namespace app\http\controllers\user;


use app\helper\ResponseError;
use app\models\Anchor;
use app\models\AnchorDailyTaskLog;
use app\models\DailyTaskLog;
use app\models\Kv;
use app\models\LevelConfig;
use app\models\OnceTaskLog;
use app\models\ShortPostsCommentReply;
use app\models\TaskConfig;
use app\models\User;
use app\models\UserAccount;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use app\models\UserSignin;
use app\models\UserSigninConfig;
use app\services\TaskUserService;
use Exception;
use app\http\controllers\ControllerBase;
use Phalcon\Cli\Task;

/**
 * TaskController
 * 任务中心 管理
 */
class TaskController extends ControllerBase
{

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/task/getReward
     * @api {post} /user/task/getReward 领取任务奖励
     * @apiName task-getReward
     * @apiGroup Task
     * @apiDescription 领取任务奖励
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} task_flg 任务标志
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} task_flg 任务标志
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.coin 本次实际领取金币
     * @apiSuccess {string} d.exp 本次实际领取经验
     * @apiSuccess {string} d.today_coin 今日已领金币  （如果大于最大值 则显示最大值）
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "coin": "18",
     *                   "exp": "0",
     *                   "today_coin": "50"
     *           },
     *           "t": "1553589233"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function getRewardAction($nUserId = 0)
    {
        $sTaskFlg = $this->getParams('task_flg');
        try {
            $oTaskConfig = TaskConfig::findFirst([
                'task_flg = :task_flg:',
                'bind' => [
                    'task_flg' => $sTaskFlg
                ]
            ]);
            if ( !$oTaskConfig || $oTaskConfig->task_on == 'N' ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::TASK_IS_CLOSE)),
                    ResponseError::TASK_IS_CLOSE
                );
            }
            $currentDate = date('Y-m-d');
            // 判断是否领取过
            if ( $oTaskConfig->task_type == TaskConfig::TASK_TYPE_ONCE ) {
                $logModel = OnceTaskLog::findFirst([
                    'once_task_id = :once_task_id: AND once_task_log_user_id = :once_task_log_user_id:',
                    'bind' => [
                        'once_task_id'          => $oTaskConfig->task_id,
                        'once_task_log_user_id' => $nUserId
                    ]
                ]);
            } else {
                $logModel = DailyTaskLog::findFirst([
                    'daily_task_date = :daily_task_date: AND daily_task_id = :daily_task_id: AND daily_task_log_user_id = :daily_task_log_user_id:',
                    'bind' => [
                        'daily_task_date'        => $currentDate,
                        'daily_task_id'          => $oTaskConfig->task_id,
                        'daily_task_log_user_id' => $nUserId
                    ]
                ]);
            }
            if ( $logModel ) {
                // 已经领取过了
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::TASK_HAS_GET)),
                    ResponseError::TASK_HAS_GET
                );
            }
            // 判断是否达成
            $taskFinished = $oTaskConfig->getTaskFinishDone($nUserId);
            if ( $taskFinished !== TRUE ) {
                // 没有完成
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::TASK_NOT_FINISH)),
                    ResponseError::TASK_NOT_FINISH
                );
            }
            $oUser = User::findFirst($nUserId);

            // 判断该任务是否 是设备唯一 才有的任务
            if ( $oUser->user_is_first_device == 'N' && $oTaskConfig->task_device_unique == 'Y' ) {
                // 用户不是第一个设备  该任务是只有第一个设备账号才能完成的
                throw new Exception(
                    '该任务无法完成',
                    ResponseError::TASK_NOT_FINISH
                );
            }
            // 判断是否有手机号码
            $oUserAccount = UserAccount::findFirst($nUserId);
            if($oUserAccount->user_phone == ''){
                throw new Exception(
                    '请先绑定手机号码才能领取奖励',
                    ResponseError::USER_NOT_BIND_PHONE
                );
            }


            // 添加记录 以及 添加金币 添加经验
            $this->db->begin();

            if ( $oTaskConfig->task_type == TaskConfig::TASK_TYPE_ONCE ) {
                $OnceTaskLog                        = new OnceTaskLog();
                $OnceTaskLog->once_task_id          = $oTaskConfig->task_id;
                $OnceTaskLog->once_task_log_user_id = $nUserId;
                $OnceTaskLog->once_task_reward_coin = $oTaskConfig->task_reward_coin;
                $OnceTaskLog->once_task_reward_exp  = $oTaskConfig->task_reward_exp;
                $OnceTaskLog->once_task_name        = $oTaskConfig->task_name;
                if ( $OnceTaskLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $OnceTaskLog->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
                $financeLogRemark    = $oTaskConfig->task_name;
                $financeFlowId       = $OnceTaskLog->once_task_log_id;
                $consume_category_id = UserConsumeCategory::DAILY_TASK;

                $nCoin = $oTaskConfig->task_reward_coin;
                $nExp  = $oTaskConfig->task_reward_exp;

            } else {
                $nCoin = $oTaskConfig->task_reward_coin;
                $nExp  = $oTaskConfig->task_reward_exp;

                if ( $oUser->user_member_expire_time > time() ) {
                    // VIP 每日任务 经验以及金币双倍
                    $nCoin = $nCoin * 2;
                    $nExp  = $nExp * 2;
                }

                $oDailyTaskLog                         = new DailyTaskLog();
                $oDailyTaskLog->daily_task_id          = $oTaskConfig->task_id;
                $oDailyTaskLog->daily_task_log_user_id = $nUserId;
                $oDailyTaskLog->daily_task_reward_coin = $nCoin;
                $oDailyTaskLog->daily_task_reward_exp  = $nExp;
                $oDailyTaskLog->daily_task_date        = $currentDate;
                $oDailyTaskLog->daily_task_name        = $oTaskConfig->task_name;
                if ( $oDailyTaskLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oDailyTaskLog->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
                $financeLogRemark    = "[每日] [" . $currentDate . "]" . $oTaskConfig->task_name;
                $financeFlowId       = $oDailyTaskLog->daily_task_log_id;
                $consume_category_id = UserConsumeCategory::ONCE_TASK;

            }
            //  给用户添加经验 以及金币
            $oTaskUserService = new TaskUserService();
            if ( $nCoin > 0 ) {
                $nCoin = $oTaskUserService->getExistsCoin($nUserId, $nCoin);
            }
            if ( $nCoin > 0 || $nExp > 0 ) {
                // 判断该用户今日任务获得金币是否达上限

                $oUser->user_free_coin       += $nCoin;
                $oUser->user_total_free_coin += $nCoin;

                $oUser->user_exp   += $nExp;
                $oUser->user_level = User::getUserLevel($oUser->user_exp);
                if ( $oUser->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }

                if ( $nCoin > 0 ) {
                    // 加用户流水
                    $oUserFinanceLog                         = new UserFinanceLog();
                    $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
                    $oUserFinanceLog->user_id                = $nUserId;
                    $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin;
                    $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
                    $oUserFinanceLog->consume_category_id    = $consume_category_id;
                    $oUserFinanceLog->consume                = $nCoin;
                    $oUserFinanceLog->remark                 = $financeLogRemark;
                    $oUserFinanceLog->flow_id                = $financeFlowId;
                    $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
                    $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
                    $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin;
                    $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin - $nCoin;
                    if ( $oUserFinanceLog->save() === FALSE ) {
                        $this->db->rollback();
                        throw new Exception(
                            sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                            ResponseError::OPERATE_FAILED
                        );
                    }
                }
            }
            $this->db->commit();
            $row = [
                'coin'       => (string)$nCoin,
                'exp'        => (string)$nExp,
                'today_coin' => (string)$oTaskUserService->getData($nUserId)
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/task/index
     * @api {get} /user/task/index 任务中心
     * @apiName task-index
     * @apiGroup Task
     * @apiDescription 任务中心
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.daily_task_list    每日任务列表
     * @apiSuccess {String} d.daily_task_list.task_name   任务名称
     * @apiSuccess {String} d.daily_task_list.task_flg   任务标识
     * @apiSuccess {number} d.daily_task_list.task_finish_times   任务需要完成度
     * @apiSuccess {number} d.daily_task_list.task_reward_coin   奖励金币数
     * @apiSuccess {number} d.daily_task_list.task_reward_exp  奖励经验数
     * @apiSuccess {String='Y(已领取)','N(未完成)','C(待领取)'} d.daily_task_list.reward_flg    是否完成
     * @apiSuccess {number} d.daily_task_list.task_done_num   任务完成度
     * @apiSuccess {number} d.daily_task_finished_count   每日任务完成数量
     * @apiSuccess {number} d.daily_task_total_count  每日任务总数量
     * @apiSuccess {object[]} d.once_task_list    一次新任务列表
     * @apiSuccess {String} d.once_task_list.task_name   任务名称
     * @apiSuccess {String} d.once_task_list.task_flg  任务标识
     * @apiSuccess {number} d.once_task_list.task_finish_times   任务需要完成度
     * @apiSuccess {number} d.once_task_list.task_reward_coin 奖励金币数
     * @apiSuccess {number} d.once_task_list.task_reward_exp 奖励经验数
     * @apiSuccess {String='Y(已领取)','N(未完成)','C(待领取)'} d.once_task_list.reward_flg   是否完成
     * @apiSuccess {number} d.once_task_list.task_done_num 任务完成度
     * @apiSuccess {number} d.once_task_finished_count  一次性任务完成数量
     * @apiSuccess {number} d.once_task_total_count  一次性任务总数
     * @apiSuccess {number} d.today_max_coin_limit 今日获得金币最高值
     * @apiSuccess {number} d.today_coin  今日已获得金币数
     * @apiSuccess {String='Y','N'} d.user_is_vip   用户是否为VIP   是VIP 则每日任务奖励 翻倍
     * @apiSuccess {String} d.daily_task_reward_multiple  每日任务奖励倍数
     * @apiSuccess {String} d.once_task_reward_multiple  一次性任务奖励倍数
     * @apiSuccessExample Success-Response:
     * {
     *     "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "daily_task_list": [
     *                 {
     *                     "task_name": "签到",
     *                     "task_flg": "sign",
     *                     "task_finish_times": "1",
     *                     "task_reward_coin": "1",
     *                     "task_reward_exp": "10",
     *                     "reward_flg": "C",
     *                     "task_done_num": "0"
     *                 },
     *                 {
     *                     "task_name": "社区点赞、评论",
     *                     "task_flg": "task_daily_posts_like_comment",
     *                     "task_finish_times": "5",
     *                     "task_reward_coin": "10",
     *                     "task_reward_exp": "10",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 },
     *                 {
     *                     "task_name": "动态打赏",
     *                     "task_flg": "task_daily_posts_send_gift",
     *                     "task_finish_times": "3",
     *                     "task_reward_coin": "11",
     *                     "task_reward_exp": "12",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 },
     *                 {
     *                     "task_name": "消费1金币",
     *                     "task_flg": "task_daily_pay_coin",
     *                     "task_finish_times": "888",
     *                     "task_reward_coin": "12",
     *                     "task_reward_exp": "12",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 },
     *                 {
     *                     "task_name": "视频通话单次10分钟",
     *                     "task_flg": "task_daily_video_chat_10_min",
     *                     "task_finish_times": "1",
     *                     "task_reward_coin": "13",
     *                     "task_reward_exp": "13",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 },
     *                 {
     *                     "task_name": "购买一次会员",
     *                     "task_flg": "task_daily_buy_vip",
     *                     "task_finish_times": "1",
     *                     "task_reward_coin": "14",
     *                     "task_reward_exp": "14",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 }
     *             ],
     *             "daily_task_finished_count": 0,
     *             "daily_task_total_count": 6,
     *             "once_task_list": [
     *                 {
     *                     "task_name": "视频通话单次超过5分钟",
     *                     "task_flg": "task_once_video_chat_5_min",
     *                     "task_finish_times": "1",
     *                     "task_reward_coin": "15",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 },
     *                 {
     *                     "task_name": "守护主播",
     *                     "task_flg": "task_once_guard_anchor",
     *                     "task_finish_times": "1",
     *                     "task_reward_coin": "16",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "Y",
     *                     "task_done_num": "1"
     *                 },
     *                 {
     *                     "task_name": "关注主播",
     *                     "task_flg": "task_once_follow_anchor",
     *                     "task_finish_times": "1",
     *                     "task_reward_coin": "17",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 },
     *                 {
     *                     "task_name": "首次充值",
     *                     "task_flg": "task_once_recharge",
     *                     "task_finish_times": "1",
     *                     "task_reward_coin": "18",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "C",
     *                     "task_done_num": "1"
     *                 },
     *                 {
     *                     "task_name": "累计送礼1000金币",
     *                     "task_flg": "task_once_send_gift_coin",
     *                     "task_finish_times": "1000",
     *                     "task_reward_coin": "19",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "N",
     *                     "task_done_num": "240"
     *                 },
     *                 {
     *                     "task_name": "累计视频通话100分钟",
     *                     "task_flg": "task_once_video_chat_min",
     *                     "task_finish_times": "100",
     *                     "task_reward_coin": "20",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "N",
     *                     "task_done_num": "15"
     *                 },
     *                 {
     *                     "task_name": "累计守护值达到10000",
     *                     "task_flg": "task_once_guard_coin",
     *                     "task_finish_times": "10000",
     *                     "task_reward_coin": "21",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "C",
     *                     "task_done_num": "10000"
     *                 },
     *                 {
     *                     "task_name": "邀请好友注册",
     *                     "task_flg": "task_once_invite_user",
     *                     "task_finish_times": "5",
     *                     "task_reward_coin": "22",
     *                     "task_reward_exp": "0",
     *                     "reward_flg": "N",
     *                     "task_done_num": "0"
     *                 }
     *             ],
     *             "once_task_finished_count": 3,
     *             "once_task_total_count": 8,
     *             "today_max_coin_limit": "300",
     *             "today_coin": "418",
     *             "user_is_vip": "N",
     *             "daily_task_reward_multiple": "1",
     *             "once_task_reward_multiple": "1"
     *         },
     *         "t": "1553589205"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction($nUserId = 0)
    {

        try {
            $oUser = User::findFirst($nUserId);

            $user_is_first_device = $oUser->user_is_first_device;

            // 签到信息
            $oUserSignin = UserSignin::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ],
            ]);

            if ( $oUserSignin === FALSE ) {
                $oUserSignin                        = new UserSignin();
                $oUserSignin->user_id               = $nUserId;
                $oUserSignin->user_signin_last_date = '0000-00-00';
            }

            // 判断今天是否签到
            $todaySignFinished = FALSE;
            if ( $oUserSignin->user_signin_last_date == date('Y-m-d') ) {
                $todaySignFinished = TRUE;
            }

            // 判断昨天是否签到
            if ( strtotime($oUserSignin->user_signin_last_date) < strtotime(date('Y-m-d', strtotime('-1 day'))) ) {
                $oUserSignin->user_signin_serial_total = 1;
            } else {
                if ( $todaySignFinished === FALSE ) {
                    // 今天没签到
                    $oUserSignin->user_signin_serial_total += 1;
                }
            }

            $nSerialSigninTotal = $oUserSignin->user_signin_serial_total % 7 ?: 7;

            $oUserSigninConfig = UserSigninConfig::findFirst([
                'user_signin_serial_total<=:user_signin_serial_total: order by user_signin_serial_total desc',
                'bind' => [
                    'user_signin_serial_total' => $nSerialSigninTotal
                ]
            ]);

            $nSerialSigninCoin = $oUserSigninConfig->user_signin_coin;
            $nSerialSigninExp  = $oUserSigninConfig->user_signin_exp;

            $firstDeviceWhere = '';
            if ( $user_is_first_device == 'N' ) {
                $firstDeviceWhere = ' AND tc.task_device_unique = "N"';
            }

            // 每日任务信息
            $dailyTaskBuilder = $this->modelsManager
                ->createBuilder()
                ->from([ 'tc' => TaskConfig::class ])
                ->leftJoin(DailyTaskLog::class, "l.daily_task_id = tc.task_id AND l.daily_task_date = '" . date('Y-m-d') . "' AND daily_task_log_user_id = " . $nUserId, 'l')
                ->columns('tc.task_name,tc.task_flg,tc.task_finish_times,tc.task_reward_coin,tc.task_reward_exp,l.daily_task_log_id,tc.task_type')
                ->where('tc.task_on = "Y" AND tc.task_type = :task_type: ' . $firstDeviceWhere, [
                    'task_type' => TaskConfig::TASK_TYPE_DAILY
                ])
                ->orderBy('tc.task_sort');

            $dailyTaskRow = $this->page($dailyTaskBuilder, 1, 100);

            $daily_task_finished_count = 0;

            $oTaskConfig = new TaskConfig();
            foreach ( $dailyTaskRow['items'] as &$dailyTaskItem ) {
                // 已经领过
                $dailyTaskItem['reward_flg']    = 'Y';
                $dailyTaskItem['task_done_num'] = $dailyTaskItem['task_finish_times'];
                $daily_task_finished_count      += 1;
                if ( !$dailyTaskItem['daily_task_log_id'] ) {
                    // 没有完成需要 判断完成进度
                    $oTaskConfig->task_finish_times = $dailyTaskItem['task_finish_times'];
                    $oTaskConfig->task_flg          = $dailyTaskItem['task_flg'];
                    $oTaskConfig->task_type         = $dailyTaskItem['task_type'];
                    $taskFinished                   = $oTaskConfig->getTaskFinishDone($nUserId);
                    if ( $taskFinished === TRUE ) {
                        // 已完成 待领取奖励
                        $dailyTaskItem['reward_flg'] = 'C';
                    } else {
                        // 未完成  进度获取值
                        $dailyTaskItem['reward_flg']    = 'N';
                        $dailyTaskItem['task_done_num'] = (string)intval($taskFinished);
                        $daily_task_finished_count      -= 1;
                    }
                }
                unset($dailyTaskItem['daily_task_log_id']);
            }


            // 一次性任务信息
            $onceTaskBuilder = $this->modelsManager
                ->createBuilder()
                ->from([ 'tc' => TaskConfig::class ])
                ->leftJoin(OnceTaskLog::class, 'l.once_task_id = tc.task_id AND once_task_log_user_id = ' . $nUserId, 'l')
                ->columns('tc.task_name,tc.task_flg,tc.task_finish_times,tc.task_reward_coin,tc.task_reward_exp,l.once_task_log_id')
                ->where('tc.task_on = "Y" AND tc.task_type = :task_type: ' . $firstDeviceWhere, [
                    'task_type' => TaskConfig::TASK_TYPE_ONCE
                ])
                ->orderBy('tc.task_sort');

            $onceTaskRow = $this->page($onceTaskBuilder, 1, 100);

            $once_task_finished_count = 0;

            foreach ( $onceTaskRow['items'] as &$onceTaskItem ) {
                // 已经领过
                $onceTaskItem['reward_flg']    = 'Y';
                $onceTaskItem['task_done_num'] = $onceTaskItem['task_finish_times'];
                $once_task_finished_count      += 1;
                if ( !$onceTaskItem['once_task_log_id'] ) {
                    // 没有完成需要 判断完成进度
                    $oTaskConfig->task_finish_times = $onceTaskItem['task_finish_times'];
                    $oTaskConfig->task_flg          = $onceTaskItem['task_flg'];
                    $taskFinished                   = $oTaskConfig->getTaskFinishDone($nUserId);
                    if ( $taskFinished === TRUE ) {
                        // 已完成 待领取奖励
                        $onceTaskItem['reward_flg'] = 'C';
                    } else {
                        // 未完成  进度获取值
                        $onceTaskItem['reward_flg']    = 'N';
                        $onceTaskItem['task_done_num'] = (string)intval($taskFinished);
                        $once_task_finished_count      -= 1;
                    }
                }
                unset($onceTaskItem['once_task_log_id']);
            }

            // 将签到任务 添加进每日任务
            array_unshift($dailyTaskRow['items'], [
                'task_name'         => '签到',
                'task_flg'          => 'sign',
                'task_finish_times' => '1',
                'task_reward_coin'  => $nSerialSigninCoin,
                'task_reward_exp'   => $nSerialSigninExp,
                'reward_flg'        => $todaySignFinished == 'Y' ? 'Y' : 'N',
                'task_done_num'     => $todaySignFinished == 'Y' ? '1' : '0',
            ]);


            $todaySignFinishedCount = $todaySignFinished == 'Y' ? '1' : '0';


            $userIsVip = $oUser->user_member_expire_time > time() ? 'Y' : 'N';

            $maxCoin   = intval(Kv::get(Kv::TASK_DAILY_COIN_MAX));
            $todayCoin = intval((new TaskUserService())->getData($nUserId));


            $row = [
                'daily_task_list'            => $dailyTaskRow['items'],
                'daily_task_finished_count'  => intval($daily_task_finished_count + $todaySignFinishedCount),
                'daily_task_total_count'     => count($dailyTaskRow['items']),
                'once_task_list'             => $onceTaskRow['items'],
                'once_task_finished_count'   => $once_task_finished_count,
                'once_task_total_count'      => count($onceTaskRow['items']),
                'today_max_coin_limit'       => (string)$maxCoin,
                'today_coin'                 => (string)min($maxCoin, $todayCoin),
                'user_is_vip'                => $userIsVip,
                'daily_task_reward_multiple' => $userIsVip == 'Y' ? '2' : '1',
                'once_task_reward_multiple'  => '1',
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/task/anchor
     * @api {get} /user/task/anchor 主播任务中心
     * @apiName task-anchor
     * @apiGroup Task
     * @apiDescription 主播任务中心
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.daily_task_list    每日任务列表
     * @apiSuccess {String} d.daily_task_list.task_name   任务名称
     * @apiSuccess {String} d.daily_task_list.task_flg   任务标识
     * @apiSuccess {number} d.daily_task_list.task_finish_times   任务需要完成度
     * @apiSuccess {number} d.daily_task_list.task_reward_dot   奖励佣金数
     * @apiSuccess {number} d.daily_task_list.task_reward_exp  奖励经验数
     * @apiSuccess {String='Y(已领取)','N(未完成)','C(待领取)'} d.daily_task_list.reward_flg    是否完成
     * @apiSuccess {number} d.daily_task_list.task_done_num   任务完成度
     * @apiSuccess {number} d.daily_task_finished_count   每日任务完成数量
     * @apiSuccess {number} d.daily_task_total_count  每日任务总数量
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *  {
     *      "c": 0,
     *      "m": "请求成功",
     *      "d": {
     *              "daily_task_list": [{
     *                  "task_name": "发布动态",
     *                  "task_flg": "task_anchor_daily_add_posts",
     *                  "task_finish_times": "5",
     *                  "task_reward_dot": "0",
     *                  "task_reward_exp": "100",
     *                  "task_type": "anchor_daily",
     *                  "reward_flg": "N",
     *                  "task_done_num": "0"
     *          }],
     *          "daily_task_finished_count": 0,
     *          "daily_task_total_count": 6
     *      },
     *      "t": "1556253102"
     *  }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function anchorAction($nUserId = 0)
    {
        try {
            $oUser = User::findFirst($nUserId);

            if ( $oUser->user_is_anchor == 'N' ) {
                // 用户信息
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::FAIL)),
                    ResponseError::FAIL
                );
            }

            // 每日任务信息
            $dailyTaskBuilder = $this->modelsManager
                ->createBuilder()
                ->from([ 'tc' => TaskConfig::class ])
                ->leftJoin(AnchorDailyTaskLog::class, "l.anchor_daily_task_id = tc.task_id AND l.anchor_daily_task_date = '" . date('Y-m-d') . "' AND anchor_daily_task_log_user_id = " . $nUserId, 'l')
                ->columns('tc.task_name,tc.task_flg,tc.task_finish_times,tc.task_reward_dot,tc.task_reward_exp,l.anchor_daily_task_log_id,tc.task_type')
                ->where('tc.task_on = "Y" AND tc.task_type = :task_type: ', [
                    'task_type' => TaskConfig::TASK_TYPE_ANCHOR_DAILY
                ])
                ->orderBy('tc.task_sort');

            $dailyTaskRow = $this->page($dailyTaskBuilder, 1, 100);

            $daily_task_finished_count = 0;

            $oTaskConfig = new TaskConfig();
            foreach ( $dailyTaskRow['items'] as &$dailyTaskItem ) {
                // 已经领过
                $dailyTaskItem['reward_flg']    = 'Y';
                $dailyTaskItem['task_done_num'] = $dailyTaskItem['task_finish_times'];
                $daily_task_finished_count      += 1;
                if ( !$dailyTaskItem['anchor_daily_task_log_id'] ) {
                    // 没有完成需要 判断完成进度
                    $oTaskConfig->task_finish_times = $dailyTaskItem['task_finish_times'];
                    $oTaskConfig->task_flg          = $dailyTaskItem['task_flg'];
                    $oTaskConfig->task_type         = $dailyTaskItem['task_type'];
                    $taskFinished                   = $oTaskConfig->getTaskFinishDone($nUserId);
                    if ( $taskFinished === TRUE ) {
                        // 已完成 待领取奖励
                        $dailyTaskItem['reward_flg'] = 'C';
                    } else {
                        // 未完成  进度获取值
                        $dailyTaskItem['reward_flg']    = 'N';
                        $dailyTaskItem['task_done_num'] = (string)intval($taskFinished);
                        $daily_task_finished_count      -= 1;
                    }
                }
                unset($dailyTaskItem['anchor_daily_task_log_id']);
            }

            $row = [
                'daily_task_list'           => $dailyTaskRow['items'],
                'daily_task_finished_count' => intval($daily_task_finished_count),
                'daily_task_total_count'    => count($dailyTaskRow['items']),
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/task/getAnchorReward
     * @api {post} /user/task/getAnchorReward 领取主播任务奖励
     * @apiName task-getAnchorReward
     * @apiGroup Task
     * @apiDescription 领取主播任务奖励
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} task_flg 任务标志
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} task_flg 任务标志
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.dot 本次领取佣金
     * @apiSuccess {string} d.exp 本次领取经验
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "dot": "18",
     *                   "exp": "0",
     *           },
     *           "t": "1553589233"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function getAnchorRewardAction($nUserId = 0)
    {
        $sTaskFlg = $this->getParams('task_flg');
        try {
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $nUserId ]
            ]);

            if ( !$oAnchor ) {
                // 用户信息
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::FAIL)),
                    ResponseError::FAIL
                );
            }

            $oTaskConfig = TaskConfig::findFirst([
                'task_flg = :task_flg:',
                'bind' => [
                    'task_flg' => $sTaskFlg
                ]
            ]);
            if ( !$oTaskConfig || $oTaskConfig->task_on == 'N' ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::TASK_IS_CLOSE)),
                    ResponseError::TASK_IS_CLOSE
                );
            }
            $currentDate = date('Y-m-d');

            $logModel = AnchorDailyTaskLog::findFirst([
                'anchor_daily_task_date = :daily_task_date: AND anchor_daily_task_id = :daily_task_id: AND anchor_daily_task_log_user_id = :daily_task_log_user_id:',
                'bind' => [
                    'daily_task_date'        => $currentDate,
                    'daily_task_id'          => $oTaskConfig->task_id,
                    'daily_task_log_user_id' => $nUserId
                ]
            ]);
            if ( $logModel ) {
                // 已经领取过了
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::TASK_HAS_GET)),
                    ResponseError::TASK_HAS_GET
                );
            }
            // 判断是否达成
            $taskFinished = $oTaskConfig->getTaskFinishDone($nUserId);
            if ( $taskFinished !== TRUE ) {
                // 没有完成
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::TASK_NOT_FINISH)),
                    ResponseError::TASK_NOT_FINISH
                );
            }
            $nDot      = $oTaskConfig->task_reward_dot;
            $anchorExp = $oTaskConfig->task_reward_exp;

            $this->db->begin();

            $oAnchorDailyTaskLog                                = new AnchorDailyTaskLog();
            $oAnchorDailyTaskLog->anchor_daily_task_id          = $oTaskConfig->task_id;
            $oAnchorDailyTaskLog->anchor_daily_task_log_user_id = $nUserId;
            $oAnchorDailyTaskLog->anchor_daily_task_reward_dot  = $nDot;
            $oAnchorDailyTaskLog->anchor_daily_task_reward_exp  = $anchorExp;
            $oAnchorDailyTaskLog->anchor_daily_task_date        = $currentDate;
            $oAnchorDailyTaskLog->anchor_daily_task_name        = $oTaskConfig->task_name;
            if ( $oAnchorDailyTaskLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorDailyTaskLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $financeLogRemark    = "[每日] [" . $currentDate . "]" . $oTaskConfig->task_name;
            $financeFlowId       = $oAnchorDailyTaskLog->anchor_daily_task_log_id;
            $consume_category_id = UserConsumeCategory::ANCHOR_DAILY_TASK_TASK;
            if ( $nDot > 0 || $anchorExp > 0 ) {
                if ( $nDot > 0 ) {
                    // 给主播充钱
                    $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
                    $this->db->execute($sql, [
                        'total_dot'    => $nDot,
                        'get_dot'      => 0,
                        'get_free_dot' => $nDot,
                        'user_id'      => $nUserId,
                    ]);
                    if ( $this->db->affectedRows() <= 0 ) {
                        $this->db->rollback();
                        throw new Exception(
                            sprintf('%s', ResponseError::getError(ResponseError::OPERATE_FAILED)),
                            ResponseError::OPERATE_FAILED
                        );
                    }

                    $oUser = User::findFirst($nUserId);

                    // 记录主播流水
                    $oUserFinanceLog                      = new UserFinanceLog();
                    $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
                    $oUserFinanceLog->user_id             = $nUserId;
                    $oUserFinanceLog->user_current_amount = $oUser->user_dot + $nDot;
                    $oUserFinanceLog->user_last_amount    = $oUser->user_dot;
                    $oUserFinanceLog->consume_category_id = $consume_category_id;
                    $oUserFinanceLog->consume             = +$nDot;
                    $oUserFinanceLog->remark              = $financeLogRemark;
                    $oUserFinanceLog->flow_id             = $financeFlowId;
                    $oUserFinanceLog->type                = 0;
                    $oUserFinanceLog->group_id            = $oUser->user_group_id;
                    if ( $oUserFinanceLog->save() === FALSE ) {
                        $this->db->rollback();
                        throw new Exception(
                            sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                            ResponseError::OPERATE_FAILED
                        );
                    }
                }

                if ( $anchorExp ) {
                    $anchorLevel = LevelConfig::getLevelInfo($oAnchor->anchor_exp + $anchorExp,LevelConfig::LEVEL_TYPE_ANCHOR);
                    // 给主播加经验(魅力值)
                    $anchorSql = 'update anchor set anchor_exp = anchor_exp + :anchor_exp,anchor_level = :anchor_level WHERE user_id = :user_id';
                    $this->db->execute($anchorSql, [
                        'anchor_exp'   => $anchorExp,
                        'anchor_level' => $anchorLevel['level'],
                        'user_id'      => $nUserId,
                    ]);
                    if ( $this->db->affectedRows() <= 0 ) {
                        $this->db->rollback();
                        throw new Exception(
                            sprintf('%s', ResponseError::getError(ResponseError::OPERATE_FAILED)),
                            ResponseError::OPERATE_FAILED
                        );
                    }
                }
            }
            $this->db->commit();
            $row = [
                'dot'   => (string)$nDot,
                'exp'   => (string)$anchorExp
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


}