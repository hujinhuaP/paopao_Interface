<?php

namespace app\live\controller\agent;

use app\common\controller\Backend;
use app\live\model\live\AgentWithdrawLog;

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
        $this->model = model('live.agent_withdraw_log');
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
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total  = $this->model->where($where)->with('Agent')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('Agent')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    public function withdrawedit($ids = '')
    {

        $row = AgentWithdrawLog::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            try {

                if ( $row->check_status != 'C' ) {
                    $this->error(__('You have no permission'));
                }


                $row->check_status = $params['check_status'];
                $row->remark       = $params['remark'];
                $row->admin_id     = $this->auth->id;

                $row->status = 'Y';

                $row->validate(
                    [
                        'admin_id'     => 'require',
                        'check_status' => 'require',
                    ],
                    [
                        'admin_id.require'     => __('Parameter %s can not be empty', [ 'admin_id' ]),
                        'check_status.require' => __('Parameter %s can not be empty', [ 'check_status' ]),
                    ]
                );
                $row->getQuery()->startTrans();
                if ( $row->save($row->getData()) === FALSE ) {
                    $row->getQuery()->rollback();
                    $this->error($row->getError());
                }

                switch ( $row->check_status ) {
                    case 'C':
                        break;

                    case 'Y':
                        break;

                    case 'N':
                    default:

                        if ( !$row->feedbackWithdraw($row->getData()) ) {
                            $this->error($row->getError());
                        }
                        break;
                }

                $row->getQuery()->commit();

            } catch ( \Payment\Exceptions\Exception $e ) {
                $row->getQuery()->rollback();
            } catch ( Exception $e ) {
                $row->getQuery()->rollback();
                $this->error($e->getMessage());
            }

            $this->success();
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

}
