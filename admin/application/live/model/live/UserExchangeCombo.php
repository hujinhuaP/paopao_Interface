<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * UserExchangeCombo 兑换套餐
 */
class UserExchangeCombo extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    public function getExchangeCategoryList()
    {
        return [
            'cash'    => __('现金'),
            'diamond' => __('钻石')
        ];
    }

}