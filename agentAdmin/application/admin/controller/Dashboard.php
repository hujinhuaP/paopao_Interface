<?php

namespace app\admin\controller;

use app\admin\model\api\Agent;
use app\admin\model\api\AgentDailyStat;
use app\common\controller\Backend;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        // 获取今日数据
        $todayStat = AgentDailyStat::get([
            'agent_id'  => $this->auth->id,
            'stat_time' => strtotime(date('Y-m-d'))
        ]);
        $oAgent    = Agent::get($this->auth->id);
        $this->view->assign([
            'todayStat' => $todayStat,
            'oAgent'    => $oAgent,
        ]);

        return $this->view->fetch();
    }

}
