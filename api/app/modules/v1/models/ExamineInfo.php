<?php

namespace app\models;

/**
 * ExamineInfo 审核信息
 */
class ExamineInfo extends ModelBase
{
    public $examine_info_id;
    public $examine_info_content;
    public $examine_info_create_time;
    public $examine_info_update_time;

    public function beforeCreate()
    {
        $this->examine_info_create_time = time();
        $this->examine_info_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->examine_info_update_time = time();
    }

}