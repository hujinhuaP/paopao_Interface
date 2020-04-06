<?php 

namespace app\live\controller\user;

use think\Exception;
use app\common\controller\Backend;

use app\live\model\live\User;
use app\live\model\live\UserInvite;
use app\live\model\live\UserInviteRewardLog;
use app\live\model\live\UserInviteRelationship;

/**
 * 用户邀请记录管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Invitelog extends Backend
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

            $oSelectQuery = User::where('u.user_invite_total>0');
            $oTotalQuery  = User::where('u.user_invite_total>0');

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
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
                            $oSelectQuery->where('u.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('u.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('u')
                                 ->join('user_invite ui', 'ui.to_user_id=u.user_id', 'LEFT')
                                 ->count();
            $list  = $oSelectQuery->alias('u')
                                  ->join('user_invite ui', 'ui.to_user_id=u.user_id', 'LEFT')
                                  ->field('ui.user_id parent_id,u.user_id,u.user_nickname,u.user_register_time,u.user_invite_coin_total')
                                  ->order('u.user_id desc')
                                  ->select();

            foreach ($list as  &$v) {
                $UserInviteRelationship = UserInviteRelationship::where('user_invite_relationship_ancestor', $v['user_id'])
                                        ->where('user_invite_relationship_distance>0')
                                        ->group('user_invite_relationship_distance')
                                        ->column('count(user_invite_relationship_descendant) total','user_invite_relationship_distance');
                $v->user_invite_total  = isset($UserInviteRelationship[1]) ? $UserInviteRelationship[1] : '0';
                $v->user_invite_total2 = isset($UserInviteRelationship[2]) ? $UserInviteRelationship[2] : '0';
                $v->user_invite_total3 = isset($UserInviteRelationship[3]) ? $UserInviteRelationship[3] : '0';
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
        if ($this->request->isAjax())
        {

            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserInviteRelationship::where('uir.user_invite_relationship_ancestor', $ids);
            $oTotalQuery  = UserInviteRelationship::where('uir.user_invite_relationship_ancestor', $ids);

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                }
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                    	case 'user_invite_relationship_ancestor':
                    	case 'user_invite_relationship_distance':
                    		$oSelectQuery->where('uir.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('uir.'.$key, $aOp[$key], $value);
                    		break;
                        default:
                            $oSelectQuery->where('u.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('u.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('uir')
            					 ->join('user u', 'u.user_id=uir.user_invite_relationship_descendant')
            					 ->join('user_invite_reward_log uirl', 'uirl.user_id=uir.user_invite_relationship_descendant and uirl.parent_user_id=uir.user_invite_relationship_ancestor', 'LEFT')
                                 ->where('uir.user_invite_relationship_distance>0')
            					 ->count();
            $list  = $oSelectQuery->alias('uir')
            					  ->join('user u', 'u.user_id=uir.user_invite_relationship_descendant')
            					  ->join('user_invite_reward_log uirl', 'uirl.user_id=uir.user_invite_relationship_descendant and uirl.parent_user_id=uir.user_invite_relationship_ancestor', 'LEFT')
            					  ->field('sum(uirl.recharge_invite_coin) recharge_invite_coin,max(u.user_id) user_id,max(u.user_nickname) user_nickname,max(u.user_register_time) user_register_time,max(uir.user_invite_relationship_distance) user_invite_relationship_distance,max(uir.user_invite_relationship_ancestor) user_invite_relationship_ancestor')
                                  ->where('uir.user_invite_relationship_distance>0')
                                  ->group('uir.user_invite_relationship_descendant')
            					  ->order('u.user_id desc')
            					  ->select();
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

        return $this->view->fetch();
    }

    /**
     * rewardlog 奖励记录
     * 
     * @param  int $ids     
     * @param  int $user_id
     */
    public function rewardlog($ids, $user_id)
    {
    	if ($this->request->isAjax())
        {
            $sKeyword = $this->request->param('search');
            $nOffset  = $this->request->param('offset');
            $nLimit   = $this->request->param('limit');
            $aFilter  = json_decode($this->request->param('filter'), 1);
            $aOp      = json_decode($this->request->param('op'), 1);

            $oSelectQuery = UserInviteRewardLog::where('uirl.parent_user_id', $ids)->where('uirl.user_id', $user_id);
            $oTotalQuery  = UserInviteRewardLog::where('uirl.parent_user_id', $ids)->where('uirl.user_id', $user_id);

            if ($sKeyword) {
                if (is_numeric($sKeyword)) {
                    $oSelectQuery->where('uirl.user_id', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('uirl.user_id', 'LIKE', '%'.$sKeyword.'%');
                } else {
                    $oSelectQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                    $oTotalQuery->where('u.user_nickname', 'LIKE', '%'.$sKeyword.'%');
                }
            }

            if ($aFilter) {
                foreach ($aFilter as $key => $value) {
                    if (stripos($aOp[$key], 'LIKE') !== FALSE) {
                        $value = str_replace(['LIKE ', '...'], ['', $value], $aOp[$key]);
                        $aOp[$key] = 'LIKE';
                    }
                    switch ($key) {
                    	case 'user_nickname':
                            $oSelectQuery->where('u.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('u.'.$key, $aOp[$key], $value);
                    		break;
                        default:
                    		$oSelectQuery->where('uirl.'.$key, $aOp[$key], $value);
                            $oTotalQuery->where('uirl.'.$key, $aOp[$key], $value);
                            break;
                    }
                }
            }

            if ($nLimit) {
                $oSelectQuery->limit($nOffset, $nLimit);
            }

            $total = $oTotalQuery->alias('uirl')
            					 ->join('user u', 'u.user_id=uirl.user_id')
            					 ->count();
            $list  = $oSelectQuery->alias('uirl')
            					  ->join('user u', 'u.user_id=uirl.user_id')
            					  ->order('uirl.user_id desc')
            					  ->select();

            foreach ($list as &$v) {
            	$v['invite_ratio'] =sprintf('%d%%', $v['invite_ratio']);
            }
            $result = array("total" => $total, "rows" => $list);
            return json($result);
        }

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