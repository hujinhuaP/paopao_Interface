<?php

namespace app\live\controller\posts;

use app\live\model\live\ShortPosts;
use app\live\model\live\ShortPostsComment;
use app\live\model\live\ShortPostsCommentDelete;
use app\live\model\live\ShortPostsCommentReply;
use app\live\model\live\ShortPostsCommentReplyDelete;
use app\live\model\live\ShortPostsMessage;
use app\live\model\live\User;
use think\Config;
use think\Exception;
use app\common\controller\Backend;

/**
 * 违规评论
 *
 */
class Replyillegal extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.short_posts_comment_reply');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $whereStr = 'reply_status = "N"';
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->where($whereStr)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where($whereStr)->order($sort, $order)->limit($offset, $limit)->select();
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
     */
    public function edit($ids = '')
    {
        $row = ShortPostsCommentReply::where('reply_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $oldStatus = $row->reply_status;
            $oldIsAutoRefuse = $row->is_auto_refuse;
            $params                  = $this->request->param('row/a');
            $row->reply_status       = $params['reply_status'];
            $row->reply_check_remark = $params['reply_check_remark'];
            $row->reply_check_time   = time();
            if($oldStatus == 'N' && $row->reply_status == 'Y'){
                $row->is_auto_refuse = 'N';
            }
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            if($oldStatus == 'N' && $row->reply_status == 'Y'){
                $oShortPostsCommentRow = ShortPostsComment::where('comment_id', $row->comment_id)->find();
                if ( $oShortPostsCommentRow ){
                    if($row->is_comment == 'Y'){
                        $oShortPostsCommentRow->comment_status = 'Y';
                    }
                    $oShortPostsCommentRow->reply_num += 1;
                    if(!$oShortPostsCommentRow->save()){
                        $this->error($oShortPostsCommentRow->getError());
                    }

                    if($oldIsAutoRefuse == 'Y'){

                        $messageUserId = $oShortPostsCommentRow->user_id;
                        if($row->is_comment == 'Y'){
                            // 本条是评论 则取动态的用户id
                            $oShortPostsRow = ShortPosts::where('short_posts_id', $row->short_posts_id)->find();
                            $messageUserId = $oShortPostsRow->short_posts_user_id;

                        }
                        // 发送动态消息
                        $oShortPostsMessage                      = new ShortPostsMessage();
                        $oShortPostsMessage->short_posts_id      = $row->short_posts_id;
                        $oShortPostsMessage->message_type        = $row->is_comment == 'N' ? ShortPostsMessage::MESSAGE_TYPE_REPLY : ShortPostsMessage::MESSAGE_TYPE_COMMENT;
                        $oShortPostsMessage->user_id             = $messageUserId;
                        $oShortPostsMessage->message_content     = $row->reply_content;
                        $oShortPostsMessage->message_target_extra = serialize([
                            'comment_id'    => $oShortPostsCommentRow->comment_id,
                            'extra_content' => $row->reply_content
                        ]);
                        $oShortPostsMessage->save();
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
        $row = ShortPostsCommentReply::where('reply_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));
        $deleteRemark            = $this->request->post('remark');
        $row->reply_check_remark = $deleteRemark;
        $deleteReplyRow          = new ShortPostsCommentReplyDelete();
        $deleteReplyRow->startTrans();
        if ( !$deleteReplyRow->save($row->toArray()) ) {
            $deleteReplyRow->rollback();
            $this->error($deleteReplyRow->getError());
        }
        if ( !$row->delete() ) {
            $deleteReplyRow->rollback();
            $this->error($row->getError());
        }
        // 如果为审核通过的 则减少未读数
        $oShortPostsCommentRow = ShortPostsComment::where('comment_id', $row->comment_id)->find();
        if ( $row->reply_status == 'Y' ) {
            if ( $oShortPostsCommentRow && $row->is_comment == 'N') {
                $oShortPostsCommentRow->reply_num -= 1;
                if ( !$oShortPostsCommentRow->save() ) {
                    $this->error($oShortPostsCommentRow->getError());
                }
            }
        }
        if($row->is_comment == 'Y'){
            $deleteRow                 = new ShortPostsCommentDelete();
            if ( !$deleteRow->save($oShortPostsCommentRow->toArray()) ) {
                $deleteReplyRow->rollback();
                $this->error($deleteRow->getError());
            }
            if ( !$oShortPostsCommentRow->delete() ) {
                $deleteReplyRow->rollback();
                $this->error($row->getError());
            }
            if ( $oShortPostsCommentRow->comment_status == 'Y' ) {
                $oShortPostsRow = ShortPosts::where('short_posts_id', $row->short_posts_id)->find();
                if ( $oShortPostsRow ) {
                    $oShortPostsRow->short_posts_comment_num -= 1;
                    $oShortPostsRow->short_posts_update_time = time();
                    if ( !$oShortPostsRow->save() ) {
                        $deleteReplyRow->rollback();
                        $this->error($oShortPostsRow->getError());
                    }
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
            'extra_content' => $row->reply_content,
            'extra_time'    => $row->create_time,
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
        $deleteReplyRow->commit();
        $this->success();
    }


}