<?php

namespace app\live\controller\voiceroom;

use app\common\controller\Backend;

/**
 * 砸蛋记录
 */
class Egglog extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_egg_log');
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
    public function detail( $ids = '' )
    {
        $this->model = model('live.user_egg_detail');
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where('user_egg_detail_log_id = '.$ids)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('user_egg_detail_log_id = '.$ids)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('ids',$ids);
        return $this->view->fetch();
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