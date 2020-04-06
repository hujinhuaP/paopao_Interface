<?php


namespace app\http\controllers\live;

use app\models\BuyHammerLog;
use app\models\EggGoods;
use app\models\Room;
use app\models\User;
use app\models\UserCashLog;
use app\models\UserConsumeCategory;
use app\models\UserDiamondLog;
use app\models\UserEggDetail;
use app\models\UserEggLog;
use app\models\UserFinanceLog;
use app\models\UserHammer;
use app\models\VipLevel;
use app\services\TaskQueueService;
use Exception;

use app\models\Kv;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
 * EggController 砸蛋
 */
class EggController extends ControllerBase
{
    /**
     * @apiVersion 1.4.0
     * @api {get} live/egg/info 001-191128砸蛋信息
     * @apiName egg-info
     * @apiGroup Egg
     * @apiDescription 191128砸蛋信息
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.user_coin   用户剩余金币
     * @apiSuccess {number} d.hammer_number   用户剩余锤子
     * @apiSuccess {number} d.eggs_hammer_coin   单个锤子价格
     * @apiSuccess {String='Y','N'} d.notice_gift_flg   是否开启大礼物全局通知
     * @apiSuccess {String} d.activity_rule_url  砸蛋玩法介绍地址
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "user_coin": 6270,
     *        "hammer_number": 0,
     *        "eggs_hammer_coin": 200,
     *        "notice_gift_flg": "Y",
     *        "activity_rule_url": "https:\/\/h5.dev.tiantongkeji.cn\/index\/egg"
     *    },
     *    "t": "1565158443"
     *   }
     */
    public function infoAction( $nUserId = 0 )
    {
        try {
            $oUser = User::findFirst($nUserId);

            $oUserHammer = UserHammer::findFirst($nUserId);
            if ( !$oUserHammer ) {
                $oUserHammer                    = new UserHammer();
                $oUserHammer->user_id           = $nUserId;
                $oUserHammer->reward_notice_flg = 'Y';
                $oUserHammer->save();
            }

            $row = [
                'user_coin'         => $oUser->user_coin + $oUser->user_free_coin,
                'hammer_number'     => intval($oUserHammer->user_hammer_number),
                'eggs_hammer_coin'  => intval(Kv::get(Kv::EGGS_HAMMER_COIN)),
                'notice_gift_flg'   => $oUserHammer->reward_notice_flg,
                'activity_rule_url' => sprintf("%s/user/egg", $this->config->application->activity_url)
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * @apiVersion 1.4.0
     * @api {get} live/egg/buyHammer 002-191128购买砸蛋锤子
     * @apiName egg-buyHammer
     * @apiGroup Egg
     * @apiDescription 002-190807购买砸蛋锤子
     * @apiParam (正常请求) {String}  number  购买数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.user_coin 用户剩余金币
     * @apiSuccess {string} d.hammer_number 用户剩余锤子
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "user_coin": 6270,
     *        "hammer_number": 10
     *    },
     *    "t": "1565158443"
     *   }
     */
    public function buyHammerAction( $nUserId = 0 )
    {
        $nNumber = $this->getParams('number');
        try {
            $oUser      = User::findFirst($nUserId);
            $singleCoin = intval(Kv::get(Kv::EGGS_HAMMER_COIN));
            $nCoin      = $nNumber * $singleCoin;
            if ( $oUser->user_coin + $oUser->user_free_coin < $nCoin ) {
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }
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

            $this->db->begin();

            $exp       = $nCoin * intval(Kv::get(Kv::COIN_TO_EXP));
            $userLevel = User::getUserLevel($oUser->user_exp + $exp);

            // 扣除余额
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

            // 添加锤子
            $oUserHammer = UserHammer::findFirst($nUserId);
            if ( !$oUserHammer ) {
                $oUserHammer                             = new UserHammer();
                $oUserHammer->user_id                    = $nUserId;
                $oUserHammer->reward_notice_flg          = 'Y';
                $oUserHammer->user_buy_hammer_first_time = time();

            }
            $oUserHammer->user_hammer_number        += $nNumber;
            $oUserHammer->user_hammer_total_number  += $nNumber;
            $oUserHammer->user_buy_hammer_last_time = time();
            if ( $oUserHammer->save() === FALSE ) {
                throw new Exception(sprintf('%s[%s]-1', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserHammer->getMessage()), ResponseError::OPERATE_FAILED);
            }

            // 添加购买记录
            $oBuyHammerLog                        = new BuyHammerLog();
            $oBuyHammerLog->buy_hammer_user_id    = $nUserId;
            $oBuyHammerLog->buy_hammer_number     = $nNumber;
            $oBuyHammerLog->buy_hammer_total_coin = $nCoin;
            if ( $oBuyHammerLog->save() === FALSE ) {
                throw new Exception(sprintf('%s[%s]-2', ResponseError::getError(ResponseError::OPERATE_FAILED), $oBuyHammerLog->getMessage()), ResponseError::OPERATE_FAILED);
            }


            // 记录用户流水
            $oUserFinanceLog                         = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id                = $nUserId;
            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::EGG_HAMMER;
            $oUserFinanceLog->consume                = -$nCoin;
            $oUserFinanceLog->remark                 = '购买砸蛋锤子';
            $oUserFinanceLog->flow_id                = $oBuyHammerLog->buy_hammer_id;
            $oUserFinanceLog->type                   = 1;
            $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
            $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserFinanceLog->getMessage()),
                    ResponseError::OPERATE_FAILED
                );
            }

