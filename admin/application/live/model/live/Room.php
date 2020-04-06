<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 摄影师
 */
class Room extends Model
{

    /**
     * 音频房ID
     */
    const B_CHAT_ID = '1';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'room_create_time';
    protected $updateTime = 'room_update_time';


    public function getRoomOpenFlgList()
    {
        return ['Y' => __('开启'), 'N' => __('关闭')];
    }

}
