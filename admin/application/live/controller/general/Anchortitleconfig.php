<?php

namespace app\live\controller\general;

use app\common\controller\Backend;
use app\live\library\Redis;


/**
 * Anchortitleconfig  主播称号配置表
 *
 */
class Anchortitleconfig extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.anchor_title_config');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
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

            $row->anchor_title_name                    = $params['anchor_title_name'];

            $row->validate(
                [
                    'anchor_title_name'                    => 'require',
                ],
                [
                    'anchor_title_name.require'                    => __('Parameter %s can not be empty', [ '称号名称' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $key    = sprintf('_PHCRcaches_anchor_title::%s', $row->anchor_title_id);
            $oRedis = new Redis();
            $oRedis->del($key);

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
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }
}