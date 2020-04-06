<?php

namespace app\models;

/**
 * UserGiftLog 用户送礼日志表
 */
class UserGiftLog extends ModelBase
{
    const RANK_COIN_PREFIX = 'room_coin_rank';

    /**
     * @param $user_id
     * @param $room_seat_number
     * @param $room_seat_heart_stat_start
     * @return int
     * 获取用户相应的
     */
    public static function getHeartValue( $user_id, $room_seat_number, $room_seat_heart_stat_start )
    {
        return self::sum([
                'anchor_user_id = :anchor_user_id: AND room_seat_number = :room_seat_number: AND user_gift_log_create_time > :user_gift_log_create_time:',
                'bind'   => [
                    'anchor_user_id'            => $user_id,
                    'room_seat_number'          => $room_seat_number,
                    'user_gift_log_create_time' => $room_seat_heart_stat_start
                ],
                'column' => 'consume_coin + consume_free_coin'
            ]) ?? 0;
    }

    public function beforeCreate()
    {
        $this->user_gift_log_update_time = time();
        $this->user_gift_log_create_time = time();

        if ( $this->room_id ) {
            // 添加语聊房 房间 周榜  金主榜
            $redis       = self::getRedis();
            $weekCoinKey = $this->_getRoomCoinRankKey('week', $this->room_id);
            if ( $redis->exists($weekCoinKey) ) {
                // 存在则直接添加
                $redis->zIncrBy($weekCoinKey, intval($this->consume_coin + $this->consume_free_coin), $this->user_id);
                $redis->expire($weekCoinKey, 3600);
            }
        }
    }

    public function beforeUpdate()
    {
        $this->user_gift_log_update_time = time();
    }

    private function _getRoomCoinRankKey( $category, $nRoomId )
    {
        if ( $category == 'day' ) {
            return sprintf('%s:%s:%s:%s', self::RANK_COIN_PREFIX, $category, $nRoomId, date('Ymd'));
        } else {
            return sprintf('%s:%s:%s:%s', self::RANK_COIN_PREFIX, $category, $nRoomId, date('oW'));
        }

    }


    /**
     * @param $category
     * @param $nRoomId
     * 获取排行榜
     * 先从缓存中取  如果没有则从数据库中取 并存入缓存
     */
    public function getRoomSendCoinRank( $category, $nRoomId )
    {
        if ( $category == 'day' ) {
            $lastTime = strtotime('today');
        } else {
            $lastTime = strtotime(date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));
        }
        $groupSql   = 'select u.user_id,u.user_nickname,u.user_avatar,u.user_sex,t.total_coin,u.user_level
from (select user_id,sum(consume_coin + consume_free_coin) as total_coin,max(user_gift_log_create_time) as last_time FROM `yuyin_live`.user_gift_log 
where user_gift_log_create_time >= :last_time AND room_id = :room_id group by user_id order by total_coin desc,last_time asc) as t 
    inner join `yuyin_live`.`user` as u on t.user_id = u.user_id';
        $connection = $this->getReadConnection();
        $result     = $connection->query($groupSql, [
            'last_time' => $lastTime,
            'room_id'   => $nRoomId
        ]);
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $data = $result->fetchAll();
        if ( $category == 'week' ) {
            $key   = $this->_getRoomCoinRankKey($category, $nRoomId);
            $redis = self::getRedis();

            if ( !$redis->exists($key) ) {
                // 从数据库取数据
                $redis->zAdd($key, -1, -1);
                foreach ( $data as $item ) {
                    $redis->zAdd($key, $item['total_coin'], $item['user_id']);
                }
                $redis->expire($key, 3600);
            }
        }
        return $data;


    }

    /**
     * @param $nUserId
     * @param $nRoomId
     * @return bool|int
     * 获取用户房间送礼周排名
     */
    public function getSendCoinWeekRankIndex( $nUserId, $nRoomId )
    {
        $key   = $this->_getRoomCoinRankKey('week', $nRoomId);
        $redis = self::getRedis();
        if ( !$redis->exists($key) ) {
            $lastTime   = strtotime(date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));
            $groupSql   = 'select u.user_id,u.user_nickname,u.user_avatar,u.user_sex,t.total_coin 
from (select user_id,sum(consume_coin + consume_free_coin) as total_coin FROM `yuyin_live`.user_gift_log 
where user_gift_log_create_time >= :last_time AND room_id = :room_id group by user_id order by total_coin desc) as t 
    inner join `yuyin_live`.`user` as u on t.user_id = u.user_id';
            $connection = $this->getReadConnection();
            $result     = $connection->query($groupSql, [
                'last_time' => $lastTime,
                'room_id'   => $nRoomId
            ]);
            $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $data = $result->fetchAll();
            // 从数据库取数据
            $redis->zAdd($key, -1, -1);
            foreach ( $data as $item ) {
                $redis->zAdd($key, $item['total_coin'], $item['user_id']);
            }
            $redis->expire($key, 3600);
        }
        $rank = $redis->zRevRank($key, $nUserId);
        if ( $rank !== FALSE ) {
            $rank += 1;
        }
        return $rank;

    }


    /**
     * @param $category
     * @param $nRoomId
     * @return array
     * 房间收礼金币榜
     */
    public function getRoomGetCoinRank( $category, $nRoomId )
    {
        if ( $category == 'day' ) {
            $lastTime = strtotime('today');
        } else {
            $lastTime = strtotime(date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));
        }
        $groupSql   = 'select u.user_id,u.user_nickname,u.user_avatar,u.user_sex,t.total_coin,u.user_level
from (select anchor_user_id as user_id,sum(consume_coin + consume_free_coin) as total_coin FROM `yuyin_live`.user_gift_log 
where user_gift_log_create_time >= :last_time AND room_id = :room_id group by anchor_user_id order by total_coin desc) as t 
    inner join `yuyin_live`.`user` as u on t.user_id = u.user_id';
        $connection = $this->getReadConnection();
        $result     = $connection->query($groupSql, [
            'last_time' => $lastTime,
            'room_id'   => $nRoomId
        ]);
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $data = $result->fetchAll();
        return $data;
    }


}