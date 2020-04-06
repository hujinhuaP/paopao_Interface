<?php

namespace app\admin\controller\finance;

use app\admin\model\api\AgentWithdrawAccount;
use app\admin\model\api\AgentWithdrawLog;
use app\admin\model\api\Agent as AgentModel;
use app\common\controller\Backend;
use app\live\model\live\AgentWaterLog;

/**
 * 财务
 *
 * @icon fa fa-user
 */
class Finance extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('api.agent_withdraw_log');
    }

    /**
     * 查看
     */
    public function agent()
    {
        $this->request->filter([ 'strip_tags' ]);
        $this->model = model('api.agent_water_log');
        if ( $this->request->isAjax() ) {
            $agent_id = $this->auth->id;
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total  = $this->model->where($where)->where('agent_water_log.agent_id = ' . $agent_id)
                ->join('agent source_agent', 'source_agent.id = agent_water_log.source_agent_id')
                ->with('User,SourceAgent')
                ->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('agent_water_log.agent_id = ' . $agent_id)
                ->join('agent source_agent', 'source_agent.id = agent_water_log.source_agent_id')
                ->with('User,SourceAgent')
                ->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * @return string|\think\response\
     * 提现
     */
    public function withdraw()
    {
        $this->request->filter([ 'strip_tags' ]);
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where('agent_id', $this->auth->id)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('agent_id', $this->auth->id)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 申请提现
     */
    public function addwithdraw()
    {
        // 查出之前的账号
        $oAgentWithdrawAccount = AgentWithdrawAccount::get([ 'agent_id' => $this->auth->id ]);
        $oAgent                = AgentModel::get($this->auth->id);
        if ( $this->request->isPost() ) {
            $params   = $this->request->param('row/a');
            $password = $params['password'];
            if ( $oAgent->auth_key != md5($password . $oAgent->invite_code) ) {
                $this->error(__('Password is incorrect'));
            }
            if($oAgent->commission < $params['withdraw_cash']){
                $this->error(__('余额不足'));
            }
            $inWithdrawMoney           = AgentWithdrawLog::where("agent_id = {$this->auth->id} AND check_status = 'C'")->sum('withdraw_cash');
            $sMicrotime                = sprintf('%.10f', microtime(1));
            $aTime                     = explode('.', $sMicrotime);
            $row                       = new AgentWithdrawLog();
            $row->user_realname        = $params['user_realname'];
            $row->withdraw_account     = $params['withdraw_account'];
            $row->withdraw_cash        = $params['withdraw_cash'];
            $row->agent_id             = $this->auth->id;
            $row->agent_money          = $oAgent->commission - $params['withdraw_cash'];
            $row->total_withdraw_money = $oAgent->total_withdraw - $inWithdrawMoney;
            $row->withdraw_log_number  = date('YmdHis', $aTime[0]) . $aTime[1];
            $row->validate([
                'user_realname'    => 'require',
                'withdraw_account' => 'require',
                'withdraw_cash'    => 'require|between:0,' . $oAgent->commission,
            ], [
                'user_realname.require'    => __('Parameter %s can not be empty', [ __('Real name') ]),
                'withdraw_account.require' => __('Parameter %s can not be empty', [ __('Withdraw account') ]),
                'withdraw_cash.between'    => __('%s must be between %d and %d', [
                    __('Amount'),
                    0,
                    $oAgent->commission
                ]),
            ]);
            if ( !$row->addWithdraw($row->getData()) ) {
                $this->error($row->getError());
            } else {
                // 修改或保存提现账号
                if ( !$oAgentWithdrawAccount ) {
                    $oAgentWithdrawAccount = new AgentWithdrawAccount();
                }
                $oAgentWithdrawAccount->agent_id         = $this->auth->id;
                $oAgentWithdrawAccount->withdraw_account = $params['withdraw_account'];
                $oAgentWithdrawAccount->user_realname    = $params['user_realname'];
                $oAgentWithdrawAccount->save();
                $this->success();
            }
        }
        $this->view->assign('oAgent', $oAgent);
        $this->view->assign('oAgentWithdrawAccount', $oAgentWithdrawAccount);
        return $this->view->fetch();
    }

}
