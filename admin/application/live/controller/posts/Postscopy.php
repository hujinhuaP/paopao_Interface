<?php

namespace app\live\controller\posts;

use app\live\model\live\ShortPosts;
use app\live\model\live\ShortPostsDelete;
use app\live\model\live\User;
use think\Exception;
use app\common\controller\Backend;

/**
 * 动态管理 删除备份
 *
 */
class Postscopy extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.short_posts_delete');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
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
     * 动态详情
     * 获取动态的用户信息 和动态内容
     */
    public function detail($ids = '')
    {
        $row = ShortPostsDelete::where('short_posts_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        $oUser = User::get($row->short_posts_user_id);

        $this->view->assign([
            'row'         => $row,
            'videoSource' => $row->short_posts_video,
            'imageSource' => explode(',', $row->short_posts_images),
            'oUser'       => $oUser
        ]);
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     */
    public function edit($ids = '')
    {
        $row = ShortPostsDelete::where('short_posts_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params                        = $this->request->param('row/a');
            $row->short_posts_status       = $params['short_posts_status'];
            $row->short_posts_check_remark = $params['short_posts_check_remark'];
            $row->short_posts_check_time   = time();
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }

        $this->view->assign([
            'row' => $row,
        ]);
        return $this->view->fetch();
    }


    /**
     * delete 删除
     * 将数据删除 放入删除表
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {
        echo 1;die;
        $row = ShortPostsDelete::where('short_posts_id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        if(!$row->delete()){
            $this->error($row->getError());
        }
        $this->success();
    }



}