<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 房间关注用户服务                                                       |
 | 记录房间直播时间段内主播被用户关注的数量                               |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use Exception;
use Phalcon\DI\FactoryDefault as Di;

/**
 * VerifycodeService 验证码服务
 */
class VerifycodeService extends RedisService
{

    /** @var string 注册 */
    const TYPE_REGISTER   = 'register';
    /** @var string 绑定手机号 */
    const TYPE_BIND_PHONE   = 'bind_phone';
    /** @var string 修改手机号码 */
    const TYPE_CHANGE_PHONE = 'change_phone';

    public function __construct($phone, $type,$ip = null,$deviceId = null)
    {
        parent::__construct();
        $this->_key = sprintf('Verifycode:%s:%s', $type, $phone);
        if($ip){
            $this->_key = sprintf('Verifycode:ip:%s:%s', $type,$ip );
        }elseif($deviceId){
            $this->_key = sprintf('Verifycode:device:%s:%s', $type,$deviceId );
        }
        $this->_ttl = 60;
    }

    /**
     * save 保存
     * 在同一个有效期内 只能保存一次 即 值只能为1
     *
     * @return bool
     */
    public function save($nUserId = 0)
    {
        $bool = $this->redis->incr($this->_key);
        if ( $bool == 1 ) {
            $this->redis->expire($this->_key, $this->_ttl);
            return TRUE;
        }else{
            return FALSE;
        }
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


}