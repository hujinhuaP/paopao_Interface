<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 用户
 */
class UserVideo extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';

    protected static function init()
    {
        self::beforeInsert(function ($row) {
            $oRedis = new \think\cache\driver\Redis(\think\Config::get('redis'));
            $key = sprintf('_PHCRcaches_user_video_count:%s',$row->user_id);
            $oRedis->rm($key);
        });

        self::beforeDelete(function ($row) {
            $oRedis = new \think\cache\driver\Redis(\think\Config::get('redis'));
            $key = sprintf('_PHCRcaches_user_video_count:%s',$row->user_id);
            $oRedis->rm($key);
        });
    }

    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function category()
    {
        return $this->belongsTo('video_category','type','id',[],'inner')->setEagerlyType(0);
    }
}
