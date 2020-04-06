<?php 

namespace app\models;

/**
* Question 热门问题表
*/
class Question extends ModelBase
{
	
	public function beforeCreate()
    {
		$this->question_create_time = time();
		$this->question_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->question_update_time = time();
    }
}