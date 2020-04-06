<?php

namespace app\live\controller\group;

use app\common\controller\Backend;
use app\live\model\live\Group as GroupModel;
use app\live\model\live\GroupIncomeStat as GroupIncomeStatModel;

/**
 * 公会列表
 */
class Group extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.group');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('group_name');
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
     * add 添加
     */
    public function add()
    {

        if ( $this->request->isPost() ) {
            $params                   = $this->request->param('row/a');
            $row                      = new GroupModel();
            $row->group_name          = $params['group_name'];
            $row->group_type          = $params['group_type'];
            $row->divid_type          = $params['divid_type'];
            $row->divid_precent       = $params['divid_precent'];
            $row->divid_time_precent  = $params['divid_time_precent'];
            $row->divid_gift_precent  = $params['divid_gift_precent'];
            $row->divid_video_precent = $params['divid_video_precent'];
            $row->divid_chat_precent  = $params['divid_chat_precent'];
            $row->status              = $params['status'];
            $row->invite_code         = $row->createInviteCode();
            $existData                = $this->model->where("group_name", $params['group_name'])->find();
            if ( $existData ) {
                $this->error(__('Exist group name'));
            }
            $row->validate([
                'group_name'          => 'require',
                'group_type'          => 'require',
                'divid_type'          => 'require',
                'divid_precent'       => 'require|between:0,100',
                'divid_time_precent'  => 'require|between:0,100',
                'divid_gift_precent'  => 'require|between:0,100',
                'divid_video_precent' => 'require|between:0,100',
                'divid_chat_precent'  => 'require|between:0,100',
            ], [
                'group_name.require'          => __('Parameter %s can not be empty', [ __('Group name') ]),
                'group_type.require'          => __('Parameter %s can not be empty', [ __('Group type') ]),
                'divid_type.require'          => __('Parameter %s can not be empty', [ __('Divid type') ]),
                'divid_precent.require'       => __('Parameter %s can not be empty', [ __('Divid precent') ]),
                'divid_time_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of live time') ]),
                'divid_gift_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of get gift') ]),
                'divid_video_precent.require' => __('Parameter %s can not be empty', [ __('Divid precent of video') ]),
                'divid_chat_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of chat') ]),
                'divid_precent.between'       => __('%s must be between %d and %d', [
                    __('Divid precent'),
                    0,
                    100
                ]),
                'divid_time_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of live time'),
                    0,
                    100
                ]),
                'divid_gift_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of get gift'),
                    0,
                    100
                ]),
            ]);
            if ( !$row->save($row->getData()) ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {

        $row = GroupModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params                   = $this->request->param('row/a');
            $row->group_type          = $params['group_type'];
            $row->divid_type          = $params['divid_type'];
            $row->divid_precent       = $params['divid_precent'];
            $row->divid_time_precent  = $params['divid_time_precent'];
            $row->divid_gift_precent  = $params['divid_gift_precent'];
            $row->divid_video_precent = $params['divid_video_precent'];
            $row->divid_chat_precent  = $params['divid_chat_precent'];
            $row->status              = $params['status'];
            if ( $row->group_name != $params['group_name'] ) {
                $row->group_name = $params['group_name'];
            }
            $existData = $this->model->where([
                "group_name" => $params['group_name'],
                'id'         => [
                    'neq',
                    $ids
                ]
            ])->find();
            if ( $existData ) {
                $this->error(__('Exist group name'));
            }
            $row->validate([
                'group_name'          => 'require',
                'group_type'          => 'require',
                'divid_type'          => 'require',
                'divid_precent'       => 'require|between:0,100',
                'divid_time_precent'  => 'require|between:0,100',
                'divid_gift_precent'  => 'require|between:0,100',
                'divid_video_precent' => 'require|between:0,100',
                'divid_chat_precent'  => 'require|between:0,100',
            ], [
                'group_name.require'          => __('Parameter %s can not be empty', [ __('Group name') ]),
                'group_type.require'          => __('Parameter %s can not be empty', [ __('Group type') ]),
                'divid_type.require'          => __('Parameter %s can not be empty', [ __('Divid type') ]),
                'divid_precent.require'       => __('Parameter %s can not be empty', [ __('Divid precent') ]),
                'divid_time_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of live time') ]),
                'divid_gift_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of get gift') ]),
                'divid_video_precent.require' => __('Parameter %s can not be empty', [ __('Divid precent of video') ]),
                'divid_chat_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of chat') ]),
                'divid_precent.between'       => __('%s must be between %d and %d', [
                    __('Divid precent'),
                    0,
                    100
                ]),
                'divid_time_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of live time'),
                    0,
                    100
                ]),
                'divid_gift_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of get gift'),
                    0,
                    100
                ]),
            ]);
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
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

}