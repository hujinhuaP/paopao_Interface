<?php

namespace app\live\controller\general;

use app\common\controller\Backend;


/**
 * 诱导话术
 *
 */
class Guidelist extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.guide_msg_list');
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

            $row->location_type   = $params['location_type'];
            $row->first_content   = $params['first_content'];
            $row->first_msg_type  = $params['first_msg_type'];
            $row->first_extra     = $params['first_extra'];
            $row->second_content  = $params['second_content'];
            $row->second_msg_type = $params['second_msg_type'];
            $row->second_extra    = $params['second_extra'];
            $row->third_content   = $params['third_content'];
            $row->third_msg_type  = $params['third_msg_type'];
            $row->third_extra     = $params['third_extra'];

            $fields = [
                'first_content'  => 'require',
                'first_msg_type' => 'require',
                'location_type'  => 'require',
            ];
            $rules  = [
                'first_content.require'  => __('Parameter %s can not be empty', [ 'first_content' ]),
                'first_msg_type.require' => __('Parameter %s can not be empty', [ 'first_msg_type' ]),
                'location_type.require'  => __('Parameter %s can not be empty', [ '诱导位置' ]),
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
        $upload_url = config('upload.uploadurl_mp3');
        $this->view->assign("upload_url", $upload_url);
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

            $row->location_type   = $params['location_type'];
            $row->first_content   = $params['first_content'];
            $row->first_msg_type  = $params['first_msg_type'];
            $row->first_extra     = $params['first_extra'];
            $row->second_content  = $params['second_content'];
            $row->second_msg_type = $params['second_msg_type'];
            $row->second_extra    = $params['second_extra'];
            $row->third_content   = $params['third_content'];
            $row->third_msg_type  = $params['third_msg_type'];
            $row->third_extra     = $params['third_extra'];

            $fields = [
                'first_content'  => 'require',
                'first_msg_type' => 'require',
                'location_type'  => 'require',
            ];
            $rules  = [
                'first_content.require'  => __('Parameter %s can not be empty', [ 'first_content' ]),
                'first_msg_type.require' => __('Parameter %s can not be empty', [ 'first_msg_type' ]),
                'location_type.require'  => __('Parameter %s can not be empty', [ '诱导位置' ]),
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