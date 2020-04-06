<?php

namespace app\live\controller\video;

use app\common\controller\Backend;
use app\live\controller\anchor\Anchor;
use app\live\model\live\User;
use app\live\model\live\UserGuideVideo;

/**
 * 新用户匹配诱导视频
 */
class Guide extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_guide_video');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
            $total  = $this->model->where($where)->with('User')->order($sort, $order)->count();
            $list   = $this->model->where($where)->with('User')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row = new UserGuideVideo();

            $row->video_url      = $params['video_url'];
            $row->anchor_user_id = $params['anchor_user_id'];
            $oUser = User::get($row->anchor_user_id);
            if(!$oUser || $oUser->user_is_anchor == 'N'){
                $this->error('主播不存在');
            }
            $row->validate(
                [
                    'video_url'      => 'require',
                    'anchor_user_id' => 'require',
                ],
                [
                    'video_url.require'      => __('Parameter %s can not be empty', [ '视频地址' ]),
                    'anchor_user_id.require' => __('Parameter %s can not be empty', [ 'Anchor' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $upload_url = config('upload.uploadurl_video');
        $this->view->assign("upload_url", $upload_url);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {
        $row = UserGuideVideo::get($ids);

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row->video_url      = $params['video_url'];
            $row->anchor_user_id = $params['anchor_user_id'];
            $oUser = User::get($row->anchor_user_id);
            if(!$oUser || $oUser->user_is_anchor == 'N'){
                $this->error('主播不存在');
            }
            $row->validate(
                [
                    'video_url'      => 'require',
                    'anchor_user_id' => 'require',
                ],
                [
                    'video_url.require'      => __('Parameter %s can not be empty', [ '视频地址' ]),
                    'anchor_user_id.require' => __('Parameter %s can not be empty', [ 'Anchor' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $upload_url = config('upload.uploadurl_video');
        $this->view->assign("upload_url", $upload_url);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {
        $this->model::where('id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids = '')
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
     * @param string $ids
     * @return string
     * @throws \think\Exception
     * 播放
     */
    public function play($ids = '')
    {
        $row = $this->model::where('id', $ids)->find();
        $this->view->assign('url', $row->video_url);
        return $this->view->fetch();
    }
}