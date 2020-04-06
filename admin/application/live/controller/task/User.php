<?php

namespace app\live\controller\task;

use app\common\controller\Backend;
use app\live\library\Redis;


/**
 * 用户任务管理
 *
 */
class User extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.task_config');
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

            $row->task_name         = $params['task_name'];
            $row->task_finish_times = $params['task_finish_times'];
            $row->task_reward_coin  = $params['task_reward_coin'];
            $row->task_reward_exp   = $params['task_reward_exp'];
            $row->task_reward_dot   = $params['task_reward_dot'];
            $row->task_on           = $params['task_on'];

            $row->validate(
                [
                    'task_name'         => 'require',
                    'task_finish_times' => 'require',
                    'task_on'           => 'require',

                ],
                [
                    'task_name.require'         => __('Parameter %s can not be empty', [ '名称' ]),
                    'task_finish_times.require' => __('Parameter %s can not be empty', [ '完成所需次数' ]),
                    'task_on.require'           => __('Parameter %s can not be empty', [ '是否开启' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }

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

    /**
     * 每日任务记录
     */
    public function oncelog()
    {
        if ( $this->request->isAjax() ) {
            $this->model = model('live.once_task_log');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,TRUE);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();

            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * sort 排序
     */
    public function sort()
    {
        if ($this->request->isPost()) {
            $ids      = $this->request->post('ids');
            $changeid = $this->request->post("changeid");
            $orderway = $this->request->post("orderway", 'strtolower');
            $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
            $ids = explode(',', $ids);

            if ($orderway == 'DESC') {
                $ids = array_reverse($ids);
            }

            foreach ($ids as $k=>$id)
            {
                $this->model::where('task_id', $id)->update(['task_sort' => $k + 1, 'task_update_time'=>time()]);
            }
            $this->success();
        }
        $this->error();
    }
}