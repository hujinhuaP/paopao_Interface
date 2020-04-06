<?php

namespace app\live\controller\matchcenter;

use app\common\controller\Backend;
use app\live\model\live\UserPrivateChatLog;

/**
 *
 * @icon fa fa-circle-o
 */
class Dispatch extends Backend
{

    /**
     * RoomChat模型对象
     * @var \app\live\model\live\DispatchChat
     */
    protected $model = NULL;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.dispatch_chat');

    }

    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->with('User,AnchorUser')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User,AnchorUser')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param string $ids
     */
    public function detail( $ids = '' )
    {
        $row = $this->model::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        $oUser       = \app\live\model\live\User::get($row->dispatch_chat_user_id);
        $oAnchorUser = \app\live\model\live\User::get($row->dispatch_chat_anchor_user_id);
        $rowChat     = UserPrivateChatLog::get($row->dispatch_chat_chat_id);

        $status = [
            0    => '等待',
            '1'  => __('接通'),
            '-1' => __('用户取消'),
            '-2' => __('主播取消'),
            '-3' => __('主播超时'),
        ];
        $row->dispatch_chat_status = $status[$row->dispatch_chat_status];

        $this->view->assign([
            'row'         => $row,
            'oUser'       => $oUser,
            'oAnchorUser' => $oAnchorUser,
            'rowChat'     => $rowChat,
        ]);
        return $this->view->fetch();
    }


}
