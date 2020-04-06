<?php

namespace app\live\controller\user;

use app\common\controller\Backend;
use app\live\model\live\UserChat;


/**
 * 聊天记录
 */
class Chatlog extends Backend {
    public function _initialize() {
        parent::_initialize();
        $this->model = new UserChat();
    }

    /**
     * index 列表
     */
    public function index() {

        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $where_str = "user_chat_pay_type != 'S'";
            $total  = $this->model->where($where)->where($where_str)->with('SendUser,GetUser')
                ->join('user get_user', 'get_user.user_id=user_chat_receiv_user_id', 'inner')
                ->join('user send_user', 'send_user.user_id=user_chat_send_user_id', 'inner')
                ->order($sort, $order)->count();
            $list   = $this->model->where($where)->where($where_str)->with('SendUser,GetUser')
                ->join('user get_user', 'get_user.user_id=user_chat_receiv_user_id', 'inner')
                ->join('user send_user', 'send_user.user_id=user_chat_send_user_id', 'inner')
                ->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('stat_start_time',date('Y-m-d 00:00:00'));
        $this->view->assign('stat_end_time',date('Y-m-d H:i:s'));
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