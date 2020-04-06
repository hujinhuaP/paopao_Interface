<?php

namespace app\live\controller\document;

use app\common\controller\Backend;
use think\Exception;

use app\live\model\live\Agreement as AgreementModel;

/**
 * 平台协议管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Agreement extends Backend
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

            $oSelectQuery = AgreementModel::where('1=1');
            $oTotalQuery  = AgreementModel::where('1=1');

            if ($sKeyword) {
                $oSelectQuery->where('agreement_name', 'LIKE', '%'.$sKeyword.'%');
                $oTotalQuery->where('agreement_name', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('agreement_id desc')->select();

            foreach ($list as $v) {
            	$v['agreement_content'] = strip_tags($v['agreement_content']);
            	$v['agreement_content'] = mb_strlen($v['agreement_content']) >= 20 ? (mb_substr($v['agreement_content'], 0 , 20).' ... ') : $v['agreement_content'];
            }

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
		$row = AgreementModel::get($ids);
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
		$row = AgreementModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row->agreement_content  = $params['agreement_content'];
            $row->validate(
                [
                    'agreement_content'      => 'require',
                ],
                [
                    'agreement_content.require'  =>  __('Parameter %s can not be empty', ['agreement_content']),
                ]
            );

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