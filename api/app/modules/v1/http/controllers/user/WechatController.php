<?php

namespace app\http\controllers\user;


use app\helper\ResponseError;
use app\models\Anchor;
use app\models\Group;
use app\models\Kv;
use app\models\Photographer;
use app\models\User;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use app\models\UserIntimateLog;
use app\models\UserWechatLog;
use app\services\AnchorStatService;
use Exception;
use app\http\controllers\ControllerBase;
use Phalcon\Security\Random;

/**
 * WechatController   用户购买微信
 */
class WechatController extends ControllerBase
{
    use \app\services\SystemMessageService;
    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.1hjp.com/v1/user/wechat/add
     * @api {post} /user/wechat/add 购买微信
     * @apiName wechat-add
     * @apiGroup Profile
     * @apiDescription 购买微信
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} sale_user_id 对方用户id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} sale_user_id 对方用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "user_coin": "1251.00",
     *               "wechat": "TTbaby02",
     *               "secret_key": "3e59lo"
     *       },
     *       "t": 1554195621
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function addAction($nUserId = 0)
    {

        $saleUserId = $this->getParams('sale_user_id');
        try {
            $oSaleUser = User::findFirst($saleUserId);

            if ( $oSaleUser->user_v_wechat == '' || $oSaleUser->user_v_wechat_price == 0 ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::OPERATE_FAILED) . '3',
                    ResponseError::OPERATE_FAILED
                );
            }
            $groupId = $oSaleUser->user_group_id;
            if ( $oSaleUser->user_is_anchor == 'Y' ) {

                $oAnchor = Anchor::findFirst([
                    'user_id = :user_id:',
                    'bind' => [
                        'user_id' => $saleUserId
                    ]
                ]);
                $nRatio  = $oAnchor->getCoinToDotRatio($oSaleUser, Anchor::RATIO_WECHAT);
            } else if ( $oSaleUser->user_is_photographer == 'Y' ) {
                $oPhotographer = Photographer::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $saleUserId ]
                ]);
                $nRatio        = $oPhotographer->getCoinToDotRatio($oSaleUser, Photographer::RATIO_WECHAT);
                $groupId       = 0;
            } else {
                throw new Exception(
                    ResponseError::getError(ResponseError::OPERATE_FAILED) .'2' ,
                    ResponseError::OPERATE_FAILED
                );
            }

            $oUser = User::findFirst($nUserId);
            $this->db->begin();
            $nCoin = $oSaleUser->user_v_wechat_price;

            $nDot            = sprintf('%.4f', $nCoin * ($nRatio / 100));
            $consumeFreeCoin = 0;
            $consumeCoin     = 0;
            if ( $oUser->user_free_coin <= 0 ) {
                // 直接扣充值币
                $consumeCoin = $nCoin;

            } else if ( $oUser->user_free_coin < $nCoin ) {
                //扣一部分充值币 扣光赠送币
                $consumeFreeCoin = $oUser->user_free_coin;
                $consumeCoin     = $nCoin - $oUser->user_free_coin;
            } else {
                $consumeFreeCoin = $nCoin;
            }

            $getDot     = sprintf('%.4f', $consumeCoin * ($nRatio / 100));
            $getFreeDot = round($nDot - $getDot, 4);

            $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin
where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin';
            $this->db->execute($sql, [
                'consume_free_coin' => $consumeFreeCoin,
                'consume_coin'      => $consumeCoin,
                'user_id'           => $nUserId,
            ]);
            if ( $this->db->affectedRows() <= 0 ) {
                // 赠送币 不够钱
                $this->db->rollback();
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }

            $randomKey = strtoupper(createNoncestr(6));

            $oUserWechatLog                          = new UserWechatLog();
            $oUserWechatLog->wechat_log_user_id      = $nUserId;
            $oUserWechatLog->wechat_log_value        = $oSaleUser->user_v_wechat;
            $oUserWechatLog->wechat_log_price        = $nCoin;
            $oUserWechatLog->wechat_log_ratio        = $nRatio;
            $oUserWechatLog->wechat_log_dot          = $nDot;
            $oUserWechatLog->wechat_log_sale_user_id = $saleUserId;
            $oUserWechatLog->wechat_log_key          = $randomKey;

            if ( $oUserWechatLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserWechatLog->getMessage()),
                    ResponseError::OPERATE_FAILED
                );
            }
            // 记录用户流水
            $oUserFinanceLog                   = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id          = $nUserId;

            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::WECHAT_PAY;
            $oUserFinanceLog->consume                = -$nCoin;
            $oUserFinanceLog->remark                 = '购买微信';
            $oUserFinanceLog->flow_id                = $oUserWechatLog->wechat_log_id;
            $oUserFinanceLog->flow_number            = '';
            $oUserFinanceLog->type                   = 1;
            $oUserFinanceLog->group_id               = $groupId;
            $oUserFinanceLog->target_user_id         = $saleUserId;
            $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
            $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserFinanceLog->getMessage()),
                    ResponseError::OPERATE_FAILED
                );
            }

            if ( $nDot > 0 ) {

                // 给主播/摄影师充钱
                $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
                $this->db->execute($sql, [
                    'total_dot'    => $nDot,
                    'get_dot'      => $getDot,
                    'get_free_dot' => $getFreeDot,
                    'user_id'      => $saleUserId,
                ]);
                if ( $this->db->affectedRows() <= 0 ) {
                    $this->db->rollback();
                    throw new Exception(
                        ResponseError::getError(ResponseError::OPERATE_FAILED) . '1',
                        ResponseError::OPERATE_FAILED
                    );
                }

                $oSaleUser = User::findFirst($saleUserId);

                // 记录主播/摄影师流水
                $oUserFinanceLog                      = new UserFinanceLog();
                $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
                $oUserFinanceLog->user_id             = $saleUserId;
                $oUserFinanceLog->user_current_amount = $oSaleUser->user_dot + $nDot;
                $oUserFinanceLog->user_last_amount    = $oSaleUser->user_dot;
                $oUserFinanceLog->consume_category_id = $oSaleUser->user_is_anchor == 'Y' ? UserConsumeCategory::WECHAT_INCOME : UserConsumeCategory::PHOTOGRAPHER_WECHAT_INCOME;
                $oUserFinanceLog->consume             = +$nDot;
                $oUserFinanceLog->remark              = '微信收益';
                $oUserFinanceLog->flow_id             = $oUserWechatLog->wechat_log_id;
                $oUserFinanceLog->flow_number         = '';
                $oUserFinanceLog->type                = 1;
                $oUserFinanceLog->group_id            = $groupId;
                $oUserFinanceLog->consume_source      = -$nCoin;
                $oUserFinanceLog->target_user_id      = $nUserId;
                if ( $oUserFinanceLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserFinanceLog->getMessage()),
                        ResponseError::OPERATE_FAILED
                    );
                }

