<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/3
 * 统计数据
 */

namespace app\tasks;

class StaticTask extends MainTask
{

    /**
     * 公会当天统计
     */
    public function todayGroupIncomeStatAction()
    {
        $stat_start = strtotime(date('Y-m-d'));
        $stat_end   = time();
        $this->_groupIncomeStat($stat_start, $stat_end);
    }

    /**
     * 公会收益统计 每天
     */
    public function groupIncomeStatAction($params = '')
    {

        // 默认为昨天
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $end        = date('Y-m-d 00:00:00');
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;


        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }
//        var_dump($stat_start,$stat_end,$start,$end);
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $this->_groupIncomeStat($item_stat_start, $item_stat_end);
        }
        die;

    }

    private function _groupIncomeStat($start_stat, $end_stat)
    {
        //获取所有收益分成的工会
        $group = $this->db->query('SELECT * FROM `group` where create_time < ' . $end_stat);
        $group->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $groupDividTypaArr = [];
        $allSaveArr        = [];
        $saveDefault       = [
            'stat_time'            => $start_stat,
            'group_id'             => 0,
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
            'divid_type'           => 0,
            'time_total'           => 0,
            'video_income'         => 0,
            'chat_game_income'     => 0,
        ];
        foreach ( $group->fetchAll() as $key => $groupItem ) {
            $groupDividTypaArr[$groupItem['id']] = [
                'divid_type'    => $groupItem['divid_type'],
                'divid_precent' => $groupItem['divid_precent'],
            ];
            $saveDefault['group_id']             = $groupItem['id'];
            $allSaveArr[$groupItem['id']]        = $saveDefault;
        }
        if ( !$allSaveArr ) {
            return;
        }

        //主播时长收益
        $sql = "select group_id,
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
 from user_finance_log where group_id > 0 AND user_amount_type = 'dot' AND consume_category_id in (17,6,21,22,23,28,31) and create_time >= $start_stat and  create_time < $end_stat group by group_id";
        $res = $this->db->query($sql);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        //邀请奖励公会长没有收益

        foreach ( $res->fetchAll() as $key => $resultItem ) {
            $itemKey                                = $resultItem['group_id'];
            $resultItem['time_dot_total']           = abs($resultItem['time_dot_total']);
            $resultItem['time_coin_total']          = abs($resultItem['time_coin_total']);
            $resultItem['gift_dot_total']           = abs($resultItem['gift_dot_total']);
            $resultItem['gift_coin_total']          = abs($resultItem['gift_coin_total']);
            $resultItem['invite_reward_dot_total']  = abs($resultItem['invite_reward_dot_total']);
            $resultItem['invite_reward_coin_total'] = abs($resultItem['invite_reward_coin_total']);
            $resultItem['word_chat_dot_total']      = abs($resultItem['word_chat_dot_total']);
            $resultItem['word_chat_coin_total']     = abs($resultItem['word_chat_coin_total']);
            $resultItem['video_dot_total']          = abs($resultItem['video_dot_total']);
            $resultItem['video_coin_total']         = abs($resultItem['video_coin_total']);
            $resultItem['time_total']               = abs($resultItem['time_total']);
            $groupData                              = $groupDividTypaArr[$resultItem['group_id']];
            $divid_type                             = $groupData['divid_type'];
            $divid_precent                          = $groupData['divid_precent'];

            // 守护收益统计
            $resultItem['guard_dot_total']      = abs($resultItem['guard_dot_total']);
            $resultItem['chat_game_total']      = abs($resultItem['chat_game_total']);
            $resultItem['chat_game_coin_total'] = abs($resultItem['chat_game_coin_total']);

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

                $allSaveArr[$itemKey]['anchor_guard_income'] = $resultItem['guard_dot_total'];
                $allSaveArr[$itemKey]['chat_game_income']    = $resultItem['chat_game_total'];

                $allSaveArr[$itemKey]['group_total_income'] = $allSaveArr[$itemKey]['anchor_time_income'] + $allSaveArr[$itemKey]['anchor_gift_income'] + $allSaveArr[$itemKey]['invite_reward_income'] + $allSaveArr[$itemKey]['group_divid_income'] + $allSaveArr[$itemKey]['word_msg_income'] + $allSaveArr[$itemKey]['video_income'] + $allSaveArr[$itemKey]['anchor_guard_income'] + $allSaveArr[$itemKey]['chat_game_income'];
            } else {
                $tmp             = $saveDefault;
                $tmp['group_id'] = $resultItem['group_id'];
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

                $tmp['anchor_guard_income'] = $resultItem['guard_dot_total'];
                $tmp['chat_game_income']    = $resultItem['chat_game_total'];

                $tmp['group_total_income'] = $tmp['anchor_time_income'] + $tmp['anchor_gift_income'] + $tmp['invite_reward_income'] + $tmp['group_divid_income'] + $tmp['video_income'] + $tmp['word_msg_income'] + $tmp['anchor_guard_income'] + $tmp['chat_game_income'];
                $key                       = $resultItem['group_id'];
                $allSaveArr[$key]          = $tmp;
            }
        }

        // 保存记录
        $saveAll = [];
        foreach ( $allSaveArr as $allItem ) {
            ksort($allItem);
            $saveAll[] = '(' . implode(',', $allItem) . ')';
        }
        $valueStr = implode(',', $saveAll);
        $keyArr   = array_keys($saveDefault);
        sort($keyArr);
        $keyStr = implode(',', $keyArr);

        $sDeleteSql = 'delete from group_income_stat where user_id = 0 AND stat_time = ' . $start_stat;
        $this->db->execute($sDeleteSql);

        $sInsertSql = sprintf('INSERT INTO group_income_stat(%s) VALUES %s;', $keyStr, $valueStr);
        $this->db->execute($sInsertSql);
    }

    /**
     * @param $start_stat
     * @param $end_stat
     * 功能主播收益统计
     */
    private function _anchorIncomeStat($start_stat, $end_stat)
    {
        //获取所有统计时间之前已成为主播的主播
        $anchor = $this->db->query('SELECT `user`.user_id,`user`.user_group_id FROM `anchor` inner join `user` on `anchor`.user_id = `user`.user_id where `anchor`.anchor_create_time <' . $end_stat);
        $anchor->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        /* 所有保存的数据  以【主播id:公会id】 为key */
        $allSaveArr = [];
        // 默认数据
        $saveDefault = [
            'stat_time'            => $start_stat,
            'group_id'             => 0,
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
            'divid_type'           => 0,
            'time_total'           => 0,
            'video_income'         => 0,
            'chat_game_income'     => 0,
        ];
        foreach ( $anchor->fetchAll() as $anchorKey => $anchorItem ) {
            $allSaveKey                          = sprintf('%s:%s', $anchorItem['user_id'], $anchorItem['user_group_id']);
            $allSaveArr[$allSaveKey]             = $saveDefault;
            $allSaveArr[$allSaveKey]['group_id'] = $anchorItem['user_group_id'];
            $allSaveArr[$allSaveKey]['user_id']  = $anchorItem['user_id'];
        }

        if ( empty($allSaveArr) ) {
            return;
        }

        //获取所有收益分成的公会
        $group = $this->db->query('SELECT * FROM `group` where create_time < ' . $end_stat);
        $group->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        $groupDividTypaArr = [];
        foreach ( $group->fetchAll() as $key => $groupItem ) {
            $groupDividTypaArr[$groupItem['id']] = [
                'divid_type'    => $groupItem['divid_type'],
                'divid_precent' => $groupItem['divid_precent'],
            ];
        }
        //主播时长收益
        $sql = "select user_id,group_id,
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
 from user_finance_log where group_id > 0 AND user_amount_type = 'dot' AND consume_category_id in (17,6,21,22,23,28,31) and create_time >= $start_stat and  create_time < $end_stat group by user_id,group_id";
        $res = $this->db->query($sql);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $key => $resultItem ) {
            $itemKey                                = sprintf('%s:%s', $resultItem['user_id'], $resultItem['group_id']);
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
            $resultItem['chat_game_coin_total']     = abs($resultItem['chat_game_coin_total']);
            $resultItem['chat_game_total']          = abs($resultItem['chat_game_total']);
            $groupData                              = $groupDividTypaArr[$resultItem['group_id']];
            $divid_type                             = $groupData['divid_type'];
            $divid_precent                          = $groupData['divid_precent'];

            // 守护收益统计
            $resultItem['guard_dot_total'] = abs($resultItem['guard_dot_total']);

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

                $allSaveArr[$itemKey]['anchor_guard_income'] = $resultItem['guard_dot_total'];
                $allSaveArr[$itemKey]['chat_game_income']    = $resultItem['chat_game_total'];

                $allSaveArr[$itemKey]['group_total_income'] = $allSaveArr[$itemKey]['anchor_time_income'] + $allSaveArr[$itemKey]['anchor_gift_income'] + $allSaveArr[$itemKey]['invite_reward_income'] + $allSaveArr[$itemKey]['group_divid_income'] + $allSaveArr[$itemKey]['word_msg_income'] + $allSaveArr[$itemKey]['video_income'] + $allSaveArr[$itemKey]['anchor_guard_income'] + $allSaveArr[$itemKey]['chat_game_income'];


            } else {
                $tmp             = $saveDefault;
                $tmp['group_id'] = $resultItem['group_id'];
                $tmp['user_id']  = $resultItem['user_id'];
                if ( $divid_type == 0 ) {
                    //主播收益分成
                    $tmp['group_divid_income'] = round(($resultItem['time_dot_total'] + $resultItem['gift_dot_total'] + $resultItem['word_chat_dot_total'] + $resultItem['video_dot_total'] + $resultItem['chat_game_total']) * $divid_precent / 100, 2);
                } else {
                    //主播流水分成  还需要除以一个 充值比例转换值 10
                    $tmp['group_divid_income'] = round(($resultItem['time_coin_total'] + $resultItem['gift_coin_total'] + $resultItem['word_chat_coin_total'] + $resultItem['video_dot_total'] + $resultItem['chat_game_coin_total']) * $divid_precent / 100 / 10, 2);
                }
                $tmp['divid_type']           = $divid_type;
                $tmp['time_total']           = $resultItem['time_total'];
                $tmp['anchor_time_income']   = $resultItem['time_dot_total'];
                $tmp['anchor_gift_income']   = $resultItem['gift_dot_total'];
                $tmp['video_income']         = $resultItem['video_dot_total'];
                $tmp['word_msg_income']      = $resultItem['word_chat_dot_total'];
                $tmp['invite_reward_income'] = $resultItem['invite_reward_dot_total'];

                $tmp['anchor_guard_income'] = $resultItem['guard_dot_total'];
                $tmp['chat_game_income']    = $resultItem['chat_game_total'];

                $tmp['group_total_income'] = $tmp['anchor_time_income'] + $tmp['anchor_gift_income'] + $tmp['invite_reward_income'] + $tmp['group_divid_income'] + $tmp['video_income'] + $tmp['word_msg_income'] + $tmp['anchor_guard_income'] + $tmp['chat_game_income'];
                $key                       = $resultItem['group_id'];
                $allSaveArr[$key]          = $tmp;
            }
        }
        $saveAll = [];
        foreach ( $allSaveArr as $allItem ) {
            ksort($allItem);
            $saveAll[] = '(' . implode(',', $allItem) . ')';
        }
        $valueStr = implode(',', $saveAll);
        $keyArr   = array_keys($saveDefault);
        sort($keyArr);
        $keyStr = implode(',', $keyArr);

        $sDeleteSql = 'delete from group_income_stat where user_id != 0 AND stat_time = ' . $start_stat;
        $this->db->execute($sDeleteSql);

        $sInsertSql = sprintf('INSERT INTO group_income_stat(%s) VALUES %s;', $keyStr, $valueStr);
        $this->db->execute($sInsertSql);
    }


    /**
     * 主播当天统计
     */
    public function todayAnchorIncomeStatAction()
    {
        $stat_start = strtotime(date('Y-m-d'));
        $stat_end   = time();
        $this->_anchorIncomeStat($stat_start, $stat_end);
    }

    /**
     * 主播统计
     */
    public function anchorIncomeStatAction($params = '')
    {
        // 默认为昨天
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $end        = date('Y-m-d 00:00:00');
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;


        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }
