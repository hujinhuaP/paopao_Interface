<?php

namespace app\tasks;

use Phalcon\Exception;
use RedisException;

/**
 * AnchorTask 主播
 */
class AnchorTask extends MainTask
{
    /**
     * livePushAction 开播提醒
     */
    public function livePushAction( $params )
    {
        $nAnchorUserId = $params['anchor_user_id'];
        $sql           = 'SELECT * FROM anchor WHERE user_id=:user_id LIMIT 1';

        $oResult = $this->db->query($sql, [
            'user_id' => $nAnchorUserId,
        ]);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $aAnchor = $oResult->fetchAll();

        $sql = 'SELECT * FROM user WHERE user_id=:user_id LIMIT 1';

        $oResult = $this->db->query($sql, [
            'user_id' => $nAnchorUserId,
        ]);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $aUser = $oResult->fetchAll();

        $nUserFollowId = 0;

        $sql = 'SELECT uf.user_follow_id,uf.user_id FROM user_follow AS uf JOIN user AS u ON uf.user_id=u.user_id WHERE uf.to_user_id=:to_user_id AND uf.user_follow_id>:user_follow_id AND uf.user_follow_is_remind="Y" AND u.user_remind>0 ORDER BY  user_follow_id LIMIT 500';

        while ( TRUE ) {

            try {
                $oResult = $this->db->query($sql, [
                    'to_user_id'     => $nAnchorUserId,
                    'user_follow_id' => $nUserFollowId
                ]);
                $aUserId = [];
                $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                foreach ( $oResult->fetchAll() as $v ) {
                    $aUserId[]     = $v['user_id'];
                    $nUserFollowId = $v['user_follow_id'];
                }

                if ( empty($aUserId) ) {
                    break;
                }

                $sAlert   = $this->config->application->app_name;
                $sContent = sprintf('你关注的直播 "%s" 开播了，快去围观吧！', $aUser[0]['user_nickname']);
                $sTyep    = 'anchor_start_live';
                $aData    = [
                    'anchor_user_id'     => $aUser[0]['user_id'],
                    'anchor_category_id' => $aAnchor[0]['anchor_category_id'],
                    'anchor_live_pay'    => $aAnchor[0]['anchor_live_pay'],
                ];

                $client   = new \JPush\Client($this->config->push->jpush->app_key, $this->config->push->jpush->master_secret);
                $response = $client->push()
                    ->setPlatform(array(
                        'ios',
                        'android'
                    ))
                    // 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
                    // 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
                    // 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求

                    ->addAlias($aUserId)
                    // ->addTag(array('tag1', 'tag2'))
                    // ->addRegistrationId($registration_id)

                    ->setNotificationAlert($sAlert)
                    ->iosNotification($sContent, array(
                        'sound'    => 'sound.caf',
                        // 'badge' => '+1',
                        // 'content-available' => true,
                        // 'mutable-content' => true,
                        'category' => 'jiguang',
                        'extras'   => array(
                            'type' => $sTyep,
                            'data' => $aData,
                            'jiguang'
                        ),
                    ))
                    ->androidNotification($sContent, array(
                        'title'  => $sAlert,
                        // 'builder_id' => 2,
                        'extras' => array(
                            'type' => $sTyep,
                            'data' => $aData,
                            'jiguang'
                        ),
                    ))
                    ->message($sContent, array(
                        'title'  => $sAlert,
                        // 'content_type' => 'text',
                        'extras' => array(
                            'type' => $sTyep,
                            'data' => $aData,
                            'jiguang'
                        ),
                    ))
                    ->options(array(
                        // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
                        // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
                        // 这里设置为 100 仅作为示例

                        // 'sendno' => 100,

                        // time_to_live: 表示离线消息保留时长(秒)，
                        // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
                        // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
                        // 这里设置为 1 仅作为示例

                        // 'time_to_live' => 1,

                        // apns_production: 表示APNs是否生产环境，
                        // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

                        'apns_production' => FALSE,

                        // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
                        // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
                        // 这里设置为 1 仅作为示例

                        // 'big_push_duration' => 1
                    ))
                    ->send();

                print_r($response);
                echo sprintf("%s %s-%s live push user %s.\n", date('r'), $aUser[0]['user_id'], $aUser[0]['user_nickname'], implode(',', $aUserId));

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
            } catch ( \Exception $e ) {
                echo $e;
            }
        }
        echo sprintf("%s Anchor %s-%s live push ok.\n", date('r'), $aUser[0]['user_id'], $aUser[0]['user_nickname']);
    }

