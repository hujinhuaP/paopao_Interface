<?php 

namespace app\models;

/**
* PhotographerSourceCertificationDetail 摄影师审核资源列表
*/
class PhotographerSourceCertificationDetail extends ModelBase
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