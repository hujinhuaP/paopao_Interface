<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends BaseController
{

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/activity/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function indexAction($user = 0)
    {
        $anchorUserId = 0;
        $userId = 0;
        if($user){
            $userResult  = $this->db->fetchRow('select user_id,user_is_anchor from user where user_id =' . intval($user));
            if($userResult){
                if($userResult['user_is_anchor'] == 'Y'){
                    $anchorUserId = $user;
                }else{
                    $userId = $user;
                }
            }
        }
        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        $this->getView()->assign("user_id", $userId);
        $this->getView()->assign("anchor_user_id", $anchorUserId);
        return TRUE;
    }

    /**
     * 规则
     */
    public function ruleAction()
    {
        return TRUE;
    }

    /**
     * 奖励
     */
    public function rewardAction()
    {
        return TRUE;
    }

    /**
     * @param int $anchor_id 主播用户id
     *
     */
    public function anchorAction($anchor_id = 0)
    {
        $anchorRankData = $this->redis->get('activity_rank:anchor');
        if ( $anchorRankData === FALSE ) {
            $data       = $this->redis->zRevRange('activity:anchor_chat_time:all', 0, 9, TRUE);
            $userIds    = [];
            $rankSource = [];
            $rank       = [];
            foreach ( $data as $user_id => $item ) {
                $userIds[]            = $user_id;
                $rankSource[$user_id] = [
                    'user_id'  => $user_id,
                    'duration' => $item
                ];
            }
            if ( $userIds ) {
                $user_id_str = implode(',', $userIds);
                $userResult  = $this->db->fetchAll('select user_id,user_nickname,user_avatar from user where user_id in (' . $user_id_str . ')');

                $userSource = [];
                foreach ( $userResult as $userItem ) {
                    $userSource[$userItem['user_id']] = $userItem;
                }
                foreach ( $rankSource as $rankItem ) {
                    if ( !isset($userSource[$rankItem['user_id']]) ) {
                        continue;
                    }
                    $userItem = $userSource[$rankItem['user_id']];
                    $flg      = mb_strlen($userItem['user_nickname']) > 6 ? '...' : '';
                    $rank[]   = [
                        'duration'      => $this->formatDuration($rankItem['duration']),
                        'user_id'       => $rankItem['user_id'],
                        'user_nickname' => mb_substr($userItem['user_nickname'], 0, 6) . $flg,
                        'user_avatar'   => $userItem['user_avatar'],
                    ];
                }

            }
            if ( count($rank) < 10 ) {
                for ( $i = count($rank); $i < 10; $i++ ) {
                    array_push($rank, [
                        'duration'      => '--',
                        'user_id'       => 0,
                        'user_nickname' => '--',
                        'user_avatar'   => 'http://lebolive-1255651273.file.myqcloud.com/static/activity/images/pig.png'
                    ]);
                }
            }
            $this->redis->set('activity_rank:anchor', json_encode($rank));
            $this->redis->expire('activity_rank:anchor', '60');
        } else {
            $rank = json_decode($anchorRankData, TRUE);
        }

        $myDuration = '-';
        $myRank     = '100名以外';
        if ( $anchor_id ) {
            $myDurationFlg = $this->redis->zScore('activity:anchor_chat_time:all', $anchor_id);
            if ( $myDurationFlg !== FALSE ) {
                $myDuration = $this->formatDuration($myDurationFlg);
            }
            $rankFlg = $this->redis->zRevRank('activity:anchor_chat_time:all', $anchor_id);
            if ( $rankFlg !== FALSE ) {
                if ( $rankFlg < 101 ) {
                    $myRank = sprintf('第%d名', $rankFlg + 1);
                }
            }
        }

        $this->getView()->assign('my_rank', [
            'duration' => $myDuration,
            'rank'     => $myRank
        ]);
        $this->getView()->assign('rank', $rank);
        $this->getView()->assign("user_id", $anchor_id);
        return TRUE;
    }

    private function formatDuration($duration = 0)
    {
        $secondTime = intval($duration);
        $minuteTime = 0;
        $hourTime   = 0;
        if ( $duration > 60 ) {
            $minuteTime = intval($secondTime / 60);
            $secondTime = intval($secondTime % 60);

            if ( $minuteTime > 60 ) {
                $hourTime   = intval($minuteTime / 60);
                $minuteTime = intval($minuteTime % 60);
            }
        }
        return sprintf('%d时%d分%d秒', $hourTime, $minuteTime, $secondTime);
    }


    public function userAction($user = 0)
    {
        $userRankData = $this->redis->get('activity_rank:user');
        if ( $userRankData === FALSE ) {
            $data       = $this->redis->zRevRange('activity:user_coin:all', 0, 9, TRUE);
            $userIds    = [];
            $rankSource = [];
            $rank       = [];
            foreach ( $data as $user_id => $item ) {
                $userIds[]            = $user_id;
                $rankSource[$user_id] = [
                    'user_id'  => $user_id,
                    'use_coin' => $item
                ];
            }
            if ( $userIds ) {
                $user_id_str = implode(',', $userIds);
                $userResult  = $this->db->fetchAll('select user_id,user_nickname,user_avatar from user where user_id in (' . $user_id_str . ')');

                $userSource = [];
                foreach ( $userResult as $userItem ) {
                    $userSource[$userItem['user_id']] = $userItem;
                }
                foreach ( $rankSource as $rankItem ) {
                    if ( !isset($userSource[$rankItem['user_id']]) ) {
                        continue;
                    }
                    $userItem = $userSource[$rankItem['user_id']];
                    $flg      = mb_strlen($userItem['user_nickname']) > 6 ? '...' : '';
                    $rank[]   = [
                        'use_coin'      => $rankItem['use_coin'],
                        'user_id'       => $rankItem['user_id'],
                        'user_nickname' => mb_substr($userItem['user_nickname'], 0, 6) . $flg,
                        'user_avatar'   => $userItem['user_avatar'],
                    ];
                }

            }
            if ( count($rank) < 10 ) {
                for ( $i = count($rank); $i < 10; $i++ ) {
                    array_push($rank, [
                        'use_coin'      => '--',
                        'user_id'       => 0,
                        'user_nickname' => '--',
                        'user_avatar'   => 'http://lebolive-1255651273.file.myqcloud.com/static/activity/images/pig.png'
                    ]);
                }
            }
            $this->redis->set('activity_rank:user', json_encode($rank));
            $this->redis->expire('activity_rank:user', '60');
        } else {
            $rank = json_decode($userRankData, TRUE);
        }
        $myUseCoin = '0';
        $myRank    = '100名以外';
        if ( $user ) {
            $myDurationFlg = $this->redis->zScore('activity:user_coin:all', $user);
            if ( $myDurationFlg !== FALSE ) {
                $myUseCoin = $myDurationFlg;
            }
            $rankFlg = $this->redis->zRevRank('activity:user_coin:all', $user);
            if ( $rankFlg !== FALSE ) {
                if ( $rankFlg < 101 ) {
                    $myRank = sprintf('第%d名', $rankFlg + 1);
                }
            }
        }

        $this->getView()->assign('my_rank', [
            'use_coin' => $myUseCoin,
            'rank'     => $myRank
        ]);
        $this->getView()->assign('rank', $rank);
        $this->getView()->assign("user_id", $user);
        return TRUE;
    }

    /**
     * @param int $anchor_id
     * 主播每天记录
     * 头一天和当天从缓存中取 收益数据  其他从数据库取数据
     */
    public function logAction($anchor_id = 0)
    {
        $startDate = strtotime('2019-02-04');
        if($anchor_id == 11111){
            $startDate = strtotime('2019-01-27');
            $anchor_id = 66748362;
        }
        $endDate   = strtotime('2019-02-17');
        if ( time() < $endDate + 3600 * 24 - 1 ) {
            $endDate = strtotime(date('Y-m-d'));
        }
        $userResult = $this->db->fetchAll('select stat_time,user_id,time_income,gift_income,video_income,word_income,chat_game_income,guard_income 
from anchor_stat where user_id = '.$anchor_id . ' AND stat_time >= ' . $startDate . ' AND stat_time <= '. $endDate);
        $userSource = [];
        foreach ( $userResult as $userItem ) {
            $statKey = date('Ymd',$userItem['stat_time']);
            $userSource[$statKey] = $userItem;
        }
        $result = [];
        for ( $tmp = $endDate; $tmp >= $startDate; $tmp -= 3600 * 24 ) {
            $dateKey = date('Ymd', $tmp);
            $duration = $this->redis->get(sprintf('activity:anchor_chat_time:%s:%s', $dateKey, $anchor_id));
            if ( $duration === FALSE ) {
                $duration = 0;
            }
            $anchorStat       = $this->redis->hGetAll(sprintf('anchor:stat:%s:%s', $dateKey, $anchor_id));
            if(!$anchorStat){
                $anchorStat = $userSource[$dateKey];
            }
            $time_income      = $anchorStat['time_income'] ?? 0;
            $gift_income      = $anchorStat['gift_income'] ?? 0;
            $video_income     = $anchorStat['video_income'] ?? 0;
            $chat_game_income = $anchorStat['chat_game_income'] ?? 0;
            $guard_income     = $anchorStat['guard_income'] ?? 0;
            $word_income      = $anchorStat['word_income'] ?? 0;
            $result[]         = [
                'date_flg' => date('y.m.d', $tmp),
                'duration' => $this->formatDuration($duration),
                'income'   => $time_income + $gift_income + $video_income + $chat_game_income + $guard_income + $word_income
            ];
        }
        $this->getView()->assign('result', $result);
        return TRUE;
    }
}
