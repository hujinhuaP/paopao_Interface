<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

/**
 * 用户"现金"兑换套餐
 */
class Exchangecombo extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_exchange_combo');
        $this->view->assign("exchangeCategoryList", $this->model->getExchangeCategoryList());
    }


    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
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
     * @param string $ids
     */
    public function detail( $ids = '' )
    {
    }

    /**
     * add 添加
     */
    public function add()
    {

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row = new $this->model();

            $row->combo_coin        = $params['combo_coin'];
            $row->combo_cash        = $params['combo_cash'];
            $row->exchange_category = $params['exchange_category'];
            $row->validate(
                [
                    'combo_coin' => 'require|gt:0',
                    'combo_cash' => 'require|gt:0',
                ],
                [
                    'combo_coin.gt'      => __('Coin') . __('Must be greater than %d', 0),
                    'combo_cash.gt'      => __('Amount') . __('Must be greater than %d', 0),
                    'combo_coin.require' => __('Parameter %s can not be empty', [ 'user_recharge_combo_coin' ]),
                    'combo_cash.require' => __('Parameter %s can not be empty', [ 'user_recharge_combo_fee' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param string $ids
     */
    public function edit( $ids = '' )
    {
        $row = $this->model::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row->combo_coin        = $params['combo_coin'];
            $row->combo_cash        = $params['combo_cash'];
            $row->exchange_category = $params['exchange_category'];
            $row->validate(
                [
                    'combo_coin' => 'require|gt:0',
                    'combo_cash' => 'require|gt:0',
                ],
                [
                    'combo_coin.gt'      => __('Coin') . __('Must be greater than %d', 0),
                    'combo_cash.gt'      => __('Amount') . __('Must be greater than %d', 0),
                    'combo_coin.require' => __('Parameter %s can not be empty', [ 'user_recharge_combo_coin' ]),
                    'combo_cash.require' => __('Parameter %s can not be empty', [ 'user_recharge_combo_fee' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param string $ids
     */
    public function delete( $ids = '' )
    {
        $this->model::where('id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     * @param string $ids
     */
    public function multi( $ids = '' )
    {

    }
}