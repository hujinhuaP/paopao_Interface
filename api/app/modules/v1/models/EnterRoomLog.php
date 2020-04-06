<?php

namespace app\models;

class EnterRoomLog extends ModelBase
{

    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=10, nullable=false)
     */
    public $enter_room_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $enter_room_room_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $enter_room_user_id;

    /**
     *
     * @var integer
     * @Column(type="integer", length=10, nullable=false)
     */
    public $enter_room_online;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $enter_room_online_time;

    /**
     *
     * @var integer
     * @Column(type="integer", length=11, nullable=false)
     */
    public $enter_room_offline_time;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'enter_room_log';
    }

    public function beforeCreate()
    {
        $this->enter_room_online_time = time();
    }


    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return EnterRoomLog[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return EnterRoomLog
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }


}
