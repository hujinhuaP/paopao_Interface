<?php

namespace app\models;

class RoomSeat extends ModelBase
{

    /**
     *
     * @var integer
     * @Primary
     * @Identity
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_room_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $room_seat_number;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $room_seat_voice_flg;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $room_seat_open_flg;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_count_down_end;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_heart_stat_start;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_heart_value;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_update_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_seat_like_number;


    /**
     * @param int $room_id
     * 获取房间座位信息
     */
    public static function getInfo( int $room_id )
    {
        $data = (new RoomSeat())->getModelsManager()->createBuilder()
            ->from([ 's' => RoomSeat::class ])
            ->leftJoin(User::class, 's.room_seat_user_id = u.user_id', 'u')
            ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_sex,s.room_seat_number,
            s.room_seat_voice_flg,s.room_seat_open_flg,s.room_seat_heart_value,s.room_seat_heart_stat_start')
            ->orderBy('l.user_coin_log_create_time desc')
            ->where('s.room_seat_room_id = :room_seat_room_id:', [
                'room_seat_room_id' => $room_id
            ])
            ->orderBy('s.room_seat_voice_flg')
            ->getQuery()->execute()->toArray();

        foreach ( $data as &$datum ) {
            $datum['user_id']       = $datum['user_id'] ?? '0';
            $datum['user_nickname'] = $datum['user_nickname'] ?? '';
            $datum['user_avatar']   = $datum['user_avatar'] ?? '';
            $datum['user_sex']      = $datum['user_sex'] ?? '0';
        }

        return $data;
    }

    /**
     * @param int $nUserId
     * @param int $nRoomId
     * 判断用户本周是否送过礼物
     * 如果送过则需要删除缓存
     */
    public static function checkSeatCache( int $nUserId, int $nRoomId )
    {
        $oUserGiftLog = UserGiftLog::findFirst([
            'user_id = :user_id: AND room_id = :room_id:',
            'bind' => [
                'user_id' => $nUserId,
                'room_id' => $nRoomId
            ]
        ]);
        if ( $oUserGiftLog ) {
            self::deleteRoomSeatCache($nRoomId);
        }
    }


    /**
     * @param int $nUserId
     * @param int $nRoomId
     * 删除缓存
     */
    public static function deleteRoomSeatCache( int $nRoomId )
    {
        $cacheKey = self::_getRoomSeatCacheKey($nRoomId);
        $redis    = self::getRedis();
        $redis->del($cacheKey);
    }


    private static function _getRoomSeatCacheKey( $nRoomId )
    {
        $cacheKey = sprintf('room_seat_rank:' . $nRoomId);
        return $cacheKey;
    }


    private static function _getInfoByRankByDb( $nRoomId )
    {
        $oRoom     = Room::findFirst($nRoomId);
        $hostWhere = '';
        if ( $oRoom->room_host_user_id ) {
            $hostWhere = ' AND user_id != ' . $oRoom->room_host_user_id;
        }
        $lastTime   = strtotime(date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600)));
        $groupSql   = 'select u.user_id,u.user_nickname,u.user_avatar,u.user_sex,t.total_coin,u.user_room_seat_flg,u.user_room_seat_voice_flg
