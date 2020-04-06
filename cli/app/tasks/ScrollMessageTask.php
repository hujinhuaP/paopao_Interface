<?php

namespace app\tasks;

use Exception;
use RedisException;

/**
 * ScrollMessageTask 用户滚屏消息
 */
class ScrollMessageTask extends MainTask
{

    public function mainAction()
    {
        while ( 1 ) {

            try {
                //  取当前时间应该发送的消息 4秒前到现在
                $existData = $this->redis->zRangeByScore('scroll_msg', time() - 4, time());
                foreach ( $existData as $item ) {
                    $keyInfo = explode('_', $item);
                    $this->redis->zDelete('scroll_msg',$item);
                    if ( count($keyInfo) != 2 ) {
                        continue;
                    }
                    $userId   = $keyInfo[0];
                    $itemInfo = $this->redis->hGetAll('scroll_recharge:'.$item);
                    $this->redis->delete('scroll_recharge:'.$item);
                    if ( !$itemInfo || count($itemInfo) != 5 ) {
                        continue;
                    }
                    $nCoin    = $itemInfo['coin'] ?? '';
                    $coinName = $itemInfo['coin_name'] ?? '';
                    $pushInfo = [
                        'user_nickname' => $itemInfo['user_nickname'] ?? '',
                        'user_avatar'   => $itemInfo['user_avatar'] ?? '',
                        'title'         => $itemInfo['user_nickname'] ?? '',
                        'content'       => sprintf('成功充值 %d %s', $nCoin, $coinName),
                    ];
                    $result = $this->httpRequest(sprintf('%sim/scrollMessage', $this->config->application->app_api_url), [
                        'user_id' => $userId,
                        'type'    => 'recharge',
                        'info'    => $pushInfo,
                        'cli_api_key' => $this->config->application->cli_api_key,
                    ]);
                    echo sprintf('[%s] userID [%s] %s',date('Y-m-d H:i:s'),$userId,$result) . "\n";

                }
            } catch ( RedisException $e ) {
                echo $e;
                try {
                    $this->redis->connect();

                    if ( $this->config->redis->pconnect ) {
                        $this->redis->pconnect($this->config->redis->host, $this->config->redis->port);
                    } else {
                        $this->redis->connect($this->config->redis->host, $this->config->redis->port);
                    }

                    $this->redis->auth($this->config->redis->auth);
                    $this->redis->select($this->config->redis->db);
                } catch ( Exception $e ) {
                    echo $e;
                }

            } catch ( Exception $e ) {
                echo $e;
            }
            sleep(1);

        }
    }
}