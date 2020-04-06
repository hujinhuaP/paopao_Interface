<?php

namespace app\admin\controller\general;

use app\admin\model\Admin;
use app\admin\model\api\Agent;
use app\admin\model\api\GroupAdmin;
use app\common\controller\Backend;
use fast\Random;
use think\Session;

/**
 * 个人配置
 *
 * @icon fa fa-user
 */
class Profile extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            $model = model('AdminLog');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $model
                    ->where($where)
                    ->where('admin_id', $this->auth->id)
                    ->order($sort, $order)
                    ->count();

            $list = $model
                    ->where($where)
                    ->where('admin_id', $this->auth->id)
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 更新个人信息
     */
    public function update()
    {
        if ($this->request->isPost())
        {
            $params = $this->request->post("row/a");
            $params = array_filter(array_intersect_key($params, array_flip(array( 'nickname', 'password', 'avatar','old_password','confirm_password'))));
            unset($v);

            if ($params)
            {
                $admin = GroupAdmin::get($this->auth->id);
                if (isset($params['password']))
                {
                    if(strlen($params['password']) < 6 || strlen($params['password']) > 16){
                        $this->error(__('Password length can not less than 6 or more than 16'));
                    }
                    if(empty($params['old_password'])){
                        $this->error(__('Please input old password'));
                    }
                    if(empty($params['confirm_password'])){
                        $this->error(__('Please input confirm password'));
                    }
                    if($params['password'] != $params['confirm_password']){
                        $this->error(__('Two input password not match'));
                    }
                    if($admin->password != md5($params['old_password'] . $admin->salt)){
                        $this->error(__('Password error'));
                    }
                    $params['password'] = md5($params['password'] . $admin->salt);
                    unset($params['old_password']);
                    unset($params['confirm_password']);
                }
                $admin->save($params);
                //因为个人资料面板读取的Session显示，修改自己资料后同时更新Session
                Session::set("admin.avatar", $admin->avatar);
                Session::set("admin.nickname", $admin->nickname);
                $this->success();
            }
            $this->error();
        }
        return;
    }

}
