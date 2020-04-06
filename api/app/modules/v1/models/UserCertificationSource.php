<?php 

namespace app\models;

/**
* UserCertificationSource 用户视频或图片认证
*/
class UserCertificationSource extends ModelBase
{

    const TYPE_FIRST = 'first';

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