<?php 

namespace app\models;

/**
* PhotographerSourceCertification 摄影师视频或图片二次审核记录表
*/
class PhotographerSourceCertification extends ModelBase
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