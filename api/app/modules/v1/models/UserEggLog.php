<?php

namespace app\models;

class UserEggLog extends ModelBase
{

    /**
     *
     * @var integer
     */
    public $user_egg_log_id;

    /**
     *
     * @var integer
     */
    public $user_egg_log_user_id;


    /**
     *
     * @var integer
     */
    public $user_egg_log_number;

    /**
     *
     * @var string
     */
    public $user_egg_log_result;

    /**
     *
     * @var integer
     */
    public $user_egg_log_times;

    /**
     *
     * @var integer
     */
    public $user_egg_log_create_time;


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user_egg_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserEggLog[]|UserEggLog|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserEggLog|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function beforeCreate(){
        $this->user_egg_log_create_time = time();
    }

}
