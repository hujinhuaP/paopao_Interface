<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\UserRechargeOrder;

/**
 * 充值管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Recharge extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword         = $this->request->param('search');
            $nOffset          = $this->request->param('offset');
            $nLimit           = $this->request->param('limit');
            $aFilter          = json_decode($this->request->param('filter'), 1);
            $aOp              = json_decode($this->request->param('op'), 1);
            $oSelectQuery     = UserRechargeOrder::where('1=1');
            $oTotalQuery      = UserRechargeOrder::where('1=1');
            $oTotalStatQuery  = UserRechargeOrder::where('user_recharge_order_status = "Y"');
            $oSelectStatQuery = UserRechargeOrder::where('1=1');
            $oTodayStatQuery  = UserRechargeOrder::where('user_recharge_order_status = "Y" AND user_recharge_order_create_time >= ' . strtotime(date('Y-m-d')));

            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('user_id', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('user_id', 'LIKE', '%' . $sKeyword . '%');
                } else {
                    $oSelectQuery->where('user_recharge_order_number', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('user_recharge_order_number', 'LIKE', '%' . $sKeyword . '%');
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
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            $oSelectStatQuery->where($key, $aOp[$key], $value);
                            if ( !in_array($key, [
                                'user_recharge_order_status',
                                'user_recharge_order_create_time'
                            ]) ) {
                                $oTodayStatQuery->where($key, $aOp[$key], $value);
                            }
                            break;
                    }
                }
            }

            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $statRechargeSum      = $oSelectStatQuery->sum('user_recharge_order_fee');
            $totalStatRechargeSum = $oTotalStatQuery->sum('user_recharge_order_fee');
            $todayStatRechargeSum = $oTodayStatQuery->sum('user_recharge_order_fee');
            $total                = $oTotalQuery->count();
            $list                 = $oSelectQuery->order('user_recharge_order_id desc')->select();

            //当前选择的所有数据中已支付的总金额

            $result = [
                "total"              => $total,
                "rows"               => $list,
                'recharge_sum'       => $statRechargeSum ? $statRechargeSum : 0,
                'total_recharge_sum' => $totalStatRechargeSum ? $totalStatRechargeSum : 0,
                'today_recharge_sum' => $todayStatRechargeSum ? $todayStatRechargeSum : 0
            ];
            return json($result);
        }
        $this->view->assign($this->getDefaultTimeInterval('month'));
        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids = '')
    {
        $row = UserRechargeOrder::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        $this->view->assign("row", $row);

        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {

    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {

    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {

    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }
}