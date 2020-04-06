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
* LiveModel 直播业务数据库
*
* @version v1
*/
class LiveModel extends Model
{
	public function __construct($data = [])
	{
		$this->connection = Config::get('livedatabase');
		parent::__construct($data);
	}
}