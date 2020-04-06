<?php

namespace app\live\controller\general;

use app\common\controller\Backend;
use app\live\library\Redis;


/**
 * APP版本管理
 *
 */
class Applist extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.app_list');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(NULL);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();

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
     * @param string $ids
     */
    public function detail( $ids = '' )
    {

    }

    /**
     * add 添加
     */
    public function add()
    {

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row = new $this->model();

            $data = $this->model::get(function ( $query ) use ( $params ) {
                $query->where("app_flg = '{$params['app_flg']}' OR app_name = '{$params['app_name']}'");
            });
            if ( $data ) {
                $this->error('已经存在相同的flg 或相同的名称');
            }

            $row->app_name                    = $params['app_name'];
            $row->app_flg                     = $params['app_flg'];
            $row->company_name                = $params['company_name'];
            $row->vip_apple_goods_prefix      = $params['vip_apple_goods_prefix'];
            $row->recharge_apple_goods_prefix = $params['recharge_apple_goods_prefix'];
            $row->anchor_prefix               = $params['anchor_prefix'];
            $row->platform_prefix             = $params['platform_prefix'];
            $row->service_phone               = $params['service_phone'];
            $row->service_wechat              = $params['service_wechat'];
            $row->service_email               = $params['service_email'];
            $row->check_user_id               = str_replace('，', ',', trim($params['check_user_id']));
            $row->check_pwd                   = $params['check_pwd'];
            $row->qq_appid                    = $params['qq_appid'];
            $row->qq_appkey                   = $params['qq_appkey'];
            $row->wx_appid                    = $params['wx_appid'];
            $row->wx_appkey                   = $params['wx_appkey'];
            $row->wb_appid                    = $params['wb_appid'];
            $row->wb_appkey                   = $params['wb_appkey'];
            $row->on_publish                  = $params['on_publish'];
            $row->jpush_app_key               = $params['jpush_app_key'];
            $row->jpush_master_secret         = $params['jpush_master_secret'];
            $row->app_os                      = $params['app_os'];
            $row->ios_pay                     = $params['ios_pay'];
            $row->app_guide_msg_flg           = $params['app_guide_msg_flg'];
            $row->check_login_change_status   = $params['check_login_change_status'];
            $row->send_posts_check_flg        = $params['send_posts_check_flg'];
            $row->on_publish_agent_id         = $params['on_publish_agent_id'];
            $row->on_publish_version          = $params['on_publish_version'];
            $row->msg_register_template_id    = $params['msg_register_template_id'];
            $row->msg_bind_template_id        = $params['msg_bind_template_id'];
            $row->msg_change_template_id      = $params['msg_change_template_id'];
            $row->msg_withdraw_template_id    = $params['msg_withdraw_template_id'];
            $row->company_address             = $params['company_address'];

            if ( $params['on_publish'] == 'Y' && $params['on_publish_version'] == '' ) {
                $this->error('上架中，请填写上架版本号');
            }

            $row->validate(
                [
                    'app_name'                    => 'require',
                    'app_flg'                     => 'require',
                    'company_name'                => 'require',
                    'vip_apple_goods_prefix'      => 'require',
                    'recharge_apple_goods_prefix' => 'require',
                    'anchor_prefix'               => 'require',
                    'platform_prefix'             => 'require',
                    'service_phone'               => 'require',
                    'service_wechat'              => 'require',
                    'service_email'               => 'require',
                    'on_publish'                  => 'require',
                    'app_os'                      => 'require',
                    'ios_pay'                     => 'require',
                    'app_guide_msg_flg'           => 'require',
                    'check_login_change_status'   => 'require',
                    'msg_register_template_id'    => 'require',
                    'msg_bind_template_id'        => 'require',
                    'msg_change_template_id'      => 'require',
                    'msg_withdraw_template_id'    => 'require',
                ],
                [
                    'app_name.require'                    => __('Parameter %s can not be empty', [ 'app_name' ]),
                    'app_flg.require'                     => __('Parameter %s can not be empty', [ 'app_flg' ]),
                    'company_name.require'                => __('Parameter %s can not be empty', [ 'company_name' ]),
                    'vip_apple_goods_prefix.require'      => __('Parameter %s can not be empty', [ 'vip_apple_goods_prefix' ]),
                    'recharge_apple_goods_prefix.require' => __('Parameter %s can not be empty', [ 'recharge_apple_goods_prefix' ]),
                    'anchor_prefix.require'               => __('Parameter %s can not be empty', [ 'anchor_prefix' ]),
                    'platform_prefix.require'             => __('Parameter %s can not be empty', [ 'platform_prefix' ]),
                    'service_phone.require'               => __('Parameter %s can not be empty', [ 'service_phone' ]),
                    'service_wechat.require'              => __('Parameter %s can not be empty', [ 'service_wechat' ]),
                    'service_email.require'               => __('Parameter %s can not be empty', [ 'service_email' ]),
                    'on_publish.require'                  => __('Parameter %s can not be empty', [ 'on_publish' ]),
                    'app_os.require'                      => __('Parameter %s can not be empty', [ 'app_os' ]),
                    'ios_pay.require'                     => __('Parameter %s can not be empty', [ 'ios_pay' ]),
                    'app_guide_msg_flg.require'           => __('Parameter %s can not be empty', [ 'app_guide_msg_flg' ]),
                    'check_login_change_status.require'   => __('Parameter %s can not be empty', [ 'app_guide_msg_flg' ]),
                    'msg_register_template_id.require'    => __('Parameter %s can not be empty', [ 'msg_register_template_id' ]),
                    'msg_bind_template_id.require'        => __('Parameter %s can not be empty', [ 'msg_bind_template_id' ]),
                    'msg_change_template_id.require'      => __('Parameter %s can not be empty', [ 'msg_change_template_id' ]),
                    'msg_withdraw_template_id.require'    => __('Parameter %s can not be empty', [ 'msg_withdraw_template_id' ]),
                ]
            );
            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }

        return $this->view->fetch();
    }

    /**
     * edit 编辑
     *
     * @param string $ids
     */
    public function edit( $ids = '' )
    {
        $row = $this->model::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params = $this->request->param('row/a');

            $row->app_name                    = $params['app_name'];
            $row->app_flg                     = $params['app_flg'];
            $row->company_name                = $params['company_name'];
            $row->vip_apple_goods_prefix      = $params['vip_apple_goods_prefix'];
            $row->recharge_apple_goods_prefix = $params['recharge_apple_goods_prefix'];
            $row->anchor_prefix               = $params['anchor_prefix'];
            $row->platform_prefix             = $params['platform_prefix'];
            $row->service_phone               = $params['service_phone'];
            $row->service_wechat              = $params['service_wechat'];
            $row->service_email               = $params['service_email'];
            $row->check_user_id               = str_replace('，', ',', trim($params['check_user_id']));
            $row->check_pwd                   = $params['check_pwd'];
            $row->qq_appid                    = $params['qq_appid'];
            $row->qq_appkey                   = $params['qq_appkey'];
            $row->wx_appid                    = $params['wx_appid'];
            $row->wx_appkey                   = $params['wx_appkey'];
            $row->wb_appid                    = $params['wb_appid'];
            $row->wb_appkey                   = $params['wb_appkey'];
            $row->on_publish                  = $params['on_publish'];
            $row->jpush_app_key               = $params['jpush_app_key'];
            $row->jpush_master_secret         = $params['jpush_master_secret'];
            $row->app_os                      = $params['app_os'];
            $row->ios_pay                     = $params['ios_pay'];
            $row->app_guide_msg_flg           = $params['app_guide_msg_flg'];
            $row->check_login_change_status   = $params['check_login_change_status'];
            $row->send_posts_check_flg        = $params['send_posts_check_flg'];
            $row->on_publish_agent_id         = $params['on_publish_agent_id'];
            $row->on_publish_version          = $params['on_publish_version'];
            $row->msg_register_template_id    = $params['msg_register_template_id'];
            $row->msg_bind_template_id        = $params['msg_bind_template_id'];
            $row->msg_change_template_id      = $params['msg_change_template_id'];
            $row->msg_withdraw_template_id    = $params['msg_withdraw_template_id'];
            $row->company_address             = $params['company_address'];

            if ( $params['on_publish'] == 'Y' && $params['on_publish_version'] == '' ) {
                $this->error('上架中，请填写上架版本号');
            }

            $row->validate(
                [
                    'app_name'                    => 'require',
                    'app_flg'                     => 'require',
                    'company_name'                => 'require',
                    'vip_apple_goods_prefix'      => 'require',
                    'recharge_apple_goods_prefix' => 'require',
                    'anchor_prefix'               => 'require',
                    'platform_prefix'             => 'require',
                    'service_phone'               => 'require',
                    'service_wechat'              => 'require',
                    'service_email'               => 'require',
                    'on_publish'                  => 'require',
                    'app_os'                      => 'require',
                    'ios_pay'                     => 'require',
                    'app_guide_msg_flg'           => 'require',
                    'check_login_change_status'   => 'require',
                    'msg_register_template_id'    => 'require',
                    'msg_bind_template_id'        => 'require',
                    'msg_change_template_id'      => 'require',
                    'msg_withdraw_template_id'    => 'require',
                ],
                [
                    'app_name.require'                    => __('Parameter %s can not be empty', [ 'app_name' ]),
                    'app_flg.require'                     => __('Parameter %s can not be empty', [ 'app_flg' ]),
                    'company_name.require'                => __('Parameter %s can not be empty', [ 'company_name' ]),
                    'vip_apple_goods_prefix.require'      => __('Parameter %s can not be empty', [ 'vip_apple_goods_prefix' ]),
                    'recharge_apple_goods_prefix.require' => __('Parameter %s can not be empty', [ 'recharge_apple_goods_prefix' ]),
                    'anchor_prefix.require'               => __('Parameter %s can not be empty', [ 'anchor_prefix' ]),
                    'platform_prefix.require'             => __('Parameter %s can not be empty', [ 'platform_prefix' ]),
                    'service_phone.require'               => __('Parameter %s can not be empty', [ 'service_phone' ]),
                    'service_wechat.require'              => __('Parameter %s can not be empty', [ 'service_wechat' ]),
                    'service_email.require'               => __('Parameter %s can not be empty', [ 'service_email' ]),
                    'on_publish.require'                  => __('Parameter %s can not be empty', [ 'on_publish' ]),
                    'app_os.require'                      => __('Parameter %s can not be empty', [ 'app_os' ]),
                    'ios_pay.require'                     => __('Parameter %s can not be empty', [ 'ios_pay' ]),
                    'app_guide_msg_flg.require'           => __('Parameter %s can not be empty', [ 'app_guide_msg_flg' ]),
                    'check_login_change_status.require'   => __('Parameter %s can not be empty', [ 'check_login_change_status' ]),
                    'msg_register_template_id.require'    => __('Parameter %s can not be empty', [ 'msg_register_template_id' ]),
                    'msg_bind_template_id.require'        => __('Parameter %s can not be empty', [ 'msg_bind_template_id' ]),
                    'msg_change_template_id.require'      => __('Parameter %s can not be empty', [ 'msg_change_template_id' ]),
                    'msg_withdraw_template_id.require'    => __('Parameter %s can not be empty', [ 'msg_withdraw_template_id' ]),
                ]
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $key    = sprintf('app_list:%s', $row->app_flg);
            $oRedis = new Redis();
            $oRedis->del($key);

            $this->success();
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();

    }

    /**
     * delete 删除
     *
     * @param string $ids
     */
    public function delete( $ids = '' )
    {

    }

    /**
     * multi 批量操作
     * @param string $ids
     */
    public function multi( $ids = '' )
    {

    }
}