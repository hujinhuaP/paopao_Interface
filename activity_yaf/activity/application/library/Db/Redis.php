<?php

class Db_Redis
{
    private $db;
    /**
     * 构造函数
     * @param $host
     * @param $port
     * @param $auth
     * @param $db
     * @param $pconnect
     */
    private function __construct($host, $port, $auth, $db_select, $pconnect)
    {
//初始化数据连接
        try {
            $redis = new \Redis();
            if ( $pconnect ) {
                $redis->pconnect($host, $port);
            } else {
                $redis->connect($host, $port);
            }

            $redis->auth($auth);
            $redis->select($db_select);
            $this->db = $redis;

        } catch ( RedisException $e ) {
            echo header("Content-type: text/html; charset=utf-8");
            echo '<pre />';
            echo '<b>Connection failed:</b>' . $e->getMessage();
            die;
        }
    }

    static public function getInstance($config = '')
    {
        $host     = $config->host;
        $port     = $config->port;
        $auth     = $config->password;
        $db_select       = $config->db;
        $pconnect = $config->pconnect;
        $db = new self($host, $port, $auth, $db_select, $pconnect);
        return $db->db;
    }


}