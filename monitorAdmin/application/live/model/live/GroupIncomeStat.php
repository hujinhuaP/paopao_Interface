<?php

namespace app\live\model\live;

use app\live\model\LiveModel as Model;

/**
 * 公会表
 */
class GroupIncomeStat extends Model
{

    public function group()
    {
        return $this->belongsTo('group','group_id','id',[],'inner')->setEagerlyType(0);
    }

    public function user()
    {
        return $this->belongsTo('user','user_id','user_id',[],'inner')->setEagerlyType(0);
    }

    public function userAccount()
    {
        return $this->belongsTo('user_account','user_id','user_id',[],'inner')->setEagerlyType(0);
    }
}