//        var_dump($stat_start,$stat_end,$start,$end);
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $this->_anchorIncomeStat($item_stat_start, $item_stat_end);
        }
        die;


    }

    /**
     * 每日统计数据
     *      注册用户数
     *      活跃用户数
     *      视频通话成功数
     *      充值订单数
     *      充值订单金额数
     *      充值成功订单数
     *      充值成功订单金额数
     *      当天注册用户充值数
     *      消费金币数
     */
    public function dailyDataStatAction($params = '')
    {
        // 默认为昨天
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $end        = date('Y-m-d 00:00:00');
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;


        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }
//        var_dump($stat_start,$stat_end,$start,$end);
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $this->_dailyDataStat($item_stat_start, $item_stat_end);
        }
        die;


    }

    /**
     * 统计当天数据
     */
    public function dailyDataStatTodayAction()
    {
        $stat_start = strtotime(date('Y-m-d'));
        $stat_end   = time();
        $this->_dailyDataStat($stat_start, $stat_end);
    }

    private function _dailyDataStat($stat_start, $stat_end)
    {

        // 注册男用户并设备去重
        $sql                     = <<<SQL
select count( DISTINCT user_device_id) as total_count,'register_ios_male_count' as save_column from user_account 
where user_create_time >= :stat_start and user_create_time < :stat_end 
and exists(select 1 from user where user_sex = 1 and user_id = user_account.user_id) and length(user_device_id) >= 18
UNION all
select count( DISTINCT user_device_id) as total_count,'register_and_male_count' as save_column from user_account 
where user_create_time >= :stat_start and user_create_time < :stat_end 
and exists(select 1 from user where user_sex = 1 and user_id = user_account.user_id) and length(user_device_id) < 18
SQL;
        $res                     = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $register_ios_male_count = 0;
        $register_and_male_count = 0;
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        foreach ( $res->fetchAll() as $key => $resultItem ) {
            $column  = $resultItem['save_column'];
            $$column = intval($resultItem['total_count']);
        }

        // 注册的设备
        $sql = <<<SQL
SELECT COUNT(1) AS register_device_count,sum(case when length(device_no) > 18 then 1 else 0 end) as register_ios_device_count
from user_device_bind where create_time >= :stat_start AND create_time < :stat_end
SQL;
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oRegisterDeviceCount      = $res->fetch();
        $register_device_count     = $oRegisterDeviceCount['register_device_count'] ?? 0;
        $register_ios_device_count = $oRegisterDeviceCount['register_ios_device_count'] ?? 0;
        $register_and_device_count = $register_device_count - $register_ios_device_count;


        //注册并成为主播的数量
        $sql = <<<SQL
SELECT count(1) as register_anchor_count from anchor as a inner join `user` as u on u.user_id = a.user_id 
where u.user_create_time >= :stat_start_day and u.user_create_time < :stat_end AND 
a.anchor_create_time >= :stat_start AND a.anchor_create_time < :stat_end
SQL;

        $res = $this->db->query($sql, [
            'stat_start_day' => strtotime(date('Y-m-d', $stat_start)),
            'stat_start'     => $stat_start,
            'stat_end'       => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oRegisterAnchorCount  = $res->fetch();
        $register_anchor_count = $oRegisterAnchorCount['register_anchor_count'] ?? 0;


        // 注册用户数
        $sql = 'select count(1) as register_user_count from `user` where  user_register_time >= :stat_start AND user_register_time < :stat_end';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oRegisterUsserCount = $res->fetch();
        $sRegisterUserCount  = $oRegisterUsserCount['register_user_count'] ?? 0;

        // 活跃用户数 设备去重
        $sql = 'select count(distinct user_account.user_device_id) as active_user_count from `user` inner join user_account on `user`.user_id = user_account.user_id where ((`user`.user_login_time >= :stat_start AND `user`.user_login_time < :stat_end) or (`user`.user_logout_time >= :stat_start AND `user`.user_logout_time < :stat_end))';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oActiveUserCount = $res->fetch();
        $sActiveUserCount = $oActiveUserCount['active_user_count'] ?? 0;

        // 视频通话成功数  已挂断 或 正在通话中   使用免费时长的用户数
        $sql = 'select count(1) as video_chat_success_count,
count(distinct case when free_times > 0 then chat_log_user_id end ) as free_times_user_count
 from `user_private_chat_log` where create_time >= :stat_start AND create_time < :stat_end AND (status = 6 or status = 4)';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oVideoChatSuccessCount = $res->fetch();
        $sVideoChatSuccessCount = $oVideoChatSuccessCount['video_chat_success_count'] ?? 0;
        $freeTimesUserCount     = $oVideoChatSuccessCount['free_times_user_count'] ?? 0;

        //充值订单数  充值订单金额 充值成功订单数  充值成功订单金额  充值成功人数   (当天注册 并且充值成功人数)
        // 充值支付宝  微信    当天注册并充值金额
        $sql = 'select count(1) as recharge_order_count,sum(o.user_recharge_order_fee) as recharge_money_count,
sum(case o.user_recharge_order_status when "Y" then 1 else 0 end) as  recharge_order_success_count,
sum(case o.user_recharge_order_status when "Y" then o.user_recharge_order_fee else 0 end) as  recharge_money_success_count,
sum(case when o.user_recharge_order_status = "Y" AND o.user_type = "Android" then o.user_recharge_order_fee else 0 end) as  recharge_money_success_count_and,
sum(case when o.user_recharge_order_status = "Y" AND o.user_type = "iOS" then o.user_recharge_order_fee else 0 end) as  recharge_money_success_count_ios,
count(distinct (select user_id from `user` where user_id = o.user_id AND o.user_recharge_order_status = "Y")) as recharge_success_user_count,
count(DISTINCT (select user_id from `user` where user_id = o.user_id AND o.user_recharge_order_status = "Y" AND user_create_time >= :stat_start AND user_create_time < :stat_end)) as  register_user_recharge_success_count,
sum(case when o.user_recharge_order_status = "Y" AND o.user_recharge_order_type in ("wx","wxh5","quanmin","318211","skycat") then o.user_recharge_order_fee else 0 end) as  recharge_wechat_money,
sum(case when o.user_recharge_order_status = "Y" AND (o.user_recharge_order_type = "zfb" or o.user_recharge_order_type = "alipayh5") then o.user_recharge_order_fee else 0 end) as  recharge_alipay_money,
sum(case when o.user_recharge_order_status = "Y" AND exists(select 1 from user where user_id = o.user_id AND user_create_time >= :stat_start AND user_create_time < :stat_end) then o.user_recharge_order_fee else 0 end) as  register_user_recharge_success_money
 from `user_recharge_order` AS o where o.user_recharge_order_update_time >= :stat_start AND o.user_recharge_order_update_time < :stat_end';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oRechargeCount                    = $res->fetch();
        $sRechargeOrderCount               = $oRechargeCount['recharge_order_count'] ?? 0;
        $sRechargeMoneyCount               = $oRechargeCount['recharge_money_count'] ?? 0;
        $sRechargeOrderSuccessCount        = $oRechargeCount['recharge_order_success_count'] ?? 0;
        $sRechargeMoneySuccessCount        = $oRechargeCount['recharge_money_success_count'] ?? 0;
        $sRechargeSuccessUserCount         = $oRechargeCount['recharge_success_user_count'] ?? 0;
        $sRegisterUserRechargeSuccessCount = $oRechargeCount['register_user_recharge_success_count'] ?? 0;

        $sRechargeMoneySuccessCountAnd = $oRechargeCount['recharge_money_success_count_and'] ?? 0;
        $sRechargeMoneySuccessCountIOS = $oRechargeCount['recharge_money_success_count_ios'] ?? 0;
        $sRechargeWechatMoney          = $oRechargeCount['recharge_wechat_money'] ?? 0;
        $sRechargeAlipayMoney          = $oRechargeCount['recharge_alipay_money'] ?? 0;

        $sRegisterUserRechargeSuccessMoney = $oRechargeCount['register_user_recharge_success_money'] ?? 0;

        // 消费金币数
        $sql = 'select sum(-consume) as consume_coin_count from `user_finance_log` where user_amount_type = "coin" AND consume < 0 AND create_time >= :stat_start AND create_time < :stat_end';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oFinanceCount     = $res->fetch();
        $sConsumeCoinCount = $oFinanceCount['consume_coin_count'] ?? 0;


        // VIP成功人数 成功金额
        $sql = "select count( distinct user_id) as vip_success_user_count,sum(user_vip_order_combo_fee) as vip_success_money_count,
sum(case when user_vip_order_type in ('wx','wxh5','quanmin','318211') then user_vip_order_combo_fee else 0 end) as  vip_wechat_money,
sum(case when user_vip_order_type = 'zfb' or user_vip_order_type = 'alipayh5' then user_vip_order_combo_fee else 0 end) as  vip_alipay_money
from user_vip_order where user_vip_order_status = 'Y' AND  user_vip_order_update_time >= :stat_start AND user_vip_order_update_time < :stat_end";
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oVipCount            = $res->fetch();
        $vipSuccessUserCount  = $oVipCount['vip_success_user_count'] ?? 0;
        $vipSuccessMoneyCount = $oVipCount['vip_success_money_count'] ?? 0;
        $vipWechatMoney       = $oVipCount['vip_wechat_money'] ?? 0;
        $vipAlipayMoney       = $oVipCount['vip_alipay_money'] ?? 0;

        //统计当天注册人 有过充值成功记录数
        $sql                                      = "select count(distinct o.user_id) as register_user_recharge_success_count_all  from user_recharge_order as o inner join `user` as u on u.user_id = o.user_id where o.user_recharge_order_status = 'Y' AND u.user_register_time >= :stat_start AND u.user_register_time < :stat_end";
        $res                                      = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $oRechargeTwoCount                        = $res->fetch();
        $register_user_recharge_success_count_all = $oRechargeTwoCount['register_user_recharge_success_count_all'] ?? 0;


        // 普通用户邀请用户人数（设备去重）主播邀请用户人数（设备去重）
        $sql = <<<SQL
select invite_user.user_is_anchor,count(1) as total_count, sum(case when length(device_no) > 18 then 1 else 0 end) as ios_count
from user_device_bind as udb inner join `user` as invite_user on invite_user.user_id = udb.bind_id 
where udb.create_time >= :stat_start and udb.create_time < :stat_end and udb.bind_type = 'user' group by invite_user.user_is_anchor;
SQL;
//        SELECT invite_user.user_is_anchor,count( distinct ua.user_device_id ) as total_count from `user` as u inner join user_account as ua on u.user_id = ua.user_id
//inner join `user` as invite_user on invite_user.user_id = u.user_invite_user_id where u.user_create_time >= :stat_start and u.user_create_time < :stat_end group by invite_user.user_is_anchor;
        $res                            = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $user_invite_device_count       = 0;
        $user_invite_ios_device_count   = 0;
        $user_invite_and_device_count   = 0;
        $anchor_invite_device_count     = 0;
        $anchor_invite_ios_device_count = 0;
        $anchor_invite_and_device_count = 0;
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        foreach ( $res->fetchAll() as $key => $resultItem ) {
            if ( $resultItem['user_is_anchor'] == 'Y' ) {
                $anchor_invite_device_count     = $resultItem['total_count'];
                $anchor_invite_ios_device_count = $resultItem['ios_count'];
                $anchor_invite_and_device_count = $anchor_invite_device_count - $anchor_invite_ios_device_count;
            } else {
                $user_invite_device_count     = $resultItem['total_count'];
                $user_invite_ios_device_count = $resultItem['ios_count'];
                $user_invite_and_device_count = $user_invite_device_count - $user_invite_ios_device_count;
            }
        }

        /*
         * 普通用户邀请用户充值成功人数（设备去重）
         * 主播邀请用户充值成功人数（设备去重）
         * 用户邀请用户充值成功金额
         * 主播邀请用户充值成功金额
         */
        $sql                                               = <<<SQL
SELECT 
invite_user.user_is_anchor,
count( distinct user_device_id ) as  recharge_user_success_count,
sum(o.user_recharge_order_fee) as  recharge_money_success_count
from user_recharge_order as o inner join `user` as u on u.user_id = o.user_id
inner join user_account as ua on ua.user_id = u.user_id
inner join `user` as invite_user on invite_user.user_id = u.user_invite_user_id
where o.user_recharge_order_status = 'Y' AND o.user_recharge_order_update_time >= :stat_start AND o.user_recharge_order_update_time < :stat_end
AND u.user_create_time >= :stat_start and u.user_create_time < :stat_end
group by invite_user.user_is_anchor
SQL;
        $res                                               = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $user_invite_device_recharge_success_count         = 0;
        $anchor_invite_device_recharge_success_count       = 0;
        $user_invite_device_recharge_money_success_count   = 0;
        $anchor_invite_device_recharge_money_success_count = 0;
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        foreach ( $res->fetchAll() as $key => $resultItem ) {
            if ( $resultItem['user_is_anchor'] == 'Y' ) {
                $anchor_invite_device_recharge_success_count       = $resultItem['recharge_user_success_count'];
                $anchor_invite_device_recharge_money_success_count = $resultItem['recharge_money_success_count'];
            } else {
                $user_invite_device_recharge_success_count       = $resultItem['recharge_user_success_count'];
                $user_invite_device_recharge_money_success_count = $resultItem['recharge_money_success_count'];
            }
        }


        // 是统计天注册的用户  充值VIP的数量
        $sql                        = <<<SQL
SELECT count( distinct u.user_id ) as vip_success_user_count_all from user_vip_order as o inner join `user` as u on u.user_id = o.user_id WHERE 
o.user_vip_order_status = 'Y' AND u.user_register_time >= :stat_start AND u.user_register_time < :stat_end
SQL;
        $res                        = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $oRechargeVipTwoCount       = $res->fetch();
        $vip_success_user_count_all = $oRechargeVipTwoCount['vip_success_user_count_all'] ?? 0;


        // 支付宝提现  微信提现
        $sql                 = <<<SQL
SELECT sum(user_withdraw_cash) as withdraw_money,
sum(case when user_withdraw_pay = '支付宝' then user_withdraw_cash else 0 end) as  withdraw_alipay_money,
0 as  withdraw_wechat_money
 from user_withdraw_log WHERE 
user_withdraw_log_status = 'Y' AND user_withdraw_way = 1 AND user_withdraw_log_update_time >= :stat_start AND user_withdraw_log_update_time < :stat_end
SQL;
        $res                 = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $oWithdrawData       = $res->fetch();
        $withdrawMoney       = $oWithdrawData['withdraw_money'] ?? 0;
        $withdrawAlipayMoney = $oWithdrawData['withdraw_alipay_money'] ?? 0;
        $withdrawWechatMoney = $oWithdrawData['withdraw_wechat_money'] ?? 0;

        // 活跃主播数
        $sql = 'select count(1) as active_anchor_count from `user` where user_is_anchor = "Y"  and ((user_login_time >= :stat_start AND user_login_time < :stat_end) or (user_logout_time >= :stat_start AND user_logout_time < :stat_end))';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oActiveAnchorCount = $res->fetch();
        $sActiveAnchorCount = $oActiveAnchorCount['active_anchor_count'] ?? 0;


        // 取当天匹配时有免费时长的用户 去重
        $sql = 'select count(distinct user_id) as free_times_try_user_count from user_match_log where update_time >= :stat_start AND update_time < :stat_end AND has_free_times > 0';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oFreeTimesTryUserCount = $res->fetch();
        $sFreeTimesTryUserCount = $oFreeTimesTryUserCount['free_times_try_user_count'] ?? 0;

        // 取次日留存，三日留存，7日留存，30日留存
        $two_retain_device_count    = intval($this->redis->sCard(sprintf('device_retain:2:%s', date('Ymd', $stat_start))));
        $three_retain_device_count  = intval($this->redis->sCard(sprintf('device_retain:3:%s', date('Ymd', $stat_start))));
        $seven_retain_device_count  = intval($this->redis->sCard(sprintf('device_retain:7:%s', date('Ymd', $stat_start))));
        $thirty_retain_device_count = intval($this->redis->sCard(sprintf('device_retain:30:%s', date('Ymd', $stat_start))));


        // 取当日激活设备数
        $sql = 'select count(1) as active_device_count from device_active_log where device_active_create_time >= :stat_start AND device_active_create_time < :stat_end';
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oActiveDeviceCount = $res->fetch();
        $sActiveDeviceCount = $oActiveDeviceCount['active_device_count'] ?? 0;


        // 匹配记录 以及匹配成功记录
        $sql                      = <<<SQL
SELECT sum( case when match_success = 'Y' then 1 else 0 end) as match_chat_success_total,count(1) as match_chat_total
 from user_match_log WHERE 
update_time >= :stat_start AND update_time < :stat_end
SQL;
        $res                      = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $oWithdrawData            = $res->fetch();
        $match_chat_total         = $oWithdrawData['match_chat_total'] ?? 0;
        $match_chat_success_total = $oWithdrawData['match_chat_success_total'] ?? 0;


        // 有效点播次数  总点播次数
        $sql                       = <<<SQL
SELECT sum( case when (status in(1,5) and duration > 5) or status = 2 then 1 else 0 end) as normal_chat_fail_total,
sum( case when status in(4,6) then 1 else 0 end) as normal_chat_success_total
 from user_private_chat_log WHERE chat_type = 'normal' AND
create_time >= :stat_start AND create_time < :stat_end
SQL;
        $res                       = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $oWithdrawData             = $res->fetch();
        $normal_chat_fail_total    = $oWithdrawData['normal_chat_fail_total'] ?? 0;
        $normal_chat_success_total = $oWithdrawData['normal_chat_success_total'] ?? 0;
        $normal_chat_total         = $normal_chat_fail_total + $normal_chat_success_total;


        //代理商总收益
        $sql                = <<<SQL
SELECT SUM(income) as agent_total_income from agent_daily_stat where stat_time = :stat_time
SQL;
        $res                = $this->db->query($sql, [
            'stat_time' => strtotime(date('Y-m-d', $stat_start)),
        ]);
        $oAgentTotalData    = $res->fetch();
        $agent_total_income = $oAgentTotalData['agent_total_income'] ?? 0;

        // 公会总收益
        $sql                = <<<SQL
SELECT SUM(group_total_income) as group_total_income from group_income_stat where user_id = 0 AND stat_time = :stat_time
SQL;
        $res                = $this->db->query($sql, [
            'stat_time' => strtotime(date('Y-m-d', $stat_start)),
        ]);
        $oGroupTotalData    = $res->fetch();
        $group_total_income = $oGroupTotalData['group_total_income'] ?? 0;

        // 用户获取现金总收益
        $sql                    = <<<SQL
SELECT sum(consume) as user_total_cash_income
 from user_cash_log WHERE consume > 0 AND consume_category != 'withdraw_back' and 
update_time >= :stat_start AND update_time < :stat_end
SQL;
        $res                    = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $oCashTotalData         = $res->fetch();
        $user_total_cash_income = $oCashTotalData['user_total_cash_income'] ?? 0;

        $sDeleteSql = "delete from daily_data_stat where stat_time = $stat_start";
        $this->db->execute($sDeleteSql);


        $stat_month = date('Y-m', $stat_start);

        $create_time = time();
        $sInsertSql  = <<<SQL
INSERT INTO `daily_data_stat`(`stat_time`,`stat_month` ,`register_user_count`, `active_user_count`,`register_user_recharge_success_count_all`,
 `video_chat_success_count`, `recharge_order_count`, `recharge_money_count`, `recharge_order_success_count`, 
 `recharge_money_success_count`, `register_user_recharge_success_count`, `consume_coin_count`, `create_time`, `update_time` ,
 `register_ios_male_count`,`register_and_male_count`,`recharge_success_user_count`,`vip_success_user_count`,`vip_success_money_count`,
 `user_invite_device_count`,`anchor_invite_device_count`,`user_invite_device_recharge_success_count`,`anchor_invite_device_recharge_success_count`,
 `user_invite_device_recharge_money_success_count`,`anchor_invite_device_recharge_money_success_count`,`vip_success_user_count_all`,
 `user_invite_ios_device_count`,`user_invite_and_device_count`,`anchor_invite_ios_device_count`,`anchor_invite_and_device_count`,
 `register_device_count`,`register_ios_device_count`,`register_and_device_count`,`register_anchor_count`,
 `recharge_money_success_count_and`,`recharge_money_success_count_ios`,
 `recharge_wechat_money`,`recharge_alipay_money`,`vip_wechat_money`,`vip_alipay_money`,`withdraw_wechat_money`,`withdraw_alipay_money`,`withdraw_money`,
 `register_user_recharge_success_money`,`active_anchor_count`,`free_times_user_count`,
 `free_times_try_user_count`,
 `two_retain_device_count`,`three_retain_device_count`,`seven_retain_device_count`,`thirty_retain_device_count`,
 `active_device_count`,`match_chat_total`,`match_chat_success_total`,`normal_chat_total`,`normal_chat_success_total`,
 `agent_total_income`,`group_total_income`,`user_total_cash_income`
 ) 
 VALUES ( 
$stat_start,'$stat_month',$sRegisterUserCount,$sActiveUserCount,$register_user_recharge_success_count_all,
$sVideoChatSuccessCount,$sRechargeOrderCount,$sRechargeMoneyCount,$sRechargeOrderSuccessCount,
$sRechargeMoneySuccessCount,$sRegisterUserRechargeSuccessCount,$sConsumeCoinCount,$create_time, $create_time,
$register_ios_male_count,$register_and_male_count,$sRechargeSuccessUserCount,$vipSuccessUserCount,$vipSuccessMoneyCount,
$user_invite_device_count,$anchor_invite_device_count,$user_invite_device_recharge_success_count,$anchor_invite_device_recharge_success_count,
$user_invite_device_recharge_money_success_count,$anchor_invite_device_recharge_money_success_count,$vip_success_user_count_all,
$user_invite_ios_device_count,$user_invite_and_device_count,$anchor_invite_ios_device_count,$anchor_invite_and_device_count,
$register_device_count,$register_ios_device_count,$register_and_device_count,$register_anchor_count,
$sRechargeMoneySuccessCountAnd,$sRechargeMoneySuccessCountIOS,
$sRechargeWechatMoney,$sRechargeAlipayMoney,$vipWechatMoney,$vipAlipayMoney,$withdrawWechatMoney,$withdrawAlipayMoney,$withdrawMoney,
$sRegisterUserRechargeSuccessMoney,$sActiveAnchorCount,$freeTimesUserCount,
$sFreeTimesTryUserCount,
$two_retain_device_count,$three_retain_device_count,$seven_retain_device_count,$thirty_retain_device_count,
$sActiveDeviceCount,$match_chat_total,$match_chat_success_total,$normal_chat_total,$normal_chat_success_total,
$agent_total_income,$group_total_income,$user_total_cash_income
);
SQL;
        $this->db->execute($sInsertSql);
    }

    /**
     * @param array $params
     * 临时统计新增字段
     */
    public function newFieldStatAction($params = [])
    {
        // 默认为昨天
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $end        = date('Y-m-d 00:00:00');
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;


        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            $stat_time     = strtotime(date('Y-m-d', $item_stat_start));
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            //代理商总收益
            $sql                = <<<SQL
SELECT SUM(income) as agent_total_income from agent_daily_stat where stat_time = :stat_time
SQL;
            $res                = $this->db->query($sql, [
                'stat_time' => strtotime(date('Y-m-d', $item_stat_start)),
            ]);
            $oAgentTotalData    = $res->fetch();
            $agent_total_income = $oAgentTotalData['agent_total_income'] ?? 0;

            // 公会总收益
            $sql                = <<<SQL
SELECT SUM(group_total_income) as group_total_income from group_income_stat where user_id = 0 AND stat_time = :stat_time
SQL;
            $res                = $this->db->query($sql, [
                'stat_time' => strtotime(date('Y-m-d', $item_stat_start)),
            ]);
            $oGroupTotalData    = $res->fetch();
            $group_total_income = $oGroupTotalData['group_total_income'] ?? 0;

            // 用户获取现金总收益
            $sql                    = <<<SQL
SELECT sum(consume) as user_total_cash_income
 from user_cash_log WHERE consume > 0 AND consume_category != 'withdraw_back' and 
update_time >= :stat_start AND update_time < :stat_end
SQL;
            $res                    = $this->db->query($sql, [
                'stat_start' => $item_stat_start,
                'stat_end'   => $item_stat_end
            ]);
            $oCashTotalData         = $res->fetch();
            $user_total_cash_income = $oCashTotalData['user_total_cash_income'] ?? 0;


            $updateSql = "update daily_data_stat set agent_total_income = $agent_total_income,group_total_income = $group_total_income,user_total_cash_income = $user_total_cash_income where stat_time = $stat_time";
            $this->db->execute($updateSql);
        }
        die;

    }


    /**
     * @param string $params
     * 每天统计
     */
    public function dailyAgentStatAction($params = '')
    {
        // 默认为昨天
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;


        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }
//        var_dump($stat_start,$stat_end,$start,$end);
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $this->_dailyAgentStat($item_stat_start, $item_stat_end);
        }
        die;


    }

    public function todayAgentStatAction()
    {
        $stat_start = $this->redis->get('agentStatFinish');
        $stat_date  = date('Y-m-d 00:00:00');
        $stat_time  = strtotime($stat_date);
        if ( !$stat_time || $stat_start < $stat_time ) {
            $stat_start = $stat_time;
        }
        $stat_end = time();
        print_r('统计时间：' . date('Y-m-d H:i:s', $stat_start) . '-' . date('Y-m-d H:i:s', $stat_end) . "\n");
        $this->_dailyAgentStat($stat_start, $stat_end, TRUE);
    }


    /**
     * 代理每日统计
     * 取出所有代理 合成代理默认数据
     */
    private function _dailyAgentStat($stat_start, $stat_end, $isToday = FALSE)
    {
        if ( $isToday ) {
            $this->redis->set('agentStatFinish', $stat_end);
            if ( $stat_start == $stat_end ) {
                return;
            }
            $stat_date   = date('Y-m-d 00:00:00');
            $stat_time   = strtotime($stat_date);
            $saveDefault = [
                'id'                              => 'null',
                'stat_time'                       => $stat_time,
                'stat_datetime'                   => "'$stat_date'",
                'agent_id'                        => 0,
                'consume'                         => 0,
                'total_consume'                   => 0,
                'recharge_money'                  => 0,
                'total_recharge_money'            => 0,
                'register_count'                  => 0,
                'total_register_count'            => 0,
                'affect_register_count'           => 0,
                'total_affect_register_count'     => 0,
                'affect_ios_register_count'       => 0,
                'total_ios_affect_register_count' => 0,
                'affect_and_register_count'       => 0,
                'total_and_affect_register_count' => 0,
                'recharge_user_count'             => 0,
                'total_recharge_user_count'       => 0,
                'income'                          => 0,
                'total_income'                    => 0,
                'vip_money'                       => 0,
                'total_vip_money'                 => 0,
                'new_user_recharge_money'         => 0,
                'total_new_user_recharge_money'   => 0,

                'total_vip_user_count'            => 0,
                'total_and_recharge_money'        => 0,
                'total_ios_recharge_money'        => 0,
                'total_recharge_order_count'      => 0,
                'total_register_recharge_count'   => 0,
                'total_free_times_try_user_count' => 0,
                'total_free_times_success_count'  => 0,

                'total_recharge_click_user_count'  => 0,
                'total_register_recharge_click_count'  => 0,

                'total_vip_click_user_count'  => 0,

                'total_active_device_count' => 0,
                'update_time'               => time(),
                'create_time'               => time(),
            ];

            $shouldStatAllDayFields = [
                'total_vip_user_count',
                'total_register_recharge_count',
                'recharge_user_count',
                'total_recharge_user_count',
                'total_recharge_click_user_count',
                'total_register_recharge_click_count',
                'total_vip_click_user_count',
            ];

            // 查出所有当天已统计的数据
            $oAgentDailyStat = $this->db->query('SELECT * FROM agent_daily_stat where stat_time =' . $stat_time);
            $oAgentDailyStat->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            foreach ( $oAgentDailyStat->fetchAll() as $AgentDailyStatItem ) {
                $itemAgentId = $AgentDailyStatItem['agent_id'];
                foreach ( $shouldStatAllDayFields as $shouldStatAllDayField ) {
                    $AgentDailyStatItem[$shouldStatAllDayField] = 0;
                }
                $oAgentDailyStatArr[$itemAgentId] = $AgentDailyStatItem;
            }

        } else {
            $stat_date   = date('Y-m-d 00:00:00', $stat_start);
            $stat_time   = $stat_start;
            $saveDefault = [
                'stat_time'                       => $stat_start,
                'stat_datetime'                   => "'$stat_date'",
                'agent_id'                        => 0,
                'consume'                         => 0,
                'total_consume'                   => 0,
                'recharge_money'                  => 0,
                'total_recharge_money'            => 0,
                'register_count'                  => 0,
                'total_register_count'            => 0,
                'affect_register_count'           => 0,
                'total_affect_register_count'     => 0,
                'affect_ios_register_count'       => 0,
                'total_ios_affect_register_count' => 0,
                'affect_and_register_count'       => 0,
                'total_and_affect_register_count' => 0,
                'recharge_user_count'             => 0,
                'total_recharge_user_count'       => 0,
                'vip_money'                       => 0,
                'total_vip_money'                 => 0,
                'income'                          => 0,
                'total_income'                    => 0,
                'new_user_recharge_money'         => 0,
                'total_new_user_recharge_money'   => 0,

                'total_vip_user_count'            => 0,
                'total_and_recharge_money'        => 0,
                'total_ios_recharge_money'        => 0,
                'total_recharge_order_count'      => 0,
                'total_register_recharge_count'   => 0,
                'total_free_times_try_user_count' => 0,
                'total_free_times_success_count'  => 0,


                'total_recharge_click_user_count'  => 0,
                'total_register_recharge_click_count'  => 0,

                'total_vip_click_user_count'  => 0,

                'total_active_device_count' => 0,
                'update_time'               => time(),
                'create_time'               => time(),
            ];
        }


        //获取所有统计时间之前已成为代理的代理
        $agent = $this->db->query('SELECT id,first_leader,second_leader,recharge_distribution_profits,vip_distribution_profits,status FROM agent where create_time <' . $stat_end);
        $agent->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        $allSaveArr = [];
        $allAgent   = [];
        foreach ( $agent->fetchAll() as $agentItem ) {
            $allSaveKey                    = $agentItem['id'];
            $changeConsumeArr[$allSaveKey] = 0;
            if ( isset($oAgentDailyStatArr[$allSaveKey]) ) {
                $saveItem                  = $oAgentDailyStatArr[$allSaveKey];
                $saveItem['stat_datetime'] = "'{$saveItem['stat_datetime']}'";
                $saveItem['update_time']   = time();
                $allSaveArr[$allSaveKey]   = $saveItem;
            } else {
                $saveDefault['agent_id'] = $allSaveKey;
                $allSaveArr[$allSaveKey] = $saveDefault;
            }
            $allAgent[$allSaveKey] = $agentItem;
        }
        if ( count($allSaveArr) == 0 ) {
            return;
        }

        //获取每天充值金币数
        $sql = "select sum(user_recharge_order_fee) as recharge_money,u.user_invite_agent_id as agent_id from user_recharge_order o 
inner join user u on u.user_id = o.user_id where u.user_invite_agent_id != 0 AND o.user_recharge_order_status = 'Y' AND o.user_recharge_agent_reward_flg = 'Y'
AND o.user_recharge_order_update_time >= :stat_start AND o.user_recharge_order_update_time < :stat_end group by u.user_invite_agent_id";

        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'recharge_money';
            $total_stat_field = 'total_recharge_money';
            $stat_num         = $resultItem[$stat_field];
            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       += $stat_num;
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                //收益
                $itemIncome = $stat_num * $allAgent[$agent_id]['recharge_distribution_profits'] / 100;
//                $allSaveArr[$agent_id]['income']       += $itemIncome;
//                $allSaveArr[$agent_id]['total_income'] += $itemIncome;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
//                    $firstLeaderIncome                                  = $stat_num * $allAgent[$first_leader_agent]['recharge_distribution_profits'] / 100 - $itemIncome;
//                    $allSaveArr[$first_leader_agent]['total_income']    += $firstLeaderIncome;
                    $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                    if ( $second_leader_agent ) {
                        $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
//                        $secondLeaderIncome                                  = $stat_num * $allAgent[$second_leader_agent]['recharge_distribution_profits'] / 100 - $firstLeaderIncome;
//                        $allSaveArr[$second_leader_agent]['total_income']    += $secondLeaderIncome;
                    }
                }
            }
        }


        //获取每天VIP充值
        $sql = "select sum(user_vip_order_combo_fee) as vip_money,u.user_invite_agent_id as agent_id from user_vip_order o 
