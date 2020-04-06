<?php

namespace app\live\controller\datastat;

use app\common\controller\Backend;

/**
 * 财务数据
 */
class Finance extends Backend
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
        $this->view->assign($this->getDefaultTimeInterval('month'));
        return $this->view->fetch();
    }


    /**
     * detaillist 分类统计列表
     */
    public function detaillist()
    {
        $this->model =  model('live.daily_recharge_stat');
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
        $this->view->assign($this->getDefaultTimeInterval('month'));
        return $this->view->fetch();
    }

}