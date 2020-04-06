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

use app\models\User;
use app\models\UserAccount;
use app\models\UserCashLog;
use app\models\UserConsumeCategory;
use app\models\UserDiamondLog;
use app\models\UserExchangeCombo;
use app\models\UserExchangeLog;
use app\models\UserFinanceLog;
use Exception;

use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
 * ExchangeController 兑换控制器
 */
class ExchangeController extends ControllerBase
{
    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/exchange/index
     * @api {get} /user/exchange/index 20191128-兑换套餐列表
     * @apiName exchange-index
     * @apiGroup Exchange
     * @apiDescription 兑换套餐列表
     * @apiParam (正常请求){String='cash(推广佣金)','diamond(钻石)'} category='cash' 类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.list  兑换套餐
     * @apiSuccess {number} d.list.id 套餐id
     * @apiSuccess {number} d.list.combo_id 套餐id
     * @apiSuccess {number} d.list.combo_cash  支付“现金”、钻石
     * @apiSuccess {number} d.list.combo_coin 获得金币
     * @apiSuccess {String='cash(推广佣金)','diamond(钻石)'} d.list.exchange_category 类型
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *  {
     *      "c": 0,
     *      "m": "请求成功",
     *      "d": {
     *              "list": [
     *              {
     *                  "id": "7",
     *                  "combo_id": "7",
     *                  "combo_coin": "42",
     *                  "combo_cash": "6.00",
     *                  "create_time": "1521186096",
     *                  "update_time": "1538220580"
     *              },
     *              {
     *                  "id": "8",
     *                  "combo_id": "8",
     *                  "combo_coin": "166",
     *                  "combo_cash": "18.00",
     *                  "create_time": "1521186105",
     *                  "update_time": "1539598829"
     *              }
     *          ]
     *      },
     *      "t": "1539850095"
     *  }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction( $nUserId = 0 )
    {
        $sCategory = $this->getParams('category', 'string', 'cash');
        try {
            if ( !in_array($sCategory, [
                UserExchangeCombo::CATEGORY_CASH,
                UserExchangeCombo::CATEGORY_DIAMOND
            ]) ) {
                $sCategory = UserExchangeCombo::CATEGORY_CASH;
            }
            $oUserRechargeCombo = UserExchangeCombo::find([
                'exchange_category = :exchange_category:',
                'bind'    => [
                    'exchange_category' => $sCategory
                ],
                'order'   => 'combo_coin asc',
                'columns' => 'id,id as combo_id,combo_coin,combo_cash,combo_cash,create_time,update_time,exchange_category'
            ]);
            $row                = [
                'list' => $oUserRechargeCombo
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/exchange/add
     * @api {post} /user/exchange/add 20191128-兑换金币
     * @apiName exchange-add
     * @apiGroup Exchange
     * @apiDescription 兑换金币
     * @apiParam (正常请求){String} combo_id 套餐id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string} d.user_cash 剩余“现金”
     * @apiSuccess {string} d.user_diamond 剩余钻石
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "user_cash" : "100",
     *              "user_diamond" : "100"
     *          },
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
    public function addAction( $nUserId = 0 )
    {
        $nComboId = $this->getParams('combo_id', 'int', 0);
        try {
            $oUserExchangeCombo = UserExchangeCombo::findFirst([
                "id=:id:",
                'bind' => [ 'id' => $nComboId ]
            ]);
            if ( $oUserExchangeCombo === FALSE ) {
                throw new Exception(sprintf('recharge_combo_id %s', ResponseError::getError(ResponseError::PARAM_ERROR)), ResponseError::PARAM_ERROR);
            }
            $aTime        = explode('.', sprintf('%.10f', microtime(TRUE)));
            $sOrderNumber = date('YmdHis', $aTime[0]) . '000' . $aTime[1] . mt_rand(10000, 99999);
            $nCoin        = $oUserExchangeCombo->combo_coin;
            $nCash        = $oUserExchangeCombo->combo_cash;
            // 添加订单
            $oUserExchangeOrder = new UserExchangeLog();
            $this->db->begin();
            $oUserExchangeOrder->user_id           = $nUserId;
            $oUserExchangeOrder->combo_coin        = $nCoin;
            $oUserExchangeOrder->combo_cash        = $oUserExchangeCombo->combo_cash;
            $oUserExchangeOrder->order_number      = $sOrderNumber;
            $oUserExchangeOrder->exchange_category = $oUserExchangeCombo->exchange_category;
            if ( $oUserExchangeOrder->save() === FALSE ) {
                $this->db->rollback();
                throw new \Phalcon\Db\Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserExchangeOrder->getMessages())), ResponseError::OPERATE_FAILED);
            }
            $oUser       = User::findFirst($nUserId);
            $saveCash    = 0;
            $saveDiamond = 0;
            if ( $oUserExchangeCombo == UserExchangeCombo::CATEGORY_CASH ) {
                $saveCash                = $nCash;
                $oUserFinanceLogRemark   = '现金兑换金币';
                $oUserFinanceLogCategory = UserConsumeCategory::CASH_EXCHANGE;
                // 记录“现金”流水
                $oUserCashLog                      = new UserCashLog();
                $oUserCashLog->user_id             = $nUserId;
                $oUserCashLog->consume_category    = UserCashLog::CATEGORY_EXCHANGE;
                $oUserCashLog->user_current_amount = $oUser->user_cash;
                $oUserCashLog->user_last_amount    = $oUser->user_cash - $nCash;
                $oUserCashLog->consume             = -$nCash;
                $oUserCashLog->remark              = '兑换金币';
                $oUserCashLog->flow_id             = $oUserExchangeOrder->id;
                $oUserCashLog->flow_number         = $sOrderNumber;
                $oUserCashLog->target_user_id      = 0;
                $flg                               = $oUserCashLog->save();
                if ( $flg == FALSE ) {
                    $this->db->rollback();
                    return FALSE;
                }
            } else {
                $saveDiamond             = $nCash;
                $oUserFinanceLogRemark   = '钻石兑换金币';
                $oUserFinanceLogCategory = UserConsumeCategory::DIAMOND_EXCHANGE;
                // 记录“现金”流水
                $oUserDiamondLog                      = new UserDiamondLog();
                $oUserDiamondLog->user_id             = $nUserId;
                $oUserDiamondLog->consume_category    = UserDiamondLog::CATEGORY_EXCHANGE;
                $oUserDiamondLog->user_current_amount = $oUser->user_diamond;
                $oUserDiamondLog->user_last_amount    = $oUser->user_diamond - $nCash;
                $oUserDiamondLog->consume             = -$nCash;
                $oUserDiamondLog->remark              = '兑换金币';
                $oUserDiamondLog->flow_id             = $oUserExchangeOrder->id;
                $oUserDiamondLog->flow_number         = $sOrderNumber;
                $flg                                  = $oUserDiamondLog->save();
                if ( $flg == FALSE ) {
                    $this->db->rollback();
                    return FALSE;
                }
            }

