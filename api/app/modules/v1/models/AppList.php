<?php

namespace app\models;

use app\services\AppListService;

/**
 * AppList APP列表
 */
class AppList extends ModelBase
{
    /** @var int iOS 视频通话跳转聊天 */
    const PUBLISH_IOS_CHAT = 0;
    /** @var int 离线主播隐藏 */
    const PUBLISH_HIDE_OFFLINE_ANCHOR = 1;
    /** @var int 不能修改昵称 个性签名 封面标题 */
    const PUBLISH_CAN_NOT_CHANGE_PROFILE = 2;
    /** @var int 苹果内购 */
    const PUBLISH_RECHARGE = 3;
    /** @var int 显示审核主播/视频 */
    const EXAMINE_ANCHOR = 4;

    public function beforeCreate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
        $oAppListService = new AppListService($this->app_flg);
        $oAppListService->delete();
    }

}