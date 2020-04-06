<?php

namespace app\tasks;

use Phalcon\Exception;

/**
 * RoomTask 房间
 */
class RoomTask extends MainTask
{
    /**
     * heartbeatAction 房间心跳检测
     */
    public function heartbeatAction()
    {
        while ( 1 ) {

            try {
                $heartbeatData = $this->redis->zRangeByScore('room:heartbeat', 0, time() - 30);
                if ( !empty($heartbeatData) ) {
                    $logPath = APP_PATH . 'bin/logs/roomHeartbeat/' . date('Ymd') . '.log';
                    foreach ( $heartbeatData as $itemHeartbeat ) {
                        $itemArr = explode('_', $itemHeartbeat);
                        if ( count($itemArr) != 2 || intval($itemArr[0]) != $itemArr[0] || intval($itemArr[1]) != $itemArr[1] ) {
                            continue;
                        }
                        // 请求离开房间接口
                        $cmd = "/usr/local/php/bin/php " . APP_PATH . "bootstrap/bootstrap.php room leaveRoom $itemHeartbeat >> $logPath 2>&1";
                        shell_exec($cmd);
                        $this->redis->zRem('room:heartbeat', $itemHeartbeat);
                    }
                }

                $this->redis->zRemRangeByScore('room:heartbeat', 0, time() - 60);

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

            } catch ( \Phalcon\Db\Exception $e ) {
                try {
                    $this->db->connect();
                } catch ( \PDOException $e ) {
                    echo $e;
                }
            } catch ( \PDOException $e ) {
                try {
                    $this->db->connect();
                } catch ( \PDOException $e ) {
                    echo $e;
                }
            } catch ( Exception $e ) {
                echo $e;
            }

            sleep(1);

            echo sprintf("%s no room heartbeat status update!\n", date('r'));
        }
    }

    public function leaveRoomAction( $params )
    {
        if ( !$params ) {
            print '参数错误';
            return;
        }
        $itemHeartbeat = $params[0];
        $itemArr       = explode('_', $itemHeartbeat);
        if ( count($itemArr) != 2 || intval($itemArr[0]) != $itemArr[0] || intval($itemArr[1]) != $itemArr[1] ) {
            print '参数错误';
            return;
        }
        $nRoomId = $itemArr[0];
        $nUserId = $itemArr[1];
        $result  = $this->httpRequest(sprintf('%s/live/room/leave?%s', $this->config->application->app_api_url, http_build_query([
            'uid'        => $nUserId,
            'room_id'    => $nRoomId,
            'debug'      => 1,
            'cli_api_key' => $this->config->application->cli_api_key,
        ])));
        print "【" . date('Y-m-d H:i:s') . "】用户id：$nUserId; 房间ID:$nRoomId; 退出结果：$result\n";
    }



}