inner join user u on u.user_id = o.user_id where u.user_invite_agent_id != 0 AND o.user_vip_order_status = 'Y' 
AND o.user_vip_order_update_time >= :stat_start AND o.user_vip_order_update_time < :stat_end group by u.user_invite_agent_id";

        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'vip_money';
            $total_stat_field = 'total_vip_money';
            $stat_num         = $resultItem[$stat_field];

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       += $stat_num;
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                //收益
//                $itemIncome                            = $stat_num * $allAgent[$agent_id]['vip_distribution_profits'] / 100;
//                $allSaveArr[$agent_id]['income']       += $itemIncome;
//                $allSaveArr[$agent_id]['total_income'] += $itemIncome;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
                    $firstLeaderIncome                                  = $stat_num * $allAgent[$first_leader_agent]['vip_distribution_profits'] / 100 - $itemIncome;
//                    $allSaveArr[$first_leader_agent]['total_income']    += $firstLeaderIncome;
//                    $second_leader_agent                                = $allAgent[$agent_id]['second_leader'];


                    if ( $second_leader_agent ) {
                        $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
//                        $secondLeaderIncome                                  = $stat_num * $allAgent[$second_leader_agent]['vip_distribution_profits'] / 100 - $firstLeaderIncome;
//                        $allSaveArr[$second_leader_agent]['total_income']    += $secondLeaderIncome;
                    }
                }
            }
        }


        // 获取今日注册用户充值金额
        $sql = "select sum(user_recharge_order_fee) as new_user_recharge_money,u.user_invite_agent_id as agent_id
