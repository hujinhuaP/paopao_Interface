<?php

namespace app\live\controller\user;

use app\admin\library\Redis;
use app\live\library\TaskQueueService;
use app\live\model\live\Agent;
use app\live\model\live\UserAgentLog;
use think\Exception;
use think\Config;
use app\common\controller\Backend;

use app\live\model\live\UserLevel;
use app\live\model\live\User as UserModel;
use app\live\model\live\UserAccount;
use think\Session;

/**
 * 用户管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class User extends Backend
{
    protected $noNeedRight = ['selectpage'];

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
            $vip_flg   = $this->request->get("vip_flg", '');
            $where_str = '1=1';
            if ( $vip_flg == 'vip' ) {
                $where_str = 'user_member_expire_time > ' . time();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('user_id', TRUE);
//            $total    = $this->model->where($where)->where($where_str)->with('UserAccount')->order($sort, $order)->count();
            $total    = 1000;
            $list     = $this->model->where($where)->where($where_str)->with('UserAccount')->order($sort, $order)->limit($offset, $limit)->select();
            if(count($list) < $limit){
                $total = $offset + count($list);
            }
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
        $oGroup = Agent::all();
        $this->view->assign("row_agent", $oGroup);
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
            $hasChangeNickname = $params['user_nickname'];
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
            $oldSuperAdmin = $row->user_is_superadmin;
//            $oldAgentId = $row->user_invite_agent_id;
            $row->user_nickname               = $params['user_nickname'];
            $row->user_avatar                 = $params['user_avatar'];
            $row->user_intro                  = $params['user_intro'];
            $row->user_sex                    = $params['user_sex'];
            $row->user_is_superadmin          = $params['user_is_superadmin'];
            $row->user_remind                 = $params['user_remind'];
//            $row->user_invite_agent_id        = $params['user_invite_agent_id'];
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
                $oUserAccount->save([ 'user_phone' => $params['user_phone'] ]);
                if ( ($oldSuperAdmin == 'C' || $row->user_is_superadmin == 'C') && $oldSuperAdmin != $row->user_is_superadmin ) {
                    //更新客服缓存数据
                    $redis = new Redis();
                    if ( $row->user_is_superadmin == 'C' ) {
                        // 添加客服
                        $redis->hSet('customer_service_list', $row->user_id, $row->user_online_status);
                    } else {
                        //去除客服
                        $redis->hDel('customer_service_list', $row->user_id);
                    }

                }

                if($hasChangeNickname){
                    $oTaskQueueService = new TaskQueueService();
                    $oTaskQueueService->enQueue([
                        'task'   => 'user',
                        'action' => 'changeNickname',
                        'param'  => [
                            'user_id' => $row->user_id,
                        ],
                    ]);
                }

//                if($oldAgentId != $row->user_invite_agent_id){
//                    // 修改了agent id
//                    $oUserAgentLog = new UserAgentLog();
//                    $oUserAgentLog->user_agent_log_user_id = $row->user_id;
//                    $oUserAgentLog->user_agent_log_old_id = $oldAgentId;
//                    $oUserAgentLog->user_agent_log_new_id = $row->user_invite_agent_id;
//                    $oUserAgentLog->user_agent_log_type = UserAgentLog::TYPE_ADMIN;
//                    $oUserAgentLog->user_agent_log_admin_id = Session::get("admin.id");
//                    $oUserAgentLog->save();
//                }


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


    public function selectpage()
    {
        //设置过滤方法
        $this->request->filter([
            'strip_tags',
            'htmlspecialchars'
        ]);

        //搜索关键词,客户端输入以空格分开,这里接收为数组
        $word = (array)$this->request->request("q_word/a");
        //当前页
        $page = $this->request->request("page");
        //分页大小
        $pagesize = $this->request->request("per_page");
        //搜索条件
        $andor = $this->request->request("and_or");
        //排序方式
        $orderby = (array)$this->request->request("order_by/a");
        //显示的字段
        $field = $this->request->request("field");
        //主键
        $primarykey = $this->request->request("pkey_name");
        //主键值
        $primaryvalue = $this->request->request("pkey_value");
        //搜索字段
        $searchfield = (array)$this->request->request("search_field/a");
        //自定义搜索条件
        $custom = (array)$this->request->request("custom/a");
        $order  = [];

        $primarykey = 'user_id';

        $orderby = [
            [
                "field(user_is_superadmin,'Y','N','C','S')",
                'asc'
            ]
        ];

        foreach ( $orderby as $k => $v ) {
            $order[$v[0]] = $v[1];
        }
        $field = $field ? $field : 'name';
        $word[0] = trim($word[0] ?? '');
        if(isset($word[0]) && mb_strlen($word[0]) > 0 && mb_strlen($word[0]) < 2){
            return json([
                'list'  => [
                    [
                        'user_id' => 0,
                        'user_nickname' => '请输入至少2个字符'
                    ]
                ],
                'total' => 1
            ]);
        }

        //如果有primaryvalue,说明当前是初始化传值
        if ( $primaryvalue ) {
            $where = [
                $primarykey => [
                    'in',
                    $primaryvalue
                ]
            ];
        } else {
            $where = function ($query) use ($word, $andor, $field, $searchfield, $custom) {
                $query->where('user_id',$word[0]);
                $query->whereOr('user_nickname','like',$word[0] . "%");
            };
        }
        $list  = [];
        $total = $this->model->where($where)->count();
        if ( $total > 0 ) {
            $list = $this->model->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field("{$primarykey},{$field},user_is_superadmin")
                ->field("password,salt", TRUE)
                ->select();
            foreach($list as &$item){
                if($item['user_is_superadmin'] == 'Y'){
                    $item['user_nickname'] .= "[官方账号]";
                }
                $item['user_nickname'] = sprintf('用户ID:%s 昵称：%s',$item['user_id'],$item['user_nickname']);
            }
        }

        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        $total += 1;
        return json([
            'list'  => $list,
            'total' => $total
        ]);
    }


    /**
     * 渠道改变记录表
     */
    public function agentchange()
    {
        if ( $this->request->isAjax() ) {
            $this->model =  model('live.user_agent_log');
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $oGroup = Agent::all();
        $this->view->assign("row_agent", $oGroup);
        $this->view->assign($this->getDefaultTimeInterval('month'));
        return $this->view->fetch();
    }
}