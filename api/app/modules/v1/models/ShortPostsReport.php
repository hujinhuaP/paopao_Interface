<?php 

namespace app\models;

/**
* ShortPostsReport 动态举报表
*/
class ShortPostsReport extends ModelBase
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
}