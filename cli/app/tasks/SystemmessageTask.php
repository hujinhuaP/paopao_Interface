<?php

namespace app\tasks;

use Phalcon\Exception;

/**
 * AnchorTask 主播
 */
class SystemmessageTask extends MainTask
{
    /**
     * push 推送消息
     */
    public function pushAction($params)
    {
        $params['system_message_id'] = isset($params['system_message_id']) ? $params['system_message_id'] : $params[0];

//        $this->db->connect();
        $retry = 0;
        while (true ){
            $sql     = 'SELECT * FROM system_message WHERE system_message_id=:system_message_id and system_message_status="N"';
            $oResult = $this->db->query($sql, [
                'system_message_id' => $params['system_message_id'],
            ]);

            $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $aMessage = $oResult->fetch();

            if ( empty($aMessage) && $retry < 3 ) {
                $retry ++;
                print_r("【{$params['system_message_id']}】没找到状态为N 的数据 请求第{$retry}次\n");
                sleep(0.5);
            }else{
                break;
            }
        }

        if($aMessage['system_message_status'] != 'N'){
            print_r("【{$params['system_message_id']}】状态为 【{$aMessage['system_message_status']}】 \n");
            return;
        }

        // 修改系统消息发送状态
        $sql = sprintf('UPDATE system_message set system_message_status="S" WHERE system_message_id=%d', $params['system_message_id']);
        $this->db->execute($sql);

        $aData = json_decode($aMessage['system_message_content'], 1) ?: [];

        $sAlert   = $this->config->application->app_name;
        $sContent = isset($aData['data']['content']) ? $aData['data']['content'] : $aMessage['system_message_content'];
        $sType    = $aMessage['system_message_type'];
        unset($aData['content']);


        // 查出所有的 配置的 极光key
        $appSql     = "SELECT * FROM app_list WHERE jpush_app_key != '' and jpush_app_key != ''";
        $oAppResult = $this->db->query($appSql);

        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oAppList = $oAppResult->fetchAll();

        $pushArr       = [];
        $imPushType    = '';
        $imPushUser    = [];
        $imPushContent = $sContent;
        switch ( $aMessage['system_message_push_type'] ) {
            // 全推
            case 0:
                $imPushType = 'all';
                $aUserId    = [ '全部' ];
                // 循环遍历 app_list 配置的 极光key
                foreach ( $oAppList as $appItem ) {
                    $pushArr[] = [
                        'app_name'            => $appItem['app_name'],
                        'jpush_app_key'       => $appItem['jpush_app_key'],
                        'jpush_master_secret' => $appItem['jpush_master_secret'],
                        'push_type'           => 'all',
                        'extraData'           => ''
                    ];
                }
                break;
            // 全部主播
            case 2:
                $imPushType = 'anchor';
                $aUserId    = [ '全部主播' ];
                $pushArr[]  = [
                    'app_name'            => $this->config->application->app_name,
                    'jpush_app_key'       => $this->config->push->jpush->app_key,
                    'jpush_master_secret' => $this->config->push->jpush->master_secret,
                    'push_type'           => 'tags',
                    'extraData'           => 'anchor',
                ];
                $sql        = 'SELECT user_id FROM anchor';
                $oResult    = $this->db->query($sql, [
                    'system_message_id' => $params['system_message_id'],
                ]);
//                        $aUserId = [];
                $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $aAnchor = $oResult->fetchAll();
                $sql     = '';
                $sql2    = '';
                foreach ( $aAnchor as $v ) {
//                            $aUserId[] = $v['user_id'];
                    $sql != '' && $sql .= ',';
                    $sql2 != '' && $sql2 .= ',';
                    $sql  .= sprintf('(%d,%d,%d,%d)', $v['user_id'], $params['system_message_id'], time(), time());
                    $sql2 .= sprintf('("%s","%s","%s","%s","%s","%s","%s")', $v['user_id'], $params['system_message_id'], $sContent, 'system', 1, time(), time());
                }

                // 生成用户系统消息关系
                $sql = 'INSERT INTO user_system_message(user_id,system_message_id,user_system_message_create_time,user_system_message_update_time) VALUES' . $sql;
                $this->db->execute($sql);

                // 更新用户系统消息对话框
                if ( $aMessage['system_message_is_admin'] == 'N' ) {
                    $sql2 = 'INSERT INTO user_system_message_dialog(user_id,system_message_id,system_message_content,user_system_message_type,user_system_message_unread,user_system_message_dialog_create_time,user_system_message_dialog_update_time) VALUES' . $sql2 . ' ON DUPLICATE KEY UPDATE system_message_id=VALUES(system_message_id),system_message_content=VALUES(system_message_content),user_system_message_unread=user_system_message_unread+1,user_system_message_dialog_update_time=VALUES(user_system_message_dialog_update_time)';
                } else {
                    // 官方公告修改 字段
                    $sql2 = 'INSERT INTO user_system_message_dialog(user_id,user_notification_message_id,user_notification_message_content,user_system_message_type,user_notification_message_unread,user_system_message_dialog_create_time,user_notification_message_update_time) VALUES' . $sql2 . ' ON DUPLICATE KEY UPDATE user_notification_message_id=VALUES(user_notification_message_id),user_notification_message_content=VALUES(user_notification_message_content),user_notification_message_unread=user_notification_message_unread+1,user_notification_message_update_time=VALUES(user_notification_message_update_time)';
                }
                $this->db->execute($sql2);
                break;

            // 指定用户
            default:
                $imPushType = 'user';
                $aUserId    = $imPushUser = explode(',', $aMessage['user_id']);

                $userSql          = "SELECT user_app_flg,user_id FROM `user` where user_id in ({$aMessage['user_id']})";
                $selectUserResult = $this->db->query($userSql);
                $selectUserResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $oSelectUserList = $selectUserResult->fetchAll();

                // 获取app列表 转化为数据
                $appFlgArr = [];
                foreach ( $oAppList as $appItem ) {
                    $appFlgArr[$appItem['app_flg']] = $appItem;
                }

                // 将用户根据app 分组
                $shouldPushArr = [];
                foreach ( $oSelectUserList as $oSelectUserItem ) {
                    $selectUserItemFlg                   = $oSelectUserItem['user_app_flg'];
                    $shouldPushArr[$selectUserItemFlg][] = $oSelectUserItem['user_id'];
                }

                // 将分组好的用户 分别设置到各自推送的数据中
                foreach ( $shouldPushArr as $appFlgKeyItem => $appFlgAliasArr ) {
                    $pushArr[] = [
                        'app_name'            => $appFlgArr[$appFlgKeyItem]['app_name'],
                        'jpush_app_key'       => $appFlgArr[$appFlgKeyItem]['jpush_app_key'],
                        'jpush_master_secret' => $appFlgArr[$appFlgKeyItem]['jpush_master_secret'],
                        'push_type'           => 'alias',
                        'extraData'           => $appFlgAliasArr,
                    ];
                }

                $sql  = '';
                $sql2 = '';
                foreach ( $aUserId as $v ) {
                    $sql != '' && $sql .= ',';
                    $sql2 != '' && $sql2 .= ',';
                    $sql  .= sprintf('(%d,%d,%d,%d)', $v, $params['system_message_id'], time(), time());
                    $sql2 .= sprintf('("%s","%s","%s","%s","%s","%s","%s")', $v, $params['system_message_id'], $sContent, 'system', 1, time(), time());
                }
                // 生成用户系统消息关系
                $sql = 'INSERT INTO user_system_message(user_id,system_message_id,user_system_message_create_time,user_system_message_update_time) VALUES' . $sql;
                $this->db->execute($sql);

                // 更新用户系统消息对话框
                if ( $aMessage['system_message_is_admin'] == 'N' ) {
                    $sql2 = 'INSERT INTO user_system_message_dialog(user_id,system_message_id,system_message_content,user_system_message_type,user_system_message_unread,user_system_message_dialog_create_time,user_system_message_dialog_update_time) VALUES' . $sql2 . ' ON DUPLICATE KEY UPDATE system_message_id=VALUES(system_message_id),system_message_content=VALUES(system_message_content),user_system_message_unread=user_system_message_unread+1,user_system_message_dialog_update_time=VALUES(user_system_message_dialog_update_time)';
                } else {
                    $sql2 = 'INSERT INTO user_system_message_dialog(user_id,user_notification_message_id,user_notification_message_content,user_system_message_type,user_notification_message_unread,user_system_message_dialog_create_time,user_notification_message_update_time) VALUES' . $sql2 . ' ON DUPLICATE KEY UPDATE user_notification_message_id=VALUES(user_notification_message_id),user_notification_message_content=VALUES(user_notification_message_content),user_notification_message_unread=user_notification_message_unread+1,user_notification_message_update_time=VALUES(user_notification_message_update_time)';
                }
                $this->db->execute($sql2);

                break;
        }

        echo sprintf("%s %s Message push user %s.\n", date('r'), $params['system_message_id'], implode(',', $aUserId));


        // 生成用户聊天记录
        echo sprintf("推送地址数%d\n", count($pushArr));
        foreach ( $pushArr as $pushItem ) {
            $this->_pushItem($pushItem['jpush_app_key'], $pushItem['jpush_master_secret'], $pushItem['app_name'], $sContent, $sType, $aData, $pushItem['push_type'], $pushItem['extraData']);
        }
        if($aMessage['system_message_is_admin'] == 'Y'){
            $flg = $this->httpRequest(sprintf('%sim/notification?%s', $this->config->application->app_api_url, http_build_query([
                'type'        => $imPushType,
                'user_ids'    => $imPushUser,
                'content'     => $imPushContent,
                'debug'       => 1,
                'cli_api_key' => $this->config->application->cli_api_key,
            ])));
            print_r($flg);
        }

        // 修改系统消息发送状态
        $sql = sprintf('UPDATE system_message set system_message_status="Y" WHERE system_message_id=%d', $params['system_message_id']);
        $this->db->execute($sql);
        echo sprintf("%s %s Message push ok.\n", date('r'), $params['system_message_id']);
    }

