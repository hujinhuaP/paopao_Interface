<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;
use app\live\library\Redis;

/**
 * 私聊
 */
class UserPrivateChatLog extends Model
{

    private $_matching_key = 'match_center_user_list';

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     * 匹配中的用户列表
     */
    public  function getMatchingUser($offset = 0,$limit = 20)
    {
        $oRedis = new Redis();
        $data = $oRedis->zRangeByScore($this->_matching_key, time() - 15, time() + 2,['withscores' => TRUE,'limit' => [$offset,$limit]]);
        $result = [];
        foreach ( $data as $data_item => $data_score ) {
            $tmp = json_decode($data_item,true);
            $tmp['add_time'] = $data_score;
            $tmp['duration'] = time() - $data_score;
            $result[] = $tmp;
        }
        return $result;
    }

    /**
     * @return int
     * 匹配中的用户数据总数
     */
    public function getMatchingUserCount()
    {
        $oRedis = new Redis();
        return $oRedis->zCount($this->_matching_key,time() - 15, time() + 2);
    }


    /**
     * @return \think\model\relation\BelongsTo
     * 邀请者
     */
    public function user()
    {
        return $this->belongsTo('user','chat_log_user_id','user_id',[],'inner')->setEagerlyType(0);
    }


    /**
     * @return \think\model\relation\BelongsTo
     * 被邀请者
     */
    public function anchorUser()
    {
        return $this->belongsTo('user','chat_log_anchor_user_id','user_id',[],'inner')->setEagerlyType(0);
    }


    /**
     * @return \think\model\relation\BelongsTo
     * 付费
     */
    public function userChatPay()
    {
        return $this->belongsTo('user_chat_pay','id','chat_log_id',[],'left')->setEagerlyType(0);
    }
}
