<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class Anchor extends ApiModel
{

    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}
