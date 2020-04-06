<?php

namespace app\live\controller\user;

use app\common\controller\Backend;
use app\live\model\live\Kv;
use app\live\model\live\LevelConfig;

/**
 * 微信认证
 *
 */
class Wechat extends Backend
{
    use \app\live\library\traits\SystemMessageService;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.wechat_certification');
    }


    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
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
    }

    /**
     * edit 编辑
     *
     * @param  string $ids
     */
    public function edit($ids = '')
    {
        $row = $this->model::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            if ( $row->wechat_certification_status != 'C' ) {
                $this->error(__('You have no permission'));
            }
            $params = $this->request->param('row/a');
            if ( $params['wechat_certification_status'] == 'N' ) {
                $aValidate        = [
                    'wechat_certification_value'  => 'require',
                    'wechat_certification_status' => 'require',
                    'wechat_certification_remark' => 'require',
                    'wechat_certification_price'  => 'require',
                ];
                $aValidateMessage = [
                    'wechat_certification_value.require'  => __('Parameter %s can not be empty', [ 'wechat_certification_value' ]),
                    'wechat_certification_status.require' => __('Parameter %s can not be empty', [ 'wechat_certification_status' ]),
                    'wechat_certification_remark.require' => __('Parameter %s can not be empty', __('Check result')),
                    'wechat_certification_price.require'  => __('Parameter %s can not be empty', __('价格')),
                ];
            } else {
                $aValidate        = [
                    'wechat_certification_value'  => 'require',
                    'wechat_certification_status' => 'require',
                    'wechat_certification_price'  => 'require',
                ];
                $aValidateMessage = [
                    'wechat_certification_value.require'  => __('Parameter %s can not be empty', [ 'wechat_certification_value' ]),
                    'wechat_certification_status.require' => __('Parameter %s can not be empty', [ 'wechat_certification_status' ]),
                    'wechat_certification_price.require'  => __('Parameter %s can not be empty', __('价格')),
                ];
            }
            $row->wechat_certification_value    = $params['wechat_certification_value'];
            $row->wechat_certification_status   = $params['wechat_certification_status'];
            $row->wechat_certification_remark   = $params['wechat_certification_remark'];
            $row->wechat_certification_price    = $params['wechat_certification_price'];
            $admin                              = \think\Session::get('admin');
            $admin_id                           = $admin ? $admin->id : 0;
            $row->wechat_certification_admin_id = $admin_id;
            $row->wechat_certification_time     = time();
            $row->validate($aValidate, $aValidateMessage);
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {

                if ( $params['wechat_certification_status'] == 'Y' ) {
                    // 将用户的微信号改为当前认证
                    $oUser = \app\live\model\live\User::get($row->wechat_certification_user_id);
                    if ( $oUser->user_wechat == '' ) {
                        // 首次审核通过 赠送经验
                        $oAnchor = \app\live\model\live\Anchor::where('user_id ='.$row->wechat_certification_user_id)->find();
                        // 判断等级
                        $addExp                = intval(Kv::getValue(Kv::WECHAT_FIRST_CHECK_ANCHOR_EXP));
                        $oLevelConfig          = LevelConfig::where([
                            'level_type' => 'anchor',
                            'level_exp'  => [
                                '<=',
                                $oAnchor->anchor_exp + $addExp
                            ]
                        ])->order('level_value desc')->find();
                        $oAnchor->anchor_exp   += $addExp;
                        $oAnchor->anchor_level = $oLevelConfig->level_value;
                        $oAnchor->save();
                    }
                    $oUser->user_wechat       = $row->wechat_certification_value;
                    $oUser->user_wechat_price = $row->wechat_certification_price;
                    $oUser->save();

                    $this->sendGeneral($oUser->user_id, '恭喜你，微信认证已通过，已上架到个人页');
                }else if( $params['wechat_certification_status'] == 'N'){
                    $oUser = \app\live\model\live\User::get($row->wechat_certification_user_id);
                    $this->sendGeneral($oUser->user_id, sprintf('微信审核不通过，拒绝原因：%s',$row->wechat_certification_remark));
                }
                $this->success();
            }
        }

        $oUser = \app\live\model\live\User::get($row->wechat_certification_user_id);
        $this->view->assign('oUser', $oUser);
        $this->view->assign('row', $row);
        return $this->view->fetch();
    }


    /**
     * multi 批量操作
     * @param  string $ids
     */
    public function multi($ids = '')
    {
        # code...
    }


    /**
     * log 微信出售记录
     */
    public function log()
    {
        if ( $this->request->isAjax() ) {
            $this->model = model('live.user_wechat_log');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        return $this->view->fetch();
    }
}