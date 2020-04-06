<?php

namespace app\live\controller\general;

use app\common\controller\Backend;
use app\live\library\Redis;
use app\live\model\live\Kv;


/**
 * APP版本管理
 *
 */
class Levelconfig extends Backend
{

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.level_config');
    }

    /**
     * index 列表
     */
    public function index()
    {
        if ( $this->request->isAjax() ) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams(null);
            $total = $this->model->where($where)->order($sort, $order)->count();
            $list  = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();

            $changeData = [];
            foreach ( $list as $listItem ) {
                $tmp          = $listItem->toArray();
                $anchorColor  = '';
                $maxChatPrice = '';
                if ( $tmp['level_type'] == 'anchor' ) {
                    $tmpExtra     = unserialize($tmp['level_extra']);
                    $anchorColor  = $tmpExtra['anchor_color'];
                    $maxChatPrice = $tmpExtra['max_chat_price'];
                }
                $tmp['anchor_color']   = $anchorColor;
                $tmp['max_chat_price'] = $maxChatPrice;
                $changeData[] = $tmp;
            }
            $result = [
                "total" => $total,
                "rows"  => $changeData
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
     * 自动添加等级
     * 等级所需经验不能小于上一级
     */
    public function add()
    {

        if ( $this->request->isPost() ) {
            $params     = $this->request->param('row/a');
            $lastConfig = \app\live\model\live\LevelConfig::where('level_type', $params['level_type'])->order('level_value', 'desc')->find();
            $lastLevel  = 0;
            $lastMinExp = 0;
            if ( $lastConfig ) {
                $lastLevel  = $lastConfig->level_value + 1;
                $lastMinExp = $lastConfig->level_exp + 1;
            }
            $params['level_value'] = $lastLevel;

            $row              = new $this->model();
            $row->level_type  = $params['level_type'];
            $row->level_name  = $params['level_name'];
            $row->level_value = $params['level_value'];
            $row->level_exp   = $params['level_exp'];
            switch ( $row->level_type ){
                case 'user':
                    $row->level_extra = $params['level_extra'];
                    $row->reward_coin = $params['reward_coin'];
                    break;
                case 'anchor':
                    if ( $params['max_chat_price'] < Kv::getValue(Kv::PRIVATE_PRICE_MIN) ) {
                        $this->error('价格不能低于最小值');
                    }
                    if ( $params['max_chat_price'] % 10 != 0 ) {
                        $this->error('价格必须为10的整数倍');
                    }
                    $row->level_extra = serialize([
                        'anchor_color'   => $params['anchor_color'],
                        'max_chat_price' => $params['max_chat_price']
                    ]);
                    break;
                case 'guard':
                    $row->level_extra = $params['level_extra_guard'];
                    break;
                case 'intimate':
                    $row->level_extra = $params['level_intimate_chat_flg'];
                    break;
                default:
                    $this->error('类型错误');
            }

            $ruleFields = [
                'level_type'  => 'require',
                'level_name'  => 'require',
                'level_value' => 'require',
                'level_exp'   => 'require|gt:' . $lastMinExp,
            ];

            $ruleContent = [
                'level_type.require'  => __('Parameter %s can not be empty', [ 'level_type' ]),
                'level_name.require'  => __('Parameter %s can not be empty', [ 'level_name' ]),
                'level_value.require' => __('Parameter %s can not be empty', [ 'level_value' ]),
                'level_exp.require'   => __('Parameter %s can not be empty', [ 'level_exp' ]),
                'level_exp.gt'        => __('经验') . __('Must be greater than %d', $lastMinExp),
            ];

            if ( $row->level_value == 1 ) {
                unset($ruleFields['level_exp']);
                unset($ruleContent['level_exp.gt']);
            }

            $row->validate(
                $ruleFields,
                $ruleContent
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $oRedis = new Redis(\think\Config::get('redis'));
            $oRedis->delete('_PHCRcaches_level_config:' . $row->level_type);
            $this->success();
        }


        $lastGuardConfig    = \app\live\model\live\LevelConfig::where('level_type', 'guard')->order('level_value', 'desc')->find();
        $lastIntimateConfig = \app\live\model\live\LevelConfig::where('level_type', 'intimate')->order('level_value', 'desc')->find();
        $lastUserConfig     = \app\live\model\live\LevelConfig::where('level_type', 'user')->order('level_value', 'desc')->find();
        $lastAnchorConfig   = \app\live\model\live\LevelConfig::where('level_type', 'anchor')->order('level_value', 'desc')->find();
        $this->view->assign('last_guard_config', $lastGuardConfig);
        $this->view->assign('last_intimate_config', $lastIntimateConfig);
        $this->view->assign('last_user_config', $lastUserConfig);
        $this->view->assign('last_anchor_config', $lastAnchorConfig);
        $this->view->assign('private_price_min', Kv::getValue(Kv::PRIVATE_PRICE_MIN));
        return $this->view->fetch();
    }

    /**
     * edit 编辑
     * 不能修改等级值
     * 等级经验不能低于上一级 或高于下一级
     */
    public function edit($ids = '')
    {
        $row = $this->model::get($ids);

        if ( !$row )
            $this->error(__('No Results were found'));

        if ( $this->request->isPost() ) {
            $params  = $this->request->param('row/a');
            $min_exp = 0;
            $expRule = 'require|egt:' . $min_exp;
            if ( $row->level_value > 1 ) {
                $lastConfig = \app\live\model\live\LevelConfig::where('level_type', $row->level_type)->where('level_value', '<=', $row->level_value - 1)->order('level_value desc')->find();
                if ( $lastConfig ) {
                    $min_exp = $lastConfig->level_exp + 1;
                    $expRule = 'require|gt:' . $min_exp;
                }
            }
            $nextConfig = $lastConfig = \app\live\model\live\LevelConfig::where('level_type', $row->level_type)->where('level_value', '>', $row->level_value)->order('level_value asc')->find();
            $rules = [
                'level_name.require' => __('Parameter %s can not be empty', [ 'level_name' ]),
                'level_exp.require'  => __('Parameter %s can not be empty', [ 'level_exp' ]),
                'level_exp.gt'       => __('经验') . __('Must be greater than %d', $min_exp),
            ];
            if ( $nextConfig ) {
                $max_exp = $nextConfig->level_exp - 1;
                if($row->level_value <= 1){
                    $expRule = 'require|egt:' . $min_exp . '|lt:' . $max_exp;
                }else{
                    $expRule = 'require|gt:' . $min_exp . '|lt:' . $max_exp;
                }
                $rules   = [
                    'level_name.require' => __('Parameter %s can not be empty', [ 'level_name' ]),
                    'level_exp.require'  => __('Parameter %s can not be empty', [ 'level_exp' ]),
                    'level_exp.gt'       => __('经验') . __('Must be greater than %d', $min_exp),
                    'level_exp.lt'       => __('经验') . __('Must be less than %d', $max_exp),
                ];
            }
            $row->level_name = $params['level_name'];
            $row->level_exp  = $params['level_exp'];

            switch ( $row->level_type ){
                case 'user':
                    $row->level_extra = $params['level_extra'];
                    $row->reward_coin = $params['reward_coin'];
                    break;
                case 'anchor':
                    if ( $params['max_chat_price'] < Kv::getValue(Kv::PRIVATE_PRICE_MIN) ) {
                        $this->error('价格不能低于最小值');
                    }
                    if ( $params['max_chat_price'] % 10 != 0 ) {
                        $this->error('价格必须为10的整数倍');
                    }
                    $row->level_extra = serialize([
                        'anchor_color'   => $params['anchor_color'],
                        'max_chat_price' => $params['max_chat_price']
                    ]);
                    break;
                case 'guard':
                    $row->level_extra = $params['level_extra_guard'];
                    break;
                case 'intimate':
                    $row->level_extra = $params['level_intimate_chat_flg'];
                    break;
                default:
                    $this->error('类型错误');
            }
            $row->validate(
                [
                    'level_name' => 'require',
                    'level_exp'  => $expRule,
                ],
                $rules
            );

            if ( $row->save($row->getData()) === FALSE ) {
                $this->error($row->getError());
            }
            $oRedis = new Redis(\think\Config::get('redis'));
            $oRedis->delete('_PHCRcaches_level_config:' . $row->level_type);
            $this->success();
        }
        $row->anchor_color   = '';
        $row->max_chat_price = '';
        if ( $row->level_type == 'anchor' ) {
            $tmpExtra            = unserialize($row->level_extra);
            $row->anchor_color   = $tmpExtra['anchor_color'] ?? '';
            $row->max_chat_price = $tmpExtra['max_chat_price'] ?? '';
        }
        $this->view->assign('private_price_min', Kv::getValue(Kv::PRIVATE_PRICE_MIN));
        $this->view->assign('row', $row);
        return $this->view->fetch();

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
     * @param  string $ids
     */
    public function multi($ids = '')
    {

    }
}