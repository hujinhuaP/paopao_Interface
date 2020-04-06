<?php

namespace app\live\controller\anchor;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\AnchorCategory;

/**
 * 主播分类管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Category extends Backend
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
            $sSort    = $this->request->param('sort');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = AnchorCategory::where('1=1');
            $oTotalQuery  = AnchorCategory::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('anchor_category_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('anchor_category_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('anchor_category_name', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('anchor_category_name', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order($sSort.' '.$sOrder)->select();
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
		$row = AnchorCategory::get($ids);
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
            
            $row = new AnchorCategory();
            
            $row->anchor_category_name = $params['anchor_category_name'];
            $row->anchor_category_logo = $params['anchor_category_logo'];
            $row->anchor_category_sort = $params['anchor_category_sort'];

            $row->validate(
            	[
                    'anchor_category_name' => 'require',
                    'anchor_category_logo' => 'require',
                    'anchor_category_sort' => 'require',
            	],
            	[
                    'anchor_category_name.require'      =>  __('Parameter %s can not be empty', ['anchor_category_name']),
                    'anchor_category_logo.require'  =>  __('Parameter %s can not be empty', ['anchor_category_logo']),
                    'anchor_category_sort.require' =>  __('Parameter %s can not be empty', ['anchor_category_sort']),
            	]
            );

            if ($row->save($row->getData()) === false) {
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

        $row = AnchorCategory::get($ids);

		if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');
            
            
            $row->anchor_category_name = $params['anchor_category_name'];
            $row->anchor_category_logo = $params['anchor_category_logo'];
            $row->anchor_category_sort = $params['anchor_category_sort'];

            $row->validate(
            	[
                    'anchor_category_name' => 'require',
                    'anchor_category_logo' => 'require',
                    'anchor_category_sort' => 'require',
            	],
            	[
                    'anchor_category_name.require'      =>  __('Parameter %s can not be empty', ['anchor_category_name']),
                    'anchor_category_logo.require'  =>  __('Parameter %s can not be empty', ['anchor_category_logo']),
                    'anchor_category_sort.require' =>  __('Parameter %s can not be empty', ['anchor_category_sort']),
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
		AnchorCategory::where('anchor_category_id', 'in', $ids)->delete();
        $this->success();
	}

	/**
	 * multi 批量操作
	 * 
	 * @param  string $ids
	 */
	public function multi($ids='')
	{
		# code...
	}

	/**
	 * sort 排序
	 */
	public function sort()
	{
		if ($this->request->isPost()) {
            $ids      = $this->request->post('ids');
            $changeid = $this->request->post("changeid");
            $orderway = $this->request->post("orderway", 'strtolower');
            $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
            $ids = explode(',', $ids);

            if ($orderway == 'DESC') {
            	$ids = array_reverse($ids);
            }

            foreach ($ids as $k=>$id)
            {
                AnchorCategory::where('anchor_category_id', $id)->update(['anchor_category_sort' => $k + 1, 'anchor_category_update_time'=>time()]);
            }
            $this->success();
        }
        $this->error();
	}

    /**
     * status 修改状态
     * 
     * @param  string $ids 
     */
    public function status($ids='')
    {
        $row = AnchorCategory::get($ids);

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