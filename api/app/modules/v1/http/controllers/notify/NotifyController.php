<?php

namespace app\http\controllers\notify;

use app\helper\AlipayServer;
use app\helper\OpensslEncryptHelper;
use app\helper\WechatPay;
use app\http\controllers\ControllerBase;
use app\models\Agent;
use app\models\AnchorDispatch;
use app\models\AppList;
use app\models\AgentWaterLog;
use app\models\Anchor;
use app\models\AnchorSignStat;
use app\models\DailyDataStat;
use app\models\DispatchChat;
use app\models\Kv;
use app\models\User;
use app\models\UserAccount;
use app\models\UserAgentLog;
use app\models\UserCashLog;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;

use app\models\UserIdentifyLog;
use app\models\UserMatchLog;
use app\models\UserOnlineLog;
use app\models\UserPrivateChatLog;
use app\models\UserRechargeActionLog;
use app\models\UserRechargeCombo;
use app\models\UserRechargeOrder;
use app\models\UserVipCombo;
use app\models\UserVipOrder;
use app\models\VipLevel;
use app\services\AnchorOfflineModifyService;
use app\services\AnchorStatService;
use app\services\ChatStreamService;
use app\services\ChatTimCheckService;
use app\services\CustomerService;
use app\services\DeviceRetainService;
use app\services\UserLoginService;
use app\services\UserOnlineService;
use app\services\UserRetainService;
use app\services\UserSayhiService;
use app\services\UserTodayLoginService;
use Exception;

class NotifyController extends ControllerBase
{

    use \app\services\SystemMessageService;
    use \app\services\UserService;

