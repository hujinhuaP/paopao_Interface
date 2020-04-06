<?php

namespace app\http\controllers\live;

use app\helper\ResponseError;
use app\models\Anchor;
use app\models\ChatGameCategory;
use app\models\ChatGameConfig;
use app\models\ChatGameLog;
use app\models\Group;
use app\models\Kv;
use app\models\LevelConfig;
use app\models\User;
use app\models\UserChatGame;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use app\models\UserIntimateLog;
use app\models\UserPrivateChatLog;
use app\services\ActivityUserService;
use app\services\AnchorStatService;
use app\services\AnchorTodayDotService;
use app\services\IntimateService;
use app\services\VideoChatService;
use Exception;
use app\http\controllers\ControllerBase;

/**
 * ChatgameController 聊天游戏
 */
class ChatgameController extends ControllerBase
{

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/chatgame/category
     * @api {get} /live/chatgame/category 游戏分类
     * @apiName chatgame-category
     * @apiGroup Chatgame
     * @apiDescription 游戏分类
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.category
     * @apiSuccess {number} d.category.category_id  分类id
     * @apiSuccess {String} d.category.chat_game_category_name 分类名称
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "category": [{
     *                      "category_id": "1",
     *                      "chat_game_category_name": "真心话"
     *              }, {
     *                      "category_id": "2",
     *                      "chat_game_category_name": "大冒险"
     *              }]
     *          },
     *          "t": "1545632526"
     *      }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function categoryAction($nUserId = 0)
    {
        try {
            $oUserChatCategory = ChatGameCategory::find([
                'columns' => 'id as category_id,chat_game_category_name'
            ]);
            $row               = [
                'category' => $oUserChatCategory
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/chatgame/index
     * @api {get} /live/chatgame/index 聊天配置表
     * @apiName chatgame-index
     * @apiGroup Chatgame
     * @apiDescription 聊天配置表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求) {String} category_id  游戏分类id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} category_id  游戏分类id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.list
     * @apiSuccess {number} d.list.game_id    游戏配置id
     * @apiSuccess {String} d.list.chat_game_content  游戏文字内容
     * @apiSuccess {number} d.list.chat_game_price   游戏金币价格
     * @apiSuccess {number} d.list.user_select_id    自己是否选择了此游戏 已选择为 记录id 未选择为 0
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "list": [{
     *                      "game_id": "1",
     *                      "chat_game_content": "今天几点起床？",
     *                      "chat_game_price": "100",
     *                      "user_select_id": "1"
     *              }, {
     *                      "game_id": "2",
     *                      "chat_game_content": "今天xxxxx？",
     *                      "chat_game_price": "200",
     *                      "user_select_id": "0"
     *              }]
     *          },
     *          "t": "1545633220"
     *      }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction($nUserId = 0)
    {
        $sCategoryId = $this->getParams('category_id');
        try {
            $aChatGame = $this->modelsManager
                ->createBuilder()
                ->from([ 'cgc' => ChatGameConfig::class ])
                ->leftJoin(UserChatGame::class, 'ucg.chat_game_id = cgc.id AND ucg.user_id = ' . intval($nUserId), 'ucg')
                ->columns('cgc.id as game_id,cgc.chat_game_content,cgc.chat_game_price,ifnull(ucg.id,0) as user_select_id')
                ->where('cgc.chat_game_category_id = :chat_game_category_id:', [ 'chat_game_category_id' => $sCategoryId ])
                ->orderBy('ucg.id desc,cgc.id desc')
                ->getQuery()
                ->execute()
                ->toArray();

            $row['list'] = $aChatGame;

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/chatgame/anchor
     * @api {get} /live/chatgame/anchor 主播选择的游戏
     * @apiName chatgame-anchor
     * @apiGroup Chatgame
     * @apiDescription 主播选择的游戏
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
     * @apiSuccess {object[]} d.list
     * @apiSuccess {number} d.list.game_id   游戏id
     * @apiSuccess {String} d.list.chat_game_content  游戏内容
     * @apiSuccess {number} d.list.chat_game_price  价格
     * @apiSuccess {number} d.list.chat_game_category_id  分类id
     * @apiSuccess {String} d.list.category_name  分类名称
     * @apiSuccess {String} d.list.intimate_value  亲密值
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "list": [{
     *                      "game_id": "1",
     *                      "chat_game_content": "今天几点起床？",
     *                      "chat_game_price": "100",
     *                      "chat_game_category_id": "1",
     *                      "category_name": "真心话",
     *                      "intimate_value": "1000"
     *              }]
     *          },
     *          "t": "1545817549"
     *      }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function anchorAction($nUserId = 0)
    {
        $sAnchorUserId = $this->getParams('anchor_user_id');
        try {
            $aChatGame = $this->modelsManager
                ->createBuilder()
                ->from([ 'cgc' => ChatGameConfig::class ])
                ->join(UserChatGame::class, 'ucg.chat_game_id = cgc.id AND ucg.user_id = ' . intval($sAnchorUserId), 'ucg')
                ->columns('cgc.id as game_id,cgc.chat_game_content,cgc.chat_game_price,cgc.chat_game_category_id')
                ->orderBy('rand()')
                ->getQuery()
                ->execute()
                ->toArray();

            $oUserChatCategory    = ChatGameCategory::find([
                'columns' => 'id,chat_game_category_name'
            ])->toArray();
            $oUserChatCategoryArr = array_column($oUserChatCategory, 'chat_game_category_name', 'id');

            $intimateRadio = intval(Kv::get(Kv::COIN_TO_INTIMATE));
            foreach ( $aChatGame as &$gameItem ) {
                $gameItem['category_name']  = $oUserChatCategoryArr[$gameItem['chat_game_category_id']];
                $gameItem['intimate_value'] = (string)($gameItem['chat_game_price'] * $intimateRadio);
            }

            $row['list'] = $aChatGame;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row, FALSE);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/chatgame/add
     * @api {post} /live/chatgame/add 发起游戏
     * @apiName chatgame-add
     * @apiGroup Chatgame
     * @apiDescription 发起游戏  购买成功后 推送消息给主播 分成比例公会设置
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} chat_log 聊天id
     * @apiParam (正常请求){String} game_id 游戏id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_log 聊天id
     * @apiParam (debug){String} game_id 游戏id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "user_coin":"100",
     *              "exp":"100",
     *              "intimate_value":"100",
     *              "anchor_exp":"100",
     *          },
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     *  购买游戏推送
     *    {
     *        "type": "chat_game",
     *        "msg": "聊天游戏",
     *        "data": {
     *                "game_id": "10",
     *                "chat_log": "4493"
     *                "chat_game_content": "内容"
     *                "chat_game_price": "100"
     *                "chat_game_category_name": "大冒险",
     *                "exp": "用户经验",
     *                "intimate_value": "亲密度",
     *                "anchor_exp": "主播经验（魅力值）",
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
    public function addAction($nUserId = 0)
    {
        $sChatLogId = $this->getParams('chat_log');
        $sGameId    = $this->getParams('game_id');
//        $this->log->info("chat_log:".$sChatLogId);
//        $this->log->info("game_id:".$sGameId);
//        $this->log->info("user_id:".$nUserId);

        try {
            if ( empty($sChatLogId) ) {
//                throw new Exception(1111, ResponseError::PARAM_ERROR);
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oChatGameConfig = ChatGameConfig::findFirst($sGameId);
            if ( !$oChatGameConfig ) {
//                throw new Exception(2222, ResponseError::PARAM_ERROR);
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oChatGameCategory   = ChatGameCategory::findFirst($oChatGameConfig->chat_game_category_id);
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $sChatLogId ]
            ]);
            if ( $oUserPrivateChatLog->chat_log_user_id != $nUserId ) {
                // 不是用户发起
//                throw new Exception(33333, ResponseError::PARAM_ERROR);
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( $oUserPrivateChatLog->status != 4 ) {
                // 没有在聊天
//                throw new Exception(4444, ResponseError::PARAM_ERROR);
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            // 判断主播
            $nAnchorUserId = $oUserPrivateChatLog->chat_log_anchor_user_id;
            $oAnchor       = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $nAnchorUserId ]
            ]);
            if ( empty($oAnchor) ) {
                throw new Exception(ResponseError::getError(ResponseError::ANCHOR_NOT_EXISTS), ResponseError::ANCHOR_NOT_EXISTS);
            }
            $oAnchorUser = User::findFirst($nAnchorUserId);
            $oUser       = User::findFirst($nUserId);
            // 用户扣费
            $nCoin           = $oChatGameConfig->chat_game_price;
            $nRatio          = $oAnchor->getCoinToDotRatio($oAnchorUser, Anchor::RATIO_CHATGAME);
            $nDot            = sprintf('%.4f', $nCoin * ($nRatio / 100));
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

            $getDot     = sprintf('%.4f', $consumeCoin * ($nRatio / 100));
            $getFreeDot = round($nDot - $getDot, 4);

            $anchorExp     = intval($nDot * intval(Kv::get(Kv::DOT_TO_ANCHOR_EXP)));
            $exp           = $nCoin * intval(Kv::get(Kv::COIN_TO_EXP));
            $intimateValue = $nCoin * intval(Kv::get(Kv::COIN_TO_INTIMATE));
            $userLevel     = User::getUserLevel($oUser->user_exp + $exp);
            $anchorLevel   = LevelConfig::getLevelInfo($oAnchor->anchor_exp + $anchorExp);

            $this->db->begin();
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

            // 添加购买日志
            $aTime                      = explode('.', sprintf('%.10f', microtime(TRUE)));
            $sOrderNumber               = date('YmdHis', $aTime[0]) . '000' . $aTime[1] . mt_rand(10000, 99999);
            $oChatGameLog               = new ChatGameLog();
            $oChatGameLog->chat_game_id = $oChatGameConfig->id;
            $oChatGameLog->user_id      = $nUserId;
            $oChatGameLog->get_user_id  = $nAnchorUserId;
            $oChatGameLog->consume_coin = $nCoin;
            $oChatGameLog->get_dot      = $nDot;
            $oChatGameLog->log_number   = $sOrderNumber;

            if ( $oChatGameLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oChatGameLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            // 记录用户流水
            $oUserFinanceLog                   = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id          = $nUserId;

            $oUserFinanceLog->consume_category_id = UserConsumeCategory::CHAT_GAME_PAY;
            $oUserFinanceLog->consume             = -$nCoin;
            $oUserFinanceLog->remark              = '视频聊天游戏';
            $oUserFinanceLog->flow_id             = $oChatGameLog->id;
            $oUserFinanceLog->flow_number         = $oChatGameLog->log_number;
            $oUserFinanceLog->type                = 2;
            $oUserFinanceLog->group_id            = $oAnchorUser->user_group_id;
            $oUserFinanceLog->target_user_id      = $nAnchorUserId;
            /**
             * 在和主播通话中 进行
             *  因为用户给主播付时间金币的流水在挂断时才统一计算，
             *  那么此时计算 上次余额 需要从流水表查 不能从用户表查
             **/
            $oLastFinanceLog                         = UserFinanceLog::findFirst([
                'user_id = :user_id: AND user_amount_type = :user_amount_type:',
                'bind'  => [
                    'user_id'          => $nUserId,
                    'user_amount_type' => UserFinanceLog::AMOUNT_COIN
                ],
                'order' => 'user_finance_log_id desc'
            ]);
            $oUserFinanceLog->user_current_amount    = $oLastFinanceLog->user_current_amount - $nCoin;
            $oUserFinanceLog->user_last_amount       = $oLastFinanceLog->user_current_amount;
            $oUserFinanceLog->user_current_user_coin = $oLastFinanceLog->user_current_user_coin - $consumeCoin;
            $oUserFinanceLog->user_last_user_coin    = $oLastFinanceLog->user_current_user_coin;
            $oUserFinanceLog->user_current_free_coin = $oLastFinanceLog->user_current_free_coin - $consumeFreeCoin;
            $oUserFinanceLog->user_last_free_coin    = $oLastFinanceLog->user_current_free_coin;
            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            if ( $nDot > 0 ) {
                // 给主播充钱
                $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
                $this->db->execute($sql, [
                    'total_dot'    => $nDot,
                    'get_dot'      => $getDot,
                    'get_free_dot' => $getFreeDot,
                    'user_id'      => $nAnchorUserId,
                ]);
                if ( $this->db->affectedRows() <= 0 ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), '主播收益失败'),
                        ResponseError::OPERATE_FAILED
                    );
                }

                // 给主播加经验(魅力值)
                $anchorSql = 'update anchor set anchor_exp = anchor_exp + :anchor_exp,anchor_level = :anchor_level WHERE user_id = :user_id';
                $this->db->execute($anchorSql, [
                    'anchor_exp'   => $anchorExp,
                    'anchor_level' => $anchorLevel['level'],
                    'user_id'      => $nAnchorUserId,
                ]);

                // 记录主播流水
                $oUserFinanceLog                      = new UserFinanceLog();
                $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
                $oUserFinanceLog->user_id             = $nAnchorUserId;
                $oUserFinanceLog->user_current_amount = $oAnchorUser->user_dot + $nDot;
                $oUserFinanceLog->user_last_amount    = $oAnchorUser->user_dot;
                $oUserFinanceLog->consume_category_id = UserConsumeCategory::CHAT_GAME_INCOME;
                $oUserFinanceLog->consume             = +$nDot;
                $oUserFinanceLog->remark              = '视频聊天游戏';
                $oUserFinanceLog->flow_id             = $oChatGameLog->id;
                $oUserFinanceLog->flow_number         = $oChatGameLog->log_number;
                $oUserFinanceLog->type                = 2;
                $oUserFinanceLog->group_id            = $oAnchorUser->user_group_id;
                $oUserFinanceLog->consume_source      = -$nCoin;
                $oUserFinanceLog->target_user_id      = $nUserId;
                if ( $oUserFinanceLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }

                if ( $oAnchorUser->user_group_id ) {
                    // 有公会的主播  需要给公会长加钱
                    $oGroup = Group::findFirst($oAnchorUser->user_group_id);
                    if ( $oGroup ) {
                        $divid_type    = $oGroup->divid_type;
                        $divid_precent = $oGroup->divid_precent;
                        if ( $divid_type == 0 ) {
                            //主播收益分成
                            $groupMoney = round($nDot * $divid_precent / 100, 2);
                        } else {
                            //主播流水分成  还需要除以一个 充值比例转换值 10
                            $groupMoney = round($nCoin * $divid_precent / 100 / 10, 2);
                        }
                        $sql = 'update `group` set money = money + :money where id = :group_id';
                        $this->db->execute($sql, [
                            'money'    => $groupMoney,
                            'group_id' => $oAnchorUser->user_group_id,
                        ]);
                    }
                }
            }

            $this->db->commit();

            // 给主播发送推送
            $this->timServer->setUid($nAnchorUserId);
            $this->timServer->sendChatGameMsg([
                'chat_log'                => $sChatLogId,
                'game_id'                 => $sGameId,
                'chat_game_content'       => $oChatGameConfig->chat_game_content,
                'chat_game_price'         => $oChatGameConfig->chat_game_price,
                'chat_game_category_name' => $oChatGameCategory->chat_game_category_name,
                'intimate_value'          => $intimateValue,
                'anchor_exp'              => $anchorExp,
                'exp'                     => $exp,
            ]);

            // 主播今日收益 增加
            $oAnchorTodayDotService = new AnchorTodayDotService($oAnchorUser->user_id);
            $oAnchorTodayDotService->save($nDot);

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($oAnchorUser->user_id);
            $oAnchorStatService->save(AnchorStatService::CHAT_GAME_INCOME, $nDot);

            // 开始# 亲密值
            if ( $nCoin ) {
                $intimateMultiple = Kv::get(Kv::COIN_TO_INTIMATE) ?? 1;
                $intimateValue    = $nCoin * $intimateMultiple;
                if ( $intimateValue > 0 ) {
                    $oUserIntimateLog                              = new UserIntimateLog();
                    $oUserIntimateLog->intimate_log_user_id        = $nUserId;
                    $oUserIntimateLog->intimate_log_anchor_user_id = $oAnchorUser->user_id;
                    $oUserIntimateLog->intimate_log_type           = UserIntimateLog::TYPE_VIDEO_CHAT_GAME;
                    $oUserIntimateLog->intimate_log_value          = $nCoin * $intimateMultiple;
                    $oUserIntimateLog->intimate_log_coin           = $nCoin;
                    $oUserIntimateLog->intimate_log_dot            = $nDot;
                    $oUserIntimateLog->save();
                }

                // 活动消费统计
                $oActivityUserService = new ActivityUserService();
                $oActivityUserService->save($nUserId, $nCoin);
            }
            // 结束# 亲密值

            // 记录聊天中 总游戏收益数 总收益数
            $oVideoChatService = new VideoChatService();
            $oVideoChatService->addChatGameData(sprintf("%s:%s", $nAnchorUserId, $nUserId), $nCoin, $nDot);

            $oUser = User::findFirst($nUserId);
            $row   = [
                'user_coin'      => $oUser->user_coin + $oUser->user_free_coin,
                'exp'            => $exp,
                'intimate_value' => $intimateValue,
                'anchor_exp'     => $anchorExp
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/chatgame/update
     * @api {post} /live/chatgame/update 修改自己的游戏
     * @apiName chatgame-update
     * @apiGroup Chatgame
     * @apiDescription 修改自己的游戏
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} game_ids 所选游戏id，以半角逗号分隔
     * @apiParam (正常请求){number} category_id 所选游戏类型id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} game_ids 所选游戏id，以半角逗号分隔
     * @apiParam (debug){number} category_id 所选游戏类型id
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
    public function updateAction($nUserId = 0)
    {
        $sGameIds    = $this->getParams('game_ids');
        $sCategoryId = $this->getParams('category_id', 'int');
        try {
            if ( empty($sGameIds) ) {
                //全部删除
                $deleteSql = "delete from user_chat_game where user_id = {$nUserId} AND exists(select 1 from chat_game_config where chat_game_category_id = {$sCategoryId} AND id = user_chat_game.chat_game_id)";
                $this->db->execute($deleteSql);
                $this->success();
            }

            // 获取所有游戏内容
            $oChatGameConfig    = ChatGameConfig::find([
                'chat_game_category_id = :chat_game_category_id:',
                'bind' => [
                    'chat_game_category_id' => $sCategoryId
                ]
            ])->toArray();
            $oChatGameConfigArr = array_column($oChatGameConfig, 'id');

            $sGameIdArr = explode(',', $sGameIds);
            $saveData   = [];
            $time       = time();
            foreach ( $sGameIdArr as $item ) {
                if ( in_array($item, $oChatGameConfigArr) ) {
                    $saveData[] = [
                        'user_id'      => $nUserId,
                        'chat_game_id' => $item,
                        'create_time'  => $time,
                        'update_time'  => $time
                    ];
                }
            }

            $deleteSql = "delete from user_chat_game where user_id = {$nUserId} AND exists(select 1 from chat_game_config where chat_game_category_id = {$sCategoryId} AND id = user_chat_game.chat_game_id)";
            $this->db->execute($deleteSql);
            if ( $saveData ) {
                $oUserChatGame = new UserChatGame();
                $oUserChatGame->saveAll($saveData);
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/chatgame/invite
     * @api {post} /live/chatgame/invite 主播邀请用户玩游戏
     * @apiName chatganme-invite
     * @apiGroup Chatgame
     * @apiDescription 主播邀请用户玩游戏
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} chat_log 聊天id
     * @apiParam (正常请求){String} game_id 游戏id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} chat_log 聊天id
     * @apiParam (debug){String} game_id 游戏id
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
     *
     *  邀请游戏推送
     *    {
     *        "type": "invite_chat_game",
     *        "msg": "邀请聊天游戏",
     *        "data": {
     *                "game_id": "10",
     *                "chat_log": "4493"
     *                "chat_game_content": "内容"
     *                "chat_game_price": "100"
     *                "chat_game_category_name": "大冒险"
     *        }
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function inviteAction($nUserId = 0)
    {
        $sChatLogId = $this->getParams('chat_log');
        $sGameId    = $this->getParams('game_id');
        try {
            if ( empty($sChatLogId) ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oChatGameConfig = ChatGameConfig::findFirst($sGameId);
            if ( !$oChatGameConfig ) {
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oChatGameCategory   = ChatGameCategory::findFirst($oChatGameConfig->chat_game_category_id);
            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                'id=:id:',
                'bind' => [ 'id' => $sChatLogId ]
            ]);
            if ( $oUserPrivateChatLog->chat_log_anchor_user_id != $nUserId ) {
                // 不是当前主播
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            if ( $oUserPrivateChatLog->status != 4 ) {
                // 没有在聊天
                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            // 给用户发送推送
            $this->timServer->setUid($oUserPrivateChatLog->chat_log_user_id);
            $this->timServer->sendInviteChatGameMsg([
                'chat_log'                => $sChatLogId,
                'game_id'                 => $sGameId,
                'chat_game_content'       => $oChatGameConfig->chat_game_content,
                'chat_game_price'         => $oChatGameConfig->chat_game_price,
                'chat_game_category_name' => $oChatGameCategory->chat_game_category_name,
            ]);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success();
    }

}