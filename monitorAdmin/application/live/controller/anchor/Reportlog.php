<?php

namespace app\live\controller\anchor;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\AnchorReportLog;

/**
 * 举报主播记录
 *
 * @Authors yeah_lsj@yeah.net
 */
class Reportlog extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = AnchorReportLog::where('1=1');
            $oTotalQuery  = AnchorReportLog::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('anchor_user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('anchor_user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('anchor_report_log_content', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('anchor_report_log_content', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('anchor_report_log_id desc')->select();
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