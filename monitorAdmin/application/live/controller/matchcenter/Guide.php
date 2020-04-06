<?php

namespace app\live\controller\matchcenter;

use app\common\controller\Backend;


/**
 * 诱导匹配记录
 */
class Guide extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_guide_video_log');
    }

    /**
     * index
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
            $total  = $this->model->where($where)->with('User,UserGuideVideo')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User,UserGuideVideo')->order($sort, $order)->limit($offset, $limit)->select();
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