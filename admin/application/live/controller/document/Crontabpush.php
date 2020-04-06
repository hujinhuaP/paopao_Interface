<?php

namespace app\live\controller\document;

use app\common\controller\Backend;
use app\live\model\live\CustomerServiceReply;
use think\Exception;
use think\Session;


/**
 * 定时推送配置
 *
 */
class Crontabpush extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.crontab_push');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids = '')
    {
    }

    /**
     * add 添加
     */
    public function add()
    {
        if ( $this->request->isPost() ) {
            $params                      = $this->request->param('row/a');
            $admin                       = Session::get('admin');
            $row                         = new $this->model;
            $row->crontab_push_hour      = $params['crontab_push_hour'];
            $row->crontab_push_content   = $params['crontab_push_content'];
            $row->crontab_push_user_type = $params['crontab_push_user_type'];
            if($row->crontab_push_user_type == 'user'){
                $row->crontab_push_user_id   = $params['crontab_push_user_id'];
            }
            $row->crontab_admin_id       = $admin->id;
            $row->crontab_admin_name     = $admin->username;
            $row->validate(
                [
                    'crontab_push_content'   => 'require',
                ],
                [
                    'crontab_push_content.require'   => __('Parameter %s can not be empty', [ 'content' ]),
                ]
            );
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {
        $row = $this->model::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $admin                       = Session::get('admin');
            $row                         = new $this->model;
            $row->crontab_push_hour      = $params['crontab_push_hour'];
            $row->crontab_push_content   = $params['crontab_push_content'];
            $row->crontab_push_user_type = $params['crontab_push_user_type'];
            if($row->crontab_push_user_type == 'user'){
                $row->crontab_push_user_id   = $params['crontab_push_user_id'];
            }
            $row->crontab_admin_id       = $admin->id;
            $row->crontab_admin_name     = $admin->username;
            $row->validate(
                [
                    'crontab_push_content'   => 'require',
                ],
                [
                    'crontab_push_content.require'   => __('Parameter %s can not be empty', [ 'content' ]),
                ]
            );
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {
        $this->model::where('crontab_push_id', 'in', $ids)->delete();
        return $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }

    public function status($ids = '')
    {
        $row = $this->model::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params       = $this->request->param('params');
            $params       = explode('=', $params);
            $allow_params = [
                'crontab_push_on_flg'
            ];
            if ( !in_array($params[0], $allow_params) ) {
                $this->error('无权修改');
            }
            $row[$params[0]] = $params[1];
            $admin                       = Session::get('admin');
            $row->crontab_admin_id       = $admin->id;
            $row->crontab_admin_name     = $admin->username;
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
    }

}