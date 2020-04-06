<?php

namespace app\models;



/**
 * UserDiamondLog 用户钻石记录表
 */
class UserDiamondLog extends ModelBase
{
    /** @var string 砸蛋 */
    const CATEGORY_EGG = 'egg';
    /** @var string 兑换消耗 */
    const CATEGORY_EXCHANGE = 'exchange';

    public $user_id;
    public $user_current_amount;
    public $user_last_amount;
    public $consume_category;
    public $consume;
    public $remark;
    public $flow_id;
    public $flow_number;
    public $update_time;
    public $create_time;


    public function beforeCreate()
    {
        $this->update_time = time();
        $this->create_time = time();
    }

    public function beforeUpdate()
    {
        $this->update_time = time();
    }

}
