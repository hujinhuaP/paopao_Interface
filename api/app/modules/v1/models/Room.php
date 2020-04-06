<?php

namespace app\models;


use app\helper\ResponseError;
use app\services\RoomHeartbeatService;
use app\services\UserOnlineService;

class Room extends ModelBase
{

    /**
     * 音频房ID
     */
    const B_CHAT_ID = '1';

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_number;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_user_id;

    /**
     *
     * @var string
     * @Column(type="string", length=100, nullable=false)
     */
    public $room_name;

    /**
     *
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    public $room_cover;

    /**
     *
     * @var integer
     * @Column(type="integer", length=3, nullable=false)
     */
    public $room_background;


    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $room_welcome_word;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $room_notice_title;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    public $room_notice_word;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_online_count;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_robot_count;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_host_user_id;


    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $room_heart_stat_open_flg;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    public $room_open_flg;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_heart_stat_start;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_update_time;


    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Room[]
     */
    public static function find( $parameters = NULL )
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Room
     */
    public static function findFirst( $parameters = NULL )
    {
        return parent::findFirst($parameters);
    }


    /**
     * @param User $oUser
     * 进入房间  将用户所在房间字段修改为当前
     * 修改房间在线人数
     */
    public function enter( User $oUser )
    {
        if ( $oUser->user_enter_room_id == $this->room_id ) {
            return TRUE;
        }
        if ( $oUser->user_enter_room_id ) {
            // 如果有其他房间 要先离开其他房间
            $oOldRoom = Room::findFirst($oUser->user_enter_room_id);
            $oOldRoom->leave($oUser);
        }

        $model      = new Room();
        $connection = $model->getWriteConnection();
        $connection->begin();
        $oUser->user_enter_room_id = $this->room_id;
        if ( $oUser->save() === FALSE ) {
            $connection->rollback();
            throw new \Exception(
                sprintf('%s[%s]-1', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUser->getMessage()),
                ResponseError::OPERATE_FAILED
            );
        }
        // 修改在线人数
        if ( $oUser->user_is_isrobot == 'Y' ) {
            $updateRoomOnlineCountSql = 'update room set room_online_count = room_online_count + 1,room_robot_count = room_robot_count + 1 where room_id = :room_id';
        } else {
            $updateRoomOnlineCountSql = 'update room set room_online_count = room_online_count + 1 where room_id = :room_id';
        }
        $connection->execute($updateRoomOnlineCountSql, [
            'room_id' => $this->room_id
        ]);
        if ( $connection->affectedRows() <= 0 ) {
            $connection->rollback();
            throw new \Exception(
                sprintf('%s[%s]-2', ResponseError::getError(ResponseError::OPERATE_FAILED), '房间人数更新失败'),
                ResponseError::OPERATE_FAILED
            );
        }
        if ( $oUser->user_is_isrobot == 'N' ) {
            // 添加进房记录
            $oEnterRoomLog                         = new EnterRoomLog();
            $oEnterRoomLog->enter_room_user_id     = $oUser->user_id;
            $oEnterRoomLog->enter_room_room_id     = $this->room_id;
            $oEnterRoomLog->enter_room_online      = $this->room_online_count + 1;
            $oEnterRoomLog->enter_room_online_time = time();
            if ( $oEnterRoomLog->save() === FALSE ) {
                $connection->rollback();
                throw new \Exception(
                    sprintf('%s[%s]-3', ResponseError::getError(ResponseError::OPERATE_FAILED), $oEnterRoomLog->getMessage()),
                    ResponseError::OPERATE_FAILED
                );
            }
        }

        $connection->commit();


//        if ( $oUser->user_is_isrobot == 'Y'  ) {
//            $timServer = self::getTimServer();
//            $timServer->setExtra($oUser);
//            $timServer->setRid($this->room_id);
//             $timServer->sendJoinSignal([
//                'room_id'                  => $this->room_id,
//                'room_online_count'        => (int)$this->room_online_count + 1,
//            ]);
//
//        } else {
//            // 增加当前房间用户
//            $oUserOnlineService = new UserOnlineService('room_user');
//            $oUserOnlineService->save($oUser->user_id);
//
//            $timServer = self::getTimServer();
//            $timServer->setExtra($oUser);
//            $timServer->setRid($this->room_id);
//            $joinResult = $timServer->sendJoinSignal([
//                'room_id'                  => $this->room_id,
//                'room_online_count'        => (int)$this->room_online_count + 1,
//            ]);
//            if ( $joinResult['ErrorCode'] != 0 ) {
//                $timServer = self::getTimServer();
//                $timServer->setRid($this->room_id);
//                $timServer->createRoom(sprintf('%s的聊天室', $this->room_id), 'ChatRoom', [ [ 'Member_Account' => $oUser->user_id ] ]);
//            } else {
//                // 将用户加入群
//                $timServer->setAccountId($oUser->user_id);
//                $timServer->joinRoom();
//            }
//        }

        return TRUE;

    }


