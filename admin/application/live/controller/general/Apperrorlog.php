<?php
namespace app\live\controller\general;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\AppErrorLog as AppErrorLogModel;

/**
 * APP错误日志管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Apperrorlog extends Backend
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

            $oSelectQuery = AppErrorLogModel::where('1=1');
            $oTotalQuery  = AppErrorLogModel::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('app_os_code', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('app_os_code', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('app_os_model', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('app_os_model', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('app_error_log_id '.$sOrder)->select();
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
    	$row = AppErrorLogModel::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {

    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids='')
    {
        
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