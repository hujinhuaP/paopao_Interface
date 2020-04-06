<?php

namespace app\models;

/**
 * User 用户
 */
class User extends ModelBase
{
    /** @type string 手机登录 */
    const LOGIN_TYPE_PHONE = 'phone';
    /** @type string QQ登录 */
    const LOGIN_TYPE_QQ = 'qq';
    /** @type string 微信登录 */
    const LOGIN_TYPE_WX = 'wx';
    /** @type string 微博登录 */
    const LOGIN_TYPE_WB = 'wb';

    /** @type string 手机注册 */
    const REGISTER_TYPE_PHONE = 'phone';
    /** @type string QQ注册 */
    const REGISTER_TYPE_QQ = 'qq';
    /** @type string 微信注册 */
    const REGISTER_TYPE_WX = 'wx';
    /** @type string 微博注册 */
    const REGISTER_TYPE_WB = 'wb';

    /** @type string 在线状态-在线 */
    const USER_ONLINE_STATUS_ONLINE = 'Online';
    /** @type string 在线状态-后台 */
    const USER_ONLINE_STATUS_PUSHONLINE = 'PushOnline';
    /** @type string 在线状态-离线 */
    const USER_ONLINE_STATUS_OFFLINE = 'Offline';
    /** @var string 神秘人头像 */
    const SECRET_AVATAR              = 'https://lebolive-1255651273.image.myqcloud.com/static/images/hide_avatar.png';

    /** @var string 缓存的key */
    protected static $_key;

    public $user_id;
    public $user_nickname;
    public $user_avatar;
    public $user_login_type;
    public $user_register_type;
    public $user_sex;
    public $user_coin;
    public $user_total_coin;
    public $user_free_coin;
    public $user_total_free_coin;
    public $user_consume_total;
    public $user_consume_free_total;
    public $user_budan_total;
    public $user_share_times;
    public $user_is_first_device;
    public $user_collect_total;
    public $user_collect_free_total;
    public $user_dot;
    public $user_intro;
    public $user_constellation;
    public $user_birth;
    public $user_lat;
    public $user_lng;
    public $user_invite_code;
    public $user_invite_dot_total;
    public $user_invite_coin_total;
    public $user_invite_total;
    public $user_invite_effective_total;
    public $user_follow_total;
    public $user_fans_total;
    public $user_is_certification;
    public $user_is_superadmin;
    public $user_is_isrobot;
    public $user_is_forbid;
    public $user_is_deny_speak;
    public $user_is_anchor;
    public $user_remind;
    public $user_login_ip;
    public $user_register_ip;
    public $user_login_time;
    public $user_logout_time;
    public $user_register_time;
    public $user_update_time;
    public $user_create_time;
    public $user_img;
    public $user_video_cover;
    public $user_video;
    public $user_home_town;
    public $user_hobby;
    public $user_profession;
    public $user_emotional_state;
    public $user_online_status;
    public $user_group_id;
    public $user_invite_user_id;
    public $user_invite_agent_id;
    public $user_member_expire_time;
    public $user_customer_id;
    public $user_income;
    public $user_height;
    public $user_is_photographer;
    public $user_wechat;
    public $user_wechat_price;
    public $user_free_match_time;
    public $user_cash;
    public $total_user_cash;
    public $user_invite_total_female;
    public $user_app_flg;
    public $user_level;
    public $user_exp;
    public $user_wechat_sale_count;
    public $user_viewed_count;
    public $user_v_wechat;
    public $user_v_wechat_price;
    public $user_diamond;
    public $user_enter_room_id;
    public $user_room_seat_flg;
    public $user_room_seat_voice_flg;
    public $user_vip_level;
    public $user_vip_exp;


    /**
     * @param $user_exp
     * @return mixed
     * 根据经验 获取用户等级
     */
    public static function getUserLevel( $user_exp )
    {
        $oLevelConfig = LevelConfig::findFirst([
            'level_type = :level_type: AND level_exp <= :exp:',
            'bind'  => [
                'level_type' => 'user',
                'exp'        => $user_exp
            ],
            'order' => 'level_value desc'
        ]);
        return $oLevelConfig->level_value;
    }


