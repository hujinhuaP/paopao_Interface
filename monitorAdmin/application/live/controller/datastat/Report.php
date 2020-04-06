<?php

namespace app\live\controller\datastat;

use app\common\controller\Backend;

/**
 * 数据报表
 */
class Report extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.daily_data_stat');
    }

    /**
     * index 列表
     */
    public function index()
    {
        $default_date = date('Y-m-d');
        if ( $this->request->isAjax() ) {
            $filter    = $this->request->get("filter", '');
            $filter    = json_decode($filter, TRUE);
            $filter    = $filter ? $filter : [];
            $stat_date = $default_date;
            if ( isset($filter['stat_time']) ) {
                $stat_date = $filter['stat_time'];
                if ( date('Y-m-d', strtotime($stat_date)) != $stat_date ) {
                    $stat_date = $default_date;
                }
            }
            $start_datetime       = strtotime($stat_date) - 29 * 24 * 3600;
            $end_datetime         = strtotime($stat_date);
            $current_datetime     = $end_datetime;
            $last_datetime        = $current_datetime - 24 * 3600;
            $seven_start_datetime = $current_datetime - 6 * 24 * 3600;
            $seven_end_datetime   = $current_datetime;

            $thirty_start_datetime = $start_datetime;
            $thirty_end_datetime   = $current_datetime;


            $currentArr = $lastArr = $sevenArr = $thirtyArr = [
                'new_register'                 => 0,
                'active_user_count'            => 0,
                'video_chat_success_count'     => 0,
                'recharge_order_count'         => 0,
                'recharge_money_count'         => 0,
                'recharge_order_success_count' => 0,
                'recharge_money_success_count' => 0,
                'recharge_success_user_count'  => 0,
                'recharge_rate'                => 0
            ];

            $data = $this->model::where(
                "stat_time >= $start_datetime AND stat_time<= $end_datetime"
            )->order('stat_time desc')->select();
            foreach ( $data as $item ) {
                if ( $item['stat_time'] == $current_datetime ) {
                    $currentArr = [
                        'new_register'                 => $item['register_ios_male_count'] + $item['register_and_male_count'],
                        'active_user_count'            => $item['active_user_count'],
                        'video_chat_success_count'     => $item['video_chat_success_count'],
                        'recharge_order_count'         => $item['recharge_order_count'],
                        'recharge_money_count'         => $item['recharge_money_count'],
                        'recharge_order_success_count' => $item['recharge_order_success_count'],
                        'recharge_money_success_count' => $item['recharge_money_success_count'],
                        'recharge_success_user_count'  => $item['recharge_success_user_count'],
                        'recharge_rate'                => ($item['register_ios_male_count'] + $item['register_and_male_count']) > 0 ? round($item['recharge_success_user_count'] / ($item['register_ios_male_count'] + $item['register_and_male_count']) * 100, 2) : 0,
                    ];
                } else if ( $item['stat_time'] == $last_datetime ) {
                    $lastArr = [
                        'new_register'                 => $item['register_ios_male_count'] + $item['register_and_male_count'],
                        'active_user_count'            => $item['active_user_count'],
                        'video_chat_success_count'     => $item['video_chat_success_count'],
                        'recharge_order_count'         => $item['recharge_order_count'],
                        'recharge_money_count'         => $item['recharge_money_count'],
                        'recharge_order_success_count' => $item['recharge_order_success_count'],
                        'recharge_money_success_count' => $item['recharge_money_success_count'],
                        'recharge_success_user_count'  => $item['recharge_success_user_count'],
                        'recharge_rate'                => ($item['register_ios_male_count'] + $item['register_and_male_count']) > 0 ? round($item['recharge_success_user_count'] / ($item['register_ios_male_count'] + $item['register_and_male_count']) * 100, 2) : 0,
                    ];
                }

                if ( $item['stat_time'] >= $seven_start_datetime ) {
                    $sevenArr['new_register']                 += $item['register_ios_male_count'] + $item['register_and_male_count'];
                    $sevenArr['active_user_count']            += $item['active_user_count'];
                    $sevenArr['video_chat_success_count']     += $item['video_chat_success_count'];
                    $sevenArr['recharge_order_count']         += $item['recharge_order_count'];
                    $sevenArr['recharge_money_count']         += $item['recharge_money_count'];
                    $sevenArr['recharge_order_success_count'] += $item['recharge_order_success_count'];
                    $sevenArr['recharge_money_success_count'] += $item['recharge_money_success_count'];
                    $sevenArr['recharge_success_user_count']  += $item['recharge_success_user_count'];
                    $sevenArr['recharge_rate']                = $sevenArr['new_register'] > 0 ? round($sevenArr['recharge_success_user_count'] / $sevenArr['new_register'] * 100, 2) : 0;
                }

                $thirtyArr['new_register']                 += $item['register_ios_male_count'] + $item['register_and_male_count'];
                $thirtyArr['active_user_count']            += $item['active_user_count'];
                $thirtyArr['video_chat_success_count']     += $item['video_chat_success_count'];
                $thirtyArr['recharge_order_count']         += $item['recharge_order_count'];
                $thirtyArr['recharge_money_count']         += $item['recharge_money_count'];
                $thirtyArr['recharge_order_success_count'] += $item['recharge_order_success_count'];
                $thirtyArr['recharge_money_success_count'] += $item['recharge_money_success_count'];
                $thirtyArr['recharge_success_user_count']  += $item['recharge_success_user_count'];
                $thirtyArr['recharge_rate']                = $thirtyArr['new_register'] > 0 ? round($thirtyArr['recharge_success_user_count'] / $thirtyArr['new_register'] * 100, 2) : 0;
            }

            $rows   = [
                [
                    'item_name'    => '新增注册用户',
                    'current_data' => $currentArr['new_register'],
                    'last_data'    => $lastArr['new_register'],
                    'seven_data'   => $sevenArr['new_register'],
                    'thirty_data'  => $thirtyArr['new_register'],
                ],
                [
                    'item_name'    => '活跃用户',
                    'current_data' => $currentArr['active_user_count'],
                    'last_data'    => $lastArr['active_user_count'],
                    'seven_data'   => $sevenArr['active_user_count'],
                    'thirty_data'  => $thirtyArr['active_user_count'],
                ],
                [
                    'item_name'    => '匹配次数',
                    'current_data' => $currentArr['video_chat_success_count'],
                    'last_data'    => $lastArr['video_chat_success_count'],
                    'seven_data'   => $sevenArr['video_chat_success_count'],
                    'thirty_data'  => $thirtyArr['video_chat_success_count'],
                ],
                [
                    'item_name'    => '充值订单数',
                    'current_data' => $currentArr['recharge_order_count'],
                    'last_data'    => $lastArr['recharge_order_count'],
                    'seven_data'   => $sevenArr['recharge_order_count'],
                    'thirty_data'  => $thirtyArr['recharge_order_count'],
                ],
                [
                    'item_name'    => '充值订单金额',
                    'current_data' => $currentArr['recharge_money_count'],
                    'last_data'    => $lastArr['recharge_money_count'],
                    'seven_data'   => $sevenArr['recharge_money_count'],
                    'thirty_data'  => $thirtyArr['recharge_money_count'],
                ],
                [
                    'item_name'    => '成功充值订单金数',
                    'current_data' => $currentArr['recharge_order_success_count'],
                    'last_data'    => $lastArr['recharge_order_success_count'],
                    'seven_data'   => $sevenArr['recharge_order_success_count'],
                    'thirty_data'  => $thirtyArr['recharge_order_success_count'],
                ],
                [
                    'item_name'    => '成功充值订单金额',
                    'current_data' => $currentArr['recharge_money_success_count'],
                    'last_data'    => $lastArr['recharge_money_success_count'],
                    'seven_data'   => $sevenArr['recharge_money_success_count'],
                    'thirty_data'  => $thirtyArr['recharge_money_success_count'],
                ],
                [
                    'item_name'    => '充值转换率',
                    'current_data' => $currentArr['recharge_rate'],
                    'last_data'    => $lastArr['recharge_rate'],
                    'seven_data'   => $sevenArr['recharge_rate'],
                    'thirty_data'  => $thirtyArr['recharge_rate'],
                ]
            ];
            $result = [
                "total" => count($rows),
                "rows"  => $rows
            ];
            return json($result);
        }
        $this->view->assign('default_date', $default_date);
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