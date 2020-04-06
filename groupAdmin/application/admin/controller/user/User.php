<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\admin\model\api\User as UserModel;
use think\Session;

/**
 * 会员列表
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
        if ($this->request->isAjax()) {

            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id,user_nickname',TRUE);

            $total  = UserModel::where($where)->where('user.user_group_id ='.Session::get('admin.group_id'))
                ->join('user_certification','user_certification.user_id = user.user_id','left')
                ->join('user_account','user_account.user_id = user.user_id')
                ->with('UserAccount,UserCertification')->order($sort, $order)->count();

            $list   = UserModel::where($where)->where('user.user_group_id ='.Session::get('admin.group_id'))
                ->join('user_certification','user_certification.user_id = user.user_id','left')
                ->join('user_account','user_account.user_id = user.user_id')
                ->with('UserAccount,UserCertification')->order($sort, $order)->limit($offset, $limit)->select();
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