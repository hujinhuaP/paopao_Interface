<?php

namespace app\live\controller\video;

use app\common\controller\Backend;

/**
 * 付费视频
 */
class Charge extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_video');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id',true);
            $total  = $this->model->where("user_video.watch_type = 'charge' AND check_status = 'Y'")->where($where)->with('User,Category')->order($sort, $order)->count();
            $list   = $this->model->where("user_video.watch_type = 'charge' AND check_status = 'Y'")->where($where)->with('User,Category')->order($sort, $order)->limit($offset, $limit)->select();
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