<?php

namespace app\live\controller\agent;

use app\live\model\live\Agent;
use think\Config;
use think\Exception;
use app\common\controller\Backend;
use app\live\model\live\Marking as markingModel;

/**
 * 代理商管理 =》 商务人员
 */
class Marking extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.marking');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('realname', TRUE);
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
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

        if ( $this->request->isPost() ) {
            $params        = $this->request->param('row/a');
            $row           = new markingModel();
            $row->realname = $params['realname'];
            $row->qq       = $params['qq'];
            $row->phone    = $params['phone'];
            if ( strlen($params['password']) < 6 || strlen($params['password']) > 16 ) {
                $this->error(__('Password length can not less than 6 or more than 16'));
            }
            if ( $params['recharge_distribution_profits'] < 0 || $params['recharge_distribution_profits'] > 100 ) {
                $this->error(__('%s must be between %d and %d', [
                    __('User recharge distribution of profits'),
                    0,
                    100
                ]));
            }
            if ( $params['vip_distribution_profits'] < 0 || $params['vip_distribution_profits'] > 100 ) {
                $this->error(__('%s must be between %d and %d', [
                    __('Buy VIP distribution of profits'),
                    0,
                    100
                ]));
            }
            $existData = model('live.Agent')->where("account", $params['account'])->find();
            if ( $existData ) {
                $this->error(__('Exist agent account'));
            }
            $row->validate([
                'realname' => 'require',
                //                'qq'       => 'require',
                //                'phone'    => 'require',
            ], [
                'realname.require' => __('Parameter %s can not be empty', [ 'realname' ]),
                //                'qq.require'       => __('Parameter %s can not be empty', ['qq']),
                //                'phone.require'    => __('Parameter %s can not be empty', ['phone']),
            ]);
            if ( !$row->save($row->getData()) ) {
                $this->error($row->getError());
            } else {
                $agent                                = new Agent();
                $invite_code                          = $agent->createInviteCode();
                $agent->invite_code                   = $invite_code;
                $agent->nickname                      = $params['realname'];
                $agent->account                       = $params['account'];
                $agent->auth_key                      = md5($params['password'] . $invite_code);
                $agent->marking_id                    = $row->id;
                $agent->recharge_distribution_profits = $params['recharge_distribution_profits'];
                $agent->vip_distribution_profits      = $params['vip_distribution_profits'];
                $agent->level                         = 1;
                $agent->avatar                        = 'http://api.sxypaopao.com/assets/images/logo.png';
                if ( !$agent->save($agent->getData()) ) {
                    $this->error($agent->getError());
                } else {
                    $this->success();
                }
            }
        }
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {

        $row   = MarkingModel::get($ids);
        $agent = Agent::get([ 'marking_id' => $ids ]);
        if ( !$row || !$agent )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');
            if ( $params['password'] ) {
                if ( strlen($params['password']) < 6 || strlen($params['password']) > 16 ) {
                    $this->error(__('Password length can not less than 6 or more than 16'));
                }
            }
            $row->realname = $params['realname'];
            $row->qq       = $params['qq'];
            $row->phone    = $params['phone'];
            $row->validate([
                'realname' => 'require',
                'qq'       => 'require',
                'phone'    => 'require',
            ], [
                'realname.require' => __('Parameter %s can not be empty', [ 'realname' ]),
                'qq.require'       => __('Parameter %s can not be empty', [ 'qq' ]),
                'phone.require'    => __('Parameter %s can not be empty', [ 'phone' ]),
            ]);
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                if ( $params['password'] ) {
                    $agent->auth_key = md5($params['password'] . $agent->invite_code);
                    if ( !$agent->save($agent->getData()) ) {
                        $this->error($agent->getError());
                    } else {
                        $this->success();
                    }
                }
                $this->success();
            }
        }
        $this->view->assign("row", $row);
        $this->view->assign("agent", $agent);
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
        $row = MarkingModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params          = $this->request->param('params');
            $params          = explode('=', $params);
            $row[$params[0]] = $params[1];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                if ( $params[0] == 'status' ) {
                    $agent           = Agent::get([ 'marking_id' => $ids ]);
                    $agent['status'] = $params[1];
                    if ( $agent->save() === FALSE ) {
                        $row[$params[0]] = $params[1] == 'Y' ? 'N' : 'Y';
                        $row->save();
                        $this->error($row->getError());
                    } else {
                        $this->success();
                    }
                }
                $this->success();
            }
        }
    }
}