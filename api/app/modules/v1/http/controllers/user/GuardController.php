<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 守护                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;


use app\helper\ResponseError;
use app\models\Anchor;
use app\models\Kv;
use app\models\LevelConfig;
use app\models\User;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use app\models\UserGuard;
use app\models\UserGuardLog;
use app\models\UserIntimateLog;
use app\services\ActivityUserService;
use app\services\AnchorStatService;
use app\services\AnchorTodayDotService;
use app\services\IntimateService;
use Exception;
use app\http\controllers\ControllerBase;

/**
 * GuardController
 */
class GuardController extends ControllerBase
{
    use \app\services\SystemMessageService;

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/guard/anchor
     * @api {get} /user/guard/anchor 主播的守护列表
     * @apiName guard-anchor
     * @apiGroup Guard
     * @apiDescription 主播的守护列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} anchor_user_id 主播用户id
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} anchor_user_id 主播用户id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.user_id    用户id
     * @apiSuccess {String} d.items.user_nickname 用户昵称
     * @apiSuccess {String} d.items.user_avatar 用户头像
     * @apiSuccess {number} d.items.total_coin  用户守护值
     * @apiSuccess {number} d.items.current_level  守护等级
     * @apiSuccess {String} d.items.current_level_name   守护等级名称
     * @apiSuccess {String} d.items.guard_status  守护状态  Y 为守护中， N 为守护过
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *          {
     *              "c": 0,
     *              "m": "请求成功",
     *              "d": {
     *                      "items": [
     *                      {
     *                          "user_id": "310",
     *                          "user_nickname": "1181732245amxij11151742522",
     *                          "user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                          "total_coin": "1680",
     *                          "current_level": "1",
     *                          "current_level_name": "中级守护",
     *                          "guard_status": "Y"
     *                      },
     *                      {
     *                          "user_id": "311",
     *                          "user_nickname": "1181732245amx",
     *                          "user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                          "total_coin": "100",
     *                      *              "current_level": "1",
     *                          "current_level_name": "初级守护",
     *                          "guard_status": "N"
     *                      }
     *                  ],
     *                  "page": 1,
     *                  "pagesize": 20,
     *                  "pagetotal": 1,
     *                  "total": 2,
     *                  "prev": 1,
     *                  "next": 1
     *              },
     *              "t": "1543199896"
     *          }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function anchorAction($nUserId = 0)
    {
        $anchorUserId = $this->getParams('anchor_user_id');
        $nPage        = $this->getParams('page', 'int', 1);
        $nPagesize    = $this->getParams('pagesize', 'int', 20);
        try {
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ug' => UserGuard::class ])
                ->join(User::class, sprintf('ug.user_id=u.user_id and ug.anchor_user_id=%d', $anchorUserId), 'u')
                ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,ug.total_coin,ug.current_level,ug.current_level_name,ug.guard_status')
                ->orderBy('ug.total_coin desc');
            $row     = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/guard/anchorDetail
     * @api {get} /user/guard/anchorDetail 主播的守护信息详情
     * @apiName Guard-anchorDetail
     * @apiGroup Guard
     * @apiDescription 主播的守护信息详情
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} anchor_user_id 主播用户id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} anchor_user_id 主播用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.guard_user   守护用户信息
     * @apiSuccess {number} d.guard_user.user_id  守护的用户id
     * @apiSuccess {String} d.guard_user.user_nickname  守护的昵称
     * @apiSuccess {String} d.guard_user.user_avatar 守护的头像
     * @apiSuccess {String} d.guard_user.guard_level 守护的等级
     * @apiSuccess {String} d.guard_user.guard_level_name 守护的等级名称
     * @apiSuccess {number} d.interval_coin 金币添加步长
     * @apiSuccess {number} d.now_guard_coin 当前主播的守护值
     * @apiSuccess {number} d.current_user_guard_coin 当前自己对该主播的守护值
     * @apiSuccess {number} d.shouldPayCoin  成为守护需要支付的金币
     * @apiSuccess {String} d.rule  规则文字
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "guard_user": {
     *                      "user_id": "",
     *                      "user_nickname": "",
     *                      "user_avatar": "",
     *                      "guard_level": "",
     *                      "guard_level_name": ""
     *              },
     *               "now_guard_coin": "100",
     *               "interval_coin": 10,
     *              "current_user_guard_coin": "0",
     *              "shouldPayCoin": "100",
     *              "rule": "您需要贡献至少比当前守护值大于100守护值才能成为她的新守护哦，最低需支付100金币"
     *          },
     *          "t": "1543214097"
     *      }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function anchorDetailAction($nUserId = 0)
    {

        $anchorUserId = $this->getParams('anchor_user_id');
        try {
            $anchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $anchorUserId ]
            ]);
            if ( !$anchor ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            // 该主播的守护金币
            $nowGuardCoin = intval(Kv::get(Kv::GUARD_MIN_COIN));
            // 该用户当前的守护金币
            $currentUserGuardCoin = 0;
            // 还需支付
            // 每次挤下守护 需要超过的金币数
            $intervalGuardCoin = intval(Kv::get(Kv::GUARD_INTERVAL_COIN));
            $guardUserInfo     = [
                'user_id'          => '',
                'user_nickname'    => '',
                'user_avatar'      => '',
                'guard_level'      => '',
                'guard_level_name' => '',
            ];
            $oUserGuard        = UserGuard::findFirst([
                'user_id = :user_id: AND anchor_user_id = :anchor_user_id:',
                'bind' => [
                    'user_id'        => $nUserId,
                    'anchor_user_id' => $anchorUserId
                ],
            ]);

            if ( $oUserGuard ) {
                $currentUserGuardCoin = $oUserGuard->total_coin;
                $shouldPayCoin        = $intervalGuardCoin;
            } else {
                $shouldPayCoin = $nowGuardCoin;
            }


//            if ( $anchor->anchor_guard_id ) {
//                $oGuardUser = User::findFirst($anchor->anchor_guard_id);
//                if ( $oGuardUser ) {
//                    $guardUserInfo = [
//                        'user_id'          => $oGuardUser->user_id,
//                        'user_nickname'    => $oGuardUser->user_nickname,
//                        'user_avatar'      => $oGuardUser->user_avatar,
//                        'user_level'       => $oGuardUser->user_level,
//                        'guard_level'      => $anchor->anchor_guard_level,
//                        'guard_level_name' => $anchor->anchor_guard_level_name,
//                    ];
//                }
//                $nowGuardCoin = max($anchor->anchor_guard_coin, $nowGuardCoin);
//                // 该用户不是当前守护
//                // 判断该用户对该主播的守护值
//                $oUserGuard = UserGuard::findFirst([
//                    'user_id = :user_id: AND anchor_user_id = :anchor_user_id:',
//                    'bind' => [
//                        'user_id'        => $nUserId,
//                        'anchor_user_id' => $anchorUserId
//                    ],
//                    //                        'cache' => [
//                    //                            'lifetime' => 3600,
//                    //                            'key'      => UserGuard::getCacheKey($nUserId, $anchorUserId)
//                    //                        ]
//                ]);
//                if ( $oUserGuard ) {
//                    $currentUserGuardCoin = $oUserGuard->total_coin;
//                }
//                $shouldPayCoin = $nowGuardCoin - $currentUserGuardCoin + $intervalGuardCoin;
//            }
//            else {
//                $shouldPayCoin = $nowGuardCoin - $currentUserGuardCoin;
//            }

            $coinName = Kv::get(Kv::KEY_COIN_NAME);
            $row      = [
                'guard_user'              => $guardUserInfo,
                'interval_coin'           => (int)$intervalGuardCoin,
                'now_guard_coin'          => (string)$nowGuardCoin,
                'current_user_guard_coin' => (string)$currentUserGuardCoin,
                'shouldPayCoin'           => (string)$shouldPayCoin,
                'rule'                    => sprintf('您需要贡献至少比当前守护值大于%d守护值，初次购买最低需支付%d%s', $intervalGuardCoin, $shouldPayCoin, $coinName)
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/guard/buyGuard
     * @api {post} /user/guard/buyGuard 购买守护
     * @apiName Guard-buyGuard
     * @apiGroup Guard
     * @apiDescription 购买守护
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} anchor_user_id 主播id
     * @apiParam (正常请求){String} coin 支付金币
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} anchor_user_id 主播id
     * @apiParam (debug){String} coin 支付金币
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.total_coin 总守护值
     * @apiSuccess {Object} d.level 购买后等级
     * @apiSuccess {Object} d.level_name 购买后等级名称
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     *  ### 飘屏推送  （新守护）
     *    {
     *        "type": "scroll_msg",
     *        "msg": "飘屏消息",
     *        "data": {
     *                "type": "new_guard",
     *                "info": {
     *                    "user_nickname": "1181732245amxij11151741020",
     *                    "user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                    "anchor_user_nickname": "Steven09131112487",
     *                    "anchor_user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eqiaMzLBn0wU7UqvVEsicpYunuqbxta3QKiaCBnpibBrCvluCDqH0ZJiaq7pue7DnC7yh2ZNMYaoVj9JCw/132",
     *                    "content": "1181732245amxij11151741020成为Steven09131112487最新的守护者"
     *            }
     *        }
     *    }
     *
     *  ### 飘屏推送  （守护等级提升）
     *    {
     *        "type": "scroll_msg",
     *        "msg": "飘屏消息",
     *        "data": {
     *                "type": "guard_level_up",
     *                "info": {
     *                    "user_nickname": "1181732245amxij11151741020",
     *                    "user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                    "anchor_user_nickname": "Steven09131112487",
     *                    "anchor_user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/DYAIOgq83eqiaMzLBn0wU7UqvVEsicpYunuqbxta3QKiaCBnpibBrCvluCDqH0ZJiaq7pue7DnC7yh2ZNMYaoVj9JCw/132",
     *                    "new_level_name": "土豪守护",
     *                    "content": "恭喜 Steven09131112487 的守护榜单等级到达 土豪守护，守护者是 1181732245amxij11151741020"
     *            }
     *        }
     *    }
     *
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function buyGuardAction($nUserId = 0)
    {

        $anchorUserId = $this->getParams('anchor_user_id');
        $nCoin        = $this->getParams('coin');
        try {

            $oUser   = User::findFirst($nUserId);
            $oAnchor = Anchor::findFirst([
                'user_id = :user_id:',
                'bind' => [ 'user_id' => $anchorUserId ]
            ]);
            if ( !$oAnchor ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $intervalGuardCoin = intval(Kv::get(Kv::GUARD_INTERVAL_COIN));
            $nowGuardCoin      = intval(Kv::get(Kv::GUARD_MIN_COIN));
            $oUserGuard        = UserGuard::findFirst([
                'user_id =:user_id: AND anchor_user_id = :anchor_user_id:',
                'bind' => [
                    'user_id'        => $nUserId,
                    'anchor_user_id' => $anchorUserId
                ]
            ]);
            $existsCoin        = 0;
            if ( $oUserGuard ) {
                $existsCoin = $oUserGuard->total_coin;
                $shouldPay  = $intervalGuardCoin;
            } else {
                $shouldPay = $nowGuardCoin;
            }
            if ( $nCoin < $shouldPay ) {
                throw new Exception(sprintf(ResponseError::getError(ResponseError::PAY_LESS_THAN), $shouldPay), ResponseError::PAY_LESS_THAN);
            }
            if ( $oUser->user_coin + $oUser->user_free_coin < $nCoin ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }
            $oldGuardUserId = $oAnchor->anchor_guard_id;

            $this->db->begin();

            // 用户扣钱
            $consumeFreeCoin = 0;
            $consumeCoin     = 0;
            if ( $oUser->user_free_coin <= 0 ) {
                // 直接扣充值币
                $consumeCoin = $nCoin;

            } else if ( $oUser->user_free_coin < $nCoin ) {
                //扣一部分充值币 扣光赠送币
                $consumeFreeCoin = $oUser->user_free_coin;
                $consumeCoin     = $nCoin - $oUser->user_free_coin;
            } else {
                $consumeFreeCoin = $nCoin;
            }

            // 主播可获得的金币 (后台配置比例为整数 所以要除以100  然后金币换佣金 要除以10)
            $oAnchorUser = User::findFirst($anchorUserId);
            $nRatio      = $oAnchor->getCoinToDotRatio($oAnchorUser, Anchor::RATIO_GUARD);
            $nDot        = sprintf('%.4f', $nCoin * $nRatio / 100);
            $getDot      = sprintf('%.4f', $consumeCoin * ($nRatio / 100));
            $getFreeDot  = round($nDot - $getDot, 4);

            //扣费
            $exp       = $nCoin * intval(Kv::get(Kv::COIN_TO_EXP));
            $userLevel = User::getUserLevel($oUser->user_exp + $exp);

            $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin,user_exp = user_exp + :exp,user_level = :user_level
where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin';
            $this->db->execute($sql, [
                'consume_free_coin' => $consumeFreeCoin,
                'consume_coin'      => $consumeCoin,
                'user_id'           => $nUserId,
                'exp'               => $exp,
                'user_level'        => $userLevel
            ]);
            if ( $this->db->affectedRows() <= 0 ) {
                // 赠送币 不够钱
                $this->db->rollback();
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }
            $total_coin = $nCoin + $existsCoin;
            $levelInfo  = LevelConfig::getLevelInfo($total_coin, LevelConfig::LEVEL_TYPE_GUARD);


            // 主播添加佣金
            $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
            $this->db->execute($sql, [
                'total_dot'    => $nDot,
                'get_dot'      => $getDot,
                'get_free_dot' => $getFreeDot,
                'user_id'      => $anchorUserId,
            ]);
            if ( $this->db->affectedRows() <= 0 ) {
                $this->db->rollback();
                throw new Exception(ResponseError::getError(ResponseError::OPERATE_FAILED), ResponseError::OPERATE_FAILED);
            }

            if ( $total_coin > $oAnchor->anchor_guard_coin ) {
                // 修改主播的守护
                $updateSql = "update anchor set anchor_guard_id = :anchor_guard_id,anchor_guard_coin = :anchor_guard_coin,anchor_guard_level = :anchor_guard_level,anchor_guard_level_name = :anchor_guard_level_name where user_id = :user_id and anchor_guard_id = :old_anchor_guard_id";
                $this->db->execute($updateSql, [
                    'anchor_guard_id'         => $nUserId,
                    'anchor_guard_coin'       => $total_coin,
                    'user_id'                 => $anchorUserId,
                    'old_anchor_guard_id'     => $oAnchor->anchor_guard_id,
                    'anchor_guard_level'      => $levelInfo['level'],
                    'anchor_guard_level_name' => $levelInfo['level_name'],
                ]);
                if ( $this->db->affectedRows() <= 0 ) {
                    throw new Exception(ResponseError::getError(ResponseError::OPERATE_FAILED), ResponseError::OPERATE_FAILED);
                }
            }

            // 获取等级
            // 修改用户守护信息
            $isNewGuard     = FALSE;
            $isGuardLevelUp = FALSE;
            if ( !$oUserGuard ) {
                $isNewGuard                 = TRUE;
                $oUserGuard                 = new UserGuard();
                $oUserGuard->user_id        = $nUserId;
                $oUserGuard->anchor_user_id = $anchorUserId;
            } else if ( $oUserGuard->current_level < $levelInfo['level'] ) {
                // 如果存在 并且守护等级增加
                $isGuardLevelUp = TRUE;
            }
            $oUserGuard->total_coin         = $total_coin;
            $oUserGuard->current_level      = $levelInfo['level'];
            $oUserGuard->current_level_name = $levelInfo['level_name'];
            $oUserGuard->guard_status       = 'Y';

            if ( !$oUserGuard->save() ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserGuard->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
//            if ( $oldGuardUserId && $oldGuardUserId != $nUserId ) {
//                //将上一个守护状态失效   且上个守护不是自己
//                $oOldUserGuard = UserGuard::findFirst([
//                    'user_id =:user_id: AND anchor_user_id = :anchor_user_id:',
//                    'bind' => [
//                        'user_id'        => $oldGuardUserId,
//                        'anchor_user_id' => $oAnchor->user_id
//                    ]
//                ]);
//                if ( $oOldUserGuard && !$oOldUserGuard->save([ 'guard_status' => 'N' ]) ) {
//                    $this->db->rollback();
//                    throw new Exception(
//                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oOldUserGuard->getMessages())),
//                        ResponseError::OPERATE_FAILED
//                    );
//                }
//            }

            // 添加记录
            $oUserGuardLog                     = new UserGuardLog();
            $oUserGuardLog->user_id            = $nUserId;
            $oUserGuardLog->anchor_user_id     = $anchorUserId;
            $oUserGuardLog->total_coin         = $total_coin;
            $oUserGuardLog->consume_coin       = $nCoin;
            $oUserGuardLog->current_level      = $levelInfo['level'];
            $oUserGuardLog->current_level_name = $levelInfo['level_name'];
            if ( !$oUserGuardLog->save() ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserGuardLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            // 记录用户流水
            $oUserFinanceLog                         = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id                = $oUser->user_id;
            $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
            $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::GUARD_PAY;
            $oUserFinanceLog->consume                = -$nCoin;
            $oUserFinanceLog->remark                 = '购买守护消耗';
            $oUserFinanceLog->flow_id                = $oUserGuardLog->id;
            $oUserFinanceLog->flow_number            = '';
            $oUserFinanceLog->type                   = 0;
            $oUserFinanceLog->target_user_id         = $anchorUserId;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            $flg                                     = $oUserFinanceLog->save();
            if ( $flg == FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $oAnchorUser = User::findFirst($oAnchor->user_id);

            // 记录主播流水
            $oUserFinanceLog                      = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
            $oUserFinanceLog->user_id             = $oAnchorUser->user_id;
            $oUserFinanceLog->user_current_amount = $oAnchorUser->user_dot + $nDot;
            $oUserFinanceLog->user_last_amount    = $oAnchorUser->user_dot;
            $oUserFinanceLog->consume_category_id = UserConsumeCategory::GUARD_GET;
            $oUserFinanceLog->consume             = +$nDot;
            $oUserFinanceLog->remark              = '守护分成';
            $oUserFinanceLog->flow_id             = $oUserGuardLog->id;
            $oUserFinanceLog->type                = 0;
            $oUserFinanceLog->group_id            = $oAnchorUser->user_group_id;
            $oUserFinanceLog->consume_source      = -$nCoin;
            $oUserFinanceLog->target_user_id      = $nUserId;
            $flg                                  = $oUserFinanceLog->save();
            if ( $flg == FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $this->db->commit();

            // 发送飘屏
            if ( $isNewGuard ) {
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                $this->timServer->sendScrollMsg([
                    'type' => 'new_guard',
                    'info' => [
                        'user_nickname'        => $oUser->user_nickname,
                        'user_avatar'          => $oUser->user_avatar,
                        'user_level'           => $oUser->user_level,
                        'anchor_user_nickname' => $oAnchorUser->user_nickname,
                        'anchor_user_avatar'   => $oAnchorUser->user_avatar,
                        'anchor_user_level'    => $oAnchorUser->user_level,
                        'title'                => sprintf('%s 成为 %s', $oUser->user_nickname, $oAnchorUser->user_nickname),
                        'content'              => '最新的守护者',
                    ]
                ]);
            } else if ( $isGuardLevelUp ) {
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                $this->timServer->sendScrollMsg([
                    'type' => 'guard_level_up',
                    'info' => [
                        'user_nickname'        => $oUser->user_nickname,
                        'user_avatar'          => $oUser->user_avatar,
                        'user_level'           => $oUser->user_level,
                        'anchor_user_nickname' => $oAnchorUser->user_nickname,
                        'anchor_user_avatar'   => $oAnchorUser->user_avatar,
                        'anchor_user_level'    => $oAnchorUser->user_level,
                        'new_level_name'       => $levelInfo['level_name'],
                        'title'                => sprintf(' %s 与 %s', $oAnchorUser->user_nickname, $oUser->user_nickname),
                        'content'              => sprintf('守护等级到达 %s', $levelInfo['level_name']),
                    ]
                ]);
            }

            // 发送系统消息（成为守护）
            $this->sendBecomeGuardMsg($nUserId, $oAnchorUser, $nCoin, $total_coin);

            if ( $oldGuardUserId && $oldGuardUserId != $nUserId ) {
                // 发送系统消息（守护被抢）
//                $this->sendGuardRobbedMsg($oldGuardUserId, $oAnchorUser);
            }

            // 发送系统消息给主播 （守护有变更）
            $this->sendAnchorGuardMsg($oAnchorUser->user_id, $oUser, $nCoin, $nDot);

            // 主播今日收益 增加
            $oAnchorTodayDotService = new AnchorTodayDotService($oAnchorUser->user_id);
            $oAnchorTodayDotService->save($nDot);

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($oAnchorUser->user_id);
            $oAnchorStatService->save(AnchorStatService::GUARD_INCOME, $nDot);

            if ( $nCoin ) {

                // 活动消费统计
                $oActivityUserService = new ActivityUserService();
                $oActivityUserService->save($nUserId, $nCoin);
            }

            if($nCoin){
                // 开始# 亲密值
                $intimateMultiple = Kv::get(Kv::COIN_TO_INTIMATE) ?? 1;
                $intimateValue    = $nCoin * $intimateMultiple;
                if ( $intimateValue > 0 ) {
                    $oUserIntimateLog                              = new UserIntimateLog();
                    $oUserIntimateLog->intimate_log_user_id        = $nUserId;
                    $oUserIntimateLog->intimate_log_anchor_user_id = $oAnchorUser->user_id;
                    $oUserIntimateLog->intimate_log_type           = UserIntimateLog::TYPE_GUARD;
                    $oUserIntimateLog->intimate_log_value          = $nCoin * $intimateMultiple;
                    $oUserIntimateLog->intimate_log_coin           = $nCoin;
                    $oUserIntimateLog->intimate_log_dot            = $nDot;
                    $oUserIntimateLog->save();
                }
                // 结束# 亲密值
            }


            $row = [
                'total_coin' => (string)$total_coin,
                'level'      => (string)$levelInfo['level'],
                'level_name' => $levelInfo['level_name']
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/guard/user
     * @api {get} /user/guard/user 用户的守护列表
     * @apiName guard-user
     * @apiGroup Guard
     * @apiDescription 用户的守护列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} user_id 用户id
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} user_id 用户id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.user_id    用户id
     * @apiSuccess {String} d.items.user_nickname 用户昵称
     * @apiSuccess {String} d.items.user_avatar 用户头像
     * @apiSuccess {number} d.items.total_coin  用户守护值
     * @apiSuccess {number} d.items.current_level  守护等级
     * @apiSuccess {String} d.items.current_level_name   守护等级名称
     * @apiSuccess {String} d.items.guard_status  守护状态  Y 为守护中， N 为守护过
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "items": [
     *                 {
     *                     "user_id": "170",
     *                     "user_nickname": "神秘",
     *                     "user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg/132",
     *                     "total_coin": "100",
     *                     "current_level": "1",
     *                     "current_level_name": "初级守护",
     *                     "guard_status": "Y"
     *                 },
     *                 {
     *                     "user_id": "168",
     *                     "user_nickname": "啦啦啦",
     *                     "user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/e1u7Ut4rUff6QDfsRXuTjJwpuqaEBeyBL8FC7bIu6fcuXkogvUBRYLVCIRFLQicgwxVVC3dibibSbkxM88BXsQVSA/132",
     *                     "total_coin": "1680",
     *                     "current_level": "1",
     *                     "current_level_name": "中级守护",
     *                     "guard_status": "Y"
     *                 }
     *             ],
     *             "page": 1,
     *             "pagesize": 20,
     *             "pagetotal": 1,
     *             "total": 2,
     *             "prev": 1,
     *             "next": 1
     *         },
     *         "t": "1543200144"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function userAction($nUserId = 0)
    {
        $userId    = $this->getParams('user_id');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            if ( $userId ) {
                $nUserId = $userId;
            }
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ug' => UserGuard::class ])
                ->join(User::class, 'ug.anchor_user_id=u.user_id', 'u')
                ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,ug.total_coin,ug.current_level,ug.current_level_name,ug.guard_status')
                ->where('ug.user_id = :user_id:', [
                    'user_id' => $nUserId
                ])
                ->orderBy('ug.guard_status,ug.total_coin desc');
            $row     = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/guard/index
     * @api {get} /user/guard/index 守护介绍
     * @apiName Guard-index
     * @apiGroup Guard
     * @apiDescription 守护介绍  获取守护规则 以及守护等级列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.rule  规则
     * @apiSuccess {object[]} d.level  等级信息
     * @apiSuccess {number} d.level.level  等级值
     * @apiSuccess {String} d.level.level_name  等级名称
     * @apiSuccess {number} d.level.coin 等级所需要的金币数
     * @apiSuccess {number} d.level.free_duration 等级拥有的每日免费时长
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *         {
     *             "c": 0,
     *             "m": "请求成功",
     *             "d": {
     *                     "rule": "心仪的对象无人守护时，成为唯一守护最低需支付100金币，当对方已有守护时，用户需要支付比当前唯一守护贡献的守护值高0金币才能成为该主播的唯一守护",
     *                     "level": [
     *                         {
     *                             "level": "0",
     *                             "level_name": "萍水相逢",
     *                             "coin": "0",
     *                             "free_duration": "0"
     *                         },
     *                         {
     *                             "level": "1",
     *                             "level_name": "初级守护",
     *                             "coin": "100",
     *                             "free_duration": "0"
     *                         },
     *                         {
     *                             "level": "2",
     *                             "level_name": "中级守护",
     *                             "coin": "1680",
     *                             "free_duration": "2"
     *                         },
     *                         {
     *                             "level": "3",
     *                             "level_name": "高级守护",
     *                             "coin": "5200",
     *                             "free_duration": "5"
     *                         },
     *                         {
     *                             "level": "4",
     *                             "level_name": "土豪守护",
     *                             "coin": "9990",
     *                             "free_duration": "10"
     *                         },
     *                         {
     *                             "level": "5",
     *                             "level_name": "贴心守护",
     *                             "coin": "18880",
     *                             "free_duration": "15"
     *                         },
     *                         {
     *                             "level": "6",
     *                             "level_name": "无敌守护",
     *                             "coin": "61880",
     *                             "free_duration": "30"
     *                         }
     *                     ]
     *             },
     *             "t": "1543201350"
     *         }
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
            // 获取守护规则
            $minGuardCoin      = intval(Kv::get(Kv::GUARD_MIN_COIN));
            $intervalGuardCoin = intval(Kv::get(Kv::GUARD_INTERVAL_COIN));
            $coinName          = Kv::get(Kv::KEY_COIN_NAME);

//            $ruleContent = sprintf("心仪的对象无人守护时，成为唯一守护最低需支付%d%s，当对方已有守护时，用户需要支付比当前唯一守护贡献的守护值高%d%s才能成为该主播的唯一守护。",
            $ruleList = [
                sprintf("01.守护是体现两个人的忠诚度量化标尺，用户如有喜欢的女神，可对心仪的女神最低支付%d%s成为守护者，守护值越高守护等级特权越多。",$minGuardCoin, $coinName),
                '02.一位女神可以有多位守护者守护值最高的为女神的最强守护。',
                '04.最高守护者可获得抢聊特权（具体查看抢聊特权介绍）',
                '05.用户如30天未登录上线与女神互动，系统将会对该用户与该女神的守护值清零，所以要及时上线守护你的女神。'
            ];
            $ruleContent = implode("\r\n\r\n      ",$ruleList);
            // 获取等级列表
            $oLevelConfig = LevelConfig::find([
                'level_type = :level_type:',
                'bind'    => [
                    'level_type' => LevelConfig::LEVEL_TYPE_GUARD
                ],
                'columns' => 'level_value as level,level_name,level_exp as coin,level_extra as free_duration',
                'order'   => 'level_value asc'
            ]);
            $row          = [
                'rule'  => $ruleContent,
                'level' => $oLevelConfig
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/guard/rank
     * @api {get} /user/guard/rank 守护排行榜
     * @apiName Guard-rank
     * @apiGroup Guard
     * @apiDescription 守护排行榜
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.anchor_user_id   主播用户id
     * @apiSuccess {String} d.items.anchor_user_nickname  主播昵称
     * @apiSuccess {String} d.items.anchor_user_avatar 主播头像
     * @apiSuccess {number} d.items.guard_user_id  用户id
     * @apiSuccess {String} d.items.guard_user_nickname  用户昵称
     * @apiSuccess {String} d.items.guard_user_avatar  用户头像
     * @apiSuccess {number} d.items.anchor_guard_coin  守护金币
     * @apiSuccess {number} d.items.anchor_guard_level  守护等级
     * @apiSuccess {String} d.items.anchor_guard_level_name  守护名称
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "items": [
     *                 {
     *                     "anchor_user_id": "168",
     *                     "anchor_user_nickname": "啦啦啦",
     *                     "anchor_user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/e1u7Ut4rUff6QDfsRXuTjJwpuqaEBeyBL8FC7bIu6fcuXkogvUBRYLVCIRFLQicgwxVVC3dibibSbkxM88BXsQVSA/132",
     *                     "guard_user_id": "310",
     *                     "guard_user_nickname": "1181732245amxij11151742522",
     *                     "guard_user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                     "anchor_guard_coin": "1680",
     *                     "anchor_guard_level": "2",
     *                     "anchor_guard_level_name": "中级守护"
     *                 },
     *                 {
     *                     "anchor_user_id": "170",
     *                     "anchor_user_nickname": "神秘",
     *                     "anchor_user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg/132",
     *                     "guard_user_id": "310",
     *                     "guard_user_nickname": "1181732245amxij11151742522",
     *                     "guard_user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
     *                     "anchor_guard_coin": "100",
     *                     "anchor_guard_level": "1",
     *                     "anchor_guard_level_name": "初级守护"
     *                 },
     *                 {
     *                     "anchor_user_id": "258",
     *                     "anchor_user_nickname": "LYXXMY一样",
     *                     "anchor_user_avatar": "http://thirdqq.qlogo.cn/qqapp/1106652113/23F1690D0AD8715603EA3D3E1AF30D19/100",
     *                     "guard_user_id": "276",
     *                     "guard_user_nickname": "天行swim95710",
     *                     "guard_user_avatar": "http://tvax3.sinaimg.cn/crop.0.0.40.40.180/007gZz1yly8fuo3yqnvisj30140140sh.jpg",
     *                     "anchor_guard_coin": "100",
     *                     "anchor_guard_level": "0",
     *                     "anchor_guard_level_name": ""
     *                 }
     *             ],
     *             "page": 1,
     *             "pagesize": 10,
     *             "pagetotal": 1,
     *             "total": 3,
     *             "prev": 1,
     *             "next": 1
     *         },
     *         "t": "1543214600"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function rankAction($nUserId = 0)
    {
        try {
            $data = $this->redis->get('guard_rank');
            if ( $data ) {
                $row = json_decode($data, TRUE);
            }
            else {
                $builder = $this->modelsManager
                    ->createBuilder()
                    ->from([ 'a' => Anchor::class ])
                    ->join(User::class, 'a.user_id=au.user_id', 'au')
                    ->join(User::class, 'a.anchor_guard_id=gu.user_id', 'gu')
                    ->columns('au.user_id as anchor_user_id,au.user_nickname as anchor_user_nickname,au.user_avatar as anchor_user_avatar,au.user_level as anchor_user_level,
                gu.user_id as guard_user_id,gu.user_nickname as guard_user_nickname,gu.user_avatar as guard_user_avatar,gu.user_level as guard_user_level,anchor_guard_coin,anchor_guard_level,anchor_guard_level_name')
                    ->orderBy('anchor_guard_coin desc');
                $row     = $this->page($builder, 1, 10);
                $this->redis->set('guard_rank', json_encode($row));
                $this->redis->expire('guard_rank', 60);
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/guard/userInfo
     * @api {get} /user/guard/userInfo 用户守护信息
     * @apiName guard-userInfo
     * @apiGroup Guard
     * @apiDescription 用户守护信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.user_id   主播用户id
     * @apiSuccess {String} d.user_nickname  主播昵称
     * @apiSuccess {String} d.user_avatar  主播头像
     * @apiSuccess {number} d.total_coin  守护值
     * @apiSuccess {number} d.guard_level  守护等级d
     * @apiSuccess {String} d.guard_level_name  守护等级名称
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *        {
     *            "c": 0,
     *            "m": "请求成功",
     *            "d": {
     *                    "user_id": "282",
     *                "user_nickname": "晴空万里10221822527",
     *                "user_avatar": "http://thirdqq.qlogo.cn/qqapp/1106652113/B79977733C76336E958F90618F14B2DD/100",
     *                "total_coin": "100",
     *                "guard_level": "1",
     *                "guard_level_name": "初级守护"
     *            },
     *            "t": "1543893413"
     *        }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function userInfoAction($nUserId = 0)
    {

        try {
            $oUserGuard      = UserGuard::findFirst([
                'user_id = :user_id: AND guard_status = "Y"',
                'bind'  => [
                    'user_id' => $nUserId,
                ],
                'order' => 'total_coin desc'
            ]);
            $guardAnchorInfo = [
                'user_id'          => '',
                'user_nickname'    => '',
                'user_avatar'      => '',
                'total_coin'       => '',
                'guard_level'      => '',
                'guard_level_name' => '',
            ];
            if ( $oUserGuard ) {
                $anchorUser      = User::findFirst($oUserGuard->anchor_user_id);
                $guardAnchorInfo = [
                    'user_id'          => $anchorUser->user_id,
                    'user_nickname'    => $anchorUser->user_nickname,
                    'user_avatar'      => $anchorUser->user_avatar,
                    'user_level'       => $anchorUser->user_level,
                    'total_coin'       => $oUserGuard->total_coin,
                    'guard_level'      => $oUserGuard->current_level,
                    'guard_level_name' => $oUserGuard->current_level_name,
                ];
            }
            $row = $guardAnchorInfo;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

}