<?php 

namespace app\models;

/**
* UserMatchLog 用户匹配记录表
*/
class UserMatchLog extends ModelBase
{
    public $id;
    public $user_id;
    public $user_type;
    public $duration;
    public $match_success;
    public $anchor_user_id;
    public $anchor_type;
    public $create_time;
    public $update_time;
    public $chat_log_id;

    const USER_TYPE_NEW = 'new';
    const USER_TYPE_OLD = 'old';
    const ANCHOR_TYPE_HOT = 'hot';
    const ANCHOR_TYPE_NORMAL = 'normal';

}