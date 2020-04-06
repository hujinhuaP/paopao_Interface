<?php

namespace app\live\controller\posts;

use app\live\model\live\ShortPosts;
use app\live\model\live\ShortPostsComment;
use app\live\model\live\ShortPostsCommentDelete;
use app\live\model\live\ShortPostsCommentReply;
use app\live\model\live\ShortPostsCommentReplyDelete;
use app\live\model\live\ShortPostsMessage;
use think\Config;
use think\Exception;
use app\common\controller\Backend;

/**
 * 动态评论管理
 *
 */
class Comment extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.short_posts_comment');
    }

    /**
     * index 列表
     */
    public function index($ids = '')
    {
        if ( $this->request->isAjax() ) {
            $whereStr = '1=1';
            if ( $ids ) {
                $whereStr = 'short_posts_id = ' . intval($ids);
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where($whereStr)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where($whereStr)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign('short_posts_id', $ids);
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     * 动态详情
     * 获取动态的用户信息 和动态内容
     */
    public function detail($ids = '')
    {
        $row = ShortPostsComment::where('comment_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        $this->view->assign([
            'row' => $row,
        ]);
        return $this->view->fetch();
    }

    /**
     * @param string $ids
     */
    public function edit($ids = '')
    {
        $row = ShortPostsComment::where('comment_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $oldStatus                 = $row->comment_status;
            $params                    = $this->request->param('row/a');
            $row->comment_status       = $params['comment_status'];
            $row->comment_check_remark = $params['comment_check_remark'];
            $row->comment_check_time   = time();
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            if ( $oldStatus == 'Y' && $row->comment_status != 'Y' ) {
                $oShortPostsRow = ShortPosts::where('short_posts_id', $row->short_posts_id)->find();
                if ( $oShortPostsRow ) {
                    $oShortPostsRow->short_posts_comment_num -= 1;
                    $oShortPostsRow->short_posts_update_time = time();
                    if ( !$oShortPostsRow->save() ) {
                        $this->error($oShortPostsRow->getError());
                    }
                }
            } else if ( $oldStatus != 'Y' && $row->comment_status == 'Y' ) {
                $oShortPostsRow = ShortPosts::where('short_posts_id', $row->short_posts_id)->find();
                if ( $oShortPostsRow ) {
                    $oShortPostsRow->short_posts_comment_num += 1;
                    $oShortPostsRow->short_posts_update_time = time();
                    if ( !$oShortPostsRow->save() ) {
                        $this->error($oShortPostsRow->getError());
                    }
                }
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
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {
        $row = ShortPostsComment::where('comment_id', $ids)->find();
        if ( !$row )
            $this->error(__('No Results were found'));
        $deleteRemark              = $this->request->post('remark');
        $row->comment_check_remark = $deleteRemark;
        $deleteRow                 = new ShortPostsCommentDelete();
        $deleteRow->startTrans();
        if ( !$deleteRow->save($row->toArray()) ) {
            $deleteRow->rollback();
            $this->error($deleteRow->getError());
        }
        if ( !$row->delete() ) {
            $deleteRow->rollback();
            $this->error($row->getError());
        }

        $oShortPostsCommentReply = ShortPostsCommentReply::where([
            'short_posts_id' => $row->short_posts_id,
            'is_comment'     => 'Y'
        ])->find();
        if ( $oShortPostsCommentReply ) {
            $oShortPostsCommentReply->reply_check_remark = $deleteRemark;
            $deleteReplyRow                              = new ShortPostsCommentReplyDelete();
            if ( !$deleteReplyRow->save($oShortPostsCommentReply->toArray()) ) {
                $deleteRow->rollback();
                $this->error($deleteReplyRow->getError());
            }
            if ( !$oShortPostsCommentReply->delete() ) {
                $deleteRow->rollback();
                $this->error($oShortPostsCommentReply->getError());
            }
        }

        // 如果为审核通过的 则减少未读数
        if ( $row->comment_status == 'Y' ) {
            $oShortPostsRow = ShortPosts::where('short_posts_id', $row->short_posts_id)->find();
            if ( $oShortPostsRow ) {
                $oShortPostsRow->short_posts_comment_num -= 1;
                $oShortPostsRow->short_posts_update_time = time();
                if ( !$oShortPostsRow->save() ) {
                    $deleteRow->rollback();
                    $this->error($oShortPostsRow->getError());
                }
            }
        }

        // 发送动态消息
        $oShortPostsMessage                       = new ShortPostsMessage();
        $oShortPostsMessage->short_posts_id       = $row->short_posts_id;
        $oShortPostsMessage->message_type         = ShortPostsMessage::MESSAGE_TYPE_REPLY_DELETE;
        $oShortPostsMessage->user_id              = $row->user_id;
        $oShortPostsMessage->message_content      = sprintf('你在社区的动态评论中违反规则 【%s】,相关信息已被清除，请遵守规则，屡次违反规则系统将会作出相应惩罚、封号等措施', $deleteRemark);
        $oShortPostsMessage->message_target_extra = serialize([
            'extra_content' => $oShortPostsCommentReply->reply_content,
            'extra_time'    => $oShortPostsCommentReply->create_time,
        ]);
        $oShortPostsMessage->save();
        file_get_contents(sprintf('%s/im/notify?%s', Config::get('api_url'), http_build_query([
            'uid'  => $oShortPostsMessage->user_id,
            'rid'  => 0,
            'type' => 'posts_message',
            'msg'  => json_encode([
                'type' => 'posts_message',
                'data' => (object)[],
            ], JSON_UNESCAPED_UNICODE)
        ])));

        $deleteRow->commit();
        $this->success();
    }


}