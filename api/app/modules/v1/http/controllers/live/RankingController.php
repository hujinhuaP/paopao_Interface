<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 |房间控制器                                                              |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use app\models\BuyHammerLog;
use app\models\Kv;
use app\models\LevelConfig;
use app\models\UserGiftLog;
use app\models\UserIntimate;
use app\services\ActivityUserService;
use app\services\AnchorTodayDotService;
use app\services\IntimateService;
use Exception;

use app\models\User;
use app\models\UserFollow;
use app\models\UserGiftRank;
use app\http\controllers\ControllerBase;

/**
 * RankingController 直播排行榜
 */
class RankingController extends ControllerBase
{
    /**
     * anchorFansAction 主播粉丝贡献榜
     *
     * @param int $nUserId
     */
    public function anchorFansAction( $nUserId = 0 )
    {
        $nAnchorUserId = $this->getParams('anchor_user_id', 'int', 0);
        $nPage         = $this->getParams('page', 'int', 0);
        $nPagesize     = $this->getParams('pagesize', 'int', 10);
        $sType         = $this->getParams('type', 'string', 'day');

        try {
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ur' => UserGiftRank::class ])
                ->join(User::class, 'u.user_id=ur.user_id', 'u')
                ->columns('ur.user_id,MAX(u.user_nickname) user_nickname,MAX(u.user_avatar) user_avatar,SUM(ur.live_gift_coin) live_gift_coin,SUM(ur.live_gift_dot) live_gift_dot')
                ->where('ur.anchor_user_id=:anchor_user_id:', [ 'anchor_user_id' => $nAnchorUserId ])
                ->groupBy('ur.user_id')
                ->orderBy('live_gift_dot desc');

            switch ( $sType ) {
                // 日榜
                case UserGiftRank::RANK_DAY:
                    // 日榜
                case UserGiftRank::RANK_WEEK:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => $sType ]);
                    break;
                // 总榜
                default:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => UserGiftRank::RANK_ALL ]);
                    break;
            }

            $row['rank'] = $this->page($builder, $nPage, $nPagesize);

            foreach ( $row['rank']['items'] as &$v ) {
                $v['live_gift_coin'] = sprintf('%.2f', $v['live_gift_coin']);
                $v['live_gift_dot']  = sprintf('%.2f', $v['live_gift_dot']);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    public function anchorOldAction( $nUserId = 0 )
    {
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 10);
        $sType     = $this->getParams('type', 'string', 'week');
        try {
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ur' => UserGiftRank::class ])
                ->join(User::class, 'u.user_id=ur.anchor_user_id', 'u')
                ->leftJoin(UserFollow::class, sprintf('uf.to_user_id=ur.anchor_user_id AND uf.user_id=%d', $nUserId), 'uf')
                ->columns('ur.anchor_user_id user_id,
                            MAX(u.user_nickname) user_nickname,MAX(u.user_avatar) user_avatar,SUM(ur.live_gift_coin) live_gift_coin,MAX(u.user_member_expire_time) user_member_expire_time,
                            SUM(ur.live_gift_dot) live_gift_dot,MAX(uf.to_user_id) is_follow')
                ->groupBy('ur.anchor_user_id')
                ->orderBy('live_gift_dot desc');

            $sDeadline = '0';

            switch ( $sType ) {
                // 日榜
                case UserGiftRank::RANK_DAY:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => $sType ]);
                    $sDeadline = (string)strtotime(date('Y-m-d 23:59:59'));
                    break;
                // 周榜
                case UserGiftRank::RANK_WEEK:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => $sType ]);
                    $sDeadline = (string)strtotime(date('Y-m-d 23:59:59', strtotime('last day next week')));
                    break;
                // 总榜
                default:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => UserGiftRank::RANK_ALL ]);
                    break;
            }

            $row['rank'] = $this->page($builder, $nPage, $nPagesize);

            foreach ( $row['rank']['items'] as &$v ) {
                $v['user_is_member'] = $v['user_member_expire_time'] == 0 ? 'N' : (time() > $v['user_member_expire_time'] ? 'O' : 'Y');
                $v['is_follow']      = $v['is_follow'] ? 'Y' : 'N';
                $v['live_gift_coin'] = sprintf('%.2f', $v['live_gift_coin']);
                $v['live_gift_dot']  = sprintf('%.2f', $v['live_gift_dot']) * 100;
            }

            // 截止时间
            $row['deadline'] = $sDeadline;

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    public function testAction( $nUserId = 0 )
    {
        $this->success([
            'action' => 'test'
        ]);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/ranking/anchor
     * @api {get} /live/ranking/anchor 主播魅力榜
     * @apiName ranking-anchor
     * @apiGroup Rank
     * @apiDescription 主播魅力榜
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {Object} d.rank 排行榜数据
     * @apiSuccess {Object} d.items
     * @apiSuccess {string} d.rank.items.user_id 用户id
     * @apiSuccess {string} d.rank.items.user_nickname 用户昵称
     * @apiSuccess {string} d.rank.items.user_avatar 用户头像
     * @apiSuccess {string} d.rank.items.user_member_expire_time 会员过期时间
     * @apiSuccess {string} d.rank.items.user_is_member 是否会员
     * @apiSuccess {string} d.rank.items.live_gift_dot 显示收益
     * @apiSuccess {string} d.rank.items.is_follow 是否关注
     * @apiSuccess {string} d.rank.items.anchor_level 主播等级
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "rank": {
     *                     "items": [{
     *                         "user_id": 243,
     *                         "user_nickname": "Boy",
     *                         "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTJlmn85HrFGz1B21Km5jahAsQ6aDLNK0G8JbVv3udLUl3FlVb4ic1bHOVibfibOKUC4y4ibWiaMMGdbLVQ\/132",
     *                         "user_member_expire_time": "0",
     *                         "user_is_member": "N",
     *                         "live_gift_dot": 32008,
     *                         "is_follow": "N",
     *                         "anchor_level": "1"
     *                 }],
     *                 "page": 1,
     *                 "pagesize": 10,
     *                 "pagetotal": 1,
     *                 "total": 10,
     *                 "prev": 1,
     *                 "next": 1
     *             },
     *             "deadline": "1556467199"
     *         },
     *         "t": "1556075397"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function anchorAction( $nUserId = 0 )
    {

        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 10);
        $sType     = $this->getParams('type', 'string', 'week');

        try {
            $sDeadline              = (string)strtotime(date('Y-m-d 23:59:59', strtotime('last day next week')));
            $oAnchorTodayDotService = new AnchorTodayDotService(0);
            $resultJson             = $oAnchorTodayDotService->getCacheData();
            if ( $resultJson ) {
                $result    = json_decode($resultJson, TRUE);
                $rankItems = $result['rank']['items'];
                if ( $rankItems ) {
                    // 如果存在 则获取当前的排名中的用户id
                    $userIds    = array_column($rankItems, 'user_id');
                    $userIdsStr = implode(',', $userIds);
                    // 取出来后 判断是否关注
                    // 判断用户是否关注了这些用户
                    $oUserFollow     = UserFollow::find([
                        'user_id = :user_id: AND to_user_id in (' . $userIdsStr . ')',
                        'bind' => [
                            'user_id' => $nUserId
                        ]
                    ]);
                    $oUserFollowList = [];
                    foreach ( $oUserFollow as $userFollowItem ) {
                        $oUserFollowList[] = $userFollowItem->to_user_id;
                    }

                    foreach ( $rankItems as &$rankItem ) {
                        $rankItem['is_follow'] = in_array($rankItem['user_id'], $oUserFollowList) ? 'Y' : 'N';
                    }
                    $result['rank']['items'] = $rankItems;
                }
                $result['deadline'] = $sDeadline;
                $this->success($result);
            }
            $rankSource = $oAnchorTodayDotService->getIncomeData(9);
            $result     = [
                'rank'     => [
                    'items'     => [],
                    'page'      => 1,
                    'pagesize'  => 10,
                    'pagetotal' => 1,
                    'total'     => 10,
                    'prev'      => 1,
                    'next'      => 1
                ],
                'deadline' => $sDeadline
            ];
            $userIds    = [];
            foreach ( $rankSource as $dataKey => $dataScore ) {
                $userIds[] = $dataKey;
            }
            if ( !$userIds ) {
                $this->success($result);
            }
            $userIdsStr = implode(',', $userIds);
//            $oUserListModel = User::find([
//                "user_id in ($userIdsStr)",
//                'columns' => 'user_id,user_nickname,user_avatar,user_level,user_sex,user_member_expire_time',
//            ]);
            $userAnchorInfoSql   = "select u.user_id,u.user_nickname,u.user_avatar,u.user_level,u.user_sex,
            u.user_member_expire_time,a.anchor_level FROM user as u INNER JOIN anchor as a ON u.user_id = a.user_id
            where u.user_id in({$userIdsStr})";
            $userAnchorInfResult = $this->db->query($userAnchorInfoSql);
            $userAnchorInfResult->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            $oUserListModel = $userAnchorInfResult->fetchAll();
            // 获得用户信息
            $oUserList = [];
            foreach ( $oUserListModel as $userItem ) {
//                $oUserList[$userItem->user_id] = $userItem->toArray();
                $oUserList[ $userItem['user_id'] ] = $userItem;
            }

            // 判断用户是否关注了这些用户
            $oUserFollow     = UserFollow::find([
                'user_id = :user_id: AND to_user_id in (' . $userIdsStr . ')',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            $oUserFollowList = [];
            foreach ( $oUserFollow as $userFollowItem ) {
                $oUserFollowList[] = $userFollowItem->to_user_id;
            }

            $rankItems = [];
            foreach ( $rankSource as $dataKey => $dataScore ) {
                $rankItems[] = [
                    'user_id'                 => $dataKey,
                    'user_nickname'           => $oUserList[ $dataKey ]['user_nickname'],
                    'user_avatar'             => $oUserList[ $dataKey ]['user_avatar'],
                    'user_member_expire_time' => $oUserList[ $dataKey ]['user_member_expire_time'],
                    'user_is_member'          => $oUserList[ $dataKey ]['user_member_expire_time'] > time() ? 'Y' : 'N',
                    'live_gift_dot'           => $dataScore * Kv::get(Kv::DOT_TO_ANCHOR_EXP),
                    'is_follow'               => in_array($dataKey, $oUserFollowList) ? 'Y' : 'N',
                    'anchor_level'            => $oUserList[ $dataKey ]['anchor_level'],
                ];
            }

            $result = [
                'rank'     => [
                    'items'     => $rankItems,
                    'page'      => 1,
                    'pagesize'  => 10,
                    'pagetotal' => 1,
                    'total'     => 10,
                    'prev'      => 1,
                    'next'      => 1
                ],
                'deadline' => $sDeadline
            ];
            $oAnchorTodayDotService->saveCacheData(json_encode($result));

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($result);
    }

    /**
     * anchorAction 用户排行榜
     * 现在只有周榜
     * @param int $nUserId
     */
    public function userAction( $nUserId = 0 )
    {
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 10);
        $sType     = $this->getParams('type', 'string', 'week');

        try {
            $sDeadline            = (string)strtotime(date('Y-m-d 23:59:59', strtotime('last day next week')));
            $oActivityUserService = new ActivityUserService();
            $resultJson           = $oActivityUserService->getCacheData();
            if ( $resultJson ) {
                $result    = json_decode($resultJson, TRUE);
                $rankItems = $result['rank']['items'];
                if ( $rankItems ) {
                    // 如果存在 则获取当前的排名中的用户id
                    $userIds    = array_column($rankItems, 'user_id');
                    $userIdsStr = implode(',', $userIds);
                    // 取出来后 判断是否关注
                    // 判断用户是否关注了这些用户
                    $oUserFollow     = UserFollow::find([
                        'user_id = :user_id: AND to_user_id in (' . $userIdsStr . ')',
                        'bind' => [
                            'user_id' => $nUserId
                        ]
                    ]);
                    $oUserFollowList = [];
                    foreach ( $oUserFollow as $userFollowItem ) {
                        $oUserFollowList[] = $userFollowItem->to_user_id;
                    }

                    foreach ( $rankItems as &$rankItem ) {
                        $rankItem['is_follow'] = in_array($rankItem['user_id'], $oUserFollowList) ? 'Y' : 'N';
                    }
                    $result['rank']['items'] = $rankItems;
                }
                $result['deadline'] = $sDeadline;
                $this->success($result);
            }
            $rankSource = $oActivityUserService->getPayData(9);
            $result     = [
                'rank'     => [
                    'items'     => [],
                    'page'      => 1,
                    'pagesize'  => 10,
                    'pagetotal' => 1,
                    'total'     => 10,
                    'prev'      => 1,
                    'next'      => 1
                ],
                'deadline' => $sDeadline
            ];
            $userIds    = [];
            foreach ( $rankSource as $dataKey => $dataScore ) {
                $userIds[] = $dataKey;
            }
            if ( !$userIds ) {
                $this->success($result);
            }
            $userIdsStr     = implode(',', $userIds);
            $oUserListModel = User::find([
                "user_id in ($userIdsStr)",
                'columns' => 'user_id,user_nickname,user_avatar,user_level,user_sex,user_member_expire_time,user_remind',
            ]);
            // 获得用户信息
            $oUserList = [];
            foreach ( $oUserListModel as $userItem ) {
                $oUserList[ $userItem->user_id ] = $userItem->toArray();
            }

            // 判断用户是否关注了这些用户
            $oUserFollow     = UserFollow::find([
                'user_id = :user_id: AND to_user_id in (' . $userIdsStr . ')',
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            $oUserFollowList = [];
            foreach ( $oUserFollow as $userFollowItem ) {
                $oUserFollowList[] = $userFollowItem->to_user_id;
            }

            $rankItems = [];
            foreach ( $rankSource as $dataKey => $dataScore ) {
                if ( !$oUserList[ $dataKey ]['user_remind'] ) {
                    $oUserList[ $dataKey ]['user_nickname'] = '神秘人';
                    $oUserList[ $dataKey ]['user_avatar']   = 'https://lebolive-1255651273.image.myqcloud.com/static/images/hide_avatar.png';
                }
                $rankItems[] = [
                    'user_id'                 => $dataKey,
                    'user_nickname'           => $oUserList[ $dataKey ]['user_nickname'],
                    'user_avatar'             => $oUserList[ $dataKey ]['user_avatar'],
                    'user_level'              => $oUserList[ $dataKey ]['user_level'],
                    'user_member_expire_time' => $oUserList[ $dataKey ]['user_member_expire_time'],
                    'user_is_member'          => $oUserList[ $dataKey ]['user_member_expire_time'] > time() ? 'Y' : 'N',
                    'live_gift_coin'          => $dataScore,
                    'is_follow'               => in_array($dataKey, $oUserFollowList) ? 'Y' : 'N',
                    'user_remind'             => $oUserList[ $dataKey ]['user_remind'],
                ];
            }

            $result = [
                'rank'     => [
                    'items'     => $rankItems,
                    'page'      => 1,
                    'pagesize'  => 10,
                    'pagetotal' => 1,
                    'total'     => 10,
                    'prev'      => 1,
                    'next'      => 1
                ],
                'deadline' => $sDeadline
            ];
            $oActivityUserService->saveCacheData(json_encode($result));

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($result);
    }


    /**
     * userAction 用户排行榜
     *
     * @param int $nUserId
     */
    public function userOldAction( $nUserId = 0 )
    {
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 10);
        $sType     = $this->getParams('type', 'string', 'week');
        try {
            $builder = $this->modelsManager
                ->createBuilder()
                ->from([ 'ur' => UserGiftRank::class ])
                ->join(User::class, 'u.user_id=ur.user_id', 'u')
                ->columns('u.user_remind,u.user_id,MAX(u.user_nickname) user_nickname,MAX(u.user_avatar) user_avatar,MAX(u.user_level) user_level,
                            MAX(u.user_member_expire_time) user_member_expire_time,
                            SUM(ur.live_gift_coin) live_gift_coin,SUM(ur.live_gift_dot) live_gift_dot')
                ->groupBy('ur.user_id')
                ->orderBy('live_gift_coin desc');

            $sDeadline = '0';

            switch ( $sType ) {
                // 日榜
                case UserGiftRank::RANK_DAY:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => $sType ]);
                    $sDeadline = (string)strtotime(date('Y-m-d 23:59:59'));
                    break;
                // 周榜
                case UserGiftRank::RANK_WEEK:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => $sType ]);
                    $sDeadline = (string)strtotime(date('Y-m-d 23:59:59', strtotime('last day next week')));
                    break;
                // 总榜
                default:
                    $builder->andWhere('ur.user_gift_rank_type=:type:', [ 'type' => UserGiftRank::RANK_ALL ]);
                    break;
            }

            $row['rank'] = $this->page($builder, $nPage, $nPagesize);

            foreach ( $row['rank']['items'] as &$v ) {
                if ( !$v['user_remind'] ) {
                    $v['user_nickname'] = '神秘人';
                    $v['user_avatar']   = User::SECRET_AVATAR;
                }

                $v['user_is_member'] = $v['user_member_expire_time'] == 0 ? 'N' : (time() > $v['user_member_expire_time'] ? 'O' : 'Y');
                $v['live_gift_coin'] = sprintf('%.2f', $v['live_gift_coin']);
                $v['live_gift_dot']  = sprintf('%.2f', $v['live_gift_dot']);
            }

            // 截止时间
            $row['deadline'] = $sDeadline;

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }

        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/ranking/intimate
     * @api {get} /live/ranking/intimate 亲密榜
     * @apiName rank-intimate
     * @apiGroup rank
     * @apiDescription 亲密榜  1分钟更新一次
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {number} d.deadline 截止时间
     * @apiSuccess {object[]} d.rank 排行榜
     * @apiSuccess {object} d.rank.anchor  排行榜 主播信息
     * @apiSuccess {number} d.rank.anchor.user_id  主播用户id
     * @apiSuccess {String} d.rank.anchor.user_nickname 主播昵称
     * @apiSuccess {String} d.rank.anchor.user_avatar 主播头像
     * @apiSuccess {number} d.rank.anchor.user_sex 主播性别
     * @apiSuccess {object} d.rank.user   用户信息
     * @apiSuccess {number} d.rank.user.user_id 用户id
     * @apiSuccess {String} d.rank.user.user_nickname 昵称
     * @apiSuccess {String} d.rank.user.user_avatar 头像
     * @apiSuccess {number} d.rank.user.user_sex 性别
     * @apiSuccess {number} d.rank.score 周榜亲密值
     * @apiSuccess {number} d.rank.level 等级
     * @apiSuccess {number} d.rank.level_name 等级名称
     * @apiSuccess {number} d.rank.total_value 总亲密值
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "deadline": "1556467199",
     *                 "rank": [
     *                 {
     *                     "anchor": {
     *                         "user_id": "168",
     *                         "user_nickname": "啦啦啦",
     *                         "user_avatar": "http://thirdwx.qlogo.cn/mmopen/vi_32/e1u7Ut4rUff6QDfsRXuTjJwpuqaEBeyBL8FC7bIu6fcuXkogvUBRYLVCIRFLQicgwxVVC3dibibSbkxM88BXsQVSA/132",
     *                         "user_sex": "2"
     *                     },
     *                     "user": {
     *                         "user_id": "251",
     *                         "user_nickname": "Dawn09101048222",
     *                         "user_avatar": "http://thirdqq.qlogo.cn/qqapp/1106652113/9012BAEA9B36E6AE8846D0EFE9C05A13/100",
     *                         "user_sex": "1"
     *                     },
     *                     "score": "660",
     *                     "level": "0",
     *                     "level_name": "萍水相逢",
     *                     "total_value": "660",
     *                 }
     *             ]
     *         },
     *         "t": "1542079949"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function intimateAction( $nUserId = 0 )
    {
        $oIntimateService = new IntimateService();

        $resultJson = $oIntimateService->getCacheData();
        if ( $resultJson ) {
            $result = json_decode($resultJson);
            $this->success($result);
        }
        $sDeadline = (string)strtotime(date('Y-m-d 23:59:59', strtotime('last day next week')));
        $rank      = $oIntimateService->getData(9);

        $row     = [];
        $userIds = [];
        foreach ( $rank as $dataKey => $dataScore ) {
            $tmp       = explode('-', $dataKey);
            $userIds[] = $tmp[0];
            $userIds[] = $tmp[1];
        }
        if ( !$userIds ) {
            $this->success($row);
        }
        $userIdsStr     = implode(',', $userIds);
        $oUserListModel = User::find([
            "user_id in ($userIdsStr)",
            'columns' => 'user_id,user_nickname,user_avatar,user_level,user_sex',
        ]);
        // 获得用户信息
        $oUserList = [];
        foreach ( $oUserListModel as $userItem ) {
            $oUserList[ $userItem->user_id ] = $userItem->toArray();
        }

        foreach ( $rank as $dataKey => $dataScore ) {
            $tmp       = explode('-', $dataKey);
            $levelInfo = UserIntimate::getIntimateLevel($tmp[0], $tmp[1]);
            $row[]     = [
                'level'       => $levelInfo['level'],
                'level_name'  => $levelInfo['level_name'],
                'total_value' => $levelInfo['total_value'],
                'anchor'      => $oUserList[ $tmp[0] ],
                'user'        => $oUserList[ $tmp[1] ],
                'score'       => (string)$dataScore
            ];
        }
        $result = [
            'rank'     => $row,
            'deadline' => $sDeadline
        ];
        $oIntimateService->saveCacheData(json_encode($result));
        $this->success($result);

    }


    /**
     * @apiVersion 1.4.0
     * @api {get} /live/ranking/egg 003-190823砸蛋排行榜
     * @apiName rank-egg
     * @apiGroup Rank
     * @apiDescription 003-190823砸蛋排行榜
     * @apiParam (正常请求) {String='day','week'} category  类型
     * @apiParam (debug) {String} debug_auth  认证
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String='day','week'} category  类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.rank
     * @apiSuccess {number} d.rank.user_id  用户ID
     * @apiSuccess {number} d.rank.user_nickname  用户昵称
     * @apiSuccess {number} d.rank.user_avatar  用户头像
     * @apiSuccess {String='Y','N'} d.rank.user_rank_show_flg  排行榜是否显示全部信息
     * @apiSuccess {number} d.rank.total_coin   消耗金币
     * @apiSuccess {number} d.rank.total_number  砸蛋次数
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *        "rank": [{
     *              "user_nickname": "哎哟不错👍👍",
     *              "user_avatar": "https:\/\/tiantong-1259630769.image.myqcloud.com\/image\/2019\/08\/14\/451081dd24.jpg",
     *            "user_id": "17",
     *            "user_rank_show_flg": "Y",
     *            "total_coin": "9302000",
     *            "total_number": "46510",
     *            "total_coin_int": 46510
     *        }]
     *    },
     *    "t": "1566527032"
     *   }
     */
    public function eggAction( $nUserId = 0 )
    {
        $nCategory = $this->getParams('category');
        try {
            if ( !in_array($nCategory, [
                'day',
                'week'
            ]) ) {
                $nCategory = 'day';
            }
            if ( $nCategory == 'day' ) {

                $rank = $this->modelsManager->createBuilder()
                    ->from([ 'l' => BuyHammerLog::class ])
                    ->join(User::class, 'l.buy_hammer_user_id = u.user_id', 'u')
                    ->where('l.buy_hammer_create_time >= :start_time:', [
                        'start_time' => strtotime('today')
                    ])
                    ->columns('buy_hammer_user_id as user_id,u.user_nickname,u.user_avatar,u.user_remind,sum(buy_hammer_total_coin) as total_coin,sum(buy_hammer_number) as total_number')
                    ->groupBy('buy_hammer_user_id')
                    ->orderBy('sum(l.buy_hammer_total_coin) desc')
                    ->limit(30)
                    ->getQuery()
                    ->cache([
                        'lifetime' => 60,
                        'key'      => 'egg_rank_day'
                    ])
                    ->execute()->toArray();

            } else {

                $rank = $this->modelsManager->createBuilder()
                    ->from([ 'l' => BuyHammerLog::class ])
                    ->join(User::class, 'l.buy_hammer_user_id = u.user_id', 'u')
                    ->where('l.buy_hammer_create_time >= :start_time:', [
                        'start_time' => strtotime(date('Y-m-d', (time() - ((date('w', time()) == 0 ? 7 : date('w', time())) - 1) * 24 * 3600)))
                    ])
                    ->columns('buy_hammer_user_id as user_id,u.user_nickname,u.user_avatar,u.user_remind,sum(buy_hammer_total_coin) as total_coin,sum(buy_hammer_number) as total_number')
                    ->groupBy('buy_hammer_user_id')
                    ->orderBy('sum(l.buy_hammer_total_coin) desc')
                    ->limit(30)
                    ->getQuery()
                    ->cache([
                        'lifetime' => 60,
                        'key'      => 'egg_rank_week'
                    ])
                    ->execute()->toArray();
            }

            foreach ( $rank as &$item ) {
                $item['total_coin_int'] = (int)$item['total_coin'];
                if ( !$item['user_remind'] ) {
                    $item['user_id']       = 0;
                    $item['user_avatar']   = User::SECRET_AVATAR;
                    $item['user_nickname'] = '神秘人';
                }
            }

            $row = [
                'rank' => $rank,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/ranking/roomSendCoin
     * @api {get} /live/ranking/roomSendCoin 004-191210房间金主榜
     * @apiName roomSendCoin
     * @apiGroup Ranking
     * @apiDescription 004-191210房间金主榜
     * @apiParam (正常请求) {String='day','week'} category  类型
     * @apiParam (正常请求) {String} room_id  房间ID
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *  {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *      "rank": [{
     *        "user_id": "428",
     *        "user_nickname": "产品产品",
     *        "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2019\/06\/13\/1560427335953.png",
     *        "user_sex": "2",
     *        "total_coin": "21.0000"
     *      }]
     *    },
     *    "t": "1575946291"
     *  }
     */
    public function roomSendCoinAction( $nUserId )
    {
        $sCategory = $this->getParams('category');
        $nRoomId   = $this->getParams('room_id');
        try {
            if ( !in_array($sCategory, [
                'day',
                'week'
            ]) ) {
                $sCategory = 'day';
            }
            $data = (new UserGiftLog())->getRoomSendCoinRank($sCategory, $nRoomId);
            $row  = [
                'rank' => $data,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.4.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/ranking/roomGetCoin
     * @api {get} /live/ranking/roomGetCoin 005-191210房间人气榜
     * @apiName roomGetCoin
     * @apiGroup Ranking
     * @apiDescription 004-191210房间人气榜
     * @apiParam (正常请求) {String='day','week'} category  类型
     * @apiParam (正常请求) {String} room_id  房间ID
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *  {
     *    "c": 0,
     *    "m": "请求成功",
     *    "d": {
     *      "rank": [{
     *        "user_id": "428",
     *        "user_nickname": "产品产品",
     *        "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2019\/06\/13\/1560427335953.png",
     *        "user_sex": "2",
     *        "total_coin": "21.0000"
     *      }]
     *    },
     *    "t": "1575946291"
     *  }
     */
    public function roomGetCoinAction( $nUserId )
    {
        $sCategory = $this->getParams('category');
        $nRoomId   = $this->getParams('room_id');
        try {
            if ( !in_array($sCategory, [
                'day',
                'week'
            ]) ) {
                $sCategory = 'day';
            }
            $data = (new UserGiftLog())->getRoomGetCoinRank($sCategory, $nRoomId);
            $row  = [
                'rank' => $data,
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


}