    public function beforeCreate()
    {
        $this->user_update_time   = time();
        $this->user_create_time   = time();
        $this->user_register_time = time();
        $this->user_login_time    = time();
        if ( $this->user_invite_user_id ) {
            // 先将有效人数 加1   在控制器出 如果判断 是重复的 则将 user_invite_effective_total 减 1
            $sql        = 'update `user` set user_invite_total = user_invite_total + 1,user_invite_effective_total = user_invite_effective_total + 1 where user_id = ' . $this->user_invite_user_id;
            $connection = $this->getWriteConnection();
            $connection->execute($sql);
        }
        if ( $this->user_invite_agent_id ) {
            //每日统计前一天时计算进入
//            $oAgent = Agent::findFirst($this->user_invite_agent_id);
//            if($oAgent){
//                // 先将有效人数 加1   在控制器出 如果判断 是重复的 则将 total_affect_register_account 减 1
//                $sql        = "update `agent` set register_count = register_count + 1,total_register_count = total_register_count + 1,affect_register_count = affect_register_count + 1,total_affect_register_count = total_affect_register_count + 1 where id = " . $this->user_invite_agent_id;
//                $connection->execute($sql);
//
//                if($oAgent->first_leader){
//                    $sql        = "update `agent` set total_register_count = total_register_count + 1,total_affect_register_count = total_affect_register_count + 1 where id = " . $oAgent->first_leader;
//                    $connection->execute($sql);
//
//                    if($oAgent->second_leader){
//                        $sql        = "update `agent` set total_register_count = total_register_count + 1,total_affect_register_count = total_affect_register_count + 1 where id = " . $oAgent->second_leader;
//                        $connection->execute($sql);
//                    }
//                }
//            }
        }
        if ( !$this->user_avatar ) {
            $this->user_avatar = 'http://cskj-1257854899.file.myqcloud.com/static/paopao.png';
        }
    }

    public function beforeUpdate()
    {
        $this->user_update_time = time();
    }

    public function afterSave()
    {
        $this->getDI()
            ->getShared("modelsCache")
            ->delete(static::$_key);
    }

    public function afterDelete()
    {
        $this->getDI()
            ->getShared("modelsCache")
            ->delete(static::$_key);
    }

    /**
     * @param null $parameters
     * @return User
     */
    public static function findFirst( $parameters = NULL )
    {
        // Convert the parameters to an array
        if ( !is_array($parameters) ) {
            $parameters = [ $parameters ];
        }

        static::$_key = self::_createKey($parameters);

        // Check if a cache key wasn't passed
        // and create the cache parameters
        // if (!isset($parameters['cache'])) {
        //     $parameters['cache'] = [
        //         'key'      => static::$_key,
        //         'lifetime' => 60*60*24,
        //     ];
        // } elseif ($parameters['cache'] == false) {
        // 	unset($parameters['cache']);
        // }
        return parent::findFirst($parameters);
    }

    public static function find( $parameters = NULL )
    {
        // Convert the parameters to an array
        if ( !is_array($parameters) ) {
            $parameters = [ $parameters ];
        }

        static::$_key = self::_createKey($parameters);

        // Check if a cache key wasn't passed
        // and create the cache parameters
        // if (!isset($parameters['cache'])) {
        //     $parameters['cache'] = [
        //         'key'      => static::$_key,
        //         'lifetime' => 60,
        //     ];
        // } elseif ($parameters['cache'] == false) {
        // 	unset($parameters['cache']);
        // }

        return parent::find($parameters);
    }

    /**
     * Implement a method that returns a string key based
     * on the query parameters
     */
    protected static function _createKey( $parameters )
    {
        $uniqueKey = [];
        unset($parameters['cache']);
        foreach ( $parameters as $key => $value ) {
            if ( is_scalar($value) ) {
                $uniqueKey[] = $key . ':' . $value;
            } else if ( is_array($value) ) {
                $uniqueKey[] = $key . ':[' . self::_createKey($value) . ']';
            }
        }

        return 'user:data:' . join(',', $uniqueKey);
    }

    public function getMatchCenterStr()
    {
        // 判断当前用户是否聊过天
        $saveStr = json_encode([
            'user_id'          => $this->user_id,
            'user_nickname'    => $this->user_nickname,
            'user_avatar'      => $this->user_avatar,
            'user_level'       => $this->user_level,
            'user_sex'         => $this->user_sex,
            'user_birth'       => $this->user_birth,
            'user_has_consume' => $this->user_consume_free_total > 0 ? 'Y' : 'N'
        ]);
        return $saveStr;
    }

    /**
     * @param int $number
     * 减少用户的有效邀请人数
     */
    public function decUserInviteEffectiveTotal( $sUserId, $number = 1 )
    {
        $sql        = "update `user` set user_invite_effective_total = user_invite_effective_total - $number where user_id = " . $sUserId;
        $connection = $this->getWriteConnection();
        $connection->execute($sql);
        return $connection->affectedRows();
    }

    /**
     * @param $anchor_chat_price
     */
    public function getVip1V1VideoPrice( $anchor_chat_price )
    {
        if ( $this->user_member_expire_time > time() ) {
            $vipInfo = VipLevel::getVipInfo($this->user_vip_level);
            $anchor_chat_price   = sprintf('%.2f', $anchor_chat_price * $vipInfo->vip_level_video_chat_discount / 10);
        }
        return $anchor_chat_price;
    }
}