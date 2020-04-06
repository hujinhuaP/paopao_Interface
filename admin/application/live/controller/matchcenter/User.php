<?php

namespace app\live\controller\matchcenter;

use app\common\controller\Backend;
use app\live\model\live\Anchor;
use app\live\model\live\UserPrivateChatLog;


/**
 * 公会列表
 */
class User extends Backend {
    public function _initialize() {
        parent::_initialize();
    }

    /**
     * matching 匹配中
     * 从redis中取出所有用户
     */
    public function matching() {

        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $oUserPrivateChatLog = new UserPrivateChatLog();
            $total  = $oUserPrivateChatLog->getMatchingUserCount();
            $list   = $oUserPrivateChatLog->getMatchingUser($offset,$limit);

            $result = [
                "total" => $total,
                "rows"  => $list,
                'chat_count' => Anchor::where('anchor_chat_status',2)->join('user','user.user_id = anchor.user_id')->where("user.user_is_isrobot = 'N'")->count(),
                'free_count' =>  Anchor::where('anchor_chat_status',3)->count(),
            ];
            return json($result);
        }

        return $this->view->fetch();
    }


    /**
     * add 添加
     */
    public function add() {
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '') {
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '') {
    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids = '') {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort() {
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '') {
    }
}