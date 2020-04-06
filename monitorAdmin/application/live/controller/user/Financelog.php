<?php

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\UserFinanceLog as UserFinanceLogModel;
use app\live\model\live\UserConsumeCategory;

/**
 * 用户流水管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Financelog extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        $oUserConsumeCategory = UserConsumeCategory::all();

        if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserFinanceLogModel::where('1=1');
            $oTotalQuery  = UserFinanceLogModel::where('1=1');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('remark', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('remark', 'LIKE', '%'.$sKeyword.'%');
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
            $list  = $oSelectQuery->order('user_finance_log_id desc')->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }
        $this->view->assign($this->getDefaultTimeInterval());
        $this->view->assign("row_category", $oUserConsumeCategory);
        return $this->view->fetch();
    }


    /**
     * 金币数据
     */
    public function coin()
    {
        $coinFlg = [
            UserConsumeCategory::RECHARGE_COIN,
            UserConsumeCategory::SEND_GIFT_COIN,
            UserConsumeCategory::SIGNIN_COIN,
            UserConsumeCategory::INVITE_USER_COIN,
            UserConsumeCategory::BUDAN_COIN,
            UserConsumeCategory::PRIVATE_CHAT,
            UserConsumeCategory::INVITE_REGISTER_REWARD,
            UserConsumeCategory::REGISTER_REWARD,
            UserConsumeCategory::VIDEO_PAY,
            UserConsumeCategory::SEND_CHAT_PAY,
            UserConsumeCategory::GUIDE_VIDEO_PAY,
        ];
        $oUserConsumeCategory = UserConsumeCategory::all($coinFlg);

        $this->model = new UserFinanceLogModel();
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id',true);
            $total  = $this->model::where('user_amount_type',UserFinanceLogModel::AMOUNT_COIN)->where('consume_category_id','in',$coinFlg)->where($where)->with('User,TargetUser')->order($sort, $order)->count();
            $list   = $this->model::where('user_amount_type',UserFinanceLogModel::AMOUNT_COIN)->where('consume_category_id','in',$coinFlg)->where($where)->with('User,TargetUser')->order($sort, $order)->limit($offset, $limit)->select();
            $gift_flg = UserConsumeCategory::SEND_GIFT_COIN;
            $chat_flg = UserConsumeCategory::PRIVATE_CHAT;
            $recharge_flg = UserConsumeCategory::RECHARGE_COIN;
            $statField = "sum(case consume_category_id when $gift_flg then -consume else 0 end) as gift_consume,sum(case consume_category_id when $chat_flg then -consume else 0 end) as chat_consume
            ,sum(case consume_category_id when $recharge_flg then consume else 0 end) as recharge_total
            ,sum(case when user_last_free_coin < user_current_free_coin then user_current_free_coin - user_last_free_coin else 0 end) as free_total
            ,sum(case when user_last_free_coin > user_current_free_coin then user_last_free_coin - user_current_free_coin else 0 end) as use_free_coin
            ,sum(case when user_last_user_coin > user_current_user_coin then user_last_user_coin - user_current_user_coin else 0 end) as use_coin";
            $stat = $this->model::where('user_amount_type',UserFinanceLogModel::AMOUNT_COIN)->where($where)->field($statField)->find();

            // 累计赠送 累计充值 累计消费赠送 累计消费充值  赠送礼物  视频匹配
            $stat = $stat->toArray();
            $stat['free_total'] = sprintf('%.2f',$stat['free_total']);
            $stat['use_coin'] = sprintf('%.2f',$stat['use_coin']);
            $stat['use_free_coin'] = sprintf('%.2f',$stat['use_free_coin']);

            $result = [
                "total" => $total,
                "rows"  => $list,
                'stat' => $stat
            ];
            return json($result);
        }
        $this->view->assign($this->getDefaultTimeInterval());
        $this->view->assign("row_category", $oUserConsumeCategory);
        return $this->view->fetch();
    }

    /**
     * 佣金数据
     */
    public function dot()
    {
        $coinFlg = [
            UserConsumeCategory::RECEIVE_GIFT_COIN,
            UserConsumeCategory::CATEGORY_WITHDRAW,
            UserConsumeCategory::BUDAN_DOT,
            UserConsumeCategory::PRIVATE_CHAT,
            UserConsumeCategory::INVITE_USER_DOT,
            UserConsumeCategory::VIDEO_PAY,
            UserConsumeCategory::SEND_CHAT_PAY,
            UserConsumeCategory::INVITE_WITHDRAW_REWARD,
        ];
        $oUserConsumeCategory = UserConsumeCategory::all($coinFlg);

        $this->model = new UserFinanceLogModel();
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id',true);
            $total  = $this->model::where('user_amount_type',UserFinanceLogModel::AMOUNT_DOT)->where('consume_category_id','in',$coinFlg)->where($where)->with('User,TargetUser')->order($sort, $order)->count();
            $list   = $this->model::where('user_amount_type',UserFinanceLogModel::AMOUNT_DOT)->where('consume_category_id','in',$coinFlg)->where($where)->with('User,TargetUser')->order($sort, $order)->limit($offset, $limit)->select();
            $statField = "sum(case when consume > 0 then consume else 0 end) as dot_total";
            $stat = $this->model::where('user_amount_type',UserFinanceLogModel::AMOUNT_DOT)->where($where)->field($statField)->find();
            $stat = $stat->toArray();
            $result = [
                "total" => $total,
                "rows"  => $list,
                'stat' => $stat
            ];
            return json($result);
        }
        $this->view->assign($this->getDefaultTimeInterval());
        $this->view->assign("row_category", $oUserConsumeCategory);
        return $this->view->fetch();
    }

    /**
     * detail 详情
     * 
     * @param  string $ids
     */
    public function detail($ids='')
    {
        $row = UserFinanceLogModel::get($ids);
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
}