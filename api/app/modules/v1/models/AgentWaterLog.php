<?php

namespace app\models;

/**
 * AgentWaterLog 代理商流水
 */
class AgentWaterLog extends ModelBase
{

    public function beforeCreate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }


    /**
     * @param $user_invite_agent_id
     * @param static $oUserRechargeOrder
     * 注册添加流水
     * 只添加流水   具体加值在每日统计时加值
     * @param \app\models\Agent $oAgent
     * @return bool
     */
    public static function addRechargeReward($user_invite_agent_id, $oUserRechargeOrder,$oAgent)
    {
        $oAgentWaterLog = new AgentWaterLog();
        $connection     = $oAgentWaterLog->getWriteConnection();
        $create_time    = $update_time = time();
        // 判断代理商的分成比例
//        $oAgent = Agent::findFirst($user_invite_agent_id);
        if ( !$oAgent && $oAgent->status == 'N' ) {
            return FALSE;
        }
//        $connection->begin();
        $saveValues = [];
        $income     = round($oUserRechargeOrder->user_recharge_order_fee * $oAgent->recharge_distribution_profits / 100, 2);
//        $sUpdateSql = "update agent set commission = commission + :income,income = income + :income,total_income = total_income + :income,recharge_money = recharge_money + :coin,total_recharge_money = total_recharge_money + :coin where id = :agent_id";
//        $connection->execute($sUpdateSql, [
//            'income'   => $income,
//            'coin'     => $oUserRechargeOrder->user_recharge_order_fee,
//            'agent_id' => $user_invite_agent_id
//        ]);
//        if ( $connection->affectedRows() <= 0 ) {
//            $connection->rollback();
//            return FALSE;
//        }
        if($income > 0){
            $saveValues[] = "($user_invite_agent_id,$income,'recharge',{$oUserRechargeOrder->user_id},
        $user_invite_agent_id,$create_time,$update_time,{$oUserRechargeOrder->user_recharge_order_id},
        {$oAgent->recharge_distribution_profits},{$oUserRechargeOrder->user_recharge_order_fee})";
        }

        //判断该代理 是否有上级
        if ( $oAgent->first_leader ) {
            $oFirstLeaderAgent = Agent::findFirst($oAgent->first_leader);
            if ( $oFirstLeaderAgent ) {
                $firstIncome = round($oUserRechargeOrder->user_recharge_order_fee * $oFirstLeaderAgent->recharge_distribution_profits / 100, 2) - $income;
                if ( $firstIncome > 0 ) {
//                    $sUpdateSql = "update agent set commission = commission + :income,income = income + :income,total_income = total_income + :income,total_recharge_money = total_recharge_money + :coin  where id = :agent_id";
//                    $connection->execute($sUpdateSql, [
//                        'income'   => $firstIncome,
//                        'coin'     => $oUserRechargeOrder->user_recharge_order_fee,
//                        'agent_id' => $oAgent->first_leader
//                    ]);
//                    if ( $connection->affectedRows() <= 0 ) {
//                        $connection->rollback();
//                        return FALSE;
//                    }
                    $affect_distribution_profits = $oFirstLeaderAgent->recharge_distribution_profits - $oAgent->recharge_distribution_profits;
                    $saveValues[]                = "($oAgent->first_leader,$firstIncome,'recharge',{$oUserRechargeOrder->user_id},
        $user_invite_agent_id,$create_time,$update_time,{$oUserRechargeOrder->user_recharge_order_id},
        {$affect_distribution_profits},{$oUserRechargeOrder->user_recharge_order_fee})";
                }

                if ( $oAgent->second_leader ) {
                    $oSecendLeaderAgent = Agent::findFirst($oAgent->second_leader);
                    if ( $oSecendLeaderAgent ) {
                        $secendIncome = round($oUserRechargeOrder->user_recharge_order_fee * $oSecendLeaderAgent->recharge_distribution_profits / 100, 2) - $firstIncome - $income;

                        if($secendIncome > 0 ){
//                            $sUpdateSql = "update agent set commission = commission + :income,income = income + :income,total_income = total_income + :income,total_recharge_money = total_recharge_money + :coin  where id = :agent_id";
//                            $connection->execute($sUpdateSql, [
//                                'income'   => $firstIncome,
//                                'coin'     => $oUserRechargeOrder->user_recharge_order_fee,
//                                'agent_id' => $oAgent->second_leader
//                            ]);
//                            if ( $connection->affectedRows() <= 0 ) {
//                                $connection->rollback();
//                                return FALSE;
//                            }
                            $affect_distribution_profits = $oSecendLeaderAgent->recharge_distribution_profits - $oFirstLeaderAgent->recharge_distribution_profits;
                            $saveValues[]                = "($oAgent->second_leader,$secendIncome,'recharge',{$oUserRechargeOrder->user_id},
        $user_invite_agent_id,$create_time,$update_time,{$oUserRechargeOrder->user_recharge_order_id},
        {$affect_distribution_profits},{$oUserRechargeOrder->user_recharge_order_fee})";
                        }
                    }
                }
            }
        }

        if ( $saveValues ) {
            $sql = 'insert into agent_water_log(agent_id,income,source_type,source_user_id,source_agent_id,create_time,update_time,follow_order_id,distribution_profits,distribution_source_value) values %s';
            $sql = sprintf($sql, implode(',', $saveValues));
            $connection->execute($sql);
            if ( $connection->affectedRows() <= 0 ) {
//                $connection->rollback();
                return FALSE;
            }
        }
