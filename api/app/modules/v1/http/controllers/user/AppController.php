<?php
/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | APP系统控制器                                                          |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use app\models\Agent;
use app\models\AppList;
use app\models\DeviceActiveLog;
use app\models\ExamineInfo;
use app\models\LevelConfig;
use app\models\User;
use app\models\UserProfileSetting;
use Exception;
use app\models\Kv;
use app\models\AboutUs;
use app\models\Agreement;
use app\models\AppVersion;
use app\models\AppErrorLog;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

class AppController extends ControllerBase
{
    use \app\services\UserService;

    /**
     * postsAction 社区规则
     *
     * @param int $nUserId
     */
    public function postsAction($nUserId = 0)
    {
        if ( $this->getParams('format', 'string', 'html') == 'html' ) {
            header('location: ' . APP_WEB_URL . '/agreement?status=posts&' . http_build_query($this->getParams()));
            return;
        }
        $row['content'] = Agreement::findFirst(6)->agreement_content;
        $row['content'] = $this->getContent($row['content']);
        $this->success($row,false);
    }

    /**
     * aboutUsAction 关于我们
     *
     * @param int $nUserId
     */
    public function aboutUsAction($nUserId = 0)
    {
        $type = $this->getParams('display', 'int', 'app');
        if ( $type == 'app' ) {
            $type = 1;
        } else {
            $type = 2;
        }
        $row['about_us'] = AboutUs::find([
            "about_us_status='Y'and type={$type} ORDER BY about_us_id",
            'columns' => 'about_us_id id, about_us_title title,about_us_create_time time ,about_us_content content'
        ]);
        $this->success($row,false);
    }

