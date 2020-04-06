<?php

namespace app\live\controller\group;

use app\common\controller\Backend;
use app\live\model\live\User as UserModel;
use app\live\model\live\Group;

/**
 * 公会列表
 */
class User extends Backend {
    public function _initialize() {
        parent::_initialize();
        $this->model = new UserModel();
    }

    /**
     * index 列表
     */
    public function index() {

        $oGroup = Group::all(['status' => 'Y']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id,user_nickname',true);
            $total  = $this->model::where('user_group_id !=0')->where($where)->with('Group')->order($sort, $order)->count();
            $list   = $this->model::where('user_group_id !=0')->where($where)->with('Group')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        $this->view->assign("row_group", $oGroup);
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add() {

        $oGroup = \app\live\model\live\Group::all(['status' => 'Y']);
        if ($this->request->isPost()) {
            $params        = $this->request->param('row/a');
            $anchor_user_id = $params['user_id'];
            $row = UserModel::get($anchor_user_id);
            if(!$row){
                $this->error(__('No Results were found'));
            }
            if($row->user_is_anchor == 'N'){
                $this->error(__('The user is not anchor'));
            }
            if($row->user_group_id != 0){
                $this->error(__('The user has been group'));
            }
//            if($row->user_invite_user_id != 0){
//                $this->error(__('The user has been bind by user'));
//            }
            $row->user_group_id = $params['group_id'];
            $row->validate([
                'user_group_id' => 'require',
            ], [
                'user_group_id.require' => __('Parameter %s can not be empty', [__('Group ID')]),
            ]);
            if (!$row->save($row->getData())) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign('row_group',$oGroup);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '') {
        $row = UserModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $oGroup = \app\live\model\live\Group::all(['status' => 'Y']);
        if ($this->request->isPost()) {
            $params                    = $this->request->param('row/a');
            $row->user_group_id = $params['group_id'];
            $row->validate([
                'user_group_id' => 'require',
            ], [
                'user_group_id.require' => __('Parameter %s can not be empty', [__('Group name')]),
            ]);
            if (!$row->save($row->getData())) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign('row_group',$oGroup);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '') {
        UserModel::where('user_id', 'in', $ids)->update(['user_group_id' => 0]);
        $this->success();
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
        $row = GroupModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost()) {
            $params          = $this->request->param('params');
            $params          = explode('=', $params);
            $row[$params[0]] = $params[1];
            if ($row->save() === false) {
                $this->error($row->getError());
            }
            $this->success();
        }
    }
}