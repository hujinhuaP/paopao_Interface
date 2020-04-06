<?php 


namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
* UserFirstShareService 用户首次分享获得免费时长限制
 * 如果存在则能领取免费时长
*/
class UserFirstShareService extends RedisService
{


	public function __construct($sUserId,$ttl = 86400 * 2)
	{
		parent::__construct();
		$this->_key        = 'user:share:'.$sUserId;
		$this->_ttl         = $ttl;
	}

	/**
	 * save 保存
	 * 
	 * @return bool
	 */
	public function save($value=1)
	{
		$bool = $this->redis->set($this->_key,$value);
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
		return $this->redis->get($this->_key);
	}

    /**
     * getData 获取过期时间
     *
     * @return mixed
     */
    public function getTTL()
    {
        return $this->redis->ttl($this->_key);
    }

}