from user_recharge_order o 
inner join user u on u.user_id = o.user_id 
where u.user_invite_agent_id != 0 AND o.user_recharge_order_status = 'Y' AND o.user_recharge_agent_reward_flg = 'Y'
AND o.user_recharge_order_update_time >= :stat_start AND o.user_recharge_order_update_time < :stat_end 
AND u.user_create_time >= :stat_start_day AND u.user_create_time < :stat_end 
group by u.user_invite_agent_id";

        $res = $this->db->query($sql, [
            'stat_start'     => $stat_start,
            'stat_end'       => $stat_end,
            'stat_start_day' => strtotime(date('Y-m-d', $stat_start))
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'new_user_recharge_money';
            $total_stat_field = 'total_new_user_recharge_money';
            $stat_num         = $resultItem[$stat_field];


            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       += $stat_num;
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;


                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
                }
            }
        }


        // 获取总注册数的
        $sql = "select count(1) as register_count,user_invite_agent_id as agent_id from user where 
user_register_time >= :stat_start AND user_register_time < :stat_end AND user_invite_agent_id != 0 group by user_invite_agent_id";

        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'register_count';
            $total_stat_field = 'total_register_count';
            $stat_num         = $resultItem[$stat_field];
            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       += $stat_num;
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
                }
            }
        }


        //获取总注册人数（去重）
        $sql = "select count(1) as affect_register_count,sum(case when length(device_no) > 18 then 1 else 0 end) as affect_ios_register_count,
