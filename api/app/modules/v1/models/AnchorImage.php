<?php 

namespace app\models;

/**
* AnchorImage 主播图片表
*/
class AnchorImage extends ModelBase
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