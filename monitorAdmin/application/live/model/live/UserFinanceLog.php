<?php 

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
* UserFinanceLog 用户流水表
*/
class UserFinanceLog extends Model
{
	/** @var string 金币 */
    const AMOUNT_COIN  = 'coin';
    /** @var string 收益 */
    const AMOUNT_DOT   = 'dot';
    /** @var string 现金 */
    const AMOUNT_MONEY = 'money';

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