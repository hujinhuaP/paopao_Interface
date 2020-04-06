<?php

namespace app\live\controller\datastat;

use app\common\controller\Backend;

/**
 * 分段统计
 *
 */
class Interval extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.interval_stat_log');
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

            //当前在线用户  当前在线主播
            $result = [
                "total" => $total,
                "rows"  => $list,
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
		# code...
	}
}