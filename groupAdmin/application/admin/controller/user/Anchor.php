<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use think\Session;

/**
 * 主播管理
 */
class Anchor extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('api.anchor');
    }

    public function index()
    {
        if ( $this->request->isAjax() ) {
            $where_str = 'user.user_group_id = '.Session::get('admin.group_id');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);

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





    public function play($ids = '')
    {
        $row = $this->model::where('user_id', $ids)->find();
        $oUser = \app\admin\model\api\User::get($ids);
        if(!$row || !$oUser || $oUser->user_group_id != Session::get('admin.group_id')){
            $this->error(__('No Results were found'));
        }
        $this->view->assign('url', $row->anchor_video);
        $this->view->assign('cover', $row->anchor_video_cover);
        return $this->view->fetch();
    }
}