<?php 

namespace app\models;

/**
* UserProfileSetting 个人信息区间设置
*/
class UserProfileSetting extends ModelBase
{

    /**
     * 好评
     */
    const PRAISE_TIP    = 'praise_tip';
    /**
     * 差评
     */
    const CRITICISM_TIP = 'criticism_tip';

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