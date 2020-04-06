<?php

namespace app\models;

/**
 * UserViewLog 用户访客记录表
 */
class UserViewLog extends ModelBase
{
    public $user_view_id;
    public $user_view_user_id;
    public $user_viewed_user_id;
    public $user_view_count = 0;
    public $user_view_today_count = 0;
    public $user_view_create_time;
    public $user_view_update_time;


    public function beforeCreate()
    {
        $this->user_view_update_time = time();
        $this->user_view_create_time = time();
    }

    public function beforeUpdate()
    {
        $this->user_view_update_time = time();
    }

    public function getTodayStat(int $nUserId) {

        $sql = "select count(1) as user_count,sum(user_view_today_count) as view_count FROM user_view_log where user_viewed_user_id = :user_viewed_user_id AND user_view_today_count > 0 AND user_view_create_time >= :user_view_create_time";
        $connetion = $this->getReadConnection();
        $result = $connetion->query($sql,[
            'user_viewed_user_id' => $nUserId,
            'user_view_create_time' => strtotime('today')
        ])->fetch();

        return [
            'user_count' => $result['user_count'] ?? 0,
            'view_count' => $result['view_count'] ?? 0,
        ];

    }


}
