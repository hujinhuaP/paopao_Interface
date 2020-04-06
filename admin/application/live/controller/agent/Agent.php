<?php

namespace app\live\controller\agent;

use app\live\library\Redis;
use app\live\model\live\Agent as AgentModel;
use app\live\model\live\TimeIntervalConfig;
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
     * @param string $ids
     */
    public function edit( $ids = '' )
    {
        $row = AgentModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        // 比例不能高于上一级别的  并且不能低于下一级别的
        $maxRechargeProfits = 100;
        $maxVipProfits      = 100;
        if ( $row->first_leader ) {
            $firstLeaderAgent = AgentModel::get($row->first_leader);
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
            $row->ad_visible                    = $params['ad_visible'];
            $row->agent_recharge_reward_radio   = $params['agent_recharge_reward_radio'];
            $row->stay_guide_msg_flg            = $params['stay_guide_msg_flg'];
            $row->video_guide_flg               = $params['video_guide_flg'];
            $row->video_guide_hour_start        = $params['video_guide_hour_start'];
            $row->video_guide_hour_end          = $params['video_guide_hour_end'];
            $row->chat_free_flg                 = $params['chat_free_flg'];
            $row->has_reward_recharge_max_money = $params['has_reward_recharge_max_money'];
            $row->user_register_reward_flg      = $params['user_register_reward_flg'];
            if ( $row->video_guide_flg == 'S' && $row->video_guide_hour_start == $row->video_guide_hour_end ) {
                $this->error('时间设置不能相同');
            }
            $row->validate([
                'recharge_distribution_profits' => 'require',
                'vip_distribution_profits'      => 'require',
                'agent_recharge_reward_radio'   => 'require',
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
                'agent_recharge_reward_radio.between'   => __('%s must be between %d and %d', [
                    __('奖励概率'),
                    0,
                    100
                ]),
            ]);
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $oRedis = new Redis();
                $oRedis->del('ad_agent', 'ad_agent:stay_guide');
                $this->success();
            }
        }

        $this->view->assign("row", $row);

        // 时间段配置
        $oTimestampConfig = TimeIntervalConfig::all();

        $this->view->assign([
            'timestampConfig'    => $oTimestampConfig,
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
     * @param string $ids
     */
    public function delete( $ids = '' )
    {
    }

    /**
     * multi 批量操作
     *
     * @param string $ids
     */
    public function multi( $ids = '' )
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
     * @param string $ids
     */
    public function status( $ids = '' )
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
            $row[ $params[0] ] = $params[1];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }

    public function selectpage()
    {
        //设置过滤方法
        $this->request->filter([
            'strip_tags',
            'htmlspecialchars'
        ]);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)$this->request->request("q_word/a");
        //当前页
        $page = $this->request->request("page");
        //分页大小
        $pagesize = $this->request->request("per_page");
        //搜索条件
        $andor = $this->request->request("and_or");
        //排序方式
        $orderby = (array)$this->request->request("order_by/a");
        //显示的字段
        $field = $this->request->request("field");
        //主键
        $primarykey = $this->request->request("pkey_name");
        //主键值
        $primaryvalue = $this->request->request("pkey_value");
        //搜索字段
        $searchfield = (array)$this->request->request("search_field/a");
        //自定义搜索条件
        $custom = (array)$this->request->request("custom/a");
        $order  = [];
        foreach ( $orderby as $k => $v ) {
            $order[ $v[0] ] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ( $primaryvalue ) {
            $where = [
                $primarykey => [
                    'in',
                    $primaryvalue
                ]
            ];
        } else {
            $where = function ( $query ) use ( $word, $andor, $field, $searchfield, $custom ) {
                foreach ( $word as $k => $v ) {
                    foreach ( $searchfield as $m => $n ) {
                        $query->where($n, "like", "%{$v}%", $andor);
                    }
                }
                if ( $custom && is_array($custom) ) {
                    foreach ( $custom as $k => $v ) {
                        $query->where($k, '=', $v);
                    }
                }
            };
        }
        $list  = [];
        $total = $this->model->where($where)->count();
        if ( $total > 0 ) {
            $list = $this->model->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field("{$primarykey},{$field}")
                ->field("password,salt", TRUE)
                ->select();
        }

        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        if ( $page == 1 ) {
            array_unshift($list, [
                'id'       => 0,
                'nickname' => '无渠道'
            ]);
        }
        $total += 1;
        return json([
            'list'  => $list,
            'total' => $total
        ]);
    }
}