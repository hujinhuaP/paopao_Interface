<?php

namespace app\live\controller\datastat;

use app\common\controller\Backend;
use app\live\library\Redis;
use app\live\model\live\Group;
use think\Db;

/**
 * 主播信息统计
 *
 */
class Anchorstat extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.anchor_stat');
    }

    /**
     * index 列表
     */
    public function index()
    {
        $stat_start_time = date('Y-m-d', strtotime('-1 month'));
        $stat_end_time   = date('Y-m-d');
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            if($sort == 'anchor_stat.stat_income'){
                $sort = 'anchor_stat.time_income + anchor_stat.gift_income + anchor_stat.video_income + anchor_stat.word_income + anchor_stat.guard_income + anchor_stat.invite_recharge_income + anchor_stat.wechat_income';
            }
            $total  = $this->model->where($where)->with('User,Anchor')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User,Anchor')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $oGroup = Group::all();
        $this->view->assign("row_group", $oGroup);
        $this->view->assign([
            'stat_start_time' => $stat_start_time,
            'stat_end_time'   => $stat_end_time,

        ]);
        return $this->view->fetch();
    }


    /**
     * sumindex 按时间段统计
     */
    public function sumindex()
    {
        if ( $this->request->isAjax() ) {
            $filter          = $this->request->get("filter", '');
            $filter          = json_decode($filter, TRUE);
            $filter          = $filter ? $filter : [];
            $stat_start_time = $filter['stat_start_time'];
            $stat_end_time   = $filter['stat_end_time'];
            $offset          = $this->request->get("offset", 0);
            $limit           = $this->request->get("limit", 0);
            $nUserId         = $filter['user_id'] ?? '';
            $groupId         = $filter['group_id'] ?? '';
            $sUserNickname   = $filter['user_nickname'] ?? '';
            $sort = $this->request->get("sort", "normal_chat_call_times");
            $order = $this->request->get("order", "DESC");

            $stat_start_timestamp = strtotime($stat_start_time);
            $stat_end_timestamp   = strtotime($stat_end_time);
            if ( $stat_start_timestamp > $stat_end_timestamp ) {
                $stat_start_time      = date('Y-m-d', strtotime('-1 month'));
                $stat_end_time        = date('Y-m-d');
                $stat_start_timestamp = strtotime($stat_start_time);
                $stat_end_timestamp   = strtotime($stat_end_time);
            }

            $oAnchorStat = new \app\live\model\live\AnchorStat();
            $total       = $oAnchorStat->getGroupCount($stat_start_timestamp, $stat_end_timestamp, $nUserId, $groupId, $sUserNickname);
            $list        = [];
            if ( $total > 0 ) {
                $list = $oAnchorStat->groupData($stat_start_timestamp, $stat_end_timestamp, $offset, $limit,$sort,$order, $nUserId, $groupId, $sUserNickname);
            }
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $oGroup = Group::all();
        $this->view->assign("row_group", $oGroup);
        return $this->view->fetch();
    }


}