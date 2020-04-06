<?php

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 默认控制器                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers\live;

use Exception;
use app\models\User;
use app\models\Anchor;
use app\models\UserFollow;
use app\helper\ResponseError;
use app\http\controllers\ControllerBase;

/**
 * SearchController
 */
class SearchController extends ControllerBase
{


    /**
     * anchorAction 首页搜索
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/search/index
     * @api {get} /live/search/index 首页搜索
     * @apiName search-index
     * @apiGroup Index
     * @apiDescription 首页搜索
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} kw 昵称或ID搜索
     * @apiParam (正常请求){String} page 页码
     * @apiParam (正常请求){String} anchor_character  主播性格
     * @apiParam (正常请求){String} anchor_dress  主播爱穿
     * @apiParam (正常请求){String} anchor_stature  主播身材
     * @apiParam (正常请求){String} max_age  最大的年龄
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} kw 昵称或ID搜索
     * @apiParam (debug){String} page 页码
     * @apiParam (debug){String} anchor_character  主播性格
     * @apiParam (debug){String} anchor_dress  主播爱穿
     * @apiParam (debug){String} anchor_stature  主播身材
     * @apiParam (debug){String} max_age  最大的年龄
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.user_id   用户id
     * @apiSuccess {String} d.items.user_nickname  用户昵称
     * @apiSuccess {String} d.items.user_avatar  用户头像
     * @apiSuccess {number} d.items.user_fans_total  用户粉丝数
     * @apiSuccess {String} d.items.is_follow  是否关注该用户
     * @apiSuccess {number} d.items.user_member_expire_time  用户的VIP过期时间
     * @apiSuccess {String} d.items.user_birth  用户生日
     * @apiSuccess {number} d.items.user_sex 用户性别
     * @apiSuccess {String} d.items.user_is_member  用户是否为会员
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *       {
     *           "c": 0,
     *           "m": "请求成功",
     *           "d": {
     *                   "items": [{
     *                       "user_id": "264",
     *                       "user_nickname": "☆Ca~mus★09301835025",
     *                       "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/D5623288FD70EF84F074F5631097A1C2\/100",
     *                       "user_fans_total": "0",
     *                       "is_follow": "N",
     *                       "user_member_expire_time": "0",
     *                       "user_birth": "",
     *                       "user_sex": "1",
     *                       "user_is_member": "N"
     *               }],
     *               "page": 1,
     *               "pagesize": 20,
     *               "pagetotal": 1,
     *               "total": 1,
     *               "prev": 1,
     *               "next": 1
     *           },
     *           "t": "1545791502"
     *       }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction($nUserId = 0)
    {
        $sKeyword  = $this->getParams('kw', 'string', '');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        // 主播性格
        $anchorCharacter = $this->getParams('anchor_character', 'string');
        // 主播爱穿
        $anchorDress = $this->getParams('anchor_dress', 'string');
        // 主播身材
        $anchorStature = $this->getParams('anchor_stature', 'string');
        // 年龄大值
        $maxAge = $this->getParams('max_age', 'int');
        $nPagesize = min($nPagesize, 100);

        try {

            $oUser = User::findFirst($nUserId);
            if ( $oUser->user_is_superadmin == 'C' ) {
                $builder = $this->modelsManager
                    ->createBuilder()
                    ->from([ 'u' => User::class ])
                    ->leftJoin(UserFollow::class, sprintf('uf.to_user_id=u.user_id and uf.user_id=%d', $nUserId), 'uf')
                    ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,u.user_fans_total,uf.to_user_id as is_follow,u.user_member_expire_time,u.user_birth,u.user_sex')
                    ->where('u.user_id = :user_id: or u.user_nickname like :user_nickname:', [
                        'user_id'       => intval($sKeyword),
                        'user_nickname' => $sKeyword . '%',
                    ]);
            } else {
                $builder = $this->modelsManager
                    ->createBuilder()
                    ->from([ 'u' => User::class ])
                    ->leftJoin(UserFollow::class, sprintf('uf.to_user_id=u.user_id and uf.user_id=%d', $nUserId), 'uf')
                    ->columns('u.user_id,u.user_nickname,u.user_avatar,u.user_level,u.user_fans_total,uf.to_user_id as is_follow,u.user_member_expire_time,u.user_birth,u.user_sex')
                    ->where('u.user_id = :user_id: or u.user_nickname like :user_nickname:', [
                        'user_id'       => intval($sKeyword),
                        'user_nickname' => $sKeyword . '%',
                    ]);
                if ( $oUser->user_is_anchor == 'Y' ) {
                    $builder->andWhere('u.user_is_anchor="N"');
                } else {
                    $builder->andWhere('u.user_is_anchor="Y"');
                }
            }
            if ( $anchorCharacter ) {
                $builder->andWhere('a.anchor_character like :anchor_character:', [
                    'anchor_character' => '%' . $anchorCharacter . '%',
                ])->join(Anchor::class,'a.user_id = u.user_id','a');
            }
            if ( $anchorDress ) {
                $builder->andWhere('a.anchor_dress like :anchor_dress:', [
                    'anchor_dress' => '%' . $anchorDress . '%',
                ])->join(Anchor::class,'a.user_id = u.user_id','a');
            }
            if ( $anchorStature ) {
                $builder->andWhere('a.anchor_stature like :anchor_stature:', [
                    'anchor_stature' => '%' . $anchorDress . '%',
                ])->join(Anchor::class,'a.user_id = u.user_id','a');
            }
            if($maxAge && $maxAge > 18){
                // 查询 18 到 maxAge的主播
                $minBirth = date('Y-m-d',strtotime('-'.intval($maxAge) . ' year'));
                $maxBirth = date('Y-m-d',strtotime('-18 year'));
                $builder->andWhere("str_to_date(u.user_birth, '%Y-%m-%d') >= :min_birth: AND str_to_date(u.user_birth, '%Y-%m-%d') <= :max_birth:", [
                    'min_birth' => $minBirth,
                    'max_birth' => $maxBirth,
                ]);
            }

            $row = $this->page($builder, $nPage, $nPagesize);

            foreach ( $row['items'] as &$v ) {
                $v['is_follow']      = $v['is_follow'] != null ? 'Y' : 'N';
                $v['user_is_member'] = $v['user_member_expire_time'] == 0 ? 'N' : (time() > $v['user_member_expire_time'] ? 'O' : 'Y');
            }

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::FAIL,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage())
            );
        }

        $this->success($row);
    }

    /**
     * getRecommendAction 获取推荐的搜索关键字
     *
     * @param  int $nUserId
     */
    public function getRecommendAction($nUserId = 0)
    {
        $sKeyword  = $this->getParams('kw', 'string', '');
        $nPage     = $this->getParams('page', 'int', 1);
        $nPagesize = $this->getParams('pagesize', 'int', 20);

        try {
            $builder = $this->modelsManager
                ->createBuilder()
                ->from(User::class)
                ->columns('user_nickname as content')
                ->where('user_is_anchor="Y"')
                ->andWhere('user_nickname like :user_nickname:', [
                    'user_nickname' => $sKeyword . '%',
                ]);

            $row = $this->page($builder, $nPage, $nPagesize);

        } catch ( Exception $e ) {
            $this->error(
                ResponseError::FAIL,
                sprintf('%s[%s]', ResponseError::getError(ResponseError::FAIL), $e->getMessage())
            );
        }

        $this->success($row);
    }

}