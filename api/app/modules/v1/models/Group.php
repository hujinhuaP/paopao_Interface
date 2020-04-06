<?php 

namespace app\models;

/**
* Group 公会
*/
class Group extends ModelBase
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