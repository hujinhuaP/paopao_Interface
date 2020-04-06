<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class UserAccount extends ApiModel
{

    public function user()
    {
        return $this->belongsTo('user');
    }
}