            // 记录用户流水
            $oUserFinanceLog                         = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type       = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id                = $nUserId;
            $oUserFinanceLog->user_current_amount    = $nUserId->user_coin + $nUserId->user_free_coin;
            $oUserFinanceLog->user_last_amount       = $oUserFinanceLog->user_current_amount + $nCoin;
            $oUserFinanceLog->consume_category_id    = $oUserFinanceLogCategory;
            $oUserFinanceLog->consume                = +$nCoin;
            $oUserFinanceLog->remark                 = $oUserFinanceLogRemark;
            $oUserFinanceLog->flow_id                = $oUserExchangeOrder->id;
            $oUserFinanceLog->flow_number            = $sOrderNumber;
            $oUserFinanceLog->type                   = 0;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin + $nCoin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            $flg                                     = $oUserFinanceLog->save();
            if ( $flg == FALSE ) {
                $this->db->rollback();
                return FALSE;
            }
            $sql = 'update `user` set user_free_coin = user_free_coin + :coin,user_total_free_coin = user_total_free_coin + :coin,
user_invite_coin_total = user_invite_coin_total + :coin,user_cash = user_cash - :cash,user_diamond = user_diamond - :diamond
 where user_id = :user_id AND user_cash >= :cash AND user_diamond >= :diamond';
            $this->db->execute($sql, [
                'coin'    => $nCoin,
                'cash'    => $saveCash,
                'diamond' => $saveDiamond,
                'user_id' => $nUserId
            ]);
            if ( $this->db->affectedRows() <= 0 ) {
                $this->db->rollback();
                return FALSE;
            }
            $this->db->commit();
            $oUser = User::findFirst($nUserId);
            $row   = [
                'user_cash'    => $oUser->user_cash,
                'user_diamond' => $oUser->user_diamond,
                'user_coin'    => $oUser->user_coin + $oUser->user_free_coin
            ];

        } catch ( \Phalcon\Db\Exception $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( \PDOException $e ) {
            $this->error(ResponseError::OPERATE_FAILED, ResponseError::getError(ResponseError::OPERATE_FAILED));
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/exchange/log
     * @api {get} /user/exchange/log 20191128-兑换记录
     * @apiName exchange-log
     * @apiGroup Exchange
     * @apiDescription 兑换记录
     * @apiParam (正常请求){String='cash(推广佣金)','diamond(钻石)'} category='cash' 类型
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.id  id
     * @apiSuccess {number} d.items.user_id  用户id
     * @apiSuccess {number} d.items.combo_coin  兑换金币
     * @apiSuccess {number} d.items.combo_cash  使用“现金”
     * @apiSuccess {number} d.items.create_time  创建时间戳
     * @apiSuccess {number} d.items.update_time
     * @apiSuccess {number} d.items.order_number  订单号
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
     *                 "list": {
     *                     "items": [
     *                     {
     *                         "id": "1",
     *                         "user_id": "175",
     *                         "combo_coin": "100",
     *                         "combo_cash": "100.00",
     *                         "create_time": "0",
     *                         "update_time": "0",
     *                         "order_number": "1232132132131"
     *                     }
     *                 ],
     *                 "page": 1,
     *                 "pagesize": 100,
     *                 "pagetotal": 1,
     *                 "total": 1,
     *                 "prev": 1,
     *                 "next": 1
     *             }
     *         },
     *         "t": "1539850294"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     *
     */
    public function logAction( $nUserId = 0 )
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $sCategory = $this->getParams('category', 'string', 'cash');
        try {
            if ( !in_array($sCategory, [
                UserExchangeLog::CATEGORY_CASH,
                UserExchangeLog::CATEGORY_DIAMOND
            ]) ) {
                $sCategory = UserExchangeLog::CATEGORY_CASH;
            }
            $builder = $this->modelsManager->createBuilder()->from(UserExchangeLog::class)
                ->where('user_id=:user_id: AND exchange_category = :exchange_category:',
                    [
                        'user_id'           => $nUserId,
                        'exchange_category' => $sCategory
                    ]
                )
                ->orderBy('id desc');
            $row     = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/exchange/cashlog
     * @api {get} /user/exchange/cashlog "现金"流水
     * @apiName exchange-cashlog
     * @apiGroup Exchange
     * @apiDescription "现金"流水
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数
     * @apiParam (正常请求) {String='incr(增加)','decr(减少)',''} type  类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String='incr(增加)','decr(减少)',''} type  类型
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {String} d.items.user_nickname  用户昵称
     * @apiSuccess {String} d.items.remark  备注
     * @apiSuccess {number} d.items.consume  “现金” 值
     * @apiSuccess {number} d.items.create_time  创建时间戳
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "items": [
     *                   {
     *                       "user_nickname": "",
     *                       "remark": "",
     *                       "consume": "100.00",
     *                       "create_time": "1539850765"
     *                   }
     *               ],
     *               "page": 1,
     *               "pagesize": 10,
     *               "pagetotal": 1,
     *               "total": 1,
     *               "prev": 1,
     *               "next": 1
     *           },
     *           "t": "1539938017"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function cashlogAction( $nUserId = 0 )
    {

        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $nType     = $this->getParams('type', 'string');
        try {
            $builder = $this->modelsManager->createBuilder()->from([ 'l' => UserCashLog::class ])
                ->leftJoin(User::class, 'u.user_id = l.target_user_id', 'u')
                ->columns('ifnull(u.user_nickname,"") as user_nickname,l.remark,l.consume,l.create_time')
                ->where('l.user_id=:user_id:', [ 'user_id' => $nUserId ])->orderBy('l.id desc');
            if ( $nType == 'incr' ) {
                $builder->andWhere('l.consume > 0');
            } elseif ( $nType == 'decr' ) {
                $builder->andWhere('l.consume < 0');
            }
            $row = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/exchange/diamondlog
     * @api {get} /user/exchange/diamondlog 钻石流水
     * @apiName exchange-diamondlog
     * @apiGroup Exchange
     * @apiDescription 钻石流水
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数
     * @apiParam (正常请求) {String='incr(增加)','decr(减少)',''} type  类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {String} d.items.user_nickname  用户昵称
     * @apiSuccess {String} d.items.remark  备注
     * @apiSuccess {number} d.items.consume  钻石值
     * @apiSuccess {number} d.items.create_time  创建时间戳
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "items": [
     *                   {
     *                       "user_nickname": "",
     *                       "remark": "",
     *                       "consume": "100.00",
     *                       "create_time": "1539850765"
     *                   }
     *               ],
     *               "page": 1,
     *               "pagesize": 10,
     *               "pagetotal": 1,
     *               "total": 1,
     *               "prev": 1,
     *               "next": 1
     *           },
     *           "t": "1539938017"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function diamondlogAction( $nUserId = 0 )
    {

        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $nType     = $this->getParams('type', 'string');
        try {
            $builder = $this->modelsManager->createBuilder()->from([ 'l' => UserDiamondLog::class ])
                ->leftJoin(User::class, 'u.user_id = l.user_id', 'u')
                ->columns('u.user_nickname,l.remark,l.consume,l.create_time')
                ->where('l.user_id=:user_id:', [ 'user_id' => $nUserId ])->orderBy('l.id desc');
            if ( $nType == 'incr' ) {
                $builder->andWhere('l.consume > 0');
            } elseif ( $nType == 'decr' ) {
                $builder->andWhere('l.consume < 0');
            }
            $row = $this->page($builder, $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

}