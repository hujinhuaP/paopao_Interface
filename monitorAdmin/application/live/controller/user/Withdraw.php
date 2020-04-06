<?php

namespace app\live\controller\user;

use app\live\model\live\Kv;
use think\Log;
use think\Config;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\User;
use app\live\model\live\UserFinanceLog;
use app\live\model\live\UserWithdrawLog;
use app\live\model\live\UserConsumeCategory;

/**
 * 用户管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Withdraw extends Backend
{
    use \app\live\library\traits\SystemMessageService;

    /**
     * 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserWithdrawLog::where('1=1');
            $oTotalQuery  = UserWithdrawLog::where('1=1');

            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('user_id', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('user_id', 'LIKE', '%' . $sKeyword . '%');
                } else {
                    $oSelectQuery->where('user_withdraw_log_number', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('user_withdraw_log_number', 'LIKE', '%' . $sKeyword . '%');
                }
            }

            if ( $aFilter ) {
                foreach ( $aFilter as $key => $value ) {
                    if ( stripos($aOp[$key], 'LIKE') !== FALSE ) {
                        $value     = str_replace([
                            'LIKE ',
                            '...'
                        ], [
                            '',
                            $value
                        ], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ( $key ) {
                        case 'user_withdraw_log_status':
                            if ( $value == 'C' ) {
                                $oSelectQuery->where('user_withdraw_log_check_status', 'N');
                                $oTotalQuery->where('user_withdraw_log_check_status', 'N');
                                $oSelectQuery->where($key, $aOp[$key], 'N');
                                $oTotalQuery->where($key, $aOp[$key], 'N');
                            } else if ( isset($aFilter['user_withdraw_log_check_status']) && $aFilter['user_withdraw_log_check_status'] == 'N' && $value == 'N' ) {
                                $oSelectQuery->where('user_withdraw_log_check_status', 'Y');
                                $oTotalQuery->where('user_withdraw_log_check_status', 'Y');
                                $oSelectQuery->where($key, $aOp[$key], $value);
                                $oTotalQuery->where($key, $aOp[$key], $value);

                            } else if ( $value == 'N' ) {
                                $oSelectQuery->where('user_withdraw_log_check_status', '<>', 'N');
                                $oTotalQuery->where('user_withdraw_log_check_status', '<>', 'N');
                                $oSelectQuery->where($key, $aOp[$key], 'N');
                                $oTotalQuery->where($key, $aOp[$key], 'N');
                            } else {
                                $oSelectQuery->where($key, $aOp[$key], $value);
                                $oTotalQuery->where($key, $aOp[$key], $value);
                            }
                            break;
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total  = $oTotalQuery->count();
            $list   = $oSelectQuery->order('user_withdraw_log_id desc')->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * 详情
     */
    public function detail($id = null)
    {

    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = UserWithdrawLog::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        // 邀请主播的主播
        $oInviteUser          = null;
        $invite_withdraw_rate = Kv::getValue(Kv::INVITE_ANCHOR_WITHDRAW_RADIO);
        $invite_reward        = 0;
        $oUser                = User::get($row->user_id);
        if ( $oUser->user_invite_user_id ) {
            $oInviteUser = User::get($oUser->user_invite_user_id);
            if ( !$oInviteUser || $oInviteUser->user_is_anchor == 'N' ) {
                $oInviteUser = null;
            }
            $invite_reward = sprintf('%.2f', $invite_withdraw_rate * $row->user_withdraw_cash / 100);
        }

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            try {

                if ( $row->user_withdraw_log_check_status != 'C' ) {
                    $this->error(__('You have no permission'));
                }


                $row->user_withdraw_log_check_status = $params['user_withdraw_log_check_status'];
                $row->user_withdraw_log_remark       = $params['user_withdraw_log_remark'];
                $row->admin_id                       = $this->auth->id;
                $row->user_withdraw_way              = isset($params['user_withdraw_way']) ? $params['user_withdraw_way'] : 0;

                if ( $row->user_withdraw_way == 2 ) {
                    $row->user_withdraw_log_status = 'Y';
                } else if ( $row->user_withdraw_way == 1 ) {
                    $row->user_withdraw_log_status = 'Y';
                    $aTime                         = explode('.', sprintf('%.14f', microtime(TRUE)));
                    $payConfig                     = Config::get('pay.alipay');
                    $transferData                  = [
                        "out_biz_no"      => date('YmdHis', $aTime[0]) . $aTime[1] . mt_rand(1000, 9999),
                        // 商户转账唯一订单号
                        "payee_type"      => 'ALIPAY_LOGONID',
                        // 收款方账户类型
                        "payee_account"   => $payConfig['mode'] == 'dev' ? 'spdhnf3246@sandbox.com' : $row->user_withdraw_account,
                        // 收款方账户
                        "amount"          => $row->user_withdraw_cash,
                        // 转账金额
                        "payer_show_name" => $payConfig['payer_show_name'],
                        //付款方姓名
                        "payee_real_name" => $payConfig['mode'] == 'dev' ? '沙箱环境' : $row->user_realname,
                        // 收款方真实姓名
                        "remark"          => '直播提现转账 - ' . $row->user_withdraw_log_remark,
                    ];
                    $alipayResult                  = \Payment\Pay::alipay($payConfig)->transfer($transferData);
                    Log::info(sprintf(
                        '【提现 - 支付宝】【PARAM - %s】【RESULT - %s】',
                        json_encode($transferData, JSON_UNESCAPED_UNICODE),
                        json_encode($alipayResult, JSON_UNESCAPED_UNICODE)
                    ));
                }

                $row->validate(
                    [
                        'admin_id'                       => 'require',
                        'user_withdraw_log_check_status' => 'require',
                        'user_withdraw_way'              => 'require',
                    ],
                    [
                        'admin_id.require'                       => __('Parameter %s can not be empty', [ 'admin_id' ]),
                        'user_withdraw_log_check_status.require' => __('Parameter %s can not be empty', [ 'user_withdraw_log_check_status' ]),
                        'user_withdraw_way.require'              => __('Parameter %s can not be empty', [ 'user_withdraw_way' ]),
                    ]
                );
                $row->getQuery()->startTrans();
                if ( $row->save($row->getData()) === FALSE ) {
                    $row->getQuery()->rollback();
                    $this->error($row->getError());
                }

                switch ( $row->user_withdraw_log_check_status ) {
                    case 'C':
                        break;

                    case 'Y':
                        if ( isset($params['invite_reward']) && $params['invite_reward'] > 0 ) {
                            $invite_reward = sprintf('%.2f',$params['invite_reward']);
                            //如果通过 并且有邀请主播奖励大于0 则需要给邀请主播加佣金
                            $oUserFinanceLog = new UserFinanceLog();
                            $data            = [
                                'user_amount_type'    => UserFinanceLog::AMOUNT_DOT,
                                'user_id'             => $oInviteUser->user_id,
                                'user_current_amount' => $oInviteUser->user_dot,
                                'user_last_amount'    => $oInviteUser->user_dot + $invite_reward,
                                'consume_category_id' => UserConsumeCategory::INVITE_WITHDRAW_REWARD,
                                'consume'             => $invite_reward,
                                'remark'              => '邀请主播提现奖励',
                                'flow_id'             => $row->user_withdraw_log_id,
                                'flow_number'         => $row->user_withdraw_log_number,
                                'admin_id'            => $this->auth->id,
                                'target_user_id'      => $row->user_id,
                            ];
                            if ( $oUserFinanceLog->save($data) === FALSE ) {
                                $row->getQuery()->rollback();
                                $this->error($oUserFinanceLog->getError());
                            }
                            $oInviteUser->user_dot              += $invite_reward;
                            $oInviteUser->user_invite_dot_total += $invite_reward;
                            if ( $oInviteUser->save() === FALSE ) {
                                $row->getQuery()->rollback();
                                $this->error($oInviteUser->getError());
                            }
                        }
                        break;

                    case 'N':
                    default:

                        $oUser           = User::get($row->user_id);
                        $oUser->user_dot += $row->user_dot;
                        if ( $oUser->save() === FALSE ) {
                            $row->getQuery()->rollback();
                            $this->error($oUser->getError());
                        }

                        $oUserFinanceLog = new UserFinanceLog();
                        $data            = [
                            'user_amount_type'    => UserFinanceLog::AMOUNT_DOT,
                            'user_id'             => $row->user_id,
                            'user_current_amount' => $oUser->user_dot,
                            'user_last_amount'    => $oUser->user_dot - $row->user_dot,
                            'consume_category_id' => UserConsumeCategory::CATEGORY_WITHDRAW,
                            'consume'             => $row->user_dot,
                            'remark'              => '提现退回收益',
                            'flow_id'             => $row->user_withdraw_log_id,
                            'flow_number'         => $row->user_withdraw_log_number,
                            'admin_id'            => $this->auth->id,
                        ];
                        if ( $oUserFinanceLog->save($data) === FALSE ) {
                            $row->getQuery()->rollback();
                            $this->error($oUserFinanceLog->getError());
                        }
                        break;
                }

                $row->getQuery()->commit();

            } catch ( \Payment\Exceptions\Exception $e ) {
                $row->getQuery()->rollback();
                Log::error(sprintf(
                    '【提现 - 支付宝】【PARAM - %s】【RESULT - %s %s】',
                    json_encode($transferData, JSON_UNESCAPED_UNICODE),
                    $e->getMessage(),
                    json_encode($e->raw, JSON_UNESCAPED_UNICODE)
                ));
                $this->error($e->raw['alipay_fund_trans_toaccount_transfer_response']['sub_msg']);
            } catch ( Exception $e ) {
                $row->getQuery()->rollback();
                $this->error($e->getMessage());
            }

            // 发送系统消息
            $this->sendWithdrawMsg($row->user_id, $row);

            $this->success();
        }

        $this->view->assign("oInviteUser", $oInviteUser);
        $this->view->assign("invite_withdraw_rate", $invite_withdraw_rate);
        $this->view->assign("invite_reward", $invite_reward);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
    }

    /**
     * 批量操作
     */
    public function multi($ids = "")
    {
    }

}