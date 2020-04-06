<?php 

namespace app\models;

/**
* AboutUs 关于我们
*/
class AboutUs extends ModelBase
{

	public function beforeCreate()
    {
		$this->about_us_create_time = time();
		$this->about_us_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->about_us_update_time = time();
    }
}