<?php

namespace app\live\controller\voiceroom;

use app\common\controller\Backend;

/**
 * 砸蛋记录
 */
class Dailyeggstat extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.daily_egg_stat');
    }


    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where('daily_egg_stat_time <= '.strtotime('today'))->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('daily_egg_stat_time <= '.strtotime('today'))->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
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


}