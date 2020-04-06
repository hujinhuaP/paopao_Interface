<?php

namespace app\live\controller\anchor;

use app\live\model\live\UserPrivateChatLog;
use think\Config;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Anchor as AnchorModel;

/**
 * 私聊主播管理
 * @Authors yeah_lsj@yeah.net
 */
class Chatanchor extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword     = $this->request->param('search');
            $nOffset      = $this->request->param('offset');
            $nLimit       = $this->request->param('limit');
            $aFilter      = json_decode($this->request->param('filter'), 1);
            $aOp          = json_decode($this->request->param('op'), 1);
            $oSelectQuery = AnchorModel::where('1=1 and a.anchor_chat_status > 0');
            $oTotalQuery  = AnchorModel::where('1=1 and a.anchor_chat_status > 0');
            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%' . $sKeyword . '%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
                }
            }
            if ( $aFilter ) {
                foreach ( $aFilter as $key => $value ) {
                    if ( stripos($aOp[$key], 'LIKE') !== FALSE ) {
                        $value     = str_replace([
                            'LIKE ',
                            '...'
                        ], [
                            '',
                            $value
                        ], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ( $key ) {

                        case 'anchor_chat_status':
                            $oSelectQuery->where('a.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('a.' . $key, $aOp[$key], $value);
                            if ( $value == 3 ) {
                                $oSelectQuery->where('a.' . $key, '<>', 2);
                                $oTotalQuery->where('a.' . $key, '<>', 2);
                            }
                            break;
                        case 'anchor_private_forbidden':
                            $oSelectQuery->where('a.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('a.' . $key, $aOp[$key], $value);
                            break;
                        default:
                            $oSelectQuery->where('u.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('u.' . $key, $aOp[$key], $value);
                            break;
                    }
                }
            }
            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }
            $total = $oTotalQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->count();
            $list  = $oSelectQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->order('u.user_id desc')->select();
            foreach ( $list as &$v ) {
                $v['user_coin']          = sprintf('%.2f', $v['user_coin']);
                $v['user_consume_total'] = sprintf('%.2f', $v['user_consume_total']);
                $v['user_dot']           = sprintf('%.2f', $v['user_dot']);
                $v['user_collect_total'] = sprintf('%.2f', $v['user_collect_total']);
            }
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
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {

    }

    /**
     *
     */
    public function forbidden($ids = '')
    {
        $row = AnchorModel::where('user_id', $ids)->find();

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->post('params');
            switch ( $params ) {
                case 1:
                    $row->anchor_private_forbidden = 1;
                    break;
                case 0:
                default:
                    $row->anchor_private_forbidden = 0;
                    break;
            }
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                if ( $params == 1 ) {
                    if ( $row->anchor_chat_status == 2 ) {
                        $chat_log = UserPrivateChatLog::where("chat_log_anchor_user_id={$ids} and status = 4")->find();
                        if ( $chat_log ) {
                            file_get_contents(sprintf('%s/live/anchor/hangupChat?%s', Config::get('api_url'), http_build_query([
                                'uid'          => $ids,
                                'chat_log'     => $chat_log->id,
                                'type'         => 1,
                                'hang_up_type' => 'auto',
                                'debug'        => 1,
                                'cli_api_key'  => Config::get('cli_api_key'),
                                'detail'       => '后台主播禁播'
                            ])));
                        }
                    }
                }
                $this->success();
            }
        }
    }


}