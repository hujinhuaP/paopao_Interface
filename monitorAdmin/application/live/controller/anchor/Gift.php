<?php

namespace app\live\controller\anchor;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\LiveGift;
use app\live\model\live\LiveGiftCategory;

/**
 * 礼物管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Gift extends Backend
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
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = LiveGift::where('1=1');
            $oTotalQuery  = LiveGift::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('live_gift_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('live_gift_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('live_gift_name', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('live_gift_name', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('live_gift_status,live_gift_sort '.$sOrder)->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        $oLiveGiftCategory = LiveGiftCategory::all();
        $this->view->assign("row_category", $oLiveGiftCategory);
        return $this->view->fetch();
	}

	/**
	 * detail 详情
	 * 
	 * @param  string $ids
	 */
	public function detail($ids='')
	{
		$row = LiveGift::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        $oLiveGiftCategory = LiveGiftCategory::all();
        $this->view->assign("row_category", $oLiveGiftCategory);
        $this->view->assign("row", $row);
        return $this->view->fetch();
	}

	/**
	 * add 添加
	 */
	public function add()
	{
        $oLiveGiftCategory = LiveGiftCategory::all();

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $row = new LiveGift();

			$row->live_gift_name        = $params['live_gift_name'];
			$row->live_gift_detail      = $params['live_gift_detail'];
			$row->live_gift_coin        = $params['live_gift_coin'];
			$row->live_gift_logo        = $params['live_gift_logo'];
			$row->live_gift_small_gif   = $params['live_gift_small_gif'];
			$row->live_gift_gif         = $params['live_gift_gif'];
            $row->live_gift_category_id = $params['live_gift_category_id'];
			$row->live_gift_sort        = $params['live_gift_sort'];

            $row->validate(
            	[
					'live_gift_name'        => 'require',
					'live_gift_detail'      => 'require',
					'live_gift_coin'        => 'require',
					'live_gift_logo'        => 'require',
                    'live_gift_category_id' => 'require',
            	],
            	[
					'live_gift_name.require'        =>  __('Parameter %s can not be empty', ['live_gift_name']),
					'live_gift_detail.require'      =>  __('Parameter %s can not be empty', ['live_gift_detail']),
					'live_gift_coin.require'        =>  __('Parameter %s can not be empty', ['live_gift_coin']),
					'live_gift_logo.require'        =>  __('Parameter %s can not be empty', ['live_gift_logo']),
                    'live_gift_category_id.require' =>  __('Parameter %s can not be empty', ['live_gift_category_id']),
                    'live_gift_sort.require'        =>  __('Parameter %s can not be empty', ['live_gift_sort']),
            	]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row_category", $oLiveGiftCategory);
        return $this->view->fetch();
	}

	/**
	 * edit 编辑
	 * 
	 * @param  string $ids
	 */
	public function edit($ids='')
	{
		$row = LiveGift::get($ids);
        if (!$row)
            $this->error(__('No Results were found'));

        $oLiveGiftCategory = LiveGiftCategory::all();

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

			$row->live_gift_name        = $params['live_gift_name'];
			$row->live_gift_detail      = $params['live_gift_detail'];
			$row->live_gift_coin        = $params['live_gift_coin'];
			$row->live_gift_logo        = $params['live_gift_logo'];
			$row->live_gift_small_gif   = $params['live_gift_small_gif'];
			$row->live_gift_gif         = $params['live_gift_gif'];
			$row->live_gift_category_id = $params['live_gift_category_id'];

            $row->validate(
            	[
					'live_gift_name'        => 'require',
					'live_gift_detail'      => 'require',
					'live_gift_coin'        => 'require',
					'live_gift_logo'        => 'require',
					'live_gift_category_id' => 'require',
            	],
            	[
					'live_gift_name.require'        =>  __('Parameter %s can not be empty', ['live_gift_name']),
					'live_gift_detail.require'      =>  __('Parameter %s can not be empty', ['live_gift_detail']),
					'live_gift_coin.require'        =>  __('Parameter %s can not be empty', ['live_gift_coin']),
					'live_gift_logo.require'        =>  __('Parameter %s can not be empty', ['live_gift_logo']),
					'live_gift_category_id.require' =>  __('Parameter %s can not be empty', ['live_gift_category_id']),
            	]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        $this->view->assign("row_category", $oLiveGiftCategory);
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
                LiveGift::where('live_gift_id', $id)->update(['live_gift_sort' => $k + 1, 'live_gift_update_time'=>time()]);
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
        $row = LiveGift::get($ids);

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