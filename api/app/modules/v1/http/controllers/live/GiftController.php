<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 |礼物控制器                                                              |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use app\models\Group;
use app\models\LevelConfig;
use app\models\Room;
use app\models\RoomAdmin;
use app\models\RoomSeat;
use app\models\UserChat;
use app\models\UserChatDialog;
use app\models\UserIntimateLog;
use app\services\ActivityUserService;
use app\services\AnchorStatService;
use app\services\AnchorTodayDotService;
use app\services\IntimateService;
use app\services\VideoChatService;
use Exception;

use app\models\Kv;
use app\models\User;
use app\models\Anchor;
use app\models\LiveGift;
use app\models\UserGiftLog;
use app\helper\ResponseError;
use app\models\UserFinanceLog;
use app\models\UserLevelUpgrade;
use app\models\LiveGiftCategory;
use app\models\UserConsumeCategory;
use app\services\UserRoomGiftService;
use app\services\RoomOnlineUserService;
use app\http\controllers\ControllerBase;

/**
 * GiftController 礼物
 */
class GiftController extends ControllerBase
{
    use \app\services\UserService;
    use \app\services\SystemMessageService;

    /**
     * indexAction 礼物列表
     *
     * @param int $nUserId
     */
    public function indexAction( $nUserId = 0 )
    {

        $nTime = $this->getParams('time', 'int', 0);
        try {
            $aGift = $this->modelsManager
                ->createBuilder()
                ->from([ 'lg' => LiveGift::class ])
                ->join(LiveGiftCategory::class, 'lgc.live_gift_category_id=lg.live_gift_category_id', 'lgc')
                ->columns('lg.live_gift_id id,lg.live_gift_name name,lg.live_gift_detail detail,lg.live_gift_coin coin,lg.live_gift_logo icon,lg.live_gift_small_gif icon_gif,lg.live_gift_gif animation,lg.live_gift_status status,lgc.live_gift_category_name,lgc.live_gift_category_id,lg.live_gift_sort sort')
                ->where('lg.live_gift_update_time>=:time:', [ 'time' => $nTime ])
                ->orderBy('lgc.live_gift_category_id asc,lg.live_gift_sort asc')
                ->getQuery()
                ->execute()
                ->toArray();

            $row['gift'] = [];

            foreach ( $aGift as &$v ) {
                $nCategoryId                         = $v['live_gift_category_id'];
                $row['gift'][ $nCategoryId ]['id']   = $v['live_gift_category_id'];
                $row['gift'][ $nCategoryId ]['name'] = $v['live_gift_category_name'];
                unset($v['live_gift_category_name'], $v['live_gift_category_id']);
                $row['gift'][ $nCategoryId ]['items'][] = $v;
            }

            $row['gift'] = array_values($row['gift']);
            $row['time'] = (string)time();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * sendAction 发送礼物
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/gift/send
     * @api {post} /live/gift/send 赠送礼物
     * @apiName gift-send
     * @apiGroup Public
     * @apiDescription 赠送礼物
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} anchor_user_id 主播用户id
     * @apiParam (正常请求){String} live_gift_id 礼物ID
     * @apiParam (正常请求){String} gift_number 礼物数量
     * @apiParam (正常请求){String='1(私聊中)','2(视频聊天中)'} type 发送类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} anchor_user_id 主播用户id
     * @apiParam (debug){String} live_gift_id 礼物ID
     * @apiParam (debug){String} gift_number 礼物数量
     * @apiParam (debug){String='1(私聊中)','2(视频聊天中)'} type 发送类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {Object} d.user 用户信息
     * @apiSuccess {string} d.user.user_coin 用户金币
     * @apiSuccess {string} d.intimate_value 收获亲密度
     * @apiSuccess {string} d.exp 用户获得经验
     * @apiSuccess {string} d.anchor_exp 主播获得经验（魅力值）
     * @apiSuccess {string} d.dialog_id 文字聊天送礼 返回
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "user" : {
     *                  "user_coin" : "1000",
     *              },
     *              "intimate_value" : 1000,
     *              "exp" : 1000,
     *              "anchor_exp" : 1000,
     *              "dialog_id" : 1000
     *         },
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
    public function sendAction( $nUserId = 0 )
    {
        $nAnchorUserId = $this->getParams('anchor_user_id', 'int', 0);
        $nLiveGiftId   = $this->getParams('live_gift_id', 'int', 0);
        $nGiftNunmer   = $this->getParams('gift_number', 'int', 1);
//        $sUserGiftLogNumber = $this->cookies->get('_ROOM_SEND_GIFT_ID')->getValue();
        $nType   = $this->getParams('type', 'int', 1);//1表示私聊中 2 表示视频通话送礼
        $nRoomId = $this->getParams('room_id', 'string', '');
//        if ( empty($sUserGiftLogNumber) ) {
//
//        }
        $sUserGiftLogNumber = date('YmdHis') . '000000' . mt_rand(10, 99) . mt_rand(100, 999);

        try {

            if ( $sUserGiftLogNumber == '' ) {
                throw new Exception(ResponseError::getError(ResponseError::FAIL), ResponseError::FAIL);
            }

            if ( $nGiftNunmer <= 0 ) {
                throw new Exception(
                    sprintf('%s %s', 'gift_number', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }
            $oUser = User::findFirst($nUserId);

            if ( $oUser->user_is_superadmin == 'S' ) {
                $this->error(10002, '该账号暂不支持此功能哦');
            }

            // 判断礼物
            $oLiveGift = LiveGift::findFirst($nLiveGiftId);
            if ( empty($oLiveGift) ) {
                throw new Exception(ResponseError::getError(ResponseError::GIFT_NOT_EXISTS), ResponseError::GIFT_NOT_EXISTS);
            }

            if ( $oLiveGift->live_gift_type == 2 && $oUser->user_member_expire_time < time() ) {
                // 不是VIP  不能发VIP 礼物
                throw new Exception(ResponseError::getError(ResponseError::NOT_VIP), ResponseError::NOT_VIP);
            }

            // 判断主播
            $oAnchor = Anchor::findFirst([
                'user_id=:user_id:',
                'bind' => [ 'user_id' => $nAnchorUserId ]
            ]);
            if ( empty($oAnchor) ) {
                throw new Exception(ResponseError::getError(ResponseError::ANCHOR_NOT_EXISTS), ResponseError::ANCHOR_NOT_EXISTS);
            }
            $oAnchorUser = User::findFirst($nAnchorUserId);

            $roomSeatUserList = [];
            if($nRoomId){
                // 用来判断座位列表是否有改变
                $oRoomSeatList = RoomSeat::getInfoByRank($nRoomId);
                $roomSeatUserList = array_column($oRoomSeatList,'owner_user_id');
            }

            // 这里的逻辑可以放在队列里
            // Start a transaction
            $this->db->begin();

            // 送礼金币换收益比例
            $nRatio = $oAnchor->getCoinToDotRatio($oAnchorUser, Anchor::RATIO_GIFT);


            // 用户扣费
            $nCoin = $nGiftNunmer * $oLiveGift->live_gift_coin;

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

            $exp       = $nCoin * intval(Kv::get(Kv::COIN_TO_EXP));
            $userLevel = User::getUserLevel($oUser->user_exp + $exp);

            $sql = 'update `yuyin_live`.`user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
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

            $room_seat_number = 100;
            if ( $nRoomId ) {
                $oRoom = Room::findFirst($nRoomId);
                if ( $oRoom->room_host_user_id == $nAnchorUserId ) {
                    $room_seat_number = 0;
                }
            }
            // 添加送礼日志
            $oUserGiftLog                              = new UserGiftLog();
            $oUserGiftLog->user_id                     = $nUserId;
            $oUserGiftLog->user_gift_log_number        = $sUserGiftLogNumber;
            $oUserGiftLog->anchor_user_id              = $nAnchorUserId;
            $oUserGiftLog->live_gift_name              = $oLiveGift->live_gift_name;
            $oUserGiftLog->live_gift_id                = $oLiveGift->live_gift_id;
            $oUserGiftLog->live_gift_coin              = $oLiveGift->live_gift_coin;
            $oUserGiftLog->live_gift_logo              = $oLiveGift->live_gift_logo;
            $oUserGiftLog->live_gift_category_id       = $oLiveGift->live_gift_category_id;
            $oUserGiftLog->user_gift_log_status        = 'Y';
            $oUserGiftLog->live_gift_number            = $nGiftNunmer;
            $oUserGiftLog->live_gift_dot               = $nRatio ? $oUserGiftLog->live_gift_coin * ($nRatio / 100) : $oUserGiftLog->live_gift_coin;
            $oUserGiftLog->live_gift_coin_to_dot_ratio = $nRatio;
            $oUserGiftLog->consume_coin                = $consumeCoin;
            $oUserGiftLog->consume_free_coin           = $consumeFreeCoin;
            $oUserGiftLog->room_id                     = $nRoomId ?? '0';
            $oUserGiftLog->room_seat_number            = $room_seat_number;

            if ( $oUserGiftLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserGiftLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            // 记录用户流水
            $oUserFinanceLog                   = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id          = $nUserId;

            $oUserFinanceLog->consume_category_id = UserConsumeCategory::SEND_GIFT_COIN;
            $oUserFinanceLog->consume             = -$nCoin;
            $oUserFinanceLog->remark              = '赠送礼物';
            $oUserFinanceLog->flow_id             = $oUserGiftLog->user_gift_log_id;
            $oUserFinanceLog->flow_number         = $oUserGiftLog->user_gift_log_number;
            $oUserFinanceLog->type                = $nType;
            $oUserFinanceLog->group_id            = $oAnchorUser->user_group_id;
            $oUserFinanceLog->target_user_id      = $nAnchorUserId;

            if ( $oAnchor->anchor_chat_status == 2 ) {
                /**
                 * 在和主播通话中 进行送礼
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

            } else {
                $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
                $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
                $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
                $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
                $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
                $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            }

            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            $anchorExp = 0;
            if ( $nDot ) {

                $anchorExp = intval($nDot * intval(Kv::get(Kv::DOT_TO_ANCHOR_EXP)));

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
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorUser->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }

                $anchorLevel = LevelConfig::getLevelInfo($oAnchor->anchor_exp + $anchorExp, LevelConfig::LEVEL_TYPE_ANCHOR);
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
                $oUserFinanceLog->consume_category_id = UserConsumeCategory::RECEIVE_GIFT_COIN;
                $oUserFinanceLog->consume             = +$nDot;
                $oUserFinanceLog->remark              = '获取礼物';
                $oUserFinanceLog->flow_id             = $oUserGiftLog->user_gift_log_id;
                $oUserFinanceLog->flow_number         = $oUserGiftLog->user_gift_log_number;
                $oUserFinanceLog->type                = $nType;
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


            $intimateValue = 0;
            if ( $nCoin ) {

                // 开始# 亲密值
                $intimateMultiple = Kv::get(Kv::COIN_TO_INTIMATE) ?? 1;
                $intimateValue    = $nCoin * $intimateMultiple;
                if ( $intimateValue > 0 ) {
                    $oUserIntimateLog                              = new UserIntimateLog();
                    $oUserIntimateLog->intimate_log_user_id        = $nUserId;
                    $oUserIntimateLog->intimate_log_anchor_user_id = $oAnchorUser->user_id;
                    $oUserIntimateLog->intimate_log_type           = $nType == 1 ? UserIntimateLog::TYPE_CHAT_GIFT : UserIntimateLog::TYPE_VIDEO_CHAT_GIFT;
                    $oUserIntimateLog->intimate_log_value          = $nCoin * $intimateMultiple;
                    $oUserIntimateLog->intimate_log_coin           = $nCoin;
                    $oUserIntimateLog->intimate_log_dot            = $nDot;
                    $oUserIntimateLog->save();

                }

                // 结束# 亲密值

                // 用户活动消费榜
                $oActivityUserService = new ActivityUserService();
                $oActivityUserService->save($nUserId, $nCoin);
            }
            if ( $nRoomId ) {
                $oRoom     = $oRoom ?? Room::findFirst($nRoomId);
                $oRoomSeat = NULL;
                if ( $oRoom->room_host_user_id == $nAnchorUserId && $oRoom->room_heart_stat_start > 0 ) {
                    $oRoomSeat                        = RoomSeat::findFirst([
                        'room_seat_room_id = :room_id: AND room_seat_number = 0',
                        'bind' => [
                            'room_id' => $nRoomId,
                        ]
                    ]);
                    $oRoomSeat->room_seat_heart_value += $nCoin;
                    $oRoomSeat->save();
//                    $roomSeatSql = "update yuyin_live.room_seat set room_seat_heart_value = room_seat_heart_value + :room_seat_heart_value,room_seat_heart_change_time = unix_timestamp(now()) where room_seat_room_id = 1 AND room_seat_number = 0";
//                    $this->db->execute($roomSeatSql, [
//                        'room_seat_heart_value' => $nCoin
//                    ]);
//                    if ( $this->db->affectedRows() <= 0 ) {
//                        $this->db->rollback();
//                        throw new Exception(
//                            ResponseError::getError(ResponseError::OPERATE_FAILED),
//                            ResponseError::OPERATE_FAILED);
//                    }
//
//                    RoomSeat::clearRoomSeatCache($nRoomId, $roomSeatArr);
                }
                $hostSeat = RoomSeat::getHostSeat($nRoomId,$oRoom->room_host_user_id, $oRoomSeat);


                // 对比座位是否改变了
                $oRoomSeatList = RoomSeat::getInfoByRank($nRoomId,FALSE);
                $newRoomSeatUserList = array_column($oRoomSeatList,'owner_user_id');

                $pushSeatList = [];

                if(implode(',',$roomSeatUserList) != implode(',',$newRoomSeatUserList)){
                    $pushSeatList = $oRoomSeatList;
                }

                // 房间送礼 推送
                $aPushMessage = [
                    'room_id'          => $nRoomId,
                    'user'             => [
                        'user_id'       => $oUser->user_id,
                        'user_nickname' => $oUser->user_nickname,
                        'user_avatar'   => $oUser->user_avatar,
                        'user_level'    => $oUser->user_level,
                        'user_coin'     => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
                    ],
                    'anchor'           => [
                        'user_id'        => $oAnchorUser->user_id,
                        'user_nickname'  => $oAnchorUser->user_nickname,
                        'user_avatar'    => $oAnchorUser->user_avatar,
                        'user_level'     => $oAnchorUser->user_level,
                        // 总的收益，不是可提现的收益
                        'user_dot'       => sprintf('%.2f', $oAnchorUser->user_collect_total),
                        'anchor_ranking' => $oAnchor->anchor_ranking,
                    ],
                    'live_gift'        => [
                        'live_gift_id'     => $oLiveGift->live_gift_id,
                        'live_gift_name'   => $oLiveGift->live_gift_name,
                        'live_gift_logo'   => $oLiveGift->live_gift_logo,
                        'live_gift_nunmer' => $nGiftNunmer,
                        'live_gift_source' => $oLiveGift->live_gift_source,
                    ],
                    'host_heart_value' => $hostSeat['room_seat_heart_value'],
                    'room_seat'        => $pushSeatList,
                ];
                $this->timServer->setUid();
                $this->timServer->setAccountId();
                $this->timServer->setRid($nRoomId);
                $flg = $this->timServer->sendGiftSignal($aPushMessage);

                $this->db->commit();

            } else {

                if ( $nType == 1 ) {
                    // 聊天中发礼物

                    $user_chat_extra = serialize([
                        'gift_number'    => $nGiftNunmer,
                        'exp'            => $exp,
                        'intimate_value' => $intimateValue,
                        'anchor_exp'     => $anchorExp
                    ]);

                    $oUserChat                           = new UserChat();
                    $oUserChat->user_chat_room_id        = UserChatDialog::getChatRoomId($nUserId, $nAnchorUserId);
                    $oUserChat->user_chat_send_user_id   = $nUserId;
                    $oUserChat->user_chat_receiv_user_id = $nAnchorUserId;
                    $oUserChat->user_chat_content        = $oLiveGift->live_gift_name;
                    $oUserChat->user_chat_source_url     = $oLiveGift->live_gift_logo;
                    $oUserChat->user_chat_extra          = $user_chat_extra;
                    $oUserChat->user_chat_type           = UserChat::TYPE_GIFT;
                    $oUserChat->user_chat_price          = $nCoin;
                    $oUserChat->user_chat_income         = $nDot;
                    $aPushMessage                        = $oUserChat->addMessage($oUser, $oAnchorUser);
                    $dialogId                            = $aPushMessage['dialog']['dialog_id'];

                    $this->timServer->setUid($oAnchorUser->user_id);
                    $this->timServer->setAccountId($nUserId);
                    $this->timServer->sendChatSignal($aPushMessage);
                }

                $this->db->commit();
                $oUser = User::findFirst($nUserId);

                $oAnchor = Anchor::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $oAnchorUser->user_id ]
                ]);
                if ( $nType == 2 ) {
                    // 记录聊天中 总送礼数 总收益数
                    $oVideoChatService = new VideoChatService();
                    $oVideoChatService->addGiftData(sprintf("%s:%s", $nAnchorUserId, $nUserId), $nCoin, $nDot);
                    $aPushMessage = [
                        'user'      => [
                            'user_id'       => $oUser->user_id,
                            'user_nickname' => $oUser->user_nickname,
                            'user_avatar'   => $oUser->user_avatar,
                            'user_level'    => $oUser->user_level,
                            'user_coin'     => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
                        ],
                        'anchor'    => [
                            'user_id'        => $oAnchorUser->user_id,
                            'user_nickname'  => $oAnchorUser->user_nickname,
                            'user_avatar'    => $oAnchorUser->user_avatar,
                            'user_level'     => $oAnchorUser->user_level,
                            // 总的收益，不是可提现的收益
                            'user_dot'       => sprintf('%.2f', $oAnchorUser->user_collect_total),
                            'anchor_ranking' => $oAnchor->anchor_ranking,
                        ],
                        'live_gift' => [
                            'live_gift_id'     => $oLiveGift->live_gift_id,
                            'live_gift_name'   => $oLiveGift->live_gift_name,
                            'live_gift_logo'   => $oLiveGift->live_gift_logo,
                            'live_gift_nunmer' => $nGiftNunmer,
                            'live_gift_source' => $oLiveGift->live_gift_source,
                        ]
                    ];
                    // $this->imServer->setRid($nAnchorUserId);
                    // $this->imServer->setUid('');
                    // $this->imServer->sendGiftSignal($aPushMessage);
//
//                $this->timServer->setRid($nAnchorUserId);
//                $this->timServer->setUid('');
//                $this->timServer->sendGiftSignal($aPushMessage);

                    $this->timServer->setUid([
                        $nAnchorUserId,
                        $nUserId
                    ]);
                    $this->timServer->sendGiftSignalBatch($aPushMessage);
                }
            }
            // 主播今日收益 增加
            $oAnchorTodayDotService = new AnchorTodayDotService($oAnchorUser->user_id);
            $oAnchorTodayDotService->save($nDot);

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($oAnchorUser->user_id);
            $oAnchorStatService->save(AnchorStatService::GIFT_INCOME, $nDot);

            // 赠送特定礼物飘屏
            if ( $oLiveGift->live_gift_notice_flg == 'Y' ) {
                $this->timServer->setUid('');
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                $flg = $this->timServer->sendScrollMsg([
                    'type' => 'notice_gift',
                    'info' => [
                        'user_nickname'        => $oUser->user_nickname,
                        'user_avatar'          => $oUser->user_avatar,
                        'anchor_user_nickname' => $oAnchorUser->user_nickname,
                        'anchor_user_avatar'   => $oAnchorUser->user_avatar,
                        'gift_logo'            => $oLiveGift->live_gift_logo,
                        'gift_name'            => $oLiveGift->live_gift_name,
                        'gift_number'          => $nGiftNunmer,
                        'gift_id'              => $oLiveGift->live_gift_id,
                        'title'                => sprintf('%s 赠送了 %s', $oUser->user_nickname, $oAnchorUser->user_nickname),
                        'content'              => $oLiveGift->live_gift_name,
                    ]
                ]);
            }


        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        } catch ( \PDOException $e ) {
            $this->error($e->getCode(), $e->getMessage());
        } catch ( Exception $e ) {
            // 重新设置用户在房间的发送礼物ID(发送弹幕ID)
            $this->cookies->set(
                '_ROOM_SEND_GIFT_ID',
                date('YmdHis') . '000000' . mt_rand(10, 99) . mt_rand(100, 999),
                time() + 60 * 60 * 24
            );
            $this->error($e->getCode(), $e->getMessage());
        }

        // 重新设置用户在房间的发送礼物ID(发送弹幕ID)
        $this->cookies->set(
            '_ROOM_SEND_GIFT_ID',
            date('YmdHis') . '000000' . mt_rand(10, 99) . mt_rand(100, 999),
            time() + 60 * 60 * 24
        );

        $row = [
            'user'           => [
                'user_coin' => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
            ],
            'intimate_value' => intval($intimateValue),
            'exp'            => $exp,
            'anchor_exp'     => $anchorExp,
            'flg'            => $flg ?? '',
            'dialog_id'      => $dialogId ?? 0
        ];

        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/gift/list
     * @api {get} /live/gift/list 礼物列表
     * @apiName 礼物列表
     * @apiGroup Gift
     * @apiDescription 礼物列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.gift_list 礼物列表
     * @apiSuccess {number} d.gift_list.id  礼物id
     * @apiSuccess {String} d.gift_list.name 礼物名称
     * @apiSuccess {number} d.gift_list.coin 礼物价格
     * @apiSuccess {String} d.gift_list.icon  图标
     * @apiSuccess {number} d.gift_list.vip_coin  VIP价格
     * @apiSuccess {number} d.gift_list.type  0为普通 1为打赏，2为VIP
     * @apiSuccess {number} d.gift_list.live_gift_source  资源地址
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                 "gift_list": [{
     *                     "id": "4",
     *                    "name": "棒棒糖",
     *                    "coin": "30",
     *                    "icon": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180329\/1522286516428231.png",
     *                    "vip_coin": "30",
     *                    "type": "1",
     *                    "live_gift_source": "https://cskj-1257854899.file.myqcloud.com/static/svga/yese.svga"
     *            }, {
     *                     "id": "76",
     *                    "name": "钻戒",
     *                    "coin": "199",
     *                    "icon": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180608\/1528435802176916.gif",
     *                    "vip_coin": "199",
     *                    "type": "2",
     *                    "live_gift_source": ""
     *            }]
     *        },
     *        "t": 1535956013
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function listAction( $nUserId = 0 )
    {
        try {
            $oLiveGift        = LiveGift::find([
                'live_gift_status = 1',
                'columns' => 'live_gift_id as id,live_gift_name as name,live_gift_coin as coin,live_gift_logo as icon,live_gift_coin as vip_coin,live_gift_type as type,live_gift_source',
                'order'   => 'live_gift_sort'
            ]);
            $row['gift_list'] = $oLiveGift;

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }
}