<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | IM控制器                                                               |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers;

use app\helper\TIM;
use app\models\Kv;
use app\models\ShortPosts;
use app\models\User;

/**
 * ImController
 *
 * */
class ImController extends ControllerBase
{
	
	public function indexAction()
	{
		
	}

	/**
	 * notifyAction 给后台使用，不能将接口公开
	 */
	public function notifyAction()
	{
		$aParam = $this->getParams();

		if ($aParam) {
			// $this->imServer->setRid($aParam['rid']);
			// $this->imServer->setUid($aParam['uid']);
			// $data = json_decode($aParam['msg'], true);
			// echo $this->imServer->notify($data['type'], isset($data['msg']) ? $data['msg'] : '', $data['data']) ? 'success' : 'fail';

			$this->timServer->setRid($aParam['rid']);
			$this->timServer->setUid($aParam['uid']);
			$data = json_decode($aParam['msg'], true);
			var_dump($this->timServer->notify($data['type'], isset($data['msg']) ? $data['msg'] : '', $data['data']));
		} else {
			echo "param error";
		}

	}

    public function sendNotifyRoomAction()
    {
        $aParam = $this->getParams();
        $content = $aParam['content'] ?? "";
        $userId = $aParam['user_id'] ?? 0;
        if(!$userId){
            return false;
        }
        if ($content && $userId) {
            $aPushMessage['user'] = [
                'user_id'       => 0,
                'user_nickname' => '系统管理员',
            ];

            // 聊天数据
            $aPushMessage['chat'] = [
                'content' => $content,
                'time'    => time(),
            ];
            $this->timServer->setUid($userId);
            $flg = $this->timServer->sendRoomChatSignal($aPushMessage);

        } else {
            echo "param error";
        }
    }

    public function sendBatchAction()
    {
        $aParam = $this->getParams();
        $content = $aParam['content'] ?? "";
        $userArr = $aParam['user_arr'] ?? [];
        if ($content && $userArr) {
            $aPushMessage['user'] = [
                'user_id'       => 0,
                'user_nickname' => '系统管理员',
            ];

            // 聊天数据
            $aPushMessage['chat'] = [
                'content' => $content,
                'time'    => time(),
            ];
            $this->timServer->setUid($userArr);
            $flg = $this->timServer->sendChatSignalBatch($aPushMessage);

        } else {
            echo "param error";
        }
	}



	/**
	 * allcountAction 给后台使用，不能将接口公开
	 */
	public function allcountAction()
	{
		$data = $this->imServer->allcount();
		$this->success($data);
	}

	public function timAction()
	{
		$nGroupId = $this->getParams('gid', 'int', 0);
		$nUserId = $this->getParams('uid', 'int', 0);
		$this->timServer->setRid($nGroupId);
		$this->timServer->setUid($nUserId);
		$this->success($this->timServer->sendKillSignal());
	}

    public function leaveGroupAction()
    {
        $nGroupId = $this->getParams('gid', 'string');
        $nUserId = $this->getParams('uid', 'string');
        $this->timServer->setRid($nGroupId);
        $this->timServer->setAccountId($nUserId);
        $this->success($this->timServer->leaveRoom());
	}


    /**
     * 系统公告
     */
    public function notificationAction()
    {
        $sType = $this->getParams('type', 'string');
        $nUserIds = $this->getParams('user_ids');
        $sContent = $this->getParams('content','string');
        if(empty($sContent)){
            $this->error(10001,'内容为空');
        }
        $sendData = [
            'content' => $sContent
        ];
        switch ($sType){
            case 'all':
                // 推送给所有人
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                $result = $this->timServer->sendNotificationMsg($sendData);
                break;
            case 'anchor':
                $this->timServer->setRid( Kv::get(Kv::MATCH_CENTER_ROOM_ID));
                $result = $this->timServer->sendNotificationMsg($sendData);
                break;
            default:
                if(empty($nUserIds)){
                    $this->error(10001,'用户为空');
                }
                if(count($nUserIds) == 1){
                    $this->timServer->setUid($nUserIds[0]);
                    $result = $this->timServer->sendNotificationMsg($sendData);
                }else{
                    $this->timServer->setUid($nUserIds);
                    $result = $this->timServer->sendNotificationMsgBatch($sendData);
                }
        }
        $this->success($result);
    }

