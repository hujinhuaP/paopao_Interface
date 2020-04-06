<?php
/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 充值控制器                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use app\helper\WechatPay;
use app\models\AppList;
use app\models\UserAccount;
use app\models\UserRechargeActionLog;
use Exception;
use app\models\Kv;
use app\models\User;

use app\helper\ResponseError;
use app\helper\AlipayServer;
use app\models\UserFinanceLog;
use app\models\UserRechargeCombo;
use app\models\UserRechargeOrder;
use app\models\UserInviteRewardLog;
use app\models\UserConsumeCategory;
use app\http\controllers\ControllerBase;

/**
 * RechargeController 充值
 */
class RechargeController extends ControllerBase
{

    use \app\services\UserService;

    /**
     * indexAction 充值
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/recharge/index
     * @api {get} /user/recharge/index 充值列表
     * @apiName recharge-index
     * @apiGroup Recharge
     * @apiDescription 充值列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.user  用户信息
     * @apiSuccess {number} d.user.user_id   用户id
     * @apiSuccess {String} d.user.user_avatar 用户头像
     * @apiSuccess {number} d.user.user_nickname  用户昵称
     * @apiSuccess {number} d.user.user_coin 用户金币
     * @apiSuccess {String} d.user.user_is_first_recharge  用户是否首次充值
     * @apiSuccess {object[]} d.recharge_combo  充值套餐
     * @apiSuccess {number} d.recharge_combo.recharge_combo_id 套餐id
     * @apiSuccess {String} d.recharge_combo.apple_id  苹果商品id
     * @apiSuccess {number} d.recharge_combo.recharge_combo_fee  支付价格
     * @apiSuccess {number} d.recharge_combo.recharge_combo_coin 获得金币
     * @apiSuccess {number} d.recharge_combo.recharge_combo_give_coin  赠送金币（暂无用）
     * @apiSuccess {number} d.recharge_combo.first_reward_vip_day  首充赠送VIP天数
     * @apiSuccess {number} d.recharge_combo.vip_reward_coin  VIP充值赠送金币数
     * @apiSuccess {number} d.connect_wechat  联系微信
     * @apiSuccess {number} d.connect_phone  联系手机号
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "connect_wechat": "TTbaby02",
     *                   "connect_phone": "13125174361",
     *                   "user": {
     *                       "user_id": "172",
     *                       "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
     *                       "user_nickname": "18823369189",
     *                       "user_coin": 10,
     *                       "user_is_first_recharge": "Y"
     *                   },
     *                   "recharge_combo": [
     *                   {
     *                       "recharge_combo_id": "23",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "6.00",
     *                       "recharge_combo_coin": "60",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   },
     *                   {
     *                       "recharge_combo_id": "8",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "30.00",
     *                       "recharge_combo_coin": "300",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   },
     *                   {
     *                       "recharge_combo_id": "9",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "118.00",
     *                       "recharge_combo_coin": "1180",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   },
     *                   {
     *                       "recharge_combo_id": "10",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "188.00",
     *                       "recharge_combo_coin": "1880",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   },
     *                   {
     *                       "recharge_combo_id": "11",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "288.00",
     *                       "recharge_combo_coin": "2880",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   },
     *                   {
     *                       "recharge_combo_id": "12",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "998.00",
     *                       "recharge_combo_coin": "9980",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   },
     *                   {
     *                       "recharge_combo_id": "24",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "1380.00",
     *                       "recharge_combo_coin": "13800",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   },
     *                   {
     *                       "recharge_combo_id": "27",
     *                       "apple_id": "",
     *                       "recharge_combo_fee": "2000.00",
     *                       "recharge_combo_coin": "20000",
     *                       "recharge_combo_give_coin": "0",
     *                       "first_reward_vip_day": "0"
     *                   }
     *               ]
     *           },
     *           "t": 1537330776
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction($nUserId = 0)
    {
        $appleFlg = $this->getParams('apple_flg','string','N');
        try {
            $oUser                  = User::findFirst($nUserId);
            $user_is_first_recharge = 'Y';
            $oUserRechargeActionLog = UserRechargeActionLog::findFirst($nUserId);
            if ( $oUserRechargeActionLog && $oUserRechargeActionLog->recharge_times > 0 ) {
                $user_is_first_recharge = 'N';
            }
            $extraWhere = '';
            if ( $user_is_first_recharge == 'N' ) {
                $extraWhere = 'AND user_recharge_is_first = "N"';
            }
            if($appleFlg == 'Y'){
                // 取内购数据
                $oUserRechargeCombo = UserRechargeCombo::find([
                    'user_recharge_combo_apple_id != "" ' . $extraWhere,
                    'columns' => 'user_recharge_combo_id as recharge_combo_id, user_recharge_combo_apple_id as apple_id, 
                    user_recharge_combo_fee as recharge_combo_fee,user_recharge_combo_coin as recharge_combo_coin,
                    user_recharge_combo_give_coin as recharge_combo_give_coin,first_recharge_reward_vip_day as first_reward_vip_day,
                    user_recharge_vip_reward_coin as vip_reward_coin',
                    'order'   => 'user_recharge_combo_fee asc',
                ])->toArray();
                $app_name           = $this->getParams('app_name');
                if ( !$app_name ) {
                    $app_name = 'tianmi';
                }
                $oAppList       = AppList::findFirst([
                    'app_flg = :app_flg:',
                    'bind' => [
                        'app_flg' => $app_name
                    ]
                ]);
                $rechargePrefix = $oAppList->recharge_apple_goods_prefix;
                foreach ( $oUserRechargeCombo as &$combo ) {
                    $combo['apple_id'] = sprintf('%s_%s', $rechargePrefix, intval($combo['recharge_combo_fee']));
                }
            }else{
                $oUserRechargeCombo = UserRechargeCombo::find([
                    'user_recharge_combo_apple_id = "" ' . $extraWhere,
                    'columns' => 'user_recharge_combo_id as recharge_combo_id, user_recharge_combo_apple_id as apple_id, 
                    user_recharge_combo_fee as recharge_combo_fee,user_recharge_combo_coin as recharge_combo_coin,
                    user_recharge_combo_give_coin as recharge_combo_give_coin,first_recharge_reward_vip_day as first_reward_vip_day,
                    user_recharge_vip_reward_coin as vip_reward_coin',
                    'order'   => 'user_recharge_combo_fee asc',
                ]);
            }


            $row['user']           = [
                'user_id'                => $oUser->user_id,
                'user_avatar'            => $oUser->user_avatar,
                'user_level'             => $oUser->user_level,
                'user_nickname'          => $oUser->user_nickname,
                'user_coin'              => $oUser->user_coin + $oUser->user_free_coin,
                'user_is_first_recharge' => $user_is_first_recharge
            ];
            $row['recharge_combo'] = $oUserRechargeCombo;
            $row['connect_wechat'] = 'TTbaby02';
            $row['connect_phone']  = '13125174361';
            $aTime                 = explode('.', sprintf('%.10f', microtime(TRUE)));
            $this->cookies->set('_RECHARGE_ORDER_ID', date('YmdHis', $aTime[0]) . '000000' . $aTime[1] . mt_rand(10000, 99999), time() + 60 * 60 * 24);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * createOrderAction 创建订单
     *
     * @param  int $nUserId
     */
    public function createOrderAction($nUserId = 0)
    {

        $nComboId = $this->getParams('recharge_combo_id', 'int', 0);
        try {

            $app_version  = $this->getParams('open_id');
            $oUserAccount = UserAccount::findFirst([ 'user_id = ' . $nUserId ]);

            $url = sprintf('%s/pay_new.php?uid=%s', $this->config->application->h5_charge_url, $nUserId);
            // 如果在审核期间 或者 是审核账号则没有url
            if ( $this->isPublish($nUserId, AppList::PUBLISH_RECHARGE) ) {
                $url = '';
            }

            $aTime                = explode('.', sprintf('%.10f', microtime(TRUE)));
            $sRechargeOrderNumber = date('YmdHis', $aTime[0]) . '000' . $aTime[1] . mt_rand(10000, 99999);
            $oUserRechargeCombo   = UserRechargeCombo::findFirst([
                "user_recharge_combo_id=:id:",
                'bind' => [ 'id' => $nComboId ]
            ]);
            if ( $oUserRechargeCombo === FALSE ) {
                throw new Exception(sprintf('recharge_combo_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
            }
            $nCoin = $oUserRechargeCombo->user_recharge_combo_coin + $oUserRechargeCombo->user_recharge_combo_give_coin;
            // 添加订单
            $oUserRechargeOrder                                = new UserRechargeOrder();
            $oUserRechargeOrder->user_id                       = $nUserId;
            $oUserRechargeOrder->user_recharge_order_number    = $sRechargeOrderNumber;
            $oUserRechargeOrder->user_recharge_combo_id        = $oUserRechargeCombo->user_recharge_combo_id;
            $oUserRechargeOrder->user_recharge_combo_fee       = $oUserRechargeCombo->user_recharge_combo_fee;
            $oUserRechargeOrder->user_recharge_combo_coin      = $oUserRechargeCombo->user_recharge_combo_coin;
            $oUserRechargeOrder->user_recharge_combo_give_coin = $oUserRechargeCombo->user_recharge_combo_give_coin;
            $oUserRechargeOrder->user_recharge_order_fee       = $oUserRechargeCombo->user_recharge_combo_fee;
            $oUserRechargeOrder->user_recharge_order_coin      = $nCoin;
            $oUserRechargeOrder->user_recharge_order_type      = 'apple';
            $oUserRechargeOrder->user_recharge_order_status    = 'N';
            if ( $oUserRechargeOrder->save() === FALSE ) {
                $this->db->rollback();
                throw new \Phalcon\Db\Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserRechargeOrder->getMessages())), ResponseError::OPERATE_FAILED);
            }
        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $row['trade_no'] = $sRechargeOrderNumber;
        $row['url']      = $url;
        $this->success($row);
    }