    /**
     * @param $appKey
     * @param $masterSecret
     * @param $sAlert
     * @param $sContent
     * @param $sType
     * @param $aData
     * @param $pushType   all : 所有  tags ： 标签   alias ：别名
     * @param $extraData
     */
    private function _pushItem($appKey, $masterSecret, $sAlert, $sContent, $sType, $aData, $pushType = 'all', $extraData = '')
    {

        $client = new \JPush\Client($appKey, $masterSecret);

        $push = $client->push();

        $push->setNotificationAlert($sAlert)
            ->iosNotification($sContent, [
                'sound'    => 'sound.caf',
                // 'badge' => '+1',
                // 'content-available' => true,
                // 'mutable-content' => true,
                'category' => 'jiguang',
                'extras'   => [
                    'type' => $sType,
                    'data' => $aData,
                    'jiguang'
                ],
            ])
            ->androidNotification($sContent, [
                'title'  => $sAlert,
                // 'builder_id' => 2,
                'extras' => [
                    'type' => $sType,
                    'data' => $aData,
                    'jiguang'
                ],
            ])
            ->message($sContent, [
                'title'  => $sAlert,
                // 'content_type' => 'text',
                'extras' => [
                    'type' => $sType,
                    'data' => $aData,
                    'jiguang'
                ],
            ])
            ->options([
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

                'apns_production' => APP_ENV == 'dev' ? FALSE : TRUE,

                // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
                // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
                // 这里设置为 1 仅作为示例

                // 'big_push_duration' => 1
            ]);

        switch ( $pushType ) {
            case 'tags':
                if ( !$extraData ) {
                    printf("没有tags参数\n");
                }
                $push->setPlatform([
                    'ios',
                    'android'
                ])->addTag($extraData);
                break;
            case 'alias':
                if ( !$extraData ) {
                    printf("没有alias参数\n");
                }
                $push->setPlatform([
                    'ios',
                    'android'
                ])->addAlias($extraData);
                break;
            default:
//                $push->setPlatform('all')->addAllAudience();
                $push->setPlatform('all')->addTag(APP_ENV);
        }
        $response = $push->send();

        print_r($response['body']);
        echo "\n";
        return $response;
    }