    /**
     * aboutUsAction 关于我们
     *
     * @param int $nUserId
     */
    public function aboutUsDetailAction($nUserId = 0)
    {
        $sFormat    = $this->getParams('format', 'string', 'html');
        $nAboutUsId = $this->getParams('about_us_id', 'int', 0);
        try {
            $AboutUs = AboutUs::findFirst([
                'about_us_id=:about_us_id: AND about_us_status="Y"',
                'bind' => [
                    'about_us_id' => $nAboutUsId,
                ],
            ]);
            if ( !$AboutUs ) {
                throw new Exception(sprintf('about_us_id %s %s', json_encode($this->getParams()), ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
            }
            $row['article']['id']      = $AboutUs->about_us_id;
            $row['article']['title']   = $AboutUs->about_us_title;
            $row['article']['content'] = $this->getContent($AboutUs->about_us_content);
            $row['article']['time']    = $AboutUs->about_us_create_time;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        if ( strtolower($sFormat) == 'html' ) {
            header('location: ' . APP_WEB_URL . '/details?status=about&' . http_build_query($this->getParams()));
            return;
        } else {
            $this->success($row,false);
        }
    }

    /**
     * registerAgreementAction 注册协议
     */
    public function registerAgreementAction()
    {
        if ( $this->getParams('format', 'string', 'html') == 'html' ) {
            header('location: ' . APP_WEB_URL . '/agreement?status=register&' . http_build_query($this->getParams()));
            return;
        }
        $row['content'] = Agreement::findFirst(1)->agreement_content;
        $row['content'] = $this->getContent($row['content']);
        $this->success($row,false);
    }

    /**
     * liveAgreement 隐私协议
     */
    public function liveAgreementAction()
    {
        if ( $this->getParams('format', 'string', 'html') == 'html' ) {
            header('location: ' . APP_WEB_URL . '/agreement?status=userStarts&' . http_build_query($this->getParams()));
            return;
        }
        $row['content'] = Agreement::findFirst(4)->agreement_content;
        $row['content'] = $this->getContent($row['content']);
        $this->success($row,false);
    }

    /**
     * cAgreement 文明公约
     */
    public function cAgreementAction()
    {
        if ( $this->getParams('format', 'string', 'html') == 'html' ) {
            header('location: ' . APP_WEB_URL . '/agreement?status=userStarts&' . http_build_query($this->getParams()));
            return;
        }
        $row['content'] = Agreement::findFirst(3)->agreement_content;
        $row['content'] = $this->getContent($row['content']);
        $this->success($row,false);
    }

    /**
     * 隐私协议
     */
    public function privacyPolicyAction()
    {
        if ( $this->getParams('format', 'string', 'html') == 'html' ) {
            header('location: ' . APP_WEB_URL . '/agreement?status=userStarts&' . http_build_query($this->getParams()));
            return;
        }
        $row['content'] = Agreement::findFirst(4)->agreement_content;
        $row['content'] = $this->getContent($row['content']);
        $this->success($row,false);
    }


    /**
     * snatchAction 抢聊特权
     */
    public function snatchAction()
    {
        if ( $this->getParams('format', 'string', 'html') == 'html' ) {
            header('location: ' . APP_WEB_URL . '/agreement?status=snatch&' . http_build_query($this->getParams()));
            return;
        }
        $row['content'] = Agreement::findFirst(5)->agreement_content;
        $row['content'] = $this->getContent($row['content']);
        $this->success($row,false);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/config
     * @api {get} /user/app/config app系统配置
     * @apiName app-config
     * @apiGroup User
     * @apiDescription app系统配置
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String='android','ios'} app_os 操作系统
     * @apiParam (正常请求){String} app_name app名称flg
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String='android','ios'} app_os 操作系统
     * @apiParam (debug){String} app_name app名称flg
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.become_anchor_guide_image  申请主播引导图片
     * @apiSuccess {String} d.invite_user_guide_image 邀请用户引导图片
     * @apiSuccess {String} d.marketAudit
     * @apiSuccess {String} d.coin
     * @apiSuccess {String} d.dot
     * @apiSuccess {String} d.guide_msg_flg   是否开启用户诱导  Y为开启 N为不开启
     * @apiSuccess {String} d.user_say_hi_flg   是否开启用户一键打招呼功能  Y为开启 N为不开启
     * @apiSuccess {String='Y','N'} d.activity_notice_flg   是否开启活动提醒
     * @apiSuccess {String} d.activity_show_url   活动跳转地址
     * @apiSuccess {String} d.user_nickname_change_interval_day   用户修改昵称间隔时间（天）
     * @apiSuccess {String} d.coin_to_intimate   金币兑换亲密度比例
     * @apiSuccess {object} d.version
     * @apiSuccess {String} d.version.name
     * @apiSuccess {number} d.version.code
     * @apiSuccess {String} d.version.content
     * @apiSuccess {String} d.version.is_force
     * @apiSuccess {String} d.version.download_url
     * @apiSuccess {number} d.version.create_time
     * @apiSuccess {object[]} d.user_level_config    用户等级信息
     * @apiSuccess {number} d.user_level_config.level  等级值
     * @apiSuccess {number} d.user_level_config.color_r  等级背景颜色 R
     * @apiSuccess {number} d.user_level_config.color_g  等级背景颜色 G
     * @apiSuccess {number} d.user_level_config.color_b 等级背景颜色 B
     * @apiSuccess {object[]} d.anchor_level_config    主播等级信息
     * @apiSuccess {number} d.anchor_level_config.level  等级值
     * @apiSuccess {number} d.anchor_level_config.color_r  等级背景颜色 R
     * @apiSuccess {number} d.anchor_level_config.color_g  等级背景颜色 G
     * @apiSuccess {number} d.anchor_level_config.color_b 等级背景颜色 B
     * @apiSuccess {number} d.apply_anchor_wechat 主播认证联系微信
     * @apiSuccess {number} d.customer_service_mobile 客服电话
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "become_anchor_guide_image": "http://lebolive-1255651273.file.myqcloud.com/static/images/icon_mine_join_yuyin.png",
     *                   "invite_user_guide_image": "http://lebolive-1255651273.file.myqcloud.com/static/images/icon_mine_invite.png",
     *                   "marketAudit": "N",
     *                   "coin": "金币",
     *                   "dot": "佣金",
     *                   "guide_msg_flg": "Y",
     *                   "activity_notice_flg": "Y",
     *                   "activity_show_url": "https://www.baidu.com/",
     *                   "user_say_hi_flg": "Y",
     *                   "user_nickname_change_interval_day": "7",
     *                   "coin_to_intimate": "10",
     *                   "version": {
     *                           "name": "v1.7.2",
     *                           "code": "44",
     *                           "content": "ssssss",
     *                           "is_force": "N",
     *                           "download_url": "https:\/\/www.baidu.com\/huanggua",
     *                           "create_time": "1540550198"
     *                       },
     *                     "user_level_config": [{
     *                         "level": "1",
     *                             "color_r": 107,
     *                             "color_g": 255,
     *                             "color_b": 245
     *                         }],
     *                     "anchor_level_config": [{
     *                         "level": "1",
     *                             "color_r": 107,
     *                             "color_g": 255,
     *                             "color_b": 245
     *                         }],
     *                      "apply_anchor_wechat": "haca5487",
     *                      "customer_service_mobile": "13238879702"
     *           },
     *           "t": "1542007936"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function configAction($nUserId = 0)
    {

        $sAppOs  = $this->getParams('app_os', 'string', 'android');
        $appName = $this->getParams('app_name', 'string', 'tianmi');

        $oAppInfo = $this->getAppInfo('qq', $appName);
        $examineFlg = $this->isPublish($nUserId, AppList::EXAMINE_ANCHOR);

        $app_id             = $oAppInfo['id'];
        $row['marketAudit'] = $examineFlg ? 'Y' : 'N';
        $data               = Kv::many([
            Kv::KEY_COIN_NAME,
            Kv::KEY_DOT_NAME,
            Kv::GUIDE_MSG_FLG,
            Kv::ACTIVITY_NOTICE_FLG,
            Kv::ACTIVITY_SHOW_URL,
            Kv::USER_SAY_HI_FLG,
            Kv::BECOME_ANCHOR_GUIDE_IMAGE,
            Kv::INVITE_USER_GUIDE_IMAGE,
            Kv::USER_NICKNAME_CHANGE_INTERVAL_DAY,
            Kv::COIN_TO_INTIMATE,
            Kv::FREE_TIME_SHOW,
        ]);
        // 申请主播引导图片
        $row['become_anchor_guide_image'] = isset($data[Kv::BECOME_ANCHOR_GUIDE_IMAGE]) ? $data[Kv::BECOME_ANCHOR_GUIDE_IMAGE] : '';
        // 邀请用户引导图片
        $row['invite_user_guide_image'] = isset($data[Kv::INVITE_USER_GUIDE_IMAGE]) ? $data[Kv::INVITE_USER_GUIDE_IMAGE] : '';
        // 金币名称
        $row['coin'] = isset($data[Kv::KEY_COIN_NAME]) ? $data[Kv::KEY_COIN_NAME] : '';
        // 收益名称
        $row['dot'] = isset($data[Kv::KEY_DOT_NAME]) ? $data[Kv::KEY_DOT_NAME] : '';
        // 收益名称
        $row['guide_msg_flg'] = $oAppInfo['app_guide_msg_flg'];
        // 活动提示 是否开始
        $row['activity_notice_flg'] = isset($data[Kv::ACTIVITY_NOTICE_FLG]) ? $data[Kv::ACTIVITY_NOTICE_FLG] : 'N';
        // 活动提示 H5地址
        $row['activity_show_url'] = isset($data[Kv::ACTIVITY_SHOW_URL]) ? $data[Kv::ACTIVITY_SHOW_URL] : '';
        // 用户一键打招呼开关
        $row['user_say_hi_flg'] = isset($data[Kv::USER_SAY_HI_FLG]) ? $data[Kv::USER_SAY_HI_FLG] : 'Y';
        // 用户修改昵称间隔时间（天）
        $row['user_change_nickname_interval_day'] = isset($data[Kv::USER_NICKNAME_CHANGE_INTERVAL_DAY]) ? $data[Kv::USER_NICKNAME_CHANGE_INTERVAL_DAY] : '0';
        // 1金币获取多少亲密度
        $row['coin_to_intimate'] = isset($data[Kv::COIN_TO_INTIMATE]) ? $data[Kv::COIN_TO_INTIMATE] : '10';
        // 是否开启赠送分钟数弹窗
        $row['free_time_show'] = isset($data[Kv::FREE_TIME_SHOW]) ? $data[Kv::FREE_TIME_SHOW] : 'N';

        // 版本
        $requestChannel = $this->request->get('app_channel', 'string');
        if($requestChannel == 'TIANMIOPPO'){
            $row['version'] = [
                'name' => '',
                'code' => '1',
                'content' => '',
                'is_force' => 'N',
                'download_url' => '',
                'create_time' => ''
            ];
        }else{
            $row['version'] = AppVersion::findFirst([
                'app_version_os=:app_version_os: AND app_id = :app_id: ORDER BY app_version_code DESC',
                'bind'    => [
                    'app_version_os' => strtolower($sAppOs),
                    'app_id'         => $app_id,
                ],
                'columns' => 'app_version_name name,app_version_code code,app_version_content content,app_version_is_force is_force,app_version_download_url download_url,app_version_create_time create_time',
            ]);
        }

        $this->getDump($row['version']);

        // 等级配置
        $oAnchorLevelConfig   = LevelConfig::find([
            'level_type = :level_type:',
            'bind' => [
                'level_type' => LevelConfig::LEVEL_TYPE_USER
            ]
        ]);
        $anchorLevelConfigArr = [];
        foreach ( $oAnchorLevelConfig as $configItem ) {
            $colorArr = explode(',', $configItem->level_extra);
            if ( count($colorArr) != 3 ) {
                continue;
            }
            $anchorLevelConfigArr[] = [
                'level'   => $configItem->level_value,
                'color_r' => intval($colorArr[0]),
                'color_g' => intval($colorArr[1]),
                'color_b' => intval($colorArr[2]),
            ];
        }
        $row['user_level_config'] = $anchorLevelConfigArr;


        $row['apply_anchor_wechat']     = 'mumu2847';
        $row['customer_service_mobile'] = '13125174361';

        // 用户昵称修改

        // 主播等级配置
        $oAnchorLevelConfig   = LevelConfig::find([
            'level_type = :level_type:',
            'bind' => [
                'level_type' => LevelConfig::LEVEL_TYPE_ANCHOR
            ]
        ]);
        $anchorLevelConfigArr = [];
        foreach ( $oAnchorLevelConfig as $anchorConfigItem ) {
            $configItemArr = unserialize($anchorConfigItem->level_extra);
            $colorArr = explode(',',$configItemArr['anchor_color']);
            $anchorLevelConfigArr[] = [
                'level'   => $anchorConfigItem->level_value,
                'color_r' => intval($colorArr[0]),
                'color_g' => intval($colorArr[1]),
                'color_b' => intval($colorArr[2]),
            ];
        }
        $row['anchor_level_config'] = $anchorLevelConfigArr;


        $oUser = User::findFirst($nUserId);
        if ( $oUser ) {
            $this->redis->hSet('user_app_version', $oUser->user_id, $this->getParams('app_version'));
        }
        $this->success($row);
    }

    /**
     * errorFeedbackAction 错误日志反馈
     */
    public function errorFeedbackAction($nUserId = 0)
    {
        $sAppOs           = $this->getParams('app_os', 'string', '');
        $sAppOsCode       = $this->getParams('app_os_code', 'string', '');
        $sAppOsModel      = $this->getParams('app_os_model', 'string', '');
        $sAppVersion      = $this->getParams('app_version', 'string', '');
        $sAppErrorContent = $this->getParams('app_error_content', 'string', '');
        if ( $sAppErrorContent ) {
            $oAppErrorLog                    = new AppErrorLog();
            $oAppErrorLog->app_os            = $sAppOs;
            $oAppErrorLog->app_os_code       = $sAppOsCode;
            $oAppErrorLog->app_os_model      = $sAppOsModel;
            $oAppErrorLog->app_version       = $sAppVersion;
            $oAppErrorLog->app_error_content = $sAppErrorContent;
            $oAppErrorLog->save();
        }
        $this->success();
    }

    /**
     * timConfigAction 腾讯云通信配置
     *
     * @param  int $nUserId
     */
    public function timSigAction($nUserId = 0)
    {
        $tim                 = $this->config->application->tim;
        $row['sdk_app_id']   = $tim->sdk_app_id;
        $row['account_type'] = $tim->account_type;
        $row['user_sig']     = '';
        $this->success($row);
    }

    /**
     * 获取微信公众号配置
     */
    public function weChatPublicAction()
    {
        $url = $this->request->getPost('url');
        if ( empty($url) ) {
            $this->error(10002);
        }
        $sTokenUrl    = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s", $this->config->oauth->wx_public->appid, $this->config->oauth->wx_public->appkey);
        $sTokenResult = file_get_contents($sTokenUrl);
        $aToken       = json_decode($sTokenResult, TRUE);
        $sTicketUrl   = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi", $aToken['access_token']);
        $sResult      = file_get_contents($sTicketUrl);
        $aTicket      = json_decode($sResult, TRUE);
        $sTimestamp   = time();
        $sNonceStr    = rand(100000, 999999);
        $sString      = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $aTicket['ticket'], $sNonceStr, $sTimestamp, $url);
        $sSign        = sha1($sString);
        $this->success([
            'appId'     => $this->config->oauth->wx_public->appid,
            'timestamp' => $sTimestamp,
            'nonceStr'  => $sNonceStr,
            'signature' => $sSign
        ]);
    }

