<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户邀请控制器                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use app\helper\ResponseError;
use app\models\Kv;
use app\models\User;
use app\http\controllers\ControllerBase;
use app\models\UserShareRewardLog;
use app\services\UserFirstShareService;
use app\services\UserTodayCashService;
use Phalcon\Exception;

/**
 * InviteController 用户邀请控制器
 */
class InviteController extends ControllerBase
{
    use \app\services\UserService;

    /**
     * indexAction 邀请用户列表
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/invite/index
     * @api {get} /user/invite/index 邀请用户列表
     * @apiName invite-index
     * @apiGroup Profile
     * @apiDescription 邀请用户列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数
     * @apiParam (正常请求){Number='1(男)','2（女）'} sex 性别
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page 页数
     * @apiParam (debug){Number} pagesize 每页数
     * @apiParam (debug){Number='1(男)','2（女）'，''} sex 性别
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.invite_user
     * @apiSuccess {object[]} d.invite_user.items
     * @apiSuccess {number} d.invite_user.items.user_id
     * @apiSuccess {String} d.invite_user.items.user_nickname
     * @apiSuccess {String} d.invite_user.items.user_avatar
     * @apiSuccess {number} d.invite_user.items.user_invite_total
     * @apiSuccess {number} d.invite_user.items.user_member_expire_time
     * @apiSuccess {number} d.invite_user.items.user_create_time
     * @apiSuccess {String} d.invite_user.items.user_is_member
     * @apiSuccess {number} d.invite_user.page
     * @apiSuccess {number} d.invite_user.pagesize
     * @apiSuccess {number} d.invite_user.pagetotal
     * @apiSuccess {number} d.invite_user.total
     * @apiSuccess {number} d.invite_user.prev
     * @apiSuccess {number} d.invite_user.next
     * @apiSuccess {number} d.invite_count
     * @apiSuccess {number} d.invite_effective_count
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "invite_user": {
     *                     "items": [{
     *                         "user_id": "256",
     *                         "user_nickname": "假面。很适合",
     *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/D39D5B8BDE61A9532ACAABF5E1C30F64\/100",
     *                         "user_invite_total": "0",
     *                         "user_member_expire_time": "0",
     *                         "user_create_time": "1537951067",
     *                         "user_is_member": "N"
     *                 }],
     *                 "page": 1,
     *                 "pagesize": 20,
     *                 "pagetotal": 1,
     *                 "total": 1,
     *                 "prev": 1,
     *                 "next": 1
     *             },
     *             "invite_count": "0",
     *             "invite_effective_count": "0"
     *         },
     *         "t": "1539938326"
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

        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $nSex      = $this->getParams('sex', 'int', 1);

        try {
            $oUser   = User::findFirst($nUserId);
            $builder = $this->modelsManager
                ->createBuilder()
                ->from(User::class)
                ->columns('user_id,user_nickname,user_avatar,user_level,user_invite_total,user_member_expire_time,user_create_time')
                ->where('user_invite_user_id=:user_id:', [
                    'user_id' => $nUserId,
                ]);
            if ( $nSex ) {
                $builder->andWhere('user_sex = :user_sex:', [ 'user_sex' => $nSex ]);
            }
            $row['invite_user'] = $this->page($builder, $nPage, $nPagesize);
            foreach ( $row['invite_user']['items'] as &$v ) {
                $v['user_is_member'] = $v['user_member_expire_time'] == 0 ? 'N' : (time() > $v['user_member_expire_time'] ? 'O' : 'Y');
            }
            $row['invite_count']           = $oUser->user_invite_total;
            $row['invite_effective_count'] = $oUser->user_invite_effective_total;

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }


    public function detailAction($nUserId = 0)
    {

        try {
            $oUser = User::findFirst($nUserId);

            // 用户
            $row['user'] = [
                'user_invite_code'            => $oUser->user_invite_code,
                'user_invite_coin_total'      => sprintf('%.2f', $oUser->user_invite_coin_total),
                'total_user_cash'             => sprintf('%.2f', $oUser->total_user_cash),
                'user_invite_total'           => $oUser->user_invite_total,
                'user_invite_effective_total' => $oUser->user_invite_effective_total,
                'user_invite_total_female'    => $oUser->user_invite_total_female,
                'user_invite_total_male'      => $oUser->user_invite_total - $oUser->user_invite_total_female,
            ];

            // 分享
            $row['share'] = [
                'logo'    => APP_IMG_URL . 'invite_friends_banner.png',
                'url'     => sprintf("%s?channelCode=gGgdfpvuzWOBWncQ&invite_code=%s", APP_DOWNLOAD_URL, $oUser->user_invite_code),
                'content' => '我在泡泡直播',
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * detailAction 我的邀请详情
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/invite/share
     * @api {get} /user/invite/share 我的邀请详情
     * @apiName invite-detail
     * @apiGroup Profile
     * @apiDescription 我的邀请详情
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.user
     * @apiSuccess {String} d.user.user_invite_code   邀请码
     * @apiSuccess {number} d.user.user_invite_effective_total  邀请有效人数
     * @apiSuccess {number} d.user.user_invite_total_female  邀请总女性
     * @apiSuccess {number} d.user.user_invite_total_male  邀请总男性
     * @apiSuccess {number} d.user.user_cash  当前剩余“现金”
     * @apiSuccess {number} d.user.today_income_cash  今日所得“现金”
     * @apiSuccess {number} d.user.user_invite_dot_total  总邀请获得佣金
     * @apiSuccess {number} d.user.user_invite_total  总邀请人数（不去重）
     * @apiSuccess {object} d.share   分享
     * @apiSuccess {String} d.share.logo  logo
     * @apiSuccess {String} d.share.url  地址
     * @apiSuccess {String} d.share.content   内容
     * @apiSuccess {object[]} d.rule
     * @apiSuccess {String} d.rule.title
     * @apiSuccess {String} d.rule.content
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "user": {
     *                     "user_invite_code": "P52215",
     *                     "user_invite_effective_total": "0",
     *                     "user_invite_total_female": "0",
     *                     "user_invite_total_male": "0",
     *                     "user_cash": "0"
     *                     "today_income_cash": "0"
     *                     "user_invite_dot_total": "0"
     *                     "user_invite_total": "0"
     *             },
     *             "share": {
     *                     "logo": "http://dev.api.sxypaopao.com/assets/images/invite_friends_banner.png",
     *                     "url": "http://dev.h5.sxypaopao.com/register?channelCode=gGgdfpvuzWOBWncQ&invite_code=P52215",
     *                     "content": "我在泡泡直播"
     *             },
     *             "rule": [
     *                 {
     *                     "title": "奖励一：邀请用户有奖",
     *                     "content": "每邀请一个真实注册的男性用户，奖励0.5元现金，上不封顶"
     *                 },
     *                 {
     *                     "title": "奖励二：邀请用户充值VIP有奖",
     *                     "content": "您邀请的人，每充值一次VIP会员，您将获得对方当前充值金额30%的等额现金"
     *                 },
     *                 {
     *                     "title": "奖励三：邀请用户充值有奖",
     *                     "content": "您邀请的人，每充值一次金币，您将获得对方当前充值金额20%的等额现金"
     *                 },
     *                 {
     *                     "title": "奖励四：邀请用户提现有奖",
     *                     "content": "您邀请的是真实注册的女性用户，并申请成为主播，那么，她每次提现，您将获得对方当前提现金额5%的等额现金"
     *                 }
     *             ]
     *         },
     *         "t": "1539915870"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     *
     */
    public function shareAction($nUserId = 0)
    {

        try {
            $oUser                 = User::findFirst($nUserId);
            $oUserTodayCashService = new UserTodayCashService($nUserId);
            // 用户
            $row['user'] = [
                'user_invite_code'            => $oUser->user_invite_code,
                'user_invite_effective_total' => $oUser->user_invite_effective_total,
                'user_invite_total_female'    => $oUser->user_invite_total_female,
                'user_invite_total_male'      => (string)$oUser->user_invite_total - $oUser->user_invite_total_female,
                'user_cash'                   => $oUser->user_cash,
                'today_income_cash'           => (string)$oUserTodayCashService->getData(),
                'user_invite_dot_total'       => sprintf('%.2f', $oUser->user_invite_dot_total),
                'user_invite_total'           => $oUser->user_invite_total,
            ];

            // 分享
            $row['share'] = $this->getShareInfo($oUser);

            if ( $oUser->user_is_anchor == 'Y' ) {
                $row['rule'] = [
                    [
                        'title'   => '邀请用户充值有奖',
                        'content' => sprintf('您邀请的人，每充值一次金币，您将获得对方当前充值金额%s的等额佣金', Kv::get(Kv::INVITE_ANCHOR_RECHARGE_RADIO) . '%'),
                    ],
                ];
            } else {
                $row['rule'] = [
                    [
                        'title'   => '奖励一：邀请用户有奖',
                        'content' => sprintf('每邀请一个真实注册的男性用户，奖励%s元现金，上不封顶', Kv::get(Kv::INVITE_REGISTER_CASH)),
                    ],
                    [
                        'title'   => '奖励二：邀请用户充值VIP有奖',
                        'content' => sprintf('您邀请的人，每充值一次VIP会员，您将获得对方当前充值金额%s的等额现金', Kv::get(Kv::INVITE_VIP_RADIO_CASH) . '%'),
                    ],
                    [
                        'title'   => '奖励三：邀请用户充值有奖',
                        'content' => sprintf('您邀请的人，每充值一次金币，您将获得对方当前充值金额%s的等额现金', Kv::get(Kv::INVITE_RECHARGE_RADIO_CASH) . '%'),
                    ],
                    [
                        'title'   => '奖励四：邀请用户提现有奖',
                        'content' => sprintf('您邀请的是真实注册的女性用户，并申请成为主播，那么，她每次提现，您将获得对方当前提现金额%s的等额现金', Kv::get(Kv::INVITE_WITHDRAW_RADIO_CASH) . '%'),
                    ],
                ];
            }


        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/invite/shareSuccess
     * @api {post} /user/invite/shareSuccess 分享成功
     * @apiName invite-shareSuccess
     * @apiGroup User
     * @apiDescription 分享成功
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String='qq','wx','wx_f','wb','qzone'} oauth_type 第三方类型  qq,wx(微信),wb(微博),wx_f(微信朋友圈),qzone(QQ空间)
     * @apiParam (正常请求){String='invite(邀请)','short_video(小视频)','profile(个人中心)','posts(动态)','invite_video(邀请视频)'} type 类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String='qq','wx','wx_f','wb','qzone'} oauth_type 第三方类型  qq,wx(微信),wb(微博),wx_f(微信朋友圈),qzone(QQ空间)
     * @apiParam (debug){String='invite(邀请)','short_video(小视频)','profile(个人中心)','posts(动态)','invite_video(邀请视频)'} type 类型
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
    public function shareSuccessAction($nUserId = 0)
    {
        $sOauthType = $this->getParams('oauth_type', 'string', '');
        $sType      = $this->getParams('type');
        try {
            if ( !in_array($sOauthType, [
                'qq',
                'wx',
                'wb',
                'wx_f',
                'qzone'
            ]) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_anchor == 'N' ) {
                $oUserFirstShareService = new UserFirstShareService($nUserId);
                $flg                    = $oUserFirstShareService->delete();
                if ( $flg > 0 ) {
                    // 能删除 证明有奖励权限
                    $first_share_reward_match_times = Kv::get(Kv::FIRST_SHARE_REWARD_MATCH_TIMES);
                    if ( Kv::get(Kv::FIRST_SHARE_REWARD_FLG) == 'Y' && $first_share_reward_match_times > 0 ) {
                        // 奖励开启中
                        $oUserShareRewardLog = new UserShareRewardLog();
                        $connection          = $oUserShareRewardLog->getWriteConnection();
                        $connection->begin();
                        $oUserShareRewardLog->user_id           = $nUserId;
                        $oUserShareRewardLog->share_type        = $sOauthType;
                        $oUserShareRewardLog->reward_free_times = $first_share_reward_match_times;

                        if ( !$oUserShareRewardLog->save() ) {
                            $connection->rollback();
                            $this->success();
                        } else {
                            $oUser->user_free_match_time = $first_share_reward_match_times;
                            if ( !$oUser->save() ) {
                                $connection->rollback();
                                $this->success();
                            }
                            $connection->commit();
                        }
                    }
                }
            }
            $oUser->user_share_times += 1;
            $oUser->save();
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }
}