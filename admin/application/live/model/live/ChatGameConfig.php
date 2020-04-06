<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 聊天游戏配置
 */
class ChatGameConfig extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'chat_game_create_time';
    protected $updateTime = 'chat_game_update_time';


}
