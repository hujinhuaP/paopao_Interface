<?php

namespace app\plugins;

use app\services\OnlineActionService;
use fast\Arr;
use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;
use app\models\UserAccount;

/**
 * SecurityPlugin
 *
 * This is the security plugin which controls that users only have access to the modules they're assigned to
 */
class SecurityPlugin extends Plugin
{

    use \app\services\UserService;

    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
    public function getAcl()
    {

        // if (!isset($this->persistent->acl)) {

        $acl = new AclList();

        $acl->setDefaultAction(Acl::ALLOW);

        // Register roles
        $roles = [
            // 用户
            'users'  => new Role(
                'Users',
                'Member privileges, granted after sign in.'
            ),
            // 游客
            'guests' => new Role(
                'Guests',
                'Anyone browsing the site who is not signed in is considered to be a "Guest".'
            )
        ];

        foreach ( $roles as $role ) {
            $acl->addRole($role);
        }

        ////////////////////////////////////////////////
        // 注意，这里是区分大小写的，统一使用小写判断 //
        ////////////////////////////////////////////////

        // User private area resources
        $userPrivateResources = [
            // 直播
            'live'    => [
                // 聊天游戏
                'chatgame' => [
                    'invite','index','anchor','add','update'
                ],
                // 直播列表
                'live'      => [ 'follow' ],
                // 主播
                'anchor'    => [
                    'index',
                    'getliveinfo',
                    'startlive',
                    'endlive',
                    'addsharelog',
                    'getprivatechat',
                    'getanchorinfo',
                    'startprivatechat',
                    'acceptprivatechat',
                    'privatechatminute',
                    'getprivatechatinfo',
                    'updatechatstatus',
                    'getchatdialog',
                    'updatechatinfo',
                    'users',
                    'privatechatinfo',
                    'snatchchat',
                    'hangupchat',
                    'cancelprivatechat',
                    'privatechatminutenew',
                    'getsearchrecommend',
                ],
                // 房间
                'room'      => [
                    'index',
                    'enter',
                    'userlist',
                    'sendchat',
                    'getchatlist',
                    'like',
                    'sendbarrage',
                    'prohibittalk',
                    'kick',
                    'addsharelog'
                ],
                // 礼物
                'gift'      => [ 'send' ],
                // 支付
                'pay'       => [
                    'time',
                    'fare'
                ],
                // 举报
                'report'    => [ 'room' ],
                // 回放
                'playback'  => [
                    'anchor',
                    'deletePlayBack'
                ],
                // 房管
                'roomadmin' => [
                    'index',
                    'search',
                    'add',
                    'delete'
                ],
                'ranking'   => [ 'intimate' ],
                'search'    => [ 'getrecommend' ],
            ],

            // 用户
            'user'    => [
                // 信息
                'profile'       => [
                    'index',
                    'update',
                    'card',
                    'updatepwd',
                    'balance',
                    'updateanchor',
                    'getlevelreward',
                    'level'
                ],
                // 实名认证
                'certification' => [
                    'check',
                    'add'
                ],
                // 绑定
                'bind'          => [
                    'phone',
                    'changephone'
                ],
                // 会员中心
                'vip'           => [
                    'index',
                    'recharge'
                ],
                // 充值
                'recharge'      => [
                    'index',
                    'applepay',
                    'wx',
                    'zfb',
                    'test',
                    'log'
                ],
                // 等级
                'level'         => [
                    'index',
                    'anchor',
                    'openchest'
                ],
                // 金额记录
                'amountrecord'  => [
                    'giftincreasedot',
                    'liveincreasedot',
                    'inviteincreasecoin',
                    'giftdecreasecoin',
                    'vipdecreasecoin',
                    'livedecreasecoin',
                    'chatconsumecoin',
                    'chatconsumedot',
                    'playbackconsumecoin',
                    'playbackconsumedot'
                ],
                // 聊天
                'chat'          => [
                    'dialoglist',
                    'dialog',
                    'send',
                    'systemdialoglist',
                    'systemdialog',
                    'ignoreunread',
                    'unread',
                    'deleteuserdialog',
                    'deletesystemdialog',
                    'sayhi',
                    'chatpaysettinglist',
                    'matchcenter',
                    'leavematchcenter',
                    'matchcenterusers',
                    'selectuser',
                    'userenterroom',
                    'deleteuserdialogall'
                ],
                // 关注
                'follow'        => [
                    'add',
                    'delete',
                    'follows',
                    'fans',
                    'reminds',
                    'updateremind'
                ],
                // 邀请
                'invite'        => [
                    'index',
                    'detail',
                    'add'
                ],
                // 签到
                'signin'        => [
                    'detail',
                    'add',
                    'judge'
                ],
                // 提现
                'withdraw'      => [
                    'index',
                    'add',
                    'log',
                    'detail',
                    'calculateservicecharge'
                ],
                // 兑换“现金”
                'exchange'      => [
                    'cashlog',
                    'log',
                    'add',
                    'index'
                ],
                // 守护
                'guard'         => [
                    'index',
                    'anchor',
                    'user',
                    'anchordetail',
                    'buyguard'
                ],
                'app'  => [
                    'heartbeat'
                ],
                'shortposts' => [
                    'checkrule',
                    'add',
                    'likecomment',
                    'addcomment',
                    'like',
                    'report',
                    'collect',
                    'sendgift',
                    'mycomment',
                    'delete',
                    'deletecomment',
                    'deletereply',
                    'readmessage',
                    'message',
                    'simpledetail'
                ],
                'message' => [
                    'notify',
                    'dialoglist'
                ]
            ],
            // 账号
            'account' => [
                // 验证码
                'verifycode' => [
                    'judgechangephone',
                    'changephone',
                    'bindphone',
                    'withdraw'
                ],
                // 登录
                'login'      => [ 'status' ],
            ],
            //小视频
            'video'   => [
                'app' => [
                    'index',
                    'list',
                    'musiclist',
                    'getvideobytype',
                    'videodetail',
                    'addvideo',
                    'videolike',
                    'replylist',
                    'addreply',
                    'getplaybackurl',
                    'playbackisfree',
                    'playbackdetail',
                    'chargepricesettinglist'
                ],
                'msg' => [
                    'index',
                    'getunreadnum'
                ]
            ],
            //pc端
            'pc'      => [
                'app' => [
                    'searchuser',
                    'status'
                ]
            ],
        ];

        foreach ( $userPrivateResources as $key => $resources ) {
            foreach ( $resources as $resource => $actions ) {
                $acl->addResource(new Resource($key . '.' . $resource), $actions);
            }
        }

        //Grant access to private area to role Users
        foreach ( $userPrivateResources as $key => $resources ) {
            foreach ( $resources as $resource => $actions ) {
                foreach ( $actions as $action ) {
                    $acl->deny('Guests', $key . '.' . $resource, $action);
                }
            }
        }
        return $acl;
    }

    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeDispatch(Event $event, Dispatcher $dispatcher)
    {
        $sAccessToken = $this->request->get('access_token', 'string', '');
        $display      = $this->request->get('display', 'string', 'app');
        $sign         = $this->request->get('sign', 'string', '');
        $timestamp    = $this->request->get('timestamp', 'string', '');
        if ( $sAccessToken == '' ) {
            $sAccessToken = $this->request->getServer('HTTP_AUTHORIZATION');
        }
        $controller = $dispatcher->getControllerName();
        $action     = $dispatcher->getActionName();
        $auth = null;
        if ( $display == 'app' ) {
            if ( $this->request->get('debug', 'int', 0) == 1 ) {
                $oUserAccount = UserAccount::findFirst($this->request->get('uid', 'int', 0));
                $nUserId      = $oUserAccount->user_id;
                $sUserToken   = $oUserAccount->user_token;
                if ( $this->request->get('cli_api_key', 'string') != $this->config->application->cli_api_key ) {
                    if( $controller != 'help' && $controller != 'app'){
                        $this->log->info('【debug not auth url】 url: ' . $_SERVER['REQUEST_URI']);
                        echo 'error';
                        return FALSE;
                    }
                }
            }else{
                $nUserId = 0;
                $sUserToken = '';
                if($controller != 'errors' && $controller != 'help'){
                    if($sign && $sAccessToken){
                        $checkSign = md5(sprintf('%s_%s_%s',$sAccessToken,$timestamp,$this->config->application->api_key));
//                        $this->log->info('check:'.$checkSign);
//                        $this->log->info('get:'.$sign);
                        if ( $checkSign != $sign ) {
                            $dispatcher->forward([
                                'module'     => 'v1',
                                'controller' => 'errors',
                                'namespace'  => 'app\\http\\controllers',
                                'action'     => 'showSignInvalid'
                            ]);
                        }
//                        list($nUserId, $sUserToken, $token_expire_time) = $this->decryptAccessToken($sAccessToken);
                    }
                    list($nUserId, $sUserToken, $token_expire_time) = $this->decryptAccessToken($sAccessToken);
                    if($nUserId){
                        // 记录用户活动行为
                        $oOnlineActionService = new OnlineActionService($nUserId);
                        $oOnlineActionService->save([
                            'time' => time(),
                            'path' => $controller . '/' . $action
                        ]);
                    }
                }
            }

//            if($nUserId == 240){
//                $this->log->info(json_encode($this->request->get()));
//            }

            $isrobot = $this->request->get('isrobot', 'string', '');
            if ( $isrobot ) {
                list($nUserId, $sUserToken, $token_expire_time) = $this->decryptAccessToken($isrobot);
            }
            if ( $nUserId ) {
                if ( $isrobot ) {
                    $auth = 'Isrobot';
                } else {
                    $oUserAccount = UserAccount::findFirst($nUserId);
                    if ( $oUserAccount && $oUserAccount->user_token === $sUserToken ) {
                        $auth = $oUserAccount;
                    }
                }
            }
        } else {
            $nUserId = $this->redis->get($sAccessToken);
            if ( $nUserId ) {
                $auth = UserAccount::findFirst($nUserId);
            }
        }
        if ( !$auth ) {
            $role = 'Guests';
        } else {
            $role = 'Users';
        }

        $aNamespace = explode('\\', $dispatcher->getNamespaceName());
        $controller = isset($aNamespace[3]) ? $aNamespace[3] . '.' . $controller : $controller;
        $acl        = $this->getAcl();

        $allowed = $acl->isAllowed($role, strtolower($controller), strtolower($action));
        if ( !$allowed ) {
            if ( strtolower($controller) == 'account.login' && strtolower($action) == 'status' ) {
                $dispatcher->forward([
                    'module'     => 'v1',
                    'controller' => 'errors',
                    'namespace'  => 'app\\http\\controllers',
                    'action'     => 'showLogout'
                ]);
            } else {
                $dispatcher->forward([
                    'module'     => 'v1',
                    'controller' => 'errors',
                    'namespace'  => 'app\\http\\controllers',
                    'action'     => 'showAccessTokenInvalid'
                ]);
            }

            return FALSE;
        }
        $aParam = $dispatcher->getParams();
        array_unshift($aParam, $nUserId);
        $dispatcher->setParams($aParam);
    }
}
