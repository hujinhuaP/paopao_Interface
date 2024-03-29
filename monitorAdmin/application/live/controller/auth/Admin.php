<?php

namespace app\live\controller\auth;

use app\live\model\admin\AuthGroup;
use app\live\model\admin\AuthGroupAccess;
use app\common\controller\Backend;
use app\live\model\AdminModel;
use fast\Random;
use fast\Tree;

/**
 * 管理员管理
 *
 * @icon   fa fa-users
 * @remark 一个管理员可以有多个角色组,左侧的菜单根据管理员所拥有的权限进行生成
 */
class Admin extends Backend {

    protected $model = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];

    public function _initialize() {
        parent::_initialize();
        $this->model            = model('admin.Admin');
        $this->childrenAdminIds = $this->auth->getChildrenAdminIds(true);
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin() ? true : false);
        $groupList              = collection(AuthGroup::where('id', 'in', $this->childrenGroupIds)->select())->toArray();
        $groupIds               = $this->auth->getGroupIds();
        Tree::instance()->init($groupList);
        $result = [];
        if ($this->auth->isSuperAdmin()) {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
        } else {
            foreach ($groupIds as $m => $n) {
                $result = array_merge($result, Tree::instance()->getTreeList(Tree::instance()->getTreeArray($n)));
            }
        }
        $groupName = [];
        foreach ($result as $k => $v) {
            $groupName[$v['id']] = $v['name'];
        }
        $this->view->assign('groupdata', $groupName);
        $this->assignconfig("admin", ['id' => $this->auth->id]);
    }

    /**
     * 查看
     */
    public function index() {
        if ($this->request->isAjax()) {

            $childrenGroupIds = $this->childrenGroupIds;
            $groupName        = AuthGroup::where('id', 'in', $childrenGroupIds)->column('id,name');
            $authGroupList    = AuthGroupAccess::where('group_id', 'in', $childrenGroupIds)->field('uid,group_id')->select();
            $adminGroupName   = [];
            foreach ($authGroupList as $k => $v) {
                if (isset($groupName[$v['group_id']]))
                    $adminGroupName[$v['uid']][$v['group_id']] = $groupName[$v['group_id']];
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model->where($where)->where('id', 'in', $this->childrenAdminIds)->order($sort, $order)->count();
            $list  = $this->model->where($where)->where('id', 'in', $this->childrenAdminIds)->field([
                'password',
                'salt',
                'token'
            ], true)->order($sort, $order)->limit($offset, $limit)->select();
            foreach ($list as $k => &$v) {
                $groups           = isset($adminGroupName[$v['id']]) ? $adminGroupName[$v['id']] : [];
                $v['groups']      = implode(',', array_keys($groups));
                $v['groups_text'] = implode(',', array_values($groups));
            }
            unset($v);
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add() {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params['salt']     = Random::alnum();
                $params['password'] = md5(md5($params['password']) . $params['salt']);
                $params['avatar']   = '/assets/img/avatar.png'; //设置新管理员默认头像。
                $existData          = model('admin.Admin')->where("username", $params['username'])->find();
                if (!empty($existData)) {
                    $this->error(__('Exist Admin'));
                }
                $admin = $this->model->create($params);
                $group = $this->request->post("group/a");
                //过滤不允许的组别,避免越权
                $group   = array_intersect($this->childrenGroupIds, $group);
                $dataset = [];
                foreach ($group as $value) {
                    $dataset[] = [
                        'uid'      => $admin->id,
                        'group_id' => $value
                    ];
                }
                model('admin.AuthGroupAccess')->saveAll($dataset);
                $this->success();
            }
            $this->error();
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = NULL) {
        $row = $this->model->get(['id' => $ids]);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                if ($params['password']) {
                    $params['salt']     = Random::alnum();
                    $params['password'] = md5(md5($params['password']) . $params['salt']);
                } else {
                    unset($params['password'], $params['salt']);
                }
                $existData = model('admin.Admin')->where("username", $params['username'])->find();
                if ($existData['id'] != $ids) {
                    $this->error(__('Exist Admin'));
                }
                $row->save($params);
                // 先移除所有权限
                model('admin.AuthGroupAccess')->where('uid', $row->id)->delete();
                $group = $this->request->post("group/a");
                // 过滤不允许的组别,避免越权
                $group   = array_intersect($this->childrenGroupIds, $group);
                $dataset = [];
                foreach ($group as $value) {
                    $dataset[] = [
                        'uid'      => $row->id,
                        'group_id' => $value
                    ];
                }
                model('admin.AuthGroupAccess')->saveAll($dataset);
                $this->success();
            }
            $this->error();
        }
        $grouplist = $this->auth->getGroups($row['id']);
        $groupids  = [];
        foreach ($grouplist as $k => $v) {
            $groupids[] = $v['id'];
        }
        $this->view->assign("row", $row);
        $this->view->assign("groupids", $groupids);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "") {
        if ($ids) {
            // 避免越权删除管理员
            $childrenGroupIds = $this->childrenGroupIds;
            $adminList        = $this->model->where('id', 'in', $ids)->where('id', 'in', function ($query) use ($childrenGroupIds) {
                $query->name('auth_group_access')->where('group_id', 'in', $childrenGroupIds)->field('uid');
            })->select();
            if ($adminList) {
                $deleteIds = [];
                foreach ($adminList as $k => $v) {
                    $deleteIds[] = $v->id;
                }
                $deleteIds = array_diff($deleteIds, [$this->auth->id]);
                if ($deleteIds) {
                    $this->model->destroy($deleteIds);
                    model('admin.AuthGroupAccess')->where('uid', 'in', $deleteIds)->delete();
                    $this->success();
                }
            }
        }
        $this->error();
    }

    /**
     * 批量更新
     *
     * @internal
     */
    public function multi($ids = "") {
        // 管理员禁止批量操作
        $this->error();
    }

}
