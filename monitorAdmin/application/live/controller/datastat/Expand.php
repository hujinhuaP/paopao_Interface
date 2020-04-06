<?php

namespace app\live\controller\datastat;

use app\common\controller\Backend;

/**
 * 推广数据
 */
class Expand extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.daily_data_stat');
    }

    /**
     * index 列表
     */
    public function index()
    {
        $defalutStart = date('Y-m-d', strtotime('-1 month'));
        $defalutEnd   = date('Y-m-d');
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
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
     * monthindex 按月统计
     */
    public function monthindex()
    {
        $defalutStart = date('Y-m-d', strtotime('-1 month'));
        $defalutEnd   = date('Y-m-d');
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $columns = 'stat_month,sum(register_ios_male_count + register_and_male_count)  as register_count,
            sum(recharge_order_count) as recharge_order_count,sum(recharge_money_count) as recharge_money_count,
            sum(recharge_order_success_count) as recharge_order_success_count,
            sum(recharge_money_success_count) as recharge_money_success_count,
            sum(video_chat_success_count) as video_chat_success_count,sum(consume_coin_count) as consume_coin_count';
            $total  = $this->model->where($where)->order($sort, $order)->group('stat_month')->count();
            $list   = $this->model->field($columns)->where($where)->group('stat_month')->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 用户推广数据
     */
    public function user()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
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


}