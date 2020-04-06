<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\IsrobotTalk;

/**
 * 机器人语言库
 *
 * @Authors yeah_lsj@yeah.net
 */
class robottalk extends Backend
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

            $oSelectQuery = IsrobotTalk::where('1=1');
            $oTotalQuery  = IsrobotTalk::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where(function ($query) use ($sKeyword) {
                        $query->whereOr('isrobot_talk_content', 'LIKE', '%'.$sKeyword.'%');
                        $query->whereOr('isrobot_talk_id', 'LIKE', '%'.$sKeyword.'%');
                    });
                    $oTotalQuery->where(function ($query) use ($sKeyword) {
                        $query->whereOr('isrobot_talk_content', 'LIKE', '%'.$sKeyword.'%');
                        $query->whereOr('isrobot_talk_id', 'LIKE', '%'.$sKeyword.'%');
                    });
                } else {
                    $oSelectQuery->where('isrobot_talk_content', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('isrobot_talk_content', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('isrobot_talk_id desc')->select();
            
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
		$row = IsrobotTalk::get($ids);

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

            $row = new IsrobotTalk();
            $row->isrobot_talk_content = $params['isrobot_talk_content'];
            $row->isrobot_talk_type    = $params['isrobot_talk_type'];
            $row->validate(
            	[
                    'isrobot_talk_content' => 'require',
                    'isrobot_talk_type' => 'require',
            	],
            	[
                    'isrobot_talk_content.require' =>  __('Parameter %s can not be empty', ['isrobot_talk_content']),
                    'isrobot_talk_type.require'    =>  __('Parameter %s can not be empty', ['isrobot_talk_type']),
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
		$row = IsrobotTalk::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            
            $params = $this->request->param('row/a');

            $row->isrobot_talk_content = $params['isrobot_talk_content'];
            $row->isrobot_talk_type    = $params['isrobot_talk_type'];
            $row->validate(
                [
                    'isrobot_talk_content' => 'require',
                    'isrobot_talk_type' => 'require',
                ],
                [
                    'isrobot_talk_content.require' =>  __('Parameter %s can not be empty', ['isrobot_talk_content']),
                    'isrobot_talk_type.require'    =>  __('Parameter %s can not be empty', ['isrobot_talk_type']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
        $this->view->assign('row', $row);
        return $this->view->fetch();
	}

	/**
	 * delete 删除
	 * 
	 * @param  string $ids
	 */
	public function delete($ids='')
	{
		if ($ids) {
			IsrobotTalk::where('isrobot_talk_id', 'in', $ids)->delete();
            $this->success();
		}
		$this->error();
	}

	/**
	 * multi 批量操作
	 * @param  string $ids
	 */
	public function multi($ids='')
	{
		# code...
	}
}