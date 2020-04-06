<?php

namespace app\live\controller\anchor;

use app\common\controller\Backend;
use app\live\library\Redis;
use app\live\model\live\Group;


/**
 * 时长分析
 *
 */
class Playtime extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.anchor');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,TRUE);
            $total  = $this->model->where($where)->with('User')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $data = [];
            $redis = new Redis();
            $key = 'anchor:today:dot:'.date('Ymd');
            $allData = $redis->hGetAll($key);
            foreach($list  as $item){
                $tmp = $item->toArray();
                $tmp['anchor_today_income'] = round($allData[$tmp['user_id']],2) ?? 0;
                $data[] = $tmp;
            }
            $result = [
                "total" => $total,
                "rows"  => $data
            ];
            return json($result);
        }
        $oGroup = Group::all();
        $this->view->assign("row_group", $oGroup);
        return $this->view->fetch();
    }


}