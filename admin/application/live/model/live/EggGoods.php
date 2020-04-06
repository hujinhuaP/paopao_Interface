<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 砸蛋记录
 */
class EggGoods extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'egg_goods_update_time';
    protected $updateTime = 'egg_goods_update_time';



    public function getEggGoodsCategoryList()
    {
        return ['coin' => __('金币'), 'vip' => __('VIP'),'diamond' => '钻石礼物'];
    }

    public function getEggGoodsNoticeFlgList()
    {
        return ['Y' => __('是'), 'N' => __('否')];
    }

}
