<?php

namespace app\live\controller\chatgame;

use app\live\library\Redis;
use app\live\model\live\ChatGameConfig as ChatGameConfigModel;
use app\live\model\live\ChatGameCategory as ChatGameCategoryModel;
use think\Exception;
use app\common\controller\Backend;

/**
 * 聊天游戏类型管理
 */
class Category extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.chat_game_category');
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
     * add 添加
     */
    public function add()
    {
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row = new ChatGameCategoryModel();

            $row->chat_game_category_name = $params['chat_game_category_name'];

            $row->validate(
                [
                    'chat_game_category_name' => 'require',
                ],
                [
                    'chat_game_category_name.require' => __('Parameter %s can not be empty', [ 'chat_game_category_name' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
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
        $row = ChatGameCategoryModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params                       = $this->request->param('row/a');
            $row->chat_game_category_name = $params['chat_game_category_name'];

            $row->validate(
                [
                    'chat_game_category_name' => 'require',
                ],
                [
                    'chat_game_category_name.require' => __('Parameter %s can not be empty', [ 'chat_game_category_name' ]),
                ]
            );

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
        // 判断是否有该类型的游戏
        $data = ChatGameConfigModel::get(['chat_game_category_id' => $ids]);
        if($data){
           $this->error('请先删除该类型下的游戏');
        }
        ChatGameCategoryModel::where('id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids = '')
    {
    }

    /**
     * sort 排序
     */
    public function sort()
    {

    }


}