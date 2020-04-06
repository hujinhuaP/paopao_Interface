<?php

namespace app\live\controller\chat;

use app\common\controller\Backend;


/**
 * 守护免费记录
 */
class Guardfree extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.free_time_guard_log');
    }

    /**
     * index 守护免费通话记录
     */
    public function index()
    {

        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total  = $this->model::where($where)->with('User,AnchorUser')->order($sort, $order)->count();
            $list   = $this->model::where($where)->with('User,AnchorUser')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
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