    /**
     * 腾讯云通信回调通知
     */
    public function indexAction()
    {
        $return = [
            'ActionStatus' => 'OK',
            'ErrorCode'    => 0,
            'ErrorInfo'    => ""
        ];
        $data   = json_decode(file_get_contents("php://input"), TRUE);
        if ( $_GET['debug'] == 1 ) {
            $data = json_decode('{"CallbackCommand":"State.StateChange","Info":{"To_Account":"66666222","Action":"Logout","Reason":"LinkClose"}}', TRUE);
        }
        if ( $_GET['debug'] == 2 ) {
            $data = json_decode('{"CallbackCommand":"State.StateChange","Info":{"To_Account":"173","Action":"Login","Reason":"Register"}}', TRUE);
        }
//        $this->log->info(file_get_contents("php://input"));

        $user_id = $data['Info']['To_Account'];
        $action  = $data['Info']['Action'];
        $oUser   = User::findFirst($user_id);
        if ( !$oUser ) {
            return FALSE;
        }
        // 开始#设备留存
        if ( $oUser->user_is_anchor == 'N' ) {
            $registerDate     = date('Ymd', $oUser->user_create_time);
            $secondRetainDate = date('Ymd', $oUser->user_create_time + 86400);
            $threeRetainDate  = date('Ymd', $oUser->user_create_time + 86400 * 2);
            $sevenRetainDate  = date('Ymd', $oUser->user_create_time + 86400 * 7);
            $thirtyRetainDate = date('Ymd', $oUser->user_create_time + 86400 * 29);
            $retainDateArr    = [
                $secondRetainDate => [
                    'timesFlg' => 2,
                    'dateFlg'  => $registerDate,
                ],
                $threeRetainDate  => [
                    'timesFlg' => 3,
                    'dateFlg'  => $registerDate,
                ],
                $sevenRetainDate  => [
                    'timesFlg' => 7,
                    'dateFlg'  => $registerDate,
                ],
                $thirtyRetainDate => [
                    'timesFlg' => 30,
                    'dateFlg'  => $registerDate,
                ]
            ];
            $currentDate      = date('Ymd');
            $retainInfo       = $retainDateArr[ $currentDate ] ?? [];
            if ( $retainInfo ) {
                // 是需要统计留存的天数 则添加
                $oDeviceRetainService = new DeviceRetainService($retainInfo['timesFlg'], $retainInfo['dateFlg']);
                $oUserAccount         = UserAccount::findFirst([
                    "user_id = :user_id:",
                    'bind' => [ 'user_id' => $user_id ]
                ]);
                $userDeviceId         = $oUserAccount->user_device_id;
                $oDeviceRetainService->save($userDeviceId);
            }
        }
        // 结束#设备留存

        if ( in_array($action, [
            'Disconnect',
            'Logout',
            'TimeOut'
        ]) ) {
            // 下线 将用户状态改为离线  如果是主播则将主播的在线状态改为离线

            // 下线客服
            if ( $oUser->user_is_superadmin == 'C' ) {
                $oCustomerService = new CustomerService();
                $oCustomerService->save($user_id, User::USER_ONLINE_STATUS_OFFLINE);
            }

            $checkUserIds = $this->getCheckUserIds();
            if ( in_array($user_id, $checkUserIds) ) {
                //修改审核状态 关闭  判断该审核账号登录的是哪个app
                $oAppInfo = $this->getAppInfo('qq', $oUser->user_app_flg);
                if ( $oAppInfo['on_publish'] == 'N' && $oAppInfo['check_login_change_status'] == 'Y' ) {
                    // 不在审核中 且 登录切换的开关 是开启的  则将审核状态开启
                    $oAppList             = AppList::findFirst($oAppInfo['id']);
                    $oAppList->on_publish = 'Y';
                    $oAppList->save();
                    $this->log->info("审核账号【{$user_id}】 登录，改变审核状态");
                }
            }
            if ( $oUser ) {
                $oUser->user_online_status = User::USER_ONLINE_STATUS_OFFLINE;
                $oUser->user_logout_time   = time();
                $oUser->save();
                $oUserOnlineService = new UserOnlineService('user');
                $oUserOnlineService->delete_item($user_id);
                if ( $oUser->user_is_anchor == 'Y' ) {
                    $oUserOnlineService = new UserOnlineService('anchor');
                    $oUserOnlineService->delete_item($user_id);

                    // 最近下线时间
                    $oAnchorStatService = new AnchorStatService($oUser->user_id);
                    $oAnchorStatService->save(AnchorStatService::TIME_LOGOUT, time(), FALSE);
                    if ( $oUser->user_login_time ) {
                        // 取上次上线时间 则可以判断本次登录多长时间
                        // 主播每日统计
                        $thisLoginStartTime = $oUser->user_login_time;
                        $todayTime          = strtotime(date('Y-m-d', time()));
                        if ( $oUser->user_login_time < $todayTime ) {
                            // 如果本次登录的时间是前一天 则只统计当天的数据
                            $thisLoginStartTime = $todayTime;
                        }
                        $oAnchorStatService->save(AnchorStatService::ONLINE_DURATION, time() - $thisLoginStartTime, TRUE);
                    }

                    $oAnchor = Anchor::findFirst([
                        "user_id = :user_id:",
                        'bind' => [ 'user_id' => $user_id ]
                    ]);
                    if ( $oAnchor && in_array($oAnchor->anchor_chat_status, [
                            2,
                            3
                        ]) ) {

                        if ( $oAnchor->anchor_chat_status == 2 ) {
                            // 正在通话中
                            $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                                'chat_log_anchor_user_id = :chat_log_anchor_user_id:',
                                'bind'  =>
                                    [ 'chat_log_anchor_user_id' => $user_id ],
                                'order' => 'id desc'
                            ]);
                            if ( $oUserPrivateChatLog ) {
                                if ( $oUserPrivateChatLog->status == 0 ) {
                                    file_get_contents(sprintf('%s/v1/live/anchor/cancelPrivateChat?%s', $this->config->application->api_url, http_build_query([
                                        'user_id'  => $user_id,
                                        'chat_log' => $oUserPrivateChatLog->id,
                                        'do_cli'   => 'hzjkb24mGJ8RWOL7GLp4U7T',
                                        'type'     => '1',
                                    ])));
                                } else if ( $oUserPrivateChatLog->status == 4 ) {

                                    // 主播TIM断线 延迟20秒  再挂断  （如果20秒内有心跳 则删除改记录）  用户id-聊天记录id-是否为主播
                                    $oChatTimCheckService = new ChatTimCheckService();
                                    $saveData             = sprintf('%s-%s-%s', $user_id, $oUserPrivateChatLog->id, 'Y');
                                    $oChatTimCheckService->save($saveData);
                                    $this->log->info($user_id . '主播TIM断线');
//                                    file_get_contents(sprintf('%s/v1/live/anchor/hangupChat?%s', $this->config->application->api_url, http_build_query([
//                                        'user_id'  => $user_id,
//                                        'chat_log' => $oUserPrivateChatLog->id,
//                                        'do_cli'   => 'hzjkb24mGJ8RWOL7GLp4U7T',
//                                        'hang_up_type' => 'auto',
//                                        'detail'    => '主播TIM断线'
//                                    ])));
                                }
                            }
                        } else {
                            $oAnchor->anchor_chat_status = 1;
                            $oAnchor->save();
                        }
                    }

                    // 如果是签约主播 则添加下线时长记录
                    if ( $oAnchor->anchor_is_sign == 'Y' ) {
                        $this->offonlineSignAnchor($oUser, $oAnchor, $user_id);
                    }

                    if ( Kv::get(Kv::ANCHOR_OFFLINE_MODIFY) == 'Y' ) {
                        // 添加进假在线主播列表
                        $oAnchorOfflineModifyService = new AnchorOfflineModifyService(AnchorOfflineModifyService::TYPE_ONLINE);
                        $oAnchorOfflineModifyService->save($oAnchor->user_id);
                    }

                }
                // 普通用户
                $oUserPrivateChatLog = UserPrivateChatLog::findFirst([
                    'chat_log_user_id = :chat_log_user_id:',
                    'bind'  =>
                        [ 'chat_log_user_id' => $user_id ],
                    'order' => 'id desc'
                ]);
                // 存在 证明在聊天
                if ( $oUserPrivateChatLog ) {
                    if ( $oUserPrivateChatLog->status == 0 ) {
                        file_get_contents(sprintf('%s/v1/live/anchor/cancelPrivateChat?%s', $this->config->application->api_url, http_build_query([
                            'user_id'  => $user_id,
                            'chat_log' => $oUserPrivateChatLog->id,
                            'do_cli'   => 'hzjkb24mGJ8RWOL7GLp4U7T',
                            'type'     => '1',
                        ])));
                    } else if ( $oUserPrivateChatLog->status == 4 ) {
                        // 用户TIM断线 延迟20秒  再挂断  （如果20秒内有心跳 则删除改记录）  用户id-聊天记录id-是否为主播
                        $oChatTimCheckService = new ChatTimCheckService();
                        $saveData             = sprintf('%s-%s-%s', $user_id, $oUserPrivateChatLog->id, 'N');
                        $oChatTimCheckService->save($saveData);
                        $this->log->info($user_id . '用户TIM断线');
//                        file_get_contents(sprintf('%s/v1/live/anchor/hangupChat?%s', $this->config->application->api_url, http_build_query([
//                            'user_id'  => $user_id,
//                            'chat_log' => $oUserPrivateChatLog->id,
//                            'do_cli'   => 'hzjkb24mGJ8RWOL7GLp4U7T',
//                            'hang_up_type' => 'auto',
//                            'detail'    => '用户TIM断线'
//                        ])));
                    }
                }

            }
        } else {
            // 上线
            //客服上线
            if ( $oUser->user_is_superadmin == 'C' ) {
                $oCustomerService = new CustomerService();
                $oCustomerService->save($user_id, User::USER_ONLINE_STATUS_ONLINE);
            }

            if ( $oUser ) {
                $oUser->user_online_status = User::USER_ONLINE_STATUS_ONLINE;
                $oUser->user_login_time    = time();
                $oUser->save();

                // 判断上线等级是否达到要求  达到滚屏
                $scroll_level_online = Kv::get(Kv::SCROLL_LEVEL_ONLINE);
                if ( $scroll_level_online > 0 && $oUser->user_level >= $scroll_level_online ) {
                    // 高级用户上线滚屏
                    $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                    $this->timServer->sendScrollMsg([
                        'type' => 'high_level_user_online',
                        'info' => [
                            'user_nickname' => $oUser->user_nickname,
                            'user_avatar'   => $oUser->user_avatar,
                            'user_level'    => $oUser->user_level,
                            'title'         => '等级特权用户',
                            'content'       => sprintf('%s 已上线。', $oUser->user_nickname),
                        ]
                    ]);
                }

                //在线人数添加
                $oUserOnlineService = new UserOnlineService('user');
                $oUserOnlineService->save($user_id);
                if ( $oUser->user_is_anchor == 'Y' ) {
                    // 最近上线时间
                    $oAnchorStatService = new AnchorStatService($oUser->user_id);
                    $oAnchorStatService->save(AnchorStatService::TIME_LOGIN, time(), FALSE);

                    $oUserOnlineService = new UserOnlineService('anchor');
                    $oUserOnlineService->save($user_id);
                    $oAnchor = Anchor::findFirst([
                        "user_id = :user_id:",
                        'bind' => [ 'user_id' => $user_id ]
                    ]);
                    if ( $oAnchor && $oAnchor->anchor_chat_status == 1 ) {
                        $oAnchor->anchor_chat_status = 3;
                        $oAnchor->save();
                    }

                    // 如果是签约主播 则添加上线时长记录
                    if ( $oAnchor->anchor_is_sign == 'Y' ) {
                        $this->onlineSignAnchor($oAnchor, $user_id);
                    }

                    // 删除假在线 假聊天 假离线主播列表
                    $oAnchorOfflineModifyService = new AnchorOfflineModifyService(AnchorOfflineModifyService::TYPE_ONLINE);
                    $oAnchorOfflineModifyService->delete_all($oAnchor->user_id);
                } else {
                    // 显示最近充值的8次飘屏 将8次值 存入有序集合中 定时推送
                    $rechargeInfoSql = "select o.user_recharge_order_id,u.user_id,u.user_nickname,u.user_avatar,o.user_recharge_order_coin from `user_recharge_order` as o 
inner join `user` as u on o.user_id = u.user_id where user_recharge_order_status = 'Y' AND user_recharge_combo_fee > 49 AND u.user_id != 118017 order by user_recharge_order_id desc limit 8";
                    $res             = $this->db->query($rechargeInfoSql);
                    $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
                    $rechargeInfoData = $res->fetchAll();
                    $sendTime         = time() + 5;
                    foreach ( $rechargeInfoData as $rechargeInfItem ) {
                        $itemIkey = sprintf('%s_%s', $oUser->user_id, $rechargeInfItem['user_recharge_order_id']);
                        $this->redis->zAdd('scroll_msg', $sendTime, $itemIkey);
                        $thisCacheKey = 'scroll_recharge:' . $itemIkey;
                        $this->redis->hMSet($thisCacheKey, [
                            'user_id'       => $rechargeInfItem['user_id'],
                            'user_nickname' => $rechargeInfItem['user_nickname'],
                            'user_avatar'   => $rechargeInfItem['user_avatar'],
                            'coin'          => $rechargeInfItem['user_recharge_order_coin'],
                            'coin_name'     => Kv::get(Kv::KEY_COIN_NAME)
                        ]);
                        $this->redis->expire($thisCacheKey, 600);
                        $sendTime += 15;
                    }
                }
            }
        }
        exit($return);
    }


    /**
     * @param $oAnchor
     * @param $user_id
     * 签约主播上线处理
     */
    private function onlineSignAnchor( $oAnchor, $user_id )
    {
        if ( $oAnchor->anchor_is_sign == 'Y' ) {
            $oUserOnlineLog = UserOnlineLog::findFirst([
                'user_id = :user_id: AND offline_time = 0',
                'bind' => [
                    'user_id' => $user_id
                ],
            ]);
            if ( $oUserOnlineLog ) {
                $oUserOnlineLog->offline_time = $oUserOnlineLog->online_time;
                $oUserOnlineLog->save();
            }
            $oUserOnlineLog              = new UserOnlineLog();
            $oUserOnlineLog->user_id     = $user_id;
            $oUserOnlineLog->online_time = time();
            $oUserOnlineLog->save();
        }
    }

    /**
     * @param $oUser
     * @param $oAnchor
     * @param $user_id
     * 签约主播下线处理
     */
    private function offonlineSignAnchor( $oUser, $oAnchor, $user_id )
    {
        if ( $oAnchor->anchor_is_sign == 'Y' ) {
            $oUserOnlineLog = UserOnlineLog::findFirst([
                'user_id = :user_id:',
                'bind'  => [
                    'user_id' => $user_id
                ],
                'order' => 'id desc',
            ]);
            // 每天统计开始时间为12点
            $stat_time = strtotime(date('Y-m-d 12:00:00'));
            if ( time() < $stat_time ) {
                $stat_time = strtotime(date('Y-m-d 12:00:00')) - 3600 * 24;
            }
            $online_time = $oUserOnlineLog->online_time;
            if ( $online_time < $stat_time ) {
                // 查到了的是跨区域的  等待10秒 查询新的数据
                sleep(10);
                $oUserOnlineLog = UserOnlineLog::findFirst([
                    'user_id = :user_id: AND offline_time = 0',
                    'bind'  => [
                        'user_id' => $user_id
                    ],
                    'order' => 'id desc'
                ]);
            }
            $offonline_time = time();

            $online_time = $oUserOnlineLog->online_time;

            $oUserOnlineLog->offline_time = $offonline_time;
            $oUserOnlineLog->duration     = $offonline_time - $online_time;
            $oUserOnlineLog->save();
//            $online_time =  strtotime('2018-07-27 09:05:00');
//            $offonline_time = strtotime('2018-07-27 11:08:00');

            // 处理统计数据
            $oAnchorSignStat = AnchorSignStat::findFirst([
                'user_id = :user_id: AND stat_date = :stat_date:',
                'bind' => [
                    'user_id'   => $user_id,
                    'stat_date' => strtotime(date('Y-m-d', $stat_time))
                ]
            ]);
            if ( !$oAnchorSignStat ) {
                //记录不存在
                $oAnchorSignStat                              = new AnchorSignStat();
                $oAnchorSignStat->user_id                     = $user_id;
                $oAnchorSignStat->stat_date                   = strtotime(date('Y-m-d', $stat_time));
                $oAnchorSignStat->group_id                    = $oUser->user_group_id;
                $oAnchorSignStat->anchor_sign_live_start_time = $oAnchor->anchor_sign_live_start_time;
                $oAnchorSignStat->anchor_sign_live_end_time   = $oAnchor->anchor_sign_live_end_time;
                $oAnchorSignStat->online_duration             = 0;
                $oAnchorSignStat->affect_online_duration      = 0;
                $oAnchorSignStat->save();
                var_dump($oAnchorSignStat->getMessages());
            }

            // 签约时间的开始时间是跨天前
            $anchor_sign_live_start_timestamp = $oAnchorSignStat->stat_date + $oAnchorSignStat->anchor_sign_live_start_time * 3600;
            if ( $oAnchorSignStat->anchor_sign_live_start_time < 12 ) {
                // 签约时间的开始时间是跨天后  即明天0点 加上小时数
                $anchor_sign_live_start_timestamp = $oAnchorSignStat->stat_date + 3600 * 24 + $oAnchorSignStat->anchor_sign_live_start_time * 3600;
            }

            // 签约时间的结束时间是跨天前
            $anchor_sign_live_end_timestamp = $oAnchorSignStat->stat_date + $oAnchorSignStat->anchor_sign_live_end_time * 3600;
            if ( $oAnchorSignStat->anchor_sign_live_end_time <= 12 ) {
                // 签约时间的结束时间是跨天后  即明天0点 加上小时数
                $anchor_sign_live_end_timestamp = $oAnchorSignStat->stat_date + 3600 * 24 + $oAnchorSignStat->anchor_sign_live_end_time * 3600;
            }

            $online_duration = $offonline_time - $online_time;
//                        if($offonline_time < $anchor_sign_live_start_timestamp){
//                            // 表示 上线下时间 还没到签约时间，只需要添加 当天总在线时长
//                        }
            /*
             * 时间分成三段
             *  1.第一个段 签约开始时间到下线时间 有效时间（即签约时间内的时间）
             *      a. 如果小于0 则有效时间 为0
             *      b. 大于0 则继续判断
             *          2.第二个时间段 需要减去的时间段 上线时间到签约开始时间
             *              a. 如果小于0 则表示 上线时间在签约开始时间之前 那么不需要减掉 则设为0
             *              b. 如果大于0 则表示 签约开始时间后到上线，有一段时间是没有在线的，那么需要由第一个时间段 减去此时间段
             *                  3.第三个时间段 签约结束时间到下线时间 此段时间不能计算在有效时间内， 需要减去
             *                      a. 如果大于0 则需要减去
             *                      b. 如果小于0 则表示 结束时间在签约时间内 不需要减去 设为0
             *                          4.第四个时间段 签约结束时间到下线时间 多减去了 开始时间到签约时间的时段 需要加上
             *                                               a. 如果大于0 则需要加上去
             *                                               b. 如果小于0 则表示 开始时间在签约时间内 不需要减去 设为0
             */
            $firstInterval  = $offonline_time - $anchor_sign_live_start_timestamp;
            $secondInterval = $online_time - $anchor_sign_live_start_timestamp;
            $thirdInterval  = $offonline_time - $anchor_sign_live_end_timestamp;
            $fourthInterval = $online_time - $anchor_sign_live_end_timestamp;

            $firstInterval  = $firstInterval > 0 ? $firstInterval : 0;
            $secondInterval = $secondInterval > 0 ? $secondInterval : 0;
            $thirdInterval  = $thirdInterval > 0 ? $thirdInterval : 0;
            $fourthInterval = $fourthInterval > 0 ? $fourthInterval : 0;

            $affect_duration = $firstInterval - $secondInterval - $thirdInterval + $fourthInterval;

//            echo '<pre>';
//            var_dump([
//                'start'          => date('Y-m-d H:i:s', $anchor_sign_live_start_timestamp),
//                'end'            => date('Y-m-d H:i:s', $anchor_sign_live_end_timestamp),
//                'online_time'    => date('Y-m-d H:i:s', $online_time),
//                'offline_time'   => date('Y-m-d H:i:s', $offonline_time),
//                'firstInterval'  => $firstInterval,
//                'secondInterval' => $secondInterval,
//                'thirdInterval'  => $thirdInterval,
//                'fourthInterval' => $fourthInterval,
//                'affect'         => ($firstInterval - $secondInterval - $thirdInterval + $fourthInterval)/ 60 ,
//                'affect_total'   => $affect_duration,
//            ]);
//            die;

            $oAnchorSignStat->online_duration        += $online_duration;
            $oAnchorSignStat->affect_online_duration += $affect_duration;
            $oAnchorSignStat->save();
        }
    }

    public function alipayNotifyAction()
    {
        $ali           = new AlipayServer();
        $tmpArr        = $this->request->getPost();
        $verify_result = $ali->confirmParams($tmpArr);
        if ( $verify_result ) {
            $out_trade_no = $tmpArr['out_trade_no'];
            $trade_no     = $tmpArr['trade_no'];
            $resOrder     = FALSE;
            if ( isset($tmpArr['trade_status']) && ($tmpArr['trade_status'] == 'TRADE_SUCCESS' || $tmpArr['trade_status'] == 'TRADE_FINISHED') ) {
                if ( $tmpArr['passback_params'] == 'BUY_VIP' ) {
                    $resOrder = $this->_addTime($out_trade_no, $trade_no);
                } else {
                    $resOrder = $this->_commitPayOrder($out_trade_no, $trade_no);
                }
            }
            if ( $resOrder ) {
                echo "success";     //请不要修改或删除
            } else {
                echo "fail";     //请不要修改或删除
            }
        } else {
            echo "fail";    //请不要修改或删除
        }
    }

    //微信回调
    public function wxNotifyAction()
    {
        $postStr = file_get_contents("php://input");

        $result    = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $attach    = $result->attach;
        $attackArr = explode('|', $attach);
        $attachFlg = $attackArr[0];
        $class     = new WechatPay();
        if ( count($attackArr) == 2 ) {
            $itemFlg       = $attackArr[1];
            $totalWxConfig = $this->config->wxpay;
            $itemConfig    = $totalWxConfig[ $itemFlg ] ?? [];
            if ( $itemConfig ) {
                $class->setPublicAppId($itemConfig['public_app_id']);
                $class->setPublicMerchantId($itemConfig['public_merchant_id']);
                $class->setPublicApiKey($itemConfig['public_api_key']);
            }
        }
        if ( $class->orderQuery($result->transaction_id, $result->trade_type) ) {
            $out_trade_no   = $result->out_trade_no;
            $transaction_id = $result->transaction_id;
            if ( $attachFlg == 'RECHARGE' ) {
                $res = $this->_commitPayOrder($out_trade_no, $transaction_id);
            } else {
                $res = $this->_addTime($out_trade_no, $transaction_id);
            }
            if ( $res ) {
                echo 'success';
                die;
            }
            echo 'fail';
            die;
        } else {
            echo 'fail';
            die;
        }
    }

    public function testAction()
    {
//        die;
//        $flg = OpensslEncryptHelper::decryptWithOpenssl("gxi7Qau9J55TOtsducuaHbQn/VIBTSPoGgvz0d3v0kcrxX03KN3psaALn90pBjpUTtnRe5VPfWudX7Lyku4oOQ==", OpensslEncryptHelper::APP_KEY, OpensslEncryptHelper::APP_IV);
//        var_dump($flg);
//        die;
        $this->_commitPayOrder('15579767916760855933896', 'test');
        die;
        $this->_addTime('15407126716679731986151', '4200000203201810289207144505');
    }


    /**
     * applePayAction 苹果内购
     *
     * @param int $nUserId
     */
    public function applePayAction( $nUserId = 0 )
    {
        //用户发来的参数
        $receipt_data   = $this->getParams("data");
        $sOrderNumber   = $this->getParams("order_number");
        $transaction_id = $this->getParams("transaction_id");
        $payType        = $this->getParams("pay_type", 'string', 'recharge');
        //验证参数
        if ( strlen($receipt_data) < 20 ) {
            $this->error(10054);
        }
        //请求验证
        $html = $this->acurl($receipt_data);
        $data = json_decode($html, 1);
        //如果是沙盒数据 则验证沙盒模式
        if ( $data['status'] == '21007' && $this->config->application->apple_sandbox ) {
            //请求验证
            $html            = $this->acurl($receipt_data, $sandbox = 1);
            $data            = json_decode($html, 1);
            $data['sandbox'] = '1';
        }
        if ( $data['status'] == 0 ) {
            // 增加用户的币

            foreach ( $data['receipt']['in_app'] as $item ) {
                if ( $item['transaction_id'] == $transaction_id ) {
                    $product_id = $item['product_id'];
                    break;
                }
            }
        } else {
            $this->error(10054);
        }
        if ( empty($product_id) ) {
            $this->error(10002);
        }
        if ( $payType == 'recharge' ) {
            $flg = $this->_commitPayOrder($sOrderNumber, $transaction_id);
        } else if ( $payType == 'vip' ) {
            $flg = $this->_addTime($sOrderNumber, $transaction_id);
        }

        if ( $flg === FALSE ) {
            $this->error();
        }
        $this->success();
    }


    private function _commitPayOrder( $out_trade_no, $trade_no = '' )
    {
        $oUserRechargeOrder = UserRechargeOrder::findFirst("user_recharge_order_number='{$out_trade_no}'");
        if ( !$oUserRechargeOrder ) {
            return FALSE;
        }
        if ( $oUserRechargeOrder->user_recharge_order_status == 'Y' ) {
            return TRUE;
        }
        $oUser = User::findFirst($oUserRechargeOrder->user_id);
        $this->db->begin();
        $oUserRechargeOrder->user_recharge_order_status         = 'Y';
        $oUserRechargeOrder->user_recharge_order_update_time    = time();
        $oUserRechargeOrder->user_recharge_order_transaction_id = $trade_no;
        $oUserRechargeOrder->user_is_vip                        = 'N';
        if ( $oUser->user_member_expire_time > time() ) {
            $oUserRechargeOrder->user_is_vip = 'Y';
        }
        $oUserAccount = UserAccount::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $oUserRechargeOrder->user_id,
            ]
        ]);
        if ( strlen($oUserAccount->user_device_id) > 18 ) {
            $oUserRechargeOrder->user_type = 'iOS';
        } else {
            $oUserRechargeOrder->user_type = 'Android';
        }
        $res = $oUserRechargeOrder->save();
        if ( !$res ) {
            $this->db->rollback();
            var_dump($oUserRechargeOrder->getMessages());
            return FALSE;
        }
        // 操作用户数据
        $oUserRechargeCombo = UserRechargeCombo::findFirst($oUserRechargeOrder->user_recharge_combo_id);
        $nCoin              = $oUserRechargeOrder->user_recharge_order_coin;
        $nFee               = $oUserRechargeOrder->user_recharge_order_fee;

        $vipRechargeReward = 0;
        if ( $oUser->user_member_expire_time > time() ) {
            // 是VIP  需要判断该套餐是否有VIP 充值赠送金币
            $vipRechargeReward = $oUserRechargeCombo->user_recharge_vip_reward_coin;
        }
        $oUser->user_coin       += $nCoin;
        $oUser->user_total_coin += $nCoin;
        if ( $vipRechargeReward ) {
            $oUser->user_free_coin       += $vipRechargeReward;
            $oUser->user_total_free_coin += $vipRechargeReward;
        }

        if ( $oUser->save() === FALSE ) {
            $this->db->rollback();
            return FALSE;
        }
        // 记录用户流水
        $oUserFinanceLog                         = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
        $oUserFinanceLog->user_id                = $oUser->user_id;
        $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin;
        $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
        $oUserFinanceLog->consume_category_id    = UserConsumeCategory::RECHARGE_COIN;
        $oUserFinanceLog->consume                = +$nCoin;
        $oUserFinanceLog->remark                 = date('Y-m-d H:i:s') . '_充值_' . $nCoin;
        $oUserFinanceLog->flow_id                = $oUserRechargeOrder->user_recharge_order_id;
        $oUserFinanceLog->flow_number            = $oUserRechargeOrder->user_recharge_order_number;
        $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
        $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin - $nCoin;
        $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin;
        $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;

        if ( $oUserFinanceLog->save() === FALSE ) {
            $this->db->rollback();
            return FALSE;
        }

        // 记录用户流水 （VIP充值赠送金币）
        if ( $vipRechargeReward ) {

            $oUserFinanceLogReward                         = new UserFinanceLog();
            $oUserFinanceLogReward->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLogReward->user_id                = $oUser->user_id;
            $oUserFinanceLogReward->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin;
            $oUserFinanceLogReward->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin - $vipRechargeReward;
            $oUserFinanceLogReward->consume_category_id    = UserConsumeCategory::VIP_RECHARGE_REWARD;
            $oUserFinanceLogReward->consume                = +$vipRechargeReward;
            $oUserFinanceLogReward->remark                 = date('Y-m-d H:i:s') . '_VIP充值赠送_' . $vipRechargeReward;
            $oUserFinanceLogReward->flow_id                = $oUserRechargeOrder->user_recharge_order_id;
            $oUserFinanceLogReward->flow_number            = $oUserRechargeOrder->user_recharge_order_number;
            $oUserFinanceLogReward->user_current_user_coin = $oUser->user_coin;
            $oUserFinanceLogReward->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLogReward->user_current_free_coin = $oUser->user_free_coin;
            $oUserFinanceLogReward->user_last_free_coin    = $oUser->user_free_coin - $vipRechargeReward;

            if ( $oUserFinanceLogReward->save() === FALSE ) {
                $this->db->rollback();
                return FALSE;
            }
        }


        $nUserId = $oUserRechargeOrder->user_id;
        $this->db->commit();

        $isFirstRecharge = FALSE;

        $firstRewardVipDay = 0;

        // 如果是该用户的第一笔充值 那么需要将统计数据中 该用户注册日的统计记录的  register_user_recharge_success_count_all + 1
        $rechargeSuccessCount = UserRechargeOrder::count("user_id='{$oUser->user_id}' AND user_recharge_order_status = 'Y'");

        $oUserRechargeOrder->user_recharge_is_first = 'N';
        if ( $rechargeSuccessCount == 1 ) {
            $oUserRechargeOrder->user_recharge_is_first = 'Y';
            $isFirstRecharge                            = TRUE;
            if ( $oUser->user_create_time > strtotime(date('Y-m-d')) ) {
                // 是第一笔 且不是当天  当天的实时统计
                $oDailyDataStat                                           = DailyDataStat::findFirst([
                    'stat_time' => strtotime(date('Y-m-d', $oUser->user_create_time))
                ]);
                $oDailyDataStat->register_user_recharge_success_count_all += 1;
                $oDailyDataStat->save();
            }
            $firstRewardVipDay = $oUserRechargeCombo->first_recharge_reward_vip_day;

            if ( $firstRewardVipDay ) {
                // 如果需要赠送 则添加赠送记录
                $oUserRechargeOrder->reward_vip_day = $firstRewardVipDay;
                $oUser->user_member_expire_time     = $oUser->user_member_expire_time == 0 ? strtotime("+$firstRewardVipDay day") : $oUser->user_member_expire_time + $firstRewardVipDay * 24 * 3600;
                $oUser->save();
            }

            // 判断第一次是否是派单
            $oDispatchChat = DispatchChat::findFirst([
                'dispatch_chat_user_id = :dispatch_chat_user_id:',
                'bind'  => [
                    'dispatch_chat_user_id' => $nUserId
                ],
                'order' => 'dispatch_chat_id desc'
            ]);
            if ( $oDispatchChat && $oDispatchChat->dispatch_chat_status == 1 ) {

                $oAnchorDispatch                                  = AnchorDispatch::findFirst([
                    'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
                    'bind' => [
                        'anchor_dispatch_user_id' => $oDispatchChat->dispatch_chat_anchor_user_id
                    ]
                ]);
                $oAnchorDispatch->anchor_dispatch_recharge_number += 1;
                $oAnchorDispatch->save();

            } else {

                // 第一笔充值 判断第一次体验匹配是否同一天 如果是同一天 给匹配的第一个主播 今日匹配的新用户充值人数 +1
                $oUserMatchLog = UserMatchLog::findFirst([
                    'user_id = :user_id:',
                    'bind' => [
                        'user_id' => $oUserRechargeOrder->user_id
                    ]
                ]);
                if ( $oUserMatchLog && date('Y-m-d', $oUserMatchLog->create_time) == date('Y-m-d') ) {
                    $chatAnchor = Anchor::findFirst([
                        "user_id = :user_id:",
                        'bind' => [ 'user_id' => $oUserMatchLog->anchor_user_id ]
                    ]);
                    if ( $chatAnchor ) {
                        $chatAnchor->anchor_today_new_recharge_count += 1;
                        $chatAnchor->save();

                        // 主播每日统计
                        $oAnchorStatService = new AnchorStatService($chatAnchor->user_id);
                        $oAnchorStatService->save(AnchorStatService::MATCH_RECHARGE_COUNT, 1);
                    }
                }
            }
        }

        if ( $oUser->user_invite_user_id ) {
            $oInviteUser = User::findFirst($oUser->user_invite_user_id);
            if ( !$oInviteUser ) {
                return TRUE;
            }
            if ( $oInviteUser->user_is_anchor == 'Y' ) {
                //主播邀请充值奖励
                if ( Kv::get(Kv::INVITE_ANCHOR_RECHARGE_FLG) ) {
                    if ( $isFirstRecharge ) {
                        $invite_anchor_recharge_radio = intval(Kv::get(Kv::INVITE_ANCHOR_FIRST_RECHARGE_RADIO));
                    } else {
                        $invite_anchor_recharge_radio = intval(Kv::get(Kv::INVITE_ANCHOR_RECHARGE_RADIO));
                    }
                    if ( $invite_anchor_recharge_radio > 0 ) {
                        $oUserFinanceLog->addAnchorInviteRecharge($oUser, $oUserRechargeOrder, $invite_anchor_recharge_radio);
                    }
                }
            } else {
                //邀请充值奖励
                if ( Kv::get(Kv::INVITE_RECHARGE_FLG) ) {
//                    $invite_recharge_radio = intval(Kv::get(Kv::INVITE_RECHARGE_RADIO));
                    //改为奖励“现金”
                    $invite_recharge_radio = intval(Kv::get(Kv::INVITE_RECHARGE_RADIO_CASH));
                    if ( $invite_recharge_radio > 0 ) {
                        $oUser = User::findFirst($nUserId);
                        $oUserFinanceLog->addInviteRecharge($oUser, $oUserRechargeOrder, $invite_recharge_radio, 'cash');
                    }
                }
            }
        } else if ( $oUser->user_invite_agent_id ) {
            // 代理商  有邀请 能获得邀请奖励
            $oAgent = Agent::findFirst($oUser->user_invite_agent_id);
            if ( $oAgent ) {
                $rewardRand = rand(0, 100);
//                if( $oUser->user_create_time >= strtotime(date('Y-m-d')) || $rewardRand <= $oAgent->agent_recharge_reward_radio ){
                // 今日注册的或者 随机数小于 设置数的
                $oUserRechargeOrder->user_recharge_agent_reward_flg = 'N';
                if ( $rewardRand <= $oAgent->agent_recharge_reward_radio ) {
                    // 随机数小于 设置数的；
                    // 如果随机的数 小于等于 要求的值 则可以添加收益 否则不能计算收益
                    if ( $oAgent->has_reward_recharge_max_money == 0 || $nFee < $oAgent->has_reward_recharge_max_money ) {
                        // 如果没有设置充值奖励限制 或者 充值金额小于限制值 才有奖励
                        AgentWaterLog::addRechargeReward($oUser->user_invite_agent_id, $oUserRechargeOrder, $oAgent);
                        $oUserRechargeOrder->user_recharge_agent_reward_flg = 'Y';
                    }
                }
            }
        }
        $oUserRechargeOrder->save();


        $oUserRechargeActionLog = UserRechargeActionLog::findFirst($oUser->user_id);
        if ( !$oUserRechargeActionLog ) {
            // 没有过充值且没有过充VIP
            $oUserRechargeActionLog                       = new UserRechargeActionLog();
            $oUserRechargeActionLog->user_id              = $oUser->user_id;
            $oUserRechargeActionLog->recharge_times       = 1;
            $oUserRechargeActionLog->recharge_total_coin  = $nCoin;
            $oUserRechargeActionLog->recharge_total_money = $nFee;
            $oUserRechargeActionLog->recharge_first_time  = time();
            $oUserRechargeActionLog->recharge_last_time   = time();
            if ( $firstRewardVipDay ) {
                $oUserRechargeActionLog->vip_times       = 1;
                $oUserRechargeActionLog->vip_total_day   = $firstRewardVipDay;
                $oUserRechargeActionLog->vip_total_money = 0;
                $oUserRechargeActionLog->vip_first_time  = time();
                $oUserRechargeActionLog->vip_last_time   = time();
            }
        } else {
            if ( $oUserRechargeActionLog->recharge_times == 0 ) {
                $oUserRechargeActionLog->recharge_first_time = time();
            }
            $oUserRechargeActionLog->recharge_times       += 1;
            $oUserRechargeActionLog->recharge_total_coin  += $nCoin;
            $oUserRechargeActionLog->recharge_total_money += $nFee;
            $oUserRechargeActionLog->recharge_last_time   = time();
            if ( $firstRewardVipDay ) {
                $oUserRechargeActionLog->vip_times       = 1;
                $oUserRechargeActionLog->vip_total_day   = $firstRewardVipDay;
                $oUserRechargeActionLog->vip_total_money = 0;
                $oUserRechargeActionLog->vip_first_time  = time();
                $oUserRechargeActionLog->vip_last_time   = time();
            }
        }
        $oUserRechargeActionLog->save();

        $this->log->info($nUserId . '充值是否有飘屏:' . $oUserRechargeCombo->user_recharge_combo_has_notify);
        if ( $oUserRechargeCombo->user_recharge_combo_has_notify == 'Y' ) {
            // 有充值飘屏
            if ( $oUser->user_id != 118017 ) {
                $this->timServer->setUid();
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                $flg = $this->timServer->sendScrollMsg([
                    'type' => 'recharge',
                    'info' => [
                        'user_nickname' => $oUser->user_nickname,
                        'user_avatar'   => $oUser->user_avatar,
                        'title'         => $oUser->user_nickname,
                        'content'       => sprintf('成功充值 %d %s', $nCoin, Kv::get(Kv::KEY_COIN_NAME)),
                    ]
                ]);
                $this->log->info($nUserId . '充值推送:' . json_encode($flg));
            }
        }


        $aPushMessage = [
            'user_coin' => $oUser->user_coin + $oUser->user_free_coin
        ];
        //支付成功 发送通知
        $this->timServer->setUid($oUser->user_id);
        $this->timServer->rechargeSuccess($aPushMessage);

        return TRUE;
    }


    /**
     * h5支付回调
     */
    public function h5PayAction()
    {
        $receiptData = $this->getParams("receipt_data");
        try {
            $decodeStr = OpensslEncryptHelper::decryptWithOpenssl($receiptData);
            $decodeArr = json_decode($decodeStr, TRUE);
            if ( !$decodeArr ) {
                echo 'failed';
                die;
            }
            $time           = $decodeArr['timestamp'];
            $order_no       = $decodeArr['order_no'];
            $third_order_no = $decodeArr['third_order_no'];
            $payType        = $decodeArr['type'] ?? 'recharge';
            if ( $payType == 'recharge' ) {
                $res = $this->_commitPayOrder($order_no, $third_order_no);
            } else {
                $res = $this->_addTime($order_no, $third_order_no);
            }
            if ( $res ) {
                $row = 'success';
            } else {
                $row = 'failed';
            }

        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        } catch ( \PDOException $e ) {
            $this->error($e->getCode(), $e->getMessage());
        } catch ( Exception $e ) {
            // 重新设置用户在房间的发送礼物ID(发送弹幕ID)
            $this->error($e->getCode(), $e->getMessage());
        }
        echo $row;
    }


    /**
     * curl 发送http请求
     *
     * @param string $url
     * @param string $data
     * @return string
     */
    //lbs的Curl方法
    protected function curl( $url = "", $param = "", $header = "" )
    {

        $postUrl = $url;
        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        if ( !empty($param) ) {
            curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);

        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }


    private function _addTime( $out_trade_no, $user_vip_transaction_id )
    {
        $this->db->begin();
        $oUserVipOrder = UserVipOrder::findFirst("user_vip_order_number = '{$out_trade_no}'");
        if ( !$oUserVipOrder ) {
//            var_dump('订单找不到');
            return FALSE;
        }
        if ( $oUserVipOrder->user_vip_order_status == 'Y' ) {
//            var_dump('订单之前已成功');
            return TRUE;
        }
        $oUserVipCombo                  = UserVipCombo::findFirst($oUserVipOrder->user_vip_combo_id);
        $oUser                          = User::findFirst("user_id={$oUserVipOrder->user_id}");
        $lastVipStatus                  = $oUser->user_member_expire_time > time() ? 'Y' : 'N';
        $nTime                          = $oUser->user_member_expire_time > time() ? $oUser->user_member_expire_time : time();
        $vipStartDateTime               = date('Y-m-d', $nTime);
        $nExpireTime                    = strtotime(sprintf('%s +%s day', date('Y-m-d H:i:s', $nTime), $oUserVipOrder->user_vip_order_combo_month * 30));
        $oUser->user_member_expire_time = $nExpireTime;
        $vipEndDateTime                 = date('Y-m-d', $nExpireTime);
        if ( $oUserVipCombo->user_vip_combo_reward_coin > 0 ) {
            $oUser->user_free_coin += $oUserVipCombo->user_vip_combo_reward_coin;
            // 添加
            // 记录用户流水
            $oUserFinanceLog                         = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id                = $oUser->user_id;
            $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin;
            $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin - $oUserVipCombo->user_vip_combo_reward_coin;
            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::VIP_RECHARGE_REWARD;
            $oUserFinanceLog->consume                = +$oUserVipCombo->user_vip_combo_reward_coin;
            $oUserFinanceLog->remark                 = date('Y-m-d H:i:s') . '_充值VIP赠送_' . $oUserVipCombo->user_vip_combo_reward_coin;
            $oUserFinanceLog->flow_id                = $oUserVipOrder->user_vip_order_id;
            $oUserFinanceLog->flow_number            = $oUserVipOrder->user_vip_order_number;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin - $oUserVipCombo->user_vip_combo_reward_coin;

            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                return FALSE;
            }
        }
        $oUser->user_vip_exp   += $oUserVipCombo->user_vip_order_combo_month * 30 * 1;
        $oUser->user_vip_level = VipLevel::getLevelInfo($oUser->user_vip_exp)['level'];
        if ( !$oUser->save() ) {
//            var_dump('更新失败'.json_encode($oUser->getMessages()));
            $this->db->rollback();
            return FALSE;
        }
        $oUserVipOrder->user_vip_order_status   = 'Y';
        $oUserVipOrder->user_vip_transaction_id = $user_vip_transaction_id;
        $oUserAccount                           = UserAccount::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $oUserVipOrder->user_id,
            ]
        ]);
        if ( strlen($oUserAccount->user_device_id) > 18 ) {
            $oUserVipOrder->user_type = 'iOS';
        } else {
            $oUserVipOrder->user_type = 'Android';
        }
        if ( !$oUserVipOrder->save() ) {
            $this->db->rollback();
//            var_dump('更新失败'.json_encode($oUserVipOrder->getMessages()));
            return FALSE;
        }
        // 记录用户流水
        $oUserFinanceLog                      = new UserFinanceLog();
        $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_MONEY;
        $oUserFinanceLog->user_id             = $oUser->user_id;
        $oUserFinanceLog->user_current_amount = 0;
        $oUserFinanceLog->user_last_amount    = 0;
        $oUserFinanceLog->consume_category_id = UserConsumeCategory::RECHARGE_VIP;
        $oUserFinanceLog->consume             = $oUserVipOrder->user_vip_order_combo_fee;
        $oUserFinanceLog->remark              = '购买VIP_' . $vipStartDateTime . '-' . $vipEndDateTime;
        $oUserFinanceLog->flow_id             = $oUserVipOrder->user_vip_order_id;
        $oUserFinanceLog->flow_number         = $oUserVipOrder->user_vip_order_number;
        if ( $oUserFinanceLog->save() === FALSE ) {
            $this->db->rollback();
//            var_dump('更新失败'.json_encode($oUserFinanceLog->getMessages()));
            return FALSE;
        }

        if ( $oUser->user_invite_agent_id ) {
//            var_dump('代理商：',$oUser->user_invite_agent_id);
            // 代理商  有邀请 能获得购买VIP奖励
            $flg = AgentWaterLog::addVipReward($oUser->user_invite_agent_id, $oUserVipOrder);
//            var_dump($flg);
        } else if ( $oUser->user_invite_user_id ) {
//            var_dump('邀请用户：',$oUser->user_invite_user_id);
            // 用户邀请 如果用户不是主播 则可以得到收益
            $flg = UserCashLog::addInviteVipReward($oUser->user_invite_user_id, $oUserVipOrder);
//            var_dump($flg);
        }


        if ( $lastVipStatus == 'N' ) {
            $oAnchorSayhiService = new UserSayhiService($oUser->user_id);
            $flg                 = $oAnchorSayhiService->delete();
        }

        $this->db->commit();

        $oUserRechargeActionLog = UserRechargeActionLog::findFirst($oUser->user_id);
        if ( !$oUserRechargeActionLog ) {
            // 没有过充值且没有过充VIP
            $oUserRechargeActionLog                  = new UserRechargeActionLog();
            $oUserRechargeActionLog->user_id         = $oUser->user_id;
            $oUserRechargeActionLog->vip_times       = 1;
            $oUserRechargeActionLog->vip_total_day   = $oUserVipOrder->user_vip_order_combo_month * 30;
            $oUserRechargeActionLog->vip_total_money = $oUserVipOrder->user_vip_order_combo_fee;
            $oUserRechargeActionLog->vip_first_time  = time();
            $oUserRechargeActionLog->vip_last_time   = time();
        } else {
            if ( $oUserRechargeActionLog->vip_times == 0 ) {
                $oUserRechargeActionLog->recharge_first_time = time();
            }
            $oUserRechargeActionLog->vip_times       += 1;
            $oUserRechargeActionLog->vip_total_day   += $oUserVipOrder->user_vip_order_combo_month * 30;
            $oUserRechargeActionLog->vip_total_money += $oUserVipOrder->user_vip_order_combo_fee;
            $oUserRechargeActionLog->vip_last_time   = time();
        }
        $oUserRechargeActionLog->save();
