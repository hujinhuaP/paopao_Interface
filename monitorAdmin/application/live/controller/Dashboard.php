<?php

namespace app\live\controller;

use app\common\controller\Backend;
use app\live\model\live\UserPrivateChatLog;
use think\Config;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.UserPrivateChatLog');
    }

    /**
     * 监控
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $current_time = time();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null, TRUE);
            $total = $this->model::where('user_private_chat_log.status = 4')->where($where)->with('User,AnchorUser')->join('user anchor_user', 'anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->order($sort, $order)->count();
            $list  = $this->model::where('user_private_chat_log.status = 4')->where($where)->with('User,AnchorUser')->join('user anchor_user', 'anchor_user.user_id = user_private_chat_log.chat_log_anchor_user_id')->field("'$current_time' as php_current_time")->order($sort, $order)->limit($offset, $limit)->select();
            $data  = [];
            foreach ( $list as $item ) {
                $tmp                     = $item->toArray();
                $tmp['user_stream_id']   = md5($tmp['id'] . '_' . $tmp['user']['user_id'] . '_main');
                $tmp['anchor_stream_id'] = md5($tmp['id'] . '_' . $tmp['anchor_user']['user_id'] . '_main');
                $data[]                  = $tmp;
            }
            $result = [
                "total" => $total,
                "rows"  => $data
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 发送房间通知
     */
    public function waringlive($ids = null)
    {
        $row = UserPrivateChatLog::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isAjax() ) {
            if ( $row->status == 4 ) {
                $params = $this->request->param('row/a');

                $content = $params['content'];

                switch ( $params['get_user_flg'] ) {
                    case 1:
                        $data = file_get_contents(sprintf('%s/im/sendNotifyRoom?%s', Config::get('api_url'), http_build_query([
                            'content' => $content,
                            'user_id' => $row['chat_log_anchor_user_id'],
                        ])));
                        break;
                    case 2:
                        $data = file_get_contents(sprintf('%s/im/sendNotifyRoom?%s', Config::get('api_url'), http_build_query([
                            'content' => $content,
                            'user_id' => $row['chat_log_user_id'],
                        ])));
                        break;
                    case 0:
                    default:
                        $data = file_get_contents(sprintf('%s/im/sendBatch?%s', Config::get('api_url'), http_build_query([
                            'content'  => $content,
                            'user_arr' => [
                                $row['chat_log_user_id'],
                                $row['chat_log_anchor_user_id'],
                            ],
                        ])));

                }
                $this->success();
            }
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }

    /**
     * 停播
     */
    public function disablelive($ids = null)
    {
        $row = UserPrivateChatLog::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $row->status == 4 ) {

            file_get_contents(sprintf('%s/live/anchor/hangupChat?%s', Config::get('api_url'), http_build_query([
                'uid'          => $row->chat_log_anchor_user_id,
                'chat_log'     => $row->id,
                'type'         => 1,
                'hang_up_type' => 'auto',
                'detail'       => '后台停播',
                'debug'        => 1,
                'cli_api_key'  => Config::get('cli_api_key'),
            ])));
        }
        $this->success();
    }

}
