<?php

namespace app\models;

class UserEggDetail extends ModelBase
{

    /**
     *
     * @var integer
     */
    public $user_egg_detail_id;

    /**
     *
     * @var integer
     */
    public $user_egg_detail_log_id;


    /**
     *
     * @var integer
     */
    public $user_egg_detail_user_id;

    /**
     *
     * @var integer
     */
    public $user_egg_detail_goods_id;

    /**
     *
     * @var string
     */
    public $user_egg_detail_goods_category;

    /**
     *
     * @var integer
     */
    public $user_egg_detail_value;

    /**
     *
     * @var string
     */
    public $user_egg_detail_name;

    /**
     *
     * @var string
     */
    public $user_egg_detail_image;

    /**
     *
     * @var integer
     */
    public $user_egg_detail_reward_number;

    /**
     *
     * @var string
     */
    public $user_egg_detail_notice_flg;

    /**
     *
     * @var integer
     */
    public $user_egg_detail_create_time;


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'user_egg_detail';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserEggDetail[]|UserEggLog|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return UserEggDetail|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function beforeCreate(){
        $this->user_egg_detail_create_time = time();
    }

}
