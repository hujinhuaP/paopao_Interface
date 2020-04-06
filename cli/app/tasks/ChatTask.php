<?php

namespace app\tasks;

use Phalcon\Exception;
use RedisException;

/**
 * ChatTask 私聊心跳
 */
class ChatTask extends MainTask
{
    /**
     * 私聊心跳检测
     */
    public function heartbeatAction()
    {
        while ( 1 ) {

            try {
                $aAnchorUserId = $this->redis->zRangeByScore('chat:anchor:heartbeat', 0, time() - 60);
                $aChatUserId   = $this->redis->zRangeByScore('chat:user:heartbeat', 0, time() - 60);
                $data          = array_merge($aAnchorUserId, $aChatUserId);
                foreach ( $data as $item ) {
                    $aUserId  = explode(':', $item);
                    $nUserId  = $aUserId[0];
                    $chat_log = $aUserId[1];
                    $sql      = "select status from user_private_chat_log where id = {$chat_log}";
                    $result   = $this->db->query($sql)->fetch();
                    if ( $result['status'] == 0 ) {
                        $this->httpRequest(sprintf('%slive/anchor/cancelPrivateChat?%s', $this->config->application->app_api_url, http_build_query([
                            'user_id'     => $nUserId,
                            'chat_log'    => $chat_log,
                            'cli_api_key' => $this->config->application->cli_api_key,
                            'type'        => 1,
                        ])));
                    } else if ( $result['status'] == 4 ) {
                        $this->httpRequest(sprintf('%slive/anchor/hangupChat?%s', $this->config->application->app_api_url, http_build_query([
                            'uid'          => $nUserId,
                            'chat_log'     => $chat_log,
                            'cli_api_key'  => $this->config->application->cli_api_key,
                            'hang_up_type' => 'auto',
                            'detail'       => '一分钟内没有心跳没有更新'
                        ])));
                    }
                }
                $this->redis->zRemRangeByScore('chat:anchor:heartbeat', 0, time() - 60);

                $this->redis->zRemRangeByScore('chat:user:heartbeat', 0, time() - 60);

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

        }
    }

    /**
     * 用户批量打招呼
     */
    public function userSayHiAction( $params )
    {
        $nUserId = $params['user_id'];
        $sql     = 'SELECT * FROM user WHERE user_id=:user_id LIMIT 1';

        $oResult = $this->db->query($sql, [
            'user_id' => $nUserId,
        ]);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $aUser     = $oResult->fetchAll();
        $sQuerySql = 'SELECT * FROM kv WHERE kv_key="anchor_reply_normal_user"';
        $oResult   = $this->db->query($sQuerySql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $kvData                  = $oResult->fetchAll();
        $isAnchorReplyNormalUser = $kvData[0]['kv_value'];
        try {
            // 查到一定数量的主播（会员20个 普通用户10个）
            $vipSayHiCount    = 20;
            $normalSayHiCount = 10;
            $sayHiCount       = $aUser[0]['user_member_expire_time'] > time() ? $vipSayHiCount : $normalSayHiCount;
//            $sayHiCount = 10;
            $sql = "select a.user_id,u.user_sex from anchor as a inner join user as u on a.user_id = u.user_id where u.user_is_superadmin = 'N' order by a.anchor_chat_status desc,rand() limit " . $sayHiCount;

            $oResult = $this->db->query($sql);
            $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $userSayHiAnchorListKey = 'user_sayhi_anchor_list:' . $nUserId;
            foreach ( $oResult->fetchAll() as $v ) {
                //给主播发消息
                if ( $nUserId > $v['user_id'] ) {
                    $chat_room_id = $nUserId . '_' . $v['user_id'];
                } else {
                    $chat_room_id = $v['user_id'] . '_' . $nUserId;
                }
                $this->httpRequest(sprintf('%suser/chat/send?%s', $this->config->application->app_api_url, http_build_query([
                    'chat_room_id' => $chat_room_id,
                    'to_user_id'   => $v['user_id'],
                    'type'         => 'say_hi',
                    'debug'        => 1,
                    'cli_api_key'  => $this->config->application->cli_api_key,
                    'uid'          => $nUserId
                ])));
                // 没有充值过的用户 需要添加主播自动回复
                if ( $isAnchorReplyNormalUser == 1 && $aUser[0]['user_total_coin'] == 0 ) {
                    $this->redis->lPush($userSayHiAnchorListKey, $v['user_id']);
                }
            }
            // 没有充值过的用户 需要添加主播自动回复
            if ( $isAnchorReplyNormalUser == 1 && $aUser[0]['user_total_coin'] == 0 ) {
                $shouldAutoReplyKey = 'should_auto_reply_user_list';
                $this->redis->zAdd($shouldAutoReplyKey, time(), $nUserId);
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

    }

    /**
     * 每天12点 将签约主播上线记录的结束时间为空的记录 结束 并记录到统计记录表
     * 并新开一条12点开始的记录
     */
    public function checkSignAnchorOnlineStatusAction()
    {
        $offline_time = time();
        if ( date('H') != '12' ) {
            print "该脚本只能在12点执行\n";
            die;
        }
        $sql = 'SELECT * FROM user_online_log WHERE offline_time= 0 and online_time < ' . $offline_time;

        $oResult = $this->db->query($sql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        $onlineLogSaveDefault = [
            'user_id'     => 0,
            'online_time' => time(),
            'create_time' => time(),
            'update_time' => time(),
        ];
        $onlineLogSaveAll     = [];

        // 此脚本只能在12点执行 统计数据写入前一天的 统计
        $stat_time = strtotime(date('Y-m-d')) - 24 * 3600;

        $statUpdateStrArr = [];

        foreach ( $oResult->fetchAll() as $key => $resultItem ) {
            $tmp            = $onlineLogSaveDefault;
            $tmp['user_id'] = $resultItem['user_id'];
            ksort($tmp);
            $onlineLogSaveAll[] = '(' . implode(',', $tmp) . ')';

            $online_duration_item = $offline_time - $resultItem['online_time'];
            $statUpdateStrArr[]   = "update anchor_sign_stat set online_duration = online_duration + $online_duration_item where user_id = {$resultItem['user_id']} AND stat_date = {$stat_time}";
        }

        //新增online log数据
        $onlineLogKeyArr = array_keys($onlineLogSaveDefault);
        sort($onlineLogKeyArr);
        $onlineLogKeyStr   = implode(',', $onlineLogKeyArr);
        $onlineLogValueStr = implode(',', $onlineLogSaveAll);


        // 将之前的数据结束
        $sUpdateSql = "update user_online_log set offline_time = $offline_time,duration = $offline_time - online_time where offline_time= 0 and online_time <" . $offline_time;
        $this->db->execute($sUpdateSql);
        print $sUpdateSql . "\n";

        // 添加新的数据
        $sInsertSql = sprintf('INSERT INTO user_online_log(%s) VALUES %s;', $onlineLogKeyStr, $onlineLogValueStr);
        $this->db->execute($sInsertSql);
        print $sInsertSql . "\n";

        // 添加到统计中去
        $statUpdateSql = implode(',', $statUpdateStrArr);
        $this->db->execute($statUpdateSql);
        print $statUpdateSql . "\n";


    }

    /**
     * 每分钟支付
     */
    public function videoPayAction()
    {
        while ( 1 ) {

            try {
                $second          = intval(date('s'));
                $beginTimeSecond = intval(date('s', time() - 5));
                if ( $second > $beginTimeSecond ) {
                    $chatUserChatId = $this->redis->zRangeByScore('chat:user:chat_id', $beginTimeSecond, $second);
                } else {
                    $chatUserChatId  = $this->redis->zRangeByScore('chat:user:chat_id', 0, $second);
                    $chatUserChatIdS = $this->redis->zRangeByScore('chat:user:chat_id', $beginTimeSecond, 59);
                    $chatUserChatId  = array_merge($chatUserChatId, $chatUserChatIdS);
                }
                $logPath = APP_PATH . 'bin/logs/videopay/' . date('Ymd') . '.log';
                foreach ( $chatUserChatId as $item ) {
                    $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat videoPayCmd $item >> $logPath 2>&1";
                    shell_exec($cmd);
//                    $aUserId  = explode(':', $item);
//                    $nUserId  = $aUserId[0];
//                    $chat_log = $aUserId[1];
//                    $sql      = "select status from user_private_chat_log where id = {$chat_log}";
//                    $result   = $this->db->query($sql)->fetch();
//                    if ( $result['status'] == 4 ) {
//                        // 需要付钱了
//                        $url = $this->config->application->app_api_url . "live/anchor/privateChatMinuteNew?user_id={$nUserId}&chat_log={$chat_log}&do_cli=hzjkb24mGJ8RWOL7GLp4U7T";
//                    } else {
//                        $this->redis->zrem('chat:user:chat_id', $item);
//                        continue;
//                    }
//                    $oResult       = $this->httpRequest($url);
//                    $resultObj     = json_decode($oResult);
//                    $oHangUpResult = '';
//                    if ( $resultObj->c != 0 ) {
//                        // 需要挂断
//                        $url           = $this->config->application->app_api_url . "live/anchor/hangUpChat?user_id={$nUserId}&chat_log={$chat_log}&do_cli=hzjkb24mGJ8RWOL7GLp4U7T";
//                        $oHangUpResult = $this->httpRequest($url);
//                    }
//                    print "【" . date('Y-m-d H:i:s') . "】用户id：$nUserId; 聊天id:$chat_log; 付费结果：$oResult; 挂断结果：$oHangUpResult\n";
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

    /**
     * @param array $params
     * 执行每条扣费
     */
    public function videoPayCmdAction( $params = [] )
    {
        if ( !$params ) {
            exit('参数错误');
        }
        $aUserId = explode(':', $params[0]);
        if ( count($aUserId) != 2 ) {
            exit('参数错误');
        }
        $nUserId  = $aUserId[0];
        $chat_log = $aUserId[1];
        if ( !$nUserId || !$chat_log ) {
            exit('参数错误');
        }
        $lastPayTimeKey = 'chat:user:pay:' . $chat_log;
        $lastPayTime    = $this->redis->get($lastPayTimeKey);
        $thisPayTime    = time();
        if ( $thisPayTime - intval($lastPayTime) < 50 ) {
            exit(date('H:i:s') . $chat_log . "距上次扣费不超过50秒\n");
        }
        $sql    = "select status,create_time from user_private_chat_log where id = {$chat_log}";
        $result = $this->db->query($sql)->fetch();
        if ( $result['status'] != 4 ) {
            $this->redis->zrem('chat:user:chat_id', $params[0]);
            $this->redis->del($lastPayTimeKey);
            die;
        }
        // 需要付钱了
        if ( $result['create_time'] > time() - 10 ) {
            print "第一分钟不在此付费\n";
            return;
        }
        $oResult       = $this->httpRequest(sprintf('%slive/anchor/privateChatMinuteNew?%s', $this->config->application->app_api_url, http_build_query([
            'uid'         => $nUserId,
            'chat_log'    => $chat_log,
            'debug'       => 1,
            'cli_api_key' => $this->config->application->cli_api_key,
        ])));
        $resultObj     = json_decode($oResult);
        $oHangUpResult = '';

        if ( $resultObj->c != 0 ) {
            // 需要挂断
            $oHangUpResult = $this->httpRequest(sprintf('%slive/anchor/hangupChat?%s', $this->config->application->app_api_url, http_build_query([
                'chat_log'     => $chat_log,
                'uid'          => $nUserId,
                'debug'        => 1,
                'cli_api_key'  => $this->config->application->cli_api_key,
                'hang_up_type' => 'auto',
                'detail'       => $resultObj->m
            ])));
        } else {
            //扣费成功记录此条记录的最后扣费时间
            $this->redis->set($lastPayTimeKey, $thisPayTime, 600);
        }
        print "【" . date('Y-m-d H:i:s') . "】用户id：$nUserId; 聊天id:$chat_log; 付费结果：$oResult; 挂断结果：$oHangUpResult\n";

    }


    /**
     * 从红人匹配大厅转到总匹配大厅
     */
    public function changeMatchRoomAction()
    {
        while ( 1 ) {

            try {
                //红人匹配大厅匹配时间达到4-6秒的进入 总匹配大厅
                $hotMatchCenterUserList = $this->redis->zRangeByScore('match_center_user_list', time() - 6, time() - 4);
                $logPath                = APP_PATH . 'bin/logs/changematchroom/' . date('Ymd') . '.log';
                foreach ( $hotMatchCenterUserList as $item ) {
                    $info = json_decode($item, TRUE);
                    if ( !$info ) {
                        continue;
                    }
                    $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat changeMatchRoomCmd {$info['user_id']} >> $logPath 2>&1";
                    shell_exec($cmd);
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


    /**
     * 从高颜值匹配大厅转到总匹配大厅
     *
     */
    public function changeMatchRoomBeautyAction()
    {
        while ( 1 ) {

            try {
                //高颜值匹配大厅匹配时间达到4-6秒的进入 总匹配大厅
                $hotMatchCenterUserList = $this->redis->zRangeByScore('match_center_beauty_list', time() - 6, time() - 4);
                $logPath                = APP_PATH . 'bin/logs/changematchroom/' . date('Ymd') . '.log';
                foreach ( $hotMatchCenterUserList as $item ) {
                    $info = json_decode($item, TRUE);
                    if ( !$info ) {
                        continue;
                    }
                    $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat changeMatchRoomCmd {$info['user_id']} 1 >> $logPath 2>&1";
                    shell_exec($cmd);
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


    public function changeMatchRoomCmdAction( $params = [] )
    {
        if ( !$params ) {
            exit('参数错误');
        }
        $isBeauty = $params[1] ?? 0;
        $result   = $this->httpRequest(sprintf('%suser/chat/matchCenter?%s', $this->config->application->app_api_url, http_build_query([
            'debug'       => 1,
            'cli_api_key' => 'hzjkb24',
            'uid'         => $params[0],
            'cli_api_key' => $this->config->application->cli_api_key,
            'is_all'      => '1',
            'is_beauty'   => $isBeauty
        ])));
        print "【" . date('Y-m-d H:i:s') . "】高颜值：{$isBeauty}； 用户id：{$params[0]}; 结果：$result\n";
    }

    public function customerServiceAutoAction( $params = [] )
    {
        $user_chat_id = $params['user_chat_id'];
        $sql          = "select * from user_chat where user_chat_id = {$user_chat_id}";
        $result       = $this->db->query($sql)->fetch();
        if ( !$result ) {
            exit();
        }
        $user_id           = $result['user_chat_send_user_id'];
        $chat_room_id      = $result['user_chat_room_id'];
        $customerServiceId = $result['user_chat_receiv_user_id'];
        $sFlgContent       = $result['user_chat_content'];
        /**
         * 判断用户发送的内容是否存在自动回复flg
         *      是： 自动回复flg对应内容
         *      否：
         *          判断该用户最近一次回复是否超过半小时
         *               是：回复first 内容
         *               否：回复unmatch 内容
         */

        $sql = "select * from user_chat where user_chat_room_id = :user_chat_room_id and user_chat_send_user_id = :user_id and user_chat_id < :user_chat_id order by user_chat_id desc";
        $res = $this->db->query($sql, [
            'user_chat_room_id' => $chat_room_id,
            'user_chat_id'      => $user_chat_id,
            'user_id'           => $user_id
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oUserToServiceLog = $res->fetch();
        if ( !$oUserToServiceLog ) {
            $lastMsgTime = 0;
        } else {
            $lastMsgTime = $oUserToServiceLog['user_chat_create_time'];
        }

        $firstContent     = '';
        $unmatchContent   = '';
        $content          = '';
        $autoContentSql   = 'select * from customer_service_reply';
        $autoContentModel = $this->db->query($autoContentSql);
        $autoContentModel->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $autoContentModel->fetchAll() as $autoContentItem ) {
            $breakFlg = FALSE;
            switch ( $autoContentItem['type'] ) {
                case 'first':
                    $firstContent = $autoContentItem['content'];
                    break;
                case 'unmatch':
                    $unmatchContent = $autoContentItem['content'];
                    break;
                case 'reply':
                    if ( $sFlgContent == $autoContentItem['reply_flg'] ) {
                        $content  = $autoContentItem['content'];
                        $breakFlg = TRUE;
                    }
                    break;
            }
            if ( $breakFlg ) {
                break;
            }
        }

        // 如果没有匹配到内容
        if ( !$content ) {
            if ( time() - $lastMsgTime > 30 * 60 ) {
                // 最近一次回复超过半小时
                $content = $firstContent;
            } else {
                $content = $unmatchContent;
            }
        }

        $url    = sprintf('%suser/chat/send', $this->config->application->app_api_url);
        $data   = [
            'chat_room_id' => $chat_room_id,
            'is_service'   => 1,
            'to_user_id'   => $user_id,
            'content'      => $content,
            'debug'        => 1,
            'cli_api_key'  => $this->config->application->cli_api_key,
            'uid'          => $customerServiceId
        ];
        $params = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-type:application/x-www-form-urlencoded",
                'content' => http_build_query($data),
            ]
        ];
//        $this->httpRequest($url, FALSE, stream_context_create($params));
        $this->httpRequest($url, $data);
    }


    /**
     * 定时1分钟 判断聊天状态为正在聊天 但是没有1分钟内没有心跳的数据 判断为异常挂断 执行挂断接口
     */
    public function checkStatusAction()
    {
        $checkTime = time() - 30;
        $sql       = 'select * from user_private_chat_log where status = 4 AND create_time < ' . $checkTime;
        $res       = $this->db->query($sql);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        // 取1分钟内的心跳
        $aAnchorUserHeartBeat = $this->redis->zRangeByScore('chat:anchor:heartbeat', time() - 60, time() + 5);
        $aUserHeartBeat       = $this->redis->zRangeByScore('chat:user:heartbeat', time() - 60, time() + 5);

        $anchorChatLogArr = [];
        foreach ( $aAnchorUserHeartBeat as $anchorHeartChatItem ) {
            $heartChatItemArr = explode(':', $anchorHeartChatItem);
            $chat_log         = $heartChatItemArr[1] ?? '';
            if ( $chat_log ) {
                $anchorChatLogArr[] = $chat_log;
            }
        }

        $userChatLogArr = [];
        foreach ( $aUserHeartBeat as $userHeartChatItem ) {
            $heartChatItemArr = explode(':', $userHeartChatItem);
            $chat_log         = $heartChatItemArr[1] ?? '';
            if ( $chat_log ) {
                $userChatLogArr[] = $chat_log;
            }
        }

        //取到所有的在聊天记录  判断该聊天的心跳是否在40秒内还有
        foreach ( $res->fetchAll() as $key => $resultItem ) {
            $chatLogId = $resultItem['id'];
            if ( !in_array($chatLogId, $userChatLogArr) ) {
                // 用户心跳不在 用户挂断
                $this->httpRequest(sprintf('%slive/anchor/hangupChat?%s', $this->config->application->app_api_url, http_build_query([
                    'uid'          => $resultItem['chat_log_user_id'],
                    'chat_log'     => $chatLogId,
                    'debug'        => 1,
                    'cli_api_key'  => $this->config->application->cli_api_key,
                    'hang_up_type' => 'auto',
                    'detail'       => '用户心跳不在'
                ])));
            } else if ( !in_array($chatLogId, $anchorChatLogArr) ) {
                // 主播心跳不在 主播挂断
                $this->httpRequest(sprintf('%slive/anchor/hangupChat?%s', $this->config->application->app_api_url, http_build_query([
                    'uid'          => $resultItem['chat_log_anchor_user_id'],
                    'chat_log'     => $chatLogId,
                    'debug'        => 1,
                    'cli_api_key'  => $this->config->application->cli_api_key,
                    'hang_up_type' => 'auto',
                    'detail'       => '主播心跳不在'
                ])));

            }
        }
    }

    /**
     * 通过付费次数判断是否异常
     * 查出所有通话状态为 通话中的记录
     * 根据付费情况判断 是否进入
     */
    public function checkStatusByPayAction()
    {
        $current_time = time();
        $sql          = <<<SQL
select l.id,l.create_time,l.chat_log_user_id from user_private_chat_log as l inner join `user` as u on u.user_id = l.chat_log_user_id where l.status = 4 AND u.user_is_superadmin = 'N' AND l.create_time < $current_time
SQL;
        $res          = $this->db->query($sql);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        $logPath = APP_PATH . 'bin/logs/checkStatusByPay/' . date('Ymd') . '.log';
        foreach ( $res->fetchAll() as $key => $resultItem ) {
            $payTimesRedisKey = sprintf('chat:pay:%s', $resultItem['id']);
            $payTimes         = $this->redis->get($payTimesRedisKey);
            $payTimes         = intval($payTimes);
            if ( !$payTimes && $resultItem['create_time'] < $current_time - 15 ) {
                // 没有支付 或者为0  且已经开始15秒以上 则表示用户没有进入房间 需要挂断
                $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat checkStatusByPayCmd {$resultItem['chat_log_user_id']} {$resultItem['id']} >> $logPath 2>&1";
                shell_exec($cmd);
            } else {
                //根据时间判断 应该支付的次数
                $shouldPayTimes = intval(($current_time - $resultItem['create_time']) / 60) + 1;
                if ( $shouldPayTimes - $payTimes >= 2 ) {
                    // 如果大于等于2 了 那么必须要退出
                    $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat checkStatusByPayCmd {$resultItem['chat_log_user_id']} {$resultItem['id']} >> $logPath 2>&1";
                    shell_exec($cmd);
                } else if ( $shouldPayTimes - $payTimes == 1 ) {
                    // 如果只差1分钟 判断 最后一分钟是否过了15秒
                    if ( $current_time - $resultItem['create_time'] - $payTimes * 60 >= 15 ) {
                        $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat checkStatusByPayCmd {$resultItem['chat_log_user_id']} {$resultItem['id']} >> $logPath 2>&1";
                        shell_exec($cmd);
                    }
                }
            }
        }
    }


    /**
     * @param array $params
     * 通过付费次数判断是否异常
     * 执行挂断
     */
    public function checkStatusByPayCmdAction( $params = [] )
    {
        if ( !$params ) {
            exit('参数错误');
        }
        $result = $this->httpRequest(sprintf('%slive/anchor/hangupChat?%s', $this->config->application->app_api_url, http_build_query([
            'uid'          => $params[0],
            'chat_log'     => $params[1],
            'debug'        => 1,
            'cli_api_key'  => $this->config->application->cli_api_key,
            'hang_up_type' => 'auto',
            'detail'       => '用户付费次数缺少'
        ])));
        print "【" . date('Y-m-d H:i:s') . "】用户id：{$params[0]};聊天记录：【{$params[1]}】 结果：$result\n";
    }


    /**
     * 主播自动回复未充值用户打招呼消息
     */
    public function anchorAutoReplyAction()
    {
        while ( 1 ) {
            try {
                $data        = $this->redis->zRangeByScore('should_auto_reply_user_list', time() - 3600, time() - 20);
                $logPath     = APP_PATH . 'bin/logs/anchorAutoReply.log';
                $sendUserArr = [];
                foreach ( $data as $item ) {
                    $userId        = $item;
                    $anchorListKey = 'user_sayhi_anchor_list:' . $userId;
                    $anchorUserId  = $this->redis->lPop($anchorListKey);
                    if ( !$anchorUserId ) {
                        $this->redis->zRem('should_auto_reply_user_list', $userId);
                        continue;
                    }
                    $anchorLen = $this->redis->lLen($anchorListKey);
                    if ( $anchorLen > 6 ) {
                        $sendUserArr[] = $userId;
                    } else {
                        $this->redis->zRem('should_auto_reply_user_list', $userId);
                        continue;
                    }
                    // 取一个主播 自动回复消息
                    $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat anchorAutoReplyCmd $userId $anchorUserId $anchorLen >> $logPath 2>&1";
                    shell_exec($cmd);
                }

                // 第二次回复
                $secondData = $this->redis->zRangeByScore('anchor_auto_image_video_reply', time() - 3600, time() - 20);

                foreach ( $secondData as $item ) {
                    $itemArr      = explode(':', $item);
                    $userId       = $itemArr[0];
                    $anchorUserId = $itemArr[1];
                    print '当前已发送:' . json_encode($sendUserArr);
                    if ( in_array($userId, $sendUserArr) ) {
                        continue;
                    }
                    // 取一个主播 自动回复消息
                    $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat anchorAutoReplyVideoCmd $userId $anchorUserId >> $logPath 2>&1";
                    shell_exec($cmd);
                    $sendUserArr[] = $userId;
                }
                $sendUserArr = [];
                $data        = $this->redis->zRangeByScore('should_auto_reply_user_list', time() - 3600, time() - 20);
                $secondData  = $this->redis->zRangeByScore('anchor_auto_image_video_reply', time() - 3600, time() - 20);
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
            sleep(rand(20, 40));

        }
    }

    /**
     * 主播自动回复未充值用户打招呼消息 执行
     */
    public function anchorAutoReplyCmdAction( $params = [] )
    {
        $nUserId      = $params[0];
        $anchorUserId = $params[1];
        $anchorLen    = $params[2];
        if ( $nUserId > $anchorUserId ) {
            $chat_room_id = $nUserId . '_' . $anchorUserId;
        } else {
            $chat_room_id = $anchorUserId . '_' . $nUserId;
        }
        $contentFlg = 9 - $anchorLen;
        if ( $contentFlg >= 3 ) {
            return;
        }
        //发一条文字消息
        $contentArr = [
            '0' => [
                '哥哥你好吖我有36E哟，要来跟我视频吗？我太直接了想看都能看哟~',
                '帅哥你在干嘛呀？方便和我视频聊聊有趣的吗？',
                '嗨小哥哥，你是新来的吗~能问下你有女朋友吗？我想找男朋友~',
                '你愿不愿意嘛，可以看看你吗~',
            ],
            '1' => [
                '哥哥，我刚洗完澡呢~，每次都是一个人寂寞的很呢~有机会陪我一起吗~',
                '小哥哥在呢~我买了条新短裙~我自己感觉有点小了~能不能和我视频看看合不合身吗？',
                '嗨哥哥~我在呢~要不要跟人家视频呀，新买的内衣好像小了，都漏**了、哥哥要不要视频帮人家看看嘛~要的话发个视频',
                '哥哥好，我在床上无聊呢~我今天的睡衣很漂亮吧，你喜欢吗？嘿嘿，是不是很可爱！',
            ],
            '2' => [
                '哥哥在呢~可以视频帮我看看我的新衣服是不是合身哦！',
                '弹我哦~',
                '哥哥你无聊是不是呢~我也很无聊没事干呢~要不视频连天聊一会儿吗~',
                '小哥哥你好啊~想看看我做一字马吗~来视频嘛~'
            ]
        ];
        $content    = $contentArr[ $contentFlg ][ rand(0, 3) ];
        $this->httpRequest(sprintf('%suser/chat/send?%s', $this->config->application->app_api_url, http_build_query([
            'chat_room_id' => $chat_room_id,
            'content'      => $content,
            'to_user_id'   => $nUserId,
            'type'         => 'normal',
            'debug'        => 1,
            'cli_api_key'  => $this->config->application->cli_api_key,
            'uid'          => $anchorUserId
        ])));

        // 准备发送下一条
        $imageVideoAnchorAutoKey = 'anchor_auto_image_video_reply';
        $this->redis->zAdd($imageVideoAnchorAutoKey, time(), sprintf('%s:%s', $nUserId, $anchorUserId));

    }


    /**
     * 主播自动回复未充值用户打招呼消息 执行
     */
    public function anchorAutoReplyVideoCmdAction( $params = [] )
    {
        $nUserId      = $params[0];
        $anchorUserId = $params[1];
        if ( $nUserId > $anchorUserId ) {
            $chat_room_id = $nUserId . '_' . $anchorUserId;
        } else {
            $chat_room_id = $anchorUserId . '_' . $nUserId;
        }
        //发一条视频或者图片
        $sql    = "select * from user_video where user_id = {$anchorUserId} and watch_type = 'free' limit 1";
        $result = $this->db->query($sql)->fetch();
        if ( $result ) {
            $content  = $result['play_url'];
            $msg_type = 'video';
            $extra    = $result['cover'];
        } else {
            //取图片
            $sql     = "select * from anchor where user_id = {$anchorUserId} limit 1";
            $result  = $this->db->query($sql)->fetch();
            $showImg = $result['anchor_check_img'];
            if ( !$showImg ) {
                $showImg = $result['anchor_images'];
            }
            if ( !$showImg ) {
                return;
            }
            $showImgArr = explode(',', $showImg);
            $content    = $showImgArr[1];
            $msg_type   = 'image';
            $extra      = '';
        }

        $this->httpRequest(sprintf('%suser/chat/send?%s', $this->config->application->app_api_url, http_build_query([
            'chat_room_id' => $chat_room_id,
            'content'      => $content,
            'extra'        => $extra,
            'msg_type'     => $msg_type,
            'to_user_id'   => $nUserId,
            'type'         => 'normal',
            'debug'        => 1,
            'cli_api_key'  => $this->config->application->cli_api_key,
            'uid'          => $anchorUserId
        ])));
        $imageVideoAnchorAutoKey = 'anchor_auto_image_video_reply';
        $this->redis->zRem($imageVideoAnchorAutoKey, sprintf('%s:%s', $nUserId, $anchorUserId));
    }


    /**
     * 聊天中延迟退出聊天
     */
    public function delayOfflineAction()
    {
        while ( 1 ) {
            try {
                // 距离现在之前50到60秒的数据
                $data    = $this->redis->zRangeByScore('chat_tim_offline', time() - 60, time() - 50);
                $logPath = APP_PATH . 'bin/logs/delayoffline/' . date('Ymd') . '.log';
                foreach ( $data as $item ) {
                    $saveArr = explode('-', $item);
                    if ( count($saveArr) != 3 ) {
                        continue;
                    }
                    $userId    = $saveArr[0];
                    $chatLogId = $saveArr[1];
                    $isAnchor  = $saveArr[2];
                    $flg       = $this->redis->zRem('chat_tim_offline', $item);
                    if ( $flg ) {
                        // 删除成功，执行真删除
                        $cmd = "/usr/local/php/bin/php /data/wwwroot/paopao_new/cli/bootstrap/bootstrap.php chat delayOfflineCmd $userId $chatLogId $isAnchor >> $logPath 2>&1";
                        shell_exec($cmd);
                    }

                }
                $data = $this->redis->zRangeByScore('chat_tim_offline', time() - 6, time() - 4);
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

    public function delayOfflineCmdAction( $params = [] )
    {
        if ( !$params && count($params) != 3 ) {
            return FALSE;
        }
        $user_id   = $params[0];
        $chatLogId = $params[1];
        $isAnchor  = $params[2];
//        $this->httpRequest(sprintf('%snotify/notify/delayOffline?%s', $this->config->application->app_api_url, http_build_query([
//            'user_id' => $user_id,
//        ])));
        if ( $isAnchor == 'Y' ) {
            $detail = '主播掉线';
        } else {
            $detail = '用户掉线';
        }
        $result = $this->httpRequest(sprintf('%slive/anchor/hangupChat?%s', $this->config->application->app_api_url, http_build_query([
            'uid'          => $user_id,
            'chat_log'     => $chatLogId,
            'debug'        => 1,
            'cli_api_key'  => $this->config->application->cli_api_key,
            'hang_up_type' => 'auto',
            'detail'       => $detail,
        ])));
        print $user_id . $detail . ':' . $result . "\n";
    }


    /**
     * 主播批量打招呼
     */
    public function anchorBatchSayHiAction( $params )
    {
        $nUserId = $params['user_id'];
        $sql     = 'SELECT * FROM user WHERE user_id=:user_id LIMIT 1';

        $oResult = $this->db->query($sql, [
            'user_id' => $nUserId,
        ]);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $aUser = $oResult->fetchAll();
        // 主播批量打招呼单次用户数
        $sQuerySql = 'SELECT * FROM kv WHERE kv_key="anchor_batch_say_hi_num"';
        $oResult   = $this->db->query($sQuerySql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $kvData                  = $oResult->fetchAll();
        $anchor_batch_say_hi_num = $kvData[0]['kv_value'];
        try {
            // 注册时间在7天之内 在线的用户 是会员的优先
//            $sayHiCount = 10;
            $lastRegisterTime = time() - 7 * 3600 * 24;
            $sql              = "select user_id,user_member_expire_time from `user` where user_is_anchor = 'N' AND user_create_time > $lastRegisterTime AND user_coin + user_free_coin < 40 and user_online_status = 'Online' order by rand() limit $anchor_batch_say_hi_num; ";

            $oResult = $this->db->query($sql);
            $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            foreach ( $oResult->fetchAll() as $v ) {
                //给用户发消息
                if ( $nUserId > $v['user_id'] ) {
                    $chat_room_id = $nUserId . '_' . $v['user_id'];
                } else {
                    $chat_room_id = $v['user_id'] . '_' . $nUserId;
                }
                $this->httpRequest(sprintf('%suser/chat/send?%s', $this->config->application->app_api_url, http_build_query([
                    'chat_room_id' => $chat_room_id,
                    'to_user_id'   => $v['user_id'],
                    'type'         => 'say_hi',
                    'debug'        => 1,
                    'cli_api_key'  => $this->config->application->cli_api_key,
                    'uid'          => $nUserId
                ])));
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

    }

    /**
     * 用户匹配 主播点取后 用户没进入房间
     */
    public function checkMatchErrorAction()
    {
        while ( 1 ) {

            try {
                //  取当前时间应该发送的消息 15秒前的数据
                $existData = $this->redis->zRangeByScore('wait_user_enter_match', 0, time() - 15);
                foreach ( $existData as $item ) {
                    $this->redis->zDelete('wait_user_enter_match', $item);
                    $keyInfo = explode('-', $item);
                    if ( count($keyInfo) != 2 ) {
                        continue;
                    }
                    $chat_log = $keyInfo[0];
                    $nUserId  = $keyInfo[1];
                    $result   = $this->httpRequest(sprintf('%slive/anchor/hangupChat?%s', $this->config->application->app_api_url, http_build_query([
                        'uid'          => $nUserId,
                        'debug'        => 1,
                        'chat_log'     => $chat_log,
                        'cli_api_key'  => $this->config->application->cli_api_key,
                        'hang_up_type' => 'auto',
                        'detail'       => '用户没有匹配进入房间'
                    ])));
                    echo sprintf('[%s] userID [%s] %s', date('Y-m-d H:i:s'), $nUserId, $result) . "\n";

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
                } catch ( \Exception $e ) {
                    echo $e;
                }

            } catch ( Exception $e ) {
                echo $e;
            }
            sleep(1);

        }
    }

}