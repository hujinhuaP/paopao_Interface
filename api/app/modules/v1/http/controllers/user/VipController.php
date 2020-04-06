<?php
/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | VIP控制器                                                              |
 +------------------------------------------------------------------------+
 | Authors: lsj <yeah_lsj@yeah.net>                                       |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use app\helper\AlipayServer;
use app\helper\WechatPay;
use app\models\AppList;
use app\models\UserAccount;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use Exception;
use app\models\User;
use app\models\UserVipCombo;
use app\models\UserVipOrder;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
 * VipController VIP
 */
class VipController extends ControllerBase
{
    use \app\services\UserService;

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/vip/index
     * @api {get} /user/vip/index 001-190912会员中心
     * @apiName vip-index
     * @apiGroup Recharge
     * @apiDescription 会员中心
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
     * @apiSuccess {object} d.user
     * @apiSuccess {number} d.user.user_id
     * @apiSuccess {String} d.user.user_avatar
     * @apiSuccess {number} d.user.user_level
     * @apiSuccess {String} d.user.user_nickname
     * @apiSuccess {number} d.user.user_coin
     * @apiSuccess {number} d.user.user_member_expire_time
     * @apiSuccess {String} d.user.user_is_member
     * @apiSuccess {object[]} d.vip_combo
     * @apiSuccess {number} d.vip_combo.combo_fee
     * @apiSuccess {number} d.vip_combo.combo_month
     * @apiSuccess {number} d.vip_combo.combo_id
     * @apiSuccess {String} d.vip_combo.apple_id
     * @apiSuccess {number} d.vip_combo.original_price
     * @apiSuccess {number} d.vip_combo.discount
     * @apiSuccess {number} d.vip_combo.average_daily_price
     * @apiSuccess {number} d.vip_combo.reward_coin   奖励金币
     * @apiSuccess {object[]} d.vip_privilege
     * @apiSuccess {String} d.vip_privilege.logo
     * @apiSuccess {String} d.vip_privilege.name
     * @apiSuccess {String} d.vip_privilege.detail
     * @apiSuccess {number} d.order_id
     * @apiSuccess {String} d.connect_wechat
     * @apiSuccess {number} d.connect_phone
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "user": {
     *            "user_id": "168",
     *            "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/e1u7Ut4rUff6QDfsRXuTjJwpuqaEBeyBL8FC7bIu6fcuXkogvUBRYLVCIRFLQicgwxVVC3dibibSbkxM88BXsQVSA\/132",
     *            "user_level": "2",
     *            "user_nickname": "啦啦啦",
     *            "user_coin": "10.00",
     *            "user_member_expire_time": "0",
     *            "user_is_member": "N"
     *        },
     *        "vip_combo": [{
     *            "combo_fee": "1",
     *            "combo_month": "1",
     *            "combo_id": "11",
     *            "apple_id": "",
     *            "original_price": "20",
     *            "discount": "0.50",
     *            "average_daily_price": "0.03",
     *            "reward_coin": "0"
     *        }],
     *        "vip_privilege": [{
     *            "logo": "https:\/\/lebolive-1255651273.image.myqcloud.com\/static\/images\/vip_rule\/1-mark.png",
     *            "name": "尊贵勋章",
     *            "detail": "专属勋章显示"
     *        }],
     *        "order_id": "2019090617341800000088426",
     *        "connect_wechat": "TTbaby02",
     *        "connect_phone": "13125174361"
     *    },
     *    "t": "1567762458"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction( $nUserId = 0 )
    {
        $appleFlg = $this->getParams('apple_flg', 'string', 'N');
        try {
            $oUser = User::findFirst($nUserId);
//            $oUserAccount = UserAccount::findFirst([
//                'user_id = :user_id:',
//                'bind' => [ 'user_id' => $nUserId ]
//            ]);
            $columns = 'user_vip_combo_fee as combo_fee,user_vip_combo_month as combo_month,user_vip_combo_id as combo_id,
            user_vip_combo_apple_id as apple_id,user_vip_combo_original_price as original_price,user_vip_combo_discount as discount,
            user_vip_combo_average_daily_price as average_daily_price,user_vip_combo_reward_coin as reward_coin';

            if ( $appleFlg == 'Y' ) {
                $oUserVipCombo = UserVipCombo::find([
                    'user_vip_combo_apple_id != ""',
                    'columns' => $columns,
                    'order'   => 'user_vip_combo_fee',
                ])->toArray();
                $app_name      = $this->getParams('app_name');
                if ( !$app_name ) {
                    $app_name = 'tianmi';
                }
                $oAppList       = AppList::findFirst([
                    'app_flg = :app_flg:',
                    'bind' => [
                        'app_flg' => $app_name
                    ]
                ]);
                $rechargePrefix = $oAppList->vip_apple_goods_prefix;
                foreach ( $oUserVipCombo as &$combo ) {
                    $combo['apple_id'] = sprintf('%s_%s', $rechargePrefix, intval($combo['combo_month']));
                }
            } else {
                $oUserVipCombo = UserVipCombo::find([
                    'user_vip_combo_apple_id = ""',
                    'columns' => $columns,
                    'order'   => 'user_vip_combo_fee',
                ]);
            }
            $row['user']           = [
                'user_id'                 => $oUser->user_id,
                'user_avatar'             => $oUser->user_avatar,
                'user_level'              => $oUser->user_level,
                'user_nickname'           => $oUser->user_nickname,
                //                'user_level'              => $oUser->user_level,
                'user_coin'               => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
                // 为0表示没有开通过会员
                'user_member_expire_time' => $oUser->user_member_expire_time,
                'user_is_member'          => time() > $oUser->user_member_expire_time ? 'N' : 'Y',
                'user_vip_level'          => $oUser->user_vip_level
            ];
            $row['vip_combo']      = $oUserVipCombo;
            $row['vip_privilege']  = [
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/1-mark.png',
                    'name'     => '作品集特权',
                    'detail'   => '全部作品集折扣特权',
                    'icon_flg' => '新'
                ],
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/1-mark.png',
                    'name'     => '视频折扣',
                    'detail'   => '1v1视频折扣特权',
                    'icon_flg' => '新'
                ],
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/1-mark.png',
                    'name'     => '尊贵勋章',
                    'detail'   => '专属勋章显示',
                    'icon_flg' => ''
                ],
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/2-chat.png',
                    'name'     => '无限畅聊',
                    'detail'   => '文字私聊永久免费',
                    'icon_flg' => ''
                ],
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/3-dub.png',
                    'name'     => '双倍奖励',
                    'detail'   => '签到、任务双倍奖励',
                    'icon_flg' => ''
                ],
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/4-match.png',
                    'name'     => '精选匹配',
                    'detail'   => '优先匹配高颜值美女',
                    'icon_flg' => ''
                ],
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/5-font.png',
                    'name'     => '访客记录',
                    'detail'   => '解锁所有访客记录',
                    'icon_flg' => ''
                ],
                [
                    'logo'     => 'https://lebolive-1255651273.image.myqcloud.com/static/images/vip_rule/6-pri.png',
                    'name'     => '查看更多私密信息',
                    'detail'   => '私密照片免费看、私密视频尊享5折',
                    'icon_flg' => ''
                ],
            ];
            $row['order_id']       = date('YmdHis') . '000000' . mt_rand(10000, 99999);
            $row['connect_wechat'] = 'TTbaby02';
            $row['connect_phone']  = '13125174361';
            $this->cookies->set('_VIP_RECHARGE_ID', $row['order_id'], time() + 60 * 60 * 24);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/vip/recharge
     * @api {post} /user/vip/recharge VIP充值
     * @apiName vip-recharge
     * @apiGroup Recharge
     * @apiDescription VIP充值
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} combo_id 套餐ID
     * @apiParam (正常请求){String='APP(原生)','MWEB(H5)'} trade_type 调取类型
     * @apiParam (正常请求){String='1(支付宝)','2(微信)'} pay_type 支付类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} combo_id 套餐ID
     * @apiParam (debug){String='APP(原生)','MWEB(H5)'} trade_type 调取类型
     * @apiParam (debug){String='1(支付宝)','2(微信)'} pay_type 支付类型
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
    public function rechargeAction( $nUserId = 0 )
    {
        $nComboId           = $this->getParams('combo_id', 'int', 0);
        $pay_type           = $this->getParams('pay_type', 'int', 1);
        $display            = $this->getParams('display', 'string', 'APP');
        $trade_type         = $this->getParams('trade_type', 'string', 'APP');
        $sCookieOrderNumber = date('YmdHis') . '000000' . mt_rand(10000, 99999);

        $sOrderNumber = $sCookieOrderNumber;
        try {

//            if ( $sOrderNumber == '' ) {
//                throw new Exception(sprintf('%s %s', 'order_id', ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
//            }
            // 验证OrderID的合法性
//            $aOrderNumber = explode('000000', $sOrderNumber);
//            if ( strtotime($aOrderNumber[0]) < strtotime('-1 day') || !is_numeric($aOrderNumber[1]) ) {
//                throw new Exception(sprintf('%s %s', 'order_id', ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
//            }
            $oUserVipCombo = UserVipCombo::findFirst($nComboId);
            if ( empty($oUserVipCombo) ) {
                throw new Exception(ResponseError::getError(ResponseError::COMBO_NOT_EXISTS), ResponseError::COMBO_NOT_EXISTS);
            }
            $oUser = User::findFirst($nUserId);
            if ( $this->config->application->evn == 'dev' || $oUser->user_is_superadmin == 'Y' ) {
                $oUserVipCombo->user_vip_combo_fee = 0.01;
            }


            $body = $subject = $this->config->application->app_name . '购买VIP';
            if ( $pay_type == 1 ) {
                $orderType  = 'zfb';
                $notify_url = $this->config->application->api_url . $this->config->application->pay->alipay->notify_url;
                $alipay     = new AlipayServer();
                if ( $trade_type == 'APP' ) {
                    $res            = $alipay->createOrder($body, $subject, $sOrderNumber, $oUserVipCombo->user_vip_combo_fee, $notify_url, 'BUY_VIP');
                    $return['data'] = $res;
                } else {
//                    $return_url = $this->config->application->pay->alipay->return_url;
//                    $res        = $alipay->getPage($return_url, $notify_url, $body, $subject, $sOrderNumber, $oUserVipCombo->user_vip_combo_fee, 'BUY_VIP');
//                    $this->db->commit();
//                    $this->cookies->set('_VIP_RECHARGE_ID', date('YmdHis') . '000000' . mt_rand(10000, 99999), time() + 60 * 60 * 24);
//                    echo $res;
                    $res            = $alipay->createOrderH5($body, $subject, $sOrderNumber, $oUserVipCombo->user_vip_combo_fee, $notify_url, 'BUY_VIP');
                    $return['data'] = $res;
                }
            } else {
                $notify_url = $this->config->application->api_url . $this->config->application->pay->wechat->notify_url;
                $ip         = $this->config->application->pay->wechat->ip;
                $wechat     = new WechatPay();
                $orderType  = 'wx';
                $wapUrl     = '';
                $attach     = 'BUY_VIP';
                if ( $trade_type == 'MWEB' ) {
                    // 获取支付配置
                    $totalWxConfig = $this->config->wxpay;
                    $configCount   = count($totalWxConfig);
                    if ( $configCount == 1 ) {
                        $attach    .= '|0';
                        $orderType = $totalWxConfig[0]['pay_type'];
                        $wapUrl    = $totalWxConfig[0]['wap_url'];
                        $wechat->setPublicAppId($totalWxConfig[0]['public_app_id']);
                        $wechat->setPublicMerchantId($totalWxConfig[0]['public_merchant_id']);
                        $wechat->setPublicApiKey($totalWxConfig[0]['public_api_key']);
                    } else if ( $configCount > 1 ) {
                        // 随机获取
//                        $rand       = rand(0, $configCount - 1);
                        // 2019-04-19 微信支付全切为澎程
                        // 2019-04-23 微信支付全切为泡泡
                        // 2019-12-04 微信支付全切为雨声
                        $rand       = 1;
                        $attach     .= '|' . $rand;
                        $itemConfig = $totalWxConfig[ $rand ];
                        $orderType  = $itemConfig['pay_type'];
                        $wapUrl     = $itemConfig['wap_url'];
                        $wechat->setPublicAppId($itemConfig['public_app_id']);
                        $wechat->setPublicMerchantId($itemConfig['public_merchant_id']);
                        $wechat->setPublicApiKey($itemConfig['public_api_key']);
                    }
                }
                if ( strtoupper($this->getParams('app_os', 'string', '')) == 'IOS' ) {
                    $wapUrl .= '://pay';
                }
                $res    = $wechat->createOrder($oUserVipCombo->user_vip_combo_fee, $sOrderNumber, $subject, $notify_url, $trade_type, $nComboId, $ip, $attach, $wapUrl);
                $return = $res;
            }
            if ( !$res ) {
                return FALSE;
            }
            $osType                                    = strtoupper($this->getParams('app_os', 'string', '')) == 'IOS' ? 'iOS' : 'Android';
            $oUserVipOrder                             = new UserVipOrder();
            $oUserVipOrder->user_id                    = $nUserId;
            $oUserVipOrder->user_vip_order_number      = $sOrderNumber;
            $oUserVipOrder->user_member_expire_time    = $oUser->user_member_expire_time;
            $oUserVipOrder->user_vip_order_combo_fee   = $oUserVipCombo->user_vip_combo_fee;
            $oUserVipOrder->user_vip_order_combo_month = $oUserVipCombo->user_vip_combo_month;
            $oUserVipOrder->user_vip_combo_id          = $nComboId;
            $oUserVipOrder->user_vip_order_type        = $orderType;
            $oUserVipOrder->user_type                  = $osType;
            if ( $oUserVipOrder->save() === FALSE ) {
                throw new Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserVipOrder->getMessages())), ResponseError::OPERATE_FAILED);
            }
            $this->cookies->set('_VIP_RECHARGE_ID', date('YmdHis') . '000000' . mt_rand(10000, 99999), time() + 60 * 60 * 24);
        } catch ( \Phalcon\Db\Exception $e ) {
            $this->cookies->set('_VIP_RECHARGE_ID', date('YmdHis') . '000000' . mt_rand(10000, 99999), time() + 60 * 60 * 24);
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->cookies->set('_VIP_RECHARGE_ID', date('YmdHis') . '000000' . mt_rand(10000, 99999), time() + 60 * 60 * 24);
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->cookies->set('_VIP_RECHARGE_ID', date('YmdHis') . '000000' . mt_rand(10000, 99999), time() + 60 * 60 * 24);
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($return);
    }

    public function createOrderAction( $nUserId = 0 )
    {

        $nComboId = $this->getParams('combo_id', 'int', 0);
        try {

            $oUserVipCombo = UserVipCombo::findFirst([
                "user_vip_combo_id=:id:",
                'bind' => [ 'id' => $nComboId ]
            ]);
            $oUser         = User::findFirst("user_id={$nUserId}");
            if ( empty($oUserVipCombo) ) {
                throw new Exception(ResponseError::getError(ResponseError::COMBO_NOT_EXISTS), ResponseError::COMBO_NOT_EXISTS);
            }
            $url = sprintf('%s/vip.php?uid=%s', $this->config->application->h5_charge_url, $nUserId);
            // 如果在审核期间 或者 是审核账号则没有url
            if ( $this->isPublish($nUserId, AppList::PUBLISH_RECHARGE) ) {
                $url = '';
            }
            $aTime        = explode('.', sprintf('%.10f', microtime(TRUE)));
            $sOrderNumber = date('YmdHis', $aTime[0]) . '000' . $aTime[1] . mt_rand(10000, 99999);
            // 下单
            $oUserVipOrder                             = new UserVipOrder();
            $oUserVipOrder->user_id                    = $nUserId;
            $oUserVipOrder->user_vip_order_number      = $sOrderNumber;
            $oUserVipOrder->user_member_expire_time    = $oUser->user_member_expire_time;
            $oUserVipOrder->user_vip_order_combo_fee   = $oUserVipCombo->user_vip_combo_fee;
            $oUserVipOrder->user_vip_order_combo_month = $oUserVipCombo->user_vip_combo_month;
            $oUserVipOrder->user_vip_order_status      = 'N';
            $oUserVipOrder->user_vip_order_type        = 'apple';
            if ( $oUserVipOrder->save() === FALSE ) {
                throw new Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserVipOrder->getMessages())), ResponseError::OPERATE_FAILED);
            }
        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $row['url']      = $url;
        $row['trade_no'] = $sOrderNumber;
        $this->success($row);
    }
}