bind_id as agent_id from user_device_bind where update_time >= :stat_start AND update_time < :stat_end AND bind_type = 'agent' group by bind_id";

        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'affect_register_count';
            $total_stat_field = 'total_affect_register_count';
            $stat_num         = $resultItem[$stat_field];

            $stat_field2       = 'affect_ios_register_count';
            $total_stat_field2 = 'total_ios_affect_register_count';
            $stat_num2         = $resultItem[$stat_field2];

            $stat_field3       = 'affect_and_register_count';
            $total_stat_field3 = 'total_and_affect_register_count';
            $stat_num3         = $stat_num - $stat_num2;

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       += $stat_num;
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                $allSaveArr[$agent_id][$stat_field2]       += $stat_num2;
                $allSaveArr[$agent_id][$total_stat_field2] += $stat_num2;

                $allSaveArr[$agent_id][$stat_field3]       += $stat_num3;
                $allSaveArr[$agent_id][$total_stat_field3] += $stat_num3;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field]  += $stat_num;
                    $allSaveArr[$first_leader_agent][$total_stat_field2] += $stat_num2;
                    $allSaveArr[$first_leader_agent][$total_stat_field3] += $stat_num3;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field]  += $stat_num;
                    $allSaveArr[$second_leader_agent][$total_stat_field2] += $stat_num2;
                    $allSaveArr[$second_leader_agent][$total_stat_field3] += $stat_num3;
                }
            }
        }


        //旗下用户消费金币数
        $sql = "select sum(- l.consume) as consume,u.user_invite_agent_id as agent_id from user_finance_log as l inner join user u on u.user_id = l.user_id
 where u.user_invite_agent_id !=0 AND user_amount_type = 'coin' AND consume < 0 AND l.create_time >= :stat_start AND l.create_time < :stat_end group by u.user_invite_agent_id";

        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'consume';
            $total_stat_field = 'total_consume';
            $stat_num         = $resultItem[$stat_field];
            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       += $stat_num;
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
                }
            }
        }

        //充值成功人数
        $sql = <<<SQL
select u.user_invite_agent_id as agent_id,count(1) as recharge_order_count,
sum(case when o.user_type = "Android" then o.user_recharge_order_fee else 0 end) as  and_recharge_money,
sum(case when o.user_type = "iOS" then o.user_recharge_order_fee else 0 end) as  ios_recharge_money
from user_recharge_order as o 
inner join `user` as u on u.user_id = o.user_id where o.user_recharge_order_status = 'Y'
and o.user_recharge_order_create_time >= :stat_start and o.user_recharge_order_create_time < :stat_end
and u.user_invite_agent_id != 0  AND o.user_recharge_agent_reward_flg = 'Y' GROUP BY u.user_invite_agent_id
SQL;
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id = $resultItem['agent_id'];


            $stat_field2       = 'and_recharge_money';
            $total_stat_field2 = 'total_and_recharge_money';
            $stat_num2         = $resultItem[$stat_field2];

            $stat_field3       = 'ios_recharge_money';
            $total_stat_field3 = 'total_ios_recharge_money';
            $stat_num3         = $resultItem[$stat_field3];

            $stat_field4       = 'recharge_order_count';
            $total_stat_field4 = 'total_recharge_order_count';
            $stat_num4         = $resultItem[$stat_field4];

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加


                $allSaveArr[$agent_id][$total_stat_field2] += $stat_num2;

                $allSaveArr[$agent_id][$total_stat_field3] += $stat_num3;

                $allSaveArr[$agent_id][$total_stat_field4] += $stat_num4;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field2] += $stat_num2;
                    $allSaveArr[$first_leader_agent][$total_stat_field3] += $stat_num3;
                    $allSaveArr[$first_leader_agent][$total_stat_field4] += $stat_num4;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field2] += $stat_num2;
                    $allSaveArr[$second_leader_agent][$total_stat_field3] += $stat_num3;
                    $allSaveArr[$second_leader_agent][$total_stat_field4] += $stat_num4;
                }
            }
        }

        // 获取体验匹配人数 以及体验匹配成功人数
        $sql = "select u.user_invite_agent_id as agent_id,count(distinct o.user_id) as free_times_try_user_count,
count(distinct case when match_success = 'Y' then o.user_id end ) as free_times_success_count 
from user_match_log as o
inner join user as u on u.user_id = o.user_id
where o.update_time >= :stat_start AND o.update_time < :stat_end AND o.has_free_times > 0
and u.user_invite_agent_id != 0
group by u.user_invite_agent_id";

        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end,
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'free_times_try_user_count';
            $total_stat_field = 'total_free_times_try_user_count';
            $stat_num         = $resultItem[$stat_field];


            $stat_field2       = 'free_times_success_count';
            $total_stat_field2 = 'total_free_times_success_count';
            $stat_num2         = $resultItem[$stat_field2];

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                $allSaveArr[$agent_id][$total_stat_field2] += $stat_num2;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field]  += $stat_num;
                    $allSaveArr[$first_leader_agent][$total_stat_field2] += $stat_num2;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field]  += $stat_num;
                    $allSaveArr[$second_leader_agent][$total_stat_field2] += $stat_num2;
                }
            }
        }

//        $shouldStatAllDayFields = [
//            'total_vip_user_count',
//            'total_register_recharge_count',
//            'recharge_user_count',
//            'total_recharge_user_count'
//        ];

        //购买VIP的人数  如果是当天的 初始化的数据已经设为0了 所以此时查询的是整天的数据
        $sql = <<<SQL
