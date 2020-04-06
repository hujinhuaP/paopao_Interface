<?php

namespace app\live\controller\game;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Kv;
use app\live\model\live\GameCategory;

/**
 * 游戏分类管理
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

            $oSelectQuery = GameCategory::where('1=1');
            $oTotalQuery  = GameCategory::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('game_category_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('game_category_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('game_category_name', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('game_category_name', 'LIKE', '%'.$sKeyword.'%');
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
		$row = GameCategory::get($ids);
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

        $row = GameCategory::get($ids);

		if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');
        
            $row->game_category_logo = $params['game_category_logo'];
            $row->game_category_sort = $params['game_category_sort'];

            $row->validate(
            	[
                    'game_category_logo' => 'require',
                    'game_category_sort' => 'require',
            	],
            	[
                    'game_category_logo.require'  =>  __('Parameter %s can not be empty', ['game_category_logo']),
                    'game_category_sort.require' =>  __('Parameter %s can not be empty', ['game_category_sort']),
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
                GameCategory::where('game_category_id', $id)->update(['game_category_sort' => $k + 1, 'game_category_update_time'=>time()]);
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
        $row = GameCategory::get($ids);

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

    /**
     * config 配置
     * 
     * @param  string $ids
     */
    public function config($ids='')
    {

        $row = GameCategory::get($ids);

        $aDeleteConfig = [
            'round'            => '0',
            'interval'         => '0',
            'idle'             => '0',
            'start_countdown'  => '0',
            'answer_countdown' => '0',
            'flop_time'        => '0',
            'odds'             => [0,0,0],
            'oddsname'         => ["","",""],
            'chance'           => [0,0,0],
            'rule'             => '',
            'bet_limit'        => '0',
        ];

        $aConfig = array_merge($aDeleteConfig, json_decode($row->game_config, 1)?: []);

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            // 回合间隔时间>揭晓倒计时时间+开奖动画时间
            if ($params['idle'] <= ($params['flop_time']+$params['answer_countdown'])) {
                $this->error(sprintf('%s > %s + %s', __('Round interval time'), __('Announce the countdown'), __('Winning animation time')));
            }

            foreach ($aConfig['odds'] as $k => $v) {
                $params['odds'][$k] = $params['odds'.$k];
                unset($params['odds'.$k]);
            }

            foreach ($aConfig['chance'] as $k => $v) {
                $params['chance'][$k] = $params['chance'.$k];
                unset($params['chance'.$k]);
            }

            $row->game_config = json_encode(array_merge($aConfig, $params), JSON_UNESCAPED_UNICODE);

            if ($row->save() === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $aConfig);
        return $this->view->fetch();
    }

    /**
     * threshold 阀值
     */
    public function threshold()
    {
        $aKey = [
            // 游戏阀值
            Kv::KEY_GAME_THRESHOLD,
        ];

        $rows = Kv::many($aKey);

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            foreach ($params as $key => $value) {
                $data[] = [
                    'kv_key' => $key,
                    'kv_value' => $value,
                ];
            }

            $row = new Kv();
            $row->validate(
                [
                    'kv_key'  => 'require',
                ],
                [
                    'kv_key.require' => __('Parameter %s can not be empty', ['kv_key']),
                ]
            );

            Kv::where('kv_key', 'in', array_keys($params))->delete();

            if ($row->saveAll($data) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        foreach ($aKey as $sKey) {
            $row[$sKey] = isset($rows[$sKey]) ? $rows[$sKey] : '';
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}