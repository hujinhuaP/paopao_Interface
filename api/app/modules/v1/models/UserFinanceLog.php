<?php

namespace app\models;

use app\helper\ResponseError;
use app\services\ActivityUserService;
use app\services\AnchorStatService;
use app\services\AnchorTodayDotService;
use app\services\IntimateService;
use app\services\UserTodayCashService;

/**
 * UserFinanceLog 用户流水表
 */
class UserFinanceLog extends ModelBase
{
    use \app\services\SystemMessageService;
    /** @var string 金币 */
    const AMOUNT_COIN = 'coin';
    /** @var string 收益 */
    const AMOUNT_DOT = 'dot';
    /** @var string 现金 */
    const AMOUNT_MONEY = 'money';


    public function beforeCreate()
    {
        $this->update_time = time();
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

    /**
     * 添加邀请奖励
     * oUser 为产生动作的用户 方法给oUser的上级用户加钱
     */
    public static function addInviteReward(\app\models\User $oUser, $invite_reward, $type = 'cash')
    {
        if ( $oUser->user_invite_user_id == 0 ) {
            return TRUE;
        }
        $oInviteUser = User::findFirst($oUser->user_invite_user_id);
        if ( $oInviteUser->user_is_anchor == 'Y' ) {
            // 主播没有邀请注册奖励
            return TRUE;
        }
        $oUserInviteRewardLog = new UserInviteRewardLog();
        $connection           = $oUserInviteRewardLog->getWriteConnection();
        $connection->begin();
        // 记录邀请奖励记录表
        if ( $type == 'cash' ) {
            $oUserInviteRewardLog->recharge_invite_cash = $invite_reward;
        } else if ( $type == 'coin' ) {
            $oUserInviteRewardLog->recharge_invite_coin = $invite_reward;
        }
        $oUserInviteRewardLog->user_invite_reward_type  = UserInviteRewardLog::TYPE_REGISTER;
        $oUserInviteRewardLog->user_id                  = $oUser->user_id;
        $oUserInviteRewardLog->parent_user_id           = $oUser->user_invite_user_id;
        $oUserInviteRewardLog->invite_level             = 1;
        $oUserInviteRewardLog->user_recharge_combo_fee  = 0;
        $oUserInviteRewardLog->user_recharge_combo_coin = 0;
        $oUserInviteRewardLog->invite_ratio             = 0;
        $flg                                            = $oUserInviteRewardLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return FALSE;
        }
        if ( $type == 'cash' ) {
            // 记录“现金”流水
            $oUserCashLog                      = new UserCashLog();
            $oUserCashLog->user_id             = $oUser->user_invite_user_id;
            $oUserCashLog->consume_category    = UserCashLog::CATEGORY_REGISTER;
            $oUserCashLog->user_current_amount = $oUser->user_cash;
            $oUserCashLog->user_last_amount    = $oUser->user_cash + $invite_reward;
            $oUserCashLog->consume             = $invite_reward;
            $oUserCashLog->remark              = '邀请注册奖励';
            $oUserCashLog->flow_id             = $oUserInviteRewardLog->user_invite_reward_log_id;
            $oUserCashLog->flow_number         = '';
            $oUserCashLog->target_user_id      = $oUser->user_id;
            $flg                               = $oUserCashLog->save();
            if ( $flg == FALSE ) {
                $connection->rollback();
                return FALSE;
            }

            $sql = 'update `user` set user_cash = user_cash + ' . $invite_reward . ',total_user_cash = total_user_cash + ' .
                $invite_reward . ' where user_id = ' . $oUser->user_invite_user_id;
            $connection->execute($sql);
            if ( $connection->affectedRows() <= 0 ) {
                $connection->rollback();
                return FALSE;
            }
            // 记录该用户今日“现金”收益
            $oUserTodayCashService = new UserTodayCashService($oUser->user_invite_user_id);
            $oUserTodayCashService->save($invite_reward);
        } else if ( $type == 'coin' ) {
            // 记录用户流水
            $oUserFinanceLog                         = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id                = $oUser->user_invite_user_id;
            $oUserFinanceLog->user_current_amount    = $oInviteUser->user_coin + $oInviteUser->user_free_coin;
            $oUserFinanceLog->user_last_amount       = $oUserFinanceLog->user_current_amount + $invite_reward;
            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::INVITE_REGISTER_REWARD;
            $oUserFinanceLog->consume                = +$invite_reward;
            $oUserFinanceLog->remark                 = '邀请注册奖励';
            $oUserFinanceLog->flow_id                = $oUserInviteRewardLog->user_invite_reward_log_id;
            $oUserFinanceLog->flow_number            = '';
            $oUserFinanceLog->type                   = 0;
            $oUserFinanceLog->target_user_id         = $oUser->user_id;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin + $invite_reward;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            $flg                                     = $oUserFinanceLog->save();
            if ( $flg == FALSE ) {
                $connection->rollback();
                return FALSE;
            }

            $sql = 'update `user` set user_free_coin = user_free_coin + ' . $invite_reward . ',user_total_free_coin = user_total_free_coin + ' .
                $invite_reward . ',user_invite_coin_total = user_invite_coin_total + ' . $invite_reward . ' where user_id = ' . $oUser->user_invite_user_id;
            $connection->execute($sql);
            if ( $connection->affectedRows() <= 0 ) {
                $connection->rollback();
                return FALSE;
            }
        }

        $connection->commit();
        return TRUE;

    }

