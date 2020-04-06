<?php

namespace app\admin\controller\subordinate;

use app\common\controller\Backend;
use think\Session;
use app\admin\model\api\Agent as AgentModel;

/**
 * 下级代理
 *
 * @icon fa fa-user
 */
class Agent extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('api.Agent');
        $agent       = $this->model::get($this->auth->id);
        if ( $agent->second_leader != 0 ) {
            $this->error(__('您无法查看此功能', 'expand/share/index'));
        }
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter([ 'strip_tags' ]);
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('nickname');
            $total  = $this->model->where($where)->where('first_leader', 'eq', $this->auth->id)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('first_leader', 'eq', $this->auth->id)->order($sort, $order)->limit($offset, $limit)->select();
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
            $params                             = $this->request->param('row/a');
            $row                                = new AgentModel();
            $row->nickname                      = $params['nickname'];
            $row->account                       = $params['account'];
            $row->recharge_distribution_profits = $params['recharge_distribution_profits'];
            $row->vip_distribution_profits      = $params['vip_distribution_profits'];
            $row->avatar                        = $this->admin['avatar'];
            $row->level                         = $this->admin['level'] + 1;
            if ( strlen($params['password']) < 6 || strlen($params['password']) > 16 ) {
                $this->error(__('Password length can not less than 6 or more than 16'));
            }
            $existData = $row->where("account", $params['account'])->find();
            if ( $existData ) {
                $this->error(__('Exist agent account'));
            }
            $row->validate([
                'nickname'                      => 'require',
                'account'                       => 'require',
                'recharge_distribution_profits' => 'require|between:0,' . $this->admin['recharge_distribution_profits'],
                'vip_distribution_profits'      => 'require|between:0,' . $this->admin['vip_distribution_profits'],
            ], [
                'nickname.require'                      => __('Parameter %s can not be empty', [ __('Nickname') ]),
                'account.require'                       => __('Parameter %s can not be empty', [ __('Username') ]),
                'recharge_distribution_profits.between' => __('%s must be between %d and %d', [
                    __('User recharge distribution of profits'),
                    0,
                    $this->admin['recharge_distribution_profits']
                ]),
                'vip_distribution_profits.between'      => __('%s must be between %d and %d', [
                    __('Buy VIP distribution of profits'),
                    0,
                    $this->admin['vip_distribution_profits']
                ]),
            ]);

            // 先添加 后台需要将代理的邀请码 添加进openinstall 后台 添加渠道

            $invite_code        = $row->createInviteCode();
            $row->invite_code   = $invite_code;
            $row->auth_key      = $row->encryptPassword($params['password'], $invite_code);
            $row->first_leader  = Session::get('admin.id');
            $row->second_leader = Session::get('admin.first_leader');
            $row->third_leader  = Session::get('admin.second_leader');
            $row->create_time   = time();
            $row->status        = 'N';
            $row->update_time   = time();
            if ( !$row->save($row->getData()) ) {
                $this->error($row->getError());
            } else {
                $this->success();
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

        $row = AgentModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');
            if ( $params['password'] ) {
                if ( strlen($params['password']) < 6 || strlen($params['password']) > 16 ) {
                    $this->error(__('Password length can not less than 6 or more than 16'));
                }
                $row->auth_key = $row->encryptPassword($params['password'], $row->invite_code);
            }
            $row->nickname                      = $params['nickname'];
            $row->recharge_distribution_profits = $params['recharge_distribution_profits'];
            $row->vip_distribution_profits      = $params['vip_distribution_profits'];
            $row->validate([
                'nickname'                      => 'require',
                'recharge_distribution_profits' => 'require|between:0,' . $this->admin['recharge_distribution_profits'],
                'vip_distribution_profits'      => 'require|between:0,' . $this->admin['vip_distribution_profits'],
            ], [
                'nickname.require'                      => __('Parameter %s can not be empty', [ __('Nickname') ]),
                'recharge_distribution_profits.between' => __('%s must be between %d and %d', [
                    __('User recharge distribution of profits'),
                    0,
                    $this->admin['recharge_distribution_profits']
                ]),
                'vip_distribution_profits.between'      => __('%s must be between %d and %d', [
                    __('Buy VIP distribution of profits'),
                    0,
                    $this->admin['vip_distribution_profits']
                ]),
            ]);
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
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
            $params          = $this->request->param('params');
            $params          = explode('=', $params);
            $row[$params[0]] = $params[1];
            if ( $params[1] == 'Y' ) {
                $this->error(__('No Rule'));
            }
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }


}
