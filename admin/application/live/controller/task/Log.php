<?php

namespace app\live\controller\task;

use app\common\controller\Backend;


/**
 * 用户任务记录管理
 *
 */
class Log extends Backend
{
    protected $noNeedRight = ['oncelog','dailylog','levellog','anchordailylog'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.once_task_log');
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
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();

            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign($this->getDefaultTimeInterval());
        return $this->view->fetch();
    }

    public function oncelog()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();

            // 统计时间内金币数
            $totalCoin = $this->model->where($where)->sum('once_task_reward_coin');

            $result = [
                "total"      => $total,
                "rows"       => $list,
                'total_coin' => intval($totalCoin)
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    public function dailylog()
    {
        if ( $this->request->isAjax() ) {
            $this->model = model('live.daily_task_log');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();

            // 统计时间内金币数
            $totalCoin = $this->model->where($where)->sum('daily_task_reward_coin');
            $result    = [
                "total"      => $total,
                "rows"       => $list,
                'total_coin' => intval($totalCoin)
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    public function levellog()
    {
        if ( $this->request->isAjax() ) {
            $this->model = model('live.level_reward_log');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();

            // 统计时间内金币数
            $totalCoin = $this->model->where($where)->sum('level_reward_log_coin');
            $result    = [
                "total"      => $total,
                "rows"       => $list,
                'total_coin' => intval($totalCoin)
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    public function anchordailylog()
    {
        if ( $this->request->isAjax() ) {
            $this->model = model('live.anchor_daily_task_log');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();

            // 统计时间内金币数
            $result    = [
                "total"      => $total,
                "rows"       => $list,
            ];
            return json($result);
        }
        return $this->view->fetch();
    }
}