from (select user_id,sum(consume_coin + consume_free_coin) as total_coin,max(user_gift_log_create_time) as last_time FROM `yuyin_live`.user_gift_log 
where user_gift_log_create_time >= :last_time AND room_id = :room_id '. $hostWhere .' group by user_id order by total_coin desc,last_time asc) as t 
    inner join `yuyin_live`.`user` as u on t.user_id = u.user_id WHERE u.user_enter_room_id = :room_id limit 4';
        $connection = (new self())->getReadConnection();
        $result     = $connection->query($groupSql, [
            'last_time' => $lastTime,
            'room_id'   => $nRoomId
        ]);
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $data = $result->fetchAll();

        $default = [
            [
                'room_seat_number'    => 1,
                'user_id'             => '0',
                'owner_user_id'       => 0,
                'user_nickname'       => '',
                'user_avatar'         => '',
                'user_sex'            => 0,
                'total_coin'          => 0,
                'user_room_seat_flg'  => 'N',
                'room_seat_voice_flg' => 'Y',
                'rank_index'          => 100,
            ],
            [
                'room_seat_number'    => 2,
                'user_id'             => '0',
                'owner_user_id'       => 0,
                'user_nickname'       => '',
                'user_avatar'         => '',
                'user_sex'            => 0,
                'total_coin'          => 0,
                'user_room_seat_flg'  => 'N',
                'room_seat_voice_flg' => 'Y',
                'rank_index'          => 100,
            ],
            [
                'room_seat_number'    => 3,
                'user_id'             => '0',
                'owner_user_id'       => 0,
                'user_nickname'       => '',
                'user_avatar'         => '',
                'user_sex'            => 0,
                'total_coin'          => 0,
                'user_room_seat_flg'  => 'N',
                'room_seat_voice_flg' => 'Y',
                'rank_index'          => 100,
            ],
            [
                'room_seat_number'    => 4,
                'user_id'             => '0',
                'owner_user_id'       => 0,
                'user_nickname'       => '',
                'user_avatar'         => '',
                'user_sex'            => 0,
                'total_coin'          => 0,
                'user_room_seat_flg'  => 'N',
                'room_seat_voice_flg' => 'Y',
                'rank_index'          => 100,
            ]
        ];
        foreach ( $data as $key => $item ) {
            $rankIndex = (new UserGiftLog())->getSendCoinWeekRankIndex($item['user_id'], $nRoomId);
            if ( $item['user_room_seat_flg'] == 'Y' ) {
                $default[ $key ] = [
                    'room_seat_number'    => $default[ $key ]['room_seat_number'],
                    'user_id'             => $item['user_id'],
                    'owner_user_id'       => $item['user_id'],
                    'user_nickname'       => $item['user_nickname'],
                    'user_avatar'         => $item['user_avatar'],
                    'owner_user_avatar'   => $item['user_avatar'],
                    'user_sex'            => $item['user_sex'],
                    'total_coin'          => $item['total_coin'],
                    'user_room_seat_flg'  => $item['user_room_seat_flg'],
                    'room_seat_voice_flg' => $item['user_room_seat_voice_flg'],
                    'rank_index'          => $rankIndex === FALSE ? 100 : $rankIndex
                ];
            } else {
                $default[ $key ]['owner_user_id']     = $item['user_id'];
                $default[ $key ]['owner_user_avatar'] = $item['user_avatar'];
            }
        }
        return $default;
    }


    /**
     * @param $nRoomId
     * @param bool $hasCache 是否从缓存取
     * @return array|mixed
     */
    public static function getInfoByRank( $nRoomId, $hasCache = TRUE )
    {
        $cacheKey = self::_getRoomSeatCacheKey($nRoomId);
        $redis    = self::getRedis();
        if ( $hasCache ) {

            if ( !$redis->exists($cacheKey) ) {

                $default = self::_getInfoByRankByDb($nRoomId);

                $redis->set($cacheKey, json_encode($default));
                $redis->expire($cacheKey, 3600);
            }
            $defaultStr = $redis->get($cacheKey);

            $default = json_decode($defaultStr, TRUE);
        } else {
            $default = self::_getInfoByRankByDb($nRoomId);
            $redis->set($cacheKey, json_encode($default));
            $redis->expire($cacheKey, 3600);
        }


        // 设置房间最后一位座位的周送出金币数
        $lastUserId = $default[4]['user_id'];
        $lastRank   = 1000;
        if ( $lastUserId ) {
            // 判断最后一个用户的排序
            $lastRank = (new UserGiftLog())->getSendCoinWeekRankIndex($lastUserId, $nRoomId);
        }
        /**
         * 进入房间  如果在座位列表里 则需要更新
         * 送礼  如果送礼后 排行高于最后一个排行 则需要更新座位
         * 离开房间  如果离开的时候高于最后一个排行  则需要更新座位
         */
        $redis       = self::getRedis();
        $lastRankKey = sprintf('room_last_seat_rank:%s', $nRoomId);
        $redis->set($lastRankKey, $lastRank);
        $redis->expire($lastRankKey, 3600);
        return $default;
    }


    /**
     * @param $nRoomId
     * @return bool|mixed|string
     * 获取最低的排行榜
     */
    public function getLastSeatRank( $nRoomId )
    {
        $redis = self::getRedis();
        $key   = sprintf('room_last_seat_rank:%s', $nRoomId);
        if ( !$redis->exists($key) ) {
            self::getInfoByRank($nRoomId);
        }
        return $redis->get($key);
    }

    /**
     * @param $nRoomId
     * @return bool|int|mixed|string
     * 获取房间座位数
     */
    public static function getSeatCount( $nRoomId )
    {
        $redis       = self::getRedis();
        $roomSeatKey = self::getCacheKey('count:' . $nRoomId);
        if ( $redis->exists($roomSeatKey) ) {
            return $redis->get($roomSeatKey);
        }
        $oRoomSeatCount = RoomSeat::count([
            'room_seat_room_id = :room_seat_room_id:',
            'bind' => [
                'room_seat_room_id' => $nRoomId
            ]
        ]);
        $seatCount      = $oRoomSeatCount ?? 0;
        $redis->set($roomSeatKey, $seatCount);
        $redis->expire($roomSeatKey, 3600);
        return $seatCount;
    }


    /**
     * @param int $nRoomId
     * @param int $nUserId
     * @param int $nSeatNumber
     * @return \app\models\RoomSeat $saveSeat  麦序
     * 普通位置上麦序
     * 判断是否有key
     *      有key
     *          从缓存麦上数加 如果数值大于8 则不能上麦
     *      没有key
     *          从数据库中取上麦数 存入缓存
     */
    public static function enter( int $nRoomId, int $nUserId, int $nSeatNumber = NULL )
    {
        $existsRoomSeat = RoomSeat::findFirst([
            'room_seat_user_id = :room_seat_user_id:',
            'bind' => [
                'room_seat_user_id' => $nUserId,
            ]
        ]);
        if ( $existsRoomSeat ) {
            // 已在麦上
            if ( $existsRoomSeat->room_seat_room_id != $nRoomId ) {
                (new RoomSeat())->leave($nRoomId, $nUserId);
            } else {
                // 已经在本房间的麦上了
                return FALSE;
            }

        }
        $saveSeat = NULL;
        if ( $nSeatNumber !== NULL ) {
            $oSelectRoomSeat = RoomSeat::findFirst([
                'room_seat_room_id = :room_seat_room_id: AND room_seat_number = :room_seat_number: AND room_seat_open_flg = "Y"',
                'bind' => [
                    'room_seat_room_id' => $nRoomId,
                    'room_seat_number'  => $nSeatNumber,
                ]
            ]);
            if ( !$oSelectRoomSeat || $oSelectRoomSeat->room_seat_user_id ) {
                return FALSE;
            }
            $saveSeat       = $oSelectRoomSeat;
            $saveSeatNumber = $nSeatNumber;
        } else {
            // 查出所有座位 判断空座位
            $firstRoomSeat = RoomSeat::findFirst([
                'room_seat_room_id = :room_seat_room_id: AND room_seat_number > 0 AND room_seat_user_id = 0 AND room_seat_open_flg = "Y"',
                'bind'  => [
                    'room_seat_room_id' => $nRoomId,
                ],
                'order' => 'room_seat_number'
            ]);
            if ( !$firstRoomSeat ) {
                return FALSE;
            }
            $saveSeat       = $firstRoomSeat;
            $saveSeatNumber = $firstRoomSeat->room_seat_number;
        }
        $saveSeat->room_seat_user_id = $nUserId;
        if ( $saveSeat->save() === FALSE ) {
            return FALSE;
        }
//        if ( $saveSeat->room_seat_number == 0 ) {
//            EnterHostLog::enter($nRoomId, $nUserId);
//        }


        return $saveSeat;
    }

    /**
     * @param int $nRoomId
     * @param int $nUserId
     * 下麦
     */
    public function leave( int $nRoomId, int $nUserId )
    {
        $oRoomSeat = RoomSeat::findFirst([
            'room_seat_room_id = :room_seat_room_id: AND room_seat_user_id = :room_seat_user_id:',
            'bind' => [
                'room_seat_room_id' => $nRoomId,
                'room_seat_user_id' => $nUserId,
            ]
        ]);
        if ( $oRoomSeat ) {
            $oRoomSeat->room_seat_user_id = 0;
            $oRoomSeat->save();
            self::deleteCache(self::getCacheKey(sprintf('%s-%s', $nRoomId, $nUserId)));
//            if ( $oRoomSeat->room_seat_number == 0 ) {
//                $flg = EnterHostLog::leave($nRoomId, $nUserId);
//            }

            return $oRoomSeat->room_seat_number;
        }
        return FALSE;
    }

    /**
     * @param int $room_id
     * 开启房间座位甜心值
     */
    public static function startHeartStat( int $room_id )
    {
        $connection = (new RoomSeat())->getWriteConnection();
        $connection->execute("update `yuyin_live`.room_seat set room_seat_heart_stat_start = :stat_start,room_seat_heart_value = 0,room_seat_heart_change_time = 0 WHERE room_seat_room_id = :room_seat_room_id", [
            'stat_start'        => time(),
            'room_seat_room_id' => $room_id
        ]);
        self::clearRoomSeatCache($room_id);
    }

    public static function clearRoomSeatCache( $nRoomId, $oRoomSeat = [] )
    {
        if ( !$oRoomSeat ) {
            $oRoomSeat = RoomSeat::find([
                'room_seat_room_id = :room_seat_room_id:',
                'bind' => [
                    'room_seat_room_id' => $nRoomId
                ]
            ])->toArray();
        }
        foreach ( $oRoomSeat as $seatItem ) {
            self::deleteCache(self::getCacheKey(sprintf('%s-%s', $seatItem['room_seat_room_id'], $seatItem['room_seat_user_id'])));
        }
    }

    /**
     * @param int $room_id
     * 获取主持位信息
     * @param int $hostUserId
     * @param RoomSeat $oRoomSeat
     * @return array
     */
    public static function getHostSeat( int $room_id, int $hostUserId, $oRoomSeat = NULL )
    {
        if ( !$oRoomSeat ) {
            $oRoomSeat = RoomSeat::findFirst([
                'room_seat_room_id = :room_seat_room_id: AND room_seat_number = 0',
                'bind' => [
                    'room_seat_room_id' => $room_id
                ]
            ]);
        }

        $oUser = NULL;
        if ( $oRoomSeat->room_seat_user_id ) {
            if( $hostUserId != $oRoomSeat->room_seat_user_id ){
                $oRoomSeat->room_seat_user_id = $hostUserId;
                $oRoomSeat->save();
            }
            $oUser = User::findFirst($oRoomSeat->room_seat_user_id);
        }
        return [
            'room_seat_number'      => '0',
            'user_id'               => $oRoomSeat->room_seat_user_id,
            'user_nickname'         => $oUser ? $oUser->user_nickname : '',
            'user_avatar'           => $oUser ? $oUser->user_avatar : '',
            'user_sex'              => $oUser ? $oUser->user_sex : '0',
            'room_seat_heart_value' => $oRoomSeat->room_seat_heart_value,
            'user_room_seat_flg'    => $oUser ? 'Y' : 'N',
            'room_seat_voice_flg'   => $oRoomSeat->room_seat_voice_flg,
        ];
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'room_seat';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return RoomSeat[]
     */
    public static function find( $parameters = NULL )
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return RoomSeat
     */
    public static function findFirst( $parameters = NULL )
    {
        return parent::findFirst($parameters);
    }


    public function beforeCreate()
    {
        $this->room_seat_update_time = time();
        $this->room_seat_create_time = time();
        self::deleteCache(self::getCacheKey(sprintf('%s-%s', $this->room_seat_room_id, $this->room_seat_user_id)));
        $redis = self::getRedis();
        $redis->del(self::getCacheKey('count:' . $this->room_seat_room_id));
    }

    public function beforeUpdate()
    {
        $this->room_seat_update_time = time();
        $flg                         = self::deleteCache(self::getCacheKey(sprintf('%s-%s', $this->room_seat_room_id, $this->room_seat_user_id)));
        $redis                       = self::getRedis();
        $redis->del(self::getCacheKey('count:' . $this->room_seat_room_id));
    }

    public function beforeDelete()
    {
        self::deleteCache(self::getCacheKey(sprintf('%s-%s', $this->room_seat_room_id, $this->room_seat_user_id)));
        $redis = self::getRedis();
        $redis->del(self::getCacheKey('count:' . $this->room_seat_room_id));
    }

    /**
     * @param $nRoomId
     * @param int $nUserId
     * @return \app\models\RoomSeat RoomSeat
     */
    public static function checkSeat( $nRoomId, int $nUserId )
    {
        $oRoomSeat = RoomSeat::findFirst([
            'room_seat_room_id = :room_seat_room_id: AND room_seat_user_id = :room_seat_user_id:',
            'bind'  => [
                'room_seat_room_id' => $nRoomId,
                'room_seat_user_id' => $nUserId,
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey(sprintf('%s-%s', $nRoomId, $nUserId))
            ]
        ]);
        return $oRoomSeat;
    }
}
