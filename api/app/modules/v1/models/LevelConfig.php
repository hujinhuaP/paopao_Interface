<?php

namespace app\models;

/**
 * LevelConfig 等级配置表
 */
class LevelConfig extends ModelBase
{
    /** @var string 亲密值 */
    const LEVEL_TYPE_INTIMATE = 'intimate';
    /** @var string 守护 */
    const LEVEL_TYPE_GUARD = 'guard';
    /** @var string 用户 */
    const LEVEL_TYPE_USER  = 'user';
    /** @var string 主播 */
    const LEVEL_TYPE_ANCHOR  = 'anchor';
    public $id;
    public $level_type;
    public $level_name;
    public $level_value;
    public $level_exp;
    public $create_time;
    public $update_time;


    /**
     * @param $exp
     * @param string $type
     *
     */
    public static function getLevelInfo($exp,$type = self::LEVEL_TYPE_INTIMATE)
    {
        $levelData = self::find([
            'level_type = :level_type:',
            'bind' => [
                'level_type' => $type
            ],
            'order' => 'level_exp',
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey($type)
            ]
        ]);
        if($levelData){
            $levelData = $levelData->toArray();
        }
        $level = 0;
        $level_name = '萍水相逢';
        foreach($levelData as $item){
            if($exp < $item['level_exp']){
                break;
            }
            $level = $item['level_value'];
            $level_name = $item['level_name'];
        }
        return [
            'level'      => $level,
            'level_name' => $level_name,
        ];

    }

    /**
     * @param $anchor_level
     * 根据主播等级值 获取主播最大的私聊设置价格
     */
    public static function getAnchorMaxPrice($anchor_level) {
        $levelData = self::find([
            'level_type = :level_type:',
            'bind' => [
                'level_type' => self::LEVEL_TYPE_ANCHOR
            ],
            'order' => 'level_exp',
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey(self::LEVEL_TYPE_ANCHOR)
            ]
        ]);
        if(!$levelData){
            return 0;
        }

        $levelData = array_column($levelData->toArray(),'level_extra','level_value');

        if(!isset($levelData[$anchor_level])){
            return 0;
        }
        $extraData = unserialize($levelData[$anchor_level]);
        return $extraData['max_chat_price'] ?? 0;

    }

}