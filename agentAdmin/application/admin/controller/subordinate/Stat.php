<?php

namespace app\admin\controller\subordinate;

use app\admin\model\api\Agent as AgentModel;
use app\common\controller\Backend;

/**
 * 代理商管理 =》 统计
 */
class Stat extends Backend {
    public function _initialize() {
        parent::_initialize();
        $this->model = model('api.agent_daily_stat');
    }

    /**
     * index 列表
     */
    public function index() {
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('agent_id', true);
            $total  = $this->model->where($where)->where('agent.id = '.$this->auth->id)->with('Agent')->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('agent.id = '.$this->auth->id)->with('Agent')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add() {

    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '') {

    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '') {
    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids = '') {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort() {
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '') {

    }

    /**
     * 下级代理数据
     */
    public function child($ids = '')
    {
        $row = $this->model::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $agent = AgentModel::get($row->agent_id);
        if($agent->id != $this->auth->id && $agent->first_leader != $this->auth->id && $agent->second_leader != $this->auth->id ){
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $agent_id = $row->agent_id;
            $stat_time = $row->stat_time;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('agent_id', true);
            $total  = $this->model->where($where)->where('agent.first_leader = '.$agent_id)->where('stat_time ='.$stat_time)->with('Agent')->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('agent.first_leader = '.$agent_id)->where('stat_time ='.$stat_time)->with('Agent')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('row',$row);
        $this->view->assign('agent',$agent);
        return $this->view->fetch();
    }


    /**
     * 收益详情
     */
    public function income($ids = '')
    {
        $row = $this->model::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $agent = AgentModel::get($row->agent_id);
        if($row->agent_id != $this->auth->id && $agent->first_leader != $this->auth->id && $agent->second_leader != $this->auth->id ){
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $this->model = model('api.agent_water_log');
            $agent_id = $row->agent_id;
            $stat_time = $row->stat_time;
            $start = $stat_time;
            $end = $stat_time + 24 * 3600;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, true);
            $total  = $this->model->where($where)->where('agent_water_log.agent_id = '.$agent_id)
                ->where('agent_water_log.create_time >='.$start)
                ->where('agent_water_log.create_time <'.$end)
                ->join('agent source_agent','source_agent.id = agent_water_log.source_agent_id')
                ->with('User,SourceAgent')
                ->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('agent_water_log.agent_id = '.$agent_id)
                ->where('agent_water_log.create_time >='.$start)
                ->where('agent_water_log.create_time <'.$end)
                ->join('agent source_agent','source_agent.id = agent_water_log.source_agent_id')
                ->with('User,SourceAgent')
                ->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('row',$row);
        $this->view->assign('agent',$agent);
        return $this->view->fetch();
    }


}