    /**
     * 添加主播邀请充值奖励
     * oUser 为产生动作的用户 方法给oUser的上级用户加钱
     */
    public function addAnchorInviteRecharge(\app\models\User $oUser, \app\models\UserRechargeOrder $oUserRechargeOrder, $invite_recharge_radio)
    {

        if ( $oUser->user_invite_user_id == 0 ) {
            return TRUE;
        }
        $oInviteUser = User::findFirst($oUser->user_invite_user_id);
        if ( $oInviteUser->user_is_anchor != 'Y' ) {
            return FALSE;
        }

        $user_recharge_order_fee = $oUserRechargeOrder->user_recharge_order_fee;
        $reward_dot              = round($user_recharge_order_fee * $invite_recharge_radio / 100, 2);

        $oUserInviteRewardLog = new UserInviteRewardLog();
        $connection           = $oUserInviteRewardLog->getWriteConnection();
        $connection->begin();
        // 记录邀请奖励记录表
        $oUserInviteRewardLog->user_invite_reward_type  = UserInviteRewardLog::TYPE_RECHARGE;
        $oUserInviteRewardLog->user_id                  = $oUser->user_id;
        $oUserInviteRewardLog->parent_user_id           = $oUser->user_invite_user_id;
        $oUserInviteRewardLog->invite_level             = 1;
        $oUserInviteRewardLog->recharge_invite_coin     = 0;
        $oUserInviteRewardLog->recharge_invite_dot      = $reward_dot;
        $oUserInviteRewardLog->user_recharge_combo_fee  = $oUserRechargeOrder->user_recharge_order_fee;
        $oUserInviteRewardLog->user_recharge_combo_coin = $oUserRechargeOrder->user_recharge_combo_coin;
        $oUserInviteRewardLog->invite_ratio             = $invite_recharge_radio;
        $flg                                            = $oUserInviteRewardLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return FALSE;
        }

