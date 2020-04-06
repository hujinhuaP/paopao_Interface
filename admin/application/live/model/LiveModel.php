<?php

namespace app\live\model;

use think\Model;
use think\Config;


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