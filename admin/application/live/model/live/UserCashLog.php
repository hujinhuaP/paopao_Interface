<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserCashLog 用户“现金”流水表
*/
class UserCashLog extends Model
{
    /** @var string 邀请注册奖励 */
    const CATEGORY_REGISTER = 'register';
    /** @var string 邀请充值奖励 */
    const CATEGORY_RECHARGE = 'recharge';
    /** @var string 邀请提现奖励 */
    const CATEGORY_WITHDRAW = 'withdraw';
    /** @var string 兑换消耗 */
    const CATEGORY_EXCHANGE = 'exchange';
    /** @var string 提现退回 */
    const CATEGORY_WITHDRAW_BACK = 'withdraw_back';
    /** @var string 补单增加 */
    const CATEGORY_BUDAN_INCR = 'budan_incr';
    /** @var string 补单减少 */
    const CATEGORY_BUDAN_DECR = 'budan_decr';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';



    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function targetUser()
    {
        return $this->belongsTo('user','target_user_id','user_id',[],'left')->setEagerlyType(0);
    }
}