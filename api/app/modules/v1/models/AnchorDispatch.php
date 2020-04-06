<?php 

namespace app\models;

/**
* AnchorDispatch 派单主播
*/
class AnchorDispatch extends ModelBase
{

    public $anchor_dispatch_id;
    public $anchor_dispatch_price;
    public $anchor_dispatch_user_id;
    public $anchor_dispatch_max_day_times;
    public $anchor_dispatch_today_times;
    public $anchor_dispatch_last_time;
    public $anchor_dispatch_create_time;
    public $anchor_dispatch_update_time;

	public function beforeCreate()
    {
		$this->anchor_dispatch_create_time = time();
		$this->anchor_dispatch_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->anchor_dispatch_update_time = time();
    }
}