<?php

namespace app\live\controller\general;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\AppVersion as AppVersionModel;

/**
 * APP版本管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Appversion extends Backend
{
    /**
     * index 当前最新版本
     */
    public function index()
    {

        if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $ids[0] = AppVersionModel::where('app_version_os', 'ios')->order('app_version_code desc')->value('app_version_id', 0);
            $ids[1] = AppVersionModel::where('app_version_os', 'android')->order('app_version_code desc')->value('app_version_id', 0);

            $oSelectQuery = AppVersionModel::where('app_version_id', 'in', $ids);
            $oTotalQuery  = AppVersionModel::where('app_version_id', 'in', $ids);

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('app_version_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('app_version_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('app_version_content', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('app_version_content', 'LIKE', '%'.$sKeyword.'%');
                }
                
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->count();
            $list  = $oSelectQuery->order('app_version_code '.$sOrder)->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * history 历史记录
     */
    public function history()
    {
        if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = AppVersionModel::where('1=1');
            $oTotalQuery  = AppVersionModel::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('app_version_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('app_version_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('app_version_content', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('app_version_content', 'LIKE', '%'.$sKeyword.'%');
                }
                
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->count();
            $list  = $oSelectQuery->order('app_version_code '.$sOrder)->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
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

    }

    /**
     * add 添加
     */
    public function add()
    {

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row = new AppVersionModel();

            $row->app_version_os           = $params['app_version_os'];
            $row->app_version_code         = $params['app_version_code'];
            $row->app_version_name         = $params['app_version_name'];
            $row->app_version_content      = $params['app_version_content'];
            $row->app_version_is_force     = $params['app_version_is_force'];
            $row->app_version_download_url = $params['app_version_download_url'];

            $row->validate(
                [
                    'app_version_os'           => 'require',
                    'app_version_code'         => 'require',
                    'app_version_name'         => 'require',
                    'app_version_is_force'     => 'require',
                    'app_version_download_url' => 'require',
                ],
                [
                    'app_version_os.require'           =>  __('Parameter %s can not be empty', ['app_version_os']),
                    'app_version_code.require'         =>  __('Parameter %s can not be empty', ['app_version_code']),
                    'app_version_name.require'         =>  __('Parameter %s can not be empty', ['app_version_name']),
                    'app_version_is_force.require'     =>  __('Parameter %s can not be empty', ['app_version_is_force']),
                    'app_version_download_url.require' =>  __('Parameter %s can not be empty', ['app_version_download_url']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            }

            $this->success();
        }

        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids='')
    {
        $row = AppVersionModel::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');
            
            $row->app_version_os           = $params['app_version_os'];
            $row->app_version_name         = $params['app_version_name'];
            $row->app_version_content      = $params['app_version_content'];
            $row->app_version_is_force     = $params['app_version_is_force'];
            $row->app_version_download_url = $params['app_version_download_url'];

            $row->validate(
                [
                    'app_version_os'           => 'require',
                    'app_version_name'         => 'require',
                    'app_version_is_force'     => 'require',
                    'app_version_download_url' => 'require',
                ],
                [
                    'app_version_os.require'           =>  __('Parameter %s can not be empty', ['app_version_os']),
                    'app_version_name.require'         =>  __('Parameter %s can not be empty', ['app_version_name']),
                    'app_version_is_force.require'     =>  __('Parameter %s can not be empty', ['app_version_is_force']),
                    'app_version_download_url.require' =>  __('Parameter %s can not be empty', ['app_version_download_url']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            }

            $this->success();
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();

    }

    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids='')
    {
        
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids='')
    {

    }
}