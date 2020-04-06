<?php

namespace app\live\controller\user;

use app\common\controller\Backend;

/**
 * 用户在线统计
 *
 */
class Online extends Backend
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
                'online' => [
                    'time' => date('Y-m-d H:00:00') . '--' . date('Y-m-d H:i:s'),
                    'user' => $this->model->getOnline('user'),
                    'anchor' => $this->model->getOnline('anchor'),
                ]
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