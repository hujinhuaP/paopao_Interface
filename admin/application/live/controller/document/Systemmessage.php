<?php

namespace app\live\controller\document;

use think\Exception;
use app\common\controller\Backend;
use app\live\library\TaskQueueService;

use app\live\model\live\SystemMessage as SystemMessageModel;

/**
 * 公告管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Systemmessage extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = SystemMessageModel::where("system_message_is_admin='Y'");
            $oTotalQuery  = SystemMessageModel::where("system_message_is_admin='Y'");

            if ( $sKeyword ) {
                $oSelectQuery->where('system_message_title', 'LIKE', '%' . $sKeyword . '%');
                $oTotalQuery->where('system_message_title', 'LIKE', '%' . $sKeyword . '%');
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
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->count();
            $list  = $oSelectQuery->order('system_message_id desc')->select();

            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids = '')
    {
        $row = SystemMessageModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        $row->system_message_content = json_decode($row->system_message_content, 1);

        switch ( $row->system_message_push_type ) {
            case 1:
                $row->system_message_push_type = __('Select user');
                break;
            case 2:
                $row->system_message_push_type = __('All anchor');
                break;
            case 0 :
            default :
                $row->system_message_push_type = __('All user');
                break;
        }

        switch ( $row->system_message_type ) {
            case 'certification':
                $row->system_message_type = __('Certification');
                break;
            case 'follow':
                $row->system_message_type = __('Follow');
                break;
            case 'withdraw' :
                $row->system_message_type = __('Withdraw');
                break;
            case 'general':
            default :
                $row->system_message_type = __('General');
                break;
        }

        switch ( $row->system_message_status ) {
            case 'Y':
                $row->system_message_status = __('Finish');
                break;
            case 'S':
                $row->system_message_status = __('Sending');
                break;
            case 'N' :
            default :
                $row->system_message_status = __('Wait');
                break;
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {

        if ( $this->request->isPost() ) {
            $params                        = $this->request->param('row/a');
            $row                           = new SystemMessageModel();
            $row->user_id                  = isset($params['user_id']) ? trim($params['user_id']) : '';
            $row->system_message_title     = mb_substr($params['system_message_content'], 0, 100);
            $row->system_message_type      = 'general';
            $row->system_message_push_type = $params['system_message_push_type'];
            $row->system_message_url       = $params['system_message_url'];
            $row->system_message_is_admin  = 'Y';
            $row->system_message_content   = json_encode([
                'type' => 'general',
                'data' => [
                    'content' => $params['system_message_content'],
                    'url'     => $params['system_message_url']
                ]
            ]);

            $row->system_message_status = 'N';

            $row->validate(
                [
                    'system_message_content'   => 'require',
                    'system_message_push_type' => 'require',
                ],
                [
                    'system_message_push_type.require' => __('Parameter %s can not be empty', [ 'system_message_push_type' ]),
                    'system_message_content.require'   => __('Parameter %s can not be empty', [ 'system_message_content' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $oTaskQueueService = new TaskQueueService();
                $oTaskQueueService->enQueue([
                    'task'   => 'systemmessage',
                    'action' => 'push',
                    'param'  => [
                        'system_message_id' => $row->system_message_id,
                    ],
                ]);
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
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }
}