<?php

namespace app\live\controller\anchor;

use app\live\library\Redis;
use app\live\model\live\AnchorLiveLog;
use app\live\model\live\AnchorSignStat;
use app\live\model\live\UserPrivateChatLog;
use think\Exception;
use app\common\controller\Backend;
use app\live\model\live\Kv;
use app\live\model\live\User;
use app\live\model\live\AnchorLevel;
use app\live\model\live\Anchor as AnchorModel;

/**
 * 主播管理
 * @Authors yeah_lsj@yeah.net
 */
class Anchor extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.anchor');
    }

    public function index()
    {
        if ( $this->request->isAjax() ) {
            $hot_flg = $this->request->get("hot_flg", '');
            $where_str = '1=1';
            if($hot_flg == 'hot'){
                $where_str = 'anchor_hot_man > 0';
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);

            $total  = $this->model::where($where)->where($where_str)->with('User')->order($sort, $order)->count();
            $list   = $this->model::where($where)->where($where_str)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * @return string|\think\response\Json
     *  签约主播列表
     */
    public function signlist()
    {
        if ( $this->request->isAjax() ) {

            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);

            $total  = $this->model::where('anchor.anchor_is_sign = "Y"')->where($where)->with('User')->order($sort, $order)->count();
            $list   = $this->model::where('anchor.anchor_is_sign = "Y"')->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list,
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * index 列表
     */
    public function indexBak()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword     = $this->request->param('search');
            $nOffset      = $this->request->param('offset');
            $nLimit       = $this->request->param('limit');
            $aFilter      = json_decode($this->request->param('filter'), 1);
            $aOp          = json_decode($this->request->param('op'), 1);
            $oSelectQuery = AnchorModel::where('1=1');
            $oTotalQuery  = AnchorModel::where('1=1');
            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%' . $sKeyword . '%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
                }
            }
            if ( $aFilter ) {
                foreach ( $aFilter as $key => $value ) {
                    if ( stripos($aOp[$key], 'LIKE') !== FALSE ) {
                        $value     = str_replace([
                            'LIKE ',
                            '...'
                        ], [
                            '',
                            $value
                        ], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ( $key ) {
                        case 'anchor_level':
                        case 'anchor_is_forbid':
                            $oSelectQuery->where('a.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('a.' . $key, $aOp[$key], $value);
                            break;
                        default:
                            $oSelectQuery->where('u.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('u.' . $key, $aOp[$key], $value);
                            break;
                    }
                }
            }
            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }
            $total = $oTotalQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->count();
            $list  = $oSelectQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->order('u.user_id desc')->select();
            foreach ( $list as &$v ) {
                $v['user_coin']          = sprintf('%.2f', $v['user_coin']);
                $v['user_consume_total'] = sprintf('%.2f', $v['user_consume_total']);
                $v['user_dot']           = sprintf('%.2f', $v['user_dot']);
                $v['user_collect_total'] = sprintf('%.2f', $v['user_collect_total']);
            }
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     * 签约主播
     */
    public function sign($ids = '')
    {
        $selectStartArr = [
            '14' => 14,
            '15' => 15,
            '16' => 16,
            '17' => 17,
            '18' => 18,
            '19' => 19,
            '20' => 20,
            '21' => 21,
            '22' => 22,
            '23' => 23,
            '0' => 24,
            '1' => 01
        ];
        $selectEndArr   = [
            '15' => 15,
            '16' => 16,
            '17' => 17,
            '18' => 18,
            '19' => 19,
            '20' => 20,
            '21' => 21,
            '22' => 22,
            '23' => 23,
            '0' => 24,
            '1' => 01,
            '2' => 02
        ];
        $row            = AnchorModel::where('user_id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isAjax() ) {
            $params = $this->request->param('row/a');
            if ( !in_array($params['anchor_sign_live_start_time'], $selectStartArr) ) {
                $this->error(__('Params'));
            }
            if ( !in_array($params['anchor_sign_live_end_time'], $selectEndArr) ) {
                $this->error(__('Params'));
            }
            $checkStartTime = $params['anchor_sign_live_start_time'];
            if ( $params['anchor_sign_live_start_time'] < 12 ) {
                $checkStartTime = $params['anchor_sign_live_start_time'] + 24;
            }

            $checkEndTime = $params['anchor_sign_live_end_time'];
            if ( $params['anchor_sign_live_end_time'] < 12 ) {
                $checkEndTime = $params['anchor_sign_live_end_time'] + 24;
            }

            if ( $checkStartTime >= $checkEndTime ) {
                $this->error(__('Start time can not later than end time'));
            }
            if ( $params['anchor_sign_live_start_time'] == 24 ) {
                $params['anchor_sign_live_start_time'] = 0;
            }
            if ( $params['anchor_sign_live_end_time'] == 24 ) {
                $params['anchor_sign_live_end_time'] = 0;
            }

            $row->anchor_sign_live_start_time = $params['anchor_sign_live_start_time'];
            $row->anchor_sign_live_end_time   = $params['anchor_sign_live_end_time'];
            $row->anchor_is_sign              = 'Y';
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                // 修改本次统计以后（不包括本次）的统计数据表记录修改
                $today12hour = strtotime(date('Y-m-d 12:00:00'));
                if ( time() >= $today12hour ) {
                    // 已经记录为当天时间
                    $updateStartStatTimestamp = strtotime(date('Y-m-d'));
                } else {
                    $updateStartStatTimestamp = strtotime(date('Y-m-d')) - 24 * 3600;
                }
                AnchorSignStat::where('user_id', $ids)->where('stat_date > ' . $updateStartStatTimestamp)->update([
                    'anchor_sign_live_start_time' => $row->anchor_sign_live_start_time,
                    'anchor_sign_live_end_time'   => $row->anchor_sign_live_end_time
                ]);

                $this->success();
            }
        }
        $this->view->assign("selectStartArr", $selectStartArr);
        $this->view->assign("selectEndArr", $selectEndArr);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     * 取消签约
     */
    public function cancelsign($ids = '')
    {
        $row = AnchorModel::where('user_id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isAjax() ) {
            $row->anchor_is_sign = 'N';
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '')
    {
        $row = AnchorModel::where('user_id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params          = $this->request->param('params');
            $params          = explode('=', $params);
            $allow_params = [
                'anchor_is_show_index'
            ];
            if(!in_array($params[0],$allow_params)){
                $this->error('无权修改');
            }
            $row[$params[0]] = $params[1];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
    }

    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids = '')
    {
        if ( $this->request->isAjax() ) {
            $user_id          = session('user_id');
            $aFilter          = json_decode($this->request->param('filter'), TRUE);
            $oLiveSelectQuery = AnchorLiveLog::where('1=1');
            $oChatSelectQuery = UserPrivateChatLog::where('1=1');
            if ( empty($aFilter['type']) ) {
                $aFilter['type'] = 1;
            }
            if ( $aFilter ) {
                switch ( $aFilter['type'] ) {
                    case 1:
                        $num        = 1;
                        $date       = empty($aFilter['date']) ? date('Y-m-d') : $aFilter['date'];
                        $start_date = strtotime(date('Y-m-d 00:00:00', strtotime($date)));
                        $end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($date)));
                        break;
                    case 2:
                        $num        = 7;
                        $start_date = strtotime(date('Y-m-d 00:00:00', strtotime('-6 days', strtotime($aFilter['date']))));
                        $end_date   = strtotime(date('Y-m-d 23:59:59'));
                        break;
                    case 3:
                        $num        = 30;
                        $start_date = strtotime(date('Y-m-d 00:00:00', strtotime('-29 days', strtotime($aFilter['date']))));
                        $end_date   = strtotime(date('Y-m-d 23:59:59'));
                        break;
                }
            } else {
                $num        = 1;
                $start_date = strtotime(date('Y-m-d 00:00:00'));
                $end_date   = strtotime(date('Y-m-d 23:59:59'));
            }
//            $oLiveData = $oLiveSelectQuery->field("sum(anchor_live_end_time-anchor_live_start_time) as total,FROM_UNIXTIME(anchor_live_log_create_time,'%Y-%m-%d') as create_time")->where("user_id={$user_id} and anchor_live_log_create_time >= {$start_date} and anchor_live_log_create_time <={$end_date} and anchor_live_end_time > 0")->group("FROM_UNIXTIME(anchor_live_log_create_time,'%Y-%m-%d')")->select();
            $oChatData = $oChatSelectQuery->field("sum(duration) as total,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time")->where("chat_log_anchor_user_id={$user_id} and create_time >= {$start_date} and create_time <={$end_date}")->group("FROM_UNIXTIME(create_time,'%Y-%m-%d')")->select();
//            foreach ($oLiveData as $item) {
//                $live[$item['create_time']] = intval($item['total'] / 60);
//            }
            foreach ( $oChatData as $item ) {
                $chat[$item['create_time']] = intval($item['total'] / 60);
            }
            $result = [];
            for ( $i = 0; $i < $num; $i++ ) {
                $key = date('Y-m-d', strtotime("+{$i} day", $start_date));
                if ( empty($live[$key]) && empty($chat[$key]) ) {
                    continue;
                } else {
                    $arr['date'] = strtotime($key);
                    $arr['live'] = isset($live[$key]) ? $live[$key] : 0;
                    $arr['chat'] = isset($chat[$key]) ? $chat[$key] : 0;
                    $arr['type'] = $aFilter['type'];
                    $result[]    = $arr;
                }
            }
            $result = [
                "total" => count($result),
                "rows"  => array_reverse($result)
            ];
            return json($result);
        }
        $row = AnchorModel::alias('a')->join('user u', 'u.user_id=a.user_id')->where('a.user_id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
//        $nHour                    = (int)($row->anchor_live_time_total / 60 / 60);
//        $nMinute                  = (int)(($row->anchor_live_time_total - ($nHour * 60 * 60)) / 60);
//        $nSecond                  = (int)(($row->anchor_live_time_total - (($nHour * 60 * 60) + ($nMinute * 60))));
//        $row->anchor_live_time    = __('%d hour', $nHour) . __('%d minute', $nMinute) . __('%d second', $nSecond);
        $nChatTime             = UserPrivateChatLog::where("chat_log_anchor_user_id", $ids)->sum('duration');
        $nHour                 = (int)($nChatTime / 60 / 60);
        $nMinute               = (int)(($nChatTime - ($nHour * 60 * 60)) / 60);
        $nSecond               = (int)(($nChatTime - (($nHour * 60 * 60) + ($nMinute * 60))));
        $row->anchor_chat_time = __('%d hour', $nHour) . __('%d minute', $nMinute) . __('%d second', $nSecond);
//        $row->user_withdraw_ratio = $row->user_withdraw_ratio ?: Kv::getValue(Kv::KEY_DOT_TO_MONEY_RATIO, 0);
        session('user_id', $ids);
        $this->view->assign("row", $row);
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
     * @param  string $ids
     */
    public function edit($ids = '')
    {
        $oAnchor        = AnchorModel::alias('a')->join('user u', 'u.user_id=a.user_id')->where('a.user_id', $ids)->find();
        $oUser          = User::get($ids);
        $nWithdrawRatio = Kv::getValue(Kv::KEY_DOT_TO_MONEY_RATIO, 0);

        if ( !$oAnchor || !$oUser )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');
//            $oUser->user_withdraw_ratio = $params['user_withdraw_ratio'];

            if ( $oAnchor->save($oAnchor->getData()) === FALSE ) {
                $this->error($oAnchor->getError());
            }
            if ( $oUser->save($oUser->getData()) === FALSE ) {
                $this->error($oUser->getError());
            } else {
                $this->success();
            }
        }
        $oAnchor->system_withdraw_ratio = $nWithdrawRatio;
        $this->view->assign("row", $oAnchor);
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

    public function getStatic()
    {

        $type    = $this->request->post('type');
        $user_id = $this->request->post('user_id', 0);
        $where   = "user_id={$user_id}";

        $date = [];
        switch ( $type ) {
            case 1:
                $title        = '每日私聊时长';
                $start_date_s = strtotime(date('Y-m-d 00:00:00', strtotime("-1 week Monday")));
                for ( $i = 0; $i < 7; $i++ ) {
                    $start_date   = strtotime(date('Y-m-d 00:00:00', strtotime("+{$i} days", $start_date_s)));
                    $end_date     = strtotime(date("Y-m-d 23:59:59", strtotime("+{$i} days", $start_date_s)));
                    $oSelectQuery = UserPrivateChatLog::where("chat_log_anchor_user_id = {$user_id} and create_time >= {$start_date} and create_time <= {$end_date}")->field("sum(duration) as total")->find();
                    $date []      = date('Y-m-d', $start_date);
                    $data[]       = $oSelectQuery['total'] ? ceil($oSelectQuery['total'] / 60) : 0;
                }
                break;
            case 2:
                $title      = '每周私聊时长';
                $query_date = $this->getMonthWeeks();
                foreach ( $query_date as $item ) {
                    $start_date   = strtotime(date('Y-m-d 00:00:00', strtotime($item['start'])));
                    $end_date     = strtotime(date('Y-m-d 23:59:59', strtotime($item['end'])));
                    $date[]       = $item['start'] . '-' . $item['end'];
                    $oSelectQuery = UserPrivateChatLog::where("chat_log_anchor_user_id = {$user_id} and create_time >= {$start_date} and create_time <= {$end_date}")->field("sum(duration) as total")->find();
                    $data[]       = $oSelectQuery['total'] ? ceil($oSelectQuery['total'] / 60) : 0;
                }
                break;
            case 3:
                $title = '每月私聊时长';
                for ( $i = 1; $i <= 12; $i++ ) {
                    if ( $i < 10 ) {
                        $firstday = date("Y") . "-0{$i}-01";
                    } else {
                        $firstday = date("Y") . "-{$i}-01";
                    }
                    $lastday      = date('Y-m-d', strtotime("{$firstday} +1 month -1 day"));
                    $start_date   = strtotime(date('Y-m-d 00:00:00', strtotime($firstday)));
                    $end_date     = strtotime(date('Y-m-d 23:59:59', strtotime($lastday)));
                    $oSelectQuery = UserPrivateChatLog::where("chat_log_anchor_user_id = {$user_id} and create_time >= {$start_date} and create_time <= {$end_date}")->field("sum(duration) as total")->find();
                    $data[]       = $oSelectQuery['total'] ? ceil($oSelectQuery['total'] / 60) : 0;
                }
                $date = [
                    '一月',
                    '二月',
                    '三月',
                    '四月',
                    '五月',
                    '六月',
                    '七月',
                    '八月',
                    '九月',
                    '十月',
                    '十一月',
                    '十二月'
                ];
                break;
        }
        $row['title'] = $title;
        $row['data']  = $data;
        $row['date']  = $date;
        $this->success('', '', $row);
    }

    private function getMonthWeeks()
    {
        $date       = date('Y-m-01');
        $ret        = [];
        $stimestamp = strtotime($date);
        $mdays      = date('t', $stimestamp);
        $msdate     = date('Y-m-d', $stimestamp);
        $medate     = date('Y-m-' . $mdays, $stimestamp);
        $etimestamp = strtotime($medate);
        //獲取第一周
        $week = date('w', $stimestamp);
        if ( date('w', $stimestamp) == 0 ) {
            $week = 7;
        }
        $zcsy            = 7 - $week;//第一周去掉第一天還有幾天
        $zcs1            = $msdate;
        $zce1            = date('Y-m-d', strtotime("+$zcsy day", $stimestamp));
        $ret[1]['start'] = $zcs1;
        $ret[1]['end']   = $zce1;
        //獲取中間周次
        $jzc = 0;
        //獲得當前月份是6周次還是5周次
        $jzc0 = "";
        $jzc6 = "";
        for ( $i = $stimestamp; $i <= $etimestamp; $i += 86400 ) {
            if ( date('w', $i) == 0 ) {
                $jzc0++;
            }
            if ( date('w', $i) == 6 ) {
                $jzc6++;
            }
        }
        if ( $jzc0 == 5 && $jzc6 == 5 ) {
            $jzc = 5;
        } else {
            $jzc = 4;
        }
        date_default_timezone_set('PRC');
        $t = strtotime('+1 monday ' . $msdate);
        $n = 1;
        for ( $n = 1; $n < $jzc; $n++ ) {
            $b                   = strtotime("+$n week -1 week", $t);
            $dsdate              = date("Y-m-d", $b);
            $dedate              = date("Y-m-d", strtotime("+6 day", $b));
            $jzcz                = $n + 1;
            $ret[$jzcz]['start'] = $dsdate;
            $ret[$jzcz]['end']   = $dedate;
        }
        if ( strtotime($dedate) < strtotime($medate) ) {
            $ret[$jzcz + 1]['start'] = date('Y-m-d', strtotime('+1 day', strtotime($dedate)));
            $ret[$jzcz + 1]['end']   = $medate;
        }
        return $ret;
    }

    public function play($ids = ''){
        $row = AnchorModel::where('user_id', $ids)->find();
        $this->view->assign('url',$row->anchor_video);
        $this->view->assign('cover',$row->anchor_video_cover);
        return $this->view->fetch();
    }
}