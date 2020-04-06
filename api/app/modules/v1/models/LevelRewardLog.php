<?php

namespace app\models;

/**
 * LevelRewardLog 等级奖励记录
 */
class LevelRewardLog extends ModelBase
{
    public $level_reward_log_id;
    public $level_reward_log_user_id;
    public $level_reward_log_level_value;
    public $level_reward_log_coin;
    public $level_reward_log_create_time;
    public $level_reward_log_update_time;

    /**
     * @param int $nUserId
     * 获取用户对应等级是否领取 以及所有等级的奖励
     */
    public function getUserInfoList(int $nUserId) {

    }



    public function beforeCreate()
    {
        $this->level_reward_log_create_time = time();
        $this->level_reward_log_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->level_reward_log_update_time = time();
    }

}