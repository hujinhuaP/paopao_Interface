<?php 

namespace app\models;

/**
* 微信认证 WechatCertification
*/
class WechatCertification extends ModelBase
{
    public function beforeCreate()
    {
        $this->wechat_certification_create_time = time();
        $this->wechat_certification_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->wechat_certification_update_time = time();
    }
}