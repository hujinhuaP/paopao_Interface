<?php

namespace app\live\controller\document;

use app\common\controller\Backend;
use app\live\model\live\CustomerServiceReply;
use think\Exception;


/**
 * 客服自动回复
 *
 */
class Autoreply extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.customer_service_reply');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('reply_flg');
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
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids = '')
    {
    }

    /**
     * add 添加
     */
    public function add()
    {
        if ( $this->request->isPost() ) {
            $params         = $this->request->param('row/a');
            $row            = new CustomerServiceReply();
            $row->type      = 'reply';
            $row->content   = $params['content'];
            $row->reply_flg = $params['reply_flg'];
            $existModel     = CustomerServiceReply::get([
                'type'      => $row->type,
                'reply_flg' => $row->reply_flg
            ]);
            if ( $existModel ) {
                $this->error('已有相同的匹配内容，不能重复添加');
            }
            $row->validate(
                [
                    'content'   => 'require',
                    'reply_flg' => 'require',
                ],
                [
                    'content.require'   => __('Parameter %s can not be empty', [ 'content' ]),
                    'reply_flg.require' => __('Parameter %s can not be empty', [ '匹配内容' ]),
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
        $row = CustomerServiceReply::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row->content   = $params['content'];
            $row->reply_flg = $params['reply_flg'];
            $existModel     = CustomerServiceReply::get([
                'type'      => $row->type,
                'reply_flg' => $row->reply_flg
            ]);
            if ( $existModel && $existModel->id != $row->id ) {
                $this->error('已有相同的匹配内容，不能重复添加');
            }
            $row->validate(
                [
                    'content'   => 'require',
                    'reply_flg' => 'require',
                ],
                [
                    'content.require'   => __('Parameter %s can not be empty', [ 'content' ]),
                    'reply_flg.require' => __('Parameter %s can not be empty', [ '匹配内容' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $aCategory = [
            'first'   => '半小时内首次发消息',
            'reply'   => '匹配回复',
            'unmatch' => '未匹配到内容',
        ];
        $this->view->assign("row", $row);
        $this->view->assign("category", $aCategory[$row->type]);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {
        CustomerServiceReply::where('id', 'in', $ids)->where("type = 'reply'")->delete();
        return $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }

}