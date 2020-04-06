<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 签到控制器                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;


use app\models\Kv;
use app\models\LevelConfig;
use app\models\UserAccount;
use app\services\TaskUserService;
use Exception;

use app\models\User;
use app\models\UserSignin;
use app\models\UserSigninLog;
use app\helper\ResponseError;
use app\models\UserFinanceLog;
use app\models\UserSigninConfig;
use app\models\UserLevelUpgrade;
use app\models\UserConsumeCategory;
use app\http\controllers\ControllerBase;

/**
 * SigninController 签到控制器
 */
class SigninController extends ControllerBase
{
    use \app\services\UserService;
    use \app\services\SystemMessageService;


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/signin/serialDetail
     * @api {get} /user/signin/serialDetail 连续签到详情
     * @apiName signin-serialDetail
     * @apiGroup Profile
     * @apiDescription 连续签到详情
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
     * @apiSuccess {number} d.user_signin_serial_total   用户已连续签到天数
     * @apiSuccess {String} d.today_is_signin   今天是否签到
     * @apiSuccess {number} d.today_coin   今天签到获得金币
     * @apiSuccess {number} d.today_exp   今天签到获得经验
     * @apiSuccess {object[]} d.signin_config   签到配置
     * @apiSuccess {number} d.signin_config.user_signin_serial_total  连续签到天数
     * @apiSuccess {number} d.signin_config.user_signin_coin  签到可得金币
     * @apiSuccess {number} d.signin_config.user_signin_exp  签到可得经验
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "user_signin_serial_total": 1,
     *                 "today_is_signin": "N",
     *                 "today_coin": 7,
     *                 "today_exp": 70,
     *                 "signin_config": [{
     *                         "user_signin_serial_total": 1,
     *                         "user_signin_coin": 1,
     *                         "user_signin_exp": 10
     *                 }, {
     *                         "user_signin_serial_total": 2,
     *                         "user_signin_coin": 2,
     *                         "user_signin_exp": 20
     *                 }, {
     *                         "user_signin_serial_total": 3,
     *                         "user_signin_coin": 3,
     *                         "user_signin_exp": 30
     *                 }, {
     *                         "user_signin_serial_total": 4,
     *                         "user_signin_coin": 4,
     *                         "user_signin_exp": 40
     *                 }, {
     *                         "user_signin_serial_total": 5,
     *                         "user_signin_coin": 5,
     *                         "user_signin_exp": 50
     *                 }, {
     *                         "user_signin_serial_total": 6,
     *                         "user_signin_coin": 6,
     *                         "user_signin_exp": 60
     *                 }, {
     *                         "user_signin_serial_total": 7,
     *                         "user_signin_coin": 7,
     *                         "user_signin_exp": 70
     *                 }]
     *         },
     *         "t": "1546066165"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function serialDetailAction($nUserId = 0)
    {
        try {
            $oUserSignin              = UserSignin::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ],
            ]);
            $user_signin_serial_total = 0;
            $today_is_signin          = 'N';
            if ( $oUserSignin === FALSE ) {
                $oUserSignin                           = new UserSignin();
                $oUserSignin->user_id                  = $nUserId;
                $oUserSignin->user_signin_last_date    = '0000-00-00';
                $oUserSignin->user_signin_total        = 0;
                $oUserSignin->user_signin_serial_total = 0;
                $oUserSignin->user_signin_exp_total    = 0;
                $oUserSignin->user_signin_coin_total   = 0;
                $oUserSignin->save();
            } else {
                if ( $oUserSignin->user_signin_last_date >= date('Y-m-d', strtotime('-1 day')) ) {
                    $user_signin_serial_total = $oUserSignin->user_signin_serial_total % 7;
                    if ( $oUserSignin->user_signin_last_date == date('Y-m-d') ) {
                        $today_is_signin = 'Y';
                        $user_signin_serial_total = $oUserSignin->user_signin_serial_total % 7 ? : 7;
                    }
                }
            }

            $oUserSigninConfig = UserSigninConfig::find([
                'order' => 'user_signin_serial_total'
            ]);
            $oUser             = User::findFirst($nUserId);
            $multiple          = $oUser->user_member_expire_time > time() ? 2 : 1;
            $today_coin        = 0;
            $today_exp         = 0;
            $configArr         = [];
            foreach ( $oUserSigninConfig as $configItem ) {
                $itemCoin = $configItem->user_signin_coin * $multiple + $configItem->user_signin_extra_coin;
                $itemExp  = $configItem->user_signin_exp * $multiple + $configItem->user_signin_extra_exp;
                if ( $today_is_signin == 'Y' ) {
                    if ( $user_signin_serial_total == $configItem->user_signin_serial_total ) {
                        $today_coin = $itemCoin;
                        $today_exp  = $itemExp;
                    }
                } else {
                    $checkSerialFlg = $user_signin_serial_total == 7 ? 0 : $user_signin_serial_total;
                    if ( $checkSerialFlg == $configItem->user_signin_serial_total - 1 ) {
                        $today_coin = $itemCoin;
                        $today_exp  = $itemExp;
                    }
                }
                $configArr[] = [
                    'user_signin_serial_total' => intval($configItem->user_signin_serial_total),
                    'user_signin_coin'         => $itemCoin,
                    'user_signin_exp'          => $itemExp,
                ];
            }
            $row = [
                'user_signin_serial_total' => intval($user_signin_serial_total),
                'today_is_signin'          => $today_is_signin,
                'signin_config'            => $configArr,
                'today_coin'               => $today_coin,
                'today_exp'                => $today_exp,
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row,false);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/signin/add
     * @api {post} /user/signin/add 签到
     * @apiName signin-add
     * @apiGroup Profile
     * @apiDescription 签到
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
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function addAction($nUserId = 0)
    {
        try {

            $oUser = User::findFirst($nUserId);

            if ( $oUser->user_is_anchor == 'Y' ) {
                throw new Exception('主播签到功能暂时维护中，请期待后续版本', ResponseError::SIGNED);
            }

            // 判断是否有手机号码
//            $oUserAccount = UserAccount::findFirst($nUserId);
//            if($oUserAccount->user_phone == ''){
//                throw new Exception(
//                    '请先绑定手机号码才能领取奖励',
//                    ResponseError::USER_NOT_BIND_PHONE
//                );
//            }

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
            if ( $oUserSignin->user_signin_last_date == date('Y-m-d') ) {
                throw new Exception(ResponseError::getError(ResponseError::SIGNED), ResponseError::SIGNED);
            }

            // 判断昨天是否签到
            if ( strtotime($oUserSignin->user_signin_last_date) < strtotime(date('Y-m-d', strtotime('-1 day'))) ) {
                $oUserSignin->user_signin_serial_total = 1;
            } else {
                $oUserSignin->user_signin_serial_total += 1;
            }

            $nSerialSigninTotal = $oUserSignin->user_signin_serial_total % 7 ?: 7;

            $oUserSigninConfig = UserSigninConfig::findFirst([
                'user_signin_serial_total<=:user_signin_serial_total: order by user_signin_serial_total desc',
                'bind' => [
                    'user_signin_serial_total' => $nSerialSigninTotal
                ]
            ]);
            $multiple          = $oUser->user_member_expire_time > time() ? 2 : 1;

            $nSerialSigninCoin = ($oUserSigninConfig->user_signin_coin * $multiple + $oUserSigninConfig->user_signin_extra_coin);
            $nSerialSigninExp  = ($oUserSigninConfig->user_signin_exp * $multiple + $oUserSigninConfig->user_signin_extra_exp);

            $oUserSignin->user_signin_total      += 1;
            $oUserSignin->user_signin_last_date  = date('Y-m-d');
            $oUserSignin->user_signin_coin_total += $nSerialSigninCoin;
            $oUserSignin->user_signin_exp_total  += $nSerialSigninExp;


            // 判断该用户今日任务获得金币是否达上限
            $oTaskUserService = new TaskUserService();
            $nSerialSigninCoin = $oTaskUserService->getExistsCoin($nUserId,$nSerialSigninCoin);


            $this->db->begin();

            $oUserSigninLog                   = new UserSigninLog();
            $oUserSigninLog->user_id          = $nUserId;
            $oUserSigninLog->user_signin_date = date('Y-m-d');
            $oUserSigninLog->user_signin_coin = $nSerialSigninCoin;
            $oUserSigninLog->user_signin_exp  = $nSerialSigninExp;

            if ( $oUserSigninLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserSigninLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            if ( $oUserSignin->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserSignin->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            // 加用户金币 经验
            $oUser = User::findFirst($nUserId);

            if ( $nSerialSigninCoin > 0 || $nSerialSigninExp > 0 ) {
                $oUser->user_free_coin       += $nSerialSigninCoin;
                $oUser->user_total_free_coin += $nSerialSigninCoin;

                $oUser->user_exp   += $nSerialSigninExp;
                $oUser->user_level = User::getUserLevel($oUser->user_exp);
                if ( $oUser->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }

                // 加用户流水
                $oUserFinanceLog                         = new UserFinanceLog();
                $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
                $oUserFinanceLog->user_id                = $nUserId;
                $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin;
                $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin - $nSerialSigninCoin;
                $oUserFinanceLog->consume_category_id    = UserConsumeCategory::SIGNIN_COIN;
                $oUserFinanceLog->consume                = $nSerialSigninCoin;
                $oUserFinanceLog->remark                 = '签到获取金币';
                $oUserFinanceLog->flow_id                = $oUserSigninLog->user_signin_log_id;
                $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
                $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
                $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin;
                $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin - $nSerialSigninCoin;
                if ( $oUserFinanceLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
            } else {
                if ( $oUser->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUser->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
            }

            $this->db->commit();

        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }


    /**
     * detailAction 签到详情
     *
     * @param int $nUserId
     */
    public function detailAction($nUserId = 0)
    {
        $sFormat = $this->getParams('format', 'string', 'html');

        $oUserSignin = UserSignin::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $nUserId,
            ],
        ]);

        if ( $oUserSignin === FALSE ) {
            $oUserSignin                           = new UserSignin();
            $oUserSignin->user_id                  = $nUserId;
            $oUserSignin->user_signin_last_date    = '0000-00-00';
            $oUserSignin->user_signin_total        = 0;
            $oUserSignin->user_signin_serial_total = 0;
            $oUserSignin->user_signin_exp_total    = 0;
            $oUserSignin->user_signin_coin_total   = 0;
            $oUserSignin->save();
        }

        // 判断是否连续签到
        if ( strtotime('today') > (strtotime($oUserSignin->user_signin_last_date) + 60 * 60 * 24) ) {
            $oUserSigninConfig                     = UserSigninConfig::findFirst([
                'user_signin_serial_total<=:user_signin_serial_total: order by user_signin_serial_total desc',
                'bind' => [
                    'user_signin_serial_total' => 1
                ]
            ]);
            $oUserSignin->user_signin_serial_total = '0';
        } else {
            $nSerialTotal = $oUserSignin->user_signin_serial_total % 7 ?: 7;

            $oUserSigninConfig = UserSigninConfig::findFirst([
                'user_signin_serial_total<=:user_signin_serial_total: order by user_signin_serial_total desc',
                'bind' => [
                    'user_signin_serial_total' => $oUserSignin->user_signin_last_date == date('Y-m-d') ? $nSerialTotal : $nSerialTotal + 1
                ]
            ]);
        }

        // 从星期一开始
        for ( $i = 1; $i <= 7; $i++ ) {
            $sTime                      = strtotime(date('Y-m-d', strtotime('last day this week +' . $i . ' day')));
            $row['signin_date'][$sTime] = [
                'date'      => date('m-d', $sTime),
                'is_signin' => 'N',
            ];
        }

        $aUserSigninLog = UserSigninLog::find([
            'user_id=:user_id: and user_signin_date >= :user_signin_date: order by user_signin_date desc',
            'columns' => 'user_signin_date',
            'bind'    => [
                'user_id'          => $nUserId,
                'user_signin_date' => date('Y-m-d', strtotime('last day this week +1 day')),
            ]
        ]);

        $aSigninDate = [];

        foreach ( $aUserSigninLog as $v ) {
            $sDate = date('m-d', strtotime($v->user_signin_date));

            if ( isset($row['signin_date'][strtotime($v->user_signin_date)]) ) {
                $row['signin_date'][strtotime($v->user_signin_date)] = [
                    'date'      => $sDate,
                    'is_signin' => 'Y',
                ];
            }

        }

        $row['signin_date'] = array_values($row['signin_date']);
        $oUser              = User::findFirst($nUserId);
        $multiple           = $oUser->user_member_expire_time > time() ? 2 : 1;

        $row['user'] = [
            'signin_total'            => $oUserSignin->user_signin_total,
            'signin_serial_total'     => $oUserSignin->user_signin_serial_total,
            'signin_exp_total'        => $oUserSignin->user_signin_exp_total,
            'signin_coin_total'       => $oUserSignin->user_signin_coin_total,
            'today_is_signin'         => $oUserSignin->user_signin_last_date == date('Y-m-d') ? 'Y' : 'N',
            'today_signin_exp'        => $oUserSigninConfig->user_signin_exp * $multiple,
            'today_signin_coin'       => $oUserSigninConfig->user_signin_coin * $multiple,
            'today_signin_extra_coin' => $oUserSigninConfig->user_signin_extra_coin,
            'today_signin_extra_exp'  => $oUserSigninConfig->user_signin_extra_exp,
        ];


        if ( $sFormat == 'html' ) {
            header('location: ' . APP_WEB_URL . '/signIn?' . http_build_query($this->getParams()));
        } else {
            $this->success($row, FALSE);
        }
    }

    /**
     * judgeAction 判断签到
     *
     * @param int $nUserId
     */
    public function judgeAction($nUserId = 0)
    {
//        $oUser = User::findFirst($nUserId);
//        $is_signin = 'Y';
//        if($oUser->user_is_anchor == 'N'){
//            $oUserSignin = UserSignin::findFirst([
//                'user_id=:user_id:',
//                'bind' => [
//                    'user_id' => $nUserId,
//                ],
//            ]);
//            $is_signin = isset($oUserSignin->user_signin_last_date) && $oUserSignin->user_signin_last_date == date('Y-m-d') ? 'Y' : 'N';
//        }
//
//        $row = [
//            'user_signin' => [
//                'is_signin' => $is_signin,
//                'tips'      => '今天还没有签到哦！',
//            ],
//        ];
        $row = $this->getUserSignStatus(null, $nUserId);

        $this->success($row);
    }
}