    /**
     * heartbeatAction 主播心跳检测
     */
    public function heartbeatAction()
    {
        while ( 1 ) {

            try {

                $aAnchorUserId = $this->redis->zRangeByScore('anchor:heartbeat', 0, time() - 60);
                if ( !empty($aAnchorUserId) ) {
                    $sql = 'UPDATE anchor set anchor_is_live="N" WHERE user_id IN (:user_id)';
                    $this->db->execute($sql, [
                        'user_id' => implode(',', $aAnchorUserId),
                    ]);
                    echo sprintf("%s anchor status update ok!\n", date('r'));
                    //私聊状态改变
                    $chat_sql = 'UPDATE anchor set anchor_chat_status = 3 WHERE anchor_chat_status = 1 and  user_id IN (:user_id)';
                    $this->db->execute($chat_sql, [
                        'user_id' => implode(',', $aAnchorUserId),
                    ]);
                    echo sprintf("%s anchor chat status update ok!\n", date('r'));
                }

                $this->redis->zRemRangeByScore('anchor:heartbeat', 0, time() - 60);

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

            echo sprintf("%s no anchor status update!\n", date('r'));
        }
    }

    /**
     * 在线状态 TIM 定时任务
     * 如果主播表显示在线 且接口返回不在线，将状态改为不在线
     */
    public function onlineStatusAction()
    {
        return FALSE;
        file_get_contents(sprintf('%sim/checkOnlineStatus?%s', $this->config->application->app_api_url, http_build_query([
            'debug' => 1
        ])));
    }


    /**
     * old
     * 1.如果当前忙碌的机器人主播大于等于5个 随机下线1到2个
     * 2.如果当前忙碌的机器人主播小于登录5个 随机上线忙碌1到2个
     * 每小时的8分，27分，49分执行
     * new
     * 5-7分钟忙碌状态
     * 然后换几个，保持3-4个在忙碌
     */
    public function robotAction()
    {
        // 查出在线的机器人主播（忙碌） 最早更新的在最上面 如果要下线 从第一个开始
        $sql          = "SELECT u.user_id,a.anchor_chat_status,a.anchor_update_time FROM anchor as a inner join `user` as u on u.user_id = a.user_id WHERE u.user_is_isrobot= 'Y' AND a.anchor_chat_status = 2 order by a.anchor_update_time asc ";
        $onlineAnchor = $this->db->query($sql);
        $onlineAnchor->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $onlineAnchorCount = 0;
        $existTime         = rand(5, 7);
        $canOfflineTime    = time() - $existTime * 60;
//        $offlineCount = rand(1,2);
        $offlineCount   = 0;
        $offlineUserIds = [];
        foreach ( $onlineAnchor->fetchAll() as $key => $anchorItem ) {
            $onlineAnchorCount++;
            if ( $anchorItem['anchor_update_time'] <= $canOfflineTime ) {
                $offlineCount++;
                $offlineUserIds[] = $anchorItem['user_id'];
            }
//            if($onlineAnchorCount <= $offlineCount){
//                $offlineUserIds[] = $anchorItem['user_id'];
//            }
        }

        // 查出离线的机器人主播（离线） 最早更新的在最上面 如果要上线 从第一个开始
        $sql           = "SELECT u.user_id,a.anchor_chat_status FROM anchor as a inner join `user` as u on u.user_id = a.user_id WHERE u.user_is_isrobot= 'Y' AND a.anchor_chat_status = 1 order by a.anchor_update_time asc ";
        $offlineAnchor = $this->db->query($sql);
        $offlineAnchor->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $offlineAnchorCount = 0;
//        $onlineCount = rand(1,2);
        $onlineCount   = $offlineCount + rand(0, 1);
        $onlineUserIds = [];
        foreach ( $offlineAnchor->fetchAll() as $key => $anchorItem ) {
            $offlineAnchorCount++;
            if ( $offlineAnchorCount <= $onlineCount ) {
                $onlineUserIds[] = $anchorItem['user_id'];
            }
        }

        $update_time = time();
        if ( $onlineAnchorCount >= 4 && $offlineUserIds ) {
//        if($offlineUserIds){
            // 需要下线
            $offlineUserIdsStr = implode(',', $offlineUserIds);
            $offlineAnchorSql  = "update anchor set anchor_chat_status = 1,anchor_update_time = {$update_time} where user_id in ({$offlineUserIdsStr})";
            $this->db->execute($offlineAnchorSql);
        }

        if ( $onlineAnchorCount - $offlineCount <= 2 && $onlineUserIds ) {
//        if($onlineUserIds){
            // 需要上线
            $onlineUserIdsStr = implode(',', $onlineUserIds);
            $onlineAnchorSql  = "update anchor set anchor_chat_status = 2,anchor_update_time = {$update_time} where user_id in ({$onlineUserIdsStr})";
            $this->db->execute($onlineAnchorSql);
        }
    }


    /**
     * 每周结算称号
     */
    public function titleChangeAction()
    {
        // 获取称号number 对应的id
        $anchorTitleSql  = 'select anchor_title_id,anchor_title_number from anchor_title_config where anchor_title_number in(1,2,3)';
        $anchorTitleData = $this->db->fetchAll($anchorTitleSql);
        $anchorTitleArr  = array_column($anchorTitleData, 'anchor_title_id', 'anchor_title_number');
        // 7天前所属的周 即上周的排行榜前3 给称号
        $key        = sprintf('ranking:anchor:%s', date('o-W', time() - 7 * 86400));
        $rankSource = $this->redis->zRevRange($key, 0, 2, TRUE);
        $this->db->begin();
        // 取出现有的称号  前三个称号
        $cancelSql = "update anchor set anchor_title_id = 0 where anchor_title_id > 0 and anchor_title_id < 4";
        $this->db->execute($cancelSql);

        // 设置称号
        $updateSqlArr = [];
        $rankId       = 1;
        $userIdArr    = [];
        foreach ( $rankSource as $dataKey => $dataScore ) {
            $userIdArr[]    = $dataKey;
            $titleId        = $anchorTitleArr[ $rankId ];
            $updateSqlArr[] = "update anchor set anchor_title_id = {$titleId} where user_id = " . intval($dataKey);
            $rankId++;
        }
        if ( $updateSqlArr ) {
            $updateSql = implode(';', $updateSqlArr);
            $this->db->execute($updateSql);
            $this->db->commit();
            echo printf("[%s] %s 用户获得称号;执行更新数%d\n", date('Y-m-d H:i:s'), implode(',', $userIdArr), $this->db->affectedRows());
        }


    }


    /**
     * 假在线主播集合中 15秒前的 取出删除 存入 假聊天主播集合
     * 假聊天主播集合中 2分钟前的 取出删除 存入 假离线主播集合
     * 假离线主播集合中 3分钟前的 取出删除 存入假在线主播集合
     */
    public function anchorOfflineModifyAction()
    {
        $online_key  = 'offline_anchor:online';
        $on_chat_key = 'offline_anchor:on_chat';
        $offline_key = 'offline_anchor:offline';

        while ( 1 ) {

            try {
                // 假在线主播集合中 15秒前的 取出删除 存入 假聊天主播集合
                $onlineData = $this->redis->zRangeByScore($online_key, 0, time() - 15);
                foreach ( $onlineData as $item ) {
                    $this->redis->zDelete($online_key, $item);
                    $this->redis->zAdd($on_chat_key, time(), $item);
                }

                // 假聊天主播集合中 2分钟前的 取出删除 存入 假离线主播集合
                $onChatData = $this->redis->zRangeByScore($on_chat_key, 0, time() - 120);
                foreach ( $onChatData as $item ) {
                    $this->redis->zDelete($on_chat_key, $item);
                    $this->redis->zAdd($offline_key, time(), $item);
                }

                $offlineData = $this->redis->zRangeByScore($offline_key, 0, time() - 180);
                foreach ( $offlineData as $item ) {
                    $this->redis->zDelete($offline_key, $item);
                    $this->redis->zAdd($online_key, time(), $item);
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

            sleep(2);
        }

    }

    /**
     * 删除一个月以上没有上线过的用户的守护信息
     */
    public function clearGuardAction()
    {
        $anchorArr  = [];
        $deleteSave = [];

        $sql           = 'select ug.id as log_id,ug.user_id,ug.anchor_user_id,ug.total_coin,ug.current_level,ug.current_level_name,
       ug.guard_status,ug.create_time,ug.update_time,ug.total_use_free_time from user_guard as ug inner join `user` as u 
           on ug.user_id = u.user_id where u.user_logout_time < ' . strtotime("-30 day");
        $userGuardData = $this->db->fetchAll($sql);

        if ( $userGuardData ) {
            foreach ( $userGuardData as $item ) {
                $tmp                                  = [
                    $item['log_id'],
                    $item['user_id'],
                    $item['anchor_user_id'],
                    $item['total_coin'],
                    $item['current_level'],
                    "'{$item['current_level_name']}'",
                    "'{$item['guard_status']}'",
                    $item['create_time'],
                    $item['update_time'],
                    $item['total_use_free_time'],
                    0,
                    time()
                ];
                $deleteSave[]                         = '(' . implode(',', $tmp) . ')';
                $anchorArr[ $item['anchor_user_id'] ] = 1;
            }

            $this->db->begin();
            // 删除记录并保存到备份
            $deleteSql = "delete from user_guard where exists( select 1 from user where user_logout_time < " . strtotime("-30 day") . " AND user_id = user_guard.user_id)";
            $this->db->execute($deleteSql);
            if ( $this->db->affectedRows() <= 0 ) {
                $this->db->rollback();
                print "删除记录执行失败\n";
                die;
            }

            //保存到备份
            $backSaveSql = "INSERT INTO `user_guard_delete`(`id`, `user_id`, `anchor_user_id`, `total_coin`, `current_level`,
                                `current_level_name`, `guard_status`, `create_time`, `update_time`, `total_use_free_time`,
                                `admin_id`, `delete_time`) values " . implode(',', $deleteSave);
            $this->db->execute($backSaveSql);
            if ( $this->db->affectedRows() <= 0 ) {
                $this->db->rollback();
                print "保存到备份执行失败\n";
                die;
            }

            // 更新主播最大守护信息
            $anchorInfoSql = <<<SQL
update anchor as a inner join (
select * from user_guard as t where not exists(select 1 from user_guard where t.anchor_user_id = anchor_user_id AND  t.total_coin < total_coin)
) as b 
on a.user_id = b.anchor_user_id 
set a.anchor_guard_id = b.user_id,a.anchor_guard_coin = b.total_coin,a.anchor_guard_level = b.current_level,a.anchor_guard_level_name = b.current_level_name;
SQL;
            $this->db->execute($anchorInfoSql);
            if ( $this->db->affectedRows() < 0 ) {
                $this->db->rollback();
                print "更新主播最大守护信息执行失败\n";
                die;
            }
            $this->db->commit();
        }


    }

    /**
     * @param $params
     * 添加奖励
     */
    public function checkRewardStatAction($params)
    {
        // 默认为昨天
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $end        = date('Y-m-d 00:00:00');
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;

        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }

        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $this->_checkRewardStat($item_stat_start, $item_stat_end);
        }
        die;
    }


