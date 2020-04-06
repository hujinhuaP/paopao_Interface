<?php 

namespace app\models;

/**
* Agent 代理商
*/
class Agent extends ModelBase
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
     * @param int $number
     * 减少用户的有效邀请人数
     */
    public function decUserInviteEffectiveTotal($user_agent_id,$number = 1)
    {
        $oAgent = Agent::findFirst($this->user_invite_agent_id);
        if($oAgent){
            $sql        = "update `agent` set affect_register_count = affect_register_count - $number,total_affect_register_count = total_affect_register_count - $number where id = " . $user_agent_id;
            $connection = $this->getWriteConnection();
            $connection->execute($sql);
            if($oAgent->first_leader){
                $sql        = "update `agent` set total_affect_register_count = total_affect_register_count - $number where id = " . $oAgent->first_leader;
                $connection->execute($sql);
                if($oAgent->second_leader){
                    $sql        = "update `agent` set total_affect_register_count = total_affect_register_count - $number where id = " . $oAgent->second_leader;
                    $connection->execute($sql);
                }
            }
        }
        return true;
    }
}