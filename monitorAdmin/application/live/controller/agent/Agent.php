<?php

namespace app\live\controller\agent;

use app\live\model\live\Agent as AgentModel;
use think\Exception;
use app\common\controller\Backend;

/**
 * 代理商管理
 */
class Agent extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.agent');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('id', TRUE);
            $total  = $this->model->where($where)->with('InviteAgent')->join('agent invite_agent', 'invite_agent.id=agent.first_leader', 'left')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('InviteAgent')->join('agent invite_agent', 'invite_agent.id=agent.first_leader', 'left')->order($sort, $order)->limit($offset, $limit)->select();
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
        $row = AgentModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        // 比例不能高于上一级别的  并且不能低于下一级别的
        $maxRechargeProfits = 100;
        $maxVipProfits      = 100;
        if ( $row->first_leader ) {
            $firstLeaderAgent = Agent::get($row->first_leader);
            if ( $firstLeaderAgent ) {
                $maxRechargeProfits = $firstLeaderAgent->recharge_distribution_profits;
                $maxVipProfits      = $firstLeaderAgent->vip_distribution_profits;
            }
        }
        // 获取下级最高比例
        $minRechargeProfits = AgentModel::where('first_leader', $ids)->max('recharge_distribution_profits');
        $minVipProfits      = AgentModel::where('first_leader', $ids)->max('vip_distribution_profits');
        if ( !$minRechargeProfits ) {
            $minRechargeProfits = 0;
        }
        if ( !$minVipProfits ) {
            $minVipProfits = 0;
        }
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');
            if ( $params['password'] ) {
                if ( strlen($params['password']) < 6 || strlen($params['password']) > 16 ) {
                    $this->error(__('Password length can not less than 6 or more than 16'));
                }
                $row->auth_key = md5($params['password'] . $row->invite_code);
            }

            $row->recharge_distribution_profits = $params['recharge_distribution_profits'];
            $row->vip_distribution_profits      = $params['vip_distribution_profits'];
            $row->validate([
                'recharge_distribution_profits' => 'require',
                'vip_distribution_profits'      => 'require',
            ], [
                'recharge_distribution_profits.between' => __('%s must be between %d and %d', [
                    __('User recharge distribution of profits'),
                    $minRechargeProfits,
                    $maxRechargeProfits
                ]),
                'vip_distribution_profits.between'      => __('%s must be between %d and %d', [
                    __('Buy VIP distribution of profits'),
                    $minVipProfits,
                    $maxVipProfits
                ]),
            ]);
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        $this->view->assign([
            'minRechargeProfits' => $minRechargeProfits,
            'maxRechargeProfits' => $maxRechargeProfits,
            'minVipProfits'      => $minVipProfits,
            'maxVipProfits'      => $maxVipProfits,
        ]);
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
     *
     * @param  string $ids
     */
    public function multi($ids = '')
    {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort()
    {
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '')
    {
        $row = AgentModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params = $this->request->param('params');
            $params = explode('=', $params);
            if ( !in_array($params[0], [ 'status' ]) ) {
                $this->error(__('No Rule'));
            }
            $row[$params[0]] = $params[1];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }
}