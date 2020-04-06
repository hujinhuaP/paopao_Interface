<?php

namespace app\live\model;

use think\Model;
use think\Config;

/*
 +------------------------------------------------------------------------+
 | 火鸟直播                                                               |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 火鸟直播 Team (https://www.fireniao.com)       |
 +------------------------------------------------------------------------+
 | This source file is subject to the ...                                 |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */
		
/**
* AdminModel Admin数据库
*
* @version v1
*/
class AdminModel extends Model
{
	public function __construct($data = [])
	{
		$this->connection = Config::get('database');
		parent::__construct($data);
	}
}