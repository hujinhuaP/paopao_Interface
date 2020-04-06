<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 审核内容
 */
class ExamineInfo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'examine_info_create_time';
    protected $updateTime = 'examine_info_update_time';


}
