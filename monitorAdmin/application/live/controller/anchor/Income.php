<?php

namespace app\live\controller\anchor;

use app\live\library\Redis;
use app\live\model\live\AnchorLiveLog;
use app\live\model\live\UserFinanceLog;
use app\live\model\live\UserPrivateChatLog;
use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\Kv;
use app\live\model\live\User;
use app\live\model\live\AnchorLevel;
use app\live\model\live\Anchor as AnchorModel;
use app\live\model\live\GroupIncomeStat as GroupIncomeStatModel;

/**
 * 主播收益
 *
 * @Authors yeah_lsj@yeah.net
 */
class Income extends Backend
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

            $oSelectQuery = AnchorModel::where('1=1');
            $oTotalQuery  = AnchorModel::where('1=1');

            if ( $sKeyword ) {
                if ( is_numeric($sKeyword) ) {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%' . $sKeyword . '%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%' . $sKeyword . '%');
                }
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
                            $oSelectQuery->where('u.' . $key, $aOp[$key], $value);
                            $oTotalQuery->where('u.' . $key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ( $nLimit ) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->count();
            $list  = $oSelectQuery->alias('a')->join('user u', 'u.user_id=a.user_id')->order('u.user_id desc')->select();
            foreach ( $list as &$v ) {
                $liveTotal       = UserFinanceLog::where("user_id = {$v['user_id']} and (((consume_category_id = 3 or consume_category_id = 4) and user_amount_type= 'dot') or (consume_category_id=6 and type =1 ) ) ")->field("sum(consume) as total")->find();
                $chatTotal       = UserFinanceLog::where("user_id = {$v['user_id']} and (((consume_category_id = 17) and user_amount_type= 'dot') or (consume_category_id=6 and type =2 ) ) ")->field("sum(consume) as total")->find();
                $v['live_total'] = sprintf('%.2f', $liveTotal['total'] ? $liveTotal['total'] : 0.00);
                $v['chat_total'] = sprintf('%.2f', $chatTotal['total'] ? $chatTotal['total'] : 0.00);
            }

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
        session('income_user_id', $ids);
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {

    }


    /**
     * delete 删除
     *
     * @param  string $ids
     */
    public function delete($ids = '')
    {

    }

    /**
     * multi 批量操作
     *
     * @param  string $ids
     */
    public function multi($ids = '')
    {
        # code...
    }

    /**
     * 统计
     */
    public function stat()
    {
        if ( $this->request->isAjax() ) {

            $this->model = new GroupIncomeStatModel();
            $filter = $_GET['filter'];
            $betweeArr = [];
            if($filter){
                $filterArr = json_decode($filter,true);
                if(isset($filterArr['total_income'])){
                    $betweeArr = explode(',',$filterArr['total_income']);
                    unset($filterArr['total_income']);
                }
                $filter = json_encode($filterArr);

            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE,$filter);
            $where_str = 'group_income_stat.user_id != 0';
            $error_flg = $this->request->get("not_zero", '');
            if ( $error_flg == 'not_zero' ) {
                $where_str .= ' AND group_total_income > 0';
            }
            if($betweeArr){
                $start = $betweeArr[0];
                $end = $betweeArr[1];
                $where_str .= sprintf(' AND group_total_income - group_divid_income >= %d AND group_total_income - group_divid_income <= %d',$start,$end);
            }
            $total  = GroupIncomeStatModel::where($where_str)
                ->with('User,UserAccount,Group')->where($where)->order($sort, $order)->count();
            $list   = GroupIncomeStatModel::where($where_str)
                ->with('User,UserAccount,Group')->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

}