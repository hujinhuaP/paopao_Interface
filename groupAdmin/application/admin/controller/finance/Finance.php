<?php

namespace app\admin\controller\finance;

use app\admin\model\api\GroupIncomeStat as GroupIncomeStatModel;
use app\admin\model\api\User;
use app\common\controller\Backend;
use think\Db;
use think\Session;

/**
 * 财务
 *
 * @icon fa fa-user
 */
class Finance extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $offset = $this->request->get("offset", 0);
            if ( $offset == 0 ) {
                $today      = strtotime(date('Y-m-d 00:00:00'));
                $group_id   = Session::get('admin.group_id');
                $todayWhere = [
                    'group_id'  => $group_id,
                    'stat_time' => $today,
                    'user_id'   => 0
                ];
                $row        = GroupIncomeStatModel::get($todayWhere);
                if ( !$row || $row->update_time < time() - 60 ) {
                    $group         = Session::get('admin.group');
                    $divid_type    = $group['divid_type'];
                    $divid_precent = $group['divid_precent'];
                    // 不存在 或者 上次统计时间已过1分钟
                    // 计算当天的数据 并存入数据库
                    $saveDefault = [
                        'stat_time'            => $today,
                        'group_id'             => $group_id,
                        'user_id'              => 0,
                        'anchor_time_income'   => 0,
                        'anchor_gift_income'   => 0,
                        'wx_sale_income'       => 0,
                        'anchor_guard_income'  => 0,
                        'word_msg_income'      => 0,
                        'voice_msg_income'     => 0,
                        'image_msg_income'     => 0,
                        'invite_reward_income' => 0,
                        'group_divid_income'   => 0,
                        'group_total_income'   => 0,
                        'time_total'           => 0,
                        'divid_type'           => $divid_type,
                        'video_income'         => 0,
                    ];
                    $sql         = "select
sum(case consume_category_id when 23 then consume else 0 end ) as word_chat_dot_total,
sum(case consume_category_id when 23 then consume_source else 0 end ) as word_chat_coin_total,
sum(case consume_category_id when 22 then consume else 0 end ) as video_dot_total,
sum(case consume_category_id when 22 then consume_source else 0 end ) as video_coin_total,
sum(case consume_category_id when 21 then consume when 25 then consume else 0 end ) as invite_reward_dot_total,
sum(case consume_category_id when 21 then consume_source else 0 end ) as invite_reward_coin_total,
sum(case consume_category_id when 6 then consume else 0 end ) as gift_dot_total,
sum(case consume_category_id when 6 then consume_source else 0 end ) as gift_coin_total,
sum(case consume_category_id when 17 then consume else 0 end ) as time_dot_total,
sum(case consume_category_id when 17 then extra_number else 0 end ) as time_total,
sum(case consume_category_id when 17 then consume_source else 0 end ) as time_coin_total,
sum(case consume_category_id when 36 then consume else 0 end ) as guard_dot_total,
sum(case consume_category_id when 27 then consume_source else 0 end ) as chat_game_coin_total,
sum(case consume_category_id when 27 then consume else 0 end ) as chat_game_total
from user_finance_log where group_id = $group_id AND consume_category_id in (17,6,21,22,23,28,31) AND user_amount_type = 'dot'
and create_time >= $today";
                    $data        = GroupIncomeStatModel::query($sql);
                    if ( $data ) {
                        $tmp                         = $data[0];
                        $tmp['gift_dot_total']       = abs($tmp['gift_dot_total']);
                        $tmp['gift_coin_total']      = abs($tmp['gift_coin_total']);
                        $tmp['time_dot_total']       = abs($tmp['time_dot_total']);
                        $tmp['time_coin_total']      = abs($tmp['time_coin_total']);
                        $tmp['word_chat_dot_total']  = abs($tmp['word_chat_dot_total']);
                        $tmp['word_chat_coin_total'] = abs($tmp['word_chat_coin_total']);
                        $tmp['video_dot_total']      = abs($tmp['video_dot_total']);
                        $tmp['video_coin_total']     = abs($tmp['video_coin_total']);
                        $tmp['time_total']           = abs($tmp['time_total']);

                        $tmp['guard_dot_total']      = abs($tmp['guard_dot_total']);
                        $tmp['chat_game_coin_total'] = abs($tmp['chat_game_coin_total']);
                        $tmp['chat_game_total']      = abs($tmp['chat_game_total']);

                        $saveDefault['time_total']           = $tmp['time_total'];
                        $saveDefault['anchor_gift_income']   = $tmp['gift_dot_total'];
                        $saveDefault['anchor_time_income']   = $tmp['time_dot_total'];
                        $saveDefault['video_income']         = $tmp['video_dot_total'];
                        $saveDefault['word_msg_income']      = $tmp['word_chat_dot_total'];
                        $saveDefault['invite_reward_income'] = abs($tmp['invite_reward_dot_total']);

                        $saveDefault['anchor_guard_income'] = $tmp['guard_dot_total'];
                        $saveDefault['chat_game_income']    = $tmp['chat_game_total'];
                        if ( $divid_type == 0 ) {
                            //主播收益分成
                            $saveDefault['group_divid_income'] = round(($tmp['time_dot_total'] + $tmp['gift_dot_total'] + $tmp['word_chat_dot_total'] + $tmp['video_dot_total'] + $tmp['chat_game_total']) * $divid_precent / 100, 2);
                        } else {
                            //主播流水分成  还需要除以一个 充值比例转换值 10
                            $saveDefault['group_divid_income'] = round(($tmp['time_coin_total'] + $tmp['gift_coin_total'] + $tmp['word_chat_coin_total'] + $tmp['video_coin_total'] + $tmp['chat_game_coin_total']) * $divid_precent / 100 / 10, 2);
                        }
                        $saveDefault['group_total_income'] = $saveDefault['anchor_gift_income'] + $saveDefault['anchor_time_income'] + $saveDefault['invite_reward_income'] + $saveDefault['group_divid_income'] + $saveDefault['video_income'] + $saveDefault['word_msg_income'] + $saveDefault['anchor_guard_income'] + $saveDefault['chat_game_income'];
                    }
                    $newRow      = new GroupIncomeStatModel();
                    $updateWhere = [];
                    if ( $row ) {
                        $updateWhere = $todayWhere;
                    }
                    $newRow->save($saveDefault, $updateWhere);
                }
            }

            $this->model = new GroupIncomeStatModel();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $where_str = 'user_id = 0 and group_id =' . Session::get('admin.group_id');

            $total  = GroupIncomeStatModel::where($where_str)->where($where)->order($sort, $order)->count();
            $list   = GroupIncomeStatModel::where($where_str)->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 主播收益
     */
    public function user()
    {
        // 获取主播总收益
        if ( $this->request->isAjax() ) {
            $offset = $this->request->get("offset", 0);
            if ( $offset == 0 ) {
                $today      = strtotime(date('Y-m-d 00:00:00'));
                $group_id   = Session::get('admin.group_id');
                $todayWhere = [
                    'group_id'  => $group_id,
                    'stat_time' => $today,
                    'user_id'   => [
                        'neq',
                        0
                    ]
                ];
                $row        = GroupIncomeStatModel::get($todayWhere);
                if ( !$row || $row->update_time < time() - 60 ) {
//                    // 不存在 或者 上次统计时间已过1分钟
//                    // 计算当天旗下主播的数据 并存入数据库

                    $user = User::all([
                        'user_group_id'  => $group_id,
                        'user_is_anchor' => 'Y'
                    ]);
                    if ( $user ) {
                        $group         = Session::get('admin.group');
                        $divid_type    = $group['divid_type'];
                        $divid_precent = $group['divid_precent'];

                        $allSaveArr  = [];
                        $saveDefault = [
                            'stat_time'            => $today,
                            'group_id'             => $group_id,
                            'user_id'              => 0,
                            'anchor_time_income'   => 0,
                            'anchor_gift_income'   => 0,
                            'wx_sale_income'       => 0,
                            'anchor_guard_income'  => 0,
                            'word_msg_income'      => 0,
                            'voice_msg_income'     => 0,
                            'image_msg_income'     => 0,
                            'invite_reward_income' => 0,
                            'group_divid_income'   => 0,
                            'group_total_income'   => 0,
                            'chat_game_income'     => 0,
                            'time_total'           => 0,
                            'divid_type'           => $divid_type,
                            'video_income'         => 0,
                        ];
                        foreach ( $user as $userItem ) {
                            $tmp                              = $saveDefault;
                            $tmp['user_id']                   = $userItem['user_id'];
                            $allSaveArr[$userItem['user_id']] = $tmp;
                        }
                        $sql  = "select user_id,
sum(case consume_category_id when 23 then consume else 0 end ) as word_chat_dot_total,
sum(case consume_category_id when 23 then consume_source else 0 end ) as word_chat_coin_total,
sum(case consume_category_id when 22 then consume else 0 end ) as video_dot_total,
sum(case consume_category_id when 22 then consume_source else 0 end ) as video_coin_total,
sum(case consume_category_id when 21 then consume when 25 then consume else 0 end ) as invite_reward_dot_total,
sum(case consume_category_id when 21 then consume_source else 0 end ) as invite_reward_coin_total,
sum(case consume_category_id when 6 then consume else 0 end ) as gift_dot_total,
sum(case consume_category_id when 6 then consume_source else 0 end ) as gift_coin_total,
sum(case consume_category_id when 17 then consume else 0 end ) as time_dot_total,
sum(case consume_category_id when 17 then extra_number else 0 end ) as time_total,
sum(case consume_category_id when 17 then consume_source else 0 end ) as time_coin_total,
sum(case consume_category_id when 36 then consume else 0 end ) as guard_dot_total,
sum(case consume_category_id when 27 then consume_source else 0 end ) as chat_game_coin_total,
sum(case consume_category_id when 27 then consume else 0 end ) as chat_game_total
 from user_finance_log where user_amount_type = 'dot' AND consume_category_id in (17,6,21,22,23,28,31) and create_time >= $today AND group_id = $group_id group by user_id";
                        $data = GroupIncomeStatModel::query($sql);


                        foreach ( $data as $resultItem ) {
                            $itemKey                                = $resultItem['user_id'];
                            $resultItem['time_dot_total']           = abs($resultItem['time_dot_total']);
                            $resultItem['time_coin_total']          = abs($resultItem['time_coin_total']);
                            $resultItem['gift_dot_total']           = abs($resultItem['gift_dot_total']);
                            $resultItem['gift_coin_total']          = abs($resultItem['gift_coin_total']);
                            $resultItem['time_total']               = abs($resultItem['time_total']);
                            $resultItem['invite_reward_dot_total']  = abs($resultItem['invite_reward_dot_total']);
                            $resultItem['invite_reward_coin_total'] = abs($resultItem['invite_reward_coin_total']);
                            $resultItem['word_chat_dot_total']      = abs($resultItem['word_chat_dot_total']);
                            $resultItem['word_chat_coin_total']     = abs($resultItem['word_chat_coin_total']);
                            $resultItem['video_dot_total']          = abs($resultItem['video_dot_total']);
                            $resultItem['video_coin_total']         = abs($resultItem['video_coin_total']);
                            $resultItem['guard_dot_total']          = abs($resultItem['guard_dot_total']);
                            $resultItem['chat_game_coin_total']     = abs($resultItem['chat_game_coin_total']);
                            $resultItem['chat_game_total']          = abs($resultItem['chat_game_total']);
                            if ( isset($allSaveArr[$itemKey]) ) {
                                if ( $divid_type == 0 ) {
                                    //主播收益分成
                                    $allSaveArr[$itemKey]['group_divid_income'] = round(($resultItem['time_dot_total'] + $resultItem['gift_dot_total'] + $resultItem['word_chat_dot_total'] + $resultItem['video_dot_total'] + $resultItem['chat_game_total']) * $divid_precent / 100, 2);
                                } else {
                                    //主播流水分成  还需要除以一个 充值比例转换值 10
                                    $allSaveArr[$itemKey]['group_divid_income'] = round(($resultItem['time_coin_total'] + $resultItem['gift_coin_total'] + $resultItem['word_chat_coin_total'] + $resultItem['video_coin_total'] + $resultItem['chat_game_coin_total']) * $divid_precent / 100 / 10, 2);
                                }
                                $allSaveArr[$itemKey]['divid_type']           = $divid_type;
                                $allSaveArr[$itemKey]['time_total']           = $resultItem['time_total'];
                                $allSaveArr[$itemKey]['anchor_time_income']   = $resultItem['time_dot_total'];
                                $allSaveArr[$itemKey]['anchor_gift_income']   = $resultItem['gift_dot_total'];
                                $allSaveArr[$itemKey]['video_income']         = $resultItem['video_dot_total'];
                                $allSaveArr[$itemKey]['word_msg_income']      = $resultItem['word_chat_dot_total'];
                                $allSaveArr[$itemKey]['invite_reward_income'] = $resultItem['invite_reward_dot_total'];
                                $allSaveArr[$itemKey]['anchor_guard_income']  = $resultItem['guard_dot_total'];
                                $allSaveArr[$itemKey]['chat_game_income']     = $resultItem['chat_game_total'];
                                $allSaveArr[$itemKey]['group_total_income']   = $allSaveArr[$itemKey]['anchor_time_income'] + $allSaveArr[$itemKey]['anchor_gift_income'] + $allSaveArr[$itemKey]['invite_reward_income'] + $allSaveArr[$itemKey]['group_divid_income'] + $allSaveArr[$itemKey]['word_msg_income'] + $allSaveArr[$itemKey]['video_income'] + $allSaveArr[$itemKey]['anchor_guard_income'] + $allSaveArr[$itemKey]['chat_game_income'];
                            } else {
                                $tmp             = $saveDefault;
                                $tmp['group_id'] = $group_id;
                                $tmp['user_id']  = $resultItem['user_id'];
                                if ( $divid_type == 0 ) {
                                    //主播收益分成
                                    $tmp['group_divid_income'] = round(($resultItem['time_dot_total'] + $resultItem['gift_dot_total'] + $resultItem['word_chat_dot_total'] + $resultItem['video_dot_total'] + $resultItem['chat_game_total']) * $divid_precent / 100, 2);
                                } else {
                                    //主播流水分成  还需要除以一个 充值比例转换值 10
                                    $tmp['group_divid_income'] = round(($resultItem['time_coin_total'] + $resultItem['gift_coin_total'] + $resultItem['word_chat_coin_total'] + $resultItem['video_coin_total'] + $resultItem['chat_game_coin_total']) * $divid_precent / 100 / 10, 2);
                                }
                                $tmp['divid_type']           = $divid_type;
                                $tmp['time_total']           = $resultItem['time_total'];
                                $tmp['anchor_time_income']   = $resultItem['time_dot_total'];
                                $tmp['anchor_gift_income']   = $resultItem['gift_dot_total'];
                                $tmp['video_income']         = $resultItem['video_dot_total'];
                                $tmp['word_msg_income']      = $resultItem['word_chat_dot_total'];
                                $tmp['invite_reward_income'] = $resultItem['invite_reward_dot_total'];
                                $tmp['anchor_guard_income']  = $resultItem['guard_dot_total'];
                                $tmp['chat_game_income']     = $resultItem['chat_game_total'];
                                $tmp['group_total_income']   = $tmp['anchor_time_income'] + $tmp['anchor_gift_income'] + $tmp['invite_reward_income'] + $tmp['group_divid_income'] + $tmp['video_income'] + $tmp['word_msg_income'] + $tmp['anchor_guard_income'] + $tmp['chat_game_income'];
                                $key                         = $resultItem['user_id'];
                                $allSaveArr[$key]            = $tmp;
                            }
                        }
                        $saveAll     = [];
                        $update_time = time();
                        foreach ( $allSaveArr as $allItem ) {
                            ksort($allItem);
                            $allItem[] = $update_time;
                            $saveAll[] = '(' . implode(',', $allItem) . ')';
                        }
                        $valueStr = implode(',', $saveAll);
                        $keyArr   = array_keys($saveDefault);
                        sort($keyArr);
                        $keyArr[]   = 'update_time';
                        $keyStr     = implode(',', $keyArr);
                        $sInsertSql = sprintf('INSERT INTO group_income_stat(%s) VALUES %s;', $keyStr, $valueStr);

                        // 先删除掉之前的
                        GroupIncomeStatModel::destroy($todayWhere);

                        GroupIncomeStatModel::execute($sInsertSql);
                    }
                }
            }

            $this->model = new GroupIncomeStatModel();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $where_str = 'group_income_stat.user_id != 0 and group_income_stat.group_id =' . Session::get('admin.group_id');
            $error_flg = $this->request->get("not_zero", '');
            if ( $error_flg == 'not_zero' ) {
                $where_str .= ' AND group_total_income > 0';
            }
            $total  = GroupIncomeStatModel::where($where_str)
                ->with('User,UserAccount')->where($where)->order($sort, $order)->count();
            $list   = GroupIncomeStatModel::where($where_str)
                ->with('User,UserAccount')->where($where)
                ->field('(select online_duration from anchor_stat where user_id = `group_income_stat`.user_id AND stat_time = `group_income_stat`.stat_time ) as online_duration')
//                ->join('anchor_stat','anchor_stat.user_id = group_income_stat.user_id AND anchor_stat.stat_time = group_income_stat.stat_time')
                ->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

}