//        var_dump($oUserRechargeActionLog->getMessages());
        $aPushMessage = [
            'user_member_expire_time' => $oUser->user_member_expire_time
        ];
        //支付成功 发送通知
        $this->timServer->setUid($oUser->user_id);
        $this->timServer->vipPaySuccess($aPushMessage);

        $this->timServer->setUid();
        $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
        $showDate = $oUserVipOrder->user_vip_order_combo_month . '月';
        if ( $oUserVipOrder->user_vip_order_combo_month == 12 ) {
            $showDate = '年费';
        }
        $flg = $this->timServer->sendScrollMsg([
            'type' => 'recharge_vip',
            'info' => [
                'user_nickname' => $oUser->user_nickname,
                'user_avatar'   => $oUser->user_avatar,
                'title'         => $oUser->user_nickname,
                'content'       => sprintf('成功充值 %s VIP', $showDate),
            ]
        ]);


        return TRUE;
    }

    public function screenshotAction()
    {
//        $this->log->info( "screenshot:  " . file_get_contents("php://input"));
        $return = [
            'ActionStatus' => 'OK',
            'ErrorCode'    => 0,
            'ErrorInfo'    => ""
        ];
        exit($return);
    }


    public function identifyAction()
    {
        $notifyData = file_get_contents("php://input");

        if ( !$notifyData ) {
            return;
        }
        $result = json_decode($notifyData, TRUE);
        if ( !$result ) {
            return;
        }
//        $this->log->info("identify:  " . file_get_contents("php://input"));
        $confidence = $result['confidence'] ?? 0;
        $streamId   = $result['streamId'] ?? '';

        if ( $confidence < 81 || !$streamId ) {
            return;
        }

//        $this->log->info("identify_high:  " . file_get_contents("php://input"));
        // 先找到流对应的地址
        $oChatStreamService = new ChatStreamService($streamId);
        $chatData           = $oChatStreamService->getData();
        if ( !$chatData ) {
            $this->log->info("鉴黄不存在流:" . $streamId);
            return;
        }
        $identifyUserId      = $chatData['user_id'] ?? 0;
        $identifyChatLogId   = $chatData['chat_log_id'] ?? 0;
        $identifyOtherUserId = $chatData['other_user_id'] ?? 0;
        if ( !$identifyUserId || !$identifyChatLogId ) {
            $this->log->info("鉴黄不存在数据:" . json_encode($chatData));
            return;
        }

        $oUserIdentifyLog = UserIdentifyLog::findFirst([
            'user_identify_user_id = :user_id: AND user_identify_chat_log_id = :chat_log_id:',
            'bind' => [
                'user_id'     => $identifyUserId,
                'chat_log_id' => $identifyChatLogId,
            ]
        ]);
        if ( $oUserIdentifyLog ) {
            // 存在 判断记录是否小于15秒
            if ( time() - $oUserIdentifyLog->user_identify_update_time < 15 ) {
                // 小于15秒则不处理
                $return = [
                    'ActionStatus' => 'OK',
                    'ErrorCode'    => 0,
                    'ErrorInfo'    => ""
                ];
                exit($return);
            }
            // 大于15秒 且不是第一次了 则挂断
            $oNewUserIdentifyLog                            = new UserIdentifyLog();
            $oNewUserIdentifyLog->user_identify_user_id     = $identifyUserId;
            $oNewUserIdentifyLog->user_identify_chat_log_id = $identifyChatLogId;
            $oNewUserIdentifyLog->user_identify_confidence  = $confidence;
            $oNewUserIdentifyLog->user_identify_image_url   = $result['img'] ?? '';
            $oNewUserIdentifyLog->user_identify_check_text  = $notifyData;
            $oNewUserIdentifyLog->user_identify_times       = $oUserIdentifyLog->user_identify_times + 1;
            if ( $oNewUserIdentifyLog->save() === FALSE ) {
                $this->log->info("鉴黄存储记录失败:" . $oNewUserIdentifyLog->getMessages()[0]->getMessage());
            }
            /**
             * 挂断聊天
             **/
            $result = $this->httpRequest(sprintf('%s/v1/live/anchor/hangupChat?%s', $this->config->application->api_url, http_build_query([
                'uid'          => $identifyUserId,
                'debug'        => 1,
                'chat_log'     => $identifyChatLogId,
                'cli_api_key'  => $this->config->application->cli_api_key,
                'hang_up_type' => 'auto',
                'detail'       => '涉黄'
            ])));
//            $this->log->info("鉴黄挂断结果:" . $result);

        } else {
            // 第一次涉黄  记录 并发送提醒
            $oNewUserIdentifyLog                            = new UserIdentifyLog();
            $oNewUserIdentifyLog->user_identify_user_id     = $identifyUserId;
            $oNewUserIdentifyLog->user_identify_chat_log_id = $identifyChatLogId;
            $oNewUserIdentifyLog->user_identify_confidence  = $confidence;
            $oNewUserIdentifyLog->user_identify_image_url   = $result['img'] ?? '';
            $oNewUserIdentifyLog->user_identify_check_text  = $notifyData;
            $oNewUserIdentifyLog->user_identify_times       = 1;
            if ( $oNewUserIdentifyLog->save() === FALSE ) {
                $this->log->info("鉴黄存储记录失败:" . $oNewUserIdentifyLog->getMessages()[0]->getMessage());
            }

            $notifyContent = "检测用户有裸露涉黄违规行为，请立即停止该行为，情节严重官方将立即进行封号操作。";
            if ( $chatData['user_is_anchor'] == 'Y' ) {
                $notifyContent = "检测主播有裸露涉黄等违规行为，请立即停止该行为，情节严重官方将立即进行封号操作。";
            }
            if ( $identifyOtherUserId ) {
                $data = file_get_contents(sprintf('%s/im/sendBatch?%s', $this->config->application->api_url, http_build_query([
                    'content'  => $notifyContent,
                    'user_arr' => [
                        $identifyUserId,
                        $identifyOtherUserId,
                    ],
                ])));
            } else {
                $data = file_get_contents(sprintf('%s/im/sendNotifyRoom?%s', $this->config->application->api_url, http_build_query([
                    'content' => $notifyContent,
                    'user_id' => $identifyUserId,
                ])));
            }
//            $this->log->info("鉴黄通知结果:" . $data);

        }

        $return = [
            'ActionStatus' => 'OK',
            'ErrorCode'    => 0,
            'ErrorInfo'    => ""
        ];
        exit($return);
    }


    public function test1Action()
    {
        $tmp = [
            'number_min' => '200',
            'number_max' => '300',
        ];
        echo json_encode($tmp);
        die;
        $notifyData = '{"ocrMsg":"","type":[1],"confidence":98,"normalScore":1,"hotScore":0,"pornScore":98,"screenshotTime":1554987984,"level":0,"img":"http://e9963995lvb1257853087screenshot-1252813850.file.myqcloud.com/2019-04-11/34574_842dc484370c3a6c869e47e6cc3606ad-screenshot-21-06-24-480x640.jpg","abductionRisk":[],"faceDetails":[],"sendTime":1554987985,"tid":20001,"streamId":"34574_842dc484370c3a6c869e47e6cc3606ad","channelId":"34574_842dc484370c3a6c869e47e6cc3606ad"}';
        $result     = json_decode($notifyData, TRUE);
        if ( !$result ) {
            return;
        }
        $confidence = $result['confidence'] ?? 0;
        $streamId   = $result['streamId'] ?? '';

        if ( $confidence < 81 || !$streamId ) {
            return;
        }

        // 先找到流对应的地址
        $oChatStreamService = new ChatStreamService($streamId);
        $chatData           = $oChatStreamService->getData();
        if ( !$chatData ) {
            return;
        }
        $identifyUserId    = $chatData['user_id'] ?? 0;
        $identifyChatLogId = $chatData['chat_log_id'] ?? 0;
        if ( !$identifyUserId || !$identifyChatLogId ) {
            return;
        }

        $oUserIdentifyLog = UserIdentifyLog::findFirst([
            'user_identify_user_id = :user_id: AND user_identify_chat_log_id = :chat_log_id:',
            'bind' => [
                'user_id'     => $identifyUserId,
                'chat_log_id' => $identifyChatLogId,
            ]
        ]);
        if ( $oUserIdentifyLog ) {
            // 存在 判断记录是否小于15秒
            if ( time() - $oUserIdentifyLog->user_identify_update_time < 15 ) {
                // 小于15秒则不处理
                $return = [
                    'ActionStatus' => 'OK',
                    'ErrorCode'    => 0,
                    'ErrorInfo'    => ""
                ];
                exit($return);
            }
            // 大于15秒 且不是第一次了 则挂断
            $oNewUserIdentifyLog                            = new UserIdentifyLog();
            $oNewUserIdentifyLog->user_identify_user_id     = $identifyUserId;
            $oNewUserIdentifyLog->user_identify_chat_log_id = $identifyChatLogId;
            $oNewUserIdentifyLog->user_identify_confidence  = $confidence;
            $oNewUserIdentifyLog->user_identify_image_url   = $result['img'] ?? '';
            $oNewUserIdentifyLog->user_identify_check_text  = $notifyData;
            $oNewUserIdentifyLog->user_identify_times       = $oUserIdentifyLog->user_identify_times + 1;
            $oNewUserIdentifyLog->save();
            /**
             * 挂断聊天
             **/
            $result = $this->httpRequest(sprintf('%s/v1/live/anchor/hangupChat?%s', $this->config->application->api_url, http_build_query([
                'uid'          => $identifyUserId,
                'debug'        => 1,
                'chat_log'     => $identifyChatLogId,
                'cli_api_key'  => $this->config->application->cli_api_key,
                'hang_up_type' => 'auto',
                'detail'       => '涉黄'
            ])));

        } else {
            // 第一次涉黄  记录 并发送提醒
            $oNewUserIdentifyLog                            = new UserIdentifyLog();
            $oNewUserIdentifyLog->user_identify_user_id     = $identifyUserId;
            $oNewUserIdentifyLog->user_identify_chat_log_id = $identifyChatLogId;
            $oNewUserIdentifyLog->user_identify_confidence  = $confidence;
            $oNewUserIdentifyLog->user_identify_image_url   = $result['img'] ?? '';
            $oNewUserIdentifyLog->user_identify_check_text  = $notifyData;
            $oNewUserIdentifyLog->user_identify_times       = 1;
            $oNewUserIdentifyLog->save();

            $notifyContent = "检测用户有裸露涉黄违规行为，请立即停止该行为，情节严重官方将立即进行封号操作。";
            if ( $chatData['user_is_anchor'] == 'Y' ) {
                $notifyContent = "检测主播有裸露涉黄等违规行为，请立即停止该行为，情节严重官方将立即进行封号操作。";
            }
            $data = file_get_contents(sprintf('%s/im/sendNotifyRoom?%s', $this->config->application->api_url, http_build_query([
                'content' => $notifyContent,
                'user_id' => $identifyUserId,
            ])));
        }

        $return = [
            'ActionStatus' => 'OK',
            'ErrorCode'    => 0,
            'ErrorInfo'    => ""
        ];
        exit($return);
    }

}

