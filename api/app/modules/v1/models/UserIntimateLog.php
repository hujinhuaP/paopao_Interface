<?php

namespace app\models;


use app\services\IntimateService;

/**
 * UserIntimateLog 用户的亲密度日志表
 */
class UserIntimateLog extends ModelBase
{
    /** @var string 私聊送礼 */
    const TYPE_CHAT_GIFT = 'chat_gift';
    /** @var string 视频聊天送礼 */
    const TYPE_VIDEO_CHAT_GIFT = 'video_chat_gift';
    /** @var string 动态送礼 */
    const TYPE_POSTS_GIFT      = 'posts_gift';
    /** @var string 视频聊天中游戏 */
    const TYPE_VIDEO_CHAT_GAME = 'video_chat_game';
    /** @var string 视频聊天 */
    const TYPE_VIDEO_CHAT      = 'video_chat';
    /** @var string 购买动态 */
    const TYPE_BUY_POSTS      = 'buy_posts';
    /** @var string 购买守护 */
    const TYPE_GUARD     = 'guard';
    /** @var string 购买大V微信 */
    const TYPE_BUY_WECHAT     = 'buy_wechat';


    public $intimate_log_id;
    public $intimate_log_user_id;
    public $intimate_log_anchor_user_id;
    public $intimate_log_type;
    public $intimate_log_value;
    public $intimate_log_level;
    public $intimate_log_level_name;
    public $intimate_log_coin;
    public $intimate_log_dot;
    public $intimate_log_create_time;
    public $intimate_log_update_time;

    public function beforeCreate()
    {
        $this->intimate_log_update_time = time();
        $this->intimate_log_create_time = time();

        // 操作 UserIntimate
        $oUserIntimate = UserIntimate::findFirst([
            'intimate_user_id = :intimate_user_id: AND intimate_anchor_user_id = :intimate_anchor_user_id:',
            'bind' => [
                'intimate_user_id'        => $this->intimate_log_user_id,
                'intimate_anchor_user_id' => $this->intimate_log_anchor_user_id
            ]
        ]);
        if ( !$oUserIntimate ) {
            $oUserIntimate = new UserIntimate();
            $oUserIntimate->intimate_value          = 0;
            $oUserIntimate->intimate_user_id        = $this->intimate_log_user_id;
            $oUserIntimate->intimate_anchor_user_id = $this->intimate_log_anchor_user_id;
        }
        $oldLevel = $oUserIntimate->intimate_level;
        $oUserIntimate->intimate_value += $this->intimate_log_value;
        // 判断 亲密等级
        $levelInfo                     = LevelConfig::getLevelInfo($oUserIntimate->intimate_value, LevelConfig::LEVEL_TYPE_INTIMATE);
        $this->intimate_log_level      = $oUserIntimate->intimate_level = $levelInfo['level'];
        $this->intimate_log_level_name = $oUserIntimate->intimate_level_name = $levelInfo['level_name'];

        $oUserIntimate->save();
        if($oldLevel < $levelInfo['level'] ){
            // 等级有所提升 IM 通知亲密等级提升
            $timServer = self::getTimServer();
            $timServer->setUid([
                $this->intimate_log_user_id,
                $this->intimate_log_anchor_user_id
            ]);
            $timServer->sendIntimateLevelUpBatch([
                'intimate_user_id'        => $this->intimate_log_user_id,
                'intimate_anchor_user_id' => $this->intimate_log_anchor_user_id,
                'intimate_level'          => $levelInfo['level'],
                'intimate_level_name'     => $levelInfo['level_name'],
                'intimate_value'          => $oUserIntimate->intimate_value,
            ]);
        }

        // 存储 排行榜
        $oIntimateService = new IntimateService($this->intimate_log_anchor_user_id,$this->intimate_log_user_id);
        $oIntimateService->save(intval($this->intimate_log_value),$levelInfo['level'],$levelInfo['level_name'],$oUserIntimate->intimate_value);

    }

    public function beforeUpdate()
    {
        $this->intimate_log_update_time = time();
    }


}