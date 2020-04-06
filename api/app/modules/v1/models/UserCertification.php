<?php 

namespace app\models;

/**
* UserCertification 用户实名认证
*/
class UserCertification extends ModelBase
{

    public $user_certification_status;
    public $user_certification_video_status;
    public $user_certification_image_status;
    public $user_certification_type;

	public function beforeCreate()
    {
		$this->user_certification_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_certification_update_time = time();
    }

}