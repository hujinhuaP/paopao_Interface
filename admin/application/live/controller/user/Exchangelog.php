<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

/**
 * 用户"现金"兑换记录管理
 */
class Exchangelog extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_exchange_log');
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
        $this->view->assign($this->getDefaultTimeInterval());
        return $this->view->fetch();
    }

    /**
     * detail 详情
     * 
     * @param  string $ids
     */
    public function detail($ids='')
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
    public function edit($ids='')
    {
        
    }

    /**
     * delete 删除
     * 
     * @param  string $ids
     */
    public function delete($ids='')
    {
        
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids='')
    {
        
    }
}