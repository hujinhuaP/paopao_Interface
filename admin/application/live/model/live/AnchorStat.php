<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 主播统计
 */
class AnchorStat extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function anchor()
    {
        return $this->belongsTo('anchor','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function groupData($statStartTime,$endStartTime,$offset=0,$limit=20,$sort = 'normal_chat_call_times',$order = 'DESC',$nUserId=0,$groupId=0,$userNickname='',$isHot='',$isNewRecord='')
    {
        if(empty($statStartTime) || empty($endStartTime)){
            return [];
        }
        $endStartTime += 3600 * 24 - 1;
        $innerWhereArr = [];
        $outWhereArr = [];
        $innerWhereArr[] = 'create_time between '. $statStartTime . ' AND ' .$endStartTime;
        if($nUserId){
            $innerWhereArr[] = 'user_id = '. intval($nUserId);
        }
        if($groupId){
            $outWhereArr[] = 'u.user_group_id = '.intval($groupId);
        }
        if($userNickname){
            $outWhereArr[] = 'u.user_nickname like %'.$userNickname . '%';
        }

        $outWhere = '';
        if($outWhereArr ){
            $outWhere = 'WHERE ' .implode(' AND ',$outWhereArr);
        }
        $innerWhere = implode(' AND ',$innerWhereArr);

        $limitStr = '';
        if($limit != 0){
            $limitStr = "limit $limit offset $offset";
        }

        $orderStr = '';
        switch ($sort){
            case 'normal_chat_call_times':
                $orderStr = ' ORDER BY normal_chat_call_times '.$order;
                break;
            case 'normal_chat_times':
                $orderStr = ' ORDER BY normal_chat_times / normal_chat_call_times '.$order;
                break;
            case 'total_income':
                $orderStr =  ' ORDER BY total_income '.$order;
                break;
            default:

        }

        $sql = <<<SQL
select t.user_id,u.user_nickname,u.user_group_id,u.user_avatar,
a.anchor_hot_time,a.anchor_is_newhot,a.anchor_create_time,
t.normal_chat_call_times,t.normal_chat_times,t.normal_chat_duration,t.gift_income,t.time_income,t.total_income,
t.match_times,t.match_duration,t.online_duration,t.invite_recharge_income
from 
(select user_id,sum(normal_chat_call_times) as normal_chat_call_times,sum(normal_chat_times) as normal_chat_times,
sum(normal_chat_duration) as normal_chat_duration,sum(gift_income) as gift_income,sum(time_income) as time_income,sum(invite_recharge_income) as invite_recharge_income,
sum(time_income + gift_income + video_income + word_income + chat_game_income + guard_income + invite_recharge_income) as total_income,
sum(match_times) as match_times,sum(match_duration) as match_duration,sum(online_duration) as online_duration
from anchor_stat where $innerWhere group by user_id order by user_id) t 
inner join `user` as u on u.user_id = t.user_id
inner join `anchor` as a on a.user_id = t.user_id
$outWhere
$orderStr
$limitStr
SQL;
        return $this->query($sql);
    }


    public function getGroupCount($statStartTime,$endStartTime,$nUserId=0,$groupId=0,$userNickname='',$isHot='',$isNewRecord='')
    {
        if(empty($statStartTime) || empty($endStartTime)){
            return 0;
        }
        $endStartTime += 3600 * 24 - 1;
        $whereArr[] = 's.create_time between '. $statStartTime . ' AND ' .$endStartTime;
        if($nUserId){
            $whereArr[] = 's.user_id = '. intval($nUserId);
        }
        if($groupId){
            $whereArr[] = 'u.user_group_id = '.intval($groupId);
        }
        if($userNickname){
            $userNickname = utf8_encode($userNickname);
            $whereArr[] = "u.user_nickname like '%$userNickname%'";
        }

        $where = implode(' AND ',$whereArr);

        $sql = <<<SQL
select count(distinct s.user_id) as total from anchor_stat as s inner join user as u on s.user_id = u.user_id 
where {$where}
SQL;
        $result = $this->query($sql);
        return $result[0]['total'];
    }
}
