<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

/**
 * Signinlog 签到记录
 */
class Signinlog extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_signin_log');
    }


    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total     = $this->model->where($where)->order($sort, $order)->count();
            $list      = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $totalCoin = $this->model->where($where)->sum('user_signin_coin');
            $result    = [
                "total"      => $total,
                "rows"       => $list,
                'total_coin' => intval($totalCoin)
            ];
            return json($result);
        }
        $this->view->assign($this->getDefaultTimeInterval());
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