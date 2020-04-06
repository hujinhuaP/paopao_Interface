<?php

namespace app\admin\model\api;


/**
 * 用户分享背景图表
 */
class UserShareBaseImage extends ApiModel
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

}