select u.user_invite_agent_id as agent_id,
count(distinct o.user_id) as  vip_click_user_count,
count(distinct case when o.user_vip_order_status = 'Y'then o.user_id end) as vip_user_count
from user_vip_order as o inner join user as u on o.user_id = u.user_id
where o.user_vip_order_update_time >= :stat_start_day and o.user_vip_order_update_time < :stat_end
and u.user_invite_agent_id != 0
group by u.user_invite_agent_id
SQL;
        $res = $this->db->query($sql, [
            'stat_start_day' => strtotime(date('Y-m-d', $stat_start)),
            'stat_end'       => $stat_end,
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'vip_user_count';
            $total_stat_field = 'total_vip_user_count';
            $stat_num         = $resultItem[$stat_field];

            $stat_field2       = 'vip_click_user_count';
            $total_stat_field2 = 'total_vip_click_user_count';
            $stat_num2         = $resultItem[$stat_field2];

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$total_stat_field] = +$stat_num;

                $allSaveArr[$agent_id][$total_stat_field2] = +$stat_num2;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
                    $allSaveArr[$first_leader_agent][$total_stat_field2] += $stat_num2;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
                    $allSaveArr[$second_leader_agent][$total_stat_field2] += $stat_num2;
                }
            }
        }

        //今日注册充值人数 以及今日充值人数  如果是当天的 初始化的数据已经设为0了 所以此时查询的是整天的数据
        $sql = <<<SQL
select u.user_invite_agent_id as agent_id,
count(distinct case when o.user_recharge_order_status = 'Y' then o.user_id end ) as recharge_user_count,
count(distinct o.user_id ) as recharge_click_user_count,
count(distinct case when u.user_create_time >= :stat_start_day and u.user_create_time < :stat_end AND o.user_recharge_order_status = 'Y' then o.user_id end) as register_recharge_count,
count(distinct case when u.user_create_time >= :stat_start_day and u.user_create_time < :stat_end then o.user_id end) as register_recharge_click_count
from user_recharge_order as o inner join user as u on o.user_id = u.user_id
where o.user_recharge_order_update_time >= :stat_start_day and o.user_recharge_order_update_time < :stat_end
and u.user_invite_agent_id != 0 AND o.user_recharge_agent_reward_flg = 'Y'
group by u.user_invite_agent_id
SQL;
        $res = $this->db->query($sql, [
            'stat_start_day' => strtotime(date('Y-m-d', $stat_start)),
            'stat_end'       => $stat_end,
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'recharge_user_count';
            $total_stat_field = 'total_recharge_user_count';
            $stat_num         = $resultItem[$stat_field];

            $stat_field2       = 'register_recharge_count';
            $total_stat_field2 = 'total_register_recharge_count';
            $stat_num2         = $resultItem[$stat_field2];

            $stat_field3       = 'recharge_click_user_count';
            $total_stat_field3 = 'total_recharge_click_user_count';
            $stat_num3         = $resultItem[$stat_field3];

            $stat_field4       = 'register_recharge_click_count';
            $total_stat_field4 = 'total_register_recharge_click_count';
            $stat_num4         = $resultItem[$stat_field4];

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       = +$stat_num;
                $allSaveArr[$agent_id][$total_stat_field] = +$stat_num;

                $allSaveArr[$agent_id][$total_stat_field2] = +$stat_num2;

                $allSaveArr[$agent_id][$total_stat_field3] = +$stat_num3;

                $allSaveArr[$agent_id][$total_stat_field4] = +$stat_num4;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$stat_field]       += $stat_num;
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;

                    $allSaveArr[$first_leader_agent][$total_stat_field2] += $stat_num2;
                    $allSaveArr[$first_leader_agent][$total_stat_field3] += $stat_num3;
                    $allSaveArr[$first_leader_agent][$total_stat_field4] += $stat_num4;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$stat_field]        += $stat_num;
                    $allSaveArr[$second_leader_agent][$total_stat_field]  += $stat_num;
                    $allSaveArr[$second_leader_agent][$total_stat_field2] += $stat_num2;
                    $allSaveArr[$second_leader_agent][$total_stat_field3] += $stat_num3;
                    $allSaveArr[$second_leader_agent][$total_stat_field4] += $stat_num4;
                }
            }
        }


        // 激活设备数
        $sql = <<<SQL
select device_active_agent_id as agent_id,count(1) as active_device_count
from device_active_log
where device_active_create_time >= :stat_start and device_active_create_time < :stat_end
and device_active_agent_id != 0
group by device_active_agent_id
SQL;
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end,
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'active_device_count';
            $total_stat_field = 'total_active_device_count';
            $stat_num         = $resultItem[$stat_field];

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
                }
            }
        }

        // 获取收益
        $sql = <<<SQL
select agent_id,sum(income) as income
from agent_water_log
where source_type in('recharge','vip') AND create_time >= :stat_start and create_time < :stat_end
group by agent_id
SQL;
        $res = $this->db->query($sql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end,
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $res->fetchAll() as $resultItem ) {
            $agent_id         = $resultItem['agent_id'];
            $stat_field       = 'income';
            $total_stat_field = 'total_income';
            $stat_num         = $resultItem[$stat_field];

            if ( isset($allSaveArr[$agent_id]) ) {
                // 存在 则添加
                $allSaveArr[$agent_id][$stat_field]       += $stat_num;
                $allSaveArr[$agent_id][$total_stat_field] += $stat_num;

                $first_leader_agent = $allAgent[$agent_id]['first_leader'];
                if ( $first_leader_agent ) {
                    $allSaveArr[$first_leader_agent][$total_stat_field] += $stat_num;
                }
                $second_leader_agent = $allAgent[$agent_id]['second_leader'];
                if ( $second_leader_agent ) {
                    $allSaveArr[$second_leader_agent][$total_stat_field] += $stat_num;
                }
            }
        }


        $saveAll = [];

        $updateAgentSqlArr = [];
        foreach ( $allSaveArr as $allItem ) {
            $itemAgentId = $allItem['agent_id'];
            if ( $allAgent[$itemAgentId]['status'] == 'N' ) {
                continue;
            }
            $updateAgentSqlArr[] = sprintf("update agent set commission = commission + %s, consume = consume + %s,total_consume = total_consume + %s,
recharge_money = recharge_money + %s,total_recharge_money = total_recharge_money + %s,register_count = register_count + %s,
total_register_count = total_register_count + %s,affect_register_count = affect_register_count + %s,total_affect_register_count = total_affect_register_count + %s,income = income + %s,
total_income = total_income + %s,balance_finish_datetime = %s,vip_money = vip_money + %s,total_vip_money = total_vip_money + %s
,affect_ios_register_count = affect_ios_register_count + %s,total_ios_affect_register_count = total_ios_affect_register_count + %s
,affect_and_register_count = affect_and_register_count + %s,total_and_affect_register_count = total_and_affect_register_count + %s
,recharge_user_count = recharge_user_count + %s,total_recharge_user_count = total_recharge_user_count + %s
 where id = %s and balance_finish_datetime < %s",
                $allItem['total_income'], $allItem['consume'], $allItem['total_consume'], $allItem['recharge_money'], $allItem['total_recharge_money'], $allItem['register_count'],
                $allItem['total_register_count'], $allItem['affect_register_count'], $allItem['total_affect_register_count'], $allItem['income'], $allItem['total_income'],
                $stat_time, $allItem['vip_money'], $allItem['total_vip_money'],
                $allItem['affect_ios_register_count'], $allItem['total_ios_affect_register_count'],
                $allItem['affect_and_register_count'], $allItem['total_and_affect_register_count'],
                $allItem['recharge_user_count'], $allItem['total_recharge_user_count'],
                $allItem['agent_id'], $stat_time);

            ksort($allItem);
            $saveAll[] = '(' . implode(',', $allItem) . ')';
        }
        $valueStr = implode(',', $saveAll);
        $keyArr   = array_keys($saveDefault);
        sort($keyArr);
        $keyStr = implode(',', $keyArr);

        if ( $isToday ) {
            $sInsertSql = sprintf('REPLACE INTO agent_daily_stat(%s) VALUES %s;', $keyStr, $valueStr);
            $this->db->execute($sInsertSql);
        } else {
            $sDeleteSql = 'delete from agent_daily_stat where stat_time = ' . $stat_time;
            $this->db->execute($sDeleteSql);

            $sInsertSql = sprintf('INSERT INTO agent_daily_stat(%s) VALUES %s;', $keyStr, $valueStr);
//echo $sInsertSql;die;
            $this->db->execute($sInsertSql);
        }

//        $sUpdateSql = '';
        if ( $isToday == FALSE && $updateAgentSqlArr ) {
            // 如果是前一天的数据 需要更新代理商 记录
            $sInsertSql = implode(';', $updateAgentSqlArr);
            $this->db->execute($sInsertSql);
        }
    }

    /**
     * 重置
     */
    public function resetTodayAgentAction()
    {
        $stat_date  = date('Y-m-d 00:00:00');
        $stat_time  = strtotime($stat_date);
        $stat_start = $stat_time;
        $stat_end   = time();
        $sDeleteSql = 'delete from agent_daily_stat where stat_time = ' . $stat_time;
        $this->db->execute($sDeleteSql);
        print_r('统计时间：' . date('Y-m-d H:i:s', $stat_start) . '-' . date('Y-m-d H:i:s', $stat_end) . "\n");
        $this->_dailyAgentStat($stat_start, $stat_end, TRUE);

    }


    /**
     * 将主播今日匹配时长 归零
     */
    public function refreshTodayMatchDurationAction()
    {
        $sql = 'update anchor set anchor_today_match_duration = 0,anchor_today_normal_duration = 0,anchor_today_match_times = 0,anchor_today_normal_times = 0,anchor_today_income = 0,anchor_today_new_match_times = 0,anchor_today_new_recharge_count = 0,anchor_today_called_times = 0';
        $this->db->execute($sql);

        // 主播派单记录
        $sql = 'update anchor_dispatch set anchor_dispatch_today_duration = 0,anchor_dispatch_today_times = 0';
        $this->db->execute($sql);
    }

    /**
     * 每分钟将主播今日收益从redis中存入mysql  TODO
     */
    public function updateAnchorTodayIncomeAction()
    {

    }

