<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 动态
 */
class ShortPosts extends Model
{

    const PAY_TYPE_FREE      = 'free';
    const PAY_TYPE_PART_FREE = 'part_free';
    const PAY_TYPE_PAY       = 'pay';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'short_posts_create_time';
    protected $updateTime = FALSE;

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $oRedis = new \think\cache\driver\Redis(\think\Config::get('redis'));
            $key = sprintf('_PHCRcaches_user_posts_count:%s',$row->short_posts_user_id);
            $oRedis->rm($key);
        });

        self::beforeDelete(function ($row) {
            $oRedis = new \think\cache\driver\Redis(\think\Config::get('redis'));
            $key = sprintf('_PHCRcaches_user_posts_count:%s',$row->short_posts_user_id);
            $oRedis->rm($key);
        });
    }
}
