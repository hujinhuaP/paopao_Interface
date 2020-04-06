<?php

namespace app\live\controller\matchcenter;

use app\common\controller\Backend;
use app\live\model\live\Group;
use app\live\model\live\UserPrivateChatLog;
use app\live\model\live\UserPrivateChatLog20181011;


/**
 * 视频记录
 */
class Log extends Backend {
    public function _initialize() {
        parent::_initialize();
        $this->model = new UserPrivateChatLog();
    }

    /**
     * connecting 通话中
     */
    public function connecting() {

        if ( $this->request->isAjax() ) {
            $current_time = time();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $total  = $this->model::where('user_private_chat_log.status = 4')->where($where)->with('User,AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->order($sort, $order)->count();
            $list   = $this->model::where('user_private_chat_log.status = 4')->where($where)->with('User,AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->field("'$current_time' as php_current_time")->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }
    /**
     * history 通话记录
     */
    public function history() {

        if ( $this->request->isAjax() ) {
            $current_time = time();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $where_str = 'user_private_chat_log.status in (4,6) ';
            $error_flg = $this->request->get("error_flg", '');
            if($error_flg == 'error'){
                $where_str .= ' AND user.user_is_superadmin = "N" AND user_private_chat_log.status = 6 AND user_private_chat_log.timepay_count < CEILING(user_private_chat_log.duration/60) AND duration mod 60 > 5';
            }
            $total  = $this->model::where($where_str)->where($where)->with('User,AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->order($sort, $order)->count();
            $list   = $this->model::where($where_str)->where($where)->with('User,AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->field("'$current_time' as php_current_time")->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('default_start',date('Y-m-d H:i:s',strtotime('-7 day')));
        $this->view->assign('default_end',date('Y-m-d H:i:s',strtotime('+1 day')));
        return $this->view->fetch();
    }

    /**
     * history 通话记录
     */
    public function historyback() {

        $this->model = new UserPrivateChatLog20181011();
        if ( $this->request->isAjax() ) {
            $current_time = time();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $where_str = 'user_private_chat_log20181011.status in (4,6) ';
            $error_flg = $this->request->get("error_flg", '');
            if($error_flg == 'error'){
                $where_str .= ' AND user_private_chat_log20181011.status = 6 AND user_private_chat_log20181011.timepay_count < CEILING(user_private_chat_log20181011.duration/60) AND duration mod 60 > 3 AND user_private_chat_log20181011.create_time > unix_timestamp(\'2018-08-20\')';
            }
            $total  = $this->model::where($where_str)->where($where)->with('User,AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log20181011.chat_log_anchor_user_id')->order($sort, $order)->count();
            $list   = $this->model::where($where_str)->where($where)->with('User,AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log20181011.chat_log_anchor_user_id')->field("'$current_time' as php_current_time")->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
//        $this->view->assign('default_start',date('Y-m-d H:i:s',strtotime('-7 day')));
//        $this->view->assign('default_end',date('Y-m-d H:i:s',strtotime('+1 day')));
        return $this->view->fetch();
    }

    /**
     * cancel 取消记录
     */
    public function cancel() {

        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,TRUE);
            $where_str = 'user_private_chat_log.status in (1,2,5)';
            $total  = $this->model::where($where_str)->where($where)->with('AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->order($sort, $order)->count();
            $list   = $this->model::where($where_str)->where($where)->with('AnchorUser')->join('user anchor_user','anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $oGroup = Group::all();
        $this->view->assign("row_group", $oGroup);
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