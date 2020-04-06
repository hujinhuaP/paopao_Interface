<?php 

namespace app\models;

/**
* AnchorSourceCertification 视频或图片二次审核记录表
*/
class AnchorSourceCertification extends ModelBase
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