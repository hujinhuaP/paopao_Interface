<?php

namespace app\models;

/**
* UserGuideLog 诱导记录表
*/
class UserGuideLog extends ModelBase
{

    /** @var string 视频诱导 */
    const TYPE_VIDEO = 'video';
    /** @var string 首页停留 */
    const TYPE_STAY_INDEX = 'stay_index';
    /** @var string 个人主页停留 */
    const TYPE_STAY_PROFILE = 'stay_profile';

    public $guide_user_id;
    public $guide_anchor_user_id;
    public $guide_type;
    public $guide_config_id;
    public $guide_create_time = 0;
    public $guide_update_time = 0;

    public function beforeCreate()
    {
        $this->guide_update_time = time();
        $this->guide_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->guide_update_time = time();
    }
}