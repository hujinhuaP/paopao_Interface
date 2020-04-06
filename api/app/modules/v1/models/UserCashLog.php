<?php

namespace app\models;


use app\services\UserTodayCashService;

/**
 * UserCashLog 用户“现金”记录表
 */
class UserCashLog extends ModelBase
{
    /** @var string 邀请注册奖励 */
    const CATEGORY_REGISTER = 'register';
    /** @var string 邀请充值奖励 */
    const CATEGORY_RECHARGE = 'recharge';
    /** @var string 邀请提现奖励 */
    const CATEGORY_WITHDRAW = 'withdraw';
    /** @var string 兑换消耗 */
    const CATEGORY_EXCHANGE = 'exchange';
    /** @var string 兑换消耗 */
    const CATEGORY_VIP = 'vip';
    /** @var string 兑换消耗 */
    const CATEGORY_EXCHANGE_BACK = 'withdraw_back';

    public $user_id;
    public $user_current_amount;
    public $user_last_amount;
    public $consume_category;
    public $consume;
    public $remark;
    public $flow_id;
    public $flow_number;
    public $target_user_id;
    public $update_time;
    public $create_time;

    /**
     * 添加用户邀请VIP 奖励 “现金”
     * @param  int $user_invite_agent_id
     * @param \app\models\UserVipOrder $oUserVipOrder
     */
    public static function addInviteVipReward($user_invite_agent_id, $oUserVipOrder)
    {
        $oUser = User::findFirst($user_invite_agent_id);
        //主播没有奖励
        if ( $oUser->user_is_anchor == 'Y' ) {
            echo 1;
            return FALSE;
        }
        $invite_recharge_radio    = Kv::get(Kv::INVITE_VIP_RADIO_CASH);
        $user_vip_order_combo_fee = $oUserVipOrder->user_vip_order_combo_fee;
        $reward_cash              = round($user_vip_order_combo_fee * $invite_recharge_radio / 100, 2);

        $oUserInviteRewardLog = new UserInviteRewardLog();
        $connection           = $oUserInviteRewardLog->getWriteConnection();
        $connection->begin();
        // 记录邀请奖励记录表
        $oUserInviteRewardLog->user_invite_reward_type  = UserInviteRewardLog::TYPE_VIP;
        $oUserInviteRewardLog->user_id                  = $oUserVipOrder->user_id;
        $oUserInviteRewardLog->parent_user_id           = $user_invite_agent_id;
        $oUserInviteRewardLog->invite_level             = 1;
        $oUserInviteRewardLog->recharge_invite_cash     = $reward_cash;
        $oUserInviteRewardLog->user_recharge_combo_fee  = $oUserVipOrder->user_vip_order_combo_fee;
        $oUserInviteRewardLog->user_recharge_combo_coin = 0;
        $oUserInviteRewardLog->invite_ratio             = $invite_recharge_radio;
        $flg                                            = $oUserInviteRewardLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            var_dump($oUserInviteRewardLog->getMessages());
            return FALSE;
        }
        // 记录“现金”流水
        $oUserCashLog                      = new UserCashLog();
        $oUserCashLog->user_id             = $user_invite_agent_id;
        $oUserCashLog->consume_category    = UserCashLog::CATEGORY_VIP;
        $oUserCashLog->user_current_amount = $oUser->user_cash - $reward_cash;
        $oUserCashLog->user_last_amount    = $oUser->user_cash;
        $oUserCashLog->consume             = $reward_cash;
        $oUserCashLog->remark              = '邀请购买VIP奖励';
        $oUserCashLog->flow_id             = $oUserInviteRewardLog->user_invite_reward_log_id;
        $oUserCashLog->flow_number         = '';
        $oUserCashLog->target_user_id      = $oUserVipOrder->user_id;
        $flg                               = $oUserCashLog->save();
        if ( $flg == FALSE ) {
            $connection->rollback();
            var_dump($oUserCashLog->getMessages());
            return FALSE;
        }

        $sql = 'update `user` set user_cash = user_cash + ' . $reward_cash . ',total_user_cash = total_user_cash + ' .
            $reward_cash . ' where user_id = ' . $user_invite_agent_id;
        $connection->execute($sql);
        if ( $connection->affectedRows() <= 0 ) {
            $connection->rollback();
            var_dump($sql);
            return FALSE;
        }
        // 记录该用户今日“现金”收益
        $oUserTodayCashService = new UserTodayCashService($user_invite_agent_id);
        $oUserTodayCashService->save($reward_cash);
        $connection->commit();

    }

    public function beforeCreate()
    {
        $this->update_time = time();
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

}
