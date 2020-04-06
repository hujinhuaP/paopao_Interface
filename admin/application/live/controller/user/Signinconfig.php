<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\UserSigninConfig;

/**
 * 签到配置表
 * 
 * @Authors yeah_lsj@yeah.net
 */
class Signinconfig extends Backend
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

            $oSelectQuery = UserSigninConfig::where('1=1');
            $oTotalQuery  = UserSigninConfig::where('1=1');

            if ($sKeyword) {
                $oSelectQuery->where('user_signin_serial_total', 'LIKE', '%'.$sKeyword.'%');
                $oTotalQuery->where('user_signin_serial_total', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('user_signin_serial_total desc')->select();
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
		// 禁止操作
        $this->error();
        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');
            $row = new UserSigninConfig();

			$row->user_signin_serial_total = $params['user_signin_serial_total'];
			$row->user_signin_coin         = $params['user_signin_coin'];
			$row->user_signin_exp          = $params['user_signin_exp'];
			$row->user_signin_extra_coin   = $params['user_signin_extra_coin'];
			$row->user_signin_extra_exp    = $params['user_signin_extra_exp'];

            $row->validate(
            	[
					'user_signin_serial_total' => 'require|gt:0',
					'user_signin_coin'         => 'require|gt:0',
					'user_signin_extra_coin'   => 'gt:-1',
            	],
            	[
					'user_signin_serial_total.gt'      =>  __('Signin serial total').__('Must be greater than %d', 0),
					'user_signin_coin.gt'              =>  __('Signin serial coin').__('Must be greater than %d', 0),
					'user_signin_extra_coin.gt'        =>  __('Signin extra coin').__('Must be greater than %d', -1),
					'user_signin_serial_total.require' =>  __('Parameter %s can not be empty', [__('Signin serial total')]),
					'user_signin_coin.require'         =>  __('Parameter %s can not be empty', [__('Signin serial coin')]),
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
		$row = UserSigninConfig::get($ids);

        if (!$row)
            $this->error(__('No Results were found'));

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');


            $row->user_signin_serial_total = $params['user_signin_serial_total'];
			$row->user_signin_coin         = $params['user_signin_coin'];
			$row->user_signin_exp          = $params['user_signin_exp'];
			$row->user_signin_extra_coin   = $params['user_signin_extra_coin'];
			$row->user_signin_extra_exp    = $params['user_signin_extra_exp'];

            $row->validate(
            	[
					'user_signin_serial_total' => 'require|gt:0',
					'user_signin_coin'         => 'require|gt:0',
					'user_signin_extra_coin'   => 'gt:-1',
            	],
            	[
					'user_signin_serial_total.gt'      =>  __('Serial total').__('Must be greater than %d', 0),
					'user_signin_coin.gt'              =>  __('Coin').__('Must be greater than %d', 0),
					'user_signin_extra_coin.gt'        =>  __('Signin extra coin').__('Must be greater than %d', -1),
					'user_signin_serial_total.require' =>  __('Parameter %s can not be empty', [__('Signin serial total')]),
					'user_signin_coin.require'         =>  __('Parameter %s can not be empty', [__('Signin serial coin')]),
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
		// 禁止操作
        $this->error();
		$row = UserSigninConfig::where('user_signin_config_id', 'in', $ids)->delete();
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