<?php

namespace app\models;

use app\services\GuardFreeTimeService;

/**
 * UserGuard 用户的守护信息
 */
class UserGuard extends ModelBase
{
    public $id;
    public $user_id;
    public $anchor_user_id;
    public $total_coin;
    public $current_level;
    public $current_level_name;
    public $guard_status;
    public $create_time;
    public $update_time;


    /**
     * @param int $nAnchorUserId
     * @param int $nUserId
     * 获取当前用户 对主播的 守护每天的免费时长
     */
    public static function getTodayFreeTimes( int $nAnchorUserId, int $nUserId )
    {
        $oUserGuard = UserGuard::findFirst([
            'user_id = :user_id: AND anchor_user_id = :anchor_user_id:',
            'bind' => [
                'user_id'        => $nUserId,
                'anchor_user_id' => $nAnchorUserId
            ]
        ]);
//        if($oUserGuard->guard_status == 'N'){
//            return 0;
//        }
        // 先取出该等级每天可以用的时长
        $oLevelConfig       = LevelConfig::findFirst([
            'level_type = :level_type: AND level_value = :level_value: ',
            'bind' => [
                'level_type'  => LevelConfig::LEVEL_TYPE_GUARD,
                'level_value' => $oUserGuard->current_level
            ]
        ]);
        $todayTotalFreeTime = intval($oLevelConfig->level_extra);
        if ( $todayTotalFreeTime == 0 ) {
            return 0;
        }
        // 取出当天已用的免费时长
        $oGuardFreeTimeService = new GuardFreeTimeService($nUserId, $nAnchorUserId);
        $todayUseFreeTime      = $oGuardFreeTimeService->getData();
        $existFreeTime         = $todayTotalFreeTime - intval($todayUseFreeTime);
        return $existFreeTime > 0 ? $existFreeTime : 0;

    }

}