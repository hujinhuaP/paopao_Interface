<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\UserVipCombo;

/**
 * 充值套餐管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Vipcombo extends Backend
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
            $sSort    = $this->request->param('sort');
            $sOrder   = $this->request->param('order');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserVipCombo::where('1=1');
            $oTotalQuery  = UserVipCombo::where('1=1');

            if ( $sKeyword ) {
                $oSelectQuery->where('user_vip_combo_fee', 'LIKE', '%' . $sKeyword . '%');
                $oTotalQuery->where('user_vip_combo_fee', 'LIKE', '%' . $sKeyword . '%');
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
            $list   = $oSelectQuery->order($sSort . ' ' . $sOrder)->select();
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

            $row = new UserVipCombo();

            $row->user_vip_combo_month               = $params['user_vip_combo_month'];
            $row->user_vip_combo_fee                 = $params['user_vip_combo_fee'];
            $row->user_vip_combo_apple_id            = isset($params['user_vip_combo_apple_id']) ? $params['user_vip_combo_apple_id'] : '';
            $row->user_vip_combo_original_price      = $params['user_vip_combo_original_price'];
            $row->user_vip_combo_discount            = round($params['user_vip_combo_fee'] / $params['user_vip_combo_original_price'], 3) * 10;
            $row->user_vip_combo_average_daily_price = round($params['user_vip_combo_fee'] / $params['user_vip_combo_month'] / 30, 2);
            $row->user_vip_combo_reward_coin      = $params['user_vip_combo_reward_coin'];
            $row->validate(
                [
                    'user_vip_combo_month' => 'require|gt:0',
                    'user_vip_combo_fee'   => 'require|gt:0',
                ],
                [
                    'user_vip_combo_month.gt'      => __('Month') . __('Must be greater than %d', 0),
                    'user_vip_combo_fee.gt'        => __('Coin') . __('Must be greater than %d', 0),
                    'user_vip_combo_month.require' => __('Parameter %s can not be empty', [ 'user_vip_combo_month' ]),
                    'user_vip_combo_fee.require'   => __('Parameter %s can not be empty', [ 'user_vip_combo_fee' ]),
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
        $row = UserVipCombo::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row->user_vip_combo_month               = $params['user_vip_combo_month'];
            $row->user_vip_combo_fee                 = $params['user_vip_combo_fee'];
            $row->user_vip_combo_apple_id            = isset($params['user_vip_combo_apple_id']) ? $params['user_vip_combo_apple_id'] : '';
            $row->user_vip_combo_original_price      = $params['user_vip_combo_original_price'];
            $row->user_vip_combo_discount            = round($params['user_vip_combo_fee'] / $params['user_vip_combo_original_price'], 3) * 10;
            $row->user_vip_combo_average_daily_price = round($params['user_vip_combo_fee'] / $params['user_vip_combo_month'] / 30, 2);
            $row->user_vip_combo_reward_coin      = $params['user_vip_combo_reward_coin'];
            $row->validate(
                [
                    'user_vip_combo_month' => 'require|gt:0',
                    'user_vip_combo_fee'   => 'require|gt:0',
                ],
                [
                    'user_vip_combo_month.gt'      => __('Coin') . __('Must be greater than %d', 0),
                    'user_vip_combo_fee.gt'        => __('Amount') . __('Must be greater than %d', 0),
                    'user_vip_combo_month.require' => __('Parameter %s can not be empty', [ 'user_vip_combo_month' ]),
                    'user_vip_combo_fee.require'   => __('Parameter %s can not be empty', [ 'user_vip_combo_fee' ]),
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
        UserVipCombo::where('user_vip_combo_id', 'in', $ids)->delete();
        $this->success();
    }

    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }
}