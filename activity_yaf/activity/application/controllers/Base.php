<?php
/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 * @property \Redis  redis
 */
class BaseController extends Yaf_Controller_Abstract {

    public $config;
    public $db;
    public $redis;
	/** 
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/activity/index/index/index/name/root 的时候, 你就会发现不同
     */
	public function init() {
		//1. fetch query
        $dbConfig = Yaf_Application::app()->getConfig()->db;
        $this->db = Db_Mysql::getInstance($dbConfig);

        $redisConfig = Yaf_Application::app()->getConfig()->redis;
        $this->redis = Db_Redis::getInstance($redisConfig);
	}

    public function testAction()
    {
        echo 2;die;
	}
}
