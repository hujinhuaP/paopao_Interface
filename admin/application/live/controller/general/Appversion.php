<?php

namespace app\live\controller\general;

use app\live\model\live\AppList;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\AppVersion as AppVersionModel;

/**
 * APP版本管理
 *
 */
class Appversion extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.app_version');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $whereStr = 'is_current = "Y"';
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $total = $this->model->where($where)->where($whereStr)->order($sort, $order)->count();
            $list  = $this->model->where($where)->where($whereStr)->order($sort, $order)->limit($offset, $limit)->select();

            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $oAppList = AppList::all();
        $this->view->assign("appList", $oAppList);
        return $this->view->fetch();
    }

    /**
     * index 当前最新版本
     */
    public function indexBack()
    {

        if ( $this->request->isAjax() ) {
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

            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('app_version_id', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('app_version_id', 'LIKE', '%' . $sKeyword . '%');
                } else {
                    $oSelectQuery->where('app_version_content', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('app_version_content', 'LIKE', '%' . $sKeyword . '%');
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

            $total  = $oTotalQuery->count();
            $list   = $oSelectQuery->order('app_version_code ' . $sOrder)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * history 历史记录
     */
    public function history()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = AppVersionModel::where('1=1');
            $oTotalQuery  = AppVersionModel::where('1=1');

            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('app_version_id', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('app_version_id', 'LIKE', '%' . $sKeyword . '%');
                } else {
                    $oSelectQuery->where('app_version_content', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('app_version_content', 'LIKE', '%' . $sKeyword . '%');
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

            $total  = $oTotalQuery->count();
            $list   = $oSelectQuery->order('app_version_code ' . $sOrder)->select();
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
            $params = $this->request->param('row/a');

            $row = new AppVersionModel();

            $row->app_version_os           = $params['app_version_os'];
            $row->app_version_code         = $params['app_version_code'];
            $row->app_version_name         = $params['app_version_name'];
            $row->app_version_content      = $params['app_version_content'];
            $row->app_version_is_force     = $params['app_version_is_force'];
            $row->app_version_download_url = $params['app_version_download_url'];
            $row->app_id                   = $params['app_id'];
            $row->is_current               = 'Y';

            $row->validate(
                [
                    'app_version_os'           => 'require',
                    'app_version_code'         => 'require',
                    'app_version_name'         => 'require',
                    'app_version_is_force'     => 'require',
                    'app_version_download_url' => 'require',
                    'app_id'                   => 'require',
                ],
                [
                    'app_version_os.require'           => __('Parameter %s can not be empty', [ 'app_version_os' ]),
                    'app_version_code.require'         => __('Parameter %s can not be empty', [ 'app_version_code' ]),
                    'app_version_name.require'         => __('Parameter %s can not be empty', [ 'app_version_name' ]),
                    'app_version_is_force.require'     => __('Parameter %s can not be empty', [ 'app_version_is_force' ]),
                    'app_version_download_url.require' => __('Parameter %s can not be empty', [ 'app_version_download_url' ]),
                    'app_id.require'                   => __('Parameter %s can not be empty', [ 'APP名称' ]),
                ]
            );
            $row->startTrans();

            // 将之前的相同APP， 相同设备的当前版本值设为N
            $sql = "update app_version set is_current = 'N' WHERE app_version_os = '{$params['app_version_os']}' AND app_id = {$params['app_id']}";
            $row->execute($sql);
            if ( $row->save($row->getData()) === FALSE ) {
                $row->rollback();
                $this->error($row->getError());
            }
            $row->commit();

            $this->success();
        }

        $oAppList = AppList::all();
        $this->view->assign("appList", $oAppList);
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {
        $row = AppVersionModel::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
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
                    'app_version_os.require'           => __('Parameter %s can not be empty', [ 'app_version_os' ]),
                    'app_version_name.require'         => __('Parameter %s can not be empty', [ 'app_version_name' ]),
                    'app_version_is_force.require'     => __('Parameter %s can not be empty', [ 'app_version_is_force' ]),
                    'app_version_download_url.require' => __('Parameter %s can not be empty', [ 'app_version_download_url' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }

            $this->success();
        }


        $oAppList = AppList::all();
        $this->view->assign("appList", $oAppList);
        $this->view->assign("row", $row);
        return $this->view->fetch();

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

    public function addmore()
    {

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row = new AppVersionModel();

            if (
                empty($params['app_version_os']) || empty($params['app_version_code']) ||
                empty($params['app_version_name']) || empty($params['app_version_content'])
            ) {
                $this->error('参数不全');
            }
            $downloadUrlArr = $this->request->post('download/a');
            $saveAll        = [];
            $appIds         = [];
            foreach ( $downloadUrlArr as $appId => $item ) {
                if ( $item ) {
                    $appIds[]  = $appId;
                    $saveAll[] = [
                        'app_version_os'           => $params['app_version_os'],
                        'app_version_code'         => $params['app_version_code'],
                        'app_version_name'         => $params['app_version_name'],
                        'app_version_content'      => $params['app_version_content'],
                        'app_version_is_force'     => $params['app_version_is_force'],
                        'app_id'                   => $appId,
                        'app_version_download_url' => $item,
                        'is_current'               => 'Y',
                        'app_version_create_time'  => time(),
                        'app_version_update_time'  => time(),
                    ];
                }
            }

            $appids_str = implode(',', $appIds);
            $row->startTrans();

            // 将之前的相同APP， 相同设备的当前版本值设为N
            $sql = "update app_version set is_current = 'N' WHERE app_version_os = '{$params['app_version_os']}' AND app_id in ({$appids_str})";
            $row->execute($sql);
            if ( $row->saveAll($saveAll) === FALSE ) {
                $row->rollback();
                $this->error($row->getError());
            }
            $row->commit();

            $this->success();
        }

        $oAppList = AppList::all();
        $this->view->assign("appList", $oAppList);
        return $this->view->fetch();
    }
}