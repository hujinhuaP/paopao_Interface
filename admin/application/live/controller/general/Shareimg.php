<?php

namespace app\live\controller\general;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\UserShareBaseImage as UserShareBaseImageModel;

/**
 * 分享背景图管理
 *
 * @property \app\live\model\live\UserShareBaseImage model
 */
class Shareimg extends Backend
{

    public function _initialize() {
        parent::_initialize();
        $this->model = model('live.UserShareBaseImage');
    }
    /**
     * index 列表
     */
    public function index() {
        if ($this->request->isAjax()) {
            //设置过滤方法
            $this->request->filter(['strip_tags']);
            if ($this->request->isAjax()) {
                list($where, $sort, $order, $offset, $limit) = $this->buildparams('title');
                $total  = $this->model->where($where)->order($sort, $order)->count();
                $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
                $result = [
                    "total" => $total,
                    "rows"  => $list
                ];
                return json($result);
            }
        }
        return $this->view->fetch();
    }


    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids='')
    {
        //$row = UserShareBaseImageModel::get($ids);
        //
        //if (!$row)
        //    $this->error(__('No Results were found'));
        //
        //$this->view->assign("row", $row);
        //return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {
        $upload_url = config('upload.uploadurl_local_img');
        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row = new $this->model;

            $row->title         = $params['title'];
            $row->img_url = $params['img_url'];

            $row->validate(
                [
                    'title'         => 'require',
                    'img_url' => 'require',
                ],
                [
                    'title.require'         =>  __('Parameter %s can not be empty', [__('Title')]),
                    'img_url.require' =>  __('Parameter %s can not be empty', [__('Image')]),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign("upload_url", $upload_url);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids='')
    {
        $row = UserShareBaseImageModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $upload_url = config('upload.uploadurl_local_img');

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row->title         = $params['title'];
            $row->img_url = $params['img_url'];

            $row->validate(
                [
                    'title'         => 'require',
                    'img_url' => 'require',
                ],
                [
                    'title.require'         =>  __('Parameter %s can not be empty', [__('Title')]),
                    'img_url.require' =>  __('Parameter %s can not be empty', [__('Image')]),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        $this->view->assign("upload_url", $upload_url);
        return $this->view->fetch();
    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids='')
    {
        UserShareBaseImageModel::where('id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids='')
    {

    }

    /**
     * sort 排序
     */
    public function sort()
    {
        //if ($this->request->isPost()) {
        //    $ids      = $this->request->post('ids');
        //    $changeid = $this->request->post("changeid");
        //    $orderway = $this->request->post("orderway", 'strtolower');
        //    $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
        //    $ids = explode(',', $ids);
        //
        //    if ($orderway == 'DESC') {
        //        $ids = array_reverse($ids);
        //    }
        //
        //    foreach ($ids as $k=>$id)
        //    {
        //        UserShareBaseImageModel::where('carousel_id', $id)->update(['carousel_sort' => $k + 1, 'carousel_update_time'=>time()]);
        //    }
        //    $this->success();
        //}
        //$this->error();
    }

    /**
     * status 修改状态
     *
     * @param  string $ids
     */
    public function status($ids='')
    {
        $row = UserShareBaseImageModel::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('params');
            $params = explode('=', $params);
            $row[$params[0]] = $params[1];
            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }
}