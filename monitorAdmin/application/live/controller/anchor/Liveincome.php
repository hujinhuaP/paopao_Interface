<?php

namespace app\live\controller\anchor;

use app\live\model\live\UserFinanceLog;
use app\common\controller\Backend;


/**
 * 主播收益列表
 *
 * @Authors yeah_lsj@yeah.net
 */
class Liveincome extends Backend
{
    /**
     * index 列表
     */
    public function index()
    {
        if ($this->request->isAjax())
        {
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $user_id = session('income_user_id');
            $oSelectQuery = UserFinanceLog::where("user_id = {$user_id} and (((consume_category_id = 3 or consume_category_id = 4) and user_amount_type= 'dot') or (consume_category_id=6 and type =1 ) ) ")->order('user_finance_log_id','desc');
            $oTotalQuery  = UserFinanceLog::where("user_id = {$user_id} and (((consume_category_id = 3 or consume_category_id = 4) and user_amount_type= 'dot') or (consume_category_id=6 and type =1 ) ) ");
            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->count();
            $list  = $oSelectQuery->select();

            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }


}