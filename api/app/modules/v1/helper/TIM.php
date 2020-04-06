<?php

namespace app\helper;

use Exception;
use app\helper\TimRestApi\TimRestAPI;
use app\helper\TimRestApi\signature\TLSSigAPI;
use app\helper\TimRestApi\signature\TLSSigAPIv2;
use app\models\UserLevelPrivilege;

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | TIM腾讯云通讯                                                          |
 +------------------------------------------------------------------------+
 +------------------------------------------------------------------------+
 */

class TIM extends IIMService
{
    use \app\helper\traits\ImSends;

    /** @var string 新版本秘钥 */
    private $key;
    /** @var string 管理员用户名 */
    private $identifier;
    /** @var string APP ID */
    private $appId;
    /** @var string 私钥 */
    private $privateKey;
    /** @var string 公钥 */
    private $publicKey;
    /** @var string 群id */
    private $groupId;
    /** @var string 接收用户id */
    private $receiverId;
    /** @var string 当前操作的用户id */
    private $accountId;
    /** @var string extra */
    protected $_extra = '';
    /** @var string user */
    protected $_user = '';
    /** @var string 签名版本 */
    private $sign_version = 'v1';

    /**
     * setExtra 设置Extra
     *
     * @param \app\models\User $oUser
     * @param bool $outSide
     * @return void
     */
    public function setExtra( \app\models\User $oUser, $outSide = TRUE )
    {
        if ( $outSide ) {
            $row = [
                'user_id'         => $oUser->user_id,
                'user_avatar'     => $oUser->user_avatar,
                'user_level'      => $oUser->user_level,
                'user_nickname'   => $oUser->user_nickname,
                'user_vip_level'  => $oUser->user_vip_level,
                'user_is_member'  => $oUser->user_member_expire_time > time() ? 'Y' : 'N',
                'is_level_effect' => 'N',
                'user_sex'        => $oUser->user_sex,
                'user_birth'      => $oUser->user_birth,
            ];
        }else{
            $row          = [
                'user' => [
                    'user_id'         => $oUser->user_id,
                    'user_avatar'     => $oUser->user_avatar,
                    'user_level'      => $oUser->user_level,
                    'user_nickname'   => $oUser->user_nickname,
                    'user_vip_level'  => $oUser->user_vip_level,
                    'user_is_member'  => $oUser->user_member_expire_time > time() ? 'Y' : 'N',
                    'is_level_effect' => 'N',
                    'user_sex'        => $oUser->user_sex,
                    'user_birth'      => $oUser->user_birth,
                ],
            ];
        }
        $this->_user  = $row;
        $this->_extra = base64_encode(json_encode($row));
    }


    public function setSignVersion( $value = '' )
    {
        $this->sign_version = $value;
    }

    /**
     * setKey 设置秘钥
     *
     * @param string $value
     */
    public function setKey( $value = '' )
    {
        $this->key = $value;
    }


    /**
     * setIdentifier 管理员用户名
     *
     * @param string $value
     */
    public function setIdentifier( $value = '' )
    {
        $this->identifier = $value;
    }

    /**
     * setAppid 设置APP ID
     *
     * @param string $value
     */
    public function setAppid( $value = '' )
    {
        $this->appId = $value;
    }

    /**
     * setPrivateKey 设置私钥路径
     *
     * @param string $value
     */
    public function setPrivateKey( $value = '' )
    {
        $this->privateKey = $value;
    }

    /**
     * setPublicKey 设置公钥路径
     *
     * @param string $value
     */
    public function setPublicKey( $value = '' )
    {
        $this->publicKey = $value;
    }

    /**
     * setRid 设置群id
     *
     * @param string $value
     */
    public function setRid( $value = '' )
    {
        $this->groupId = $value;
    }

    /**
     * setUid 设置接收者id
     *
     * @param string $value
     */
    public function setUid( $value = '' )
    {
        $this->receiverId = $value;
    }

    /**
     * setAccountId 设置发送者id
     *
     * @param string $value
     */
    public function setAccountId( $value = '' )
    {
        $this->accountId = $value;
    }

    /**
     * genSig 生成usersig
     *
     * @param string $account
     * @return string
     */
    public function genSig( $account )
    {
        if ( $this->sign_version == 'v1' ) {
            $TLSSigAPI = new TLSSigAPI();
            $TLSSigAPI->SetAppid($this->appId);
            $TLSSigAPI->SetPrivateKey($this->privateKey);
            return $TLSSigAPI->genSig($account);
        } else {
            $TLSSigAPIv2 = new TLSSigAPIv2($this->appId, $this->key);
            return $TLSSigAPIv2->genSig($account);
        }

    }

    /**
     * verifySig 验证usersig
     *
     * @param string $sig
     * @param string $account
     * @param string &$init_time
     * @param string &$expire_time
     * @param string &$error_msg
     * @return bool
     */
    public function verifySig( $sig, $account, &$init_time, &$expire_time, &$error_msg )
    {
        $TLSSigAPI = new TLSSigAPI();
        $TLSSigAPI->SetAppid($this->appId);
        $TLSSigAPI->SetPublicKey($this->publicKey);
        return $TLSSigAPI->verifySig($sig, $account, $init_time, $expire_time, $error_msg);
    }

