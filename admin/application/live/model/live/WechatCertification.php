<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 微信认证
 */
class WechatCertification extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'wechat_certification_create_time';
    protected $updateTime = 'wechat_certification_update_time';
}
