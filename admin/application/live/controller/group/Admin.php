<?php

namespace app\live\controller\group;

use app\common\controller\Backend;
use app\live\model\live\GroupAdmin as GroupAdminModel;

/**
 * 公会列表
 */
class Admin extends Backend {
    public function _initialize() {
        parent::_initialize();
        $this->model = new GroupAdminModel();
    }

    /**
     * index 列表
     */
    public function index() {
        if ($this->request->isAjax()) {

            list($where, $sort, $order, $offset, $limit) = $this->buildparams('username',true);

            $total  = $this->model::where($where)->with('Group')->order($sort, $order)->count();
            $list   = $this->model::where($where)->with('Group')->order($sort, $order)->limit($offset, $limit)->select();
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

        $oGroup = \app\live\model\live\Group::all();
        if ($this->request->isPost()) {
            $params        = $this->request->param('row/a');
            $row           = new GroupAdminModel();
            $row->username = $params['username'];
            $row->nickname       = $params['nickname'];
            if(strlen($params['password']) < 6 || strlen($params['password']) > 16){
                $this->error(__('Password length can not less than 6 or more than 16'));
            }
            if(strlen($params['username']) < 6 || strlen($params['username']) > 16){
                $this->error(__('Username length can not less than 6 or more than 16'));
            }
            $row->salt = createNoncestr(6);
            $row->password = md5($params['password'] . $row->salt);
            $row->status    = $params['status'];
            $row->group_id    = $params['group_id'];
            $existData = $this->model->where("username", $params['username'])->find();
            if ($existData) {
                $this->error(__('Exist group name'));
            }
            $row->validate([
                'username' => 'require',
                'nickname' => 'require',
                'group_id' => 'require',
            ], [
                'username.require' => __('Parameter %s can not be empty', [__('Username')]),
                'nickname.require'       => __('Parameter %s can not be empty', [__('Nickname')]),
                'group_id.require'       => __('Parameter %s can not be empty', [__('Group name')]),
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

        $row = GroupAdminModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $oGroup = \app\live\model\live\Group::all();
        if ($this->request->isPost()) {
            $params                    = $this->request->param('row/a');
            $row->nickname       = $params['nickname'];
            if($params['password']){
                if(strlen($params['password']) < 6 || strlen($params['password']) > 16){
                    $this->error(__('Password length can not less than 6 or more than 16'));
                }
                $row->password = md5($params['password'] . $row->salt);
            }
            $row->status    = $params['status'];
            $row->group_id    = $params['group_id'];

            $row->validate([
                'nickname' => 'require',
                'group_id' => 'require',
            ], [
                'nickname.require'       => __('Parameter %s can not be empty', [__('Nickname')]),
                'group_id.require'       => __('Parameter %s can not be empty', [__('Group name')]),
            ]);
            if ($row->save($row->getData()) === false) {
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
        $row = GroupAdminModel::get($ids);
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