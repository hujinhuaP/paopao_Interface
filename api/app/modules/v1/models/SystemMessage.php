<?php 

namespace app\models;

/**
* SystemMessage 系统消息表
*/
class SystemMessage extends ModelBase
{
    /** @var string 普通消息类型 */
    const TYPE_GENERAL       = 'general';
    /** @var string 关注消息类型 */
    const TYPE_FOLLOW        = 'follow';
    /** @var string 提现消息类型 */
    const TYPE_WITHDRAW      = 'withdraw';
    /** @var string 实名认证消息类型 */
    const TYPE_CERTIFICATION = 'certification';
    /** @var string 成为守护 */
    const TYPE_BECOME_GUARD = 'become_guard';
    /** @var string 守护被抢走 */
    const TYPE_GUARD_ROBBED = 'guard_robbed';
    /** @var string 收益详情 */
    const TYPE_DOT_INCOME = 'dot_income';
    /** @var string 购买微信 */
    const TYPE_BUY_WECHAT = 'buy_wechat';

	public function beforeCreate()
    {
		$this->system_message_create_time = time();
		$this->system_message_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->system_message_update_time = time();
    }
}