//        $connection->commit();
        return TRUE;

    }


    /**
     * @param $user_invite_agent_id
     * @param \app\models\UserVipOrder $oUserVipOrder
     * @return bool
     */
    public static function addVipReward($user_invite_agent_id, $oUserVipOrder)
    {
        $oAgentWaterLog = new AgentWaterLog();
        $connection     = $oAgentWaterLog->getWriteConnection();
        $create_time    = $update_time = time();
        // 判断代理商的分成比例
        $oAgent = Agent::findFirst($user_invite_agent_id);
        if ( !$oAgent ) {
            return FALSE;
        }
//        $connection->begin();
        $saveValues = [];
        $income     = round($oUserVipOrder->user_vip_order_combo_fee * $oAgent->vip_distribution_profits / 100, 2);

        if($income > 0){
            $saveValues[] = "($user_invite_agent_id,$income,'vip',{$oUserVipOrder->user_id},
        $user_invite_agent_id,$create_time,$update_time,{$oUserVipOrder->user_vip_order_id},
        {$oAgent->vip_distribution_profits},{$oUserVipOrder->user_vip_order_combo_fee})";
        }

        //判断该代理 是否有上级
        if ( $oAgent->first_leader ) {
            $oFirstLeaderAgent = Agent::findFirst($oAgent->first_leader);
            if ( $oFirstLeaderAgent ) {
                $firstIncome = round($oUserVipOrder->user_vip_order_combo_fee * $oFirstLeaderAgent->vip_distribution_profits / 100 / 10, 2) - $income;
                if ( $firstIncome > 0 ) {
//                    $sUpdateSql = "update agent set commission = commission + :income,income = income + :income,total_income = total_income + :income,total_recharge_money = total_recharge_money + :coin  where id = :agent_id";
//                    $connection->execute($sUpdateSql, [
//                        'income'   => $firstIncome,
//                        'coin'     => $oUserRechargeOrder->user_recharge_combo_coin,
//                        'agent_id' => $oAgent->first_leader
//                    ]);
//                    if ( $connection->affectedRows() <= 0 ) {
//                        $connection->rollback();
//                        return FALSE;
//                    }
                    $affect_distribution_profits = $oFirstLeaderAgent->vip_distribution_profits - $oAgent->vip_distribution_profits;
                    $saveValues[]                = "($oAgent->first_leader,$firstIncome,'vip',{$oUserVipOrder->user_id},
        $user_invite_agent_id,$create_time,$update_time,{$oUserVipOrder->user_vip_order_id},
        {$affect_distribution_profits},{$oUserVipOrder->user_vip_order_combo_fee})";
                }

                if ( $oAgent->second_leader ) {
                    $oSecendLeaderAgent = Agent::findFirst($oAgent->second_leader);
                    if ( $oSecendLeaderAgent ) {
                        $secendIncome = round($oUserVipOrder->user_vip_order_combo_fee * $oSecendLeaderAgent->vip_distribution_profits / 100 / 10, 2) - $firstIncome - $income;

                        if($secendIncome > 0 ){
//                            $sUpdateSql = "update agent set commission = commission + :income,income = income + :income,total_income = total_income + :income,total_recharge_money = total_recharge_money + :coin  where id = :agent_id";
//                            $connection->execute($sUpdateSql, [
//                                'income'   => $firstIncome,
//                                'coin'     => $oUserRechargeOrder->user_recharge_combo_coin,
//                                'agent_id' => $oAgent->second_leader
//                            ]);
//                            if ( $connection->affectedRows() <= 0 ) {
//                                $connection->rollback();
//                                return FALSE;
//                            }
                            $affect_distribution_profits = $oSecendLeaderAgent->vip_distribution_profits - $oFirstLeaderAgent->vip_distribution_profits;
                            $saveValues[]                = "($oAgent->second_leader,$secendIncome,'vip',{$oUserVipOrder->user_id},
        $user_invite_agent_id,$create_time,$update_time,{$oUserVipOrder->user_vip_order_id},
        {$affect_distribution_profits},{$oUserVipOrder->user_vip_order_combo_fee})";
                        }
                    }
                }
            }
        }

        if ( $saveValues ) {
            $sql = 'insert into agent_water_log(agent_id,income,source_type,source_user_id,source_agent_id,create_time,update_time,follow_order_id,distribution_profits,distribution_source_value) values %s';
            $sql = sprintf($sql, implode(',', $saveValues));
            $connection->execute($sql);
            if ( $connection->affectedRows() <= 0 ) {
//                $connection->rollback();
                return FALSE;
            }
        }
//        $connection->commit();
        return TRUE;

    }
}