        // 记录用户流水
        $oUserFinanceLog                      = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
        $oUserFinanceLog->user_id             = $oUser->user_invite_user_id;
        $oUserFinanceLog->user_current_amount = $oInviteUser->user_dot;
        $oUserFinanceLog->user_last_amount    = $oUserFinanceLog->user_current_amount + $reward_dot;
        $oUserFinanceLog->consume_category_id = UserConsumeCategory::INVITE_USER_DOT;
        $oUserFinanceLog->consume             = +$reward_dot;
        $oUserFinanceLog->consume_source      = $user_recharge_order_fee;
        $oUserFinanceLog->remark              = '邀请充值奖励';
        $oUserFinanceLog->flow_id             = $oUserInviteRewardLog->user_invite_reward_log_id;
        $oUserFinanceLog->flow_number         = '';
        $oUserFinanceLog->type                = 0;
        $oUserFinanceLog->target_user_id      = $oUser->user_id;
        $oUserFinanceLog->group_id            = $oInviteUser->user_group_id;
        $flg                                  = $oUserFinanceLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return FALSE;
        }
        $sql = 'update `user` set user_dot = user_dot + ' . $reward_dot . ',user_invite_dot_total = user_invite_dot_total + ' .
            $reward_dot . ' where user_id = ' . $oUser->user_invite_user_id;
        $connection->execute($sql);
        if ( $connection->affectedRows() <= 0 ) {
            $connection->rollback();
            return FALSE;
        }
        $connection->commit();

        // 主播今日收益 增加
        $oAnchorTodayDotService = new AnchorTodayDotService($oUser->user_invite_user_id);
        $oAnchorTodayDotService->save($reward_dot);

        // 主播每日统计
        $oAnchorStatService = new AnchorStatService($oUser->user_invite_user_id);
        $oAnchorStatService->save(AnchorStatService::INVITE_RECHARGE_INCOME,$reward_dot);


        $this->sendRewardMsg($oUser, $oUser->user_invite_user_id, $user_recharge_order_fee, $reward_dot, Kv::get(Kv::KEY_DOT_NAME));
        return TRUE;


    }


    /**
     * 添加邀请充值奖励
     * oUser 为产生动作的用户 方法给oUser的上级用户加钱
     */
    public function addInviteRecharge(\app\models\User $oUser, \app\models\UserRechargeOrder $oUserRechargeOrder, $invite_recharge_radio, $type = 'cash')
    {
        if ( $oUser->user_invite_user_id == 0 ) {
            return TRUE;
        }
        $oInviteUser = User::findFirst($oUser->user_invite_user_id);

        if ( $oInviteUser->user_is_anchor == 'Y' ) {
            // 执行主播邀请规则
            return TRUE;
        }
        if ( $type == 'cash' ) {
            $rewardName = '现金';

            $user_recharge_order_fee = $oUserRechargeOrder->user_recharge_order_fee;
            $reward_cash             = round($user_recharge_order_fee * $invite_recharge_radio / 100, 2);
            if($reward_cash <= 0 ){
                return TRUE;
            }

            $oUserInviteRewardLog = new UserInviteRewardLog();
            $connection           = $oUserInviteRewardLog->getWriteConnection();
            $connection->begin();
            // 记录邀请奖励记录表
            $oUserInviteRewardLog->user_invite_reward_type  = UserInviteRewardLog::TYPE_RECHARGE;
            $oUserInviteRewardLog->user_id                  = $oUser->user_id;
            $oUserInviteRewardLog->parent_user_id           = $oUser->user_invite_user_id;
            $oUserInviteRewardLog->invite_level             = 1;
            $oUserInviteRewardLog->recharge_invite_cash     = $reward_cash;
            $oUserInviteRewardLog->user_recharge_combo_fee  = $oUserRechargeOrder->user_recharge_order_fee;
            $oUserInviteRewardLog->user_recharge_combo_coin = $oUserRechargeOrder->user_recharge_combo_coin;
            $oUserInviteRewardLog->invite_ratio             = $invite_recharge_radio;
            $flg                                            = $oUserInviteRewardLog->save();
            if ( $flg == FALSE ) {
                $connection->rollback();
                return FALSE;
            }
            // 记录“现金”流水
            $oUserCashLog                   = new UserCashLog();
            $oUserCashLog->user_id          = $oUser->user_invite_user_id;
            $oUserCashLog->consume_category = UserCashLog::CATEGORY_RECHARGE;;
            $oUserCashLog->user_current_amount = $oUser->user_cash;
            $oUserCashLog->user_last_amount    = $oUser->user_cash + $reward_cash;
            $oUserCashLog->consume             = $reward_cash;
            $oUserCashLog->remark              = '邀请充值奖励';
            $oUserCashLog->flow_id             = $oUserInviteRewardLog->user_invite_reward_log_id;
            $oUserCashLog->flow_number         = '';
            $oUserCashLog->target_user_id      = $oUser->user_id;
            $flg                               = $oUserCashLog->save();
            if ( $flg == FALSE ) {
                $connection->rollback();
                return FALSE;
            }

            $sql = 'update `user` set user_cash = user_cash + ' . $reward_cash . ',total_user_cash = total_user_cash + ' .
                $reward_cash . ' where user_id = ' . $oUser->user_invite_user_id;
            $connection->execute($sql);
            if ( $connection->affectedRows() <= 0 ) {
                $connection->rollback();
                return FALSE;
            }
            $connection->commit();
            $reward = $reward_cash;
            // 记录该用户今日“现金”收益
            $oUserTodayCashService = new UserTodayCashService($oUser->user_invite_user_id);
            $oUserTodayCashService->save($reward);
        } else if ( $type == 'coin' ) {

            $rewardName               = Kv::get(Kv::KEY_COIN_NAME);
            $user_recharge_combo_coin = $oUserRechargeOrder->user_recharge_combo_coin;
            $reward_coin              = round($user_recharge_combo_coin * $invite_recharge_radio / 100, 2);

            $oUserInviteRewardLog = new UserInviteRewardLog();
            $connection           = $oUserInviteRewardLog->getWriteConnection();
            $connection->begin();
            // 记录邀请奖励记录表
            $oUserInviteRewardLog->user_invite_reward_type  = UserInviteRewardLog::TYPE_RECHARGE;
            $oUserInviteRewardLog->user_id                  = $oUser->user_id;
            $oUserInviteRewardLog->parent_user_id           = $oUser->user_invite_user_id;
            $oUserInviteRewardLog->invite_level             = 1;
            $oUserInviteRewardLog->recharge_invite_coin     = $reward_coin;
            $oUserInviteRewardLog->user_recharge_combo_fee  = $oUserRechargeOrder->user_recharge_order_fee;
            $oUserInviteRewardLog->user_recharge_combo_coin = $user_recharge_combo_coin;
            $oUserInviteRewardLog->invite_ratio             = $invite_recharge_radio;
            $flg                                            = $oUserInviteRewardLog->save();
            if ( $flg == FALSE ) {
                $connection->rollback();
                return FALSE;
            }

            // 记录用户流水
            $oUserFinanceLog                         = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id                = $oUser->user_invite_user_id;
            $oUserFinanceLog->user_current_amount    = $oInviteUser->user_coin + $oInviteUser->user_free_coin;
            $oUserFinanceLog->user_last_amount       = $oUserFinanceLog->user_current_amount + $reward_coin;
            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::INVITE_USER_COIN;
            $oUserFinanceLog->consume                = +$reward_coin;
            $oUserFinanceLog->remark                 = '邀请充值奖励';
            $oUserFinanceLog->flow_id                = $oUserInviteRewardLog->user_invite_reward_log_id;
            $oUserFinanceLog->flow_number            = '';
            $oUserFinanceLog->type                   = 0;
            $oUserFinanceLog->target_user_id         = $oUser->user_id;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin + $reward_coin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            $flg                                     = $oUserFinanceLog->save();
            if ( $flg == FALSE ) {
                $connection->rollback();
                return FALSE;
            }
            $sql = 'update `user` set user_free_coin = user_free_coin + ' . $reward_coin . ',user_total_free_coin = user_total_free_coin + ' .
                $reward_coin . ',user_invite_coin_total = user_invite_coin_total + ' . $reward_coin . ' where user_id = ' . $oUser->user_invite_user_id;
            $connection->execute($sql);
            if ( $connection->affectedRows() <= 0 ) {
                $connection->rollback();
                return FALSE;
            }
            $connection->commit();
            $reward = $reward_coin;
        } else {
            return TRUE;
        }
        $this->sendRewardMsg($oUser, $oUser->user_invite_user_id, $oUserRechargeOrder->user_recharge_order_fee, $reward, $rewardName);
        return TRUE;

    }

    /**
     * 注册奖励
     */
    public static function addRegisterReward(\app\models\User $oUser, $register_reward_coin)
    {
        $oUserRegisterRewardLog = new UserRegisterRewardLog();
        $connection             = $oUserRegisterRewardLog->getWriteConnection();
        $connection->begin();
        // 记录邀请奖励记录表
        $oUserRegisterRewardLog->user_id     = $oUser->user_id;
        $oUserRegisterRewardLog->reward_coin = $register_reward_coin;
        $flg                                 = $oUserRegisterRewardLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
        }
        // 记录用户流水
        $oUserFinanceLog                         = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
        $oUserFinanceLog->user_id                = $oUser->user_id;
        $oUserFinanceLog->user_current_amount    = $register_reward_coin;
        $oUserFinanceLog->user_last_amount       = 0;
        $oUserFinanceLog->consume_category_id    = UserConsumeCategory::REGISTER_REWARD;
        $oUserFinanceLog->consume                = +$register_reward_coin;
        $oUserFinanceLog->remark                 = '注册奖励';
        $oUserFinanceLog->flow_id                = $oUserRegisterRewardLog->user_register_reward_log_id;
        $oUserFinanceLog->flow_number            = '';
        $oUserFinanceLog->type                   = 0;
        $oUserFinanceLog->user_current_user_coin = 0;
        $oUserFinanceLog->user_last_user_coin    = 0;
        $oUserFinanceLog->user_current_free_coin = $register_reward_coin;
        $oUserFinanceLog->user_last_free_coin    = 0;
        $flg                                     = $oUserFinanceLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return FALSE;
        }
        $sql = 'update `user` set user_free_coin = user_free_coin + ' . $register_reward_coin . ',user_total_free_coin = user_total_free_coin + ' . $register_reward_coin . ' where user_id = ' . $oUser->user_id;
        $connection->execute($sql);
        if ( $connection->affectedRows() <= 0 ) {
            $connection->rollback();
            return FALSE;
        }
        $connection->commit();
        return TRUE;

    }


    /**
     * 私密视频支付
     */
    public static function addVideoPay(\app\models\UserVideo $oVideo, $user_id)
    {
        $oUserVideoPay = new UserVideoPay();
        $connection    = $oUserVideoPay->getWriteConnection();
        $oUser         = User::findFirst($user_id);
        $nCoin         = $oVideo->watch_price;
        $payType       = UserVideoPay::PAY_TYPE_NORMAL;
        if ( $oUser->user_member_expire_time > time() ) {
            $nCoin   = round($oVideo->watch_price / 2, 2);
            $payType = UserVideoPay::PAY_TYPE_VIP;
        }
        $connection->begin();
        // 操作用户的数据
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
        //扣费
        $exp = $nCoin * intval(Kv::get(Kv::COIN_TO_EXP));
        $userLevel = User::getUserLevel($oUser->user_exp + $exp);

        $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin,user_exp = user_exp + :exp,user_level = :user_level
where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin';
        $connection->execute($sql, [
            'consume_free_coin' => $consumeFreeCoin,
            'consume_coin'      => $consumeCoin,
            'user_id'           => $user_id,
            'exp'               => $exp,
            'user_level'        => $userLevel
        ]);
        if ( $connection->affectedRows() <= 0 ) {
            // 赠送币 不够钱
            $connection->rollback();
            return [
                'c' => ResponseError::USER_COIN_NOT_ENOUGH,
                'm' => '余额不足'
            ];

        }
        // 记录支付表
        $oUserVideoPay->user_id   = $oUser->user_id;
        $oUserVideoPay->video_id  = $oVideo->id;
        $oUserVideoPay->pay_price = $nCoin;
        $oUserVideoPay->pay_type  = $payType;
        $flg                      = $oUserVideoPay->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => implode(',', $oUserVideoPay->getMessages())
            ];
        }
        // 记录用户流水
        $oUserFinanceLog                         = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
        $oUserFinanceLog->user_id                = $oUser->user_id;
        $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
        $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
        $oUserFinanceLog->consume_category_id    = UserConsumeCategory::VIDEO_PAY;
        $oUserFinanceLog->consume                = -$nCoin;
        $oUserFinanceLog->remark                 = '私密视频付费';
        $oUserFinanceLog->flow_id                = $oUserVideoPay->id;
        $oUserFinanceLog->flow_number            = '';
        $oUserFinanceLog->type                   = 0;
        $oUserFinanceLog->target_user_id         = $oVideo->user_id;
        $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
        $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
        $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
        $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
        $flg                                     = $oUserFinanceLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => implode(',', $oUserFinanceLog->getMessages())
            ];
        }

        // 主播获得收益
        $oAnchor     = Anchor::findFirst([
            'user_id=:user_id:',
            'bind' => [ 'user_id' => $oVideo->user_id ]
        ]);
        $oAnchorUser = User::findFirst($oVideo->user_id);
        $nRatio      = $oAnchor->getCoinToDotRatio($oAnchorUser, Anchor::RATIO_VIDEO);
        $nDot        = sprintf('%.4f', $nCoin * ($nRatio / 100));
        $getDot      = sprintf('%.4f', $consumeCoin * ($nRatio / 100));
        $getFreeDot  = round($nDot - $getDot, 4);

        $anchorExp     = intval($nDot * intval(Kv::get(Kv::DOT_TO_ANCHOR_EXP)));
        $anchorLevel   = LevelConfig::getLevelInfo($oAnchor->anchor_exp + $anchorExp);
        // 给主播充钱
        $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
        $connection->execute($sql, [
            'total_dot'    => $nDot,
            'get_dot'      => $getDot,
            'get_free_dot' => $getFreeDot,
            'user_id'      => $oVideo->user_id,
        ]);
        if ( $connection->affectedRows() <= 0 ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => '主播增加收益失败'
            ];
        }
        // 给主播加经验(魅力值)
        $anchorSql = 'update anchor set anchor_exp = anchor_exp + :anchor_exp,anchor_level = :anchor_level WHERE user_id = :user_id';
        $connection->execute($anchorSql, [
            'anchor_exp'   => $anchorExp,
            'anchor_level' => $anchorLevel['level'],
            'user_id'      => $oVideo->user_id,
        ]);

        // 记录主播流水
        $oUserFinanceLog                      = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
        $oUserFinanceLog->user_id             = $oVideo->user_id;
        $oUserFinanceLog->user_current_amount = $oAnchorUser->user_dot + $nDot;
        $oUserFinanceLog->user_last_amount    = $oAnchorUser->user_dot;
        $oUserFinanceLog->consume_category_id = UserConsumeCategory::VIDEO_PAY;
        $oUserFinanceLog->consume             = +$nDot;
        $oUserFinanceLog->remark              = '私密视频收益';
        $oUserFinanceLog->flow_id             = $oUserVideoPay->id;
        $oUserFinanceLog->type                = 0;
        $oUserFinanceLog->group_id            = $oAnchorUser->user_group_id;
        $oUserFinanceLog->consume_source      = -$nCoin;
        $oUserFinanceLog->target_user_id      = $user_id;
        if ( $oUserFinanceLog->save() === FALSE ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => implode(',', $oUserFinanceLog->getMessages())
            ];
        }

        //视频的付费次数加1  收入加
        $sql = 'update `user_video` set total_pay_times = total_pay_times + 1,total_income = total_income + :total_dot where id = :video_id';
        $connection->execute($sql, [
            'total_dot' => $nDot,
            'video_id'  => $oVideo->id,
        ]);
        if ( $connection->affectedRows() <= 0 ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => '视频收费记录失败'
            ];
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
                $connection->execute($sql, [
                    'money'    => $groupMoney,
                    'group_id' => $oAnchorUser->user_group_id,
                ]);
            }
        }

        // 主播今日收益 增加
        $oAnchorTodayDotService = new AnchorTodayDotService($oAnchorUser->user_id);
        $oAnchorTodayDotService->save($nDot);

        // 主播每日统计
        $oAnchorStatService = new AnchorStatService($oAnchorUser->user_id);
        $oAnchorStatService->save(AnchorStatService::VIDEO_INCOME,$nDot);
        $connection->commit();
        return TRUE;

    }


    /**
     * 发送付费私聊
     */
    public static function addSendChat(\app\models\User $oUser, $price, \app\models\User $oToUser, $nRatio = null)
    {
        $userFinanceLog = new UserFinanceLog();
        $connection     = $userFinanceLog->getWriteConnection();
        $nCoin          = $price;
        $connection->begin();
        // 操作用户的数据
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
        //扣费
        $exp = $nCoin * intval(Kv::get(Kv::COIN_TO_EXP));
        $userLevel = User::getUserLevel($oUser->user_exp + $exp);
        $oAnchor       = Anchor::findFirst([
            'user_id=:user_id:',
            'bind' => [ 'user_id' => $oToUser->user_id ]
        ]);


        $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin,user_exp = user_exp + :exp,user_level = :user_level
where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin';
        $connection->execute($sql, [
            'consume_free_coin' => $consumeFreeCoin,
            'consume_coin'      => $consumeCoin,
            'user_id'           => $oUser->user_id,
            'exp'               => $exp,
            'user_level'        => $userLevel
        ]);
        if ( $connection->affectedRows() <= 0 ) {
            // 赠送币 不够钱
            $connection->rollback();
            return [
                'c' => ResponseError::USER_COIN_NOT_ENOUGH,
                'm' => '余额不足'
            ];

        }
        // 记录用户流水
        $oUserFinanceLog                         = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
        $oUserFinanceLog->user_id                = $oUser->user_id;
        $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
        $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
        $oUserFinanceLog->consume_category_id    = UserConsumeCategory::SEND_CHAT_PAY;
        $oUserFinanceLog->consume                = -$nCoin;
        $oUserFinanceLog->remark                 = '私聊收费';
        $oUserFinanceLog->flow_id                = 0;
        $oUserFinanceLog->flow_number            = '';
        $oUserFinanceLog->type                   = 0;
        $oUserFinanceLog->target_user_id         = $oToUser->user_id;
        $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
        $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
        $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
        $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
        $flg                                     = $oUserFinanceLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => implode(',', $oUserFinanceLog->getMessages())
            ];
        }

        $oAnchorUser = $oToUser;
        if ( !$nRatio ) {
            // 主播获得收益
            if(!$oAnchor){

                $oAnchor = Anchor::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $oToUser->user_id ]
                ]);

            }
            $nRatio  = $oAnchor->getCoinToDotRatio($oAnchorUser, Anchor::RATIO_CHAT);
        }
        $nDot       = sprintf('%.4f', $nCoin * ($nRatio / 100));
        $getDot     = sprintf('%.4f', $consumeCoin * ($nRatio / 100));
        $getFreeDot = round($nDot - $getDot, 4);

        $anchorExp     = intval($nDot * intval(Kv::get(Kv::DOT_TO_ANCHOR_EXP)));
        $anchorLevel   = LevelConfig::getLevelInfo($oAnchor->anchor_exp + $anchorExp);
        if($nDot) {

            // 给主播充钱
            $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
            $connection->execute($sql, [
                'total_dot'    => $nDot,
                'get_dot'      => $getDot,
                'get_free_dot' => $getFreeDot,
                'user_id'      => $oToUser->user_id,
            ]);
            if ( $connection->affectedRows() <= 0 ) {
                $connection->rollback();
                return [
                    'c' => ResponseError::FAIL,
                    'm' => '主播增加收益失败'
                ];
            }
            // 给主播加经验(魅力值)
            $anchorSql = 'update anchor set anchor_exp = anchor_exp + :anchor_exp,anchor_level = :anchor_level WHERE user_id = :user_id';
            $connection->execute($anchorSql, [
                'anchor_exp'   => $anchorExp,
                'anchor_level' => $anchorLevel['level'],
                'user_id'      => $oToUser->user_id,
            ]);

            // 记录主播流水
            $oUserFinanceLog                      = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
            $oUserFinanceLog->user_id             = $oToUser->user_id;
            $oUserFinanceLog->user_current_amount = $oAnchorUser->user_dot + $nDot;
            $oUserFinanceLog->user_last_amount    = $oAnchorUser->user_dot;
            $oUserFinanceLog->consume_category_id = UserConsumeCategory::SEND_CHAT_PAY;
            $oUserFinanceLog->consume             = +$nDot;
            $oUserFinanceLog->remark              = '私聊收费';
            $oUserFinanceLog->flow_id             = 0;
            $oUserFinanceLog->type                = 0;
            $oUserFinanceLog->group_id            = $oAnchorUser->user_group_id;
            $oUserFinanceLog->consume_source      = -$nCoin;
            $oUserFinanceLog->target_user_id      = $oUser->user_id;
            if ( $oUserFinanceLog->save() === FALSE ) {
                $connection->rollback();
                return [
                    'c' => ResponseError::FAIL,
                    'm' => implode(',', $oUserFinanceLog->getMessages())
                ];
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
                    $connection->execute($sql, [
                        'money'    => $groupMoney,
                        'group_id' => $oAnchorUser->user_group_id,
                    ]);
                }
            }
        }

        $connection->commit();

        // 主播今日收益 增加
        $oAnchorTodayDotService = new AnchorTodayDotService($oAnchorUser->user_id);
        $oAnchorTodayDotService->save($nDot);

        // 主播每日统计
        $oAnchorStatService = new AnchorStatService($oAnchorUser->user_id);
        $oAnchorStatService->save(AnchorStatService::WORD_INCOME,$nDot);

        if ( $nCoin ) {

            // 活动消费统计
            $oActivityUserService = new ActivityUserService();
            $oActivityUserService->save($oUser->user_id,$nCoin);
        }


        return TRUE;

    }


    /**
     * 添加诱导付费
     */
    public static function addGuidePay(\app\models\User $oUser, $price, $oUserGuideVideo)
    {
        $userFinanceLog = new UserFinanceLog();
        $connection     = $userFinanceLog->getWriteConnection();
        $connection->begin();
        // 操作用户的数据
        $nCoin = $price;
        //扣费
        $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin 
where user_id = :user_id AND user_free_coin >= :consume_free_coin';
        $connection->execute($sql, [
            'consume_free_coin' => $nCoin,
            'user_id'           => $oUser->user_id,
        ]);
        if ( $connection->affectedRows() <= 0 ) {
            // 赠送币 不够钱
            $connection->rollback();
            return [
                'c' => ResponseError::USER_COIN_NOT_ENOUGH,
                'm' => '余额不足'
            ];
        }

        //记录诱导
        $oUserGuideVideoLog                 = new UserGuideVideoLog();
        $oUserGuideVideoLog->user_id        = $oUser->user_id;
        $oUserGuideVideoLog->guide_video_id = $oUserGuideVideo->id;
        if ( $oUserGuideVideoLog->save() == FALSE ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => implode(',', $oUserGuideVideoLog->getMessages())
            ];
        }
        // 记录用户流水
        $oUserFinanceLog                         = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
        $oUserFinanceLog->user_id                = $oUser->user_id;
        $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
        $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
        $oUserFinanceLog->consume_category_id    = UserConsumeCategory::GUIDE_VIDEO_PAY;
        $oUserFinanceLog->consume                = -$nCoin;
        $oUserFinanceLog->remark                 = '诱导匹配视频付费';
        $oUserFinanceLog->flow_id                = $oUserGuideVideoLog->id;
        $oUserFinanceLog->flow_number            = '';
        $oUserFinanceLog->type                   = 0;
        $oUserFinanceLog->target_user_id         = $oUserGuideVideo->anchor_user_id;
        $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
        $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
        $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $nCoin;
        $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
        $flg                                     = $oUserFinanceLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            return [
                'c' => ResponseError::FAIL,
                'm' => implode(',', $oUserFinanceLog->getMessages())
            ];
        }
        $connection->commit();
        return TRUE;

    }

}