            $this->db->commit();

            $oUser = User::findFirst($nUserId);
            $row   = [
                'user_coin'     => $oUser->user_coin + $oUser->user_free_coin,
                'hammer_number' => intval($oUserHammer->user_hammer_number),
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.4.0
     * @api {get} live/egg/updateSetting 003-191128修改设置
     * @apiName egg-updateSetting
     * @apiGroup Egg
     * @apiDescription 003-190808修改设置
     * @apiParam (正常请求) {String='Y(开启)','N(关闭)'} notice_gift_flg  是否开启大礼物全局通知
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.notice_gift_flg 是否开启大礼物全局通知
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "notice_gift_flg": "Y",
     *    },
     *    "t": "1565158443"
     *   }
     */
    public function updateSettingAction( $nUserId = 0 )
    {
        $noticeGiftFlg = $this->getParams('notice_gift_flg');
        try {
            $oUserHammer = UserHammer::findFirst($nUserId);
            if ( !$oUserHammer ) {
                $oUserHammer          = new UserHammer();
                $oUserHammer->user_id = $nUserId;
            }
            $oUserHammer->reward_notice_flg = $noticeGiftFlg == 'Y' ? 'Y' : 'N';
            $oUserHammer->save();
            $row = [
                'notice_gift_flg' => $oUserHammer->reward_notice_flg,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.4.0
     * @api {get} /live/egg/break 004-190822砸蛋
     * @apiName egg-break
     * @apiGroup Egg
     * @apiDescription 004-190822砸蛋
     * @apiParam (正常请求) {String='1','10','100'} number  数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.hammer_number 锤子数
     * @apiSuccess {object[]} d.gift
     * @apiSuccess {number} d.gift.egg_goods_id   砸蛋礼物ID
     * @apiSuccess {String='coin(金币)','diamond(钻石物品)','vip'} d.gift.egg_goods_category   砸蛋物品类型
     * @apiSuccess {number} d.gift.egg_goods_value    相应值
     * @apiSuccess {String} d.gift.egg_goods_name   礼物名称
     * @apiSuccess {String} d.gift.egg_goods_image   礼物展示图片
     * @apiSuccess {String} d.gift.egg_goods_notice_flg   是否全局推送
     * @apiSuccess {number} d.gift.reward_number   奖励获得数量
     * @apiSuccess {object[]} d.noticeSpecialGiftArr    全局推送内容
     * @apiSuccess {number} d.noticeSpecialGiftArr.egg_goods_id
     * @apiSuccess {String} d.noticeSpecialGiftArr.egg_goods_category
     * @apiSuccess {number} d.noticeSpecialGiftArr.egg_goods_value
     * @apiSuccess {String} d.noticeSpecialGiftArr.egg_goods_name
     * @apiSuccess {String} d.noticeSpecialGiftArr.egg_goods_image
     * @apiSuccess {String} d.noticeSpecialGiftArr.egg_goods_notice_flg
     * @apiSuccess {number} d.noticeSpecialGiftArr.reward_number
     * @apiSuccess {number} d.hammer_number 锤子数
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *     "c": 0,
     *     "m": "请求成功",
     *     "d": {
     *       "gift": [{
     *         "egg_goods_id": "1",
     *         "egg_goods_category": "coin",
     *         "egg_goods_value": "10",
     *         "egg_goods_name": "10金币",
     *         "egg_goods_image": "",
     *         "egg_goods_notice_flg": "N",
     *         "reward_number": 5
     *       }, {
     *         "egg_goods_id": "3",
     *         "egg_goods_category": "vip",
     *         "egg_goods_value": "30",
     *         "egg_goods_name": "VIP月卡",
     *         "egg_goods_image": "",
     *         "egg_goods_notice_flg": "Y",
     *         "reward_number": 3
     *       }],
     *       "noticeSpecialGiftArr": [{
     *         "egg_goods_id": "3",
     *         "egg_goods_category": "vip",
     *         "egg_goods_value": "30",
     *         "egg_goods_name": "VIP月卡",
     *         "egg_goods_image": "",
     *         "egg_goods_notice_flg": "Y",
     *         "reward_number": 3
     *       }],
     *       "pushData": {
     *         "room_id": "1",
     *         "gift_list": [{
     *           "egg_goods_id": "3",
     *           "egg_goods_category": "vip",
     *           "egg_goods_value": "30",
     *           "egg_goods_name": "VIP月卡",
     *           "egg_goods_image": "",
     *           "egg_goods_notice_flg": "Y",
     *           "reward_number": 3
     *         }]
     *       },
     *       "flg": {
     *         "ActionStatus": "OK",
     *         "ErrorCode": 0,
     *         "ErrorInfo": "",
     *         "MsgSeq": 3131,
     *         "MsgTime": 1574999027
     *       },
     *       "hammer_number": 90
     *     },
     *     "t": "1574999027"
     *   }
     */
    public function breakAction( $nUserId = 0 )
    {
        $nNumber = $this->getParams('number');
        try {
            $oUser = User::findFirst($nUserId);
            if ( !in_array($nNumber, [
                1,
                10,
                100
            ]) ) {
                throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '数量'), ResponseError::PARAM_ERROR);
            }
            $oUserHammer = UserHammer::findFirst($nUserId);
            if ( $oUserHammer->user_hammer_number < $nNumber ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::EGG_HAMMER_NOT_ENOUGH),
                    ResponseError::EGG_HAMMER_NOT_ENOUGH
                );
            }
            // 统计字段修改
            switch ( $nNumber ) {
                case 1:
                    $oUserHammer->user_break_1_egg_number += 1;
                    $totalTimes                           = $oUserHammer->user_break_1_egg_number;
                    $statColumn[]                         = 'daily_egg_stat_1_egg_number = daily_egg_stat_1_egg_number + 1';
                    $actionName                           = '1锤';
                    break;
                case 10:
                    $oUserHammer->user_break_10_egg_number += 1;
                    $totalTimes                            = $oUserHammer->user_break_10_egg_number;
                    $statColumn[]                          = 'daily_egg_stat_10_egg_number = daily_egg_stat_10_egg_number + 1';
                    $actionName                            = '10连锤';
                    break;
                case 100:
                    $oUserHammer->user_break_100_egg_number += 1;
                    $totalTimes                             = $oUserHammer->user_break_100_egg_number;
                    $statColumn[]                           = 'daily_egg_stat_100_egg_number = daily_egg_stat_100_egg_number + 1';
                    $actionName                             = '100连锤';
                    break;
                default:
                    throw new Exception(sprintf('%s[%s]', ResponseError::getError(ResponseError::PARAM_ERROR), '数量'), ResponseError::PARAM_ERROR);

            }

            $oUserHammer->user_hammer_number -= $nNumber;

            $lastEggLog = UserEggLog::findFirst([
                'user_egg_log_user_id = :user_egg_log_user_id:',
                'bind'  => [
                    'user_egg_log_user_id' => $nUserId
                ],
                'order' => 'user_egg_log_id desc'
            ]);
            if ( !$lastEggLog || $lastEggLog->user_egg_log_create_time < strtotime('today') ) {
                // 没有过记录 或者今天没有记录
                $statColumn[] = 'daily_egg_stat_user_number = daily_egg_stat_user_number + 1';
            }

            $this->db->begin();

            $giftList = EggGoods::getReward($nNumber);


            $oUserEggLog                       = new UserEggLog();
            $oUserEggLog->user_egg_log_user_id = $nUserId;
            $oUserEggLog->user_egg_log_number  = $nNumber;
            $oUserEggLog->user_egg_log_result  = json_encode($giftList);
            $oUserEggLog->user_egg_log_times   = $totalTimes;
            if ( $oUserEggLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]-2', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserEggLog->getMessage()), ResponseError::OPERATE_FAILED);
            }

            // 分配礼物给用户
            $totalCoin      = 0;
            $totalDiamond   = 0;
            $totalVip       = 0;
            $diamondLogSave = [];
            $aPushContent   = [];

            foreach ( $giftList as $item ) {

                $userEggDetailArr[] = [
                    'user_egg_detail_log_id'         => $oUserEggLog->user_egg_log_id,
                    'user_egg_detail_user_id'        => $nUserId,
                    'user_egg_detail_goods_id'       => $item['egg_goods_id'],
                    'user_egg_detail_goods_category' => $item['egg_goods_category'],
                    'user_egg_detail_value'          => $item['egg_goods_value'],
                    'user_egg_detail_name'           => $item['egg_goods_name'],
                    'user_egg_detail_image'          => $item['egg_goods_image'],
                    'user_egg_detail_reward_number'  => $item['reward_number'],
                    'user_egg_detail_notice_flg'     => $item['egg_goods_notice_flg'],
                    'user_egg_detail_create_time'    => time(),
                ];

                switch ( $item['egg_goods_category'] ) {
                    case EggGoods::CATEGORY_COIN:
                        $totalCoin += $item['egg_goods_value'] * $item['reward_number'];
                        break;
                    case EggGoods::CATEGORY_DIAMOND:
                        $itemDiamond         = $item['egg_goods_value'] * $item['reward_number'];
                        $totalDiamond        += $itemDiamond;
                        $oUser->user_diamond += $itemDiamond;
                        $diamondLogSave[]    = [
                            'user_id'             => $nUserId,
                            'consume_category'    => UserDiamondLog::CATEGORY_EGG,
                            'user_current_amount' => $oUser->user_diamond,
                            'user_last_amount'    => $oUser->user_diamond - $itemDiamond,
                            'consume'             => $itemDiamond,
                            'remark'              => sprintf('获得礼物（%s）', $item['egg_goods_name']),
                            'flow_id'             => $oUserEggLog->user_egg_log_id,
                            'create_time'         => time(),
                            'update_time'         => time()
                        ];
                        break;
                    case EggGoods::CATEGORY_VIP:
                        $totalVip += $item['egg_goods_value'] * $item['reward_number'];
                        break;
                }
                if ( $item['egg_goods_notice_flg'] == 'Y' ) {
                    $aPushContent[] = sprintf('%s砸出了 %s x%d', $actionName, $item['egg_goods_name'], $item['reward_number']);
                }
            }

            // 奖励详情记录
            $oUserEggDetail = new UserEggDetail();
            if ( $oUserEggDetail->saveAll($userEggDetailArr) === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]-2', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserEggDetail->getMessage()), ResponseError::OPERATE_FAILED);
            }

            if ( $totalCoin > 0 ) {

                $statColumn[] = 'daily_egg_stat_coin = daily_egg_stat_coin + ' . $totalCoin;

                $oUser->user_free_coin       += $totalCoin;
                $oUser->user_total_free_coin += $totalCoin;
                // 记录用户流水
                $oUserFinanceLog                         = new UserFinanceLog();
                $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
                $oUserFinanceLog->user_id                = $oUser->user_id;
                $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin;
                $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin - $totalCoin;
                $oUserFinanceLog->consume_category_id    = UserConsumeCategory::EGG_REWARD;
                $oUserFinanceLog->consume                = +$totalCoin;
                $oUserFinanceLog->remark                 = '砸蛋奖励' . $totalCoin;
                $oUserFinanceLog->flow_id                = $oUserEggLog->user_egg_log_id;
                $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
                $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
                $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin;
                $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin - $totalCoin;

                if ( $oUserFinanceLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(sprintf('%s[%s]-3', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserFinanceLog->getMessage()), ResponseError::OPERATE_FAILED);
                }
            }
            if ( $totalDiamond > 0 ) {

                $statColumn[] = 'daily_egg_stat_diamond = daily_egg_stat_diamond + ' . $totalDiamond;

                $oUserDiamondLog = new UserDiamondLog();
                if ( $oUserDiamondLog->saveAll($diamondLogSave) === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(sprintf('%s[%s]-2', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserDiamondLog->getMessage()), ResponseError::OPERATE_FAILED);
                }
            }
            if ( $totalVip > 0 ) {

                $statColumn[] = 'daily_egg_stat_vip = daily_egg_stat_vip + ' . $totalVip;

                $oUser->user_member_expire_time = $oUser->user_member_expire_time > time() ? $oUser->user_member_expire_time + $totalVip * 86400 : time() + $totalVip * 86400;
                $oUser->user_vip_exp            += $totalVip;
                $oUser->user_vip_level          = VipLevel::getLevelInfo($oUser->user_vip_exp)['level'];
            }

            if ( $oUser->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(sprintf('%s[%s]-2', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUser->getMessage()), ResponseError::OPERATE_FAILED);
            }

            $oUserHammer->reward_diamond_total += $totalDiamond;
            $oUserHammer->reward_coin          += $totalCoin;
            $oUserHammer->reward_vip_day       += $totalVip;

            if ( $oUserHammer->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]-1', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserHammer->getMessage()),
                    ResponseError::OPERATE_FAILED
                );
            }
            // 砸蛋统计
            $sColumnStr = implode(',', $statColumn);
            $statSql    = sprintf("update `yuyin_live`.daily_egg_stat set %s where daily_egg_stat_time = :daily_egg_stat_time", $sColumnStr);
            $this->db->execute($statSql, [
                'daily_egg_stat_time' => strtotime('today'),
            ]);
            if ( $this->db->affectedRows() <= 0 ) {
                $this->log->info('砸蛋统计失败');
            }

            $this->db->commit();
            $flg = '';
            if ( $aPushContent ) {
                $this->timServer->setUid();
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                $flg = $this->timServer->sendScrollMsg([
                    'type' => 'egg',
                    'info' => [
                        'user_nickname' => $oUser->user_nickname,
                        'user_avatar'   => $oUser->user_avatar,
                        'title'         => $oUser->user_nickname,
                        'content_list'  => $aPushContent
                    ]
                ]);


            }


            $row = [
                'gift'          => $giftList,
                'flg'           => $flg,
                'hammer_number' => intval($oUserHammer->user_hammer_number),
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }


    /**
     * @apiVersion 1.2.0
     * @api {get} /live/egg/myHistory 005-190823我的中奖纪录
     * @apiName egg-myHistory
     * @apiGroup Egg
     * @apiDescription 005-190823我的中奖纪录
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.list
     * @apiSuccess {number} d.list.number 砸蛋次数
     * @apiSuccess {number} d.list.create_time  砸蛋时间
     * @apiSuccess {object} d.list.gift_list  礼物信息
     * @apiSuccess {number} d.list.gift_list.egg_goods_id   砸蛋礼物ID
     * @apiSuccess {String='coin(金币)','diamond(钻石物品)','vip'} d.gift.egg_goods_category   砸蛋物品类型
     * @apiSuccess {number} d.list.gift_list.egg_goods_value    相应值
     * @apiSuccess {String} d.list.gift_list.egg_goods_name   礼物名称
     * @apiSuccess {String} d.list.gift_list.egg_goods_image   礼物展示图片
     * @apiSuccess {String} d.list.gift_list.egg_goods_notice_flg   是否全局推送
     * @apiSuccess {number} d.list.gift_list.reward_number   奖励获得数量
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "list": [{
     *            "number": "100",
     *            "create_time": "1566463622",
     *            "gift_list": [{
     *                  "egg_goods_id": "3",
     *                  "egg_goods_category": "vip",
     *                  "egg_goods_value": "30",
     *                  "egg_goods_name": "VIP月卡",
     *                  "egg_goods_image": "",
     *                  "egg_goods_notice_flg": "Y",
     *                  "reward_number": 3
     *            }]
     *        }]
     *    },
     *    "t": "1566524783"
     *   }
     */
    public function myHistoryAction( $nUserId = 0 )
    {
        try {
            $row['list'] = $this->modelsManager->createBuilder()
                ->from(UserEggLog::class)
                ->where('user_egg_log_user_id = :user_egg_log_user_id:', [
                    'user_egg_log_user_id' => $nUserId,
                ])
                ->columns('user_egg_log_number as number,user_egg_log_result,user_egg_log_create_time as create_time')
                ->orderBy('user_egg_log_id desc')
                ->limit(100)
                ->getQuery()->execute()->toArray();
            foreach ( $row['list'] as &$item ) {
                $item['gift_list'] = json_decode($item['user_egg_log_result']);
                unset($item['user_egg_log_result']);
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.4.0
     * @api {get} /live/egg/rewardLog 006-190823平台中奖纪录
     * @apiName egg-rewardLog
     * @apiGroup Egg
     * @apiDescription 006-190823平台中奖纪录
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.egg_gift_log  砸蛋礼物记录
     * @apiSuccess {number} d.egg_gift_log.user_id    用户ID
     * @apiSuccess {String} d.egg_gift_log.user_nickname  用户昵称
     * @apiSuccess {String} d.egg_gift_log.user_avatar  用户头像
     * @apiSuccess {String} d.egg_gift_log.gift_name  礼物名称
     * @apiSuccess {number} d.egg_gift_log.gift_number  礼物数量
     * @apiSuccess {number} d.egg_gift_log.gift_id   礼物ID
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "egg_gift_log": [{
     *            "user_id": "17",
     *            "user_nickname": "哎哟不错👍👍",
     *            "user_avatar": "https:\/\/tiantong-1259630769.image.myqcloud.com\/image\/2019\/08\/14\/451081dd24.jpg",
     *            "gift_name": "巧克力",
     *            "gift_number": "1",
     *            "gift_id": "112"
     *        }]
     *    },
     *    "t": "1566527032"
     *   }
     */
    public function rewardLogAction( $nUserId = 0 )
    {
        try {
            // 平台用户中奖纪录100条
            $eggGiftLog          = $this->modelsManager->createBuilder()
                ->from([ 'l' => UserEggDetail::class ])
                ->join(User::class, 'l.user_egg_detail_user_id = u.user_id', 'u')
                ->columns('u.user_id,u.user_nickname,u.user_avatar,l.user_egg_detail_name as gift_name,
                l.user_egg_detail_reward_number as gift_number,l.user_egg_detail_goods_id as gift_id')
                ->limit(100)
                ->getQuery()
                ->cache([
                    'lifetime' => 60,
                    'key'      => 'egg_reward_log'
                ])
                ->execute()->toArray();
            $row['egg_gift_log'] = $eggGiftLog;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


}