<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\User;
use app\live\model\live\UserBudan;
use app\live\model\live\UserFinanceLog;
use app\live\model\live\UserConsumeCategory;
use app\live\model\admin\Admin;

/**
 * 补单管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Budan extends Backend
{

    /**
     * index 列表
     */
    public function index()
    {

        if ( $this->request->isAjax() ) {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserBudan::where('1=1');
            $oTotalQuery  = UserBudan::where('1=1');

            if ( $sKeyword ) {
                $oSelectQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
                $oTotalQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
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
                        case 'user_id':
                        case 'user_nickname':
                            $oSelectQuery->where('u.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('u.' . $key, $aOp[$key], $value);
                            break;

                        default:
                            $oSelectQuery->where('ub.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('ub.' . $key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('ub')->join('user u', 'u.user_id=ub.user_id')->count();
            $list  = $oSelectQuery->alias('ub')->join('user u', 'u.user_id=ub.user_id')->order('ub.user_budan_id desc')->select();

            $aAdminId = [];

            foreach ( $list as &$v ) {
                $aAdminId[] = $v['admin_id'];
            }

            if ( $aAdminId ) {
                $aAdmin = Admin::where('id', 'in', $aAdminId)->select();
                $aAdmin = array_column($aAdmin, 'username', 'id');
            }

            foreach ( $list as &$v ) {
                $v['user_budan_amount'] = sprintf('%.2f', $v['user_budan_amount']);
                $v['operator']          = isset($aAdmin[$v['admin_id']]) ? $aAdmin[$v['admin_id']] : null;
            }

            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  int $ids
     */
    public function detail($ids = '')
    {

    }

    /**
     * add 添加
     *
     * @param int $ids
     */
    public function add($ids = '')
    {
        $row = User::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            if ( $params['user_budan_amount'] <= 0 ) {
                $this->error(__('Budan amount') . __('Must be greater than %d', 0));
            }

            $oUserBudan = new UserBudan();
            $oUserBudan->getQuery()->startTrans();

            try {

                $data = [
                    'user_id'           => $ids,
                    'admin_id'          => $this->auth->id,
                    'user_budan_type'   => $params['user_budan_type'],
                    'user_budan_amount' => $params['operate_type'] == '+' ? $params['user_budan_amount'] : -$params['user_budan_amount'],
                ];

                $bBool = $oUserBudan->validate(
                    [
                        'user_id'           => 'require',
                        'admin_id'          => 'require',
                        'user_budan_type'   => 'require',
                        'user_budan_amount' => 'require',
                    ],
                    [
                        'user_id.require'           => __('Parameter %s can not be empty', [ 'user_id' ]),
                        'admin_id.require'          => __('Parameter %s can not be empty', [ 'admin_id' ]),
                        'user_budan_type.require'   => __('Parameter %s can not be empty', [ 'user_budan_type' ]),
                        'user_budan_amount.require' => __('Parameter %s can not be empty', [ 'user_budan_amount' ]),
                    ]
                )->save($data);

                if ( $bBool === FALSE ) {
                    $oUserBudan->getQuery()->rollback();
                    $this->error($oUserBudan->getError());
                }


                switch ( $params['user_budan_type'] ) {
                    case 'vip':
                        // VIP补单
                        $row->user_member_expire_time =   $row->user_member_expire_time == 0 ? (time() + $data['user_budan_amount'] * 24 * 3600) : ($row->user_member_expire_time + $data['user_budan_amount'] * 24 * 3600);
                        break;
                    case 'coin':
                        $lastUserCoin        = $row->user_coin;
                        $currentUserCoin     = $row->user_coin + $data['user_budan_amount'];
                        $lastUserFreeCoin    = $row->user_free_coin;
                        $currentUserFreeCoin = $row->user_free_coin;

                        $nLastAmount          = $row->user_coin + $row->user_free_coin;
                        $row->user_coin       += $data['user_budan_amount'];
                        $row->user_total_coin += $data['user_budan_amount'];
                        $sAmountType          = UserFinanceLog::AMOUNT_COIN;
                        $nConsumeCategoryId   = UserConsumeCategory::BUDAN_COIN;
                        $nCurrentAmount       = $row->user_coin + $row->user_free_coin + $data['user_budan_amount'];
                        $sRemark              = sprintf('%s_%s_补单_%s', date('Y-m-d H:i:s'), __('Coin'), $data['user_budan_amount']);
                        break;
                    case 'free_coin':
                        $lastUserCoin              = $row->user_coin;
                        $currentUserCoin           = $row->user_coin;
                        $lastUserFreeCoin          = $row->user_free_coin;
                        $currentUserFreeCoin       = $row->user_free_coin + $data['user_budan_amount'];
                        $nLastAmount               = $row->user_coin + $row->user_free_coin;
                        $row->user_free_coin       += $data['user_budan_amount'];
                        $row->user_total_free_coin += $data['user_budan_amount'];
                        $sAmountType               = UserFinanceLog::AMOUNT_COIN;
                        $nConsumeCategoryId        = UserConsumeCategory::BUDAN_COIN;
                        $nCurrentAmount            = $row->user_coin + $row->user_free_coin + $data['user_budan_amount'];
                        $sRemark                   = sprintf('%s_%s_补单_%s', date('Y-m-d H:i:s'), __('Coin'), $data['user_budan_amount']);
                        break;

                    case 'dot':
                    default:
                        $lastUserCoin        = $row->user_coin;
                        $currentUserCoin     = $row->user_coin;
                        $lastUserFreeCoin    = $row->user_free_coin;
                        $currentUserFreeCoin = $row->user_free_coin;
                        $nLastAmount         = $row->user_dot;
                        $row->user_dot       += $data['user_budan_amount'];
                        $sAmountType         = UserFinanceLog::AMOUNT_DOT;
                        $nCurrentAmount      = $row->user_dot;
                        $nConsumeCategoryId  = UserConsumeCategory::BUDAN_DOT;
                        $sRemark             = sprintf('%s_%s_补单_%s', date('Y-m-d H:i:s'), __('Dot'), $data['user_budan_amount']);
                        break;
                }

                $bBool = $row->validate(
                    [
                        'user_coin'               => 'gt:-1',
                        'user_free_coin'          => 'gt:-1',
                        'user_dot'                => 'gt:-1',
                        'user_member_expire_time' => 'gt:-1',
                    ],
                    [
                        'user_coin.gt'               => __('Coin') . __('Must be greater than %d', -1),
                        'user_free_coin.gt'          => __('赠送金币') . __('Must be greater than %d', -1),
                        'user_dot.gt'                => __('Dot') . __('Must be greater than %d', -1),
                        'user_member_expire_time.gt' => __('VIP') . __('Must be greater than %d', -1),
                    ]
                )->save($row->getData());

                if ( $bBool === FALSE ) {
                    $oUserBudan->getQuery()->rollback();
                    $this->error($row->getError());
                }
                if($params['user_budan_type'] !=  'vip'){
                    $oUserFinanceLog = new UserFinanceLog();
                    $data            = [
                        'user_amount_type'       => $sAmountType,
                        'user_id'                => $ids,
                        'user_current_amount'    => $nCurrentAmount,
                        'user_last_amount'       => $nLastAmount,
                        'consume_category_id'    => $nConsumeCategoryId,
                        'consume'                => $data['user_budan_amount'],
                        'remark'                 => $sRemark,
                        'flow_id'                => $oUserBudan->user_budan_id,
                        'admin_id'               => $this->auth->id,
                        'user_current_user_coin' => $currentUserCoin,
                        'user_last_user_coin'    => $lastUserCoin,
                        'user_current_free_coin' => $currentUserFreeCoin,
                        'user_last_free_coin'    => $lastUserFreeCoin,
                    ];
                    if ( $oUserFinanceLog->save($data) === FALSE ) {
                        $this->error($oUserFinanceLog->getError());
                    }
                }
                $oUserBudan->getQuery()->commit();
            } catch ( Exception $e ) {
                $oUserBudan->getQuery()->rollback();
                $this->error($e->getMessage());
            }
            $this->success();

        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  int $ids
     */
    public function edit($ids = '')
    {

    }

    /**
     * multi 批量修改
     *
     * @param  int $ids
     */
    public function multi($ids = '')
    {

    }

    /**
     * delete 删除
     *
     * @param  int $ids
     */
    public function delete($ids = '')
    {
        # code...
    }
}