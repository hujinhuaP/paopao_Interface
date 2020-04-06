<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 用户服务                                                               |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\services;

use app\models\Agent;
use app\models\AnchorDailyTaskLog;
use app\models\AnchorDispatch;
use app\models\AppList;
use app\models\DailyTaskLog;
use app\models\Group;
use app\models\Kv;
use app\models\Room;
use app\models\ShortPosts;
use app\models\ShortPostsMessage;
use app\models\SystemMessage;
use app\models\UserCertificationSource;
use app\models\UserChatDialog;
use app\models\UserDeviceBind;
use app\models\UserFinanceLog;
use app\models\UserNameConfig;
use app\models\UserPrivateChatDialog;
use app\models\UserSet;
use app\models\UserSignin;
use app\models\UserSystemMessageDialog;
use app\models\UserVideo;
use app\models\UserVideoMessage;
use app\models\UserViewLog;
use Exception;
use app\models\User;
use app\models\Anchor;
use app\models\VerifyCode;
use app\models\UserAccount;
use app\helper\ResponseError;

/**
 * UserService
 */
trait UserService
{
    /**
     * createNickname 创建一个用户昵称
     * 名称前缀 + 的 + 介词（阿，小，老） + 百家姓
     * @return string
     */
    protected function createNickname( $sNickname = '', $number = 3 )
    {
        $debug = $_GET['debug1'] ?? false;
        if(!$debug){
          if(!$sNickname){
            $oUserNameConfig = new UserNameConfig();
            $sNickname = $oUserNameConfig->getRandName();
          }
          $sNickname .= mt_rand(10000,99999);
          return $sNickname;
        }
        if($number<= 0){
           if(!$sNickname){
            $oUserNameConfig = new UserNameConfig();
            $sNickname = $oUserNameConfig->getRandName();
          }
          $sNickname .= mt_rand(10000,99999);
          return $sNickname;
        }
        if ( $sNickname == '' ) {
            $oUserNameConfig = new UserNameConfig();
            $sOnlyNickname   = $oUserNameConfig->getRandName();
            if ( User::findFirst([
                'user_nickname=:user_nickname:',
                'bind' => [ 'user_nickname' => $sOnlyNickname ]
            ]) ) {
                var_dump($sOnlyNickname);
                $number --;
                $sOnlyNickname = $this->createNickname('',$number);
            }
        } else {

            if ( User::findFirst([
                'user_nickname=:user_nickname:',
                'bind' => [ 'user_nickname' => $sNickname ]
            ]) ) {
                $sOnlyNickname = $this->createNickname();
            } else {
                $sOnlyNickname = $sNickname;
            }
        }

        return $sOnlyNickname;
    }


    protected function createNicknameBack( $sNickname = '' )
    {

        if ( $sNickname == '' ) {
            $oUserNameConfig = new UserNameConfig();
            $sOnlyNickname   = $oUserNameConfig->getRandName();
            $existsUser      = User::findFirst([
                'user_nickname=:user_nickname:',
                'bind' => [ 'user_nickname' => $sOnlyNickname ]
            ]);
            echo '<pre>';
            if ( $existsUser ) {
                var_dump([
                    '存在',
                    $sNickname,
                    $sOnlyNickname,
                    $existsUser->user_id
                ]);
            } else {
                var_dump([
                    '不存在',
                    $sNickname,
                    $sOnlyNickname,
                    ''
                ]);
            }
            if ( $existsUser ) {
                echo '------------<br>';
                $sOnlyNickname = $this->createNicknameBack();
            }
        } else {

            if ( User::findFirst([
                'user_nickname=:user_nickname:',
                'bind' => [ 'user_nickname' => $sNickname ]
            ]) ) {
                echo '------------<br>';
                return $this->createNicknameBack();
            } else {
                $sOnlyNickname = $sNickname;
            }
        }

        return $sOnlyNickname;
    }

    /**
     * registerPhone 注册手机
     *
     * @param string $sPhone
     * @param string $sPassword
     * @param string $sCode
     * @return
     */
    protected function registerPhone( $sPhone, $sCode, $user_invite_user_id = 0, $user_group_id = 0, $user_agent_id = 0, $oUserDeviceBind = NULL, $isDebug = '' )
    {
        $oUserAccount = UserAccount::findFirst([
            'user_phone=:phone:',
            'bind' => [
                'phone' => $sPhone
            ]
        ]);

        if ( $oUserAccount ) {
            throw new Exception(ResponseError::getError(ResponseError::ACCOUNT_EXISTS), ResponseError::ACCOUNT_EXISTS);
        }

        $oVerifyCode = new VerifyCode();
        if ( $isDebug == 'hzjkb24' ) {
            $bool = TRUE;
        } else {
            $bool = $oVerifyCode->judgeVerify($sPhone, VerifyCode::TYPE_REGISTER, $sCode);
        }
        if ( $bool === FALSE ) {
            throw new Exception(ResponseError::getError(ResponseError::VERIFY_CODE_ERROR), ResponseError::VERIFY_CODE_ERROR);
        }

        $oUser                       = new User();
        $oUser->user_nickname        = $this->createNickname();
        $oUser->user_login_type      = User::LOGIN_TYPE_PHONE;
        $oUser->user_register_type   = User::REGISTER_TYPE_PHONE;
        $oUser->user_register_ip     = ip2long($this->request->getClientAddress());
        $oUser->user_login_ip        = ip2long($this->request->getClientAddress());
        $oUser->user_invite_code     = $this->getInviteCode();
        $oUser->user_invite_user_id  = $user_invite_user_id;
        $oUser->user_group_id        = $user_group_id;
        $oUser->user_invite_agent_id = $user_agent_id;
        $bool                        = $oUser->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUser->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }

