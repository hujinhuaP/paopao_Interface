<?php

namespace app\live\controller\posts;

use think\Exception;
use app\common\controller\Backend;

/**
 * 动态礼物管理
 *
 */
class Gift extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.short_posts_gift');
    }

    /**
     * index 列表
     */
    public function index($ids = '')
    {
        if ( $this->request->isAjax() ) {
            $whereStr = '1=1';
            if ( $ids ) {
                $whereStr = ' short_posts_id = ' . intval($ids);
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where($whereStr)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where($whereStr)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        $this->view->assign('short_posts_id', $ids);
        return $this->view->fetch();
    }



}