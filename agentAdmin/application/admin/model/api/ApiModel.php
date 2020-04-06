<?php

namespace app\admin\model\api;

use think\Model;
use think\Config;

class ApiModel extends Model
{
    public function __construct($data = [])
    {
        $this->connection = Config::get('livedatabase');
        parent::__construct($data);
    }
}
