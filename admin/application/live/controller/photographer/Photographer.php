<?php

namespace app\live\controller\photographer;

use app\common\controller\Backend;
use app\live\model\live\User;

/**
 * 摄影师管理
 */
class Photographer extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.photographer');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(NULL, TRUE);
            $total  = $this->model->where($where)->with('User')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }


    public function edit( $ids = '' )
    {
        $row   = $this->model::get($ids);
        $oUser = User::where('user_id', $row->user_id)->find();
        if ( !$row || !$oUser )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params                     = $this->request->param('row/a');
            $oUser->user_v_wechat       = $params['user_v_wechat'] ?? '';
            $oUser->user_v_wechat_price = $params['user_v_wechat_price'] ?? '';
            if ( $oUser->save($oUser->getData()) === FALSE ) {
                $this->error($oUser->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign("userrow", $oUser);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


    /**
     * delete 删除
     *
     * @param string $ids
     */
    public function delete( $ids = '' )
    {
    }

    /**
     * multi 批量操作
     *
     * @param string $ids
     */
    public function multi( $ids = '' )
    {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort()
    {
    }


}