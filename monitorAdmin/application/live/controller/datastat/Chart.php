<?php

namespace app\live\controller\datastat;

use app\common\controller\Backend;

/**
 * 图表数据
 */
class Chart extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.daily_data_stat');
    }

    /**
     * index 图表
     * 每日注册、每月注册、
     * 每日充值订单总数、每月充值订单总数、
     * 每日充值订单金额、每月充值订单金额、
     * 每日成功充值订单数、每月成功充值订单数、
     * 每日成功充值金额、每月成功充值金额、
     * 每日消费金币、每月消费金币、
     * 每日匹配成功、每月匹配成功
     */
    public function index()
    {
        $stat_start_time = date('Y-m-d', strtotime('-1 month'));
        $stat_end_time   = date('Y-m-d');
        if ( $this->request->isAjax() ) {
            $stat_start_time = $this->request->request('stat_start_time');
            $stat_end_time   = $this->request->request('stat_end_time');

            $stat_start_timestamp = strtotime($stat_start_time);
            $stat_end_timestamp   = strtotime($stat_end_time);
            if ( $stat_start_timestamp > $stat_end_timestamp ) {
                $stat_start_time      = date('Y-m-d', strtotime('-1 month'));
                $stat_end_time        = date('Y-m-d');
                $stat_start_timestamp = strtotime($stat_start_time);
                $stat_end_timestamp   = strtotime($stat_end_time);
            }

            $xAxis              = [];
            $xAxisMonth         = [];
            $defaultKeyArr      = [];
            $defaultMonthKeyArr = [];
            for ( $item = $stat_start_timestamp; $item <= $stat_end_timestamp; $item += 24 * 3600 ) {
                $tmp      = date('Y-m-d', $item);
                $monthTmp = date('Y-m', $item);
                $xAxis[]  = $tmp;
                if ( !in_array($monthTmp, $xAxisMonth) ) {
                    $xAxisMonth[] = $monthTmp;
                }
                $defaultKeyArr[$tmp]           = 0;
                $defaultMonthKeyArr[$monthTmp] = 0;
            }
            $registerArr      = $rechargeOrderArr = $rechargeMoneyArr = $rechargeSuccessOrderArr = $rechargeSuccessMoneyArr = $consumeCoinArr = $videoChatSuccessArr = $defaultKeyArr;
            $registerMonthArr = $rechargeOrderMonthArr = $rechargeMoneyMonthArr = $rechargeSuccessOrderMonthArr = $rechargeSuccessMoneyMonthArr = $consumeCoinMonthArr = $videoChatSuccessMonthArr = $defaultMonthKeyArr;

            $data = $this->model::where(
                "stat_time >= $stat_start_timestamp AND stat_time<= $stat_end_timestamp"
            )->order('stat_time desc')->select();
            foreach ( $data as $item ) {
                $dateKey                                     = date('Y-m-d', $item['stat_time']);
                $registerArr[$dateKey]                       = $item['register_ios_male_count'] + $item['register_and_male_count'];
                $rechargeOrderArr[$dateKey]                  = $item['recharge_order_count'];
                $rechargeMoneyArr[$dateKey]                  = $item['recharge_money_count'];
                $rechargeSuccessOrderArr[$dateKey]           = $item['recharge_order_success_count'];
                $rechargeSuccessMoneyArr[$dateKey]           = $item['recharge_money_success_count'];
                $consumeCoinArr[$dateKey]                    = $item['consume_coin_count'];
                $videoChatSuccessArr[$dateKey]               = $item['video_chat_success_count'];
                $dateMonthKey                                = date('Y-m', $item['stat_time']);
                $registerMonthArr[$dateMonthKey]             += $item['register_ios_male_count'] + $item['register_and_male_count'];
                $rechargeOrderMonthArr[$dateMonthKey]        += $item['recharge_order_count'];
                $rechargeMoneyMonthArr[$dateMonthKey]        += sprintf('%.2f', $item['recharge_money_count']);
                $rechargeSuccessOrderMonthArr[$dateMonthKey] += $item['recharge_order_success_count'];
                $rechargeSuccessMoneyMonthArr[$dateMonthKey] += sprintf('%.2f', $item['recharge_money_success_count']);
                $consumeCoinMonthArr[$dateMonthKey]          += sprintf('%.2f', $item['consume_coin_count']);
                $videoChatSuccessMonthArr[$dateMonthKey]     += $item['video_chat_success_count'];

            }
            $result = [
                'start_date' => $stat_start_time,
                'end_date' => $stat_end_time,
                "xAxis"      => $xAxis,
                "rows"       => [
                    'register_count'               => array_values($registerArr),
                    'recharge_order_count'         => array_values($rechargeOrderArr),
                    'recharge_money_count'         => array_values($rechargeMoneyArr),
                    'recharge_order_success_count' => array_values($rechargeSuccessOrderArr),
                    'recharge_money_success_count' => array_values($rechargeSuccessMoneyArr),
                    'consume_coin_count'           => array_values($consumeCoinArr),
                    'video_chat_success_count'     => array_values($videoChatSuccessArr),
                ],
                "xAxisMonth" => $xAxisMonth,
                "rowsMonth"  => [
                    'register_count'               => array_values($registerMonthArr),
                    'recharge_order_count'         => array_values($rechargeOrderMonthArr),
                    'recharge_money_count'         => array_values($rechargeMoneyMonthArr),
                    'recharge_order_success_count' => array_values($rechargeSuccessOrderMonthArr),
                    'recharge_money_success_count' => array_values($rechargeSuccessMoneyMonthArr),
                    'consume_coin_count'           => array_values($consumeCoinMonthArr),
                    'video_chat_success_count'     => array_values($videoChatSuccessMonthArr),
                ]
            ];
            $this->success('', null, $result);
        }
        $this->view->assign([
            'stat_start_time' => $stat_start_time,
            'stat_end_time'   => $stat_end_time,

        ]);
        return $this->view->fetch();
    }


    /**
     * @param string $ids
     * 详情
     */
    public function detail($ids = '')
    {
    }


}