<?php

namespace app\live\controller\video;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\VideoCategory;
use app\live\model\live\UserVideo;

/**
 * 视频分类管理
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

            $oSelectQuery = VideoCategory::where('1=1');
            $oTotalQuery  = VideoCategory::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('name', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('name', 'LIKE', '%'.$sKeyword.'%');
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

            $row = new VideoCategory();
            
            $row->name = $params['name'];
            $row->sort = $params['sort'];
            $row->logo = $params['logo'];
            $row->validate(
            	[
                    'name' => 'require',
                    'sort' => 'require',
                    'logo' => 'require',
            	],
            	[
                    'name.require'      =>  __('Parameter %s can not be empty', ['name']),
                    'sort.require' =>  __('Parameter %s can not be empty', ['sort']),
                    'logo.require' =>  __('Parameter %s can not be empty', ['logo']),
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

        $row = VideoCategory::get($ids);

		if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row->name = $params['name'];
            $row->sort = $params['sort'];
            $row->logo = $params['logo'];
            $row->validate(
            	[
                    'name' => 'require',
                    'sort' => 'require',
                    'logo' => 'require',
            	],
            	[
                    'name.require'      =>  __('Parameter %s can not be empty', ['name']),
                    'sort.require' =>  __('Parameter %s can not be empty', ['sort']),
                    'logo.require' =>  __('Parameter %s can not be empty', ['logo']),
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
        $num = UserVideo::where('type','in',$ids)->count();
        if($num > 0){
            $this->error("该分类下已有视频，不能删除");
        }
		VideoCategory::where('id', 'in', $ids)->delete();
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
                VideoCategory::where('id', $id)->update(['sort' => $k + 1, 'update_time'=>time()]);
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
        $row = VideoCategory::get($ids);

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