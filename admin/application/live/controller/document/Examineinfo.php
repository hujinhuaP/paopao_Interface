<?php

namespace app\live\controller\document;

use app\common\controller\Backend;
use app\live\model\live\CustomerServiceReply;
use think\Exception;


/**
 * 审核内容
 *
 */
class Examineinfo extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.examine_info');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('examine_info_content');
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
            $params         = $this->request->param('row/a');
            $row            = $this->model;
            $row->examine_info_content   = $params['examine_info_content'];
            $row->validate(
                [
                    'examine_info_content'   => 'require',
                ],
                [
                    'examine_info_content.require'   => __('Parameter %s can not be empty', [ 'content' ]),
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

            $row->examine_info_content   = $params['examine_info_content'];
            $row->validate(
                [
                    'examine_info_content'   => 'require',
                ],
                [
                    'examine_info_content.require'   => __('Parameter %s can not be empty', [ 'content' ]),
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
        $this->model::where('examine_info_id', 'in', $ids)->delete();
        return $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }

}