//    /**
//     * 在线用户每小时统计
//     * 添加记录  将当前在线用户 和上小时在线并离线的数据加起来
//     * 然后删除上小时在线并离线的数据加起来的key
//     */
//    public function onlineUserAction()
//    {
//        $timeFlg            = date('YmdH', strtotime('-1 hours'));
//        $statTime           = strtotime(date('Y-m-d H:00:00', strtotime('-1 hours')));
//        $user_online_key    = 'online:user';
//        $user_offline_key   = sprintf('offline:user:%s', $timeFlg);
//        $anchor_online_key  = 'online:anchor';
//        $anchor_offline_key = sprintf('offline:anchor:%s', $timeFlg);
//
//        $userOnlineCount  = $this->redis->sCard($user_online_key);
//        $userOfflineCount = $this->redis->sCard($user_offline_key);
//
//        $userOnlineCount += intval($userOfflineCount);
//
//        $anchorOnlineCount  = $this->redis->sCard($anchor_online_key);
//        $anchorOfflineCount = $this->redis->sCard($anchor_offline_key);
//
//        $anchorOnlineCount += intval($anchorOfflineCount);
//
//        $deleteSql = 'delete from online_stat_log where stat_time = ' . $statTime;
//        $this->db->execute($deleteSql);
//        $time       = time();
//        $sInsertSql = sprintf("insert into online_stat_log(stat_time,user_count,anchor_count,create_time,update_time)value (%s,%s,%s,%s,%s)", $statTime, $userOnlineCount, $anchorOnlineCount, $time, $time);
//        $this->db->execute($sInsertSql);
//
//        $this->redis->del($user_offline_key, $anchor_offline_key);
//    }

    protected function _intervalStat($statTimeHour)
    {
        $timeFlg            = date('YmdH', $statTimeHour);
        $statTime           = strtotime(date('Y-m-d H:00:00', $statTimeHour));
        $user_online_key    = 'online:user';
        $user_offline_key   = sprintf('offline:user:%s', $timeFlg);
        $anchor_online_key  = 'online:anchor';
        $anchor_offline_key = sprintf('offline:anchor:%s', $timeFlg);

        $userOnlineCount  = $this->redis->sCard($user_online_key);
        $userOfflineCount = $this->redis->sCard($user_offline_key);

        $userOnlineCount += intval($userOfflineCount);

        $anchorOnlineCount  = $this->redis->sCard($anchor_online_key);
        $anchorOfflineCount = $this->redis->sCard($anchor_offline_key);

        $anchorOnlineCount += intval($anchorOfflineCount);


        $statEndTime = $statTime + 3600 - 1;
        // 注册的设备
        $sql = <<<SQL
SELECT COUNT(1) AS new_device_count
from user_device_bind where create_time >= :stat_start AND create_time < :stat_end
SQL;
        $res = $this->db->query($sql, [
            'stat_start' => $statTime,
            'stat_end'   => $statEndTime
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oRegisterDeviceCount = $res->fetch();
        $new_device_count     = $oRegisterDeviceCount['new_device_count'] ?? 0;

        // 首次充值数  总充值人数
        $sql = <<<SQL
SELECT sum(case user_recharge_is_first when 'Y' then 1 else 0 end ) AS first_recharge_count,
count( distinct user_id ) as recharge_user_count
from user_recharge_order where user_recharge_order_update_time >= :stat_start AND user_recharge_order_update_time < :stat_end
SQL;
        $res = $this->db->query($sql, [
            'stat_start' => $statTime,
            'stat_end'   => $statEndTime
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $oRechargeResult      = $res->fetch();
        $first_recharge_count = $oRechargeResult['first_recharge_count'] ?? 0;
        $recharge_user_count  = $oRechargeResult['recharge_user_count'] ?? 0;


        // 主播点播接单数
        $sql = <<<SQL
SELECT COUNT(1) AS normal_video_chat_count
from user_private_chat_log where
 chat_type = 'normal' AND status = 6 AND
create_time >= :stat_start AND create_time < :stat_end
SQL;
        $res = $this->db->query($sql, [
            'stat_start' => $statTime,
            'stat_end'   => $statEndTime
        ]);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $videoChatResult         = $res->fetch();
        $normal_video_chat_count = $videoChatResult['normal_video_chat_count'] ?? 0;

        $deleteSql = 'delete from interval_stat_log where stat_time = ' . $statTime;
        $this->db->execute($deleteSql);
        $time       = time();
        $sInsertSql = sprintf("insert into interval_stat_log(stat_time,user_count,anchor_count,create_time,update_time,new_device_count,first_recharge_count,recharge_user_count,normal_video_chat_count)
value (%s,%s,%s,%s,%s,%s,%s,%s,%s)", $statTime, $userOnlineCount, $anchorOnlineCount, $time, $time, $new_device_count, $first_recharge_count, $recharge_user_count, $normal_video_chat_count);
        $this->db->execute($sInsertSql);

        $this->redis->del($user_offline_key, $anchor_offline_key);
    }

    /**
     * 分段统计
     */
    public function intervalStatAction($params = '')
    {
        // 默认为上个小时
        $start      = date('Y-m-d H:00:00', strtotime('-1 hours'));
        $todayStart = $start;
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;

        if ( $params ) {
            $tmpOne     = explode('-', $params[0]);
            $tmpOneHour = array_pop($tmpOne);
            $start      = implode('-', $tmpOne) . ' ' . $tmpOneHour . ':00:00';

            if ( $start != date('Y-m-d H:i:s', strtotime($start)) ) {
                var_dump($start, date('Y-m-d H:i:s', strtotime($start)));
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime($todayStart) ) {
                exit("开始最多统计到$todayStart\n");
            }
            if ( isset($params[1]) ) {
                $tmpTwo     = explode('-', $params[1]);
                $tmpTwoHour = array_pop($tmpTwo);
                $end        = implode('-', $tmpTwo) . ' ' . $tmpTwoHour . ':00:00';

                if ( $end != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime($todayStart) ) {
                    exit("结束最多统计到$todayStart\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 3600 - 1;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }

        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 3600 ) {
            $item_stat_end = $item_stat_start + 3600;
            print_r('统计日期：' . date('Y-m-d H', $item_stat_start) . ' - ' . date('Y-m-d H', $item_stat_end) . "\n");
            $this->_intervalStat($item_stat_start);
        }
    }

    /**
     * 苹果审核状态定时修改
     */
    public function appleCheckAction($params = [])
    {
        return;
    }


    public function signToRetainAction()
    {
        //查询所有签到记录存入数据
        $sql = <<<SQL
select ua.user_device_id,l.user_signin_log_create_time,u.user_create_time from user_signin_log as l inner join user_account as ua on l.user_id = ua.user_id
inner join user as u on u.user_id = l.user_id
where l.user_signin_log_create_time >= unix_timestamp('2018-10-08') and u.user_is_anchor = 'N'
SQL;
        $res = $this->db->query($sql);
        $res->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        foreach ( $res->fetchAll() as $key => $resultItem ) {
            $registerDate     = date('Ymd', $resultItem['user_create_time']);
            $secondRetainDate = date('Ymd', $resultItem['user_create_time'] + 86400);
            $threeRetainDate  = date('Ymd', $resultItem['user_create_time'] + 86400 * 2);
            $sevenRetainDate  = date('Ymd', $resultItem['user_create_time'] + 86400 * 7);
            $thirtyRetainDate = date('Ymd', $resultItem['user_create_time'] + 86400 * 29);
            $retainDateArr    = [
                $secondRetainDate => [
                    'timesFlg' => 2,
                    'dateFlg'  => $registerDate,
                ],
                $threeRetainDate  => [
                    'timesFlg' => 3,
                    'dateFlg'  => $registerDate,
                ],
                $sevenRetainDate  => [
                    'timesFlg' => 7,
                    'dateFlg'  => $registerDate,
                ],
                $thirtyRetainDate => [
                    'timesFlg' => 30,
                    'dateFlg'  => $registerDate,
                ]
            ];
            $currentDate      = date('Ymd', $resultItem['user_signin_log_create_time']);
            $retainInfo       = $retainDateArr[$currentDate] ?? [];
            if ( $retainInfo ) {
                $key = sprintf('device_retain:%s:%s', $retainInfo['timesFlg'], $retainInfo['dateFlg']);
//                if($retainInfo['dateFlg'] == '20181109'){
//                    var_dump(date('Ymd', $resultItem['user_create_time']),$currentDate,$retainDateArr,$retainInfo);die;
//                }
                $this->redis->sAdd($key, $resultItem['user_device_id']);
            }
        }
    }

    /**
     * 单独统计某个更新数据
     */
    public function oldDateRetainAction()
    {
        $stat_start = strtotime(date('Y-m-d', strtotime('-30 day')));
        $stat_end   = strtotime(date('Y-m-d', strtotime('-1 day')));
        $itemSql    = '';
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $secondRetainKey            = sprintf('device_retain:2:%s', date('Ymd', $item_stat_start));
            $threeRetainKey             = sprintf('device_retain:3:%s', date('Ymd', $item_stat_start));
            $sevenRetainKey             = sprintf('device_retain:7:%s', date('Ymd', $item_stat_start));
            $thirtyRetainKey            = sprintf('device_retain:30:%s', date('Ymd', $item_stat_start));
            $two_retain_device_count    = intval($this->redis->sCard($secondRetainKey));
            $three_retain_device_count  = intval($this->redis->sCard($threeRetainKey));
            $seven_retain_device_count  = intval($this->redis->sCard($sevenRetainKey));
            $thirty_retain_device_count = intval($this->redis->sCard($thirtyRetainKey));
            $itemSql                    .= <<<SQL
update daily_data_stat set two_retain_device_count = $two_retain_device_count,three_retain_device_count = $three_retain_device_count,
seven_retain_device_count = $seven_retain_device_count,thirty_retain_device_count = $thirty_retain_device_count where stat_time = $item_stat_start;
SQL;
        }
        $this->db->execute($itemSql);
    }


    /**
     * 主播信息统计
     * 每天从缓存数据中统计
     */
    public function todayAnchorStatAction()
    {
        $stat_start = strtotime(date('Y-m-d'));
        $stat_end   = time();
        $this->_dailyAnchorStat($stat_start, $stat_end);
    }

    public function dailyAnchorStatAction($params = [])
    {
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $end        = date('Y-m-d 00:00:00');
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;


        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }
//        var_dump($stat_start,$stat_end,$start,$end);
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $this->_dailyAnchorStat($item_stat_start, $item_stat_end);
        }
        die;
    }


    private function _dailyAnchorStat($stat_start, $stat_end)
    {

        // 查询出之前的统计
        $anchorStat = $this->db->query('SELECT * FROM anchor_stat where stat_time =' . $stat_start);
        $anchorStat->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $anchorStatArr = [];
        foreach ( $anchorStat->fetchAll() as $anchorStatItem ) {
            $anchorStatArr[$anchorStatItem['user_id']] = $anchorStatItem;
        }

        $anchor = $this->db->query('SELECT * FROM anchor where anchor_create_time <' . $stat_end);
        $anchor->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        $saveData  = [];
        $deleteIds = [];
        foreach ( $anchor->fetchAll() as $anchorItem ) {
            $redisKey = sprintf('anchor:stat:%s:%s', date('Ymd', $stat_start), $anchorItem['user_id']);
            if ( $this->redis->exists($redisKey) ) {
                $tmp = $this->redis->hGetAll($redisKey);

                $loginTime       = $tmp['login_time'] ?? 0;
                $logoutTime      = $tmp['logout_time'] ?? 0;
                $online_duration = $tmp['online_duration'] ?? 0;
                if ( $loginTime ) {
                    $itemStartTime = $loginTime;
                    $itemEndTime   = $logoutTime;
                    if ( $loginTime > $logoutTime ) {
                        // 如果登录时间比下线时间长 则表示还没下线 则计算时间为结束时间
                        $itemEndTime     = $stat_end;
                        $online_duration += $itemEndTime - $itemStartTime;
                    }

                }
                $saveItem = [
                    $anchorItem['user_id'],
                    $stat_start,
                    $tmp['time_income'] ?? 0,
                    $tmp['gift_income'] ?? 0,
                    $tmp['video_income'] ?? 0,
                    $tmp['word_income'] ?? 0,
                    $tmp['match_duration'] ?? 0,
                    $tmp['match_times'] ?? 0,
                    $tmp['match_recharge_count'] ?? 0,
                    $tmp['normal_chat_duration'] ?? 0,
                    $tmp['normal_chat_times'] ?? 0,
                    $tmp['normal_chat_call_times'] ?? 0,
                    $tmp['update_time'],
                    $tmp['update_time'],
                    $online_duration,
                    $tmp['guide_msg_times'] ?? 0,
                    $tmp['guide_user_count'] ?? 0,
                    $tmp['chat_game_income'] ?? 0,
                    $tmp['guard_income'] ?? 0,
                    $tmp['invite_recharge_income'] ?? 0,
                    $tmp['wechat_income'] ?? 0,
                ];
                if ( key_exists($anchorItem['user_id'], $anchorStatArr) ) {
                    // 之前有统计  需要判断数据是否改变
                    if ( $tmp['update_time'] != $anchorStatArr[$anchorItem['user_id']]['update_time'] ) {
                        $deleteIds[] = $anchorStatArr[$anchorItem['user_id']]['id'];
                        $saveData[]  = '(' . implode(',', $saveItem) . ')';
                    }
                } else {
                    $saveData[] = '(' . implode(',', $saveItem) . ')';
                }
            }
        }

        if ( $saveData ) {
            // 先删除数据
            if ( $deleteIds ) {
                $deleteIdsStr = implode(',', $deleteIds);
                $deleteSql    = "delete from anchor_stat where id in ($deleteIdsStr)";
                $this->db->execute($deleteSql);
            }

            $insertValue = implode(',', $saveData);
            $itemSql     = <<<SQL
insert into anchor_stat (user_id,stat_time,time_income,gift_income,video_income,word_income,match_duration,match_times,match_recharge_count,
normal_chat_duration,normal_chat_times,normal_chat_call_times,create_time,update_time,online_duration,guide_msg_times,guide_user_count,chat_game_income,guard_income,invite_recharge_income,wechat_income) values $insertValue
SQL;
            $this->db->execute($itemSql);

            print "[" . date('Y-m-d H:i:s') . "]更新" . count($saveData) . "条主播记录\n";
        } else {
            print "[" . date('Y-m-d H:i:s') . "]没有需要更新的数据\n";
        }


    }


    /**
     * 重置今天主播的有效被呼叫数
     */
    public function resetTodayAnchorNormalCallTimeAction()
    {
        $anchor = $this->db->query('SELECT * FROM anchor where anchor_today_called_times > 0');
        $anchor->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        foreach ( $anchor->fetchAll() as $anchorItem ) {
            $redisKey = sprintf('anchor:stat:%s:%s', date('Ymd'), $anchorItem['user_id']);
            $this->redis->hSet($redisKey, 'normal_chat_call_times', $anchorItem['anchor_today_called_times']);
            $this->redis->hSet($redisKey, 'update_time', time());
        }
    }


    /**
     * 每日统计数据
     *      支付类型
     *      支付金额
     *      支付成功金额
     *      充值订单数
     *      支付成功订单数
     */
    public function dailyRechargeStatAction($params = '')
    {
        // 默认为昨天
        $start      = date('Y-m-d 00:00:00', strtotime('-1 day'));
        $yesterday  = date('Y-m-d', strtotime('-1 day'));
        $end        = date('Y-m-d 00:00:00');
        $today      = date('Y-m-d');
        $stat_start = strtotime($start);
        $stat_end   = $stat_start;


        if ( $params ) {
            $start = $params[0];
            if ( $start . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($start)) ) {
                exit("开始参数错误\n");
            }
            $stat_start = strtotime($start);
            if ( $stat_start > strtotime(date('Y-m-d')) ) {
                exit("最多统计到$today\n");
            }
            if ( isset($params[1]) ) {
                $end = $params[1];
                if ( $end . ' 00:00:00' != date('Y-m-d H:i:s', strtotime($end)) ) {
                    exit("结束参数错误\n");
                }
                $stat_end = strtotime($end);
                if ( $stat_end > strtotime(date('Y-m-d')) ) {
                    exit("最多统计到$today\n");
                }
                if ( $stat_start == $stat_end ) {
                    $stat_end += 24 * 3600;
                    $end      = date('Y-m-d H:i:s', $stat_end);
                } else if ( $stat_end < $stat_start ) {
                    exit("开始日期不能大于结束日期\n");
                }
            }
        }
//        var_dump($stat_start,$stat_end,$start,$end);
        for ( $item_stat_start = $stat_start; $item_stat_start <= $stat_end; $item_stat_start += 24 * 3600 ) {
            $item_stat_end = $item_stat_start + 24 * 3600;
            print_r('统计日期：' . date('Y-m-d', $item_stat_start) . "\n");
            $this->_dailyRechargeStat($item_stat_start, $item_stat_end);
        }
        die;


    }

    /**
     * 统计当天数据
     */
    public function dailyRechargeStatTodayAction()
    {
        $stat_start = strtotime(date('Y-m-d'));
        $stat_end   = time();
        $this->_dailyRechargeStat($stat_start, $stat_end);
    }

    private function _dailyRechargeStat($stat_start, $stat_end)
    {

        $saveData    = [];
        $currentTime = time();


        // 充值订单统计
        $rechargeSql = <<<RECHARGESQL
SELECT user_recharge_order_type,count(1) as recharge_order_count,sum(user_recharge_order_fee) as recharge_money_count,
sum(case user_recharge_order_status when "Y" then 1 else 0 end) as  recharge_order_success_count,
sum(case user_recharge_order_status when "Y" then user_recharge_order_fee else 0 end) as  recharge_money_success_count
FROM user_recharge_order where user_recharge_order_update_time >= :stat_start AND user_recharge_order_update_time < :stat_end
group by user_recharge_order_type
RECHARGESQL;
        $rechargeRes = $this->db->query($rechargeSql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $rechargeRes->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $rechargeRes->fetchAll() as $resultItem ) {
            $tmp        = [
                'recharge_stat_order_type'          => "'recharge'",
                'recharge_stat_pay_type'            => "'{$resultItem['user_recharge_order_type']}'",
                'recharge_stat_pay_money'           => $resultItem['recharge_money_count'] ?? 0,
                'recharge_stat_pay_success_money'   => $resultItem['recharge_money_success_count'] ?? 0,
                'recharge_stat_order_count'         => $resultItem['recharge_order_count'] ?? 0,
                'recharge_stat_order_success_count' => $resultItem['recharge_order_success_count'] ?? 0,
                'recharge_stat_time'                => $stat_start,
                'recharge_stat_create_time'         => $currentTime
            ];
            $saveData[] = sprintf("(%s)", implode(',', $tmp));
        }

        // VIP订单统计
        $vipSql = <<<RECHARGESQL
SELECT user_vip_order_type,count(1) as vip_order_count,sum(user_vip_order_combo_fee) as vip_money_count,
sum(case user_vip_order_status when "Y" then 1 else 0 end) as  vip_order_success_count,
sum(case user_vip_order_status when "Y" then user_vip_order_combo_fee else 0 end) as  vip_money_success_count
FROM user_vip_order where user_vip_order_update_time >= :stat_start AND user_vip_order_update_time < :stat_end
group by user_vip_order_type
RECHARGESQL;
        $vipRes = $this->db->query($vipSql, [
            'stat_start' => $stat_start,
            'stat_end'   => $stat_end
        ]);
        $vipRes->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

        foreach ( $vipRes->fetchAll() as $resultItem ) {
            $tmp        = [
                'recharge_stat_order_type'          => "'vip'",
                'recharge_stat_pay_type'            => "'{$resultItem['user_vip_order_type']}'",
                'recharge_stat_pay_money'           => $resultItem['vip_money_count'] ?? 0,
                'recharge_stat_pay_success_money'   => $resultItem['vip_money_success_count'] ?? 0,
                'recharge_stat_order_count'         => $resultItem['vip_order_count'] ?? 0,
                'recharge_stat_order_success_count' => $resultItem['vip_success_count'] ?? 0,
                'recharge_stat_time'                => $stat_start,
                'recharge_stat_create_time'         => $currentTime
            ];
            $saveData[] = sprintf("(%s)", implode(',', $tmp));
        }

        if($saveData){
            $keyStr = 'recharge_stat_order_type,recharge_stat_pay_type,recharge_stat_pay_money,recharge_stat_pay_success_money,
        recharge_stat_order_count,recharge_stat_order_success_count,recharge_stat_time,recharge_stat_create_time';

            $valueStr   = implode(',', $saveData);
            $sDeleteSql = "delete from daily_recharge_stat where recharge_stat_time = $stat_start";
            $this->db->execute($sDeleteSql);

            $sInsertSql = sprintf('INSERT INTO daily_recharge_stat(%s) VALUES %s;', $keyStr, $valueStr);
            $this->db->execute($sInsertSql);
        }

    }


    /**
     * 初始化砸蛋统计数据
     */
    public function initDailyEggStatAction()
    {
        $todayTime = strtotime('today');
        $todayDate = date('Y-m-d' ,$todayTime);
        $selectSql = 'select * from yuyin_live.daily_egg_stat where daily_egg_stat_time = '.$todayTime;
        $data = $this->db->query($selectSql);
        $insertArr = [];
        if(!$data->fetchAll()){
            $insertArr[] = "($todayTime,'$todayDate')";
        }

        $tomorrowTime = $todayTime + 86400;
        $tomorrowDate = date('Y-m-d' ,$tomorrowTime);
        $selectSql = 'select * from yuyin_live.daily_egg_stat where daily_egg_stat_time = '.$tomorrowTime;
        $data = $this->db->query($selectSql);
        $insertArr = [];
        if(!$data->fetchAll()){
            $insertArr[] = "($tomorrowTime,'$tomorrowDate')";
        }

        if($insertArr){
            $insertSql = 'insert into yuyin_live.daily_egg_stat(daily_egg_stat_time,daily_egg_stat_date)values '.implode(',',$insertArr);
            $this->db->execute($insertSql);
            print '更新数据'.$this->db->affectedRows() . "\n";
        }else {
            print '没有更新' . "\n";
        }
    }

}
