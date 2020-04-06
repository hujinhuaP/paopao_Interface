<?php

namespace app\live\controller\user;

use think\Exception;
use think\Config;
use app\common\controller\Backend;

use app\live\model\live\UserLevel;
use app\live\model\live\User as UserModel;
use app\live\model\live\UserAccount;

/**
 * 用户管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class User extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user');
    }

    /**
     * index 列表
     */
    public function index()
    {

        if ( $this->request->isAjax() ) {

            $vip_flg = $this->request->get("vip_flg", '');
            $where_str = '1=1';
            if($vip_flg == 'vip'){
                $where_str = 'user_member_expire_time > '.time();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
            $total    = $this->model->where($where)->where($where_str)->with('UserAccount')->order($sort, $order)->count();
            $list     = $this->model->where($where)->where($where_str)->with('UserAccount')->order($sort, $order)->limit($offset, $limit)->select();
            $saveList = [];
            foreach ( $list as $item ) {
                $itemArr                   = $item->toArray();
                $itemArr['user_is_member'] = $itemArr['user_member_expire_time'] == 0 ? 'N' : (time() > $itemArr['user_member_expire_time'] ? 'O' : 'Y');
                $saveList[]                = $itemArr;
            }
            $result = [
                "total" => $total,
                "rows"  => $saveList,
            ];
            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * detail 详情
     *
     * @param  int $ids
     */
    public function detail($ids = '')
    {
        $row = UserModel::alias('u')->join('user_account a', 'a.user_id=u.user_id')
            ->where('u.user_id', $ids)
            ->find();

        switch ( $row['user_register_type'] ) {
            case 'wx':
                $row['user_register_type'] = __('Wechat');
                break;
            case 'qq':
                $row['user_register_type'] = __('QQ');
                break;
            case 'wb':
                $row['user_register_type'] = __('Weibo');
                break;
            case 'phone':
            default:
                $row['user_register_type'] = __('Mobile phone');
                break;
        }

        switch ( $row['user_login_type'] ) {
            case 'wx':
                $row['user_login_type'] = __('Wechat');
                break;
            case 'qq':
                $row['user_login_type'] = __('QQ');
                break;
            case 'wb':
                $row['user_login_type'] = __('Weibo');
                break;
            case 'phone':
            default:
                $row['user_login_type'] = __('Mobile phone');
                break;
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
    }

    /**
     * add 添加
     */
    public function add()
    {
        # code...
    }

    /**
     * edit 编辑
     *
     * @param  int $ids
     */
    public function edit($ids = '')
    {
        $row = UserModel::get($ids);

        $oUserAccount = UserAccount::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {

            $params = $this->request->param('row/a');
            if ( $params['user_phone'] != $oUserAccount->user_phone && UserAccount::where('user_phone', $params['user_phone'])->find() ) {
                $this->error(__('手机号码已存在'));
            }
            if ( $params['user_nickname'] != $row->user_nickname && UserModel::where('user_nickname', $params['user_nickname'])->find() ) {
                $this->error(__('Username exists'));
            }
            if ( !in_array($params['user_sex'], [
                0,
                1,
                2
            ]) ) {
                $this->error(__('性别设置错误'));
            }

            $row->user_nickname      = $params['user_nickname'];
            $row->user_avatar        = $params['user_avatar'];
            $row->user_intro         = $params['user_intro'];
            $row->user_sex           = $params['user_sex'];
            $row->user_is_superadmin = $params['user_is_superadmin'];
            $row->validate(
                [
                    'user_id'       => 'require',
                    'user_nickname' => 'require',
                ],
                [
                    'user_id.require'       => __('Parameter %s can not be empty', [ 'user_id' ]),
                    'user_nickname.require' => __('Parameter %s can not be empty', [ 'admin_id' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            } else {
                $oUserAccount->save(['user_phone' => $params['user_phone']]);
                $this->success();
            }
        }
        $this->view->assign("oUserAccount", $oUserAccount);
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
        # code...
    }

    /**
     * multi 批量修改
     *
     * @param  int $ids
     */
    public function multi($ids = '')
    {

    }

    /**
     * forbid 禁用
     */
    public function forbid($ids = '')
    {
        $row = UserModel::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->post('params');
            switch ( $params ) {
                case 'Y':
                case 'N':
                    $row->user_is_forbid = $params;
                    break;
                default:
                    # code...
                    break;
            }
            $row->user_online_status = 'Offline';
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }

            $oUserAccount             = UserAccount::get($ids);
            $oUserAccount->user_token = '';
            if ( $oUserAccount->save() === FALSE ) {
                $this->error($oUserAccount->getError());
            }

            if ( $row->user_is_forbid == 'Y' ) {
                file_get_contents(sprintf('%s/im/killOnline?%s', Config::get('api_url'), http_build_query([
                    'user_id'   => $ids,
                    'content'   => __('Forbid user tips'),
                    'device_id' => $oUserAccount->user_device_id,
                ])));
            }
            $this->success();
        }
    }

    /**
     * denyspeak 禁言
     */
    public function denyspeak($ids = '')
    {
        $row = UserModel::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->post('params');
            switch ( $params ) {
                case 'Y':
                case 'N':
                    $row->user_is_deny_speak = $params;
                    break;
                default:
                    # code...
                    break;
            }
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }
    }
}