<?php 

namespace app\models;

/**
* DailyDataStat 每日统计
*/
class DailyDataStat extends ModelBase
{
	
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