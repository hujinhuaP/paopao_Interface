<?php

namespace app\live\controller\datastat;

use app\common\controller\Backend;

/**
 * 留存数据
 */
class Retain extends Backend
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


}