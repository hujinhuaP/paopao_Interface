<?php

namespace app\live\controller\chatgame;

use app\live\library\Redis;
use app\live\model\live\ChatGameConfig as ChatGameConfigModel;
use app\live\model\live\ChatGameCategory as ChatGameCategoryModel;
use app\live\model\live\UserChatGame as UserChatGameModel;
use think\Exception;
use app\common\controller\Backend;

/**
 * 聊天游戏管理
 */
class Index extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.chat_game_config');
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
        $oChatGameCategory = ChatGameCategoryModel::all();
        $this->view->assign("row_category", $oChatGameCategory);
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {
        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row = new ChatGameConfigModel();

            $row->chat_game_content     = $params['chat_game_content'];
            $row->chat_game_category_id = $params['chat_game_category_id'];
            $row->chat_game_price       = $params['chat_game_price'];

            $row->validate(
                [
                    'chat_game_content'     => 'require',
                    'chat_game_category_id' => 'require',
                    'chat_game_price'       => 'require',
                ],
                [
                    'chat_game_content.require'     => __('Parameter %s can not be empty', [ 'live_gift_name' ]),
                    'chat_game_category_id.require' => __('Parameter %s can not be empty', [ 'live_gift_detail' ]),
                    'chat_game_price.require'       => __('Parameter %s can not be empty', [ 'live_gift_coin' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $oChatGameCategory = ChatGameCategoryModel::all();
        $this->view->assign("row_category", $oChatGameCategory);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {
        $row = ChatGameConfigModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params                     = $this->request->param('row/a');
            $row->chat_game_content     = $params['chat_game_content'];
            $row->chat_game_category_id = $params['chat_game_category_id'];
            $row->chat_game_price       = $params['chat_game_price'];

            $row->validate(
                [
                    'chat_game_content'     => 'require',
                    'chat_game_category_id' => 'require',
                    'chat_game_price'       => 'require',
                ],
                [
                    'chat_game_content.require'     => __('Parameter %s can not be empty', [ 'live_gift_name' ]),
                    'chat_game_category_id.require' => __('Parameter %s can not be empty', [ 'live_gift_detail' ]),
                    'chat_game_price.require'       => __('Parameter %s can not be empty', [ 'live_gift_coin' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $oChatGameCategory = ChatGameCategoryModel::all();
        $this->view->assign("row_category", $oChatGameCategory);
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
        ChatGameConfigModel::where('id', 'in', $ids)->delete();
        UserChatGameModel::where('chat_game_id', 'in', $ids)->delete();
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