	/**
	 * killOnlineAction 发送下线信号，给后台使用
	 */
	public function killOnlineAction()
	{
		$nUserId   = $this->getParams('user_id', 'int', 0);
		$sContent  = $this->getParams('content', 'string', '');
		$sDeviceId = $this->getParams('device_id', 'string', '');

		$aPushMessage = [
			'device_id' => $sDeviceId,
		];

		$this->timServer->setUid($nUserId);
		$this->timServer->sendKillSignal($sContent, $aPushMessage);
		$this->success();
	}

    /**
     * 检测状态
     */
    public function checkOnlineStatusAction()
    {
//        return false;
        $p = 1;
        while(true){
            $offset = ($p - 1) * 100;
            $userArr = User::find([
                'user_online_status != :user_online_status:',
                'bind' => [
                    'user_online_status' => User::USER_ONLINE_STATUS_OFFLINE
                ],
                'columns' => 'user_id,user_is_anchor,user_online_status',
                'order' => 'user_id',
                'limit' => [100,$offset]
            ])->toArray();
            $anchor_ids_arr = [];
            $ids_arr = [];
            $old_push_online_arr = [];
            foreach($userArr as $userItem){
                if($userItem['user_is_anchor'] == 'Y'){
                    $anchor_ids_arr[] =  $userItem['user_id'];
                }
                $ids_arr[] = $userItem['user_id'];
                if($userItem['user_online_status'] == User::USER_ONLINE_STATUS_PUSHONLINE){
                    $old_push_online_arr[] = $userItem['user_id'];
                }
            }
            $result = $this->timServer->querystate($ids_arr);

            if(isset($result['ActionStatus']) && $result['ActionStatus'] == 'OK'){
//            判断成功
                $onlineOnlineArr = [];
                $offOnlineArr = [];
                $pushOnlineArr = [];
                $anchorOffOnlineArr = [];
                // 结果集
                $resultIdArr = [];
                foreach ($result['QueryResult'] as $QueryResultItem){
                    $resultIdArr[] = $QueryResultItem['To_Account'];
                    if($QueryResultItem['State'] == 'Offline' && in_array($QueryResultItem['To_Account'],$ids_arr)){
                        $offOnlineArr[] = $QueryResultItem['To_Account'];
                        if(in_array($QueryResultItem['To_Account'],$anchor_ids_arr)){
                            // 需要改变主播表状态的主播
                            $anchorOffOnlineArr[] = $QueryResultItem['To_Account'];
                        }
                    }elseif($QueryResultItem['State'] == 'PushOnline' && in_array($QueryResultItem['To_Account'],$ids_arr)){
                        $pushOnlineArr[] = $QueryResultItem['To_Account'];
                        $offOnlineArr[] = $QueryResultItem['To_Account'];
                    }elseif($QueryResultItem['State'] == 'Online' && in_array($QueryResultItem['To_Account'],$old_push_online_arr)){
                        $onlineOnlineArr[] = $QueryResultItem['To_Account'];
                    }
                }
                // 遍历查询集合  如果结果中没有则认为是离线状态
                foreach ($ids_arr as $searchIdItem){
                    if(!in_array($searchIdItem,$resultIdArr)){
                        $offOnlineArr[] = $searchIdItem;
                        if(in_array($searchIdItem,$anchor_ids_arr)){
                            // 需要改变主播表状态的主播
                            $anchorOffOnlineArr[] = $searchIdItem;
                        }
                    }
                }

                $onlineOnlineStr = implode(',',$onlineOnlineArr);
                $offOnlineStr = implode(',',$offOnlineArr);
                $pushOnlineStr = implode(',',$pushOnlineArr);
                $anchorOffOnlineStr = implode(',',$anchorOffOnlineArr);

                print "后台用户转换到在线".count($onlineOnlineArr) . "条记录". $onlineOnlineStr ."\n";
                print "在线用户转换到离线".count($offOnlineArr) . "条记录". $offOnlineStr ."\n";
                print "在线用户转换到后台".count($pushOnlineArr) . "条记录". $pushOnlineStr ."\n";
                print "2018-7-13暂时将后台用户提示为离线" ."\n";

                $model = new User();
                $connection = $model->getWriteConnection();
                if($onlineOnlineArr){
                    /*修改在线用户的状态*/
                    $sql = "UPDATE user SET user_online_status = :user_online_status where user_id in ($onlineOnlineStr)";
                    $connection->execute($sql, [
                        'user_online_status'   => User::USER_ONLINE_STATUS_ONLINE,
                    ]);
                }
                if($offOnlineArr){
                    /*修改离线用户的状态*/
                    $sql = "UPDATE user SET user_online_status = :user_online_status where user_id in ($offOnlineStr)";
                    $connection->execute($sql, [
                        'user_online_status'   => User::USER_ONLINE_STATUS_OFFLINE,
                    ]);
                }
                if($pushOnlineArr){
                    /*修改后台用户的状态*/
                    /* print "2018-7-13暂时将后台用户提示为离线" ."\n";*/
//                    $sql = "UPDATE user SET user_online_status = :user_online_status where user_id in ($pushOnlineStr)";
////                    $connection->execute($sql, [
//////                        'user_online_status'   => User::USER_ONLINE_STATUS_PUSHONLINE,
////                        'user_online_status'   => User::USER_ONLINE_STATUS_OFFLINE,
////                    ]);
                }
                if($anchorOffOnlineArr){
                    /*修改离线主播的状态*/
                    $sql = "UPDATE anchor SET anchor_chat_status = 1 where (anchor_chat_status = 2 or anchor_chat_status = 3) AND user_id in ($anchorOffOnlineStr)";
                    $connection->execute($sql);
                }

                if($pushOnlineArr){
                    /*修改后台用户的状态*/
                    /* print "2018-7-13暂时将后台用户提示为离线" ."\n";*/
//                    $sql = "UPDATE anchor SET anchor_chat_status = 1 where (anchor_chat_status = 2 or anchor_chat_status = 3) AND user_id in ($pushOnlineStr)";
//                    $connection->execute($sql);
                }
            }
            print "已操作完".count($userArr) . "条记录\n";
            if(count($userArr) < 100){
                break;
            }
            $p ++;
            sleep(1);
        }


	}


    /**
     * offlineAllAction 发送全局下线信号，给后台使用
     */
    public function offlineAllAction()
    {
        $nUserId   = $this->getParams('user_id', 'int', 0);
        $sContent  = $this->getParams('content', 'string', '');
        $sDeviceId = $this->getParams('device_id', 'string', '');

        $sContent = '系统维护中，预计持续30分钟';
        $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
        $this->timServer->sendOfflineAll([
            'content' => $sContent
        ]);
        $this->success();
    }


    /**
     * scrollMessageAction 发送滚屏，给后台使用
     */
    public function scrollMessageAction()
    {
        $nUserId   = $this->getParams('user_id', 'int', 0);
        $sType = $this->getParams('type','string','recharge');
        $sInfo  = $this->getParams('info');

        if($nUserId){
            $this->timServer->setUid((string)$nUserId);
        }else{
            $this->timServer->setUid();
        }
        $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
        $res = $this->timServer->sendScrollMsg([
            'type' => $sType,
            'info' => $sInfo
        ]);
        $this->success($res);
    }


    public function testAction()
    {
        $data = ShortPosts::getPostsCount(186);
        var_dump($data);die;
        $ShortPosts = ShortPosts::findFirst(20);
        var_dump($ShortPosts->test());
    }
}