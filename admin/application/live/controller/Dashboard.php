<?php

namespace app\live\controller;

use app\common\controller\Backend;
use app\live\model\live\User;
use app\live\model\live\Anchor;
use app\live\model\live\UserWithdrawLog;
use app\live\model\live\UserRechargeOrder;
use app\live\model\live\AnchorLiveLog;

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
        // 成员
        $row['member'] = [];
        $row['member']['today_register_total'] = User::where('user_register_time', '>=', strtotime('today'))->where('user_is_isrobot="N"')->count();
        $row['member']['anchor_total']         = Anchor::count();
        $row['member']['isrobot_total']        = User::where('user_is_isrobot="Y"')->count();
        $row['member']['all_user_total']       = User::count();
        $row['member']['user_total']           = $row['member']['all_user_total']-($row['member']['isrobot_total']+$row['member']['anchor_total']);

        // 订单
        $model = new UserRechargeOrder();
        $row['order'] = [];
        $sql = "select count(1) as total_order_count, sum(user_recharge_order_fee) as total_money FROM user_recharge_order
 where user_recharge_order_create_time >= :user_recharge_order_create_time AND user_recharge_order_status = 'Y'";
        $result = $model->query($sql,[
            'user_recharge_order_create_time' => strtotime('today')
        ]);
        $row['order']['today_recharge_total'] = $result[0]['total_order_count'] ?? 0;
        $row['order']['today_recharge_total_money'] = $result[0]['total_money'] ?? 0;
        // 今日充值订单
        // 待处理提现单
        $row['order']['withdraw_total']       = UserWithdrawLog::where('user_withdraw_log_check_status="C"')->count();

        if ($this->request->isAjax()) {
            return $this->success(__('Refresh success'), '', $row);
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

}
