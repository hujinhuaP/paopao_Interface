<?php

namespace app\live\controller\chatgame;

use app\live\library\Redis;
use app\live\model\live\ChatGameConfig as ChatGameConfigModel;
use app\live\model\live\ChatGameCategory as ChatGameCategoryModel;
use app\live\model\live\UserChatGame as UserChatGameModel;
use think\Exception;
use app\common\controller\Backend;

/**
 * 聊天游戏记录
 */
class Log extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.chat_game_log');
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
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {
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
    }

    /**
     * sort 排序
     */
    public function sort()
    {

    }


}