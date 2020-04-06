<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 腾讯云直播服务                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\helper;

use Exception;
use app\models\UserLevelPrivilege;
use app\helper\TimRestApi;

class IMService extends IIMService
{
    use \app\helper\traits\ImSends;

    /** @var string API URL */
    protected $_api_url;

    /** @var string WS URL */
    protected $_ws_url;

    /** @var int rid */
    protected $_rid;

    /** @var int uid */
    protected $_uid;

    /** @var string extra */
    protected $_extra = '';

    /** @var string user */
    protected $_user = '';

    /** @var string token */
    protected $_token = '';

    /**
     * setApiUrl 设置API URL
     *
     * @param string $sUrl
     * @return viod
     */
    public function setApiUrl($sUrl)
    {
        $this->_api_url = $sUrl;
    }

    /**
     * setApiUrl 设置WS URL
     *
     * @param string $sUrl
     * @return viod
     */
    public function setWsUrl($sUrl)
    {
        $this->_ws_url = $sUrl;
    }

    /**
     * setRoomId 设置房间号
     *
     * @param int $nRid
     * @return viod
     */
    public function setRid($nRid)
    {
        $this->_rid = $nRid;
    }

    /**
     * setUid 设置用户ID
     *
     * @param int $nUid
     * @return viod
     */
    public function setUid($nUid)
    {
        $this->_uid = $nUid;
    }

    /**
     * setToken 设置token
     *
     * @param string $sToken
     * @return viod
     */
    public function setToken($sToken)
    {
        $this->_token = $sToken;
    }

    /**
     * setExtra 设置Extra
     *
     * @param \app\models\User $oUser
     * @return viod
     */
    public function setExtra(\app\models\User $oUser)
    {


        $row          = [
            'user' => [
                'user_id'        => $oUser->user_id,
                'user_avatar'    => $oUser->user_avatar,
                'user_level'     => $oUser->user_level,
                'user_nickname'  => $oUser->user_nickname,
                'user_sex'       => $oUser->user_sex,
                'user_is_member' => $oUser->user_member_expire_time == 0 ? 'N' : (time() > $oUser->user_member_expire_time ? 'O' : 'Y'),
            ],
        ];
        $this->_user  = $row;
        $this->_extra = base64_encode(json_encode($row));
    }

    /**
     * getApiUrl 获取IM API URL
     *
     * @return string
     */
    public function getApiUrl()
    {
        return $this->_api_url;
    }

    /**
     * getWsUrl 获取websocket URL
     *
     * @return string
     */
    public function getWsUrl()
    {
        $param = http_build_query([
            'rid'   => $this->_rid,
            'uid'   => $this->_uid,
            'token' => $this->_token,
            'extra' => $this->_extra,
        ]);
        return $this->_ws_url . '?' . $param;
    }

    /**
     * notify 通知
     *
     * @return bool
     */
    public function notify($sMsgCode = '', $sMsg = '', $aData = [])
    {
        if ( !isset(static::$aMsg[$sMsgCode]) ) {
            throw new Exception(sprintf("IM SERVER Exception: %s code not exists", $sMsgCode));
        }

        $aMsg = json_encode([
            'type' => $sMsgCode,
            'msg'  => $sMsg ? $sMsg : static::$aMsg[$sMsgCode],
            'data' => (object)$aData,
        ], JSON_UNESCAPED_UNICODE);

        $param = http_build_query([
            'rid' => $this->_rid,
            'uid' => $this->_uid ?: '',
            'msg' => $aMsg,
        ]);
        $url   = $this->_api_url . '/broadcast';

        if ( isset($_GET['debug']) ) {
            print_r($url . '?' . $param);
            echo "<br>";
            echo 1111;
            echo "<br>";
            print_r($this->_extra);
        }

        return $this->curl($url, $param) == 'success' ? TRUE : FALSE;
    }

    /**
     * syncuser 更新用户信息
     *
     * @return bool
     */
    public function syncuser()
    {
        $url = $this->_api_url . "/syncuser";
        $url .= "?rid=" . $this->_rid;
        $url .= "&uid=" . $this->_uid;
        $url .= "&extra=" . $this->_extra;
        $url .= "&token=" . $this->_token;
        $res = $this->curl($url);
        if ( $res == 'ok' ) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取房间人数
     *
     * @return string
     */
    public function count()
    {
        $url = $this->_api_url . "/count?rid=" . $this->_rid;
        return $this->curl($url) ?: '0';
    }

    /**
     * 获取所有房间人数
     *
     * @return array
     */
    public function allcount()
    {
        $url = $this->_api_url . "/allcount";
        $res = $this->curl($url);

        if ( !empty($res) ) {
            $result = json_decode($res, TRUE);
            if ( $result ) {
                return $result;
            } else {
                return [];
            }
        } else {
            return [];
        }
    }

    /**
     * leave 离开房间，断开长连接
     *
     * @return string
     */
    public function leave()
    {
        $param = http_build_query([
            'rid' => $this->_rid,
            'uid' => $this->_uid,
        ]);
        $url   = $this->_api_url . '/leave';
        return $this->curl($url, $param);
    }

    //lbs的Curl方法
    protected function curl($url = "", $param = "", $header = "")
    {

        $postUrl = $url;
        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ( !empty($param) ) {
            curl_setopt($ch, CURLOPT_POST, 1);                      //post提交方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        if ( !empty($header) ) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);           // 增加 HTTP Header（头）里的字段
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
        return $data;
    }

}