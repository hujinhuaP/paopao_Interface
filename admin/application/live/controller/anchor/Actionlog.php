<?php

namespace app\live\controller\anchor;

use app\common\controller\Backend;

/**
 * Actionlog  操作记录
 *
 */
class Actionlog extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('admin.anchor_action_log');
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



}