    /**
     * 定时推送
     * 每小时执行一次 判断
     */
    public function crontabAction()
    {
        $currentHour = date('G');
        $taskSql = "SELECT * FROM crontab_push where crontab_push_on_flg = 'Y' AND crontab_push_hour = {$currentHour}";
        $oResult = $this->db->query($taskSql);
        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $aCrontabResult = $oResult->fetchAll();


        // 查出所有的 配置的 极光key
        $appSql     = "SELECT * FROM app_list WHERE jpush_app_key != '' and jpush_app_key != ''";
        $oAppResult = $this->db->query($appSql);

        $oResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oAppList = $oAppResult->fetchAll();

        foreach($aCrontabResult as $crontabItem){
            $this->_crontabItem($crontabItem,$oAppList);
        }


    }

    private function _crontabItem($crontabItem,$oAppList)
    {
        $pushArr       = [];
        $imPushUser    = [];
        $imPushType    = $crontabItem['crontab_push_user_type'];
        $imPushContent = $crontabItem['crontab_push_content'];
        switch ($crontabItem['crontab_push_user_type']){
            case 'all':
                // 循环遍历 app_list 配置的 极光key
                foreach ( $oAppList as $appItem ) {
                    $pushArr[] = [
                        'app_name'            => $appItem['app_name'],
                        'jpush_app_key'       => $appItem['jpush_app_key'],
                        'jpush_master_secret' => $appItem['jpush_master_secret'],
                        'push_type'           => 'all',
                        'extraData'           => ''
                    ];
                }
                break;
            case 'anchor':
                $pushArr[]  = [
                    'app_name'            => $this->config->application->app_name,
                    'jpush_app_key'       => $this->config->push->jpush->app_key,
                    'jpush_master_secret' => $this->config->push->jpush->master_secret,
                    'push_type'           => 'tags',
                    'extraData'           => 'anchor',
                ];
                break;
            case 'user':
                $userSql          = "SELECT user_app_flg,user_id FROM `user` where user_id in ({$crontabItem['crontab_push_user_id']})";
                $selectUserResult = $this->db->query($userSql);
                $selectUserResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                $oSelectUserList = $selectUserResult->fetchAll();

                // 获取app列表 转化为数据
                $appFlgArr = [];
                foreach ( $oAppList as $appItem ) {
                    $appFlgArr[$appItem['app_flg']] = $appItem;
                }

                // 将用户根据app 分组
                $shouldPushArr = [];
                foreach ( $oSelectUserList as $oSelectUserItem ) {
                    $selectUserItemFlg                   = $oSelectUserItem['user_app_flg'];
                    $shouldPushArr[$selectUserItemFlg][] = $oSelectUserItem['user_id'];
                    $imPushUser[] = $oSelectUserItem['user_id'];
                }

                // 将分组好的用户 分别设置到各自推送的数据中
                foreach ( $shouldPushArr as $appFlgKeyItem => $appFlgAliasArr ) {
                    $pushArr[] = [
                        'app_name'            => $appFlgArr[$appFlgKeyItem]['app_name'],
                        'jpush_app_key'       => $appFlgArr[$appFlgKeyItem]['jpush_app_key'],
                        'jpush_master_secret' => $appFlgArr[$appFlgKeyItem]['jpush_master_secret'],
                        'push_type'           => 'alias',
                        'extraData'           => $appFlgAliasArr,
                    ];
                }
                break;
        }
        $aData = [
            'type' => 'general',
            'data' => [
                'content' => $imPushContent,
                'url'    =>''
            ]
        ];
        foreach ( $pushArr as $pushItem ) {
            $this->_pushItem($pushItem['jpush_app_key'], $pushItem['jpush_master_secret'], $pushItem['app_name'], $imPushContent, 'system', $aData, $pushItem['push_type'], $pushItem['extraData']);
        }

        $flg = $this->httpRequest(sprintf('%sim/notification?%s', $this->config->application->app_api_url, http_build_query([
            'type'        => $imPushType,
            'user_ids'    => $imPushUser,
            'content'     => $imPushContent,
            'debug'       => 1,
            'cli_api_key' => $this->config->application->cli_api_key,
        ])));
        print_r($flg);

    }

}