    /**
     * notify 发送通知
     *
     * @param string $sTextContent
     * @return array
     */
    public function notify( $sMsgCode = '', $sMsg = '', $aData = [] )
    {
        if ( !isset(static::$aMsg[ $sMsgCode ]) ) {
            throw new Exception(sprintf("IM SERVER Exception: %s code not exists", $sMsgCode));
        }

        $sTextContent = json_encode([
            'type' => $sMsgCode,
            'msg'  => $sMsg ? $sMsg : static::$aMsg[ $sMsgCode ],
            'data' => (object)$aData,
        ], JSON_UNESCAPED_UNICODE);
        $TimRestAPI   = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $TimRestAPI->set_user_sig($this->genSig($this->identifier));
        if ( $this->receiverId == NULL ) {
            return $TimRestAPI->group_send_group_msg($this->identifier, $this->groupId, $sTextContent);
        }
        return $TimRestAPI->openim_send_msg($this->identifier, $this->receiverId, $sTextContent);
    }

    /**
     * joinRoom 加入群
     *
     * @return array
     */
    public function joinRoom()
    {
        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $TimRestAPI->set_user_sig($this->genSig($this->identifier));
        return $TimRestAPI->group_add_group_member($this->groupId, $this->accountId, 1);
    }

    /**
     * leaveRoom 离开群
     *
     * @return array
     */
    public function leaveRoom()
    {
        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $TimRestAPI->set_user_sig($this->genSig($this->identifier));
        return $TimRestAPI->group_delete_group_member($this->groupId, $this->accountId, 1);
    }

    /**
     * createRoom 创建群组
     *
     * @param string 群组名 $group_name
     * @param string 群类型 $group_type
     * @param 群拥有者 $owner_id
     * @param 群基本信息 $info_set
     * @param 群成员列表 $mem_list
     * @return array
     */
    public function createRoom( $group_name, $group_type = 'AVChatRoom', $mem_list = [] )
    {
        $aInfoSet = [
            'group_id'       => $this->groupId,
            'introduction'   => NULL,
            'notification'   => NULL,
            'face_url'       => NULL,
            'max_member_num' => NULL,
            'apply_join'     => "FreeAccess"
        ];

        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $TimRestAPI->set_user_sig($this->genSig($this->identifier));
        return $TimRestAPI->group_create_group2($group_type, $group_name, $this->accountId, $aInfoSet, $mem_list);
    }

    /**
     * destroyRoom 解散群组
     *
     * @param string $group_id
     * @return array
     */
    public function destroyRoom()
    {
        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $sign = $this->genSig($this->identifier);
        $TimRestAPI->set_user_sig($sign);
        return $TimRestAPI->group_destroy_group($this->groupId);
    }

    /**
     * getRoomInfo 获取群组信息
     */
    public function getRoomInfo()
    {
        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $sign = $this->genSig($this->identifier);
        $TimRestAPI->set_user_sig($sign);
        return $TimRestAPI->group_get_group_info($this->groupId);
    }

    /**
     * 导入账号到Tim
     * @param $identifier 用户名
     * @param $nick 昵称
     * @param $face_url 头像
     */

    public function account_import( $identifier, $nick = '', $face_url = '' )
    {
        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $sign = $this->genSig($this->identifier);
        $TimRestAPI->set_user_sig($sign);
        return $TimRestAPI->account_import($identifier, $nick, $face_url);
    }

    public function querystate( $to_account = [] )
    {
        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $sign = $this->genSig($this->identifier);
        $TimRestAPI->set_user_sig($sign);
        return $TimRestAPI->querystate($to_account);
    }

    public function notify_batch( $sMsgCode, $sMsg, $aData = [] )
    {
        if ( !isset(static::$aMsg[ $sMsgCode ]) ) {
            throw new Exception(sprintf("IM SERVER Exception: %s code not exists", $sMsgCode));
        }

        $sTextContent = json_encode([
            'type' => $sMsgCode,
            'msg'  => $sMsg ? $sMsg : static::$aMsg[ $sMsgCode ],
            'data' => (object)$aData,
        ], JSON_UNESCAPED_UNICODE);

        $TimRestAPI = new TimRestAPI;
        $TimRestAPI->init($this->appId, $this->identifier);
        $TimRestAPI->set_user_sig($this->genSig($this->identifier));
        return $TimRestAPI->openim_batch_sendmsg($this->receiverId, $sTextContent);
    }

    public function genPrivateMapKey( $userId, $roomId )
    {
        $oWebRTCSigApi = new WebRTCSigApi();
        $oWebRTCSigApi->setSdkAppid($this->appId);
        $oWebRTCSigApi->SetPrivateKey($this->privateKey);
        $oWebRTCSigApi->setPublicKey($this->publicKey);
        return $oWebRTCSigApi->genPrivateMapKey($userId, $roomId);
    }
}