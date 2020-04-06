<?php

namespace app\models;

class BuyHammerLog extends ModelBase
{

    /**
     *
     * @var integer
     */
    public $buy_hammer_id;

    /**
     *
     * @var integer
     */
    public $buy_hammer_user_id;

    /**
     *
     * @var integer
     */
    public $buy_hammer_number;

    /**
     *
     * @var integer
     */
    public $buy_hammer_total_coin;

    /**
     *
     * @var integer
     */
    public $buy_hammer_create_time;


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'buy_hammer_log';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return BuyHammerLog[]|BuyHammerLog|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return BuyHammerLog|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function beforeCreate()
    {
        $this->buy_hammer_create_time = time();
    }



}
