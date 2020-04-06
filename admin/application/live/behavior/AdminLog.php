<?php

namespace app\live\behavior;

use think\Config;

class AdminLog
{

    public function run(&$params)
    {
        if (request()->isPost())
        {
            \app\live\model\admin\AdminLog::record();
        }
    }

}
