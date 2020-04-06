<?php

namespace app\models;

class RoomChatForbidden extends ModelBase
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_chat_forbidden_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_chat_forbidden_room_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_chat_forbidden_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_chat_forbidden_admin_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_chat_forbidden_create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_chat_forbidden_update_time;



    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'room_chat_forbidden';
    }

    public function beforeCreate()
    {
        $this->room_chat_forbidden_update_time = time();
        $this->room_chat_forbidden_create_time = time();
        self::deleteCache(self::getCacheKey(sprintf('%s-%s',$this->room_chat_forbidden_room_id,$this->room_chat_forbidden_user_id)));
        self::deleteCache(self::getCacheKey($this->room_chat_forbidden_room_id));
    }

    public function beforeUpdate()
    {
        $this->room_chat_forbidden_update_time = time();
    }

    public function beforeDelete()
    {
        self::deleteCache(self::getCacheKey(sprintf('%s-%s',$this->room_chat_forbidden_room_id,$this->room_chat_forbidden_user_id)));
        self::deleteCache(self::getCacheKey($this->room_chat_forbidden_room_id));
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Roomchat_forbidden[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Roomchat_forbidden
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


    /**
     * @param $nRoomId
     * @param int $nUserId
     * @return \app\models\RoomChatForbidden Roomchat_forbidden
     */
    public static function checkResult($nRoomId, int $nUserId)
    {
        $oRoomAdmin = RoomChatForbidden::findFirst([
            'room_chat_forbidden_room_id = :room_chat_forbidden_room_id: AND room_chat_forbidden_user_id = :room_chat_forbidden_user_id:',
            'bind' => [
                'room_chat_forbidden_room_id' => $nRoomId,
                'room_chat_forbidden_user_id' => $nUserId,
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey(sprintf('%s-%s',$nRoomId,$nUserId))
            ]
        ]);
        return $oRoomAdmin;
    }


    /**
     * @param $nRoomId
     * @return array
     * 获取房间禁言列表
     */
    public static function getRoomList($nRoomId)
    {
        $oRoomChatForbidden = RoomChatForbidden::find([
            'room_chat_forbidden_room_id = :room_chat_forbidden_room_id:',
            'bind' => [
                'room_chat_forbidden_room_id' => $nRoomId,
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey($nRoomId)
            ]
        ]);
        $dataArr = [];
        if($oRoomChatForbidden){
            $dataArr = array_column($oRoomChatForbidden->toArray(),'room_chat_forbidden_user_id');
        }
        return $dataArr;
    }

}
