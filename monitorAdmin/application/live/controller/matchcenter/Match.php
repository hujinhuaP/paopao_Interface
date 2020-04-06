<?php

namespace app\live\controller\matchcenter;

use app\common\controller\Backend;
use app\live\model\live\UserMatchLog;


/**
 * 用户匹配记录
 */
class Match extends Backend {
    public function _initialize() {
        parent::_initialize();
        $this->model = new UserMatchLog();
    }

    /**
     * index 列表
     */
    public function index() {

        if ( $this->request->isAjax() ) {
            $current_time = time();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $total  = $this->model->where($where)->with('User,Anchor,userPrivateChatLog')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User,Anchor,userPrivateChatLog')->order($sort, $order)->limit($offset, $limit)->select();
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
    public function add() {
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '') {
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '') {
    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids = '') {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort() {
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '') {
    }
}