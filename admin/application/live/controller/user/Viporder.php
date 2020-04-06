<?php

namespace app\live\controller\user;

use app\common\controller\Backend;

/**
 * 公会列表
 */
class Viporder extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_vip_order');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
            $total = $this->model->where($where)->with('User')->order($sort, $order)->count();
            $list  = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();

            // 支付成功金额
            $statRechargeSum = $this->model->where("user_vip_order_status = 'Y'")->where($where)->sum('user_vip_order_combo_fee');
            $result          = [
                "total"     => $total,
                "rows"      => $list,
                "vip_money" => $statRechargeSum
            ];
            return json($result);
        }
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
     *
     * @param  string $ids
     */
    public function multi($ids = '')
    {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort()
    {
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '')
    {
    }
}