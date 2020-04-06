<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class AgentWithdrawLog extends ApiModel
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function Agent()
    {
        return $this->belongsTo('agent', 'agent_id', 'id', [], 'INNER')->setEagerlyType(0);
    }

    /**
     * 添加记录
     * 需要添加 用户流水记录
     *
     * @param $user User
     *
     * @return bool
     */
    public function addWithdraw($saveData)
    {
        $this->startTrans();
        $flg = Agent::where("id={$saveData['agent_id']} and commission >= {$saveData['withdraw_cash']}")->setDec('commission', $saveData['withdraw_cash']);
        if ( $flg === FALSE ) {
            $this->rollback();
            return FALSE;
        }
        $flg = Agent::where('id=' . $saveData['agent_id'])->setInc('total_withdraw', $saveData['withdraw_cash']);
        if ( $flg === FALSE ) {
            $this->rollback();
            return FALSE;
        }
        if ( $this->save() === FALSE ) {
            $this->rollback();
            return FALSE;
        }
        // 添加流水
        $oAgentWaterLog                            = new AgentWaterLog();
        $oAgentWaterLog->agent_id                  = $saveData['agent_id'];
        $oAgentWaterLog->income                    = -$saveData['withdraw_cash'];
        $oAgentWaterLog->source_type               = 'withdraw';
        $oAgentWaterLog->source_agent_id           = $saveData['agent_id'];
        $oAgentWaterLog->follow_order_id           = $this->id;
        $oAgentWaterLog->distribution_profits      = 100;
        $oAgentWaterLog->distribution_source_value = $saveData['withdraw_cash'];
        if ( $oAgentWaterLog->save() === FALSE ) {
            $this->rollback();
            return FALSE;
        }
        $this->commit();
        return TRUE;
    }
}
