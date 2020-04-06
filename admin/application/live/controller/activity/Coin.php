<?php

namespace app\live\controller\activity;

use app\live\library\Redis;
use app\live\model\live\User;
use think\Exception;
use app\common\controller\Backend;

/**
 * 金币
 */
class Coin extends Backend
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
        if ($this->request->isAjax()) {
            $redisKey = 'activity:user_coin:all';
            $filter = $this->request->get("filter", '');
            $user_id = 0;
            if ($filter) {
                $filterData = json_decode($filter, TRUE);
                if (isset($filterData['user_id'])) {
                    $user_id = $filterData['user_id'];
                }
            }
            if ($user_id) {
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
                        'use_coin'      => $oRedis->zScore($redisKey,$user_id),
                        'user_id'       => $user_id,
                        'user_nickname' => $oUser->user_nickname,
                        'user_avatar'   => $oUser->user_avatar,
                    ];
                }
            } else {

                $offset = $this->request->get("offset", 0);
                $limit = $this->request->get("limit", 12);
                $rankFlg = $offset + 1;
                $startS = $offset;
                $endS = $offset + $limit - 1;
                $oRedis = new Redis();
                $total = intval($oRedis->zCard($redisKey));
                $data = $oRedis->zRevRange($redisKey, $startS, $endS, TRUE);
                $userIds = [];
                $rankSource = [];
                $rank = [];
                foreach ($data as $user_id => $item) {
                    $userIds[] = $user_id;
                    $rankSource[$user_id] = [
                        'rank_flg' => $rankFlg,
                        'user_id' => $user_id,
                        'use_coin' => $item
                    ];
                    $rankFlg++;
                }
                if ($userIds) {
                    $user_id_str = implode(',', $userIds);
                    $oUser = new User();
                    $userResult = $oUser->query('select user_id,user_nickname,user_avatar from user where user_id in (' . $user_id_str . ')');

                    $userSource = [];
                    foreach ($userResult as $userItem) {
                        $userSource[$userItem['user_id']] = $userItem;
                    }
                    foreach ($rankSource as $rankItem) {
                        if (!isset($userSource[$rankItem['user_id']])) {
                            continue;
                        }
                        $userItem = $userSource[$rankItem['user_id']];
                        $rank[] = [
                            'rank_flg' => $rankItem['rank_flg'],
                            'use_coin' => $rankItem['use_coin'],
                            'user_id' => $rankItem['user_id'],
                            'user_nickname' => $userItem['user_nickname'],
                            'user_avatar' => $userItem['user_avatar'],
                        ];
                    }
                }
            }
            $list = $rank;
            $result = [
                "total" => $total,
                "rows" => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }


}