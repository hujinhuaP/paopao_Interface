<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 今天主播dot 记录        后台展示时长分析 总表                                            |
 | 负责记录主播今天获取的dot                                          |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

/**
* AnchorTodayDotService
 * 同时记录主播收益 周榜
*/
class AnchorTodayDotService extends RedisService
{

	/** @var int 直播日志ID */
	protected $_userId;

    private $_key_income;


	public function __construct($nUserId=0)
	{
		parent::__construct();
		$this->_userId = $nUserId;
		$this->_key    = 'anchor:today:dot:'.date('Ymd');
		$this->_ttl    = 60*60*24;
        $this->_key_income = 'ranking:anchor:'.date('o-W');
	}

	/**
	 * save 保存
	 * 
	 * @param  float $nDot
	 * @return float 
	 */
	public function save($nDot)
	{
        $newDot = $this->redis->hIncrByFloat($this->_key,$this->_userId, $nDot);

        // 保存收益周榜
        if($this->_userId == 120269 ){
            $bool = TRUE;
        }else{
            $bool = $this->redis->zIncrBy($this->_key_income, floatval(sprintf('%.2f', $nDot)), $this->_userId);
        }
        if($bool && date('D') == 'Sun'){
            // 周末设置 周榜过期时间为一周
            $this->redis->expire($this->_key_income, 8 * 86400);
        }

		if ($newDot) {
			$this->redis->expire($this->_key, $this->_ttl);
		}
		return $newDot;
	}

    /**
     * getData 获取周榜数据 排行榜前10
     *
     * @return mixed
     */
    public function getIncomeData($number = 10)
    {
        return $this->redis->zRevRange($this->_key_income,0,$number,TRUE);
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
     * 从缓存中取已经统计好的数据
     */
    public function getCacheData()
    {
        $cacheKey = 'ranking:anchor:0tmp';
        return $this->redis->get($cacheKey);
    }

    /**
     * 从缓存中取已经统计好的数据  （周榜）
     */
    public function saveCacheData($data)
    {
        $cacheKey = 'ranking:anchor:0tmp';
        return $this->redis->set($cacheKey,$data,60);
    }
}