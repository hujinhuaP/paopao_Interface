<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2018 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 主播统计                          |
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
* ADAgentService 可以看广告的代理商
 * 可以有停留诱导的代理商
*/
class ADAgentService extends RedisService
{

	public function __construct($type = 'ad')
	{
		parent::__construct();
		switch ($type){
            case 'stay_guide':
                $this->_key = sprintf('ad_agent:stay_guide');
                break;
            case 'ad':
            default:
            $this->_key        = sprintf('ad_agent');
        }
		$this->_ttl         = 86400 * 7;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($value = '')
	{
        $bool = $this->redis->sAddArray($this->_key,$value);
		if ($bool > 0) {
			$this->redis->expire($this->_key, $this->_ttl);
        }
		return $bool;
	}

	/**
	 * delete 删除
	 * 
	 * @return bool
	 */
	public function delete()
	{
		return $this->redis->del($this->_key);
	}

	/**
	 * getData 获取数据
	 * 
	 * @return mixed
	 */
	public function getData()
	{
		return $this->redis->sMembers($this->_key);
	}

    public function exists($value)
    {
        return $this->redis->sIsMember($this->_key,$value);
	}


}