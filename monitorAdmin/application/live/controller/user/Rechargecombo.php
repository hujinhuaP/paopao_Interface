<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Kv;
use app\live\model\live\UserRechargeCombo;

/**
 * 充值套餐管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Rechargecombo extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserRechargeCombo::where('1=1');
            $oTotalQuery  = UserRechargeCombo::where('1=1');

            if ( $sKeyword ) {
                $oSelectQuery->where('user_recharge_combo_coin', 'LIKE', '%' . $sKeyword . '%');
                $oTotalQuery->where('user_recharge_combo_coin', 'LIKE', '%' . $sKeyword . '%');
            }

            if ( $aFilter ) {
                foreach ( $aFilter as $key => $value ) {
                    if ( stripos($aOp[$key], 'LIKE') !== FALSE ) {
                        $value     = str_replace([
                            'LIKE ',
                            '...'
                        ], [
                            '',
                            $value
                        ], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ( $key ) {
                        default:
                            $oSelectQuery->where($key, $aOp[$key], $value);
                            $oTotalQuery->where($key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total  = $oTotalQuery->count();
            $list   = $oSelectQuery->order('user_recharge_combo_fee asc')->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  string $ids
     */
    public function detail($ids = '')
    {

    }

    /**
     * add 添加
     */
    public function add()
    {

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row = new UserRechargeCombo();

            $row->user_recharge_combo_coin      = $params['user_recharge_combo_coin'];
            $row->user_recharge_combo_fee       = $params['user_recharge_combo_fee'];
            $row->first_recharge_reward_vip_day = intval($params['first_recharge_reward_vip_day']);
            $row->user_recharge_combo_apple_id  = isset($params['user_recharge_combo_apple_id']) ? $params['user_recharge_combo_apple_id'] : '';
            $row->validate(
                [
                    'user_recharge_combo_coin' => 'require|gt:0',
                    'user_recharge_combo_fee'  => 'require|gt:0',
                ],
                [
                    'user_recharge_combo_coin.gt'      => __('Coin') . __('Must be greater than %d', 0),
                    'user_recharge_combo_fee.gt'       => __('Amount') . __('Must be greater than %d', 0),
                    'user_recharge_combo_coin.require' => __('Parameter %s can not be empty', [ 'user_recharge_combo_coin' ]),
                    'user_recharge_combo_fee.require'  => __('Parameter %s can not be empty', [ 'user_recharge_combo_fee' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
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
    public function edit($ids = '')
    {
        $row = UserRechargeCombo::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row->user_recharge_combo_coin      = $params['user_recharge_combo_coin'];
            $row->user_recharge_combo_fee       = $params['user_recharge_combo_fee'];
            $row->first_recharge_reward_vip_day = intval($params['first_recharge_reward_vip_day']);
            $row->user_recharge_combo_apple_id  = isset($params['user_recharge_combo_apple_id']) ? $params['user_recharge_combo_apple_id'] : '';
            $row->validate(
                [
                    'user_recharge_combo_coin' => 'require|gt:0',
                    'user_recharge_combo_fee'  => 'require|gt:0',
                ],
                [
                    'user_recharge_combo_coin.gt'      => __('Coin') . __('Must be greater than %d', 0),
                    'user_recharge_combo_fee.gt'       => __('Amount') . __('Must be greater than %d', 0),
                    'user_recharge_combo_coin.require' => __('Parameter %s can not be empty', [ 'user_recharge_combo_coin' ]),
                    'user_recharge_combo_fee.require'  => __('Parameter %s can not be empty', [ 'user_recharge_combo_fee' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
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
    public function delete($ids = '')
    {
        UserRechargeCombo::where('user_recharge_combo_id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }

    /**
     * ratio 充值比例
     *
     * @param  string $ids
     */
    public function ratio()
    {
        $row = Kv::where('kv_key', Kv::KEY_RECHARGE_RATIO)->find();

        if ( $row == FALSE ) {
            $row           = new Kv();
            $row->kv_key   = Kv::KEY_RECHARGE_RATIO;
            $row->kv_value = [
                'coin'   => '',
                'amount' => '',
            ];
        } else {
            $row->kv_value = json_decode($row->kv_value, 1);
        }

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row->kv_value = json_encode([
                'coin'   => $params['coin'],
                'amount' => $params['amount'],
            ]);

            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }
}