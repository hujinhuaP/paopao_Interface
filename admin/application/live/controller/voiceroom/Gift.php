<?php

namespace app\live\controller\voiceroom;

use app\common\controller\Backend;

/**
 * 摄影师管理
 */
class Gift extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_gift_log');
    }


    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where('room_id = '.\app\live\model\live\Room::B_CHAT_ID)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('room_id = '.\app\live\model\live\Room::B_CHAT_ID)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
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


}