<?php

namespace app\live\controller\voiceroom;

use app\common\controller\Backend;

/**
 * 摄影师管理
 */
class Room extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.room');
    }


    public function index()
    {
        $row = $this->model::get(\app\live\model\live\Room::B_CHAT_ID);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');
            $result = $row->save($params);
            if ( $result !== FALSE ) {
                $this->success();
            } else {
                $this->error($row->getError());
            }
        }
        $this->view->assign("roomOpenFlgList", $this->model->getRoomOpenFlgList());
        $this->view->assign("row", $row);
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
     * @return string|\think\response\Json
     * @throws \think\Exception
     * 统计
     */
    public function stat()
    {
        $startDate = date('Y-m-d', strtotime('-7 day'));
        $endDate   = date('Y-m-d');
        $filter    = $this->request->get("filter", '');
        $filter    = json_decode($filter, TRUE);
        $filter    = $filter ? $filter : [];
        if ( is_array($filter) && isset($filter['createtime']) ) {
            $statTimeArr = explode(' - ', $filter['createtime']);
            if ( count($statTimeArr) == 2 ) {
                $startDate = date('Y-m-d', strtotime($statTimeArr[0]));
                $endDate   = date('Y-m-d', strtotime($statTimeArr[1]));
            }

        }
        if ( $this->request->isAjax() ) {
            $startTime = strtotime($startDate);
            $endTime   = strtotime($endDate) + 86399;

            $allData = [];
            for ( $time = $endTime; $time >= $startTime; $time -= 86400 ) {
                $stat_date             = date('Y-m-d', $time);
                $allData[ $stat_date ] = [
                    'stat_date'         => $stat_date,
                    'total_coin'        => 0,
                    'total_dot'         => 0,
                    'enter_max_user'    => 0,
                    'enter_times'       => 0,
                    'enter_user_number' => 0,
                ];
            }

            $financeStatSql = "select sum(consume_coin + consume_free_coin) as total_coin,sum(live_gift_dot) as total_dot,
FROM_UNIXTIME(user_gift_log_create_time,'%Y-%m-%d') as stat_date from yuyin_live.user_gift_log where room_id = 1
AND user_gift_log_create_time >= {$startTime} AND user_gift_log_create_time <= {$endTime}
group by stat_date desc";
            $financeStatData           = $this->model->query($financeStatSql);

            foreach ( $financeStatData as $item ) {
                $itemDate = $item['stat_date'];
                if ( array_key_exists($itemDate, $allData) ) {
                    $allData[ $itemDate ]['total_coin'] = $item['total_coin'];
                    $allData[ $itemDate ]['total_dot']  = $item['total_dot'];
                }
            }

            // 进房统计
            $userStatSql = "select count(DISTINCT enter_room_user_id) as enter_user_number,count(1) as enter_times,
       max(enter_room_online) as enter_max_user,FROM_UNIXTIME(enter_room_online_time,'%Y-%m-%d') as stat_date 
from yuyin_live.enter_room_log where enter_room_room_id = 1 AND enter_room_online_time >= {$startTime} 
 AND enter_room_online_time <= {$endTime} group by stat_date desc";
            $userStatData           = $this->model->query($userStatSql);

            foreach ( $userStatData as $item ) {
                $itemDate = $item['stat_date'];
                if ( array_key_exists($itemDate, $allData) ) {
                    $allData[ $itemDate ]['enter_user_number'] = $item['enter_user_number'];
                    $allData[ $itemDate ]['enter_times']  = $item['enter_times'];
                    $allData[ $itemDate ]['enter_max_user']  = $item['enter_max_user'];
                }
            }

            $list = [];
            foreach ($allData as $item){
                $list[] = $item;
            }


            $total  = count($list);
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('default_date', sprintf("%s - %s", $startDate, $endDate));
        return $this->view->fetch();
    }

}