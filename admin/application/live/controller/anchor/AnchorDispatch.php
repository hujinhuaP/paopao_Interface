<?php

namespace app\live\controller\anchor;

use app\common\controller\Backend;
use app\live\model\live\Group;

/**
 *
 * @icon fa fa-circle-o
 */
class AnchorDispatch extends Backend
{

    /**
     * RoomChat模型对象
     * @var \app\live\model\live\AnchorDispatch
     */
    protected $model = NULL;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.anchor_dispatch');

    }

    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where('anchor.anchor_dispatch_flg = "Y"')->with('User,Anchor')->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('anchor.anchor_dispatch_flg = "Y"')->with('User,Anchor')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        $oGroup = Group::all();
        $this->view->assign("row_group", $oGroup);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param string $ids
     */
    public function edit( $ids = '' )
    {
        $row = $this->model::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row->anchor_dispatch_max_day_times = $params['anchor_dispatch_max_day_times'];
            $row->anchor_dispatch_price         = $params['anchor_dispatch_price'];
            $row->validate(
                [
                    'anchor_dispatch_max_day_times' => 'require',
                    'anchor_dispatch_price' => 'require',
                ],
                [
                    'anchor_dispatch_max_day_times.require' => __('Parameter %s can not be empty', [ '最大匹配次数' ]),
                    'anchor_dispatch_price.require' => __('Parameter %s can not be empty', [ '价格' ]),
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


}
