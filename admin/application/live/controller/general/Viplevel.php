<?php

namespace app\live\controller\general;

use app\common\controller\Backend;

/**
 * Vip等级配置
 */
class Viplevel extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.vip_level');
    }


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
     * delete 删除
     *
     * @param string $ids
     */
    public function delete( $ids = '' )
    {
    }

    /**
     * multi 批量操作
     *
     * @param string $ids
     */
    public function multi( $ids = '' )
    {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort()
    {
    }


}