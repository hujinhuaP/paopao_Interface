<?php

namespace app\live\controller\video;

use app\common\controller\Backend;
use app\live\model\live\UserVideo;

/**
 * 付费视频
 */
class Check extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_video');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id',true);
            $total  = $this->model->where("user_video.watch_type = 'charge'")->where($where)->with('User,Category')->order($sort, $order)->count();
            $list   = $this->model->where("user_video.watch_type = 'charge'")->where($where)->with('User,Category')->order($sort, $order)->limit($offset, $limit)->select();
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
        $row = UserVideo::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            try {

                if ( $row->check_status != 'C' ) {
                    $this->error(__('You have no permission'));
                }


                $row->check_status = $params['check_status'];
                $row->admin_id     = $this->auth->id;

                $row->validate(
                    [
                        'admin_id'     => 'require',
                        'check_status' => 'require',
                    ],
                    [
                        'admin_id.require'     => __('Parameter %s can not be empty', [ 'admin_id' ]),
                        'check_status.require' => __('Parameter %s can not be empty', [ 'check_status' ]),
                    ]
                );
                if ( $row->save($row->getData()) === FALSE ) {
                    $this->error($row->getError());
                }
            } catch ( Exception $e ) {
                $this->error($e->getMessage());
            }
            $this->success();
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