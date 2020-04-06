<?php

namespace app\models;


use app\services\IntimateService;

/**
 * UserIntimate 用户的亲密度表
 */
class UserIntimate extends ModelBase
{
    public $intimate_id;
    public $intimate_user_id;
    public $intimate_anchor_user_id;
    public $intimate_value;
    public $intimate_level;
    public $intimate_level_name;
    public $intimate_create_time;
    public $intimate_update_time;

    public function beforeCreate()
    {
        $this->intimate_update_time = time();
        $this->intimate_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->intimate_update_time = time();
    }


    /**
     * @param $anchorUserId
     * @param $userId
     * @return array
     * 获取等级信息
     */
    public static function getIntimateLevel( $nToUserId, $nUserId, $nToUserIsAnchor = 'Y', $nUserIsAnchor = 'N' )
    {
        if ( $nToUserIsAnchor == $nUserIsAnchor ) {
            // 两者属性相同
            return [
                'level'       => 0,
                'level_name'  => '',
                'total_value' => 0,
            ];
        }

        if ( $nToUserIsAnchor == 'Y' ) {
            $anchorUserId = $nToUserId;
            $userId       = $nUserId;
        } else {
            $anchorUserId = $nUserId;
            $userId       = $nToUserId;
        }

        $oIntimateService = new IntimateService($anchorUserId, $userId);
        $levelInfo        = $oIntimateService->getInfo();
        if ( !$levelInfo ) {
            // 先从缓存取  不存在从数据库取
            $oUserIntimate = UserIntimate::findFirst([
                'intimate_user_id = :intimate_user_id: AND intimate_anchor_user_id = :intimate_anchor_user_id:',
                'bind' => [
                    'intimate_user_id'        => $userId,
                    'intimate_anchor_user_id' => $anchorUserId
                ]
            ]);
            if ( !$oUserIntimate ) {
                $levelInfoData = LevelConfig::getLevelInfo(0);
                $levelInfo     = [
                    'level'       => $levelInfoData['level'],
                    'level_name'  => $levelInfoData['level_name'],
                    'total_value' => 0,
                ];
            } else {
                $levelInfo = [
                    'level'       => $oUserIntimate->intimate_level,
                    'level_name'  => $oUserIntimate->intimate_level_name,
                    'total_value' => $oUserIntimate->intimate_value,
                ];
            }
            $oIntimateService->saveItem($levelInfo);
        }
        return [
            'level'           => intval($levelInfo['level']) ?? 0,
            'level_name'      => $levelInfo['level_name'] ?? '',
            'total_value'     => intval($levelInfo['total_value']) ?? 0,
            'level_free_chat' => $levelInfo['total_extra'] ?? 'N',
        ];
    }

}