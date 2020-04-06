<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 金额记录控制器                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\user;

use app\models\AnchorLiveLog;
use app\models\UserPrivateChatLog;
use Exception;

use app\models\User;
use app\models\UserLivePay;
use app\models\UserGiftLog;
use app\models\UserVipOrder;
use app\models\UserFinanceLog;
use app\helper\ResponseError;
use app\models\UserConsumeCategory;
use app\models\UserInviteRewardLog;
use app\http\controllers\ControllerBase;


/**
 * AmountrecordController 金额记录控制器
 */
class AmountrecordController extends ControllerBase
{

    /**
     * 金币流水(最近7天)
     */
    public function coinWaterAction($nUserId=0)
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            $start_time = strtotime(date('Y-m-d',strtotime('-7 day')));
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ufl' => UserFinanceLog::class ])
                ->join(User::class, 'u.user_id=ufl.user_id', 'u')
                ->join(UserConsumeCategory::class, 'ucc.consume_category_id=ufl.consume_category_id', 'ucc')
                ->where('ufl.user_id=:user_id: AND ufl.create_time > :create_time:',['user_id' => $nUserId,'create_time' => $start_time])
                ->andWhere('ufl.user_amount_type=:user_amount_type:', [ 'user_amount_type' => UserFinanceLog::AMOUNT_COIN ])
                ->orderBy('ufl.user_finance_log_id desc');

