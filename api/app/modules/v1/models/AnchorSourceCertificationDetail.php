<?php 

namespace app\models;

/**
* AnchorSourceCertificationDetail 主播审核资源列表
*/
class AnchorSourceCertificationDetail extends ModelBase
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