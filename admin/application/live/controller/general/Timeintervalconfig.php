<?php

namespace app\live\controller\general;

use app\common\controller\Backend;


/**
 * 时间段配置表
 *
 */
class Timeintervalconfig extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.time_interval_config');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();

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
            $params = $this->request->param('row/a');

            $row = new $this->model();

            $row->start_hour = $params['start_hour'];
            $row->end_hour   = $params['end_hour'];
            if ( $row->start_hour == $row->end_hour ) {
                $this->error('时间段不能相等');
            }

            $fields = [
                'start_hour' => 'require',
                'end_hour'   => 'require',
            ];
            $rules  = [
                'start_hour.require' => __('Parameter %s can not be empty', [ 'start_hour' ]),
                'end_hour.require'   => __('Parameter %s can not be empty', [ 'end_hour' ]),
            ];
            $row->validate(
                $fields,
                $rules
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
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

            $row->start_hour = $params['start_hour'];
            $row->end_hour   = $params['end_hour'];
            if ( $row->start_hour == $row->end_hour ) {
                $this->error('时间段不能相等');
            }

            $fields = [
                'start_hour' => 'require',
                'end_hour'   => 'require',
            ];
            $rules  = [
                'start_hour.require' => __('Parameter %s can not be empty', [ 'start_hour' ]),
                'end_hour.require'   => __('Parameter %s can not be empty', [ 'end_hour' ]),
            ];
            $row->validate(
                $fields,
                $rules
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }

            $this->success();
        }
        $this->view->assign("row", $row);
        $upload_url = config('upload.uploadurl_mp3');
        $this->view->assign("upload_url", $upload_url);
        return $this->view->fetch();

    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {
        $this->model::where('id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }
}