<?php

namespace app\tasks;

use Phalcon\Exception;

/**
 * OnlineTask 在线控制
 */
class OnlineTask extends MainTask
{
    private $_online_anchor_key = 'online:anchor';
    /**
     * 在线主播 活动时间超过 10分钟的 提示过期
     */
    public function AnchorAction()
    {
        $anchorList = $this->redis->sMembers($this->_online_anchor_key);

        // 取出所有在线的 并判断上次活跃时间是否超过10分钟
        $offline = [];
        foreach ($anchorList as $anchor){
            if(!$anchor){
                continue;
            }
            if($this->_checkOnlineByAction($anchor) === FALSE){
                // 已经离线 将在线状态改变
                $offline[] = $anchor;
                // 处理在线列表
                $this->redis->sAdd(sprintf('offline:anchor:%s', date('YmdH')), $anchor);
                return $this->redis->sRem($this->_online_anchor_key, $anchor);
            }
        }
        if($offline){
            $offlineIdStr = implode(',',$offline);
            $sql = "update user set user_online_status = 'Offline' where user_id in({$offlineIdStr})";
            $this->db->execute($sql);

            $sql = "update anchor set anchor_chat_status = 1 where user_id in({$offlineIdStr}) AND anchor_chat_status = 3";
            $this->db->execute($sql);
//            if(count($offline) == 1){
//                $sql = "update user set user_online_status = 'Offline' where user_id = {$offline[0]}";
//                $this->db->execute($sql);
//
//                $sql = "update anchor set anchor_chat_status = 1 where user_id = {$offline[0]} AND anchor_chat_status = 3";
//                $this->db->execute($sql);
//            }else{
//                $offlineIdStr = implode(',',$offline);
//                $sql = "update user set user_online_status = 'Offline' where user_id in({$offlineIdStr})";
//                $this->db->execute($sql);
//
//                $sql = "update anchor set anchor_chat_status = 1 where user_id in({$offlineIdStr}) AND anchor_chat_status = 3";
//                $this->db->execute($sql);
//            }

            print "[". date('Y-m-d H:i:s')."] update anchor to offline [{$offlineIdStr}]\n";
        }
	}

    /**
     * 通过最后一次活动时间判断是否在线
     */
	private function _checkOnlineByAction($nUserId){
        $key = $this->_getOnlineActionKey($nUserId);
        $lastActionTime = $this->redis->hGet($key,'time');
        $overSecond = 600;
        if($lastActionTime > time() - $overSecond){
            return TRUE;
        }
        return FALSE;
    }

	private function _getOnlineActionKey($nUserId){
        return sprintf("online:action:%s",$nUserId);
    }
}