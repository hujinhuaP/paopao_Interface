<?php

namespace app\live\controller\anchor;

use app\live\library\Redis;
use app\live\model\live\AnchorLiveLog;
use app\live\model\live\UserPrivateChatLog;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Kv;
use app\live\model\live\User;
use app\live\model\live\AnchorLevel;
use app\live\model\live\Anchor as AnchorModel;

/**
 * 转播管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Relaylist extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = AnchorModel::where('1=1 and anchor_type > 0');
            $oTotalQuery  = AnchorModel::where('1=1 and anchor_type > 0');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                }
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        case 'anchor_is_live':
                        case 'anchor_level':
                        case 'anchor_is_forbid':
                        case 'anchor_live_time_total':
                            $oSelectQuery->where('a.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('a.'.$key, $aOp[$key], $value);
                            break;

                        default:
                            $oSelectQuery->where('u.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('u.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->count();
            $list  = $oSelectQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->order('u.user_id desc')->select();
            foreach ($list as &$v) {
                $v['user_coin']          = sprintf('%.2f', $v['user_coin']);
                $v['user_consume_total'] = sprintf('%.2f', $v['user_consume_total']);
                $v['user_dot']           = sprintf('%.2f', $v['user_dot']);
                $v['user_collect_total'] = sprintf('%.2f', $v['user_collect_total']);
            }

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids='')
    {
        if($this->request->isAjax()){
            $user_id = session('user_id');
            $aFilter  = json_decode($this->request->param('filter'), true);
            $oLiveSelectQuery = AnchorLiveLog::where('1=1');
            $oChatSelectQuery = UserPrivateChatLog::where('1=1');
            if(empty($aFilter['type'])){
                $aFilter['type'] = 1;
            }
            if ($aFilter) {
                switch($aFilter['type']){
                    case 1:
                        $num = 1;
                        $date = empty($aFilter['date']) ? date('Y-m-d'): $aFilter['date'] ;
                        $start_date =  strtotime(date('Y-m-d 00:00:00',strtotime($date)));
                        $end_date = strtotime(date('Y-m-d 23:59:59',strtotime($date)));
                        break;
                    case 2:
                        $num = 7;
                        $start_date = strtotime(date('Y-m-d 00:00:00',strtotime('-6 days',time())));
                        $end_date = strtotime(date('Y-m-d 23:59:59'));
                        break;
                    case 3:
                        $num = 30;
                        $start_date = strtotime(date('Y-m-d 00:00:00',strtotime('-29 days',time())));
                        $end_date = strtotime(date('Y-m-d 23:59:59'));
                        break;
                }
            }else{
                $num = 1;
                $start_date = strtotime(date('Y-m-d 00:00:00'));
                $end_date = strtotime(date('Y-m-d 23:59:59'));
            }
            $oLiveData = $oLiveSelectQuery->field("sum(anchor_live_end_time-anchor_live_start_time) as total,FROM_UNIXTIME(anchor_live_log_create_time,'%Y-%m-%d') as create_time")
                ->where("user_id={$user_id} and anchor_live_log_create_time >= {$start_date} and anchor_live_log_create_time <={$end_date}")
                ->group("FROM_UNIXTIME(anchor_live_log_create_time,'%Y-%m-%d')")
                ->select();
            $oChatData = $oChatSelectQuery->field("sum(duration) as total,FROM_UNIXTIME(create_time,'%Y-%m-%d') as create_time")
                ->where("chat_log_anchor_user_id={$user_id} and create_time >= {$start_date} and create_time <={$end_date}")
                ->group("FROM_UNIXTIME(create_time,'%Y-%m-%d')")
                ->select();
            foreach ($oLiveData as $item){
                $live[$item['create_time']] = intval($item['total']/60);
            }
            foreach ($oChatData as $item){
                $chat[$item['create_time']] = intval($item['total']/60);
            }
            $result = [];
            for($i = 0; $i<$num;$i++){
                $key = date('Y-m-d',strtotime("+{$i} day",$start_date));
                if(empty($live[$key]) && empty($chat[$key])){
                    continue;
                }else{
                    $arr['date'] = strtotime($key);
                    $arr['live'] = isset($live[$key]) ? $live[$key]: 0;
                    $arr['chat'] = isset($chat[$key]) ? $chat[$key]: 0;
                    $result[] = $arr;
                }
            }
            $result = array("total" => count($result), "rows" => $result);
            return json($result);
        }
        $row = AnchorModel::alias('a')
            ->join('user u', 'u.user_id=a.user_id')
            ->where('a.user_id', $ids)
            ->find();
        if (!$row)
            $this->error(__('No Results were found'));

        $nHour   = (int)($row->anchor_live_time_total/60/60);
        $nMinute = (int)(($row->anchor_live_time_total-($nHour*60*60))/60);
        $nSecond = (int)(($row->anchor_live_time_total-(($nHour*60*60)+($nMinute*60))));
        $row->anchor_live_time = __('%d hour', $nHour).__('%d minute', $nMinute).__('%d second', $nSecond);

        $nChatTime = UserPrivateChatLog::where("chat_log_anchor_user_id",$ids)->sum('duration');
        $nHour   = (int)($nChatTime/60/60);
        $nMinute = (int)(($nChatTime-($nHour*60*60))/60);
        $nSecond = (int)(($nChatTime-(($nHour*60*60)+($nMinute*60))));

        $row->anchor_chat_time = __('%d hour', $nHour).__('%d minute', $nMinute).__('%d second', $nSecond);
        $row->user_withdraw_ratio = $row->user_withdraw_ratio ?: Kv::getValue(Kv::KEY_DOT_TO_MONEY_RATIO, 0);
        $row->anchor_type = $row->anchor_type == 1 ? '手动转播':'自动转播';
        session('user_id',$ids);
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
    public function edit($ids='')
    {
        $oAnchor = AnchorModel::alias('a')
            ->join('user u', 'u.user_id=a.user_id')
            ->where('a.user_id', $ids)
            ->find();

        $oUser = User::get($ids);

        $nWithdrawRatio = Kv::getValue(Kv::KEY_DOT_TO_MONEY_RATIO, 0);
        $nMaxAnchorLevel = AnchorLevel::max('anchor_level');

        if (!$oAnchor || !$oUser)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');
            $oAnchor->anchor_exp = $oAnchor->anchor_level == $params['anchor_level'] ? $oAnchor->anchor_exp : AnchorLevel::where('anchor_level', $params['anchor_level'])->value('anchor_level_exp', 0);
            $oAnchor->anchor_level = $params['anchor_level'];
            $oUser->user_withdraw_ratio = $params['user_withdraw_ratio'];

            $oAnchor->validate(
                [
                    'anchor_level'         => 'require|egt:0|elt:'.$nMaxAnchorLevel,
                ],
                [
                    'anchor_level.egt'     =>  __('%d <= %s <= %d', 0, __('Anchor level'), $nMaxAnchorLevel),
                    'anchor_level.elt'     =>  __('%d <= %s <= %d', 0, __('Anchor level'), $nMaxAnchorLevel),
                    'anchor_level.require' =>  __('Parameter %s can not be empty', ['user_budan_type']),
                ]
            );

            if ($oAnchor->save($oAnchor->getData()) === false) {
                $this->error($oAnchor->getError());
            }

            $oUser->validate(
                [
                    'user_withdraw_ratio'         => 'require|egt:0|elt:100',
                ],
                [
                    'user_withdraw_ratio.egt'     =>  __('%d%% <= %s <= %d%%', 0, __('Withdraw ratio'), 100),
                    'user_withdraw_ratio.elt'     =>  __('%d%% <= %s <= %d%%', 0, __('Withdraw ratio'), 100),
                    'user_withdraw_ratio.require' =>  __('Parameter %s can not be empty', ['user_budan_type']),
                ]
            );

            if ($oUser->save($oUser->getData()) === false) {
                $this->error($oUser->getError());
            } else {
                $this->success();
            }
        }

        $oAnchor->user_withdraw_ratio   = $oUser->user_withdraw_ratio;
        $oAnchor->system_withdraw_ratio = $nWithdrawRatio;
        $oAnchor->max_anchor_level      = $nMaxAnchorLevel;
        $this->view->assign("row", $oAnchor);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids='')
    {

    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids='')
    {
        # code...
    }

}