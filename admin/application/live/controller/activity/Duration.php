<?php

namespace app\live\controller\activity;

use app\live\library\Redis;
use app\live\model\live\User;
use think\Exception;
use app\common\controller\Backend;

/**
 * 活动时长活动
 */
class Duration extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $redisKey = 'activity:anchor_chat_time:all';
            $filter = $this->request->get("filter", '');
            $user_id = 0;
            if($filter){
                $filterData = json_decode($filter, TRUE);
                if(isset($filterData['user_id'])){
                    $user_id = $filterData['user_id'];
                }
            }
            if($user_id){
                // 查看该用户的数据
                $total = 1;
                $oRedis = new Redis();
                $rankFlg = $oRedis->zRevRank($redisKey,$user_id);
                if($rankFlg === false){
                    $total = 0;
                    $rank = [];
                }else{
                    $oUser = User::get($user_id);
                    $rank[] = [
                        'rank_flg'      => $rankFlg + 1,
                        'duration'      => $oRedis->zScore($redisKey,$user_id),
                        'user_id'       => $user_id,
                        'user_nickname' => $oUser->user_nickname,
                        'user_avatar'   => $oUser->user_avatar,
                    ];
                }
            }else{

                $offset = $this->request->get("offset", 0);
                $limit = $this->request->get("limit", 12);
                $rankFlg = $offset + 1;
                $startS = $offset;
                $endS = $offset + $limit - 1;
                $oRedis = new Redis();
                $total  = intval($oRedis->zCard($redisKey));
                $data       = $oRedis->zRevRange($redisKey, $startS, $endS, TRUE);
                $userIds    = [];
                $rankSource = [];
                $rank       = [];
                foreach ( $data as $user_id => $item ) {
                    $userIds[]            = $user_id;
                    $rankSource[$user_id] = [
                        'rank_flg' => $rankFlg,
                        'user_id'  => $user_id,
                        'duration' => $item
                    ];
                    $rankFlg ++;
                }
                if ( $userIds ) {
                    $user_id_str = implode(',', $userIds);
                    $oUser = new User();
                    $userResult = $oUser->query('select user_id,user_nickname,user_avatar from user where user_id in (' . $user_id_str . ')');
                    $userSource = [];
                    foreach ( $userResult as $userItem ) {
                        $userSource[$userItem['user_id']] = $userItem;
                    }
                    foreach ( $rankSource as $rankItem ) {
                        if ( !isset($userSource[$rankItem['user_id']]) ) {
                            continue;
                        }
                        $userItem = $userSource[$rankItem['user_id']];
                        $rank[]   = [
                            'rank_flg'      => $rankItem['rank_flg'],
                            'duration'      => $rankItem['duration'],
                            'user_id'       => $rankItem['user_id'],
                            'user_nickname' => $userItem['user_nickname'],
                            'user_avatar'   => $userItem['user_avatar'],
                        ];
                    }

                }
            }
            $list   = $rank;
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    public function detail($ids = 0)
    {
        $anchor_id = $ids;
        if ( $this->request->isAjax() ) {
            $startDate = strtotime('2019-02-04');
            $endDate = strtotime('2019-02-17');
            if (time() < $endDate + 3600 * 24 - 1) {
                $endDate = strtotime(date('Y-m-d'));
            }
            // 从数据库中查出统计数据   两天前的统计
            $oUser = new User();
            $userResult = $oUser->query('select stat_time,user_id,time_income,gift_income,video_income,word_income,chat_game_income,guard_income 
from anchor_stat where user_id = '.$anchor_id . ' AND stat_time >= ' . $startDate . ' AND stat_time <= '. $endDate);
            $userSource = [];
            foreach ( $userResult as $userItem ) {
                $statKey = date('Ymd',$userItem['stat_time']);
                $userSource[$statKey] = $userItem;
            }
            $result = [];
            $oRedis = new Redis();
            for ($tmp = $endDate; $tmp >= $startDate; $tmp -= 3600 * 24) {
                $dateKey = date('Ymd', $tmp);
                $duration = $oRedis->get(sprintf('activity:anchor_chat_time:%s:%s', $dateKey, $anchor_id));
                if ($duration === FALSE) {
                    $duration = 0;
                }
                $anchorStat = $oRedis->hGetAll(sprintf('anchor:stat:%s:%s', $dateKey, $anchor_id));
                if(!$anchorStat){
                    $anchorStat = $userSource[$dateKey];
                }
                $time_income = $anchorStat['time_income'] ?? 0;
                $gift_income = $anchorStat['gift_income'] ?? 0;
                $video_income = $anchorStat['video_income'] ?? 0;
                $chat_game_income = $anchorStat['chat_game_income'] ?? 0;
                $guard_income = $anchorStat['guard_income'] ?? 0;
                $word_income = $anchorStat['word_income'] ?? 0;
                $result[] = [
                    'date_flg' => date('Y-m-d', $tmp),
                    'duration' => $duration,
                    'income' => $time_income + $gift_income + $video_income + $chat_game_income + $guard_income + $word_income
                ];
            }
            $result = [
                "total" => count($result),
                "rows" => $result
            ];
            return json($result);
        }
        $this->view->assign('user_id',$anchor_id);
        return $this->view->fetch();
    }



}