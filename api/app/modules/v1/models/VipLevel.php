<?php

namespace app\models;

/**
 * VipLevel VIP等级配置表
 */
class VipLevel extends ModelBase
{
    public $vip_level_id;
    public $vip_level_name;
    public $vip_level_value;
    public $vip_level_min_exp;
    public $vip_level_exhibition_discount;
    public $vip_level_video_chat_discount;
    public $vip_level_create_time;
    public $vip_level_update_time;


    /**
     * @param $exp
     * @param string $type
     *
     */
    public static function getLevelInfo($exp)
    {
        $levelData = self::find([
            'order' => 'vip_level_value',
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey('all')
            ]
        ]);
        if($levelData){
            $levelData = $levelData->toArray();
        }
        $level = 0;
        $level_name = 'v0';
        foreach($levelData as $item){
            if($exp < $item['vip_level_min_exp']){
                break;
            }
            $level = $item['vip_level_value'];
            $level_name = $item['vip_level_name'];
        }
        return [
            'level'      => $level,
            'level_name' => $level_name,
        ];

    }

    /**
     * @param $level
     * @return VipLevel
     * 获取等级信息
     */
    public static function getVipInfo($level)
    {
        return self::findFirst([
            'vip_level_value = :vip_level_value:',
            'bind' => [
                'vip_level_value' => $level
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey('level-'.$level)
            ]
        ]);
    }


}