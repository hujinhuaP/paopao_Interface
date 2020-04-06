<?php

namespace app\models;

class RoomBlack extends ModelBase
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_black_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_black_room_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_black_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $room_black_admin_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_black_create_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $room_black_update_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'room_black';
    }

    public function beforeCreate()
    {
        $this->room_black_update_time = time();
        $this->room_black_create_time = time();
        self::deleteCache(self::getCacheKey(sprintf('%s-%s',$this->room_black_room_id,$this->room_black_user_id)));
    }

    public function beforeUpdate()
    {
        $this->room_black_update_time = time();
    }

    public function beforeDelete()
    {
        self::deleteCache(self::getCacheKey(sprintf('%s-%s',$this->room_black_room_id,$this->room_black_user_id)));
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return RoomBlack[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return RoomBlack
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


    /**
     * @param $nRoomId
     * @param int $nUserId
     * @return \app\models\RoomBlack $oRoomBlack
     */
    public static function checkResult($nRoomId, int $nUserId)
    {
        $oRoomBlack = RoomBlack::findFirst([
            'room_black_room_id = :room_black_room_id: AND room_black_user_id = :room_black_user_id:',
            'bind' => [
                'room_black_room_id' => $nRoomId,
                'room_black_user_id' => $nUserId,
            ],
            'cache' => [
                'lifetime' => 3600,
                'key'      => self::getCacheKey(sprintf('%s-%s',$nRoomId,$nUserId))
            ]
        ]);
        return $oRoomBlack;
    }

    /**
     * @param $nRoomId
     * @return \Phalcon\Mvc\Model\Query\BuilderInterface
     * 获取房间的黑名单列表
     */
    public function getRoomDetail($nRoomId)
    {
        $builder = $this->getModelsManager()->createBuilder()->from(['a' => self::class])
            ->join(User::class,'u.user_id = a.room_black_user_id','u')
            ->columns('u.user_id,u.user_nickname,u.user_avatar')
            ->where('a.room_black_room_id = :room_black_room_id:', [
                'room_black_room_id' => $nRoomId,
            ])->orderBy('a.room_black_id');
        return $builder;
    }

}