//            if ( $groupId ) {
//                // 有公会的主播  需要给公会长加钱
//                $oGroup = Group::findFirst($groupId);
//                if ( $oGroup ) {
//                    $divid_type    = $oGroup->divid_type;
//                    $divid_precent = $oGroup->divid_precent;
//                    if ( $divid_type == 0 ) {
//                        //主播收益分成
//                        $groupMoney = round($nDot * $divid_precent / 100, 2);
//                    } else {
//                        //主播流水分成  还需要除以一个 充值比例转换值 10
//                        $groupMoney = round($nCoin * $divid_precent / 100 / 10, 2);
//                    }
//                    $sql = 'update `group` set money = money + :money where id = :group_id';
//                    $this->db->execute($sql, [
//                        'money'    => $groupMoney,
//                        'group_id' => $groupId,
//                    ]);
//                }
//            }

            }
            $oSaleUser->user_wechat_sale_count += 1;
            $oSaleUser->save();

            $this->db->commit();
            // 发送关注消息
            $this->sendBuyWechatMsg($oSaleUser->user_id, $oUser,$randomKey);
            $oUser = User::findFirst($nUserId);
            $row   = [
                'user_coin'  => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
                'wechat'     => $oSaleUser->user_v_wechat,
                'secret_key' => $randomKey,
            ];

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($oSaleUser->user_id);
            $oAnchorStatService->save(AnchorStatService::WECHAT_INCOME,$nDot);

            if($nCoin){
                // 开始# 亲密值
                $intimateMultiple = Kv::get(Kv::COIN_TO_INTIMATE) ?? 1;
                $intimateValue    = $nCoin * $intimateMultiple;
                if ( $intimateValue > 0 ) {
                    $oUserIntimateLog                              = new UserIntimateLog();
                    $oUserIntimateLog->intimate_log_user_id        = $nUserId;
                    $oUserIntimateLog->intimate_log_anchor_user_id = $oSaleUser->user_id;
                    $oUserIntimateLog->intimate_log_type           = UserIntimateLog::TYPE_BUY_WECHAT;
                    $oUserIntimateLog->intimate_log_value          = $nCoin * $intimateMultiple;
                    $oUserIntimateLog->intimate_log_coin           = $nCoin;
                    $oUserIntimateLog->intimate_log_dot            = $nDot;
                    $oUserIntimateLog->save();
                }
                // 结束# 亲密值
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.1hjp.com/v1/user/wechat/log
     * @api {get} /user/wechat/log 微信购买记录
     * @apiName wechat-log
     * @apiGroup Profile
     * @apiDescription 微信购买记录
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String='buy(我购买的)','sale(我出售的)'} type 类型
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String='buy(我购买的)','sale(我出售的)'} type 类型
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {String} d.items.wechat_log_id   记录id
     * @apiSuccess {String} d.items.wechat_log_value   微信号
     * @apiSuccess {String} d.items.wechat_log_key     暗号
     * @apiSuccess {String='Y','N'} d.items.check_flg    是否已完成
     * @apiSuccess {number} d.items.user_id     用户id
     * @apiSuccess {String} d.items.user_nickname   用户昵称
     * @apiSuccess {String} d.items.user_avatar  用户头像
     * @apiSuccess {String} d.items.user_birth   生日
     * @apiSuccess {String} d.items.user_is_member   是否VIP
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "items": [
     *                 {
     *                     "wechat_log_id": "1",
     *                     "wechat_log_value": "TTbaby02",
     *                     "wechat_log_key": "8A99LG",
     *                     "check_flg": "N",
     *               *           "user_id": "13",
     *                     "user_nickname": "阿肖",
     *                     "user_avatar": "https://cskj-1257854899.image.myqcloud.com/image/2019/01/09/1547024017412.png",
     *                     "user_birth": "",
     *                     "user_is_member": "N"
     *                 }
     *             ],
     *             "page": 1,
     *             "pagesize": 20,
     *             "pagetotal": 1,
     *             "total": 1,
     *             "prev": 1,
     *             "next": 1
     *         },
     *         "t": 1554195768
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function logAction($nUserId = 0)
    {

        $type      = $this->getParams('type', 'string', 'buy');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $nPagesize = min($nPagesize, 100);
        try {

            if ( $type == 'buy' ) {
                $columns = 'l.wechat_log_id,l.wechat_log_value,l.wechat_log_key,wechat_log_user_check as check_flg,u.user_id,u.user_nickname,u.user_avatar,
                u.user_birth,u.user_member_expire_time';
                $builder = $this->modelsManager->createBuilder()
                    ->from([ 'l' => UserWechatLog::class ])
                    ->columns($columns)
                    ->join(User::class, 'u.user_id = l.wechat_log_sale_user_id', 'u')
                    ->where('l.wechat_log_user_id = :user_id:', [ 'user_id' => $nUserId ])
                    ->orderBy('l.wechat_log_update_time desc');
            } else {
                $columns = 'l.wechat_log_id,l.wechat_log_value,l.wechat_log_key,wechat_log_sale_check as check_flg,u.user_id,u.user_nickname,u.user_avatar,
                u.user_birth,u.user_member_expire_time';
                $builder = $this->modelsManager->createBuilder()
                    ->from([ 'l' => UserWechatLog::class ])
                    ->columns($columns)
                    ->join(User::class, 'u.user_id = l.wechat_log_user_id', 'u')
                    ->where('l.wechat_log_sale_user_id = :user_id:', [ 'user_id' => $nUserId ])
                    ->orderBy('l.wechat_log_update_time desc');
            }

            $row = $this->page($builder, $nPage, $nPagesize);
            foreach ( $row['items'] as &$item ) {
                $item['user_is_member'] = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
                unset($item['user_member_expire_time']);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.yuyin-tv.com/v1/user/wechat/setCheck
     * @api {post} /user/wechat/setCheck 微信购买-设置已完成
     * @apiName wechat-setCheck
     * @apiGroup Profile
     * @apiDescription 设置已完成
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} wechat_log_id 记录id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} wechat_log_id 记录id
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
    public function setCheckAction($nUserId = 0)
    {
        $nWechatLogId = $this->getParams('wechat_log_id', 'int');
        try {
            $oUserWechatLog = UserWechatLog::findFirst($nWechatLogId);
            if ( !$oUserWechatLog || !in_array($nUserId, [
                    $oUserWechatLog->wechat_log_user_id,
                    $oUserWechatLog->wechat_log_sale_user_id
                ]) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            if ( $nUserId == $oUserWechatLog->wechat_log_user_id ) {
                if ( $oUserWechatLog->wechat_log_user_check == 'N' ) {
                    $oUserWechatLog->wechat_log_user_check = 'Y';
                    $oUserWechatLog->save();
                }
            } else {
                if ( $oUserWechatLog->wechat_log_sale_check == 'N' ) {
                    $oUserWechatLog->wechat_log_sale_check = 'Y';
                    $oUserWechatLog->save();
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


}