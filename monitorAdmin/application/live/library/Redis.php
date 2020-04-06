<?php

namespace app\live\library;

use Redis as RedisBase;

use think\Config;

class Redis extends RedisBase
{
	public function __construct()
	{
		parent::__construct();
        
        $config = Config::get('redis');
        
        if ($config['pconnect']) {
            $this->pconnect($config['host'], $config['port']);
        } else {
            $this->connect($config['host'], $config['port']);
        }

        $this->auth($config['auth']);
        $this->select($config['db']);
	}
}