    /**
     * @param User $oUser
     * @param bool $pushFlg
     * @return bool/array
     * @throws \Phalcon\Exception
     * 离开房间
     */
    public function leave( User $oUser, $pushFlg = TRUE )
    {
        if ( $oUser->user_enter_room_id != $this->room_id ) {
            return TRUE;
        }

        // 判断是否在麦上
        $inSeat = FALSE;
        if ( $oUser->user_is_isrobot == 'N' ) {
            $myIndex   = (new UserGiftLog())->getSendCoinWeekRankIndex($oUser->user_id, $this->room_id);
            $lastIndex = (new RoomSeat())->getLastSeatRank($this->room_id);
            if ( $myIndex !== FALSE && $myIndex <= $lastIndex ) {
                $inSeat = TRUE;
            }
        }


        $model      = new Room();
        $connection = $model->getWriteConnection();
        $connection->begin();
        $oUser->user_enter_room_id       = 0;
        $oUser->user_room_seat_flg       = 'Y';
        $oUser->user_room_seat_voice_flg = 'Y';
        if ( $oUser->save() === FALSE ) {
            $connection->rollback();
            throw new \Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUser->getMessage()),
                ResponseError::OPERATE_FAILED
            );
        }
        // 修改在线人数
        if ( $oUser->user_is_isrobot == 'Y' ) {
            $updateRoomOnlineCountSql = 'update `yuyin_live`.room set room_online_count = room_online_count - 1,room_robot_count = room_robot_count - 1 where room_id = :room_id AND room_robot_count >= 1 AND room_online_count >= 1';
        } else {
            $updateRoomOnlineCountSql = 'update `yuyin_live`.room set room_online_count = room_online_count - 1 where room_id = :room_id AND room_online_count >= room_robot_count + 1';
        }
        $connection->execute($updateRoomOnlineCountSql, [
            'room_id' => $this->room_id
        ]);
        if ( $connection->affectedRows() < 0 ) {
            $connection->rollback();
            throw new \Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), '房间人数更新失败'),
                ResponseError::OPERATE_FAILED
            );
        }


        $leaveHasSeat = [
            'room_id'           => $this->room_id,
            'seat_flg'          => 'N',
            'room_seat'         => [],
            'room_online_count' => $this->room_online_count - 1 < 0 ? 0 : $this->room_online_count - 1
        ];
        if ( $oUser->user_is_isrobot == 'N' ) {
            // 判断在不在麦序上  在麦序上 要离开麦序
            // 判断咋不在等待上麦列表中
            // 查出所有座位
            $oRoomSeat = [];
            if ( $this->room_host_user_id == $oUser->user_id ) {
                $leaveSeatNumber = (new RoomSeat())->leave($this->room_id, $oUser->user_id);
                if ( $leaveSeatNumber !== FALSE ) {
                    $updateFlg = FALSE;
                    if ( $this->room_host_user_id == $oUser->user_id ) {
                        $this->room_host_user_id = 0;
                        $updateFlg               = TRUE;
                    }
                    if ( $updateFlg ) {
                        if ( $this->save() === FALSE ) {
                            throw new \Exception(
                                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $this->getMessage()),
                                ResponseError::OPERATE_FAILED
                            );
                        }
                    }
                    // 推送下麦
                    $leaveHasSeat = [
                        'room_id'           => $this->room_id,
                        'seat_flg'          => 'Y',
                        'room_seat'         => $oRoomSeat,
                        'room_online_count' => $this->room_online_count - 1 < 0 ? 0 : $this->room_online_count - 1
                    ];
                }
            } elseif ( $inSeat ) {
                // 删除缓存再查
                $oRoomSeat = RoomSeat::getInfoByRank($this->room_id,FAlSE);
                // 推送下麦
                $leaveHasSeat = [
                    'room_id'           => $this->room_id,
                    'seat_flg'          => 'Y',
                    'room_seat'         => $oRoomSeat,
                    'room_online_count' => $this->room_online_count - 1 < 0 ? 0 : $this->room_online_count - 1
                ];
            }

            // 添加离开房间记录
            $oEnterRoomLog = EnterRoomLog::findFirst([
                'enter_room_user_id = :enter_room_user_id: AND enter_room_room_id = :enter_room_room_id: AND enter_room_offline_time = 0',
                'bind'  => [
                    'enter_room_user_id' => $oUser->user_id,
                    'enter_room_room_id' => $this->room_id
                ],
                'order' => 'enter_room_id desc'
            ]);
            if ( $oEnterRoomLog ) {
                $oEnterRoomLog->enter_room_offline_time = time();
                if ( $oEnterRoomLog->save() === FALSE ) {
                    $connection->rollback();
                    throw new \Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oEnterRoomLog->getMessage()),
                        ResponseError::OPERATE_FAILED
                    );
                }
            }
        }

        $connection->commit();

        if ( $oUser->user_is_isrobot == 'Y' ) {
            $timServer = self::getTimServer();
            $timServer->setRid($this->room_id);
            if ( $pushFlg ) {
                $timServer->setExtra($oUser);
                $timServer->sendLeaveSignal(FALSE, $leaveHasSeat);
            }
        } else {
            // 减少当前房间用户
            $oUserOnlineService = new UserOnlineService('room_user');
            $oUserOnlineService->delete_item($oUser->user_id);

            $timServer = self::getTimServer();
            $timServer->setRid($this->room_id);
            if ( $pushFlg ) {
                $timServer->setExtra($oUser);
                $timServer->sendLeaveSignal(FALSE, $leaveHasSeat);
            }

            if ( $this->room_online_count - $this->room_robot_count == 1 ) {
                // 之前只有1个人 则删除群组
//                $timServer = self::getTimServer();
//                $timServer->setRid($this->room_id);
//                $timServer->destroyRoom();
            } else {
                // 退出TIM群
                $timServer->setAccountId($oUser->user_id);
                $timServer->leaveRoom();
            }

            // 删除心跳
            $oRoomHeartbeatService = new RoomHeartbeatService();
            $oRoomHeartbeatService->delItem(sprintf("%s_%s", $this->room_id, $oUser->user_id));
        }

        return $leaveHasSeat;
    }


    /**
     * 获取房间内部信息
     *
     * 先从缓存中取 如果缓存中没有 则从数据库中取 并写入缓存  （通过模型缓存操作）
     *      主持信息
     *      房间基本信息
     *      麦序信息
     *
     */
    public function getInsideDetail()
    {
        // 查出所有座位
        $oRoomSeat = RoomSeat::getInfoByRank($this->room_id);

        $oHostSeat = RoomSeat::getHostSeat($this->room_id,$this->room_host_user_id);

        $roomOwner = User::findFirst($this->room_user_id);

        $oKvData = Kv::many([
            Kv::USER_ENTER_ROOM_SCROLL_RICH_LEVEL,
        ]);

        $row = [
            'room_owner_info'                   => [
                'user_id'          => $roomOwner->user_id,
                'user_nickname'    => $roomOwner->user_nickname,
                'user_avatar'      => $roomOwner->user_avatar,
                'user_birth'       => $roomOwner->user_birth,
                'user_on_room_flg' => $roomOwner->user_enter_room_id == $this->room_id ? 'Y' : 'N'
            ],
            'room_id'                           => $this->room_id,
            'room_name'                         => $this->room_name,
            'room_number'                       => $this->room_number,
            'room_online_count'                 => (int)$this->room_online_count,
            'room_background'                   => intval($this->room_background),
            'room_cover'                        => $this->room_cover,
            'room_heart_stat_open_flg'          => $this->room_heart_stat_open_flg,
            'room_notice_word'                  => $this->room_notice_word ? str_replace("\r", "\n", $this->room_notice_word) : '',
            'room_notice_title'                 => $this->room_notice_title ?? '',
            'room_welcome_word'                 => $this->room_welcome_word ? str_replace("\r", "\n", $this->room_welcome_word) : '',
            'room_seat'                         => $oRoomSeat,
            'host_seat'                         => $oHostSeat,
            'user_enter_room_scroll_rich_level' => $oKvData[ Kv::USER_ENTER_ROOM_SCROLL_RICH_LEVEL ] ?? '100',
            'system_notice'                     => $this->room_system_notice
        ];

        return $row;
    }

    /**
     * @param int $nUserId
     * 获取用户与房间的关系
     *      是否是管理员  是否是房主  是否收藏
     * @param User $oUser
     * @return array
     */
    public function getRelationship( int $nUserId, $oUser = NULL )
    {
        $collectFlg   = 'N';
        $waitFlg      = 'N';
        $userGroupFlg = 'N';
        $roomRoleFlg  = 'normal';
        if ( $nUserId == $this->room_user_id ) {
            $roomRoleFlg = 'owner';
        } else if ( RoomAdmin::checkAdmin($this->room_id, $nUserId) ) {
            $roomRoleFlg = 'admin';
        }
        if ( $oUser ) {
            if ( $oUser->user_is_superadmin == 'Y' ) {
                $roomRoleFlg = 'super_admin';
            }
        }

        return [
            'collect_flg'    => $collectFlg,
            'wait_flg'       => $waitFlg,
            'user_group_flg' => $userGroupFlg,
            'user_role_flg'  => $roomRoleFlg,
        ];
    }

    /**
     * @param $nUserId
     * @param string $category
     * @return string
     */
    public function getToken( $nUserId, $category = 'agora' )
    {
        switch ( $category ) {
            case 'zego':
                $config    = self::getConfig();
                $secretKey = $config->application->zego->server_secret;
                $timestamp = time() + 3600;
                $text      = json_encode([
                    "app_id"  => (int)$config->application->zego->app_id,
                    // 数值型, appid联系zego技术支持
                    "timeout" => $timestamp,
                    // 数值型, 注意必须是当前时间戳(秒)加超时时间(秒)
                    "nouce"   => mt_rand(100000, 999999),
                    // 随机数,须为数值型
                    "id_name" => (string)$nUserId,
                    // 字符串,id_name必须跟setUser的userid相同
                ]);
                $encrypted = \app\helper\AesCipher::encrypt($secretKey, $text);
                $str       = "01" . $encrypted;
                break;
            default:
                $str = '';
        }
        return $str;
    }

    public function getUserRoleFlg($oUser)
    {
        $userRoleFlg = 'normal';
        // 判断自己是否为超管
        if ( $oUser->user_id == $this->room_user_id ) {
            // 自己是房主
            $userRoleFlg = 'owner';
        } elseif ( $oUser->user_is_superadmin == 'Y' ) {
            $userRoleFlg = 'super_admin';
        } elseif ( RoomAdmin::checkAdmin($this->room_id, $oUser->user_id) ) {
            $userRoleFlg = 'admin';
        }
        return $userRoleFlg;
    }

    /**
     * 发送更新房间座位推送
     *
     *  注 临时模拟一个虚拟用户退出房间
     */
    public function updateRoomSeat()
    {
        $oRoomSeat = RoomSeat::getInfoByRank($this->room_id,FAlSE);
        $leaveHasSeat = [
            'room_id'           => $this->room_id,
            'seat_flg'          => 'Y',
            'room_seat'         => $oRoomSeat,
            'room_online_count' => $this->room_online_count - 1 < 0 ? 0 : $this->room_online_count - 1
        ];
        $timServer = self::getTimServer();
        $oUser = User::findFirst(1);
        $timServer->setExtra($oUser);
        $timServer->sendLeaveSignal(FALSE, $leaveHasSeat);

    }


}
