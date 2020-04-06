<?php

namespace app\live\controller\group;

use app\common\controller\Backend;
use app\live\model\live\Group as GroupModel;
use app\live\model\live\GroupIncomeStat as GroupIncomeStatModel;

/**
 * 公会列表
 */
class Group extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.group');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams('group_name');
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
     * add 添加
     */
    public function add()
    {

        if ( $this->request->isPost() ) {
            $params                        = $this->request->param('row/a');
            $row                           = new GroupModel();
            $row->group_name               = $params['group_name'];
            $row->group_type               = $params['group_type'];
            $row->divid_type               = $params['divid_type'];
            $row->divid_precent            = $params['divid_precent'];
            $row->divid_time_precent       = $params['divid_time_precent'];
            $row->divid_gift_precent       = $params['divid_gift_precent'];
            $row->divid_video_precent      = $params['divid_video_precent'];
            $row->divid_chat_precent       = $params['divid_chat_precent'];
            $row->divid_chat_game          = $params['divid_chat_game'];
            $row->divid_posts_precent      = $params['divid_posts_precent'];
            $row->divid_wechat_precent     = $params['divid_wechat_precent'];
            $row->video_chat_reward_config = $params['video_chat_reward_config'];
            $row->status                   = $params['status'];
            $row->invite_code              = $row->createInviteCode();
            $existData                     = $this->model->where("group_name", $params['group_name'])->find();
            if ( $existData ) {
                $this->error(__('Exist group name'));
            }
            $row->validate([
                'group_name'          => 'require',
                'group_type'          => 'require',
                'divid_type'          => 'require',
                'divid_precent'       => 'require|between:0,100',
                'divid_time_precent'  => 'require|between:0,100',
                'divid_gift_precent'  => 'require|between:0,100',
                'divid_video_precent' => 'require|between:0,100',
                'divid_chat_precent'  => 'require|between:0,100',
                'divid_chat_game'     => 'require|between:0,100',
            ], [
                'group_name.require'          => __('Parameter %s can not be empty', [ __('Group name') ]),
                'group_type.require'          => __('Parameter %s can not be empty', [ __('Group type') ]),
                'divid_type.require'          => __('Parameter %s can not be empty', [ __('Divid type') ]),
                'divid_precent.require'       => __('Parameter %s can not be empty', [ __('Divid precent') ]),
                'divid_time_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of live time') ]),
                'divid_gift_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of get gift') ]),
                'divid_video_precent.require' => __('Parameter %s can not be empty', [ __('Divid precent of video') ]),
                'divid_chat_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of chat') ]),
                'divid_chat_game.require'     => __('Parameter %s can not be empty', [ __('游戏比例') ]),
                'divid_precent.between'       => __('%s must be between %d and %d', [
                    __('Divid precent'),
                    0,
                    100
                ]),
                'divid_time_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of live time'),
                    0,
                    100
                ]),
                'divid_gift_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of get gift'),
                    0,
                    100
                ]),
            ]);
            if ( !$row->save($row->getData()) ) {
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
     * @param string $ids
     */
    public function edit( $ids = '' )
    {

        $row = GroupModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params                        = $this->request->param('row/a');
            $row->group_type               = $params['group_type'];
            $row->divid_type               = $params['divid_type'];
            $row->divid_precent            = $params['divid_precent'];
            $row->divid_time_precent       = $params['divid_time_precent'];
            $row->divid_gift_precent       = $params['divid_gift_precent'];
            $row->divid_video_precent      = $params['divid_video_precent'];
            $row->divid_chat_precent       = $params['divid_chat_precent'];
            $row->divid_chat_game          = $params['divid_chat_game'];
            $row->divid_posts_precent      = $params['divid_posts_precent'];
            $row->divid_wechat_precent     = $params['divid_wechat_precent'];
            $row->video_chat_reward_config = $params['video_chat_reward_config'];
            $row->status                   = $params['status'];
            if ( $row->group_name != $params['group_name'] ) {
                $row->group_name = $params['group_name'];
            }
            $existData = $this->model->where([
                "group_name" => $params['group_name'],
                'id'         => [
                    'neq',
                    $ids
                ]
            ])->find();
            if ( $existData ) {
                $this->error(__('Exist group name'));
            }
            $row->validate([
                'group_name'          => 'require',
                'group_type'          => 'require',
                'divid_type'          => 'require',
                'divid_precent'       => 'require|between:0,100',
                'divid_time_precent'  => 'require|between:0,100',
                'divid_gift_precent'  => 'require|between:0,100',
                'divid_video_precent' => 'require|between:0,100',
                'divid_chat_precent'  => 'require|between:0,100',
                'divid_chat_game'     => 'require|between:0,100',
            ], [
                'group_name.require'          => __('Parameter %s can not be empty', [ __('Group name') ]),
                'group_type.require'          => __('Parameter %s can not be empty', [ __('Group type') ]),
                'divid_type.require'          => __('Parameter %s can not be empty', [ __('Divid type') ]),
                'divid_precent.require'       => __('Parameter %s can not be empty', [ __('Divid precent') ]),
                'divid_time_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of live time') ]),
                'divid_gift_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of get gift') ]),
                'divid_video_precent.require' => __('Parameter %s can not be empty', [ __('Divid precent of video') ]),
                'divid_chat_precent.require'  => __('Parameter %s can not be empty', [ __('Divid precent of chat') ]),
                'divid_chat_game.require'     => __('Parameter %s can not be empty', [ __('游戏分成比例') ]),
                'divid_precent.between'       => __('%s must be between %d and %d', [
                    __('Divid precent'),
                    0,
                    100
                ]),
                'divid_time_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of live time'),
                    0,
                    100
                ]),
                'divid_gift_precent.between'  => __('%s must be between %d and %d', [
                    __('Divid precent of get gift'),
                    0,
                    100
                ]),
            ]);
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
     * @param string $ids
     */
    public function delete( $ids = '' )
    {
    }

    /**
     * multi 批量操作
     *
     * @param string $ids
     */
    public function multi( $ids = '' )
    {
        # code...
    }

    /**
     * sort 排序
     */
    public function sort()
    {
    }

    /**
     * status 修改状态
     *
     * @param string $ids
     */
    public function status( $ids = '' )
    {
        $row = GroupModel::get($ids);
        if ( !$row )
            $this->error(__('No Results were found'));
        if ( $this->request->isPost() ) {
            $params            = $this->request->param('params');
            $params            = explode('=', $params);
            $row[ $params[0] ] = $params[1];
            if ( $row->save() === FALSE ) {
                $this->error($row->getError());
            }
            $this->success();
        }
    }


    /**
     * 公会收益
     */
    public function finance()
    {
        $oGroup = GroupModel::all();
        if ( $this->request->isAjax() ) {
            // 如果是第一页 查询上次统计时间在1分钟前的公会数据
            $offset = $this->request->get("offset", 0);
            if ( $offset == 0 ) {
                $today      = strtotime(date('Y-m-d 00:00:00'));
                $todayWhere = [
                    'stat_time' => $today,
                    'user_id'   => 0
                ];
                $row        = GroupIncomeStatModel::get($todayWhere);
                if ( !$row || $row->update_time < time() - 60 ) {
                    // 不存在 或者 上次统计时间已过1分钟
                    // 计算当天的数据 并存入数据库
                    $group = GroupModel::all();
                    if ( $group ) {
                        $allSaveArr        = [];
                        $groupDividTypaArr = [];
                        $saveDefault       = [
                            'stat_time'            => $today,
                            'group_id'             => 0,
                            'user_id'              => 0,
                            'anchor_time_income'   => 0,
                            'anchor_gift_income'   => 0,
                            'wx_sale_income'       => 0,
                            'anchor_guard_income'  => 0,
                            'word_msg_income'      => 0,
                            'voice_msg_income'     => 0,
                            'image_msg_income'     => 0,
                            'invite_reward_income' => 0,
                            'group_divid_income'   => 0,
                            'group_total_income'   => 0,
                            'divid_type'           => 0,
                            'time_total'           => 0,
                            'video_income'         => 0,
                            'chat_game_income'     => 0,
                        ];

                        foreach ( $group as $groupItem ) {
                            $tmp                                   = $saveDefault;
                            $tmp['group_id']                       = $groupItem['id'];
                            $allSaveArr[ $groupItem['id'] ]        = $tmp;
                            $groupDividTypaArr[ $groupItem['id'] ] = [
                                'divid_type'    => $groupItem['divid_type'],
                                'divid_precent' => $groupItem['divid_precent'],
                            ];
                        }
                        $sql  = "select group_id,
sum(case consume_category_id when 23 then consume else 0 end ) as word_chat_dot_total,
sum(case consume_category_id when 23 then consume_source else 0 end ) as word_chat_coin_total,
sum(case consume_category_id when 22 then consume else 0 end ) as video_dot_total,
sum(case consume_category_id when 22 then consume_source else 0 end ) as video_coin_total,
sum(case consume_category_id when 21 then consume when 25 then consume else 0 end ) as invite_reward_dot_total,
sum(case consume_category_id when 21 then consume_source else 0 end ) as invite_reward_coin_total,
sum(case consume_category_id when 6 then consume else 0 end ) as gift_dot_total,
sum(case consume_category_id when 6 then consume_source else 0 end ) as gift_coin_total,
sum(case consume_category_id when 17 then consume else 0 end ) as time_dot_total,
sum(case consume_category_id when 17 then extra_number else 0 end ) as time_total,
sum(case consume_category_id when 17 then consume_source else 0 end ) as time_coin_total,
sum(case consume_category_id when 36 then consume else 0 end ) as guard_dot_total,
sum(case consume_category_id when 27 then consume_source else 0 end ) as chat_game_coin_total,
sum(case consume_category_id when 27 then consume else 0 end ) as chat_game_total
 from user_finance_log where user_amount_type = 'dot' AND consume_category_id in (17,6,21,22,23,28,31) and create_time >= $today AND group_id != 0 group by group_id";
                        $data = GroupIncomeStatModel::query($sql);

                        foreach ( $data as $resultItem ) {
                            $groupData                              = $groupDividTypaArr[ $resultItem['group_id'] ];
                            $divid_type                             = $groupData['divid_type'];
                            $divid_precent                          = $groupData['divid_precent'];
                            $itemKey                                = $resultItem['group_id'];
                            $resultItem['time_dot_total']           = abs($resultItem['time_dot_total']);
                            $resultItem['time_coin_total']          = abs($resultItem['time_coin_total']);
                            $resultItem['gift_dot_total']           = abs($resultItem['gift_dot_total']);
                            $resultItem['gift_coin_total']          = abs($resultItem['gift_coin_total']);
                            $resultItem['time_total']               = abs($resultItem['time_total']);
                            $resultItem['invite_reward_dot_total']  = abs($resultItem['invite_reward_dot_total']);
                            $resultItem['invite_reward_coin_total'] = abs($resultItem['invite_reward_coin_total']);
                            $resultItem['word_chat_dot_total']      = abs($resultItem['word_chat_dot_total']);
                            $resultItem['word_chat_coin_total']     = abs($resultItem['word_chat_coin_total']);
                            $resultItem['video_dot_total']          = abs($resultItem['video_dot_total']);
                            $resultItem['video_coin_total']         = abs($resultItem['video_coin_total']);
                            $resultItem['guard_dot_total']          = abs($resultItem['guard_dot_total']);
                            $resultItem['chat_game_coin_total']     = abs($resultItem['chat_game_coin_total']);
                            $resultItem['chat_game_total']          = abs($resultItem['chat_game_total']);
                            if ( isset($allSaveArr[ $itemKey ]) ) {
                                if ( $divid_type == 0 ) {
                                    //主播收益分成
                                    $allSaveArr[ $itemKey ]['group_divid_income'] = round(($resultItem['time_dot_total'] + $resultItem['gift_dot_total'] + $resultItem['word_chat_dot_total'] + $resultItem['video_dot_total'] + $resultItem['chat_game_total']) * $divid_precent / 100, 2);
                                } else {
                                    //主播流水分成  还需要除以一个 充值比例转换值 10
                                    $allSaveArr[ $itemKey ]['group_divid_income'] = round(($resultItem['time_coin_total'] + $resultItem['gift_coin_total'] + $resultItem['word_chat_coin_total'] + $resultItem['video_coin_total'] + $resultItem['chat_game_coin_total']) * $divid_precent / 100 / 10, 2);
                                }
                                $allSaveArr[ $itemKey ]['divid_type']           = $divid_type;
                                $allSaveArr[ $itemKey ]['time_total']           = $resultItem['time_total'];
                                $allSaveArr[ $itemKey ]['anchor_time_income']   = $resultItem['time_dot_total'];
                                $allSaveArr[ $itemKey ]['anchor_gift_income']   = $resultItem['gift_dot_total'];
                                $allSaveArr[ $itemKey ]['video_income']         = $resultItem['video_dot_total'];
                                $allSaveArr[ $itemKey ]['word_msg_income']      = $resultItem['word_chat_dot_total'];
                                $allSaveArr[ $itemKey ]['invite_reward_income'] = $resultItem['invite_reward_dot_total'];

                                $allSaveArr[ $itemKey ]['anchor_guard_income'] = $resultItem['guard_dot_total'];
                                $allSaveArr[ $itemKey ]['chat_game_income']    = $resultItem['chat_game_total'];

                                $allSaveArr[ $itemKey ]['group_total_income'] = $allSaveArr[ $itemKey ]['anchor_time_income'] + $allSaveArr[ $itemKey ]['anchor_gift_income'] + $allSaveArr[ $itemKey ]['invite_reward_income'] + $allSaveArr[ $itemKey ]['group_divid_income'] + $allSaveArr[ $itemKey ]['word_msg_income'] + $allSaveArr[ $itemKey ]['video_income'] + $allSaveArr[ $itemKey ]['anchor_guard_income'];
                            } else {
                                $tmp             = $saveDefault;
                                $tmp['group_id'] = $resultItem['group_id'];
                                if ( $divid_type == 0 ) {
                                    //主播收益分成
                                    $tmp['group_divid_income'] = round(($resultItem['time_dot_total'] + $resultItem['gift_dot_total'] + $resultItem['word_chat_dot_total'] + $resultItem['video_dot_total'] + $resultItem['chat_game_total']) * $divid_precent / 100, 2);
                                } else {
                                    //主播流水分成  还需要除以一个 充值比例转换值 10
                                    $tmp['group_divid_income'] = round(($resultItem['time_coin_total'] + $resultItem['gift_coin_total'] + $resultItem['word_chat_coin_total'] + $resultItem['video_dot_total'] + $resultItem['chat_game_coin_total']) * $divid_precent / 100 / 10, 2);
                                }
                                $tmp['divid_type']           = $divid_type;
                                $tmp['time_total']           = $resultItem['time_total'];
                                $tmp['anchor_time_income']   = $resultItem['time_dot_total'];
                                $tmp['anchor_gift_income']   = $resultItem['gift_dot_total'];
                                $tmp['video_income']         = $resultItem['video_dot_total'];
                                $tmp['word_msg_income']      = $resultItem['word_chat_dot_total'];
                                $tmp['invite_reward_income'] = $resultItem['invite_reward_dot_total'];
                                $tmp['anchor_guard_income']  = $resultItem['guard_dot_total'];
                                $tmp['chat_game_income']     = $resultItem['chat_game_total'];
                                $tmp['group_total_income']   = $tmp['anchor_time_income'] + $tmp['anchor_gift_income'] + $tmp['invite_reward_income'] + $tmp['group_divid_income'] + $tmp['video_income'] + $tmp['word_msg_income'] + $tmp['anchor_guard_income'] + $tmp['chat_game_income'];
                                $key                         = $resultItem['group_id'];
                                $allSaveArr[ $key ]          = $tmp;
                            }
                        }
                        $saveAll     = [];
                        $update_time = time();
                        foreach ( $allSaveArr as $allItem ) {
                            ksort($allItem);
                            $allItem[] = $update_time;
                            $saveAll[] = '(' . implode(',', $allItem) . ')';
                        }
                        $valueStr = implode(',', $saveAll);
                        $keyArr   = array_keys($saveDefault);
                        sort($keyArr);
                        $keyArr[]   = 'update_time';
                        $keyStr     = implode(',', $keyArr);
                        $sInsertSql = sprintf('INSERT INTO group_income_stat(%s) VALUES %s;', $keyStr, $valueStr);

                        // 先删除掉之前的
                        GroupIncomeStatModel::destroy($todayWhere);

                        GroupIncomeStatModel::execute($sInsertSql);
                    }

                }
            }
            $this->model = new GroupIncomeStatModel();
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(NULL, TRUE);
            $total  = GroupIncomeStatModel::where('group_income_stat.user_id = 0')->where($where)->with('Group')->order($sort, $order)->count();
            $list   = GroupIncomeStatModel::where('group_income_stat.user_id = 0')->where($where)->with('Group')->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }
        $this->view->assign("row_group", $oGroup);
        return $this->view->fetch();
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
        foreach ( $orderby as $k => $v ) {
            $order[ $v[0] ] = $v[1];
        }
        $field = $field ? $field : 'name';

        //如果有primaryvalue,说明当前是初始化传值
        if ( $primaryvalue ) {
            $where = [
                $primarykey => [
                    'in',
                    $primaryvalue
                ]
            ];
        } else {
            $where = function ( $query ) use ( $word, $andor, $field, $searchfield, $custom ) {
                foreach ( $word as $k => $v ) {
                    foreach ( $searchfield as $m => $n ) {
                        $query->where($n, "like", "%{$v}%", $andor);
                    }
                }
                if ( $custom && is_array($custom) ) {
                    foreach ( $custom as $k => $v ) {
                        $query->where($k, '=', $v);
                    }
                }
            };
        }
        $list  = [];
        $total = $this->model->where($where)->count();
        if ( $total > 0 ) {
            $list = $this->model->where($where)
                ->order($order)
                ->page($page, $pagesize)
                ->field("{$primarykey},{$field}")
                ->field("password,salt", TRUE)
                ->select();
        }

        //这里一定要返回有list这个字段,total是可选的,如果total<=list的数量,则会隐藏分页按钮
        if ( $page == 1 ) {
            array_unshift($list, [
                'id'         => 0,
                'group_name' => '无公会'
            ]);
        }
        $total += 1;
        return json([
            'list'  => $list,
            'total' => $total
        ]);
    }
}