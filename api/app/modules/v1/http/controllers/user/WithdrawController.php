<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 提现控制器                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;


use app\models\UserCashLog;
use Exception;
use PDOException;

use app\models\Kv;
use app\models\User;
use app\models\VerifyCode;
use app\models\UserAccount;
use app\helper\ResponseError;
use app\models\UserFinanceLog;
use app\models\UserWithdrawLog;
use app\models\UserCertification;
use app\models\UserWithdrawAccount;
use app\models\UserConsumeCategory;
use app\http\controllers\ControllerBase;
use app\models\UserWithdrawAccountCategory;

/**
 * WithdrawController 提现
 */
class WithdrawController extends ControllerBase
{
    use \app\services\SystemMessageService;

    /**
     * indexAction 我的提现
     *
     * @param  int $nUserId
     */
    public function indexAction($nUserId = 0)
    {
        try {
            $oUser = User::findFirst($nUserId);

            $oUserWithdrawAccount = UserWithdrawAccount::findFirst([
                'user_id=:user_id: ORDER BY user_withdraw_account_update_time DESC',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);

            $oUserWithdrawAccountCategory = UserWithdrawAccountCategory::findFirst($oUserWithdrawAccount ? $oUserWithdrawAccount->user_withdraw_account_category_id : 1);

            $oUser = User::findFirst($nUserId);
//			$nRatio = $oUser->user_withdraw_ratio ?: Kv::get(Kv::KEY_DOT_TO_MONEY_RATIO);
            $nRatio = 100;

            if ( $nRatio <= 0 || $nRatio > 100 ) {
                $nRatio = 100;
            }

            $row['user']['cash']     = (string)(floor($oUser->user_dot * ($nRatio / 100) * 100) / 100);
            $row['user']['pay']      = $oUserWithdrawAccountCategory->user_withdraw_account_category_name;
            $row['user']['account']  = $oUserWithdrawAccount ? $oUserWithdrawAccount->user_withdraw_account : '';
            $row['user']['realname'] = $oUserWithdrawAccount ? $oUserWithdrawAccount->user_withdraw_account_realname : '';

            // 设置用户的提现ID
            $aTime       = explode('.', sprintf('%.20f', microtime(1)));
            $withdraw_id = date('YmdHis', $aTime[0]) . $aTime[1];

            $this->cookies->set(
                '_WITHDRAW_ID',
                $withdraw_id,
                $aTime[0] + 60 * 60 * 24
            );
            $row['user']['withdraw_id'] = $withdraw_id;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * addAction 添加提现
     *
     * @param int $nUserId
     */
    public function addAction($nUserId = 0)
    {
        $is_freeze = Kv::get(Kv::FREEZE_WITHDRAWALS);
        if ( $is_freeze ) {
            $this->error(10053);
        }
        $sPay                   = $this->getParams('pay', 'string', '');
        $sCash                  = $this->getParams('cash', 'string', '');
        $sCode                  = $this->getParams('code', 'string', '');
        $sAccount               = $this->getParams('account', 'string', '');
        $sName                  = $this->getParams('name', 'string', '');
        $sUserWithdrawLogMumber = $this->cookies->get('_WITHDRAW_ID')->getValue();
        if ( empty($sUserWithdrawLogMumber) ) {
            $sUserWithdrawLogMumber = $this->getParams('withdraw_id', 'string', '');
        }
        $aTime                  = explode('.', sprintf('%.20f', microtime(1)));
        $sUserWithdrawLogMumber = date('YmdHis', $aTime[0]) . $aTime[1];

        try {
//            if(date('D') != 'Sun'){
//                throw new Exception(
//                    sprintf('%s', ResponseError::getError(ResponseError::WITHDRAW_ADD_DAY_ERROR)),
//                    ResponseError::WITHDRAW_ADD_DAY_ERROR
//                );
//            }
            if ( $sUserWithdrawLogMumber == '' ) {
                throw new Exception(
                    sprintf('_WITHDRAW_ID %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            if ( $sAccount == '' || ((!is_numeric($sAccount) && strlen($sAccount) != 11) && !filter_var($sAccount, FILTER_VALIDATE_EMAIL)) ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::ACCOUNT_ERROR)),
                    ResponseError::ACCOUNT_ERROR
                );
            }
            if ( $sCode == '' ) {
                throw new Exception(
                    sprintf('code %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            if ( $sCash == '' ) {
                throw new Exception(
                    sprintf('cash %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            $oUserWithdrawAccountCategory = UserWithdrawAccountCategory::findFirst([
                'user_withdraw_account_category_name=:name:',
                'bind' => [
                    'name' => $sPay,
                ],
            ]);

            if ( !$oUserWithdrawAccountCategory ) {
                throw new Exception(
                    sprintf('pay %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            // 判断验证码
            $oUserAccount = UserAccount::findFirst($nUserId);
            VerifyCode::judgeVerify($oUserAccount->user_phone, VerifyCode::TYPE_WITHDRAW, $sCode);

            $oUserWithdrawAccount = UserWithdrawAccount::findFirst([
                'user_id=:user_id: AND user_withdraw_account=:user_withdraw_account: AND user_withdraw_account_category_id=:user_withdraw_account_category_id:',
                'bind' => [
                    'user_id'                           => $nUserId,
                    'user_withdraw_account'             => $sAccount,
                    'user_withdraw_account_category_id' => $oUserWithdrawAccountCategory->user_withdraw_account_category_id,
                ]
            ]);

            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            if ( !$sName ) {
                $sName = isset($oUserCertification->user_realname) ? $oUserCertification->user_realname : '';
            }

            // 初始化账号
            if ( !$oUserWithdrawAccount ) {
                $oUserWithdrawAccount                                    = new UserWithdrawAccount();
                $oUserWithdrawAccount->user_id                           = $nUserId;
                $oUserWithdrawAccount->user_withdraw_account             = $sAccount;
                $oUserWithdrawAccount->user_withdraw_account_realname    = $sName;
                $oUserWithdrawAccount->user_withdraw_account_category_id = $oUserWithdrawAccountCategory->user_withdraw_account_category_id;
            } else if ( $oUserWithdrawAccount->user_withdraw_account_realname != $sName ) {
                $oUserWithdrawAccount->user_withdraw_account_realname = $sName;
            }

            if ( $oUserWithdrawAccount->save() === FALSE ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserWithdrawAccount->getMessages()),
                    ResponseError::OPERATE_FAILED
                );
            }

            $oUser = User::findFirst($nUserId);
//			$nRatio = $oUser->user_withdraw_ratio ?: Kv::get(Kv::KEY_DOT_TO_MONEY_RATIO);
            $nRatio = 100;
            if ( $nRatio <= 0 || $nRatio > 100 ) {
                $nRatio = 100;
            }

            $nDot = ($sCash / ($nRatio / 100));

            $oUser->user_dot -= $nDot;
            if ( $oUser->user_dot < 0 ) {
                throw new Exception(ResponseError::getError(ResponseError::OPERATE_FAILED), ResponseError::OPERATE_FAILED);
            }

            $this->db->begin();

            if ( $oUser->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUser->getMessages()),
                    ResponseError::OPERATE_FAILED
                );
            }

            $serviceChargeInfo = $this->_calculateServiceCharge($sPay, $sCash);

            $oUserWithdrawLog                                 = new UserWithdrawLog();
            $oUserWithdrawLog->user_withdraw_log_number       = $sUserWithdrawLogMumber;
            $oUserWithdrawLog->user_withdraw_pay              = $sPay;
            $oUserWithdrawLog->user_withdraw_account          = $sAccount;
            $oUserWithdrawLog->user_id                        = $nUserId;
            $oUserWithdrawLog->user_dot                       = $nDot;
            $oUserWithdrawLog->withdraw_ratio                 = $nRatio;
            $oUserWithdrawLog->user_withdraw_cash             = $sCash - $serviceChargeInfo['serviceCharge'];
            $oUserWithdrawLog->service_charge                 = $serviceChargeInfo['serviceCharge'];
            $oUserWithdrawLog->user_realname                  = $sName;
            $oUserWithdrawLog->user_withdraw_log_check_status = 'C';

            if ( $oUserWithdrawLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserWithdrawLog->getMessages()),
                    ResponseError::OPERATE_FAILED
                );
            }

            // 添加流水
            $oUserFinanceLog                      = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
            $oUserFinanceLog->user_id             = $nUserId;
            $oUserFinanceLog->user_current_amount = $oUser->user_dot;
            $oUserFinanceLog->user_last_amount    = $oUser->user_dot + $nDot;
            $oUserFinanceLog->consume_category_id = UserConsumeCategory::CATEGORY_WITHDRAW;
            $oUserFinanceLog->consume             = -$nDot;
            $oUserFinanceLog->remark              = '提现扣除收益';
            $oUserFinanceLog->flow_id             = $oUserWithdrawLog->user_withdraw_log_id;
            $oUserFinanceLog->flow_number         = $oUserWithdrawLog->user_withdraw_log_number;

            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }


            VerifyCode::delVerify($oUserAccount->user_phone, VerifyCode::TYPE_WITHDRAW);

            $this->db->commit();

            // 发送提现系统消息
            $this->sendWithdrawMsg($oUser->user_id, $oUserWithdrawLog);

            $row['user']['user_dot']   = sprintf('%.2f', $oUser->user_dot);
            $row['withdraw_log']['id'] = $oUserWithdrawLog->user_withdraw_log_id;

            // 设置用户的提现ID
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );

        } catch ( \Phalcon\Db\Exception $e ) {
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );
            $this->error($e->getCode(), $e->getMessage());
        } catch ( PDOException $e ) {
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );
            $this->error($e->getCode(), $e->getMessage());
        } catch ( Exception $e ) {
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * logAction 提现记录
     *
     * @param  int $nUserId
     */
    public function logAction($nUserId = 0)
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {

            $builder = $this->modelsManager
                ->createBuilder()
                ->from(UserWithdrawLog::class)
                ->where('user_id=:user_id: AND dot_type = :dot_type:', [
                    'user_id'  => $nUserId,
                    'dot_type' => 'dot'
                ])
                ->orderBy('user_withdraw_log_id desc');

            $row['withdraw_log'] = $this->page($builder->columns('user_withdraw_log_id id,user_withdraw_log_create_time time,user_withdraw_log_check_status status,user_withdraw_account account,user_withdraw_pay pay,user_withdraw_cash cash'), $nPage, $nPagesize);

            $row['withdraw_total'] = $builder->columns('sum(user_withdraw_cash) total')
                ->andWhere('user_withdraw_log_check_status IN ("Y","C")')
                ->getQuery()
                ->execute()
                ->getFirst()
                ->total;

            $row['withdraw_total'] = sprintf('%.2f', $row['withdraw_total']);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * detailAction 提现详情
     *
     * @param  int $nUserId
     * @return
     */
    public function detailAction($nUserId = 0)
    {
        $nWithdrawLogId = $this->getParams('withdraw_log_id', 'int', 0);

        try {
            $oUserWithdrawLog = UserWithdrawLog::findFirst($nWithdrawLogId);

            $row['status'] = $oUserWithdrawLog->user_withdraw_log_check_status;

            $row['info'] = [
                [
                    'title'  => '提现申请已提交，每周三0点到24点统一审核打款，遇节假日顺延，请耐心等待 ~',
                    'remark' => sprintf('支付宝账号(%s)', $oUserWithdrawLog->user_withdraw_account),
                    'cash'   => sprintf('%.2f元', $oUserWithdrawLog->user_withdraw_cash),
                ],
                [
                    'title'  => sprintf('预计%s日到账', date('Y-m-d', strtotime(date('Y-m-d', $oUserWithdrawLog->user_withdraw_log_create_time) . '+2 day'))),
                    'remark' => '如信息填写错误，请及时联系客服',
                ]
            ];

            switch ( $row['status'] ) {
                case 'Y':
                    $row['info'][] = [
                        'title'  => '提现成功',
                        'remark' => '',
                    ];
                    break;

                case 'N':
                    $row['info'][] = [
                        'title'  => '提现失败',
                        'remark' => '',
                    ];
                    break;
                case 'C':
                default:
                    break;
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);

    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/withdraw/calculateServiceCharge
     * @api {post} /user/withdraw/calculateServiceCharge 手续费计算
     * @apiName 手续费计算
     * @apiGroup Profile
     * @apiDescription 手续费计算
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){Number} cash 金额
     * @apiParam (正常请求){String} pay 提现类型
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} cash 金额
     * @apiParam (debug){String} pay 提现类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "notice": "提现少于1000元收取手续费2元，超过1000部分,加收超过部分0.015%",
     *             "service_charge": "2.01"
     *         },
     *         "t": "1539747831"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function calculateServiceChargeAction($nUserId = 0)
    {

        $sPay  = $this->getParams('pay', 'string', '');
        $sCash = $this->getParams('cash', 'string', '0');

        try {
            $serviceChargeInfo = $this->_calculateServiceCharge($sPay, $sCash);
            $row               = [
                'notice'         => $serviceChargeInfo['notice'],
                'service_charge' => (string)$serviceChargeInfo['serviceCharge'],
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    private function _calculateServiceCharge($sPay, $sCash)
    {
        //手续费先写固定数据
//        $minCash          = 1000;
//        $minServiceCharge = 2;
//        $overRatio        = 0.0015;
        $minCash          = 0;
        $minServiceCharge = 0;
        $overRatio        = 0;
        $notice = '提现少于1000元收取手续费2元，超过1000部分,加收超过部分0.015%';
        if ( $sCash < $minServiceCharge ) {
            throw new Exception(
                ResponseError::getError(ResponseError::OPERATE_FAILED),
                ResponseError::OPERATE_FAILED
            );
        }
        $serviceCharge = $minServiceCharge;
        if ( $sCash > $minCash ) {
            $serviceCharge += round(($sCash - $minCash) * $overRatio, 2);
        }
        return [
            'notice'        => $notice,
            'serviceCharge' => $serviceCharge
        ];
    }


    /**
     * cashAction “现金”提现
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/withdraw/cash
     * @api {get} /user/withdraw/cash 现金提现信息
     * @apiName withdraw-cash
     * @apiGroup Profile
     * @apiDescription 现金提现信息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.user
     * @apiSuccess {number} d.user.cash   用户可提现余额
     * @apiSuccess {String} d.user.pay   支付类型
     * @apiSuccess {String} d.user.account  支付账号
     * @apiSuccess {String} d.user.realname  真实姓名
     * @apiSuccess {number} d.user.withdraw_id  订单id
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
    public function cashAction($nUserId = 0)
    {
        try {
            $oUser = User::findFirst($nUserId);

            $oUserWithdrawAccount = UserWithdrawAccount::findFirst([
                'user_id=:user_id: ORDER BY user_withdraw_account_update_time DESC',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);

            $oUserWithdrawAccountCategory = UserWithdrawAccountCategory::findFirst($oUserWithdrawAccount ? $oUserWithdrawAccount->user_withdraw_account_category_id : 1);

            $oUser = User::findFirst($nUserId);
//			$nRatio = $oUser->user_withdraw_ratio ?: Kv::get(Kv::KEY_DOT_TO_MONEY_RATIO);
            $nRatio = 100;

            if ( $nRatio <= 0 || $nRatio > 100 ) {
                $nRatio = 100;
            }

            $row['user']['cash']     = (string)(floor($oUser->user_cash * ($nRatio / 100) * 100) / 100);
            $row['user']['pay']      = $oUserWithdrawAccountCategory->user_withdraw_account_category_name;
            $row['user']['account']  = $oUserWithdrawAccount ? $oUserWithdrawAccount->user_withdraw_account : '';
            $row['user']['realname'] = $oUserWithdrawAccount ? $oUserWithdrawAccount->user_withdraw_account_realname : '';

            // 设置用户的提现ID
            $aTime       = explode('.', sprintf('%.20f', microtime(1)));
            $withdraw_id = date('YmdHis', $aTime[0]) . $aTime[1];

            $this->cookies->set(
                '_WITHDRAW_ID',
                $withdraw_id,
                $aTime[0] + 60 * 60 * 24
            );
            $row['user']['withdraw_id'] = $withdraw_id;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * addCashAction 添加“现金”提现
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/withdraw/addCash
     * @api {post} /user/withdraw/addCash 添加“现金”提现
     * @apiName withdraw-addCash
     * @apiGroup Profile
     * @apiDescription 添加“现金”提现
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} pay 申请账号类型
     * @apiParam (正常请求){String} cash 金额
     * @apiParam (正常请求){String} code 验证码
     * @apiParam (正常请求){String} account 账号
     * @apiParam (正常请求){String} name 真实姓名
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} pay 申请账号类型
     * @apiParam (debug){String} cash 金额
     * @apiParam (debug){String} code 验证码
     * @apiParam (debug){String} account 账号
     * @apiParam (debug){String} name 真实姓名
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {Object} d.user
     * @apiSuccess {string} d.user.user_cash  用户“现金”余额
     * @apiSuccess {Object} d.withdraw_log
     * @apiSuccess {string} d.withdraw_log.id  提现订单id
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *              "user": {
     *                  "user_cash": "1000",
     *                  },
     *                  "withdraw_log": {
     *                  "id": "1000",
     *                  },
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
    public function addCashAction($nUserId = 0)
    {
        $is_freeze = Kv::get(Kv::FREEZE_WITHDRAWALS);
        if ( $is_freeze ) {
            $this->error(10053);
        }
        $sPay                   = $this->getParams('pay', 'string', '');
        $sCash                  = $this->getParams('cash', 'string', '');
        $sCode                  = $this->getParams('code', 'string', '');
        $sAccount               = $this->getParams('account', 'string', '');
        $sName                  = $this->getParams('name', 'string', '');
        $sUserWithdrawLogMumber = $this->cookies->get('_WITHDRAW_ID')->getValue();
        if ( empty($sUserWithdrawLogMumber) ) {
            $sUserWithdrawLogMumber = $this->getParams('withdraw_id', 'string', '');
        }
        $aTime                  = explode('.', sprintf('%.20f', microtime(1)));
        $sUserWithdrawLogMumber = date('YmdHis', $aTime[0]) . $aTime[1];

        try {
            if(date('Y-m-d') > '2019-04-16' && date('D') != 'Sun' && $this->config->application->evn != 'dev'){
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::WITHDRAW_ADD_DAY_ERROR)),
                    ResponseError::WITHDRAW_ADD_DAY_ERROR
                );
            }
            if ( $sUserWithdrawLogMumber == '' ) {
                throw new Exception(
                    sprintf('_WITHDRAW_ID %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            if ( $sAccount == '' || ((!is_numeric($sAccount) && strlen($sAccount) != 11) && !filter_var($sAccount, FILTER_VALIDATE_EMAIL)) ) {
                throw new Exception(
                    sprintf('%s', ResponseError::getError(ResponseError::ACCOUNT_ERROR)),
                    ResponseError::ACCOUNT_ERROR
                );
            }
            if ( $sCode == '' ) {
                throw new Exception(
                    sprintf('code %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            if ( $sCash == '' ) {
                throw new Exception(
                    sprintf('cash %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

            $oUserWithdrawAccountCategory = UserWithdrawAccountCategory::findFirst([
                'user_withdraw_account_category_name=:name:',
                'bind' => [
                    'name' => $sPay,
                ],
            ]);

            if ( !$oUserWithdrawAccountCategory ) {
                throw new Exception(
                    sprintf('pay %s', ResponseError::getError(ResponseError::PARAM_ERROR)),
                    ResponseError::PARAM_ERROR
                );
            }

//            // 判断验证码
            $oUserAccount = UserAccount::findFirst($nUserId);
//            VerifyCode::judgeVerify($oUserAccount->user_phone, VerifyCode::TYPE_WITHDRAW, $sCode);

            $oUserWithdrawAccount = UserWithdrawAccount::findFirst([
                'user_id=:user_id: AND user_withdraw_account=:user_withdraw_account: AND user_withdraw_account_category_id=:user_withdraw_account_category_id:',
                'bind' => [
                    'user_id'                           => $nUserId,
                    'user_withdraw_account'             => $sAccount,
                    'user_withdraw_account_category_id' => $oUserWithdrawAccountCategory->user_withdraw_account_category_id,
                ]
            ]);

            $oUserCertification = UserCertification::findFirst([
                'user_id=:user_id:',
                'bind' => [
                    'user_id' => $nUserId,
                ]
            ]);
            if ( !$sName ) {
                $sName = isset($oUserCertification->user_realname) ? $oUserCertification->user_realname : '';
            }

            // 初始化账号
            if ( !$oUserWithdrawAccount ) {
                $oUserWithdrawAccount                                    = new UserWithdrawAccount();
                $oUserWithdrawAccount->user_id                           = $nUserId;
                $oUserWithdrawAccount->user_withdraw_account             = $sAccount;
                $oUserWithdrawAccount->user_withdraw_account_realname    = $sName;
                $oUserWithdrawAccount->user_withdraw_account_category_id = $oUserWithdrawAccountCategory->user_withdraw_account_category_id;
            } else if ( $oUserWithdrawAccount->user_withdraw_account_realname != $sName ) {
                $oUserWithdrawAccount->user_withdraw_account_realname = $sName;
            }

            if ( $oUserWithdrawAccount->save() === FALSE ) {
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserWithdrawAccount->getMessages()),
                    ResponseError::OPERATE_FAILED
                );
            }

            $oUser = User::findFirst($nUserId);
//			$nRatio = $oUser->user_withdraw_ratio ?: Kv::get(Kv::KEY_DOT_TO_MONEY_RATIO);
            $nRatio = 100;
            if ( $nRatio <= 0 || $nRatio > 100 ) {
                $nRatio = 100;
            }

            $nCash = ($sCash / ($nRatio / 100));

            $oUser->user_cash -= $nCash;
            if ( $oUser->user_cash < 0 ) {
                throw new Exception(ResponseError::getError(ResponseError::OPERATE_FAILED), ResponseError::OPERATE_FAILED);
            }

            $this->db->begin();

            if ( $oUser->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUser->getMessages()),
                    ResponseError::OPERATE_FAILED
                );
            }

            $serviceChargeInfo = $this->_calculateServiceCharge($sPay, $sCash);

            $oUserWithdrawLog                                 = new UserWithdrawLog();
            $oUserWithdrawLog->user_withdraw_log_number       = $sUserWithdrawLogMumber;
            $oUserWithdrawLog->user_withdraw_pay              = $sPay;
            $oUserWithdrawLog->user_withdraw_account          = $sAccount;
            $oUserWithdrawLog->user_id                        = $nUserId;
            $oUserWithdrawLog->user_dot                       = $nCash;
            $oUserWithdrawLog->withdraw_ratio                 = $nRatio;
            $oUserWithdrawLog->user_withdraw_cash             = $sCash - $serviceChargeInfo['serviceCharge'];
            $oUserWithdrawLog->service_charge                 = $serviceChargeInfo['serviceCharge'];
            $oUserWithdrawLog->user_realname                  = $sName;
            $oUserWithdrawLog->user_realname                  = $sName;
            $oUserWithdrawLog->dot_type                       = UserWithdrawLog::DOT_TYPE_CASH;
            $oUserWithdrawLog->user_withdraw_log_check_status = 'C';

            if ( $oUserWithdrawLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), $oUserWithdrawLog->getMessages()),
                    ResponseError::OPERATE_FAILED
                );
            }

            // 添加流水
            $oUserFinanceLog                      = new UserCashLog();
            $oUserFinanceLog->user_id             = $nUserId;
            $oUserFinanceLog->user_current_amount = $oUser->user_cash;
            $oUserFinanceLog->user_last_amount    = $oUser->user_cash + $nCash;
            $oUserFinanceLog->consume_category    = UserCashLog::CATEGORY_WITHDRAW;
            $oUserFinanceLog->consume             = -$nCash;
            $oUserFinanceLog->remark              = '提现扣除现金';
            $oUserFinanceLog->flow_id             = $oUserWithdrawLog->user_withdraw_log_id;
            $oUserFinanceLog->flow_number         = $oUserWithdrawLog->user_withdraw_log_number;

            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }


            VerifyCode::delVerify($oUserAccount->user_phone, VerifyCode::TYPE_WITHDRAW);

            $this->db->commit();

            // 发送提现系统消息
            $this->sendWithdrawMsg($oUser->user_id, $oUserWithdrawLog);

            $row['user']['user_cash']  = sprintf('%.2f', $oUser->user_cash);
            $row['withdraw_log']['id'] = $oUserWithdrawLog->user_withdraw_log_id;

            // 设置用户的提现ID
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );

        } catch ( \Phalcon\Db\Exception $e ) {
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );
            $this->error($e->getCode(), $e->getMessage());
        } catch ( PDOException $e ) {
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );
            $this->error($e->getCode(), $e->getMessage());
        } catch ( Exception $e ) {
            $aTime = explode('.', sprintf('%.20f', microtime(1)));
            $this->cookies->set(
                '_WITHDRAW_ID',
                date('YmdHis', $aTime[0]) . $aTime[1],
                $aTime[0] + 60 * 60 * 24
            );
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }


    /**
     * cashLogAction 现金提现记录
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/withdraw/cashLog
     * @api {get} /user/withdraw/cashLog 现金提现记录
     * @apiName Withdraw-cashLog
     * @apiGroup Profile
     * @apiDescription 现金提现记录
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Numver} page 页码
     * @apiParam (debug){Number} pagesize 每页数
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.withdraw_log
     * @apiSuccess {object[]} d.withdraw_log.items
     * @apiSuccess {number} d.withdraw_log.items.id
     * @apiSuccess {number} d.withdraw_log.items.create_time  添加时间
     * @apiSuccess {String} d.withdraw_log.items.remark  备注
     * @apiSuccess {number} d.withdraw_log.items.cash  金额
     * @apiSuccess {String} d.withdraw_log.items.status  状态
     * @apiSuccess {number} d.withdraw_log.page
     * @apiSuccess {number} d.withdraw_log.pagesize
     * @apiSuccess {number} d.withdraw_log.pagetotal
     * @apiSuccess {number} d.withdraw_log.total
     * @apiSuccess {number} d.withdraw_log.prev
     * @apiSuccess {number} d.withdraw_log.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "withdraw_log": {
     *                    "items": [{
     *                        "id": "14",
     *                        "create_time": "1539945802",
     *                        "remark": "",
     *                        "cash": "98.00",
     *                        "status": "C"
     *                }],
     *                "page": 1,
     *                "pagesize": 20,
     *                "pagetotal": 1,
     *                "total": 1,
     *                "prev": 1,
     *                "next": 1
     *            }
     *        },
     *        "t": "1539947619"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function cashLogAction($nUserId = 0)
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {
            $builder             = $this->modelsManager
                ->createBuilder()
                ->from(UserWithdrawLog::class)
                ->columns('user_withdraw_log_id as id,user_withdraw_log_create_time as create_time,user_withdraw_log_remark as remark,user_withdraw_cash as cash, user_withdraw_log_check_status as status')
                ->where('user_id=:user_id: AND dot_type = :dot_type:',
                    [
                        'user_id'  => $nUserId,
                        'dot_type' => UserWithdrawLog::DOT_TYPE_CASH,
                    ])->orderBy('user_withdraw_log_id desc');
            $row['withdraw_log'] = $this->page($builder, $nPage, $nPagesize);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

}