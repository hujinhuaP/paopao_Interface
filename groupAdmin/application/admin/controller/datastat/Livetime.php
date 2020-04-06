<?php
namespace app\admin\controller\datastat;

use app\common\controller\Backend;
use app\admin\model\api\UserOnlineLog;
use app\admin\model\api\AnchorSignStat;
use think\Session;

/**
 * 直播时长
 */
class Livetime extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new AnchorSignStat();
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id',true);
            $begin_stat_time = strtotime(date('Y-m-d 12:00:00'));
            $search_stat_time = strtotime(date('Y-m-d'));
            if(time() < $begin_stat_time){
                $search_stat_time -= 24 * 3600;
            }
            $total  = $this->model::where('stat_date <= '.$search_stat_time)->where('anchor_sign_stat.group_id',Session::get('admin.group_id'))->where($where)->with('User,Group')->order($sort, $order)->count();
            $list   = $this->model::where('stat_date <= '.$search_stat_time)->where('anchor_sign_stat.group_id',Session::get('admin.group_id'))->where($where)->with('User,Group')->order($sort, $order)->limit($offset, $limit)->select();
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
     * 详情
     */
    public function detail($ids = '')
    {
        $row            = AnchorSignStat::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isAjax() ) {
            $this->model = new UserOnlineLog();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null,true);
            $stat_date = $row->stat_date;
            $begin_time = $stat_date + 12 * 3600;
            $end_time = $begin_time + 24 * 3600;
            $user_id = $row->user_id;
            $oSelectQuery = $this->model::where('user.user_id',$user_id)->where('online_time','>=',$begin_time)
                ->where('online_time','<',$end_time)->where('user.user_group_id',Session::get('admin.group_id'));
            $oCountQuery = $this->model::where('user.user_id',$user_id)->where('online_time','>=',$begin_time)
                ->where('online_time','<',$end_time)->where('user.user_group_id',Session::get('admin.group_id'));
            $total  = $oCountQuery->where($where)->with('User')->order($sort, $order)->count();
            $list   = $oSelectQuery->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('row',$row);
        return $this->view->fetch();
    }


}