            $row = $this->page($builder->columns('ufl.consume,ucc.consume_category_name,ufl.create_time'), $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * 佣金流水(最近7天)
     */
    public function dotWaterAction($nUserId=0)
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            $start_time = strtotime(date('Y-m-d',strtotime('-7 day')));
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ufl' => UserFinanceLog::class ])
                ->join(User::class, 'u.user_id=ufl.user_id', 'u')
                ->join(UserConsumeCategory::class, 'ucc.consume_category_id=ufl.consume_category_id', 'ucc')
                ->where('ufl.user_id=:user_id: AND ufl.create_time > :create_time:',['user_id' => $nUserId,'create_time' => $start_time])
                ->andWhere('ufl.user_amount_type=:user_amount_type:', [ 'user_amount_type' => UserFinanceLog::AMOUNT_DOT ])
                ->orderBy('ufl.user_finance_log_id desc');

            $row = $this->page($builder->columns('ufl.consume,ucc.consume_category_name,ufl.create_time'), $nPage, $nPagesize);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * giftIncreaseDotAction 礼物收益详情
     *
     * @param  int $nUserId
     */
    public function giftIncreaseDotAction($nUserId = 0)
    {
        $sDateType = $this->getParams('date_type', 'string', 'day');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $date      = $this->getParams('date', 'string', '');
        try {

            switch ( $sDateType ) {
                case 'day':
                    $nTime = strtotime('today');
                    break;

                case 'week':
                    $nTime = strtotime(date('Y-m-d', strtotime('last day this week')));
                    break;

                case 'month':
                    $nTime = strtotime(date('Y-m-01'));
                    break;

                case 'quarter':
                    $nTime = strtotime(date('Y-m-d', strtotime('-3 month')));
                    break;

                case 'all':
                default:
                    $nTime = 0;
                    break;
            }
            if ( !empty($date) ) {
                $nTime = strtotime($date);
            }
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ugl' => UserGiftLog::class ])
                ->join(UserFinanceLog::class, 'ufl.flow_id=ugl.user_gift_log_id', 'ufl')
                ->join(User::class, 'u.user_id=ugl.user_id', 'u')
                ->where('ugl.user_gift_log_status="Y"')
                ->andWhere('ugl.anchor_user_id=:anchor_user_id:', [ 'anchor_user_id' => $nUserId ])
                ->andWhere('ugl.user_gift_log_create_time>=:time:', [ 'time' => $nTime ])
                ->andWhere('ufl.user_id=:user_id:', [ 'user_id' => $nUserId ])
                ->andWhere('ufl.user_amount_type=:user_amount_type:', [ 'user_amount_type' => UserFinanceLog::AMOUNT_DOT ])
                ->andWhere('ufl.consume_category_id in ({consume_category_id:array})', [ 'consume_category_id' => [ UserConsumeCategory::RECEIVE_GIFT_COIN ] ])
                ->orderBy('ugl.user_gift_log_create_time desc');

            $data = $this->page($builder->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,ugl.live_gift_logo,
			ugl.live_gift_name,ugl.live_gift_number,ugl.user_gift_log_create_time time,ufl.consume'), $nPage, $nPagesize);

            $row['record_list']['items'] = [];

            foreach ( $data['items'] as &$v ) {
                $row['record_list']['items'][] = [
                    'user' => [
                        'user_id'       => $v['user_id'],
                        'user_nickname' => $v['user_nickname'],
                        'user_avatar'   => $v['user_avatar'],
                        'user_level'    => $v['user_level'],
                    ],
                    'gift' => [
                        'live_gift_logo'   => $v['live_gift_logo'],
                        'live_gift_name'   => $v['live_gift_name'],
                        'live_gift_number' => $v['live_gift_number'],
                        'time'             => $v['time'],
                        'consume'          => sprintf('%.2f', $v['consume']),
                    ],
                ];
            }

            unset($data['items']);

            $row['record_list'] = array_merge($row['record_list'], $data);

            $row['amount_total'] = $builder->columns('SUM(ufl.consume) as consume')
                ->getQuery()
                ->execute()
                ->getFirst()
                ->consume ?: '0.00';
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * liveIncreaseDotAction 直播收益详情
     *
     * @param  int $nUserId
     */
    public function liveIncreaseDotAction($nUserId = 0)
    {
        $sDateType = $this->getParams('date_type', 'string', 'day');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {

            switch ( $sDateType ) {
                case 'day':
                    $nTime = strtotime('today');
                    break;

                case 'week':
                    $nTime = strtotime(date('Y-m-d', strtotime('last day this week')));
                    break;

                case 'month':
                    $nTime = strtotime(date('Y-m-01'));
                    break;

                case 'quarter':
                    $nTime = strtotime(date('Y-m-d', strtotime('-3 month')));
                    break;

                case 'all':
                default:
                    $nTime = 0;
                    break;
            }


            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ulp' => UserLivePay::class ])
                ->join(UserFinanceLog::class, 'ufl.flow_id=ulp.user_live_pay_id', 'ufl')
                ->join(User::class, 'u.user_id=ulp.user_id', 'u')
                ->where('ulp.user_live_pay_status="Y"')
                ->andWhere('ulp.anchor_user_id=:anchor_user_id:', [ 'anchor_user_id' => $nUserId ])
                ->andWhere('ulp.user_live_pay_create_time>=:time:', [ 'time' => $nTime ])
                ->andWhere('ufl.user_id=:user_id:', [ 'user_id' => $nUserId ])
                ->andWhere('ufl.user_amount_type=:user_amount_type:', [ 'user_amount_type' => UserFinanceLog::AMOUNT_DOT ])
                ->andWhere('ufl.consume_category_id in ({consume_category_id:array})', [
                    'consume_category_id' => [
                        UserConsumeCategory::TIME_COIN,
                        UserConsumeCategory::FARE_COIN
                    ]
                ])
                ->orderBy('ulp.user_live_pay_create_time desc');

            $data = $this->page($builder->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,ulp.anchor_live_pay,ulp.user_live_pay_create_time time,ufl.consume'), $nPage, $nPagesize);

            $row['record_list']['items'] = [];

            foreach ( $data['items'] as &$v ) {
                $nAnchorLivePay = $v['anchor_live_pay'];

                switch ( $nAnchorLivePay ) {

                    case 2:
                        $nAnchorLivePay = '1';
                        break;


                    default:
                        $nAnchorLivePay = '2';
                        break;
                }

                $row['record_list']['items'][] = [
                    'user' => [
                        'user_id'       => $v['user_id'],
                        'user_nickname' => $v['user_nickname'],
                        'user_avatar'   => $v['user_avatar'],
                        'user_level'    => $v['user_level'],
                    ],
                    'live' => [
                        'anchor_live_pay' => $nAnchorLivePay,
                        'time'            => $v['time'],
                        'consume'         => sprintf('%.2f', $v['consume']),
                    ],
                ];
            }

            unset($data['items']);

            $row['record_list'] = array_merge($row['record_list'], $data);

            $row['amount_total'] = $builder->columns('SUM(ufl.consume) as consume')
                ->getQuery()
                ->execute()
                ->getFirst()
                ->consume;
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * inviteIncreaseCoinAction 邀请收益详情
     * TODO
     * @param  int $nUserId
     */
    public function inviteIncreaseCoinAction($nUserId = 0)
    {
        $sDateType = $this->getParams('date_type', 'string', 'all');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {

            switch ( $sDateType ) {
                case 'day':
                    $nTime = strtotime('today');
                    break;

                case 'week':
                    $nTime = strtotime(date('Y-m-d', strtotime('last day this week')));
                    break;

                case 'month':
                    $nTime = strtotime(date('Y-m-01'));
                    break;

                case 'quarter':
                    $nTime = strtotime(date('Y-m-d', strtotime('-3 month')));
                    break;

                case 'all':
                default:
                    $nTime = 0;
                    break;
            }

            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'i' => UserInviteRewardLog::class ])
                ->join(User::class, 'u.user_id=i.user_id', 'u')
                ->where('i.parent_user_id=:parent_user_id:', [ 'parent_user_id' => $nUserId ])
                ->andWhere('i.user_invite_reward_log_create_time>=:time:', [ 'time' => $nTime ])
                ->orderBy('i.user_invite_reward_log_create_time desc');

            $data = $this->page($builder->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,i.recharge_invite_coin consume,
			i.user_invite_reward_log_create_time time,i.invite_level,i.user_invite_reward_type reward_type'), $nPage, $nPagesize);

            $row['record_list']['items'] = [];

            foreach ( $data['items'] as &$v ) {

                switch ( $v['reward_type'] ) {
                    case 'recharge':
                    default:
                        $sRewardType = '充值';
                        break;
                }
                $row['record_list']['items'][] = [
                    'user'   => [
                        'user_id'       => $v['user_id'],
                        'user_nickname' => $v['user_nickname'],
                        'user_avatar'   => $v['user_avatar'],
                        'user_level'    => $v['user_level'],
                        'invite_level'  => $v['invite_level'],
                    ],
                    'invite' => [
                        'time'        => $v['time'],
                        'consume'     => sprintf('%.2f', $v['consume']),
                        'reward_type' => $sRewardType,
                    ],
                ];
            }

            unset($data['items']);
            $row['record_list'] = array_merge($row['record_list'], $data);

            $row['amount_total'] = $builder->columns('SUM(i.recharge_invite_coin) as consume')
                ->getQuery()
                ->execute()
                ->getFirst()
                ->consume ?: '0.00';
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * giftDecreaseCoinAction 送礼消费详情
     *
     * @param  string $nUserId
     */
    public function giftDecreaseCoinAction($nUserId = '')
    {
        $sDateType = $this->getParams('date_type', 'string', 'all');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $date      = $this->getParams('date', 'string', '');
        try {

            switch ( $sDateType ) {
                case 'day':
                    $nTime = strtotime('today');
                    break;

                case 'week':
                    $nTime = strtotime(date('Y-m-d', strtotime('last day this week')));
                    break;

                case 'month':
                    $nTime = strtotime(date('Y-m-01'));
                    break;

                case 'quarter':
                    $nTime = strtotime(date('Y-m-d', strtotime('-3 month')));
                    break;

                case 'all':
                default:
                    $nTime = 0;
                    break;
            }
            if ( !empty($date) ) {
                $nTime = strtotime($date);
            }
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ugl' => UserGiftLog::class ])
                ->join(UserFinanceLog::class, 'ufl.flow_id=ugl.user_gift_log_id', 'ufl')
                ->join(User::class, 'u.user_id=ugl.anchor_user_id', 'u')
                ->where('ugl.user_gift_log_status="Y"')
                ->andWhere('ugl.user_id=:user_id:', [ 'user_id' => $nUserId ])
                ->andWhere('ugl.user_gift_log_create_time>=:time:', [ 'time' => $nTime ])
                ->andWhere('ufl.user_id=:user_id:', [ 'user_id' => $nUserId ])
                ->andWhere('ufl.user_amount_type=:user_amount_type:', [ 'user_amount_type' => UserFinanceLog::AMOUNT_COIN ])
                ->andWhere('ufl.consume_category_id in ({consume_category_id:array})', [ 'consume_category_id' => [ UserConsumeCategory::SEND_GIFT_COIN ] ])
                ->orderBy('ugl.user_gift_log_create_time desc');

            $data = $this->page($builder->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,ugl.live_gift_logo,
			ugl.live_gift_name,ugl.live_gift_number,ugl.user_gift_log_create_time time,ufl.consume'), $nPage, $nPagesize);

            $row['record_list']['items'] = [];

            foreach ( $data['items'] as &$v ) {
                $row['record_list']['items'][] = [
                    'anchor' => [
                        'user_id'       => $v['user_id'],
                        'user_nickname' => $v['user_nickname'],
                        'user_avatar'   => $v['user_avatar'],
                        'user_level'    => $v['user_level'],
                    ],
                    'gift'   => [
                        'live_gift_logo'   => $v['live_gift_logo'],
                        'live_gift_name'   => $v['live_gift_name'],
                        'live_gift_number' => $v['live_gift_number'],
                        'time'             => $v['time'],
                        'consume'          => sprintf('%.2f', $v['consume']),
                    ],
                ];
            }

            unset($data['items']);

            $row['record_list'] = array_merge($row['record_list'], $data);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * chatConsumeCoinAction 私聊消费详情
     *
     * @param  int $nUserId
     */
    public function chatConsumeCoinAction($nUserId = 0)
    {
        $sDateType = $this->getParams('date_type', 'string', 'all');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $date      = $this->getParams('date', 'string');

        try {

            switch ( $sDateType ) {
                case 'day':
                    $nTime = strtotime('today');
                    break;

                case 'week':
                    $nTime = strtotime(date('Y-m-d', strtotime('last day this week')));
                    break;

                case 'month':
                    $nTime = strtotime(date('Y-m-01'));
                    break;

                case 'quarter':
                    $nTime = strtotime(date('Y-m-d', strtotime('-3 month')));
                    break;

                case 'all':
                default:
                    $nTime = 0;
                    break;
            }
            if ( !empty($date) ) {
                $nTime = strtotime($date);
            }

            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ufl' => UserFinanceLog::class ])
                ->join(UserPrivateChatLog::class, 'up.id = ufl.flow_id', 'up')
                ->join(User::class, 'u.user_id=up.chat_log_anchor_user_id', 'u')
                ->where('ufl.user_id=:user_id:', [ 'user_id' => $nUserId ])
                ->andWhere('ufl.create_time>=:time:', [ 'time' => $nTime ])
                ->andWhere("ufl.user_amount_type='coin'")
                ->andWhere('ufl.consume_category_id =:consume_category_id:', [ 'consume_category_id' => UserConsumeCategory::PRIVATE_CHAT ])
                ->orderBy('ufl.create_time desc');
            $row     = $this->page($builder->columns('u.user_nickname,ufl.create_time time,ABS(ufl.consume) as consume'), $nPage, $nPagesize);

            $this->success($row);
        } catch ( Exception $e ) {

            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * chatConsumeDotAction 私聊收益详情
     *
     * @param  int $nUserId
     */
    public function chatConsumeDotAction($nUserId = 0)
    {
        $sDateType = $this->getParams('date_type', 'string', 'all');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $date      = $this->getParams('date', 'string');
        try {

            switch ( $sDateType ) {
                case 'day':
                    $nTime = strtotime('today');
                    break;

                case 'week':
                    $nTime = strtotime(date('Y-m-d', strtotime('last day this week')));
                    break;

                case 'month':
                    $nTime = strtotime(date('Y-m-01'));
                    break;

                case 'quarter':
                    $nTime = strtotime(date('Y-m-d', strtotime('-3 month')));
                    break;

                case 'all':
                default:
                    $nTime = 0;
                    break;
            }
            if ( !empty($date) ) {
                $nTime = strtotime($date);
            }

            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ufl' => UserFinanceLog::class ])
                ->join(UserPrivateChatLog::class, 'up.id = ufl.flow_id', 'up')
                ->join(User::class, 'u.user_id=up.chat_log_user_id', 'u')
                ->where('ufl.user_id=:user_id:', [ 'user_id' => $nUserId ])
                ->andWhere('ufl.create_time>=:time:', [ 'time' => $nTime ])
                ->andWhere("ufl.user_amount_type='dot'")
                ->andWhere('ufl.consume_category_id =:consume_category_id:', [ 'consume_category_id' => UserConsumeCategory::PRIVATE_CHAT ])
                ->orderBy('ufl.create_time desc');
            $row     = $this->page($builder->columns('u.user_nickname,ufl.create_time time,ABS(ufl.consume) as consume'), $nPage, $nPagesize);

            $row['amount_total'] = $builder->columns('ABS(SUM(ufl.consume)) as consume')
                ->getQuery()
                ->execute()
                ->getFirst()
                ->consume ?: '0.00';
            $this->success($row);
        } catch ( Exception $e ) {

            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }
}