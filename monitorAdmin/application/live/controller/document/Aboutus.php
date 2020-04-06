<?php

namespace app\live\controller\document;

use app\common\controller\Backend;
use think\Exception;

use app\live\model\live\AboutUs as AboutUsModel;

/**
 * 关于我们管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Aboutus extends Backend
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

            $oSelectQuery = AboutUsModel::where('1=1');
            $oTotalQuery  = AboutUsModel::where('1=1');

            if ($sKeyword) {
                $oSelectQuery->where('about_us_title', 'LIKE', '%'.$sKeyword.'%');
                $oTotalQuery->where('about_us_title', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('about_us_id desc')->select();

            foreach ($list as $v) {
            	$v['about_us_content'] = strip_tags($v['about_us_content']);
            	$v['about_us_content'] = mb_strlen($v['about_us_content']) >= 20 ? (mb_substr($v['about_us_content'], 0 , 20).' ... ') : $v['about_us_content'];
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
		$row = AboutUsModel::get($ids);
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

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row = new AboutUsModel();
            
            $row->about_us_title  = $params['about_us_title'];
            $row->about_us_content  = $params['about_us_content'];
            
            $row->validate(
                [
                    'about_us_title' => 'require',
                    'about_us_content' => 'require',
                ],
                [
                    'about_us_title.require' => __('Parameter %s can not be empty', ['about_us_title']),
                    'about_us_content.require' => __('Parameter %s can not be empty', ['about_us_content']),
                ]
            );

            if ($row->save($params) === false) {
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
	public function edit($ids='')
	{
		$row = AboutUsModel::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row->about_us_title  = $params['about_us_title'];
            $row->about_us_content  = $params['about_us_content'];
            $row->type  = $params['type'];

            $row->validate(
                [
                    'about_us_title'      => 'require',
                    'about_us_content'    => 'require',
                    'type'                => 'require',
                ],
                [
                    'about_us_title.require'    =>  __('Parameter %s can not be empty', ['about_us_title']),
                    'about_us_content.require'  =>  __('Parameter %s can not be empty', ['about_us_content']),
                    'type'                      =>  __('Parameter %s can not be empty', ['type']),
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
        AboutUsModel::where('about_us_id', 'in', $ids)->delete();
		$this->success();
	}

	/**
	 * multi 批量操作
	 * @param  string $ids
	 */
	public function multi($ids='')
	{
		
	}
}