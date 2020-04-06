<?php 

namespace app\models;

/**
* UserExchangeCombo 用户兑换套餐表
*/
class UserExchangeCombo extends ModelBase
{

    /** @var string 类型 - 推广佣金 */
    const CATEGORY_CASH    = 'cash';
    /** @var string 类型 - 钻石 */
    const CATEGORY_DIAMOND = 'diamond';

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