    /**
     * 判断主播是否有额外奖励
     * @param $start
     * @param $item_stat_end
     */
    public function _checkRewardStat($start,$item_stat_end)
    {
//        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
//        $start      = '2019-09-12';
        $stat_start = $start;
        $stat_end   = $stat_start + 86399;

        $financeLogInsertSqlArr       = [];
        $updateUserDotSqlArr = [];

        $statSql = <<<SQL
select g.video_chat_reward_config,g.divid_time_precent,u.user_id,u.user_dot,sum(p.anchor_get_dot) as total_dot,
       sum(case when p.free_times_type = 'guard' then p.timepay_count - p.free_times else p.timepay_count end ) as stat_times 
from user_private_chat_log as p inner join `user` as u on p.chat_log_anchor_user_id = u.user_id inner join `group` as g on u.user_group_id = g.id
where p.create_time between :stat_start AND :stat_end group by u.user_id;
SQL;
        $res     = $this->db->query($statSql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        foreach ( $res->fetchAll() as $resultItem ) {
            if ( !$resultItem['video_chat_reward_config'] || !$resultItem['stat_times'] || !$resultItem['divid_time_precent'] ) {
                continue;
            }
            $itemConfig = json_decode($resultItem['video_chat_reward_config'], TRUE);
            if ( !$itemConfig ) {
                continue;
            }
            array_multisort(array_column($itemConfig, 'minute'), SORT_DESC, $itemConfig);
            $sourceCoin = intval($resultItem['total_dot'] * 100 / $resultItem['divid_time_precent']);
            foreach ( $itemConfig as $config ) {
                if ( $resultItem['stat_times'] >= $config['minute'] ) {
                    $consume               = round($sourceCoin * $config['reward_radio'] / 100, 2);
                    $financeLogTmp       = [
                        'user_id'             => $resultItem['user_id'],
                        'user_amount_type'    => "'dot'",
                        'user_current_amount' => $resultItem['user_dot'] + $consume,
                        'user_last_amount'    => $resultItem['user_dot'],
                        'consume_category_id' => 44,
                        'consume'             => $consume,
                        'remark'              => sprintf("'%s总分钟数%s，达到%s,额外奖励%s'", date('Y-m-d',$start),$resultItem['stat_times'], $config['minute'], $config['reward_radio']."%"),
                        'update_time'         => time(),
                        'create_time'         => time()
                    ];
                    $financeLogInsertSqlArr[] = '('.implode(',',$financeLogTmp) . ')';
                    $updateUserDotSqlArr[] = sprintf('update `user` set user_dot = user_dot + %s where user_id = %s', $consume, $resultItem['user_id']);
                    break;
                }
            }
        }

        $shouldExecCount = count($financeLogInsertSqlArr);
        if($financeLogInsertSqlArr){
            $this->db->begin();
            $sInsertSql = sprintf('INSERT INTO user_finance_log(`user_id`,`user_amount_type`,`user_current_amount`,`user_last_amount`,`consume_category_id`,`consume`,`remark`,`update_time`,`create_time`) VALUES %s;', implode(',',$financeLogInsertSqlArr));
            $this->db->execute($sInsertSql);
            $affectRow = $this->db->affectedRows();
            if($affectRow != $shouldExecCount){
                $this->db->rollback();
                echo sprintf("error %s 插入流水记录，应记录%s条，实际%s条\n",$start,$shouldExecCount,$affectRow);
                die;
            }
            $this->db->execute(implode(';',$updateUserDotSqlArr));
            $this->db->commit();
        }
        echo sprintf("success %s 执行%s条\n",$start,$shouldExecCount);

    }
}