    public function getContent($content)
    {
        $app_name = $this->getParams('app_name');
        if ( !$app_name ) {
            $app_name = 'tianmi';
        }
        $oAppList = AppList::findFirst([
            'app_flg = :app_flg:',
            'bind' => [
                'app_flg' => $app_name
            ]
        ]);
        return str_replace(
            [
                '#APP_NAME#',
                '#COMPANY_NAME#',
                '#ANCHOR_PREFIX#',
                '#PLATFORM_PREFIX#',
                '#SERVICE_PHONE#',
                '#SERVICE_WECHAT#',
                '#SERVICE_EMAIL#',
                '#COMPANY_ADDRESS#',
            ],
            [
                $oAppList->app_name,
                $oAppList->company_name,
                $oAppList->anchor_prefix,
                $oAppList->platform_prefix,
                $oAppList->service_phone,
                $oAppList->service_wechat,
                $oAppList->service_email,
                $oAppList->company_address,
            ],
            $content);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/commentTip
     * @api {get} /user/app/commentTip 评价标签
     * @apiName app-commentTip
     * @apiGroup Chat
     * @apiDescription 评价标签
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {String} d.praise_tip   好评标签  以半角逗号分隔
     * @apiSuccess {String} d.criticism_tip  差评标签  以半角逗号分隔
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "praise_tip": "知性大方,风情万种,丰满诱人,邻家女孩,完美身材,秀色可餐,性感妖娆,佛系少女,可爱萝莉,绝美女音",
     *                   "criticism_tip": "不露脸,不说话,态度恶劣,资料不符,私加微信欺诈"
     *           },
     *           "t": "1542276133"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function commentTipAction($nUserId = 0)
    {

        $praiseKey    = 'comment_tip:' . UserProfileSetting::PRAISE_TIP;
        $criticismKey = 'comment_tip:' . UserProfileSetting::CRITICISM_TIP;
        $praiseTip    = $this->redis->get($praiseKey);
        $criticismTip = $this->redis->get($criticismKey);
        if ( !$praiseTip ) {
            $oUserProfileSetting = UserProfileSetting::findFirst([
                'profile_key = :profile_key:',
                'bind'    => [
                    'profile_key' => UserProfileSetting::PRAISE_TIP
                ],
                'columns' => 'profile_select'
            ]);
            $praiseTip           = $oUserProfileSetting->profile_select;
            $this->redis->set($praiseKey, $praiseTip);
        }
        if ( !$criticismTip ) {
            $oUserProfileSetting = UserProfileSetting::findFirst([
                'profile_key = :profile_key:',
                'bind'    => [
                    'profile_key' => UserProfileSetting::CRITICISM_TIP
                ],
                'columns' => 'profile_select'
            ]);
            $criticismTip        = $oUserProfileSetting->profile_select;
            $this->redis->set($criticismKey, $criticismTip);
        }
        $row = [
            'praise_tip'    => $praiseTip,
            'criticism_tip' => $criticismTip,
        ];
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/activeDevice
     * @api {post} /user/app/activeDevice 激活设备
     * @apiName activeDevice
     * @apiGroup Index
     * @apiDescription 激活设备
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} invite_code 邀请码
     * @apiParam (正常请求){String} device_id 设备编号
     * @apiParam (正常请求){String} sign 签名 格式 %s_%s_%s   md5(邀请码 + 下划线 + 设备号  + 下划线 + key（秘钥）)
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} invite_code 邀请码
     * @apiParam (debug){String} device_id 设备编号
     * @apiParam (debug){String} sign 签名 格式
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function activeDeviceAction()
    {
        $signDevice  = $this->getParams('sign_device');
        $sInviteCode = $this->getParams('invite_code');
        $sDeviceNo   = $this->getParams('device_id');
//        $this->log->info('$signDevice:' . $signDevice);
//        $this->log->info('$sInviteCode:' . $sInviteCode);
//        $this->log->info('$sDeviceNo:' . $sDeviceNo);
        try {
            $key       = 'uJyTSwke8TSWnydM';
            $checkSign = md5(sprintf('%s_%s_%s', $sInviteCode, $sDeviceNo, $key));
//            $this->log->info('$checkSign:' . $checkSign);
            if ( $checkSign != $signDevice ) {
//                throw new Exception(ResponseError::getError(ResponseError::PARAM_ERROR), ResponseError::PARAM_ERROR);
            }
            $oDeviceActiveLog = DeviceActiveLog::findFirst([
                'device_active_device_no = :device_active_device_no:',
                'bind' => [
                    'device_active_device_no' => $sDeviceNo,
                ]
            ]);
            if ( !$oDeviceActiveLog ) {
                // 没有重复数据
                $device_active_agent_id = 0;
                if ( $sInviteCode ) {
                    $oParentInviteAgent = Agent::findFirst([
                        'invite_code=:invite_code:',
                        'bind' => [
                            'invite_code' => strtoupper($sInviteCode),
                        ]
                    ]);
                    if ( $oParentInviteAgent ) {
                        $device_active_agent_id = $oParentInviteAgent->id;
                    }
                }

                $oDeviceActiveLog                            = new DeviceActiveLog();
                $oDeviceActiveLog->device_active_device_no   = $sDeviceNo;
                $oDeviceActiveLog->device_active_invite_code = $sInviteCode;
                $oDeviceActiveLog->device_active_agent_id    = $device_active_agent_id;
                $oDeviceActiveLog->save();
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/heartbeat
     * @api {get} /user/app/heartbeat 心跳
     * @apiName app-heartbeat
     * @apiGroup Public
     * @apiDescription 心跳
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function heartbeatAction($nUserId = 0)
    {
        $this->success();
    }

    public function examineInfoAction( $nUserId = 0 )
    {
//        $nPage      = $this->getParams('page', 'int', 0);
//        $nPgaesize  = $this->getParams('pagesize', 'int', 200);
        try {
            $row['list'] = ExamineInfo::find([
                'columns' => 'examine_info_content',
                'order' => 'examine_info_id asc'
                              ]);
//            $builder = $this->modelsManager->createBuilder()->from(ExamineInfo::class)
//                ->columns('examine_info_content')
//                ->orderBy('examine_info_id asc');
//
//            $data = $this->page($builder, $nPage, $nPgaesize);
//
//            $row = [
//                'list' => $data['items']
//            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.2.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/matchShowNumber
     * @api {get} /user/app/matchShowNumber 001-190905匹配界面显示
     * @apiName app-matchShowNumber
     * @apiGroup Public
     * @apiDescription 001-190905匹配界面显示
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.number_min 显示最小值
     * @apiSuccess {number} d.number_max 显示最大值
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "number_min": 100,
     *                   "number_max": 200
     *           },
     *           "t": "1542276133"
     *       }
     */
    public function matchShowNumberAction($nUserId = 0)
    {
        try{
            $row = Kv::getMatchShowNumber();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

}