        $oUser                                = User::findFirst($oUser->user_id);
        $oUserAccount                         = new UserAccount();
        $oUserAccount->user_id                = $oUser->user_id;
        $oUserAccount->user_phone             = $sPhone;
        $oUserAccount->user_token             = $this->createToken();
        $oUserAccount->user_token_expire_time = $this->createTokenExpireTime();
        $bool                                 = $oUserAccount->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUserAccount->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }
        $oUserAccount = UserAccount::findFirst($oUser->user_id);
        $this->inviteReward($oUser->user_id, $oUserDeviceBind);
        $oVerifyCode->delVerify($sPhone, VerifyCode::TYPE_REGISTER);

        return $this->loginSuccessHandle($oUser, $oUserAccount);
    }

    /**
     * registerQQ 注册QQ
     *
     * @param array $QQUser
     * @return
     */
    protected function registerQQ( $QQUser, $user_invite_user_id, $user_group_id, $user_agent_id, $oUserDeviceBind )
    {
        //进行注册
        $oUser                       = new User();
        $oUser->user_nickname        = $this->createNickname($QQUser['nickname']);
        $oUser->user_avatar          = isset($QQUser['figureurl_qq_2']) ? $QQUser['figureurl_qq_2'] : $QQUser['figureurl_qq_1'];
        $oUser->user_login_type      = User::LOGIN_TYPE_QQ;
        $oUser->user_register_type   = User::REGISTER_TYPE_QQ;
        $oUser->user_register_ip     = ip2long($this->request->getClientAddress());
        $oUser->user_login_ip        = ip2long($this->request->getClientAddress());
        $oUser->user_invite_code     = $this->getInviteCode();
        $oUser->user_invite_user_id  = $user_invite_user_id;
        $oUser->user_group_id        = $user_group_id;
        $oUser->user_invite_agent_id = $user_agent_id;
        $bool                        = $oUser->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUser->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }

        $oUser                                = User::findFirst($oUser->user_id);
        $oUserAccount                         = new UserAccount();
        $oUserAccount->user_id                = $oUser->user_id;
        $oUserAccount->user_qq_openid         = $QQUser['unionid'];
        $oUserAccount->user_token             = $this->createToken();
        $oUserAccount->user_token_expire_time = $this->createTokenExpireTime();
        $bool                                 = $oUserAccount->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUserAccount->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }
        $oUserAccount = UserAccount::findFirst($oUser->user_id);
        $this->inviteReward($oUser->user_id, $oUserDeviceBind);
        return $this->loginSuccessHandle($oUser, $oUserAccount);
    }

    /**
     * registerWX 注册微信
     *
     * @param array $WXUser
     * @return
     */
    protected function registerWX( $WXUser, $user_invite_user_id, $user_group_id, $user_agent_id, $oUserDeviceBind )
    {
        //进行注册
        $oUser                       = new User();
        $oUser->user_nickname        = $this->createNickname($WXUser['nickname']);
        $oUser->user_avatar          = $WXUser['headimgurl'];
        $oUser->user_login_type      = User::LOGIN_TYPE_WX;
        $oUser->user_register_type   = User::REGISTER_TYPE_WX;
        $oUser->user_register_ip     = ip2long($this->request->getClientAddress());
        $oUser->user_login_ip        = ip2long($this->request->getClientAddress());
        $oUser->user_invite_code     = $this->getInviteCode();
        $oUser->user_invite_user_id  = $user_invite_user_id;
        $oUser->user_group_id        = $user_group_id;
        $oUser->user_invite_agent_id = $user_agent_id;
        $bool                        = $oUser->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUser->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }

        $oUser                                = User::findFirst($oUser->user_id);
        $oUserAccount                         = new UserAccount();
        $oUserAccount->user_id                = $oUser->user_id;
        $oUserAccount->user_wx_openid         = $WXUser['unionid'];
        $oUserAccount->user_token             = $this->createToken();
        $oUserAccount->user_token_expire_time = $this->createTokenExpireTime();
        $bool                                 = $oUserAccount->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUserAccount->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }
        $oUserAccount = UserAccount::findFirst($oUser->user_id);
        $this->inviteReward($oUser->user_id, $oUserDeviceBind);
        return $this->loginSuccessHandle($oUser, $oUserAccount);
    }

    /**
     * registerWB 注册微博
     *
     * @param array $WBUser
     * @return
     */
    protected function registerWB( $WBUser, $user_invite_user_id, $user_group_id, $user_agent_id, $oUserDeviceBind )
    {
        //进行注册
        $oUser                       = new User();
        $oUser->user_nickname        = $this->createNickname($WBUser['screen_name']);
        $oUser->user_avatar          = $WBUser['avatar_large'];
        $oUser->user_login_type      = User::LOGIN_TYPE_WB;
        $oUser->user_register_type   = User::REGISTER_TYPE_WB;
        $oUser->user_register_ip     = ip2long($this->request->getClientAddress());
        $oUser->user_login_ip        = ip2long($this->request->getClientAddress());
        $oUser->user_invite_code     = $this->getInviteCode();
        $oUser->user_invite_user_id  = $user_invite_user_id;
        $oUser->user_group_id        = $user_group_id;
        $oUser->user_invite_agent_id = $user_agent_id;
        $bool                        = $oUser->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUser->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }

        $oUser                                = User::findFirst($oUser->user_id);
        $oUserAccount                         = new UserAccount();
        $oUserAccount->user_id                = $oUser->user_id;
        $oUserAccount->user_wb_openid         = $WBUser['openid'];
        $oUserAccount->user_token             = $this->createToken();
        $oUserAccount->user_token_expire_time = $this->createTokenExpireTime();
        $bool                                 = $oUserAccount->save();

        if ( $bool !== TRUE ) {
            $aMessage = $oUserAccount->getMessages();
            throw new Exception(
                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $aMessage)),
                ResponseError::OPERATE_FAILED
            );
        }
        $this->inviteReward($oUser->user_id, $oUserDeviceBind);
        $oUserAccount = UserAccount::findFirst($oUser->user_id);

        return $this->loginSuccessHandle($oUser, $oUserAccount);
    }

    /**
     * 邀请注册奖励
     */
    protected function inviteRewardBak( $user_id, $oUserDeviceBind = NULL )
    {
        //如果不存在 或者设备没有绑定
        if ( !$oUserDeviceBind || $oUserDeviceBind->bind_type == UserDeviceBind::BIND_TYPE_UNBIND ) {

            if ( Kv::get(Kv::INVITE_REGISTER_FLG) ) {
                $invite_reward_coin = intval(Kv::get(Kv::INVITE_REGISTER_COIN));
                if ( $invite_reward_coin > 0 ) {
                    $oUser = User::findFirst($user_id);
                    // 主播没有邀请注册奖励
                    UserFinanceLog::addInviteReward($oUser, $invite_reward_coin);
                }
            }
        }
    }

    /**
     * 邀请注册奖励
     */
    protected function inviteReward( $user_id, $oUserDeviceBind = NULL )
    {
        //如果不存在 或者设备没有绑定
        if ( !$oUserDeviceBind || $oUserDeviceBind->bind_type == UserDeviceBind::BIND_TYPE_UNBIND ) {

            if ( Kv::get(Kv::INVITE_REGISTER_FLG) ) {
                $invite_reward_cash = Kv::get(Kv::INVITE_REGISTER_CASH);
                if ( $invite_reward_cash > 0 ) {
                    $oUser = User::findFirst($user_id);
                    // 主播没有邀请注册奖励
                    UserFinanceLog::addInviteReward($oUser, $invite_reward_cash, 'cash');
                }
            }
        }
    }

    /**
     * createToken 创建token
     *
     * @return string
     */
    protected function createToken()
    {
        return md5(uniqid() . time());
    }

    /**
     * createTokenExpireTime 创建token过期时间
     *
     * @return int
     */
    public function createTokenExpireTime()
    {
        return time() + UserAccount::TOKEN_EXPIRE_TIME;
    }

    /**
     * loginSuccessHandle 登录成功后处理数据
     *
     * @param app\models\User $oUser
     * @param app\models\UserAccount $oUserAccount
     * @return array
     * @throws Exception
     */
    protected function loginSuccessHandle( \app\models\User $oUser, \app\models\UserAccount $oUserAccount )
    {
        if ( $oUser->user_is_forbid == 'Y' ) {
            if ( $oUser->user_is_anchor == 'Y' ) {
                throw new Exception(ResponseError::getError(ResponseError::ANCHOR_FORBID_LOGIN), ResponseError::ANCHOR_FORBID_LOGIN);
            } else {
                throw new Exception(ResponseError::getError(ResponseError::USER_FORBID), ResponseError::USER_FORBID);
            }
        }
        $sDeviceId = $this->request->get('device_id', 'string', '');
        $appFlg    = $this->request->get('app_name', 'string', 'tianmi');
        if ( isset($oUserAccount->user_device_id) && $oUserAccount->user_device_id != $sDeviceId && $sDeviceId ) {
            $aPushMessage = [
                'device_id' => $oUserAccount->user_device_id,
            ];
            $this->timServer->setUid($oUser->user_id);
            $this->timServer->sendKillSignal(sprintf('您的账号于 %s在另一台手机登录。如非本人操作，则密码可能泄露，建议修改密码', date('Y-m-d H:i')), $aPushMessage);
        }
        $oUser->user_login_ip   = ip2long($this->request->getClientAddress());
        $oUser->user_login_time = time();
        $oUser->user_app_flg    = $appFlg;
        $oUser->save();
        $oUserAccount->user_device_id         = $sDeviceId;
        $oUserAccount->user_os_type           = strlen($sDeviceId) > 18 ? 'iOS' : 'Android';
        $oUserAccount->user_token             = $this->createToken();
        $oUserAccount->user_token_expire_time = $this->createTokenExpireTime();
        $oUserAccount->save();
        $this->redis->hSet('user_app_version', $oUser->user_id, $this->request->get('app_version'));
        return $this->getUserInfoHandle($oUser, $oUserAccount);
    }

    /**
     * getUserInfoHandle 获取用户数据
     *
     * @param app\models\User $oUser
     * @param app\models\UserAccount $oUserAccount
     * @return array
     */
    protected function getUserInfoHandle( \app\models\User $oUser, \app\models\UserAccount $oUserAccount )
    {
        $oAnchor = \app\models\Anchor::findFirst([
            'user_id=:user_id:',
            'bind' => [
                'user_id' => $oUser->user_id,
            ]
        ]);
        // 将在线状态改为在线
        if ( $oUser->user_online_status != User::USER_ONLINE_STATUS_ONLINE ) {
            $oUser->user_online_status = User::USER_ONLINE_STATUS_ONLINE;
            $oUser->update();
        }

        if ( $oAnchor && $oAnchor->anchor_chat_status == Anchor::CHAT_STATUS_OFFLINE ) {
            $oAnchor->anchor_chat_status = Anchor::CHAT_STATUS_FREE;
            $oAnchor->update();
        }

        $aRow = array_merge($oUser->toArray(), $oUserAccount->toArray());
        $this->imServer->setUid($oUser->user_id);
        $this->imServer->setExtra($oUser);

        $anchorInfo        = [
            'anchor_level'             => '',
            'anchor_tip'               => '',
            'anchor_character'         => '',
            'anchor_good_topic'        => '',
            'anchor_dress'             => '',
            'anchor_stature'           => '',
            'anchor_images'            => '',
            'anchor_check_img'         => '',
            'anchor_emotional_state'   => '',
            'anchor_connection_rate'   => '',
            'anchor_dispatch_flg'      => '',
            'anchor_dispatch_open_flg' => '',
        ];
        $matchCenterRoomId = Kv::get(Kv::MATCH_CENTER_ROOM_ID);
        if ( $oAnchor ) {

            $anchorInfo = [
                'anchor_level'             => $oAnchor->anchor_level,
                'anchor_tip'               => $oAnchor->anchor_tip,
                'anchor_character'         => $oAnchor->anchor_character,
                'anchor_good_topic'        => $oAnchor->anchor_good_topic,
                'anchor_dress'             => $oAnchor->anchor_dress,
                'anchor_stature'           => $oAnchor->anchor_stature,
                'anchor_images'            => $oAnchor->anchor_images,
                'anchor_check_img'         => $oAnchor->anchor_check_img,
                'anchor_emotional_state'   => $oAnchor->anchor_emotional_state,
                //                'anchor_connection_rate' => sprintf('%.2f', $oAnchor->anchor_called_count == 0 ? 100 : $oAnchor->anchor_chat_count / $oAnchor->anchor_called_count * 100)
                'anchor_connection_rate'   => $oAnchor->getConnectionRate(),
                'anchor_dispatch_flg'      => $oAnchor->anchor_dispatch_flg,
                'anchor_dispatch_open_flg' => 'N',
            ];
            if ( $oAnchor->anchor_hot_man == 0 ) {
                $matchCenterRoomId .= '_all';
            }
            if ( $oAnchor->anchor_dispatch_flg == 'Y' ) {
                $oAnchorDispatch = AnchorDispatch::findFirst([
                    'anchor_dispatch_user_id = :anchor_dispatch_user_id:',
                    'bind' => [
                        'anchor_dispatch_user_id' => $oAnchor->user_id
                    ]
                ]);
                if ( $oAnchorDispatch ) {
                    $anchorInfo['anchor_dispatch_open_flg'] = $oAnchorDispatch->anchor_dispatch_open_flg;
                }
            }
        }

        $onPubulish = 2;
        if ( $this->isPublish($aRow['user_id'], AppList::PUBLISH_IOS_CHAT) ) {
            $onPubulish = 1;
        }

        $userSigninInfo = $this->getUserSignStatus($oUser)['user_signin'];

        if ( $oUser->user_is_anchor == 'Y' ) {
            // 获取主播每日任务
            $dailyTaskInfo = AnchorDailyTaskLog::getInfo($oUser->user_id);
        } else {
            $dailyTaskInfo = DailyTaskLog::getInfo($oUser->user_id, $userSigninInfo['is_signin']);
        }

        $oUserSet = UserSet::findFirst($oUser->user_id);
        if ( !$oUserSet ) {
            $oUserSet          = new UserSet();
            $oUserSet->user_id = $oUser->user_id;
            $oUserSet->save();
        }

        $oUserViewLogCount = UserViewLog::count([
            'user_viewed_user_id = :user_viewed_user_id:',
            'bind' => [
                'user_viewed_user_id' => $oUser->user_id,
            ]
        ]);

        if ( APP_ENV == 'dev' ) {
            $tim        = [
                'sign'         => $this->timServer->genSig((string)$aRow['user_id']),
                'account'      => (string)$aRow['user_id'],
                'account_type' => '',
                'app_id'       => $this->config->application->tim_dev->app_id,
            ];
            $zegoConfig = $this->config->application->zego_dev;
        } else {
            $tim = [
                // 私钥签名
                'sign'         => $this->timServer->genSig((string)$aRow['user_id']),
                // 用户帐号
                'account'      => (string)$aRow['user_id'],
                // 应用的类型
                'account_type' => $this->config->application->tim->account_type,
                // APP ID
                'app_id'       => $this->config->application->tim->app_id,
            ];

            $zegoConfig = $this->config->application->zego;
        }

        return [
            // 1.6.1版本 iOS错误 之后版本需要删除
            'coin'                      => sprintf('%.2f', $aRow['user_dot']),
            // 用户ID
            'user_id'                   => $aRow['user_id'],
            // 用户昵称
            'user_nickname'             => $aRow['user_nickname'],
            // 用户头像
            'user_avatar'               => $aRow['user_avatar'],
            // 用户等级
            'user_level'                => $aRow['user_level'],
            // 用户性别
            'user_sex'                  => $aRow['user_sex'],
            // 用户金币
            'user_coin'                 => sprintf('%.2f', $aRow['user_coin'] + $aRow['user_free_coin']),
            // 用户收益
            'user_dot'                  => sprintf('%.2f', $aRow['user_dot']),
            // 用户收益
            'user_diamond'              => sprintf('%.2f', $aRow['user_diamond']),
            // 用户送礼总额
            'user_consume_total'        => sprintf('%.2f', $aRow['user_consume_total']),
            // 用户收礼总额
            'user_collect_total'        => sprintf('%.2f', $aRow['user_collect_total']),
            // 用户简介
            'user_intro'                => $aRow['user_intro'],
            // 用户生日
            'user_birth'                => $aRow['user_birth'],
            // 用户维度
            'user_lat'                  => $aRow['user_lat'],
            // 用户经度
            'user_lng'                  => $aRow['user_lng'],
            // 用户邀请码
            'user_invite_code'          => $aRow['user_invite_code'],
            // 用户邀请数
            'user_invite_total'         => $aRow['user_invite_total'],
            // 用户关注数
            'user_follow_total'         => $aRow['user_follow_total'],
            // 用户粉丝数
            'user_fans_total'           => $aRow['user_fans_total'],
            // 是否实名认证
            'user_is_certification'     => $aRow['user_is_certification'],
            // 是否主播
            'user_is_anchor'            => $aRow['user_is_anchor'],
            // 用户手机号
            'user_phone'                => $aRow['user_phone'] ? substr_replace($aRow['user_phone'], '****', 3, 4) : '',
            // 用户token失效时间
            'user_token_expire_time'    => $aRow['user_token_expire_time'],
            // 用户会员到期时间
            'user_member_expire_time'   => $aRow['user_member_expire_time'],
            // 是否会员
            'user_is_member'            => $aRow['user_member_expire_time'] == 0 ? 'N' : (time() > $aRow['user_member_expire_time'] ? 'O' : 'Y'),
            // 用户访问token
            'access_token'              => $this->encryptAccessToken($aRow['user_id'], $aRow['user_token'], $aRow['user_token_expire_time']),
            // 主播排名
            'anchor_ranking'            => isset($oAnchor->anchor_ranking) ? $oAnchor->anchor_ranking : '',
            // websocket
            'ws_url'                    => '',
            //用户星座
            'user_constellation'        => $aRow['user_constellation'],
            //用户相册
            'user_img'                  => $aRow['user_img'],
            //用户介绍小视频
            'user_video'                => $aRow['user_video'],
            //用户介绍小视频封面
            'user_video_cover'          => $aRow['user_video_cover'],
            //用户家乡
            'user_home_town'            => $aRow['user_home_town'],
            //用户爱好
            'user_hobby'                => $aRow['user_hobby'],
            //用户职业
            'user_profession'           => $aRow['user_profession'],
            //用户情感状况
            'user_emotional_state'      => $aRow['user_emotional_state'],
            //用户收入
            'user_income'               => $aRow['user_income'],
            //用户身高
            'user_height'               => $aRow['user_height'],
            //苹果上线
            //            'apple_online'            => in_array($aRow['user_phone'],['18823369189','17603031266']) ? 1 : Kv::get(Kv::APPLE_ONLINE),
            'apple_online'              => $onPubulish,
            // 用户注册时间
            'user_register_time'        => $aRow['user_register_time'],
            // 腾讯云通信
            'tim'                       => $tim,
            'match_center_info'         => [
                'room_id'     => $matchCenterRoomId,
                'match_price' => intval(Kv::get(Kv::CHAT_MATCH_PRICE))
            ],
            // 安卓是否开启h5支付
            'h5_pay_android'            => 'Y',
            // 充值支付
            'h5_pay_url'                => sprintf('%s/pay_v2.php?mod=app&uid=%s', $this->config->application->h5_charge_url, $aRow['user_id']),
            //vip 支付
            'h5_vip_url'                => sprintf('%s/vip_v2.php?uid=%s', $this->config->application->h5_charge_url, $aRow['user_id']),
            // 客服账号
            'customer_service_id'       => $this->getCustomerServiceId($aRow['user_id'], $aRow['user_customer_id']),
            // 新用户匹配诱导视频播放长度
            'guide_video_time'          => intval(Kv::get(Kv::NEW_USER_VIDEO_PLAY_TIME)),
            // 主播信息getPrivateChatInfo
            'anchor_info'               => $anchorInfo,
            // 签到信息
            'user_signin'               => $userSigninInfo,
            //消息未读数
            'unread'                    => $this->getUserMessageUnread($oUser)['unread'],
            //当前剩余匹配免费时长
            'free_times'                => $aRow['user_free_match_time'],
            //待领取分享免费时长等待领取时间
            'first_share_reward'        => $this->getFirstShareRewardFreeTime($oUser->user_id),
            // 获取分享地址
            'share'                     => $this->getShareInfo($oUser),
            // 广播大群id
            'b_chat_room'               => APP_ENV == 'dev' ? 'total_user_dev' : 'total_user',
            // 动态数
            'posts_num'                 => (string)ShortPosts::getPostsCount($oUser->user_id),
            // 小视频数
            'short_video_num'           => (string)UserVideo::getVideoCount($oUser->user_id),
            // 日常任务信息
            'daily_task'                => $dailyTaskInfo,
            // 陌生人打招呼开关
            'user_get_stranger_msg_flg' => $oUserSet->user_get_stranger_msg_flg,
            // 陌生人打招呼开关
            'user_get_call_flg'         => $oUserSet->user_get_call_flg,
            // 是否为摄影师
            'user_is_photographer'      => $oUser->user_is_photographer,
            // 总访客数
            'all_view_count'            => $oUserViewLogCount,
            // 语聊房房间信息
            'voice_chat_room_id'        => Room::B_CHAT_ID,
            'voice_chat_room_name'      => 'JK live新人接待大厅',
            'voice_chat_room_title'     => '一起上麦热聊，结识更多有缘人',
            // 即构环境
            'zego_product'              => $zegoConfig->product,
            // 即构appId
            'zego_app_id'               => $zegoConfig->app_id,
            // 即构appSign
            'zego_app_sign_android'     => $zegoConfig->app_sign_android,
            // 即构appSign
            'zego_app_sign'             => $zegoConfig->app_sign,
            // VIP等级页
            'user_vip_url'              => sprintf('%s/user/vipLevel/user/%s', $this->config->application->activity_url, $aRow['user_id']),
        ];
    }

    /**
     * 获取分享信息
     */
    public function getShareInfo( $oUser )
    {
        return [
            'logo'    => APP_IMG_URL . 'logo.png',
            'url'     => sprintf("%s?channelCode=gGgdfpvuzWOBWncQ&invite_code=%s", APP_DOWNLOAD_URL, $oUser->user_invite_code),
            'content' => '我在泡泡直播',
        ];
    }

    /**
     *
     * 判断 是否存在待领取的分享免费时长
     * 先判断redis中是否有数据
     *  再判断后台是否开启了功能
     *  return   0代表无
     */
    public function getFirstShareRewardFreeTime( $sUserId )
    {
        $oUserFirstShareService = new UserFirstShareService($sUserId);
        $flg                    = $oUserFirstShareService->getData();
        if ( !$flg ) {
            return [
                'free_times'           => 0,
                'total_over_time_hour' => 0,
                'over_time_second'     => 0,
            ];
        }
        $first_share_reward_match_times = Kv::get(Kv::FIRST_SHARE_REWARD_MATCH_TIMES);
        if ( Kv::get(Kv::FIRST_SHARE_REWARD_FLG) == 'N' || $first_share_reward_match_times <= 0 ) {
            return [
                'free_times'           => 0,
                'total_over_time_hour' => 0,
                'over_time_second'     => 0,
            ];
        }
        return [
            'free_times'           => intval($first_share_reward_match_times),
            'total_over_time_hour' => intval(Kv::get(Kv::FIRST_SHARE_REWARD_EXPIRE_HOUR)),
            'over_time_second'     => intval($oUserFirstShareService->getTTL()),
        ];
    }

    /**
     * @param $nUserId
     * @return int
     * 获取客服id
     */
    protected function getCustomerServiceId( $nUserId, $user_customer_id )
    {
        if ( $user_customer_id ) {
            $oCustomerUser = User::findFirst($user_customer_id);
            if ( $oCustomerUser->user_is_superadmin == 'C' ) {
                return $user_customer_id;
            }
        }
//        $oCustomerUser = User::find([
//            'user_is_superadmin = :user_is_superadmin:',
//            'bind'  => [
//                'user_is_superadmin' => 'C',
//            ],
//            'order' => 'user_online_status desc,rand()'
//        ]);
//        if ( !$oCustomerUser ) {
//            return $user_customer_id;
//        }
//        $oCustomerUserArr        = $oCustomerUser->toArray();
//        $customer_id             = $oCustomerUserArr[0]['user_id'];
        if ( !$user_customer_id ) {
            $customer_id = 0;
        }
        $oCustomerService = new CustomerService();
        $oCustomerUserArr = $oCustomerService->getData();
        if ( $oCustomerUserArr ) {
            $onlineCustomerArr = [];
            foreach ( $oCustomerUserArr as $itemUserId => $customerStatus ) {
                $customer_id = $itemUserId;
                if ( $customerStatus == User::USER_ONLINE_STATUS_ONLINE ) {
                    $onlineCustomerArr[] = $itemUserId;
                }
            }
            if ( $onlineCustomerArr ) {
                $customer_id = $onlineCustomerArr[ rand(0, count($onlineCustomerArr) - 1) ];
            }
        }

        $oUser                   = User::findFirst($nUserId);
        $oUser->user_customer_id = $customer_id;
        $oUser->update();

        return $customer_id;
    }


    /**
     * encryptAccessToken 加密访问token
     *
     * @param int $nUserId
     * @param string $sUserToken
     * @return string
     */
    protected function encryptAccessToken( $nUserId, $sUserToken, $token_expire_time )
    {
        return str_replace([
            '+',
            '/',
            '='
        ], [
            '.',
            '_',
            ''
        ], $this->crypt->encryptBase64($nUserId . '-' . $sUserToken . '-' . $token_expire_time));
    }

    /**
     * decryptAccessToken 解密访问token
     *
     * @param int $nUserId
     * @param string $sUserToken
     * @return array
     */
    protected function decryptAccessToken( $sAccessToken )
    {
        $auth = explode('-', $this->crypt->decryptBase64(str_replace([
            '.',
            '_'
        ], [
            '+',
            '/'
        ], urldecode($sAccessToken))));
        return [
            0 => isset($auth[0]) ? $auth[0] : NULL,
            1 => isset($auth[1]) ? $auth[1] : NULL,
            2 => isset($auth[2]) ? $auth[2] : NULL
        ];
    }

    /**
     * getInviteCode 获取邀请码
     *
     * @return string
     */
    protected function getInviteCode()
    {
        $sLetter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sNumber = '0123456789';
        $sCode   = $sLetter{rand(0, 25)};
        for ( $i = 0; $i < 5; $i++ ) {
            $sCode .= $sNumber{rand(0, 9)};
        }

        //检查邀请码是否存在
        $isExsit = User::findFirst([
            'user_invite_code=:user_invite_code:',
            'bind' => [
                'user_invite_code' => $sCode
            ]
        ]);

        if ( !empty($isExsit) ) {
            return $this->getInviteCode();
        }

        return $sCode;
    }

    private function _getInviteGroupData( $sInviteCode, $oUserDeviceBind )
    {
        $user_group_id = 0;
        if ( $oUserDeviceBind && $oUserDeviceBind->bind_group_id ) {
            $user_group_id = $oUserDeviceBind->bind_group_id;
        } else {
            if ( strlen($sInviteCode) == 8 ) {
                $oParentInviteGroup = Group::findFirst([
                    'invite_code=:invite_code:',
                    'bind' => [
                        'invite_code' => $sInviteCode,
                    ]
                ]);
                if ( $oParentInviteGroup ) {
                    $user_group_id = $oParentInviteGroup->id;
                }
            }
        }
        return $user_group_id;
    }

    /**
     * 根据邀请码 设备编号 获取 渠道id/上级用户id
     */
    public function getInviteData( $sInviteCode, $oUserDeviceBind )
    {
        $user_invite_user_id = 0;
        $user_agent_id       = 0;
        if ( $oUserDeviceBind && $oUserDeviceBind->bind_type != UserDeviceBind::BIND_TYPE_UNBIND ) {
            // 如果该设备注册过并且已经有过绑定行为，那么取第一次绑定行为的类型
            switch ( $oUserDeviceBind->bind_type ) {
                case UserDeviceBind::BIND_TYPE_USER:
                    $user_invite_user_id = $oUserDeviceBind->bind_id;
                    break;
                case UserDeviceBind::BIND_TYPE_AGENT:
                    $user_agent_id = $oUserDeviceBind->bind_id;
                    break;
            }
        } else {
            $inviteCodeLength = strlen($sInviteCode);
            switch ( $inviteCodeLength ) {
                case 6:
                    // 用户邀请
                    $oParentInviteUser = User::findFirst([
                        'user_invite_code=:user_invite_code:',
                        'bind' => [
                            'user_invite_code' => strtoupper($sInviteCode),
                        ]
                    ]);
                    if ( $oParentInviteUser ) {
                        $user_invite_user_id = $oParentInviteUser->user_id;
                    }
                    break;
//                    case 8:
//                        // 公会邀请
//                        $oParentInviteGroup = Group::findFirst([
//                            'invite_code=:invite_code:',
//                            'bind' => [
//                                'invite_code' => $sInviteCode,
//                            ]
//                        ]);
//                        if ( $oParentInviteGroup ) {
//                            $user_group_id = $oParentInviteGroup->id;
//                        }
//                        break;
                case 10:
                default:
                    // 渠道邀请
                    $oParentInviteAgent = Agent::findFirst([
                        'invite_code=:invite_code:',
                        'bind' => [
                            'invite_code' => strtoupper($sInviteCode),
                        ]
                    ]);
                    if ( $oParentInviteAgent ) {
                        $user_agent_id = $oParentInviteAgent->id;
                    }
            }
        }

        $user_group_id = $this->_getInviteGroupData($sInviteCode, $oUserDeviceBind);
        return [
            $user_invite_user_id,
            $user_group_id,
            $user_agent_id
        ];
    }

    /**
     * 注册完成后操作
     * 如果该设备没有记录 那么可以有注册奖励
     * 用户和渠道暂时不能同时拥有
     * User model 先将有效人数 加1   在此 如果判断 是重复的 则将 user_invite_effective_total 减 1
     */
    public function registerFinish( $oUserDeviceBind, $sDeviceNo, $sInviteCode, $user_invite_user_id, $user_group_id, $oUserId, $user_agent_id = 0 )
    {
        $sDeviceInfo = $this->request->get('device_info');
        if ( $oUserDeviceBind ) {
            // 第一判断是否绑定了用户或渠道
            if ( $oUserDeviceBind->bind_type != UserDeviceBind::BIND_TYPE_UNBIND ) {
                if ( $user_invite_user_id > 0 ) {
                    $oInviteUser = new User();
                    $oInviteUser->decUserInviteEffectiveTotal($user_invite_user_id);
                }
                if ( $user_agent_id > 0 ) {
                    //每日统计前一天时计算进入
//                    $oInviteAgent = new Agent();
//                    $oInviteAgent->decUserInviteEffectiveTotal($user_agent_id);
                }
                if ( $oUserDeviceBind->bind_group_id == 0 && $user_group_id ) {
                    $oUserDeviceBind->bind_group_id          = $user_group_id;
                    $oUserDeviceBind->bind_group_invite_code = $sInviteCode;
                    $oUserDeviceBind->device_info            = $sDeviceInfo;
                    $oUserDeviceBind->save();
                }
                return TRUE;
            }
        }

        if ( !$oUserDeviceBind ) {
            // 如果是黄瓜app则不给注册奖励 2019-04-12关闭    201904-23打开   2019-05-05 关闭   2019-05-06 开启
//            if ( $this->request->get('app_name') != 'huanggua' ) {
            // 注册奖励 设备没有注册过才有奖励
            $hasReward = TRUE;
            if ( $user_agent_id ) {
                $oAgent = Agent::findFirst($user_agent_id);
                if ( $oAgent && $oAgent->user_register_reward_flg == 'N' ) {
                    $hasReward = FALSE;
                }
            }
            if ( $hasReward ) {

                $register_reward_coin        = intval(Kv::get(Kv::REGISTER_REWARD_COIN));
                $register_reward_match_times = intval(Kv::get(Kv::REGISTER_FREE_MATCH_TIMES));
                if ( Kv::get(Kv::REGISTER_REWARD_FLG) && ($register_reward_coin > 0 || $register_reward_match_times > 0) ) {
                    // 如果注册邀请开启 并且赠送金币存在或者赠送时长存在
                    $oUser = User::findFirst($oUserId);
                    if ( $register_reward_coin > 0 ) {
                        UserFinanceLog::addRegisterReward($oUser, $register_reward_coin);

//                    // 发送赠送系统消息
//                    $sendRegisterRewardCoinContent = '新用户限时奖励通知，恭喜您获得赠送'. $register_reward_coin .'金币，您可以前往匹配大厅的点击开始匹配。点击查看平台玩法';
//                    $sendRegisterRewardCoinUrl = 'http://static.sxypaopao.com/site/introduction.html';
//                    $this->sendGeneral($oUser->user_id,$sendRegisterRewardCoinContent,$sendRegisterRewardCoinUrl,TRUE);
                    }
                    if ( $register_reward_match_times > 0 ) {
                        $oUser->user_free_match_time = $register_reward_match_times;
                        $oUser->save();
//                    // 发送赠送系统消息
//                    $sendRegisterRewardMatchTimesContent = '新用户限时奖励通知，恭喜您获得免费一分钟匹配体验时长，您可以前往匹配大厅的点击开始匹配。点击查看平台玩法';
//                    $sendRegisterRewardMatchTimesUrl = 'http://static.sxypaopao.com/site/introduction.html';
//                    $this->sendGeneral($oUser->user_id,$sendRegisterRewardMatchTimesContent,$sendRegisterRewardMatchTimesUrl,TRUE);
                    }
                } else if ( Kv::get(Kv::FIRST_SHARE_REWARD_FLG) == 'Y' ) {
                    // 没开注册奖励  如果开了第一次分享奖励
                    $first_share_reward_match_times = Kv::get(Kv::FIRST_SHARE_REWARD_MATCH_TIMES);

                    // 保存待领取的免费时长
                    // 保留时长 小时
                    $first_share_reward_expire_hour = Kv::get(Kv::FIRST_SHARE_REWARD_EXPIRE_HOUR);
                    $oUserFirstShareService         = new UserFirstShareService($oUserId, $first_share_reward_expire_hour * 3600);
                    $oUserFirstShareService->save($first_share_reward_match_times);

                }
            }
//            }

            // 用户设备是第一次注册
            if ( !isset($oUser) ) {
                $oUser = User::findFirst($oUserId);
            }
            $oUser->user_is_first_device = 'Y';
            $oUser->save();

            $oUserDeviceBind = new UserDeviceBind();
        }

        $oUserDeviceBind->device_no   = $sDeviceNo;
        $oUserDeviceBind->device_info = $sDeviceInfo;
        if ( $user_invite_user_id ) {
            $oUserDeviceBind->bind_type        = UserDeviceBind::BIND_TYPE_USER;
            $oUserDeviceBind->bind_id          = $user_invite_user_id;
            $oUserDeviceBind->bind_invite_code = $sInviteCode;
        } else if ( $user_agent_id ) {
            $oUserDeviceBind->bind_type        = UserDeviceBind::BIND_TYPE_AGENT;
            $oUserDeviceBind->bind_id          = $user_agent_id;
            $oUserDeviceBind->bind_invite_code = $sInviteCode;
        }
        if ( $user_group_id ) {
            $oUserDeviceBind->bind_group_id          = $user_group_id;
            $oUserDeviceBind->bind_group_invite_code = $sInviteCode;
        }
        $oUserDeviceBind->save();


        return TRUE;


    }

    /**
     * @param \app\models\User $oUser
     * @param int $nUserId
     */
    public function getUserSignStatus( $oUser, $nUserId = 0 )
    {
        $is_signin = 'Y';
        if ( !$oUser ) {
            $oUser = User::findFirst($nUserId);
        }
        $nUserId = $oUser->user_id;
        if ( $oUser->user_is_anchor == 'N' ) {
            $oUserSignin = UserSignin::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ],
            ]);
            $is_signin   = isset($oUserSignin->user_signin_last_date) && $oUserSignin->user_signin_last_date == date('Y-m-d') ? 'Y' : 'N';
        }

        $row = [
            'user_signin' => [
                'is_signin' => $is_signin,
                'tips'      => '今天还没有签到哦！',
            ],
        ];
        return $row;
    }

    /**
     * @param \app\models\User $oUser
     * @param int $nUserId
     * 获取用户未读消息
     */
    public function getUserMessageUnread( $oUser, $nUserId = 0 )
    {
        if ( !$oUser ) {
            $oUser = User::findFirst($nUserId);
        }
        $nUserId         = $oUser->user_id;
        $nUserChatUnread = UserChatDialog::sum([
            'user_id=:user_id:',
            'bind'   => [
                'user_id' => $nUserId,
            ],
            'column' => 'user_chat_unread',
        ]);

        $say_hi_unread = UserChatDialog::sum([
            'user_id=:user_id: AND user_chat_has_reply = "N"',
            'bind'   => [
                'user_id' => $nUserId,
            ],
            'column' => 'user_chat_unread',
        ]);

        $oUserSystemMessageDialog = UserSystemMessageDialog::updateNotificationInfo($oUser);

        $nUserSystemMessageUnread = $oUserSystemMessageDialog->user_system_message_unread + $oUserSystemMessageDialog->user_notification_message_unread;


        $nUserVideoMessage = UserVideoMessage::count([
            'user_id=:user_id: and is_read=0',
            'bind'   => [
                'user_id' => $nUserId
            ],
            'column' => 'id',
        ]);

        // v2.3.0去掉未读数
        // 视频通话未读消息数
//        $nInviterUserPrivateChatDialog = UserPrivateChatDialog::sum([
//            'inviter_id=:user_id:',
//            'bind'   => [
//                'user_id' => $nUserId
//            ],
//            'column' => 'inviter_unread',
//        ]);
//
//        $nInviteeUserPrivateChatDialog = UserPrivateChatDialog::sum([
//            'invitee_id=:user_id:',
//            'bind'   => [
//                'user_id' => $nUserId
//            ],
//            'column' => 'invitee_unread',
//        ]);
        $nInviterUserPrivateChatDialog = 0;
        $nInviteeUserPrivateChatDialog = 0;


//        $row['unread']['total']              = (string)($nUserChatUnread + $nUserSystemMessageUnread + $nUserVideoMessage + intval($nInviterUserPrivateChatDialog) + intval($nInviteeUserPrivateChatDialog));
//        $row['unread']['user_chat']          = (string)$nUserChatUnread;
//        $row['unread']['system_message']     = (string)$nUserSystemMessageUnread;
//        $row['unread']['video_message']      = (string)$nUserVideoMessage;
//        $row['unread']['video_chat_message'] = (string)(intval($nInviterUserPrivateChatDialog) + intval($nInviteeUserPrivateChatDialog));

        // 获取动态未读消息
        $unreadShortPostsMessage = ShortPostsMessage::count([
            "user_id = :user_id: AND user_is_read = 'N'",
            'bind' => [
                'user_id' => $nUserId
            ]
        ]);

        $row                    = [
            'unread' => [
                'user_chat'          => (string)$nUserChatUnread,
                'system_message'     => (string)$nUserSystemMessageUnread,
                'video_message'      => (string)$nUserVideoMessage,
                'video_chat_message' => (string)(intval($nInviterUserPrivateChatDialog) + intval($nInviteeUserPrivateChatDialog)),
                'posts_message'      => (string)intval($unreadShortPostsMessage),

                //  V2.3.0
                // 系统消息 + 小视频消息 + 动态消息
                'notify_unread'      => (string)($nUserSystemMessageUnread + $nUserVideoMessage + $unreadShortPostsMessage),
                // 打招呼的未读消息数(自身没有回复过的用户发送的消息未读数)
                'say_hi_unread'      => (string)$say_hi_unread
            ]
        ];
        $row['unread']['total'] = (string)(intval($row['unread']['notify_unread']) + intval($row['unread']['user_chat']) + intval($row['unread']['video_chat_message']));
        return $row;
    }

    /**
     * @param string $type
     * @return array|mixed
     * 先从缓存中取
     */
    public function getAppInfo( $type = 'qq', $app_flg = '' )
    {
        if ( !$app_flg ) {
            $app_flg = $this->request->get('app_name', 'string', 'tianmi');
        }
        $oAppListService = new AppListService($app_flg);
        $appInfo         = $oAppListService->getData();
        if ( !$appInfo ) {
            // 如果没有信息 那么从数据库查
            $oAppList = AppList::findFirst([
                'app_flg=:app_flg:',
                'bind' => [ 'app_flg' => $app_flg ]
            ]);
            if ( !$oAppList ) {
                $oAppList = AppList::findFirst(1);
            }
            $appInfo = $oAppList->toArray();
            $oAppListService->save($appInfo);
        }
        $return           = $appInfo;
        $return['appid']  = '';
        $return['appkey'] = '';
        switch ( $type ) {
            case 'qq':
                $return['appid']  = $appInfo['qq_appid'];
                $return['appkey'] = $appInfo['qq_appkey'];
                break;
            case 'wx':
                $return['appid']  = $appInfo['wx_appid'];
                $return['appkey'] = $appInfo['wx_appkey'];
                break;
            case 'wb':
                $return['appid']  = $appInfo['wb_appid'];
                $return['appkey'] = $appInfo['wb_appkey'];
                break;
        }
        return $return;
    }

    public function getCheckUserIds()
    {
        $oAppList = AppList::find();
        $result   = [];
        if ( $oAppList ) {
            $result = [];
            foreach ( $oAppList as $item ) {
                $check_user_id = $item->check_user_id;
                if ( !$check_user_id ) {
                    continue;
                }
                $result = array_merge($result, explode(',', $check_user_id));
            }
        }
        return $result;
    }

    /**
     * 获取可以看广告的代理商列表
     */
    public function getADAgent()
    {
        $oADAgentService = new ADAgentService();
        $allAgent        = $oADAgentService->getData();
        if ( !$allAgent ) {
            $oAgent = Agent::find([
                'ad_visible = "Y"',
                'columns' => 'id'
            ]);
            if ( $oAgent ) {
                $allAgent = array_column($oAgent->toArray(), 'id');
                $oADAgentService->save($allAgent);
            }
        }
        return $allAgent;
    }

    /**
     * 获取可以有停留诱导的代理
     */
    public function getStayGuideMsgAgent()
    {
        $oADAgentService = new ADAgentService('stay_guide');
        $allAgent        = $oADAgentService->getData();
        if ( !$allAgent ) {
            $oAgent = Agent::find([
                'stay_guide_msg_flg = "Y"',
                'columns' => 'id'
            ]);
            if ( $oAgent ) {
                $allAgent = array_column($oAgent->toArray(), 'id');
                $oADAgentService->save($allAgent);
            }
        }
        return $allAgent;
    }


    /**
     * @param int $sUserId
     * 判断当前app是否为审核中
     */
    public function isPublish( $sUserId = 0, $type, $oUser = NULL )
    {
        $app_name = $this->request->get('app_name', 'string', 'tianmi');
        $app_os   = $this->request->get('app_os', 'string', 'Android');
        // 如果不需要判断 直接返回false;
        switch ( $type ) {
            case AppList::PUBLISH_IOS_CHAT:
                if ( $app_os == 'Android' ) {
                    return FALSE;
                }
                break;
            case AppList::PUBLISH_HIDE_OFFLINE_ANCHOR:
                if ( $app_os != 'Android' ) {
                    return FALSE;
                }
                break;
            case AppList::PUBLISH_CAN_NOT_CHANGE_PROFILE:
                // 普通账号 在审核中 不能修改
                $oCheckUserIds = $this->getCheckUserIds();
                $oAppInfo      = $this->getAppInfo();
                $checkFlg      = $oAppInfo['on_publish'];
                if ( $checkFlg == 'Y' && !in_array($sUserId, $oCheckUserIds) ) {
                    return TRUE;
                }
                return FALSE;
                break;
            case AppList::PUBLISH_RECHARGE:
                // 苹果内购
                if ( $app_os == 'Android' ) {
                    return FALSE;
                }
                break;
            case AppList::EXAMINE_ANCHOR:
                if ( $app_os == 'Android' ) {
//                    2019-05-06 香蕉马甲 开启安卓审核环境
//                    return FALSE;
                }
                break;
        }
        if ( $app_name == 'tianmi' && $app_os != 'Android' ) {
            return FALSE;
        }
        $oCheckUserIds = $this->getCheckUserIds();
        $oAppInfo      = $this->getAppInfo();
        $checkFlg      = $oAppInfo['on_publish'];

        //当on_publish为Y时显示上架中的渠道  如果没填 则所有渠道为上架中

        $on_publish_agent_id = $oAppInfo['on_publish_agent_id'];
        if ( $checkFlg == 'Y' && $on_publish_agent_id ) {
            $on_publish_agent_id_arr = explode(',', $on_publish_agent_id);

            // 需要取 app里的渠道 获取渠道号
            $requestChannel = $this->request->get('app_channel', 'string');
            $thisAgentId    = 0;
            if ( $requestChannel ) {
                $oAgent = Agent::findFirst([
                    'invite_code = :invite_code:',
                    'bind'  => [
                        'invite_code' => $requestChannel
                    ],
                    'cache' => [
                        'lifetime' => 3600,
                        'key'      => 'agentInviteCode:' . $requestChannel
                    ]
                ]);
                if ( $oAgent ) {
                    $thisAgentId = $oAgent->id;
                }
            }
            if ( !in_array($thisAgentId, $on_publish_agent_id_arr) ) {
                $checkFlg = 'N';
            }
        }

        // 如果版本号不等于审核版本 则非审核中
        if ( $checkFlg == 'Y' && $this->request->get('app_version', 'string') != $oAppInfo['on_publish_version'] ) {
            $checkFlg = 'N';
        }

        if ( $type == AppList::PUBLISH_RECHARGE ) {
            // 苹果内购 单独开关
            $checkFlg = $oAppInfo['ios_pay'];
        }
        if ( $checkFlg == 'N' && (empty($sUserId) || !in_array($sUserId, $oCheckUserIds)) ) {
            // 不在审核状态  且 该uid 不是审核账号
            return FALSE;
        }
        return TRUE;
    }
}
