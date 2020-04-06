<?php

namespace app\admin\controller\expand;

use app\admin\model\api\UserShareBaseImage;
use app\common\controller\Backend;
use think\Session;
use app\admin\model\api\AgentUrl as AgentUrlModel;

/**
 * 推广地址管理
 *
 * @icon fa fa-user
 */
class Share extends Backend {

    public function _initialize() {
        parent::_initialize();
        $this->model = model('api.AgentUrl');
    }

    /**
     * 查看
     */
    public function index() {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('title');
            $total  = $this->model->where($where)->where('agent_id', 'eq', $this->auth->id)->order($sort, $order)->count();
            $list   = $this->model->where($where)->where('agent_id', 'eq', $this->auth->id)->order($sort, $order)->limit($offset, $limit)->select();
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
    public function add() {
        if ($this->request->isPost()) {
            $params         = $this->request->param('row/a');
            $row            = new AgentUrlModel();
            $row->title     = $params['title'];
            $row->short_url = $params['short_url'];
            $row->agent_id  = $this->auth->id;
            $row->validate([
                'title'     => 'require',
                'short_url' => 'require',
            ], [
                'title.require'     => __('Parameter %s can not be empty', [__('Title')]),
                'short_url.require' => __('Parameter %s can not be empty', [__('Short url')]),
            ]);
            if (!$row->save($row->getData())) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign('random_str',createNoncestr(10));
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '') {

        $row = AgentUrlModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        if ($this->request->isPost()) {
            $params = $this->request->param('row/a');
            $row->title     = $params['title'];
            $row->agent_id  = $this->auth->id;
            $row->validate([
                'title'     => 'require',
            ], [
                'title.require'     => __('Parameter %s can not be empty', [__('Title')]),
            ]);
            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids = '') {
        //$row = AgentUrlModel::get($ids);
        //if (!$row)
        //    $this->error(__('No Results were found'));
        //if ($this->request->isPost()) {
        //    $params          = $this->request->param('params');
        //    $params          = explode('=', $params);
        //    $row[$params[0]] = $params[1];
        //    if ($row->save() === false) {
        //        $this->error($row->getError());
        //    } else {
        //        $this->success();
        //    }
        //}
    }

    /**
     * 分享图片
     */
    public function show_img($ids = null) {
        $row = AgentUrlModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));
        $oUserShareBaseImage = UserShareBaseImage::where("status='Y'")->select();
        $this->view->assign("oUserShareBaseImage", $oUserShareBaseImage);
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


}