    /**
     * zfbAction 支付宝
     *
     * @param  int $nUserId
     */
    public function zfbAction($nUserId = 0)
    {
//        $this->error(10002,'支付宝暂时无法使用，请选择其他支付方式');
        $is_freeze = Kv::get(Kv::FREEZE_RECHARGE);
        if ( $is_freeze ) {
            $this->error(10054);
        }
        $nRechargeComboId   = $this->getParams('recharge_combo_id', 'int', 0);
        $trade_type         = $this->getParams('trade_type', 'string', 'APP');
        $oUserRechargeCombo = UserRechargeCombo::findFirst($nRechargeComboId);
        if ( $oUserRechargeCombo === FALSE ) {
            throw new Exception(sprintf('recharge_combo_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
        }
        $nCoin      = $oUserRechargeCombo->user_recharge_combo_coin + $oUserRechargeCombo->user_recharge_combo_give_coin;
        $order_num  = time() . $nUserId . rand(10000, 99999);
        $class      = new AlipayServer();
        $body       = $subject = $this->config->application->app_name . '充值';
        $notify_url = $this->config->application->api_url . $this->config->application->pay->alipay->notify_url;
        $money      = $oUserRechargeCombo->user_recharge_combo_fee;
        $oUser      = User::findFirst($nUserId);
        if ( $this->config->application->evn == 'dev' || $oUser->user_is_superadmin == 'Y' ) {
            $money = 0.01;
        }
        if ( $trade_type == 'MWEB' ) {
            $res = $class->createOrderH5($body, $subject, $order_num, $money, $notify_url, 'RECHARGE');
        } else {
            $res = $class->createOrder($body, $subject, $order_num, $money, $notify_url, 'RECHARGE');
        }

        $osType = strtoupper($this->getParams('app_os', 'string', '')) == 'IOS' ? 'iOS' : 'Android';
        // 添加订单
        $oUserRechargeOrder                                = new UserRechargeOrder();
        $oUserRechargeOrder->user_id                       = $nUserId;
        $oUserRechargeOrder->user_recharge_order_number    = $order_num;
        $oUserRechargeOrder->user_recharge_combo_id        = $oUserRechargeCombo->user_recharge_combo_id;
        $oUserRechargeOrder->user_recharge_combo_fee       = $oUserRechargeCombo->user_recharge_combo_fee;
        $oUserRechargeOrder->user_recharge_combo_coin      = $oUserRechargeCombo->user_recharge_combo_coin;
        $oUserRechargeOrder->user_recharge_combo_give_coin = $oUserRechargeCombo->user_recharge_combo_give_coin;
        $oUserRechargeOrder->user_recharge_order_fee       = $money;
        $oUserRechargeOrder->user_recharge_order_coin      = $nCoin;
        $oUserRechargeOrder->user_recharge_order_status    = 'N';
        $oUserRechargeOrder->user_recharge_order_type      = 'zfb';
        $oUserRechargeOrder->user_type                     = $osType;
        if ( $oUserRechargeOrder->save() === FALSE ) {
            throw new \Phalcon\Db\Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserRechargeOrder->getMessages())), ResponseError::OPERATE_FAILED);
        }
        $this->success([ 'data' => $res ]);
    }

    /**
     * logAction 充值记录
     *
     * @param  int $nUserId
     */
    public function logAction($nUserId = 0)
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            $builder              = $this->modelsManager->createBuilder()->from(UserRechargeOrder::class)
                ->columns('user_recharge_order_id id,user_recharge_order_fee fee,user_recharge_order_coin coin,
                user_recharge_order_status status,user_recharge_order_create_time time')
                ->where('user_id=:user_id:', [ 'user_id' => $nUserId ])->orderBy('user_recharge_order_create_time desc');
            $row['recharge_list'] = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * wxAction 微信
     *
     * @param  int $nUserId
     */
    public function wxAction($nUserId = 0)
    {
        $is_freeze = Kv::get(Kv::FREEZE_RECHARGE);
        if ( $is_freeze ) {
            $this->error(10054);
        }
        $nRechargeComboId   = $this->getParams('recharge_combo_id', 'int', 0);
        $trade_type         = $this->getParams('trade_type', 'string', 'APP');
        $oUserRechargeCombo = UserRechargeCombo::findFirst($nRechargeComboId);
        if ( $oUserRechargeCombo === FALSE ) {
            throw new Exception(sprintf('recharge_combo_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
        }
        $nCoin     = $oUserRechargeCombo->user_recharge_combo_coin + $oUserRechargeCombo->user_recharge_combo_give_coin;
        $order_num = time() . $nUserId . rand(10000, 99999);

        $class     = new WechatPay();
        $orderType = 'wx';
        $wapUrl    = '';
        $attach    = 'RECHARGE';
        if ( $trade_type == 'MWEB' ) {
            // 获取支付配置
            $totalWxConfig = $this->config->wxpay;
            $configCount   = count($totalWxConfig);
            if ( $configCount == 1 ) {
                $attach    .= '|0';
                $orderType = $totalWxConfig[0]['pay_type'];
                $wapUrl    = $totalWxConfig[0]['wap_url'];
                $class->setPublicAppId($totalWxConfig[0]['public_app_id']);
                $class->setPublicMerchantId($totalWxConfig[0]['public_merchant_id']);
                $class->setPublicApiKey($totalWxConfig[0]['public_api_key']);
            } else if ( $configCount > 1 ) {
                // 随机获取
//                $rand       = rand(0, $configCount - 1);
                // 2019-04-19 微信支付全切为澎程
                // 2019-04-23 微信支付全切为泡泡
                // 2019-12-04 微信支付全切为雨声
                $rand       = 1;
                $attach     .= '|' . $rand;
                $itemConfig = $totalWxConfig[$rand];
                $orderType  = $itemConfig['pay_type'];
                $wapUrl     = $itemConfig['wap_url'];
                $class->setPublicAppId($itemConfig['public_app_id']);
                $class->setPublicMerchantId($itemConfig['public_merchant_id']);
                $class->setPublicApiKey($itemConfig['public_api_key']);
            }
        }

        $body       = $subject = $this->config->application->app_name . '充值';
        $notify_url = $this->config->application->api_url . $this->config->application->pay->wechat->notify_url;
        $ip         = $this->config->application->pay->wechat->ip;
        $money      = $oUserRechargeCombo->user_recharge_combo_fee;
        $oUser      = User::findFirst($nUserId);
        if ( $this->config->application->evn == 'dev' || $oUser->user_is_superadmin == 'Y' ) {
            $money = 0.01;
        }
        if ( strtoupper($this->getParams('app_os', 'string', '')) == 'IOS' ) {
            $wapUrl .= '://pay';
        }
        $data = $class->createOrder($money, $order_num, $body, $notify_url, $trade_type, $nRechargeComboId, $ip, $attach, $wapUrl);


        $osType = strtoupper($this->getParams('app_os', 'string', '')) == 'IOS' ? 'iOS' : 'Android';

        // 添加订单
        $oUserRechargeOrder                                = new UserRechargeOrder();
        $oUserRechargeOrder->user_id                       = $nUserId;
        $oUserRechargeOrder->user_recharge_order_number    = $order_num;
        $oUserRechargeOrder->user_recharge_combo_id        = $oUserRechargeCombo->user_recharge_combo_id;
        $oUserRechargeOrder->user_recharge_combo_fee       = $oUserRechargeCombo->user_recharge_combo_fee;
        $oUserRechargeOrder->user_recharge_combo_coin      = $oUserRechargeCombo->user_recharge_combo_coin;
        $oUserRechargeOrder->user_recharge_combo_give_coin = $oUserRechargeCombo->user_recharge_combo_give_coin;
        $oUserRechargeOrder->user_recharge_order_fee       = $money;
        $oUserRechargeOrder->user_recharge_order_coin      = $nCoin;
        $oUserRechargeOrder->user_recharge_order_status    = 'N';
        $oUserRechargeOrder->user_recharge_order_type      = $orderType;
        $oUserRechargeOrder->user_type                     = $osType;
        if ( $oUserRechargeOrder->save() === FALSE ) {
            throw new \Phalcon\Db\Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserRechargeOrder->getMessages())), ResponseError::OPERATE_FAILED);
        }
        $this->success($data);
    }

}