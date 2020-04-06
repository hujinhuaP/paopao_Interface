<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class UserFinanceLog extends ApiModel
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

}
