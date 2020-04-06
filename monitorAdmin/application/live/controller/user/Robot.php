<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\UserLevel;
use app\live\model\live\User as UserModel;

/**
 * 机器人管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Robot extends Backend
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

            $oSelectQuery = UserModel::where('user_is_isrobot="Y"');
            $oTotalQuery  = UserModel::where('user_is_isrobot="Y"');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('user_nickname', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('user_id desc')->select();

            foreach ($list as &$v) {
                $v['user_coin']          = sprintf('%.2f', $v['user_coin']);
                $v['user_consume_total'] = sprintf('%.2f', $v['user_consume_total']);
                $v['user_dot']           = sprintf('%.2f', $v['user_dot']);
                $v['user_collect_total'] = sprintf('%.2f', $v['user_collect_total']);
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
		$row = UserModel::where('user_id', $ids)
						->where('user_is_isrobot="Y"')
                        ->find();
        $this->view->assign("row", $row);
        return $this->view->fetch();
	}

	/**
	 * add 添加
	 */
	public function add()
	{

		$row = new UserModel();
//        $nMaxUserLevel = UserLevel::max('user_level');

        if ($this->request->isPost())
        {
            
            $params = $this->request->param('row/a');

        	if (UserModel::where('user_nickname', $params['user_nickname'])->find()) {
                $this->error(__('Username exists'));
            }

            $row->user_nickname      = $params['user_nickname'];
            $row->user_avatar        = $params['user_avatar'];
//            $row->user_exp           = UserLevel::where('user_level', $params['user_level'])->value('user_level_exp', 0);
//            $row->user_level         = $params['user_level'];
            $row->user_intro         = $params['user_intro'];
            $row->user_consume_total = $params['user_consume_total'];
            $row->user_collect_total = $params['user_collect_total'];
            $row->user_sex           = $params['user_sex'];
            $row->user_is_isrobot    = 'Y';
            $row->validate(
            	[
                    'user_nickname'      => 'require',
//                    'user_level'         => 'require|egt:0|elt:'.$nMaxUserLevel,
                    'user_consume_total' => 'require|egt:0',
                    'user_collect_total' => 'require|egt:0',
            	],
            	[
//                    'user_level.egt'             =>  __('%d <= %s <= %d', 0, __('User level'), $nMaxUserLevel),
//                    'user_level.elt'             =>   __('%d <= %s <= %d', 0, __('User level'), $nMaxUserLevel),
                    'user_consume_total.egt'     =>  __('%s >= %d', __('Coin consume total'), 0),
                    'user_collect_total.egt'     =>  __('%s >= %d', __('Dot collect total'), 0),
                    'user_nickname.require'      =>  __('Parameter %s can not be empty', ['admin_id']),
//                    'user_level.require'         =>  __('Parameter %s can not be empty', ['user_budan_type']),
                    'user_consume_total.require' =>  __('Parameter %s can not be empty', ['user_budan_type']),
                    'user_collect_total.require' =>  __('Parameter %s can not be empty', ['user_budan_type']),
            	]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

//        $row->max_user_level = $nMaxUserLevel;
        $this->view->assign("row", $row);
        return $this->view->fetch();
	}

	/**
	 * edit 编辑
	 * 
	 * @param  string $ids
	 */
	public function edit($ids='')
	{
		$row = UserModel::where('user_id', $ids)
						->where('user_is_isrobot="Y"')
                        ->find();
        if (!$row)
            $this->error(__('No Results were found'));

//        $nMaxUserLevel = UserLevel::max('user_level');

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

        	if ($params['user_nickname'] != $row->user_nickname && UserModel::where('user_nickname', $params['user_nickname'])->find()) {
                $this->error(__('Username exists'));
            }

            $row->user_nickname      = $params['user_nickname'];
            $row->user_avatar        = $params['user_avatar'];
//            $row->user_exp           = $row->user_level == $params['user_level'] ? $row->user_exp : UserLevel::where('user_level', $params['user_level'])->value('user_level_exp', 0);
//            $row->user_level         = $params['user_level'];
            $row->user_intro         = $params['user_intro'];
            $row->user_consume_total = $params['user_consume_total'];
            $row->user_collect_total = $params['user_collect_total'];
            $row->user_sex = $params['user_sex'];

            $row->validate(
                [
                    'user_nickname'      => 'require',
//                    'user_level'         => 'require|egt:0|elt:'.$nMaxUserLevel,
                    'user_consume_total' => 'require|egt:0',
                    'user_collect_total' => 'require|egt:0',
                ],
                [
//                    'user_level.egt'             =>  __('%d <= %s <= %d', 0, __('User level'), $nMaxUserLevel),
//                    'user_level.elt'             =>   __('%d <= %s <= %d', 0, __('User level'), $nMaxUserLevel),
                    'user_consume_total.egt'     =>  __('%s >= %d', __('Coin consume total'), 0),
                    'user_collect_total.egt'     =>  __('%s >= %d', __('Dot collect total'), 0),
                    'user_nickname.require'      =>  __('Parameter %s can not be empty', ['admin_id']),
//                    'user_level.require'         =>  __('Parameter %s can not be empty', ['user_budan_type']),
                    'user_consume_total.require' =>  __('Parameter %s can not be empty', ['user_budan_type']),
                    'user_collect_total.require' =>  __('Parameter %s can not be empty', ['user_budan_type']),
                ]
            );

            if ($row->save($row->getData()) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

//        $row->max_user_level = $nMaxUserLevel;
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
		if ($ids) {
			UserModel::where('user_id', $ids)->where('user_is_isrobot="Y"')->delete();
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