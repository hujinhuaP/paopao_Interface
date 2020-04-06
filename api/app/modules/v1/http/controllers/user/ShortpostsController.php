<?php

namespace app\http\controllers\user;


use app\helper\ResponseError;
use app\models\Anchor;
use app\models\AppList;
use app\models\Banword;
use app\models\Group;
use app\models\Kv;
use app\models\LiveGift;
use app\models\Photographer;
use app\models\ShortPosts;
use app\models\ShortPostsBuy;
use app\models\ShortPostsCollect;
use app\models\ShortPostsComment;
use app\models\ShortPostsCommentDelete;
use app\models\ShortPostsCommentLike;
use app\models\ShortPostsCommentReply;
use app\models\ShortPostsCommentReplyDelete;
use app\models\ShortPostsDelete;
use app\models\ShortPostsGift;
use app\models\ShortPostsLike;
use app\models\ShortPostsMessage;
use app\models\ShortPostsReport;
use app\models\User;
use app\models\UserAccount;
use app\models\UserConsumeCategory;
use app\models\UserFinanceLog;
use app\models\UserFollow;
use app\models\UserGuard;
use app\models\UserGuardLog;
use app\models\UserIntimateLog;
use app\models\LevelConfig;
use app\models\VipLevel;
use app\services\ActivityUserService;
use app\services\AnchorStatService;
use app\services\AnchorTodayDotService;
use app\services\IntimateService;
use Exception;
use app\http\controllers\ControllerBase;

/**
 * ShortpostsController   动态
 */
class ShortpostsController extends ControllerBase
{
    use \app\services\UserService;

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/index
     * @api {get} /user/shortposts/index 002-190904动态列表
     * @apiName shortposts-index
     * @apiGroup ShortPosts
     * @apiDescription 动态列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String='all(全部)','hot(热门)','time(时间)','selection(精选)','collect(收藏)','like(点赞)','follow(关注)','mine(自己发的)','其他用户id'} position 位置
     * @apiParam (正常请求){String='all(全部)','word(纯文字)','image(图文)','video(视频文字)','exhibition(作品)'} short_posts_type  动态类型
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数量
     * @apiParam (debug){String='all(全部)','hot(热门)','time(时间)','selection(精选)','collect(收藏)','like(点赞)','follow(关注)','mine(自己发的)','其他用户id'} position 位置
     * @apiParam (debug){String='all(全部)','word(纯文字)','image(图文)','video(视频文字)','exhibition(作品)'} short_posts_type  动态类型
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.short_posts_id    动态id
     * @apiSuccess {number} d.items.user_id    发布动态用户id
     * @apiSuccess {String} d.items.user_avatar  发布动态用户头像
     * @apiSuccess {String} d.items.user_nickname   发布动态用户昵称
     * @apiSuccess {String} d.items.user_birth   发布动态用户生日
     * @apiSuccess {number} d.items.user_member_expire_time   发布动态用户VIP 过期时间戳
     * @apiSuccess {number} d.items.user_is_member   发布动态用户是否为VIP
     * @apiSuccess {number} d.items.user_is_anchor   发布动态用户是否为主播
     * @apiSuccess {number} d.items.user_is_superadmin   Y为官方发布
     * @apiSuccess {number} d.items.user_level  发布动态用户等级
     * @apiSuccess {number} d.items.user_sex   发布动态用户性别
     * @apiSuccess {number} d.items.user_is_anchor   发布动态用户是否为主播
     * @apiSuccess {number} d.items.create_time    动态发布时间
     * @apiSuccess {String} d.items.short_posts_position    动态位置
     * @apiSuccess {String} d.items.short_posts_word   动态文字
     * @apiSuccess {String} d.items.short_posts_images  动态图片 半角逗号分隔
     * @apiSuccess {String} d.items.short_posts_video   动态视频地址
     * @apiSuccess {String='word(纯文字)','image(图文)','video(视频文字)','exhibition(作品)'} d.items.short_posts_type  动态类型
     * @apiSuccess {number} d.items.short_posts_watch_num   观看人数
     * @apiSuccess {number} d.items.short_posts_comment_num  评论人数
     * @apiSuccess {number} d.items.short_posts_gift_num   送礼数量
     * @apiSuccess {number} d.items.short_posts_like_num  点赞数量
     * @apiSuccess {number} d.items.short_posts_collect_num    收藏数量
     * @apiSuccess {number} d.items.posts_like_id   自己点赞该动态的id  为0为没有点赞，其他为已点赞
     * @apiSuccess {number} d.items.short_posts_status   C 为 仅自己可见，Y为全部可见
     * @apiSuccess {String='Y','N'} d.items.short_posts_is_top   是否为置顶
     * @apiSuccess {String='Y','N'} d.items.has_buy   是否购买过
     * @apiSuccess {String='free(免费)','part_free(图片前两张免费)','pay(付费)'} d.items.short_posts_pay_type    付费类型
     * @apiSuccess {number} d.items.short_posts_price    付费价格
     * @apiSuccess {number} d.items.show_width    显示宽
     * @apiSuccess {number} d.items.show_height    显示高
     * @apiSuccess {number} d.items.short_posts_buy_num    购买数量
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *      {
     *          "c": 0,
     *          "m": "请求成功",
     *          "d": {
     *                  "items": [
     *                  {
     *                      "short_posts_id": "1",
     *                      "user_id": "318",
     *                      "user_avatar": "http://thirdqq.qlogo.cn/qqapp/1107915107/63F3F098E6FAC4B5C210CA2458C66BE6/100",
     *                      "user_nickname": "渐入佳境",
     *                      "user_birth": "1991-1-10",
     *                      "user_member_expire_time": "0",
     *                      "user_is_member": "N",
     *                      "user_is_anchor": "N",
     *                      "user_is_superadmin": "Y",
     *                      "user_level": "1",
     *                      "user_sex": "2",
     *                      "create_time": "1546412208",
     *                      "short_posts_position": "深圳",
     *                      "short_posts_word": "美女美女",
     *                      "short_posts_images": "https://lebolive-1255651273.image.myqcloud.com/image/2018/12/30/6656bb25f764335232179e893ff65afe,https://lebolive-1255651273.image.myqcloud.com/image/2018/12/28/1546006714328.png",
     *                      "short_posts_video": "",
     *                      "short_posts_type": "image",
     *                      "short_posts_watch_num": "0",
     *                      "short_posts_comment_num": "0",
     *                      "short_posts_gift_num": "0",
     *                      "short_posts_like_num": "0",
     *                      "short_posts_collect_num": "0",
     *                      "posts_like_id": "0",
     *                      "short_posts_status": "C",
     *                      "short_posts_is_top": "Y",
     *                      "show_width": "100",
     *                      "show_height": "200",
     *                      "short_posts_buy_num": "200",
     *                  }
     *              ],
     *              "page": 1,
     *              "pagesize": 20,
     *              "pagetotal": 1,
     *              "total": 1,
     *              "prev": 1,
     *              "next": 1
     *          },
     *          "t": "1546412315"
     *      }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function indexAction( $nUserId = 0 )
    {
        $nUserId         = intval($nUserId);
        $nPage           = $this->getParams('page', 'int', 0);
        $nPagesize       = $this->getParams('pagesize', 'int', 20);
        $nPagesize       = max($nPagesize, 20);
        $sPosition       = $this->getParams('position', 'string', 'selection');
        $sShortPostsType = $this->getParams('short_posts_type', 'string', 'all');
        try {

            $oUser   = User::findFirst($nUserId);
            $vipInfo = [];
            //如果是VIP 判断打折
            if ( $oUser->user_member_expire_time > time() ) {
                $vipInfo = VipLevel::getVipInfo($oUser->user_vip_level);
            }

            $columns = 'c.short_posts_id,u.user_id,u.user_avatar,u.user_nickname,u.user_member_expire_time,u.user_level,u.user_sex,u.user_birth,u.user_is_anchor,u.user_is_superadmin,
            c.short_posts_check_time,c.short_posts_create_time as create_time,c.short_posts_position,c.short_posts_word,c.short_posts_images,c.short_posts_video,
            c.short_posts_type,c.short_posts_watch_num,c.short_posts_comment_num,c.short_posts_gift_num,c.short_posts_like_num,
            c.short_posts_collect_num,ifnull(l.id,0) as posts_like_id,c.short_posts_status,c.short_posts_is_top,u.user_is_anchor,
            b.id as buy_id,c.short_posts_pay_type,c.short_posts_price,c.short_posts_show_width as show_width,c.short_posts_show_height as show_height,short_posts_buy_num';
            $builder = $this->modelsManager->createBuilder()
                ->from([ 'c' => ShortPosts::class ])
                ->columns($columns)
                ->join(User::class, 'u.user_id = c.short_posts_user_id', 'u')
                ->where('c.short_posts_status = "Y"');
//                ->orWhere('c.short_posts_user_id = ' . $nUserId);
            $isPublish = $this->isPublish($nUserId, AppList::EXAMINE_ANCHOR);
            if ( $isPublish ) {
                $appInfo = $this->getAppInfo('qq');
                $builder->andWhere('c.short_posts_examine = ' . intval($appInfo['id']));
            } else {
                $builder->andWhere('c.short_posts_examine = 0');
            }
            if ( $sPosition == 'all' && $nPage >= 2 ) {
                $sShortPostsType = 'exhibition';
            }
            if ( in_array($sShortPostsType, [
                'word',
                'image',
                'video',
                'exhibition'
            ]) ) {
                $builder->andWhere("c.short_posts_type = '{$sShortPostsType}'");
            }
            switch ( $sPosition ) {
                case 'all':
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                        ->orderBy('c.short_posts_top_time desc,c.short_posts_create_time desc');
                    break;
                case 'hot':
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
//                        ->orderBy('c.short_posts_is_top,c.short_posts_like_num desc');
                        ->orderBy('c.short_posts_is_top,c.short_posts_create_time desc');
                    break;
                case 'time':
                    // 根据更新时间排序
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
//                        ->orderBy('c.short_posts_update_time desc');
                        ->orderBy('c.short_posts_create_time desc');
                    break;
                case 'selection':
                    // 最新置顶排在前  其他精选随机排列
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                        ->andWhere('c.short_posts_selection_time > 0')
                        ->orderBy('c.short_posts_top_time desc,short_posts_create_time desc');
                    break;
                case 'collect':
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                        ->join(ShortPostsCollect::class, 'cc.short_posts_id = c.short_posts_id AND cc.user_id = ' . $nUserId, 'cc')
                        ->orderBy('cc.create_time desc');
                    break;
                case 'like':
                    $builder->join(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                        ->orderBy('l.create_time desc');
                    break;
                case 'follow':
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                        ->join(UserFollow::class, 'uf.to_user_id = c.short_posts_user_id AND uf.user_id = ' . $nUserId, 'uf')
                        ->orderBy('c.short_posts_create_time desc');
                    break;
                case 'mine':
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                        ->andWhere('c.short_posts_user_id =' . intval($nUserId))
                        ->orderBy('c.short_posts_create_time desc');
                    break;
                case 'buy':
                    $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                        ->join(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                        ->andWhere('c.short_posts_user_id =' . intval($nUserId))
                        ->orderBy('c.short_posts_create_time desc');
                    break;
                default:
                    if ( $sPosition == intval($sPosition) ) {
                        // 查看某个用户的动态
                        $builder->leftJoin(ShortPostsLike::class, 'l.short_posts_id = c.short_posts_id AND l.user_id = ' . $nUserId, 'l')
                            ->leftJoin(ShortPostsBuy::class, 'b.short_posts_id = c.short_posts_id AND b.user_id = ' . $nUserId, 'b')
                            ->andWhere('c.short_posts_user_id =' . intval($sPosition))
//                            ->orderBy('c.short_posts_check_time desc');
                            ->orderBy('c.short_posts_create_time desc');
                    }
            }
            $row = $this->page($builder, $nPage, $nPagesize);
            foreach ( $row['items'] as &$item ) {
                $item['user_is_member']          = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
                $item['short_posts_watch_num']   = intval($item['short_posts_watch_num']);
                $item['short_posts_comment_num'] = intval($item['short_posts_comment_num']);
                $item['short_posts_gift_num']    = intval($item['short_posts_gift_num']);
                $item['short_posts_like_num']    = intval($item['short_posts_like_num']);
                $item['short_posts_collect_num'] = intval($item['short_posts_collect_num']);
//                $item['create_time']             = $item['short_posts_check_time'] ? $item['short_posts_check_time'] : $item['create_time'];
                if ( $sPosition != 'selection' ) {
                    $item['short_posts_is_top'] = 'N';
                }
                $item['has_buy'] = 'N';
                if ( $item['buy_id'] ) {
                    $item['has_buy'] = 'Y';
                }
                if ( $item['user_id'] == $nUserId ) {
                    $item['short_posts_pay_type'] = ShortPosts::PAY_TYPE_FREE;
                }
                $item['short_posts_discount_price'] = $item['short_posts_price'];
                if ( $item['short_posts_pay_type'] != ShortPosts::PAY_TYPE_FREE && $vipInfo && $item['short_posts_type'] == ShortPosts::TYPE_EXHIBITION ) {
                    // VIP 作品打折
                    $item['short_posts_discount_price'] = sprintf('%.2f', $item['short_posts_price'] * $vipInfo->vip_level_exhibition_discount / 10);
                }
                unset($item['buy_id']);
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/checkRule
     * @api {get} /user/shortposts/checkRule 发帖权限
     * @apiName shortposts-checkrule
     * @apiGroup ShortPosts
     * @apiDescription 发帖权限
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
     * @apiSuccess {object[]} d.price_list
     * @apiSuccess {number} d.price_list.price
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "price_list": [{
     *                   "price": "100"
     *           }, {
     *                   "price": "190"
     *           }, {
     *                   "price": "200"
     *           }]
     *       },
     *       "t": 1554279868
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *         "c": 10036,
     *         "d": "",
     *         "m": "没绑定手机号码",
     *         "t": 1534911421
     *     }
     *     {
     *         "c": 10076,
     *         "d": "",
     *         "m": "您不是VIP请购买VIP",
     *         "t": 1534911421
     *     }
     */
    public function checkRuleAction( $nUserId = 0 )
    {
        try {
            $this->_checkRule($nUserId);

            // 获取收费动态价格列表
            $kvData    = Kv::many([
                Kv::POSTS_MAX_PRICE,
                Kv::POSTS_MIN_PRICE,
                Kv::POSTS_INTERVAL_PRICE
            ]);
            $priceList = [];
            for ( $i = $kvData[ Kv::POSTS_MIN_PRICE ]; $i <= $kvData[ Kv::POSTS_MAX_PRICE ]; $i += $kvData[ Kv::POSTS_INTERVAL_PRICE ] ) {
                $priceList[] = [
                    'price' => (string)$i
                ];
            }
            $row = [
                'price_list' => $priceList
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    private function _checkRule( $nUserId )
    {
        $appName = $this->getParams('app_name', 'string', 'tianmi');
        $oUser   = User::findFirst($nUserId);
        if ( $oUser->user_is_anchor == 'N' && $oUser->user_is_photographer == 'N' ) {
            throw new Exception(
                ResponseError::getError(ResponseError::POSTS_ADD_RULE),
                ResponseError::POSTS_ADD_RULE
            );
        }
        $oAppInfo = $this->getAppInfo('qq', $appName);
        if ( $oAppInfo['send_posts_check_flg'] == 'N' ) {
            return TRUE;
        }

        return TRUE;
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/add
     * @api {post} /user/shortposts/add 001-190904发布动态
     * @apiName shortposts-add
     * @apiGroup ShortPosts
     * @apiDescription 发布动态
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} post_content 文字内容
     * @apiParam (正常请求){String='word(纯文字)','video(带视频)','image(带图片)','exhibition(作品)'} post_type 动态类型
     * @apiParam (正常请求){String} images 图片地址 半角逗号分隔 最多9张
     * @apiParam (正常请求){String} video 视频地址
     * @apiParam (正常请求){String} position 定位
     * @apiParam (正常请求){String='free(免费)','part_free(图片前两张免费)','pay(付费)'} pay_type 付费类型
     * @apiParam (正常请求){Number} pay_price 付费价格
     * @apiParam (正常请求){Number} show_width 显示宽
     * @apiParam (正常请求){Number} show_height 显示高
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} post_content 文字内容
     * @apiParam (debug){String='word(纯文字)','video(带视频)','image(带图片)','exhibition(作品)'} post_type 动态类型
     * @apiParam (debug){String} images 图片地址 半角逗号分隔 最多9张
     * @apiParam (debug){String} video 视频地址
     * @apiParam (debug){String} position 定位
     * @apiParam (debug){String='free(免费)','part_free(图片前两张免费)','pay(付费)'} pay_type 付费类型
     * @apiParam (debug){Number} pay_price 付费价格
     * @apiParam (debug){Number} show_width 显示宽
     * @apiParam (debug){Number} show_height 显示高
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *         "c": 10036,
     *         "d": "",
     *         "m": "没绑定手机号码",
     *         "t": 1534911421
     *     }
     *     {
     *         "c": 10076,
     *         "d": "",
     *         "m": "您不是VIP请购买VIP",
     *         "t": 1534911421
     *     }
     */
    public function addAction( $nUserId = 0 )
    {
        $sPostContent   = $this->getParams('post_content');
        $sPostType      = $this->getParams('post_type');
        $sImages        = $this->getParams('images', 'string', '');
        $sVideo         = $this->getParams('video');
        $sPosition      = $this->getParams('position');
        $sPostsPayType  = $this->getParams('pay_type');
        $sPostsPayPrice = $this->getParams('pay_price');
        $sShowWidth     = $this->getParams('show_width', 'int', 0);
        $sShowHeight    = $this->getParams('show_height', 'int', 0);

        try {
            if ( empty($sPostContent) || !in_array($sPostType, [
                    'word',
                    'video',
                    'image',
                    'exhibition',
                ]) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            if ( !in_array($sPostsPayType, [
                ShortPosts::PAY_TYPE_FREE,
                ShortPosts::PAY_TYPE_PART_FREE,
                ShortPosts::PAY_TYPE_PAY
            ]) ) {
                throw new Exception(
                    '付费类型错误',
                    ResponseError::PARAM_ERROR
                );
            }
            if ( $sPostsPayType != ShortPosts::PAY_TYPE_FREE ) {
                if ( empty($sPostsPayPrice) ) {
                    throw new Exception(
                        '付费类型，价格不能为空',
                        ResponseError::PARAM_ERROR
                    );
                }
                // 获取收费动态价格列表
                $kvData    = Kv::many([
                    Kv::POSTS_MAX_PRICE,
                    Kv::POSTS_MIN_PRICE,
                    Kv::POSTS_INTERVAL_PRICE
                ]);
                $priceList = [];
                for ( $i = $kvData[ Kv::POSTS_MIN_PRICE ]; $i <= $kvData[ Kv::POSTS_MAX_PRICE ]; $i += $kvData[ Kv::POSTS_INTERVAL_PRICE ] ) {
                    $priceList[] = (string)$i;
                }
                if ( !in_array($sPostsPayPrice, $priceList) ) {
                    throw new Exception(
                        '付费价格设置错误',
                        ResponseError::PARAM_ERROR
                    );
                }
            }

            if ( $sPostsPayType == ShortPosts::PAY_TYPE_PART_FREE && $sPostType != 'image' ) {
                $sPostsPayType = ShortPosts::PAY_TYPE_PAY;
            }

            switch ( $sPostType ) {
                case 'word':
                    $sVideo  = '';
                    $sImages = '';
                    break;
                case 'video':
                    if ( empty($sVideo) ) {
                        throw new Exception(
                            ResponseError::getError(ResponseError::PARAM_ERROR),
                            ResponseError::PARAM_ERROR
                        );
                    }
                    break;
                case 'image':
                    if ( empty($sImages) ) {
                        throw new Exception(
                            ResponseError::getError(ResponseError::PARAM_ERROR),
                            ResponseError::PARAM_ERROR
                        );
                    }
                    break;
            }
            $this->_checkRule($nUserId);
            $remark          = '';
            $posts_new_check = Kv::get(Kv::POSTS_NEW_CHECK);
            // 如果为Y 则需要审核 初始状态为审核中
            $postsStatus = $posts_new_check == 'Y' ? 'C' : 'Y';
//            if($sPostsPayType ==  ShortPosts::PAY_TYPE_FREE){
//                // 免费的动态不审核
//                $postsStatus = 'Y';
//            }else{
//                $posts_new_check = Kv::get(Kv::POSTS_NEW_CHECK);
//                // 如果为Y 则需要审核 初始状态为审核中
//                $postsStatus = $posts_new_check == 'Y' ? 'C' : 'Y';
//            }
            $banword = $this->banword($sPostContent, Banword::LOCATION_POSTS, TRUE);
            if ( $banword ) {
//                throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
                $remark      = sprintf('禁用关键词：【%s】', $banword);
                $postsStatus = 'N';
            }
            $oShortPosts                           = new ShortPosts();
            $oShortPosts->short_posts_word         = $sPostContent;
            $oShortPosts->short_posts_user_id      = $nUserId;
            $oShortPosts->short_posts_position     = $sPosition;
            $oShortPosts->short_posts_type         = $sPostType;
            $oShortPosts->short_posts_images       = $sImages;
            $oShortPosts->short_posts_video        = $sVideo;
            $oShortPosts->short_posts_status       = $postsStatus;
            $oShortPosts->short_posts_check_remark = $remark;
            $oShortPosts->short_posts_pay_type     = $sPostsPayType;
            $oShortPosts->short_posts_price        = intval($sPostsPayPrice);
            $oShortPosts->short_posts_show_width   = $sShowWidth;
            $oShortPosts->short_posts_show_height  = $sShowHeight;
            if ( !$oShortPosts->save() ) {
                throw new Exception(sprintf('%s [%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPosts->getMessages())), ResponseError::OPERATE_FAILED);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();

    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/commentList
     * @api {get} /user/shortposts/commentList 动态评论列表
     * @apiName shortposts-commentlist
     * @apiGroup ShortPosts
     * @apiDescription 动态评论列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (正常请求){String='hot(热门)','time(时间)'} orderby 排序 默认时间
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数量
     * @apiParam (debug){String='hot(热门)','time(时间)'} orderby 排序 默认时间
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.comment_id   评论id
     * @apiSuccess {number} d.items.user_id   用户id
     * @apiSuccess {String} d.items.user_avatar   用户头像
     * @apiSuccess {String} d.items.user_nickname  用户昵称
     * @apiSuccess {String} d.items.user_level  用户等级
     * @apiSuccess {number} d.items.user_member_expire_time  用户VIP 过期时间
     * @apiSuccess {number} d.items.user_is_member  用户是否VIP
     * @apiSuccess {number} d.items.user_is_anchor  用户是否为主播
     * @apiSuccess {number} d.items.create_time  发表时间
     * @apiSuccess {String} d.items.comment_content  评论内容
     * @apiSuccess {number} d.items.at_user_id    @用户id
     * @apiSuccess {String} d.items.at_user_nickname @用户昵称
     * @apiSuccess {number} d.items.comment_like_num   评论点赞数
     * @apiSuccess {number} d.items.show_reply_user_id   显示的回复用户id
     * @apiSuccess {String} d.items.show_reply_content  显示的回复内容
     * @apiSuccess {String} d.items.show_reply_user_nickname   显示的回复用户昵称
     * @apiSuccess {number} d.items.reply_num   总回复数
     * @apiSuccess {number} d.items.comment_like_id   评论点赞id  自己如果点赞过 则显示为评论点赞id 否则为 0
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "items": [{
     *                    "comment_id": "1",
     *                    "user_id": "313",
     *                    "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9E3CD73EA8A959B6B2C44F9C7EA5FD27\/100",
     *                    "user_nickname": "雨晴👄👄👄",
     *                    "user_level": "5",
     *                    "user_member_expire_time": "0",
     *                    "user_is_member": "Y",
     *                    "user_is_anchor": "Y",
     *                    "create_time": "1546426245",
     *                    "comment_content": "好贴",
     *                    "at_user_id": "317",
     *                    "at_user_nickname": "Dawn11261527320",
     *                    "comment_like_num": "0",
     *                    "show_reply_user_id": "0",
     *                    "show_reply_content": "",
     *                    "show_reply_user_nickname": "",
     *                    "reply_num": "0",
     *                    "comment_like_id": "0"
     *            }],
     *            "page": 1,
     *            "pagesize": 20,
     *            "pagetotal": 1,
     *            "total": 1,
     *            "prev": 1,
     *            "next": 1
     *        },
     *        "t": "1546426438"
     *    }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function commentListAction( $nUserId = 0 )
    {

        $sPostsId  = $this->getParams('posts_id');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $sOrderby  = $this->getParams('orderby');
        try {
            $row = $this->_getCommentList($nUserId, $sPostsId, $nPage, $nPagesize, $sOrderby);
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    private function _getCommentList( $nUserId, $sPostsId, $nPage, $nPagesize, $sOrderby )
    {
        $order = 'c.comment_id desc';
        if ( $sOrderby == 'hot' ) {
            $order = 'c.comment_like_num desc';
        }
        $columns = 'c.comment_id,u.user_id,u.user_avatar,u.user_nickname,u.user_member_expire_time,c.create_time,c.comment_content,
            c.at_user_id,c.at_user_nickname,u.user_level,u.user_sex,u.user_is_anchor,u.user_sex,u.user_birth,
            c.comment_like_num,c.show_reply_user_id,c.show_reply_content,c.show_reply_user_nickname,c.reply_num,ifnull(cl.id,0) as comment_like_id';
        $builder = $this->modelsManager->createBuilder()
            ->from([ 'c' => ShortPostsComment::class ])
            ->columns($columns)
            ->join(User::class, 'u.user_id = c.user_id', 'u')
            ->leftJoin(ShortPostsCommentLike::class, 'cl.comment_id = c.comment_id AND cl.user_id = ' . intval($nUserId), 'cl')
            ->where('(c.comment_status = "Y" or u.user_id = :user_id:) AND c.short_posts_id=:short_posts_id:',
                [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ])
            ->orderBy($order);
        $row     = $this->page($builder, $nPage, $nPagesize);
        foreach ( $row['items'] as &$item ) {
            $item['user_is_member']   = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
            $item['comment_like_num'] = intval($item['comment_like_num']);
            $item['reply_num']        = intval($item['reply_num']);
        }
        return $row;
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/likeComment
     * @api {post} /user/shortposts/likeComment 评论点赞
     * @apiName shortposts-likeComment
     * @apiGroup ShortPosts
     * @apiDescription 评论点赞
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} comment_id 评论id
     * @apiParam (正常请求){String} is_like 是否为点赞  Y为点赞  N为取消
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} comment_id 评论id
     * @apiParam (debug){String} is_like 是否为点赞  Y为点赞  N为取消
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function likeCommentAction( $nUserId = 0 )
    {
        $sCommentId = $this->getParams('comment_id');
        $sIsLike    = $this->getParams('is_like');
        try {
            $oShortPostsComment = ShortPostsComment::findFirst($sCommentId);
            if ( !$oShortPostsComment ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPostsCommentLike = ShortPostsCommentLike::findFirst([
                'comment_id = :comment_id: AND user_id = :user_id:',
                'bind' => [
                    'comment_id' => $sCommentId,
                    'user_id'    => $nUserId
                ]
            ]);
            if ( $sIsLike == 'Y' ) {
                if ( !$oShortPostsCommentLike ) {
                    $oShortPostsCommentLike             = new ShortPostsCommentLike();
                    $oShortPostsCommentLike->comment_id = $sCommentId;
                    $oShortPostsCommentLike->user_id    = $nUserId;
                    $oShortPostsCommentLike->save();

                    $oShortPostsComment->comment_like_num += 1;
                    $oShortPostsComment->save();
                }
            } else {
                if ( $oShortPostsCommentLike ) {
                    $oShortPostsCommentLike->delete();
                    $oShortPostsComment->comment_like_num -= 1;
                    if ( $oShortPostsComment->comment_like_num >= 0 ) {
                        $oShortPostsComment->save();
                    }
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/addComment
     * @api {post} /user/shortposts/addComment 动态评论
     * @apiName shortposts-addComment
     * @apiGroup ShortPosts
     * @apiDescription 动态评论
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} content 文字内容   （@位置 用 “@__@”代替）
     * @apiParam (正常请求){String} at_user_id "@"的用户id
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (正常请求){String} comment_id 评论id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} content 文字内容
     * @apiParam (debug){String} at_user_id "@"的用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiParam (debug){String} comment_id 评论id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *    {
     *        "c": 0,
     *        "m": "请求成功",
     *        "d": {
     *                "comment": {
     *                    "comment_id": "4",
     *                    "user_id": "163",
     *                    "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/FAB72B107E3DBBA9A3C08221D856F8A7\/100",
     *                    "user_nickname": "Tom",
     *                    "user_sex": "1",
     *                    "user_birth": "2018-08-24",
     *                    "user_level": "1",
     *                    "user_is_member": "N",
     *                    "create_time": 1547114905,
     *                    "comment_content": "这是22222条回复",
     *                    "at_user_id": 0,
     *                    "at_user_nickname": "",
     *                    "show_reply_user_id": 0,
     *                    "comment_like_num": 0,
     *                    "show_reply_content": "",
     *                    "show_reply_user_nickname": "",
     *                    "reply_num": 0,
     *                    "comment_like_id": "0"
     *            },
     *            "reply": {}
     *        },
     *        "t": "1547114905"
     *    }
     *
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "comment": {},
     *                 "reply": {
     *                         "reply_id": "163",
     *                         "user_id": "163",
     *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/FAB72B107E3DBBA9A3C08221D856F8A7\/100",
     *                         "user_nickname": "Tom",
     *                         "user_level": "1",
     *                         "user_is_member": "N",
     *                         "user_sex": "1",
     *                         "create_time": 1547114948,
     *                         "at_user_id": "0",
     *                         "at_user_nickname": "",
     *                         "reply_content": '12321321321'
     *                 }
     *         },
     *         "t": "1547114948"
     *     }
     */
    public function addCommentAction( $nUserId = 0 )
    {
        $sContent   = $this->getParams('content');
        $sAtUserId  = $this->getParams('at_user_id');
        $sPostsId   = $this->getParams('posts_id');
        $sCommentId = $this->getParams('comment_id');
        try {
            if ( empty($sContent) ) {
                throw new Exception(
                    '内容不能为空',
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                throw new Exception(
                    '动态不存在' . $sPostsId,
                    ResponseError::PARAM_ERROR
                );
            }
            if ( $sAtUserId ) {
                $atUser = User::findFirst($sAtUserId);
                if ( !$atUser ) {
                    throw new Exception(
                        '@用户不存在' . $sAtUserId,
                        ResponseError::PARAM_ERROR
                    );
                }
            }
            $oUser = User::findFirst($nUserId);

            $appName  = $this->getParams('app_name', 'string', 'tianmi');
            $oAppInfo = $this->getAppInfo('qq', $appName);
            if ( $oAppInfo['send_posts_check_flg'] == 'Y' ) {
                $posts_comment_level = Kv::get(Kv::POSTS_COMMENT_LEVEL);
                if ( $oUser->user_is_anchor == 'N' && $oUser->user_level < intval($posts_comment_level) ) {
                    throw new Exception(
                        sprintf('只有等级达到%d才能评论哦', $posts_comment_level),
                        ResponseError::PARAM_ERROR
                    );
                }
            }
            // 30秒请求1次
            $frequencyKey = sprintf('addComment:%s', $nUserId);
            $frequencyFlg = $this->redis->get($frequencyKey);
            if ( $frequencyFlg ) {
                throw new Exception(
                    '请求过于频繁，请稍后再试',
                    ResponseError::OPERATE_FAILED
                );
            }


            $remark        = '';
            $commentStatus = 'Y';
            $banword       = $this->banword($sContent, Banword::LOCATION_POSTS, TRUE);
            if ( $banword ) {
//                throw new Exception(ResponseError::getError(ResponseError::BANWORD), ResponseError::BANWORD);
                $remark        = sprintf('禁用关键词：【%s】', $banword);
                $commentStatus = 'N';
            }
            $comment       = (object)[];
            $reply         = (object)[];
            $messageUserId = $oShortPosts->short_posts_user_id;
            $extraContent  = mb_substr($oShortPosts->short_posts_word, 0, 100);
            $extraTime     = $oShortPosts->short_posts_check_time;
            if ( $sCommentId ) {
                //  评论回复
                $oShortPostsComment = ShortPostsComment::findFirst([
                    'comment_id = :comment_id: AND short_posts_id = :short_posts_id:',
                    'bind' => [
                        'comment_id'     => $sCommentId,
                        'short_posts_id' => $sPostsId,
                    ]
                ]);
                if ( !$oShortPostsComment ) {
                    throw new Exception(
                        '评论不存在' . $sCommentId,
                        ResponseError::PARAM_ERROR
                    );
                }
                $messageUserId                    = $oShortPostsComment->user_id;
                $extraContent                     = mb_substr($oShortPostsComment->comment_content, 0, 100);
                $extraTime                        = $oShortPostsComment->create_time;
                $oShortPostsCommentReply          = new ShortPostsCommentReply();
                $oShortPostsCommentReply->user_id = $nUserId;
                if ( $sAtUserId ) {
                    $oShortPostsCommentReply->at_user_id       = $sAtUserId;
                    $oShortPostsCommentReply->at_user_nickname = $atUser->user_nickname;
                    $messageUserId                             = $sAtUserId;
                }
                $oShortPostsCommentReply->short_posts_id = $sPostsId;
                $oShortPostsCommentReply->reply_content  = $sContent;
                $oShortPostsCommentReply->comment_id     = $sCommentId;
                $oShortPostsCommentReply->reply_status   = $commentStatus;
                if ( $commentStatus == 'N' ) {
                    $oShortPostsCommentReply->reply_check_remark = $remark;
                    $oShortPostsCommentReply->reply_check_time   = time();
                    $oShortPostsCommentReply->is_auto_refuse     = 'Y';
                }
                if ( $oShortPostsCommentReply->save() === FALSE ) {
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentReply->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
                if ( $commentStatus == 'Y' ) {
                    // 无禁用词
                    $oShortPostsComment->show_reply_id            = $oShortPostsCommentReply->reply_id;
                    $oShortPostsComment->show_reply_user_id       = $nUserId;
                    $oShortPostsComment->show_reply_content       = $sContent;
                    $oShortPostsComment->show_reply_user_nickname = $oUser->user_nickname;
                    if ( $sAtUserId ) {
                        $oShortPostsComment->show_reply_at_user_id       = $sAtUserId;
                        $oShortPostsComment->show_reply_at_user_nickname = $atUser->user_nickname;
                    }
                    $oShortPostsComment->reply_num += 1;
                    $oShortPostsComment->save();
                }

                $reply = [
                    'reply_id'         => (string)$oShortPostsCommentReply->reply_id,
                    'user_id'          => (string)$oUser->user_id,
                    'user_avatar'      => $oUser->user_avatar,
                    'user_nickname'    => $oUser->user_nickname,
                    'user_level'       => $oUser->user_level,
                    'user_is_member'   => $oUser->user_member_expire_time > time() ? 'Y' : 'N',
                    'user_sex'         => $oUser->user_sex,
                    'create_time'      => (string)$oShortPostsCommentReply->create_time,
                    'at_user_id'       => (string)$oShortPostsCommentReply->at_user_id,
                    'at_user_nickname' => $oShortPostsCommentReply->at_user_nickname,
                    'reply_content'    => $oShortPostsCommentReply->reply_content,
                ];

            } else {
                // 评论动态
                $oShortPostsComment          = new ShortPostsComment();
                $oShortPostsComment->user_id = $nUserId;
                if ( $sAtUserId ) {
                    $oShortPostsComment->at_user_id       = $sAtUserId;
                    $oShortPostsComment->at_user_nickname = $atUser->user_nickname;
                }
                $oShortPostsComment->short_posts_id  = $sPostsId;
                $oShortPostsComment->comment_content = $sContent;
                $oShortPostsComment->comment_status  = $commentStatus;
                if ( $commentStatus == 'N' ) {
                    $oShortPostsComment->comment_check_remark = $remark;
                    $oShortPostsComment->comment_check_time   = time();
                }
                if ( $oShortPostsComment->save() === FALSE ) {
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsComment->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
                $oShortPostsCommentReply          = new ShortPostsCommentReply();
                $oShortPostsCommentReply->user_id = $nUserId;
                if ( $sAtUserId ) {
                    $oShortPostsCommentReply->at_user_id       = $sAtUserId;
                    $oShortPostsCommentReply->at_user_nickname = $atUser->user_nickname;
                }
                $oShortPostsCommentReply->short_posts_id = $sPostsId;
                $oShortPostsCommentReply->reply_content  = $sContent;
                $oShortPostsCommentReply->comment_id     = $oShortPostsComment->comment_id;
                $oShortPostsCommentReply->reply_status   = $commentStatus;
                $oShortPostsCommentReply->is_comment     = 'Y';
                if ( $commentStatus == 'N' ) {
                    $oShortPostsCommentReply->reply_check_remark = $remark;
                    $oShortPostsCommentReply->reply_check_time   = time();
                    $oShortPostsCommentReply->is_auto_refuse     = 'Y';
                }
                if ( $oShortPostsCommentReply->save() === FALSE ) {
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentReply->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
                $comment = [
                    'comment_id'               => (string)$oShortPostsComment->comment_id,
                    'user_id'                  => (string)$oUser->user_id,
                    'user_avatar'              => $oUser->user_avatar,
                    'user_nickname'            => $oUser->user_nickname,
                    'user_sex'                 => $oUser->user_sex,
                    'user_birth'               => $oUser->user_birth,
                    'user_level'               => $oUser->user_level,
                    'user_is_member'           => $oUser->user_member_expire_time > time() ? 'Y' : 'N',
                    'create_time'              => (string)$oShortPostsComment->create_time,
                    'comment_content'          => $oShortPostsComment->comment_content,
                    'at_user_id'               => (string)$oShortPostsComment->at_user_id,
                    'at_user_nickname'         => $oShortPostsComment->at_user_nickname,
                    'show_reply_user_id'       => (string)$oShortPostsComment->show_reply_user_id,
                    'comment_like_num'         => intval($oShortPostsComment->comment_like_num),
                    'show_reply_content'       => $oShortPostsComment->show_reply_content,
                    'show_reply_user_nickname' => $oShortPostsComment->show_reply_user_nickname,
                    'reply_num'                => intval($oShortPostsComment->reply_num),
                    'comment_like_id'          => '0',
                ];
            }
            if ( $commentStatus == 'Y' ) {
                $oShortPosts->short_posts_comment_num += 1;
                $oShortPosts->save();

                // 添加动态消息
                $oShortPostsMessage                       = new ShortPostsMessage();
                $oShortPostsMessage->short_posts_id       = $sPostsId;
                $oShortPostsMessage->message_type         = $sCommentId ? ShortPostsMessage::MESSAGE_TYPE_REPLY : ShortPostsMessage::MESSAGE_TYPE_COMMENT;
                $oShortPostsMessage->user_id              = $messageUserId;
                $oShortPostsMessage->send_user_id         = $nUserId;
                $oShortPostsMessage->message_content      = $sContent;
                $oShortPostsMessage->message_target_extra = serialize([
                    'comment_id'    => $oShortPostsCommentReply->comment_id,
                    'extra_content' => $extraContent,
                    'extra_time'    => $extraTime
                ]);
                $oShortPostsMessage->save();
            }

            $row = [
                'comment' => $comment,
                'reply'   => $reply

            ];
            $this->redis->set($frequencyKey, 1);
            $this->redis->expire($frequencyKey, 30);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/like
     * @api {post} /user/shortposts/like 动态点赞
     * @apiName shortposts-like
     * @apiGroup ShortPosts
     * @apiDescription 动态点赞
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (正常请求){String} is_like 是否为点赞  Y为点赞  N为取消
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiParam (debug){String} is_like 是否为点赞  Y为点赞  N为取消
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function likeAction( $nUserId = 0 )
    {
        $sPostsId = $this->getParams('posts_id');
        $sIsLike  = $this->getParams('is_like');
        try {
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPostsLike = ShortPostsLike::findFirst([
                'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                'bind' => [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ]
            ]);
            if ( $sIsLike == 'Y' ) {
                if ( !$oShortPostsLike ) {
                    $oShortPostsLike                 = new ShortPostsLike();
                    $oShortPostsLike->short_posts_id = $sPostsId;
                    $oShortPostsLike->user_id        = $nUserId;
                    $oShortPostsLike->save();

                    $oShortPosts->short_posts_like_num += 1;
                    $oShortPosts->save();
                }
            } else {
                if ( $oShortPostsLike ) {
                    $oShortPostsLike->delete();
                }
                $oShortPosts->short_posts_like_num -= 1;
                if ( $oShortPosts->short_posts_like_num >= 0 ) {
                    $oShortPosts->save();
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/report
     * @api {post} /user/shortposts/report 动态举报
     * @apiName shortposts-report
     * @apiGroup ShortPosts
     * @apiDescription 动态举报
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (正常请求){String} report_content 举报内容
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiParam (debug){String} report_content 举报内容
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function reportAction( $nUserId = 0 )
    {
        $sPostsId = $this->getParams('posts_id');
        $sContent = $this->getParams('report_content');
        try {
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPostsReport = ShortPostsReport::findFirst([
                'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                'bind' => [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ]
            ]);
            if ( $oShortPostsReport ) {
                throw new Exception(
                    '您已举报该动态，平台人员会抓紧核实，感谢您对平台的支持',
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPostsReport                 = new ShortPostsReport();
            $oShortPostsReport->short_posts_id = $sPostsId;
            $oShortPostsReport->user_id        = $nUserId;
            $oShortPostsReport->content        = $sContent;
            $oShortPostsReport->save();


            $oShortPosts->short_posts_report_num += 1;
            $oShortPosts->save();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/collect
     * @api {post} /user/shortposts/collect 动态收藏
     * @apiName shortposts-collect
     * @apiGroup ShortPosts
     * @apiDescription 动态收藏
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (正常请求){String} is_collect 是否为收藏  Y为收藏  N为取消收藏
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiParam (debug){String} is_collect 是否为收藏  Y为收藏  N为取消收藏
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function collectAction( $nUserId = 0 )
    {
        $sPostsId   = $this->getParams('posts_id');
        $sIsCollect = $this->getParams('is_collect');
        try {
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPostsCollect = ShortPostsCollect::findFirst([
                'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                'bind' => [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ]
            ]);
            if ( $sIsCollect == 'Y' ) {
                if ( !$oShortPostsCollect ) {
                    $oShortPostsCollect                 = new ShortPostsCollect();
                    $oShortPostsCollect->short_posts_id = $sPostsId;
                    $oShortPostsCollect->user_id        = $nUserId;
                    $oShortPostsCollect->save();
                }

                $oShortPosts->short_posts_collect_num += 1;
                $oShortPosts->save();
            } else {
                if ( $oShortPostsCollect ) {
                    $oShortPostsCollect->delete();
                }
                $oShortPosts->short_posts_collect_num -= 1;
                if ( $oShortPosts->short_posts_collect_num >= 0 ) {
                    $oShortPosts->save();
                }
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/sendGift
     * @api {post} /user/shortposts/sendGift 打赏-赠送礼物
     * @apiName shortposts-sendGift
     * @apiGroup ShortPosts
     * @apiDescription 打赏-赠送礼物
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (正常请求){String} gift_id 礼物id
     * @apiParam (正常请求){Number} gift_number 礼物数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiParam (debug){String} gift_id 礼物id
     * @apiParam (debug){Number} gift_number 礼物数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.user  用户信息
     * @apiSuccess {number} d.user.user_coin  剩余金币
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": {
     *                "user": {
     *                     "user_coin": "30"
     *              },
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function sendGiftAction( $nUserId = 0 )
    {
        $sPostsId    = $this->getParams('posts_id');
        $nLiveGiftId = $this->getParams('gift_id');
        $nGiftNumber = $this->getParams('gift_number', 'int', 1);
        $sLogNumber  = date('YmdHis') . '000000' . mt_rand(10, 99) . mt_rand(100, 999);
        try {
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $saleUserId = $oShortPosts->short_posts_user_id;

            $oUser = User::findFirst($nUserId);

            if ( $oUser->user_is_superadmin == 'S' ) {
                $this->error(10002, '该账号暂不支持此功能哦');
            }
            // 判断礼物
            $oLiveGift = LiveGift::findFirst($nLiveGiftId);
            if ( empty($oLiveGift) ) {
                throw new Exception(ResponseError::getError(ResponseError::GIFT_NOT_EXISTS), ResponseError::GIFT_NOT_EXISTS);
            }
            if ( $oLiveGift->live_gift_type == 2 && $oUser->user_member_expire_time < time() ) {
                // 不是VIP  不能发VIP 礼物
                throw new Exception(ResponseError::getError(ResponseError::NOT_VIP), ResponseError::NOT_VIP);
            }

            $oSaleUser = User::findFirst($saleUserId);

            $groupId = $oSaleUser->user_group_id;
            if ( $oSaleUser->user_is_anchor == 'Y' ) {

                $oAnchor = Anchor::findFirst([
                    'user_id = :user_id:',
                    'bind' => [
                        'user_id' => $saleUserId
                    ]
                ]);
                $nRatio  = $oAnchor->getCoinToDotRatio($oSaleUser, Anchor::RATIO_GIFT);
            } else if ( $oSaleUser->user_is_photographer == 'Y' ) {
                $oPhotographer = Photographer::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $saleUserId ]
                ]);
                $nRatio        = $oPhotographer->getCoinToDotRatio($oSaleUser, Photographer::RATIO_GIFT);
                $groupId       = 0;
            } else {
                throw new Exception(
                    ResponseError::getError(ResponseError::OPERATE_FAILED),
                    ResponseError::OPERATE_FAILED
                );
            }
            // 这里的逻辑可以放在队列里
            // Start a transaction
            $this->db->begin();

            // 用户扣费
            $nCoin = $nGiftNumber * $oLiveGift->live_gift_coin;

            $nDot            = sprintf('%.4f', $nCoin * ($nRatio / 100));
            $consumeFreeCoin = 0;
            $consumeCoin     = 0;
            if ( $oUser->user_free_coin <= 0 ) {
                // 直接扣充值币
                $consumeCoin = $nCoin;

            } else if ( $oUser->user_free_coin < $nCoin ) {
                //扣一部分充值币 扣光赠送币
                $consumeFreeCoin = $oUser->user_free_coin;
                $consumeCoin     = $nCoin - $oUser->user_free_coin;
            } else {
                $consumeFreeCoin = $nCoin;
            }

            $getDot     = sprintf('%.4f', $consumeCoin * ($nRatio / 100));
            $getFreeDot = round($nDot - $getDot, 4);

            $exp       = $nCoin * intval(Kv::get(Kv::COIN_TO_EXP));
            $userLevel = User::getUserLevel($oUser->user_exp + $exp);

            $intimateValue = $nCoin * intval(Kv::get(Kv::COIN_TO_INTIMATE));
            if ( $oSaleUser->user_is_anchor == 'Y' ) {
                $anchorExp   = intval($nDot * intval(Kv::get(Kv::DOT_TO_ANCHOR_EXP)));
                $anchorLevel = LevelConfig::getLevelInfo($oAnchor->anchor_exp + $anchorExp);
            }


            $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin,user_exp = user_exp + :exp,user_level = :user_level
where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin';
            $this->db->execute($sql, [
                'consume_free_coin' => $consumeFreeCoin,
                'consume_coin'      => $consumeCoin,
                'user_id'           => $nUserId,
                'exp'               => $exp,
                'user_level'        => $userLevel
            ]);
            if ( $this->db->affectedRows() <= 0 ) {
                // 赠送币 不够钱
                $this->db->rollback();
                throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
            }

            // 添加帖子送礼记录
            $oShortPostsGift                 = new ShortPostsGift();
            $oShortPostsGift->short_posts_id = $sPostsId;
            $oShortPostsGift->user_id        = $nUserId;
            $oShortPostsGift->gift_id        = $nLiveGiftId;
            $oShortPostsGift->gift_num       = $nGiftNumber;
            $oShortPostsGift->gift_name      = $oLiveGift->live_gift_name;
            $oShortPostsGift->get_dot        = $nDot;
            $oShortPostsGift->send_coin      = $nCoin;
            $oShortPostsGift->log_number     = $sLogNumber;
            $oShortPostsGift->gift_logo      = $oLiveGift->live_gift_logo;
            if ( $oShortPostsGift->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsGift->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            // 记录用户流水
            $oUserFinanceLog                   = new UserFinanceLog();
            $oUserFinanceLog->user_amount_type = UserFinanceLog::AMOUNT_COIN;
            $oUserFinanceLog->user_id          = $nUserId;

            $oUserFinanceLog->consume_category_id    = UserConsumeCategory::POSTS_GIFT_PAY;
            $oUserFinanceLog->consume                = -$nCoin;
            $oUserFinanceLog->remark                 = '动态打赏';
            $oUserFinanceLog->flow_id                = $oShortPostsGift->id;
            $oUserFinanceLog->flow_number            = $sLogNumber;
            $oUserFinanceLog->type                   = 1;
            $oUserFinanceLog->group_id               = $groupId;
            $oUserFinanceLog->target_user_id         = $saleUserId;
            $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
            $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
            $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
            $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
            $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
            $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
            if ( $oUserFinanceLog->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            if ( $nDot ) {
                // 给主播充钱
                $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
                $this->db->execute($sql, [
                    'total_dot'    => $nDot,
                    'get_dot'      => $getDot,
                    'get_free_dot' => $getFreeDot,
                    'user_id'      => $saleUserId,
                ]);
                if ( $this->db->affectedRows() <= 0 ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oAnchorUser->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
                if ( $oSaleUser->user_is_anchor == 'Y' ) {
                    // 给主播加经验(魅力值)
                    $anchorSql = 'update anchor set anchor_exp = anchor_exp + :anchor_exp,anchor_level = :anchor_level WHERE user_id = :user_id';
                    $this->db->execute($anchorSql, [
                        'anchor_exp'   => $anchorExp,
                        'anchor_level' => $anchorLevel['level'],
                        'user_id'      => $saleUserId,
                    ]);
                }
                // 记录主播流水
                $oUserFinanceLog                      = new UserFinanceLog();
                $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
                $oUserFinanceLog->user_id             = $saleUserId;
                $oUserFinanceLog->user_current_amount = $oSaleUser->user_dot + $nDot;
                $oUserFinanceLog->user_last_amount    = $oSaleUser->user_dot;
                $oUserFinanceLog->consume_category_id = UserConsumeCategory::POSTS_GIFT_INCOME;
                $oUserFinanceLog->consume             = +$nDot;
                $oUserFinanceLog->remark              = '动态获取礼物';
                $oUserFinanceLog->flow_id             = $oShortPostsGift->id;
                $oUserFinanceLog->flow_number         = $sLogNumber;
                $oUserFinanceLog->type                = 1;
                $oUserFinanceLog->group_id            = $oSaleUser->user_group_id;
                $oUserFinanceLog->consume_source      = -$nCoin;
                $oUserFinanceLog->target_user_id      = $nUserId;
                if ( $oUserFinanceLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }

                if ( $groupId ) {
                    // 有公会的主播  需要给公会长加钱
                    $oGroup = Group::findFirst($groupId);
                    if ( $oGroup ) {
                        $divid_type    = $oGroup->divid_type;
                        $divid_precent = $oGroup->divid_precent;
                        if ( $divid_type == 0 ) {
                            //主播收益分成
                            $groupMoney = round($nDot * $divid_precent / 100, 2);
                        } else {
                            //主播流水分成  还需要除以一个 充值比例转换值 10
                            $groupMoney = round($nCoin * $divid_precent / 100 / 10, 2);
                        }
                        $sql = 'update `group` set money = money + :money where id = :group_id';
                        $this->db->execute($sql, [
                            'money'    => $groupMoney,
                            'group_id' => $groupId,
                        ]);
                    }
                }
            }

            $oShortPosts->short_posts_gift_num  += 1;
            $oShortPosts->short_posts_dot_count += $nDot;
            $oShortPosts->save();

            $this->db->commit();
            $oUser       = User::findFirst($nUserId);
            $row['user'] = [
                'user_coin' => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
            ];

            // 主播今日收益 增加
            $oAnchorTodayDotService = new AnchorTodayDotService($saleUserId);
            $oAnchorTodayDotService->save($nDot);

            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($saleUserId);
            $oAnchorStatService->save(AnchorStatService::POSTS_INCOME, $nDot);


            // 用户活动消费榜
            $oActivityUserService = new ActivityUserService();
            $oActivityUserService->save($nUserId, $nCoin);


            if ( $nCoin ) {
                // 开始# 亲密值
                if ( $intimateValue > 0 ) {
                    $oUserIntimateLog                              = new UserIntimateLog();
                    $oUserIntimateLog->intimate_log_user_id        = $nUserId;
                    $oUserIntimateLog->intimate_log_anchor_user_id = $saleUserId;
                    $oUserIntimateLog->intimate_log_type           = UserIntimateLog::TYPE_POSTS_GIFT;
                    $oUserIntimateLog->intimate_log_value          = $intimateValue;
                    $oUserIntimateLog->intimate_log_coin           = $nCoin;
                    $oUserIntimateLog->intimate_log_dot            = $nDot;
                    $oUserIntimateLog->save();
                }
                // 结束# 亲密值
            }

            // 添加动态消息
            $oShortPostsMessage                       = new ShortPostsMessage();
            $oShortPostsMessage->short_posts_id       = $sPostsId;
            $oShortPostsMessage->message_type         = ShortPostsMessage::MESSAGE_TYPE_GIFT;
            $oShortPostsMessage->user_id              = $oShortPosts->short_posts_user_id;
            $oShortPostsMessage->send_user_id         = $nUserId;
            $oShortPostsMessage->message_content      = '收到礼物';
            $oShortPostsMessage->message_target_extra = serialize([
                'extra_content' => $oShortPosts->short_posts_word,
                'extra_time'    => $oShortPosts->short_posts_check_time,
                'gift_id'       => $nLiveGiftId,
                'gift_num'      => $nGiftNumber,
                'gift_name'     => $oLiveGift->live_gift_name,
                'gift_logo'     => $oLiveGift->live_gift_logo,
                'dot'           => $nDot,
            ]);
            $oShortPostsMessage->save();

            // 赠送特定礼物飘屏
            if ( $oLiveGift->live_gift_notice_flg == 'Y' ) {
                $this->timServer->setRid(APP_ENV == 'dev' ? 'total_user_dev' : 'total_user');
                $flg = $this->timServer->sendScrollMsg([
                    'type' => 'notice_gift',
                    'info' => [
                        'user_nickname' => $oUser->user_nickname,
                        'user_avatar'   => $oUser->user_avatar,
                        'gift_logo'     => $oLiveGift->live_gift_logo,
                        'gift_name'     => $oLiveGift->live_gift_name,
                        'gift_id'       => $oLiveGift->live_gift_id,
                        'title'         => sprintf('%s 赠送了 %s', $oUser->user_nickname, $oSaleUser->user_nickname),
                        'content'       => $oLiveGift->live_gift_name,
                    ]
                ]);
            }


        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/giftList
     * @api {get} /user/shortposts/giftList 帖子打赏列表
     * @apiName shortposts-giftList
     * @apiGroup ShortPosts
     * @apiDescription 帖子打赏列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.s_log_id   记录id
     * @apiSuccess {number} d.items.user_id   打赏用户id
     * @apiSuccess {String} d.items.user_avatar   打赏用户头像
     * @apiSuccess {String} d.items.user_nickname  打赏用户昵称
     * @apiSuccess {number} d.items.user_member_expire_time VIP过期时间
     * @apiSuccess {number} d.items.user_level  打赏用户等级
     * @apiSuccess {number} d.items.user_sex  打赏用户性别
     * @apiSuccess {number} d.items.user_birth  打赏用户生日
     * @apiSuccess {number} d.items.create_time   创建时间戳
     * @apiSuccess {String} d.items.gift_name  礼物名称
     * @apiSuccess {String} d.items.gift_logo  礼物logo
     * @apiSuccess {number} d.items.gift_num  礼物数量
     * @apiSuccess {number} d.items.get_dot   主播收益
     * @apiSuccess {number} d.items.send_coin   用户消费
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "items": [
     *               {
     *                   "gift_log_id": "1",
     *                   "user_id": "313",
     *                   "user_avatar": "http://thirdqq.qlogo.cn/qqapp/1106652113/9E3CD73EA8A959B6B2C44F9C7EA5FD27/100",
     *                   "user_nickname": "雨晴👄👄👄",
     *                   "user_member_expire_time": "0",
     *                   "user_level": "3",
     *                   "user_sex": "2",
     *                   "user_birth": "",
     *                   "create_time": "1546424288",
     *                   "gift_name": "钻石戒指",
     *                   "gift_logo": "https://lebolive-1255651273.image.myqcloud.com/image/20180904/1536053424745177.png",
     *                   "gift_num": "1",
     *                   "get_dot": "20.80",
     *                   "send_coin": "520"
     *               }
     *           ],
     *           "page": 1,
     *           "pagesize": 20,
     *           "pagetotal": 1,
     *           "total": 1,
     *           "prev": 1,
     *           "next": 1
     *       },
     *       "t": "1546424364"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function giftListAction( $nUserId = 0 )
    {
        $sPostsId  = $this->getParams('posts_id');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            $columns = 'g.id as gift_log_id,u.user_id,u.user_avatar,u.user_nickname,u.user_member_expire_time,u.user_level,u.user_sex,u.user_birth,
            g.create_time,g.gift_name,g.gift_logo,g.gift_num,g.get_dot,g.send_coin';
            $builder = $this->modelsManager->createBuilder()
                ->from([ 'g' => ShortPostsGift::class ])
                ->columns($columns)
                ->join(User::class, 'u.user_id = g.user_id', 'u')
                ->where('g.short_posts_id = :short_posts_id:', [ 'short_posts_id' => $sPostsId ])
                ->orderBy('g.create_time desc');
            $row     = $this->page($builder, $nPage, $nPagesize);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/replyList
     * @api {get} /user/shortposts/replyList 评论回复列表
     * @apiName shortposts-replyList
     * @apiGroup ShortPosts
     * @apiDescription 评论回复列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} comment_id 评论id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} comment_id 评论id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.reply_id    回复id
     * @apiSuccess {number} d.items.user_id  用户id
     * @apiSuccess {String} d.items.user_avatar  用户头像
     * @apiSuccess {String} d.items.user_nickname  用户昵称
     * @apiSuccess {number} d.items.user_member_expire_time   VIP过期时间
     * @apiSuccess {number} d.items.user_level  用户等级
     * @apiSuccess {number} d.items.user_sex  用户性别
     * @apiSuccess {number} d.items.create_time   创建时间
     * @apiSuccess {number} d.items.at_user_id    @用户id
     * @apiSuccess {String} d.items.at_user_nickname   @用户昵称
     * @apiSuccess {String} d.items.reply_content  回复内容
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "comment": {
     *                   "comment_id": "2",
     *                   "user_id": "324",
     *                   "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106563075\/BC33171CABF6130E37A9D94856724FB5\/100",
     *                   "user_nickname": "倔强的小青虫丶12141616411",
     *                   "user_sex": "1",
     *                   "user_birth": "",
     *                   "user_level": "1",
     *                   "user_is_member": "N",
     *                   "create_time": "1547114639",
     *                   "comment_content": "这是第二条回复",
     *                   "at_user_id": "0",
     *                   "at_user_nickname": "",
     *                   "show_reply_user_id": "324",
     *                   "comment_like_num": 0,
     *                   "show_reply_content": "这是微信213213123复",
     *                   "show_reply_user_nickname": "倔强的小青虫丶12141616411",
     *                   "reply_num": 2,
     *                   "comment_like_id": "0"
     *           },
     *           "reply": {
     *                   "items": [{
     *                       "reply_id": "35",
     *                       "user_id": "324",
     *                       "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106563075\/BC33171CABF6130E37A9D94856724FB5\/100",
     *                       "user_nickname": "倔强的小青虫丶12141616411",
     *                       "user_member_expire_time": "0",
     *                       "user_level": "1",
     *                       "user_sex": "1",
     *                       "create_time": "1547545005",
     *                       "at_user_id": "0",
     *                       "at_user_nickname": "",
     *                       "reply_content": "这是微信213213123复"
     *                   }, {
     *                       "reply_id": "9",
     *                       "user_id": "324",
     *                       "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106563075\/BC33171CABF6130E37A9D94856724FB5\/100",
     *                       "user_nickname": "倔强的小青虫丶12141616411",
     *                       "user_member_expire_time": "0",
     *                       "user_level": "1",
     *                       "user_sex": "1",
     *                       "create_time": "1547115895",
     *                       "at_user_id": "0",
     *                       "at_user_nickname": "",
     *                       "reply_content": "这是222bbbbbb复"
     *               }],
     *               "page": 1,
     *               "pagesize": 20,
     *               "pagetotal": 1,
     *               "total": 2,
     *               "prev": 1,
     *               "next": 1
     *           }
     *       },
     *       "t": "1547627709"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function replyListAction( $nUserId = 0 )
    {

        $sCommentId = $this->getParams('comment_id');
        $nPage      = $this->getParams('page', 'int', 0);
        $nPagesize  = $this->getParams('pagesize', 'int', 20);
        try {
            $oShortPostsComment = ShortPostsComment::findFirst($sCommentId);
            if ( !$oShortPostsComment ) {
                $oShortPostsComment = new ShortPostsComment();
            }
            $oUser                  = User::findFirst($oShortPostsComment->user_id);
            $columns                = 'r.reply_id,u.user_id,u.user_avatar,u.user_nickname,u.user_member_expire_time,u.user_level,u.user_sex,u.user_birth,
            r.create_time,r.at_user_id,r.at_user_nickname,r.reply_content';
            $builder                = $this->modelsManager->createBuilder()
                ->from([ 'r' => ShortPostsCommentReply::class ])
                ->columns($columns)
                ->join(User::class, 'u.user_id = r.user_id', 'u')
                ->where('r.is_comment ="N" AND r.comment_id = :comment_id: AND r.reply_status = "Y"', [ 'comment_id' => $sCommentId ])
                ->orderBy('r.create_time desc');
            $oShortPostsCommentLike = ShortPostsCommentLike::findFirst([
                'comment_id = :comment_id: AND user_id = :user_id:',
                'bind' => [
                    'comment_id' => $sCommentId,
                    'user_id'    => $nUserId
                ]
            ]);
            $row                    = [
                'comment' => [
                    'comment_id'               => (string)$oShortPostsComment->comment_id,
                    'user_id'                  => (string)$oUser->user_id,
                    'user_avatar'              => $oUser->user_avatar,
                    'user_nickname'            => $oUser->user_nickname,
                    'user_sex'                 => $oUser->user_sex,
                    'user_birth'               => $oUser->user_birth,
                    'user_level'               => $oUser->user_level,
                    'user_is_member'           => $oUser->user_member_expire_time > time() ? 'Y' : 'N',
                    'create_time'              => (string)$oShortPostsComment->create_time,
                    'comment_content'          => $oShortPostsComment->comment_content,
                    'at_user_id'               => (string)$oShortPostsComment->at_user_id,
                    'at_user_nickname'         => $oShortPostsComment->at_user_nickname,
                    'show_reply_user_id'       => (string)$oShortPostsComment->show_reply_user_id,
                    'comment_like_num'         => intval($oShortPostsComment->comment_like_num),
                    'show_reply_content'       => $oShortPostsComment->show_reply_content,
                    'show_reply_user_nickname' => $oShortPostsComment->show_reply_user_nickname,
                    'reply_num'                => intval($oShortPostsComment->reply_num),
                    'comment_like_id'          => $oShortPostsCommentLike ? $oShortPostsCommentLike->id : '0',
                ],
                'reply'   => $this->page($builder, $nPage, $nPagesize),
            ];
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/detail
     * @api {get} /user/shortposts/detail 动态详情
     * @apiName shortposts-detail
     * @apiGroup ShortPosts
     * @apiDescription 动态详情
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (正常请求){String='Y','N'} is_new 是否需要增加观看数
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiParam (debug){String='Y','N'} is_new 是否需要增加观看数
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object} d.posts_detail   动态详情
     * @apiSuccess {number} d.posts_detail.user_id   用户id
     * @apiSuccess {String} d.posts_detail.user_avatar  用户头像
     * @apiSuccess {String} d.posts_detail.user_nickname   用户昵称
     * @apiSuccess {number} d.posts_detail.user_level  用户等级
     * @apiSuccess {number} d.posts_detail.user_member_expire_time 用户VIP过期时间
     * @apiSuccess {number} d.posts_detail.user_is_member  用户是否为会员
     * @apiSuccess {number} d.posts_detail.user_is_anchor  用户是否为主播
     * @apiSuccess {number} d.posts_detail.user_is_superadmin  Y为官方发布
     * @apiSuccess {number} d.posts_detail.user_sex  用户性别
     * @apiSuccess {number} d.posts_detail.user_birth  用户生日
     * @apiSuccess {number} d.posts_detail.create_time   创建时间
     * @apiSuccess {String} d.posts_detail.short_posts_id   动态id
     * @apiSuccess {String} d.posts_detail.short_posts_position   定位
     * @apiSuccess {String} d.posts_detail.short_posts_word  文字
     * @apiSuccess {String} d.posts_detail.short_posts_images  图片 半角逗号分隔
     * @apiSuccess {String} d.posts_detail.short_posts_video  视频地址
     * @apiSuccess {String} d.posts_detail.short_posts_type  动态类型
     * @apiSuccess {number} d.posts_detail.short_posts_watch_num   观看人数
     * @apiSuccess {number} d.posts_detail.short_posts_comment_num  评论人数
     * @apiSuccess {number} d.posts_detail.short_posts_gift_num   打赏人次
     * @apiSuccess {number} d.posts_detail.short_posts_like_num 点赞数量
     * @apiSuccess {number} d.posts_detail.short_posts_collect_num  收藏数量
     * @apiSuccess {number} d.posts_detail.posts_like_id   点赞id 未点赞为 0
     * @apiSuccess {number} d.posts_detail.short_posts_is_top   是否置顶
     * @apiSuccess {String='Y','N'} d.posts_detail.has_buy   是否购买
     * @apiSuccess {String='free(免费)','part_free(图片前两张免费)','pay(付费)'} d.posts_detail.short_posts_pay_type   付费类型
     * @apiSuccess {String} d.posts_detail.short_posts_price   价格
     * @apiSuccess {object[]} d.gift_list   打赏列表
     * @apiSuccess {number} d.gift_list.user_id   打赏用户id
     * @apiSuccess {String} d.gift_list.user_avatar  打赏用户头像
     * @apiSuccess {String} d.gift_list.user_nickname 打赏用户昵称
     * @apiSuccess {object[]} d.buy_list   购买列表
     * @apiSuccess {number} d.buy_list.user_id   购买用户id
     * @apiSuccess {String} d.buy_list.user_avatar  购买用户头像
     * @apiSuccess {String} d.buy_list.user_nickname 购买用户昵称
     * @apiSuccess {object[]} d.like_list   点赞列表
     * @apiSuccess {number} d.like_list.user_id  点赞用户id
     * @apiSuccess {String} d.like_list.user_avatar  点赞用户头像
     * @apiSuccess {String} d.like_list.user_nickname 点赞用户昵称
     * @apiSuccess {object[]} d.first_comment_page  第一页评论列表
     * @apiSuccess {number} d.first_comment_page.items.comment_id   评论id
     * @apiSuccess {number} d.first_comment_page.items.user_id   用户id
     * @apiSuccess {String} d.first_comment_page.items.user_avatar   用户头像
     * @apiSuccess {String} d.first_comment_page.items.user_nickname  用户昵称
     * @apiSuccess {String} d.first_comment_page.items.user_level  用户等级
     * @apiSuccess {number} d.first_comment_page.items.user_member_expire_time  用户VIP 过期时间
     * @apiSuccess {number} d.first_comment_page.items.create_time  发表时间
     * @apiSuccess {String} d.first_comment_page.items.comment_content  评论内容
     * @apiSuccess {number} d.first_comment_page.items.at_user_id    @用户id
     * @apiSuccess {String} d.first_comment_page.items.at_user_nickname @用户昵称
     * @apiSuccess {number} d.first_comment_page.items.comment_like_num   评论点赞数
     * @apiSuccess {number} d.first_comment_page.items.show_reply_user_id   显示的回复用户id
     * @apiSuccess {String} d.first_comment_page.items.show_reply_content  显示的回复内容
     * @apiSuccess {String} d.first_comment_page.items.show_reply_user_nickname   显示的回复用户昵称
     * @apiSuccess {number} d.first_comment_page.items.reply_num   总回复数
     * @apiSuccess {number} d.first_comment_page.items.comment_like_id   评论点赞id  自己如果点赞过 则显示为评论点赞id 否则为 0
     * @apiSuccess {String} d.is_like   是否点赞 Y 为是 N 为否
     * @apiSuccess {String} d.is_collect  是否收藏 Y 为是 N 为否
     * @apiSuccess {String} d.is_report   是否举报 Y 为是 N 为否
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "m": "请求成功",
     *         "d": {
     *                 "posts_detail": {
     *                     "user_id": "318",
     *                     "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1107915107\/63F3F098E6FAC4B5C210CA2458C66BE6\/100",
     *                     "user_nickname": "渐入佳境",
     *                     "user_level": "1",
     *                     "user_member_expire_time": "0",
     *                     "user_is_member": "N",
     *                     "user_is_anchor": "N",
     *                     "user_is_superadmin": "N",
     *                     "user_sex": "2",
     *                     "user_birth": "",
     *                     "create_time": "1546412208",
     *                     "short_posts_position": "深圳",
     *                     "short_posts_word": "美女美女",
     *                     "short_posts_images": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/12\/30\/6656bb25f764335232179e893ff65afe,https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/12\/28\/1546006714328.png",
     *                     "short_posts_video": "",
     *                     "short_posts_type": "image",
     *                     "short_posts_watch_num": "0",
     *                     "short_posts_comment_num": "1",
     *                     "short_posts_gift_num": "0",
     *                     "short_posts_like_num": "0",
     *                     "short_posts_collect_num": "0",
     *                     "posts_like_id": "0",
     *                     "short_posts_is_top": "Y",
     *                     "has_buy": "Y",
     *                     "short_posts_pay_type": "Y",
     *                     "short_posts_price": "Y"
     *                 },
     *                 "gift_list": [{
     *                         "user_id": "313",
     *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9E3CD73EA8A959B6B2C44F9C7EA5FD27\/100",
     *                         "user_nickname": "雨晴👄👄👄"
     *                 }],
     *                 "like_list": [{
     *                         "user_id": "313",
     *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9E3CD73EA8A959B6B2C44F9C7EA5FD27\/100",
     *                         "user_nickname": "雨晴👄👄👄"
     *                 }],
     *                 "first_comment_page": {
     *                         "items": [{
     *                             "comment_id": "1",
     *                             "user_id": "313",
     *                             "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9E3CD73EA8A959B6B2C44F9C7EA5FD27\/100",
     *                             "user_nickname": "雨晴👄👄👄",
     *                             "user_member_expire_time": "0",
     *                             "create_time": "1546426245",
     *                             "comment_content": "好贴",
     *                             "at_user_id": "317",
     *                             "at_user_nickname": "Dawn11261527320",
     *                             "user_level": "3",
     *                             "comment_like_num": "0",
     *                             "show_reply_user_id": "0",
     *                             "show_reply_content": "",
     *                             "show_reply_user_nickname": "",
     *                             "reply_num": "0",
     *                             "comment_like_id": "0"
     *                         }],
     *                         "page": 1,
     *                         "pagesize": 20,
     *                         "pagetotal": 1,
     *                         "total": 1,
     *                         "prev": 1,
     *                         "next": 1
     *                     },
     *                 "is_like": "N",
     *                 "is_collect": "N",
     *                 "is_report": "N"
     *         },
     *         "t": "1546483749"
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function detailAction( $nUserId = 0 )
    {

        $sPostsId = $this->getParams('posts_id');
        // 是否查看 Y 则需要记录观看次数
        $isNew     = $this->getParams('is_new');
        $nPage     = 1;
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                $oShortPostsDelete = ShortPostsDelete::findFirst($sPostsId);
                if ( !$oShortPostsDelete ) {
                    throw new Exception(
                        ResponseError::getError(ResponseError::PARAM_ERROR),
                        ResponseError::PARAM_ERROR
                    );
                } else {
                    throw new Exception(
                        ResponseError::getError(ResponseError::POSTS_DELETE),
                        ResponseError::POSTS_DELETE
                    );
                }

            }
            $oShortPostsUser = User::findFirst($oShortPosts->short_posts_user_id);
            // 打赏过的人头像
            $giftUserSql = 'SELECT distinct u.user_id,u.user_avatar,u.user_nickname from short_posts_gift as g inner join user as u on g.user_id = u.user_id where g.short_posts_id = :short_posts_id order by g.create_time desc limit 6';
            $giftRes     = $this->db->query($giftUserSql, [
                'short_posts_id' => $sPostsId
            ]);
            $giftRes->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            // 购买过的人头像
            $buyUserSql = 'SELECT distinct u.user_id,u.user_avatar,u.user_nickname from short_posts_buy as g inner join user as u on g.user_id = u.user_id where g.short_posts_id = :short_posts_id order by g.create_time desc limit 6';
            $buyRes     = $this->db->query($buyUserSql, [
                'short_posts_id' => $sPostsId
            ]);
            $buyRes->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            // 赞过的人的头像
            $likeUserSql = 'SELECT u.user_id,u.user_avatar,u.user_nickname from short_posts_like as l inner join user as u on l.user_id = u.user_id where l.short_posts_id = :short_posts_id order by l.create_time desc limit 6';
            $likeRes     = $this->db->query($likeUserSql, [
                'short_posts_id' => $sPostsId
            ]);
            $likeRes->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
            // 评论第一页
            $commentList = $this->_getCommentList($nUserId, $sPostsId, $nPage, $nPagesize, 'hot');
            // 是否点赞
            $oShortPostsLike = ShortPostsLike::findFirst([
                'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                'bind' => [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ]
            ]);
            // 是否收藏
            $oShortPostsCollect = ShortPostsCollect::findFirst([
                'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                'bind' => [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ]
            ]);
            // 是否举报
            $oShortPostsReport = ShortPostsReport::findFirst([
                'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                'bind' => [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ]
            ]);
            $hasBuy            = 'N';
            if ( $oShortPosts->short_posts_pay_type != ShortPosts::PAY_TYPE_FREE ) {
                $oShortPostsBuy = ShortPostsBuy::findFirst([
                    'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                    'bind' => [
                        'short_posts_id' => $sPostsId,
                        'user_id'        => $nUserId
                    ]
                ]);
                IF ( $oShortPostsBuy ) {
                    $hasBuy = 'Y';
                }
            }

            $short_posts_discount_price = $oShortPosts->short_posts_price;
            $oUser                      = User::findFirst($nUserId);
            if ( $oUser->user_member_expire_time > time() && $oShortPosts->short_posts_type == ShortPosts::TYPE_EXHIBITION ) {
                $vipInfo                    = VipLevel::getVipInfo($oUser->user_vip_level);
                $short_posts_discount_price = sprintf('%.2f', $oShortPosts->short_posts_price * $vipInfo->vip_level_exhibition_discount / 10);
            }

            $row = [
                'posts_detail'       => [
                    'user_id'                    => $oShortPostsUser->user_id,
                    'user_avatar'                => $oShortPostsUser->user_avatar,
                    'user_nickname'              => $oShortPostsUser->user_nickname,
                    'user_level'                 => $oShortPostsUser->user_level,
                    'user_member_expire_time'    => $oShortPostsUser->user_member_expire_time,
                    'user_is_member'             => $oShortPostsUser->user_member_expire_time > time() ? 'Y' : 'N',
                    'user_is_anchor'             => $oShortPostsUser->user_is_anchor,
                    'user_is_superadmin'         => $oShortPostsUser->user_is_superadmin,
                    'user_sex'                   => $oShortPostsUser->user_sex,
                    'user_birth'                 => $oShortPostsUser->user_birth,
                    'create_time'                => $oShortPosts->short_posts_check_time ? $oShortPosts->short_posts_check_time : $oShortPosts->short_posts_create_time,
                    'short_posts_id'             => $oShortPosts->short_posts_id,
                    'short_posts_position'       => $oShortPosts->short_posts_position,
                    'short_posts_word'           => $oShortPosts->short_posts_word,
                    'short_posts_images'         => $oShortPosts->short_posts_images,
                    'short_posts_video'          => $oShortPosts->short_posts_video,
                    'short_posts_type'           => $oShortPosts->short_posts_type,
                    'short_posts_watch_num'      => intval($oShortPosts->short_posts_watch_num),
                    'short_posts_comment_num'    => intval($oShortPosts->short_posts_comment_num),
                    'short_posts_gift_num'       => intval($oShortPosts->short_posts_gift_num),
                    'short_posts_like_num'       => intval($oShortPosts->short_posts_like_num),
                    'short_posts_collect_num'    => intval($oShortPosts->short_posts_collect_num),
                    'posts_like_id'              => $oShortPostsLike ? $oShortPostsLike->id : "0",
                    'short_posts_is_top'         => $oShortPosts->short_posts_is_top,
                    'has_buy'                    => $hasBuy,
                    'short_posts_pay_type'       => $nUserId == $oShortPosts->short_posts_user_id ? ShortPosts::PAY_TYPE_FREE : $oShortPosts->short_posts_pay_type,
                    'short_posts_price'          => $oShortPosts->short_posts_price,
                    'short_posts_discount_price' => $short_posts_discount_price,
                    'short_posts_buy_num'        => $oShortPosts->short_posts_buy_num,
                    'show_width'                 => intval($oShortPosts->short_posts_show_width),
                    'show_height'                => intval($oShortPosts->short_posts_show_height),
                ],
                'gift_list'          => $giftRes->fetchAll(),
                'like_list'          => $likeRes->fetchAll(),
                'buy_list'           => $buyRes->fetchAll(),
                'first_comment_page' => $commentList,
                'is_like'            => $oShortPostsLike ? 'Y' : 'N',
                'is_collect'         => $oShortPostsCollect ? 'Y' : 'N',
                'is_report'          => $oShortPostsReport ? 'Y' : 'N',
            ];
            if ( $isNew == 'Y' ) {
                $oShortPosts->short_posts_watch_num += 1;
                $oShortPosts->save();
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/myComment
     * @api {get} /user/shortposts/myComment 我的评论列表
     * @apiName shortposts-myComment
     * @apiGroup ShortPosts
     * @apiDescription 我的评论列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.short_posts_id    动态id  如果是0 则表示 动态已删除
     * @apiSuccess {number} d.items.user_id    发布动态用户id
     * @apiSuccess {String} d.items.user_avatar  发布动态用户头像
     * @apiSuccess {String} d.items.user_nickname   发布动态用户昵称
     * @apiSuccess {String} d.items.user_birth   发布动态用户生日
     * @apiSuccess {number} d.items.user_member_expire_time   发布动态用户VIP 过期时间戳
     * @apiSuccess {number} d.items.user_is_member   发布动态用户是否为VIP
     * @apiSuccess {number} d.items.user_level  发布动态用户等级
     * @apiSuccess {number} d.items.user_sex   发布动态用户性别
     * @apiSuccess {number} d.items.create_time    动态发布时间
     * @apiSuccess {String} d.items.short_posts_position    动态位置
     * @apiSuccess {String} d.items.short_posts_word   动态文字
     * @apiSuccess {String} d.items.short_posts_images  动态图片 半角逗号分隔
     * @apiSuccess {String} d.items.short_posts_video   动态视频地址
     * @apiSuccess {String} d.items.short_posts_type  动态类型
     * @apiSuccess {number} d.items.short_posts_watch_num   观看人数
     * @apiSuccess {number} d.items.short_posts_comment_num  评论人数
     * @apiSuccess {number} d.items.short_posts_gift_num   礼物数量
     * @apiSuccess {number} d.items.short_posts_like_num  点赞数量
     * @apiSuccess {number} d.items.short_posts_collect_num    收藏数量
     * @apiSuccess {String} d.items.comment_content   评论内容
     * @apiSuccess {String} d.items.comment_id   评论id
     * @apiSuccess {String} d.items.is_comment   Y 为评论  N 为评论回复
     * @apiSuccess {String} d.items.comment_create_time  评论时间戳
     * @apiSuccess {String} d.items.comment_status   评论状态  C 为 仅自己可见，Y为全部可见 N 为不显示
     * @apiSuccess {String} d.items.comment_reply_num   评论回复数
     * @apiSuccess {String} d.items.comment_like_num   评论点赞数
     * @apiSuccess {String} d.items.comment_user_id   评论的用户ID
     * @apiSuccess {String} d.items.comment_user_avatar  评论的用户头像
     * @apiSuccess {String} d.items.comment_user_nickname   评论的用户昵称
     * @apiSuccess {String} d.items.comment_user_is_member   评论的用户是否为VIP
     * @apiSuccess {String} d.items.comment_user_level   评论的用户的等级
     * @apiSuccess {String} d.items.comment_user_sex   评论的用户性别
     * @apiSuccess {String} d.items.comment_user_birth   评论的用户生日
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "items": [{
     *                   "short_posts_id": "1",
     *                   "user_id": "318",
     *                   "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1107915107\/63F3F098E6FAC4B5C210CA2458C66BE6\/100",
     *                   "user_nickname": "渐入佳境",
     *                   "user_member_expire_time": "0",
     *                   "user_level": "1",
     *                   "user_sex": "2",
     *                   "user_birth": "1995-07-11",
     *                   "create_time": "1546412208",
     *                   "short_posts_position": "深圳",
     *                   "short_posts_word": "美女美女",
     *                   "short_posts_images": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/12\/30\/6656bb25f764335232179e893ff65afe,https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/12\/28\/1546006714328.png",
     *                   "short_posts_video": "",
     *                   "short_posts_type": "image",
     *                   "short_posts_watch_num": "0",
     *                   "short_posts_comment_num": "1",
     *                   "short_posts_gift_num": "0",
     *                   "short_posts_like_num": "0",
     *                   "short_posts_status": "Y",
     *                   "comment_content": "好贴",
     *                   "is_comment": "Y",
     *                   "comment_create_time": "1546426245",
     *                   "comment_status": "N",
     *                   "comment_id": "10",
     *                   "user_is_member": "N",
     *                   "comment_user_id": "313",
     *                   "comment_user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9E3CD73EA8A959B6B2C44F9C7EA5FD27\/100",
     *                   "comment_user_nickname": "雨晴👄👄👄",
     *                   "comment_user_is_member": "N",
     *                   "comment_user_level": "1",
     *                   "comment_user_sex": "2",
     *                   "comment_user_birth": ""
     *           }],
     *           "page": 1,
     *           "pagesize": 20,
     *           "pagetotal": 1,
     *           "total": 1,
     *           "prev": 1,
     *           "next": 1
     *       },
     *       "t": "1547018825"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function myCommentAction( $nUserId = 0 )
    {

        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $nPagesize = max($nPagesize, 20);
        try {
            $order   = 'l.create_time desc';
            $columns = 'ifnull(c.short_posts_id,"0") as short_posts_id,u.user_id,u.user_avatar,u.user_nickname,u.user_member_expire_time,u.user_level,u.user_sex,u.user_birth,
            c.short_posts_create_time as create_time,c.short_posts_position,c.short_posts_word,c.short_posts_images,c.short_posts_video,
            c.short_posts_type,c.short_posts_watch_num,c.short_posts_comment_num,c.short_posts_gift_num,c.short_posts_like_num,
            c.short_posts_status,
            l.reply_content as comment_content,l.create_time as comment_create_time,l.reply_status as comment_status,l.comment_id,l.is_comment';
            $builder = $this->modelsManager->createBuilder()
                ->from([ 'l' => ShortPostsCommentReply::class ])
                ->columns($columns)
                ->leftJoin(ShortPosts::class, 'l.short_posts_id = c.short_posts_id', 'c')
                ->join(ShortPostsComment::class, 'l.comment_id = cc.comment_id', 'cc')
                ->leftJoin(User::class, 'u.user_id = c.short_posts_user_id', 'u')
                ->where('l.user_id = ' . $nUserId)
                ->orderBy($order);
            $row     = $this->page($builder, $nPage, $nPagesize);
            $oUser   = User::findFirst($nUserId);
            foreach ( $row['items'] as &$item ) {
                $item['user_is_member']         = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
                $item['comment_user_id']        = $oUser->user_id;
                $item['comment_user_avatar']    = $oUser->user_avatar;
                $item['comment_user_nickname']  = $oUser->user_nickname;
                $item['comment_user_is_member'] = $oUser->user_member_expire_time > time() ? 'Y' : 'N';
                $item['comment_user_level']     = $oUser->user_level;
                $item['comment_user_sex']       = $oUser->user_sex;
                $item['comment_user_birth']     = $oUser->user_birth;
                if ( $item['is_comment'] == 'N' ) {
                    $item['comment_content'] = '回复：' . $item['comment_content'];
                }
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/delete
     * @api {post} /user/shortposts/delete 动态删除
     * @apiName shortposts-delete
     * @apiGroup ShortPosts
     * @apiDescription 动态删除
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function deleteAction( $nUserId = 0 )
    {
        $sPostsId = $this->getParams('posts_id');
        try {
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                throw new Exception(
                    '该动态不存在[' . $sPostsId,
                    ResponseError::PARAM_ERROR
                );
            }
            if ( $oShortPosts->short_posts_user_id != $nUserId ) {
                throw new Exception(
                    '无权进行此操作',
                    ResponseError::PARAM_ERROR
                );
            }
            // 将数据存入delete表
            $this->db->begin();
            $oShortPostsDelete = new ShortPostsDelete();
            if ( !$oShortPostsDelete->save($oShortPosts->toArray()) ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsDelete->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            if ( !$oShortPosts->delete() ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPosts->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            $this->db->commit();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/deleteComment
     * @api {post} /user/shortposts/deleteComment 动态评论删除
     * @apiName shortposts-deleteComment
     * @apiGroup ShortPosts
     * @apiDescription 动态评论删除
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} comment_id 评论id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} comment_id 动态id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function deleteCommentAction( $nUserId = 0 )
    {
        $sCommentId = $this->getParams('comment_id');
        try {
            $oShortPostsComment = ShortPostsComment::findFirst($sCommentId);
            if ( !$oShortPostsComment || $oShortPostsComment->user_id != $nUserId ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            // 将数据存入delete表
            $this->db->begin();
            $oShortPostsCommentDelete = new ShortPostsCommentDelete();
            if ( !$oShortPostsCommentDelete->save($oShortPostsComment->toArray()) ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentDelete->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            if ( !$oShortPostsComment->delete() ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsComment->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            //同时删除掉复制
            $oShortPostsCommentReply = ShortPostsCommentReply::findFirst([
                'comment_id = :comment_id: AND is_comment = "Y"',
                'bind' => [
                    'comment_id' => $sCommentId
                ]
            ]);
            if ( $oShortPostsCommentReply ) {

                $oShortPostsCommentReplyDelete = new ShortPostsCommentReplyDelete();
                if ( !$oShortPostsCommentReplyDelete->save($oShortPostsCommentReply->toArray()) ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentReplyDelete->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
                if ( !$oShortPostsCommentReply->delete() ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentReply->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
            }

            if ( $oShortPostsComment->comment_status == 'Y' ) {
                // 只有审核通过的 才算做评论数
                $oShortPosts                          = ShortPosts::findFirst($oShortPostsComment->short_posts_id);
                $oShortPosts->short_posts_comment_num = $oShortPosts->short_posts_comment_num - 1 - $oShortPostsComment->reply_num;
                if ( $oShortPosts->short_posts_comment_num < 0 ) {
                    $oShortPosts->short_posts_comment_num = 0;
                }
                if ( !$oShortPosts->save() ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPosts->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }
            }

            $this->db->commit();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/deleteReply
     * @api {post} /user/shortposts/deleteReply 动态评论回复删除
     * @apiName shortposts-deleteReply
     * @apiGroup ShortPosts
     * @apiDescription 动态评论回复删除
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} reply_id 评论回复id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} reply_id 评论回复id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function deleteReplyAction( $nUserId = 0 )
    {
        $sReplyId = $this->getParams('reply_id');
        try {
            $oShortPostsCommentReply = ShortPostsCommentReply::findFirst($sReplyId);
            if ( !$oShortPostsCommentReply || $oShortPostsCommentReply->user_id != $nUserId ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $isComment          = $oShortPostsCommentReply->is_comment;
            $sCommentId         = $oShortPostsCommentReply->comment_id;
            $oShortPostsComment = ShortPostsComment::findFirst($sCommentId);
            // 将数据存入delete表
            $this->db->begin();
            $oShortPostsCommentReplyDelete = new ShortPostsCommentReplyDelete();
            if ( !$oShortPostsCommentReplyDelete->save($oShortPostsCommentReply->toArray()) ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentReplyDelete->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            if ( !$oShortPostsCommentReply->delete() ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentReply->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }
            if ( $oShortPostsComment ) {
                if ( $isComment == 'Y' ) {
                    $oShortPostsCommentDelete = new ShortPostsCommentDelete();
                    if ( !$oShortPostsCommentDelete->save($oShortPostsComment->toArray()) ) {
                        $this->db->rollback();
                        throw new Exception(
                            sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsCommentDelete->getMessages())),
                            ResponseError::OPERATE_FAILED
                        );
                    }
                    if ( !$oShortPostsComment->delete() ) {
                        $this->db->rollback();
                        throw new Exception(
                            sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsComment->getMessages())),
                            ResponseError::OPERATE_FAILED
                        );
                    }
                    if ( $oShortPostsComment->comment_status == 'Y' ) {
                        // 只有审核通过的 才算做评论数
                        $oShortPosts                          = ShortPosts::findFirst($oShortPostsComment->short_posts_id);
                        $oShortPosts->short_posts_comment_num -= 1;
                        if ( $oShortPosts->short_posts_comment_num >= 0 ) {
                            if ( !$oShortPosts->save() ) {
                                $this->db->rollback();
                                throw new Exception(
                                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPosts->getMessages())),
                                    ResponseError::OPERATE_FAILED
                                );
                            }
                        }

                    }
                } else {
                    if ( $oShortPostsCommentReply->reply_status == 'Y' ) {
                        // 只有审核通过的 才算做评论数
                        $oShortPostsComment->reply_num -= 1;
                        if ( $oShortPostsComment->reply_num <= 0 ) {
                            $oShortPostsComment->show_reply_id               = 0;
                            $oShortPostsComment->show_reply_user_id          = 0;
                            $oShortPostsComment->show_reply_content          = '';
                            $oShortPostsComment->show_reply_user_nickname    = '';
                            $oShortPostsComment->show_reply_at_user_id       = 0;
                            $oShortPostsComment->show_reply_at_user_nickname = '';
                        }
                        $oShortPostsComment->update_time = time();
                        // 判断该条回复 是否是评论的最新一条
                        if ( $oShortPostsComment->show_reply_id == $oShortPostsCommentReply->reply_id ) {
                            // 需要取 显示中的最后一条
                            $lastShortPostsCommentReply                      = ShortPostsCommentReply::findFirst([
                                'comment_id = :comment_id: AND reply_status = "Y"',
                                'bind'  => [
                                    'comment_id' => $oShortPostsComment->comment_id,
                                ],
                                'order' => 'create_time desc'
                            ]);
                            $oShortPostsComment->show_reply_id               = 0;
                            $oShortPostsComment->show_reply_user_id          = 0;
                            $oShortPostsComment->show_reply_content          = '';
                            $oShortPostsComment->show_reply_user_nickname    = '';
                            $oShortPostsComment->show_reply_at_user_id       = 0;
                            $oShortPostsComment->show_reply_at_user_nickname = '';
                            if ( $lastShortPostsCommentReply ) {
                                $lastUser                                        = User::findFirst($lastShortPostsCommentReply->user_id);
                                $oShortPostsComment->show_reply_id               = $lastShortPostsCommentReply->reply_id;
                                $oShortPostsComment->show_reply_user_id          = $lastShortPostsCommentReply->user_id;
                                $oShortPostsComment->show_reply_content          = $lastShortPostsCommentReply->reply_content;
                                $oShortPostsComment->show_reply_user_nickname    = $lastUser->user_nickname;
                                $oShortPostsComment->show_reply_at_user_id       = $lastShortPostsCommentReply->at_user_id;
                                $oShortPostsComment->show_reply_at_user_nickname = $lastShortPostsCommentReply->at_user_nickname;
                            }
                        }
                        if ( !$oShortPostsComment->save() ) {
                            $this->db->rollback();
                            throw new Exception(
                                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsComment->getMessages())),
                                ResponseError::OPERATE_FAILED
                            );
                        }
                        $oShortPosts                          = ShortPosts::findFirst($oShortPostsComment->short_posts_id);
                        $oShortPosts->short_posts_comment_num -= 1;
                        if ( $oShortPosts->short_posts_comment_num < 0 ) {
                            $oShortPosts->short_posts_comment_num = 0;
                        }
                        if ( !$oShortPosts->save() ) {
                            $this->db->rollback();
                            throw new Exception(
                                sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPosts->getMessages())),
                                ResponseError::OPERATE_FAILED
                            );
                        }

                    }
                }
            }

            $this->db->commit();

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/likeList
     * @api {get} /user/shortposts/likeList 帖子点赞列表
     * @apiName shortposts-likeList
     * @apiGroup ShortPosts
     * @apiDescription 帖子点赞列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.gift_log_id   记录id
     * @apiSuccess {number} d.items.user_id   点赞用户id
     * @apiSuccess {String} d.items.user_avatar   点赞用户头像
     * @apiSuccess {String} d.items.user_nickname  点赞用户昵称
     * @apiSuccess {number} d.items.user_member_expire_time VIP过期时间
     * @apiSuccess {number} d.items.user_is_member 是否是会员
     * @apiSuccess {number} d.items.user_level  点赞用户等级
     * @apiSuccess {number} d.items.user_sex  点赞用户性别
     * @apiSuccess {number} d.items.create_time   创建时间戳
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "items": [
     *               {
     *                   "like_id": "40",
     *                   "user_id": "315",
     *                   "user_avatar": "https://lebolive-1255651273.image.myqcloud.com/image/2018/12/13/1544681627908.png",
     *                   "user_nickname": "LYXXMY",
     *                   "user_member_expire_time": "0",
     *                   "user_level": "2",
     *                   "user_sex": "2",
     *                   "create_time": "1547175586",
     *                   "user_is_member": "N"
     *               }
     *           ],
     *           "page": 1,
     *           "pagesize": 100,
     *           "pagetotal": 1,
     *           "total": 1,
     *           "prev": 1,
     *           "next": 1
     *       },
     *       "t": "1547200768"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function likeListAction( $nUserId = 0 )
    {

        $sPostsId  = $this->getParams('posts_id');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        $nPagesize = min($nPagesize, 100);
        try {
            $columns = 'l.id as like_id,u.user_id,u.user_avatar,u.user_nickname,u.user_member_expire_time,u.user_level,u.user_sex,
            l.create_time';
            $builder = $this->modelsManager->createBuilder()
                ->from([ 'l' => ShortPostsLike::class ])
                ->columns($columns)
                ->join(User::class, 'u.user_id = l.user_id', 'u')
                ->where('l.short_posts_id = :short_posts_id:', [ 'short_posts_id' => $sPostsId ])
                ->orderBy('l.create_time desc');
            $row     = $this->page($builder, $nPage, $nPagesize);
            foreach ( $row['items'] as &$item ) {
                $item['user_is_member'] = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/message
     * @api {get} /user/shortposts/message 动态消息
     * @apiName shortposts-message
     * @apiGroup ShortPosts
     * @apiDescription 动态消息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){Number} page 页码
     * @apiParam (正常请求){Number} pagesize 每页数量
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){Number} page 页码
     * @apiParam (debug){Number} pagesize 每页数量
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.id    消息id
     * @apiSuccess {number} d.items.send_user_id  发送消息的用户id
     * @apiSuccess {String} d.items.send_user_nickname  发送消息的用户昵称
     * @apiSuccess {String} d.items.send_user_avatar 发送消息的用户头像
     * @apiSuccess {number} d.items.send_user_level  发送消息的用户等级
     * @apiSuccess {number} d.items.send_user_sex  发送消息的用户 性别
     * @apiSuccess {number} d.items.send_user_birth  发送消息的用户生日
     * @apiSuccess {number} d.items.user_member_expire_time
     * @apiSuccess {String} d.items.user_is_read   消息是否已读
     * @apiSuccess {String} d.items.send_user_is_member  发送消息的用户是否为VIP
     * @apiSuccess {number} d.items.short_posts_id    动态id
     * @apiSuccess {String='Y','N'} d.items.user_is_read    是否已读  Y为已读
     * @apiSuccess {String='gift(送礼)','reply(评论回复)','comment(评论)','posts_delete(动态删除)','reply_delete(评论删除)'} d.items.message_type   消息类型
     * @apiSuccess {number} d.items.create_time  创建时间戳
     * @apiSuccess {number} d.items.update_time
     * @apiSuccess {number} d.items.user_id   用户id
     * @apiSuccess {String} d.items.message_content  显示内容
     * @apiSuccess {object} d.items.message_target_extra  额外内容信息
     * @apiSuccess {String} d.items.message_target_extra.extra_content 外部额外信息
     * @apiSuccess {String} d.items.message_target_extra.extra_time 外部额外信息时间戳
     * @apiSuccess {String} d.items.message_target_extra.user_nickname 发帖人昵称
     * @apiSuccess {String} d.items.message_target_extra.user_avatar 发帖人头像
     * @apiSuccess {String} d.items.message_target_extra.comment_id 跳转评论id
     * @apiSuccess {String} d.items.message_target_extra.comment_id 跳转评论id
     * @apiSuccess {String} d.items.message_target_extra.gift_id 礼物id
     * @apiSuccess {String} d.items.message_target_extra.gift_num 礼物数量
     * @apiSuccess {String} d.items.message_target_extra.gift_name 礼物名称
     * @apiSuccess {String} d.items.message_target_extra.gift_logo 礼物logo
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "items": [{
     *                   "id": "6",
     *                   "send_user_id": "0",
     *                   "send_user_nickname": "",
     *                   "send_user_avatar": "",
     *                   "send_user_level": "",
     *                   "send_user_sex": "",
     *                   "user_member_expire_time": "",
     *                   "send_user_birth": "",
     *                   "short_posts_id": "26",
     *                   "message_type": "posts_delete",
     *                   "create_time": "1548214493",
     *                   "update_time": "1548214493",
     *                   "user_id": "186",
     *                   "user_is_read": "Y",
     *                   "send_user_is_member": "N"
     *                   "message_content": "你在社区的动态帖子中违反规则 【涉嫌恶意灌水】,相关信息已被清除，请遵守规则，屡次违反规则系统将会作出相应惩罚、封号等措施",
     *                   "message_target_extra": {
     *                           "extra_content": "呵呵哈哈哈",
     *                           "user_nickname": "用户昵称",
     *                           "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180904\/1536053280549579.png",
     *                   }
     *           }, {
     *                   "id": "5",
     *                   "send_user_id": "186",
     *                   "send_user_nickname": "泡泡小妹子",
     *                   "send_user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/09\/05\/1536138026183.png",
     *                   "send_user_level": "1",
     *                   "send_user_sex": "2",
     *                   "user_member_expire_time": "0",
     *                   "send_user_birth": "2008-01-5",
     *                   "short_posts_id": "15",
     *                   "message_type": "reply",
     *                   "create_time": "1548154180",
     *                   "update_time": "1548154180",
     *                   "user_id": "186",
     *                   "user_is_read": "Y",
     *                   "send_user_is_member": "N"
     *                   "message_content": "这是评论",
     *                   "message_target_extra": {
     *                           "comment_id": "40",
     *                       "extra_content": "上班额.8我你说呢"
     *                           "user_nickname": "用户昵称",
     *                           "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180904\/1536053280549579.png",
     *                   }
     *           }, {
     *                   "id": "4",
     *                   "send_user_id": "186",
     *                   "send_user_nickname": "泡泡小妹子",
     *                   "send_user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/09\/05\/1536138026183.png",
     *                   "send_user_level": "1",
     *                   "send_user_sex": "2",
     *                   "user_member_expire_time": "0",
     *                   "send_user_birth": "2008-01-5",
     *                   "short_posts_id": "20",
     *                   "message_type": "comment",
     *                   "create_time": "1548154146",
     *                   "update_time": "1548154146",
     *                   "user_id": "186",
     *                   "user_is_read": "Y",
     *                   "send_user_is_member": "N"
     *                   "message_content": "这是评论",
     *                   "message_target_extra": {
     *                           "comment_id": "46",
     *                       "extra_content": "还不放假密码看看i"
     *                           "user_nickname": "用户昵称",
     *                           "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180904\/1536053280549579.png",
     *                   }
     *           }, {
     *                   "id": "3",
     *                   "send_user_id": "186",
     *                   "send_user_nickname": "泡泡小妹子",
     *                   "send_user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/09\/05\/1536138026183.png",
     *                   "send_user_level": "1",
     *                   "send_user_sex": "2",
     *                   "user_member_expire_time": "0",
     *                   "send_user_birth": "2008-01-5",
     *                   "short_posts_id": "20",
     *                   "message_type": "gift",
     *                   "create_time": "1548154108",
     *                   "update_time": "1548154108",
     *                   "user_id": "186",
     *                   "user_is_read": "Y",
     *                   "send_user_is_member": "N"
     *                   "message_content": "收到礼物",
     *                   "message_target_extra": {
     *                           "extra_content": "还不放假密码看看i",
     *                           "user_nickname": "用户昵称",
     *                           "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180904\/1536053280549579.png",
     *                       "gift_id": "40",
     *                       "gift_num": "2",
     *                       "gift_name": "么么哒",
     *                       "gift_logo": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180904\/1536053280549579.png"
     *                   }
     *           }],
     *           "page": 1,
     *           "pagesize": 100,
     *           "pagetotal": 1,
     *           "total": 4,
     *           "prev": 1,
     *           "next": 1
     *       },
     *       "t": "1548228884"
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function messageAction( $nUserId = 0 )
    {

        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 10);
        $nPagesize = min($nPagesize, 100);
        try {
            // 判断是否有未读消息
            $unreadShortPostsMessage = ShortPostsMessage::findFirst([
                "user_id = :user_id: AND user_is_read = 'N'",
                'bind' => [
                    'user_id' => $nUserId
                ]
            ]);
            $extraWhere              = '';
            if ( $unreadShortPostsMessage ) {
                $extraWhere = ' AND user_is_read = "N"';
            }

            $columns       = 'm.id,m.id as message_id,m.send_user_id,u.user_nickname as send_user_nickname,u.user_avatar as send_user_avatar,u.user_level as send_user_level,
            u.user_sex as send_user_sex,u.user_member_expire_time,u.user_birth as send_user_birth,m.short_posts_id,
            m.message_type,m.create_time,m.user_id,m.message_content,m.message_target_extra,m.user_is_read';
            $builder       = $this->modelsManager->createBuilder()
                ->from([ 'm' => ShortPostsMessage::class ])
                ->leftJoin(User::class, 'u.user_id = m.send_user_id', 'u')
                ->columns($columns)
                ->where('m.user_id = :user_id:' . $extraWhere, [ 'user_id' => $nUserId ])
                ->orderBy('m.create_time desc');
            $row           = $this->page($builder, $nPage, $nPagesize);
            $oUser         = User::findFirst($nUserId);
            $readMessageId = [];
            foreach ( $row['items'] as &$item ) {
                if ( $item['send_user_id'] == 0 ) {
                    $item['send_user_nickname']  = '';
                    $item['send_user_avatar']    = '';
                    $item['send_user_level']     = '';
                    $item['send_user_sex']       = '';
                    $item['send_user_birth']     = '';
                    $item['send_user_is_member'] = 'N';
                } else {
                    $item['send_user_is_member'] = $item['user_member_expire_time'] > time() ? 'Y' : 'N';
                }
                $tmpExtra                     = unserialize($item['message_target_extra']);
                $tmpExtra['user_nickname']    = $oUser->user_nickname;
                $tmpExtra['user_avatar']      = $oUser->user_avatar;
                $item['message_target_extra'] = $tmpExtra;
                if ( $item['user_is_read'] == 'N' ) {
                    $readMessageId[] = $item['id'];
                }
            }
            if ( $readMessageId ) {
                ShortPostsMessage::readMessage($readMessageId);
            }

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }

    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/readMessage
     * @api {post} /user/shortposts/readMessage 读取动态消息
     * @apiName shortposts-readMessage
     * @apiGroup ShortPosts
     * @apiDescription 读取动态消息
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} message_id 消息id  或者  'all'
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} message_id 消息id  或者  'all'
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function readMessageAction( $nUserId = 0 )
    {
        $sMessageId = $this->getParams('message_id');
        try {
            if ( $sMessageId == 'all' ) {
                // 全部已读
                ShortPostsMessage::readAll($nUserId);
            } else {
                $oShortPostsMessage = ShortPostsMessage::findFirst([
                    'user_id = :user_id: AND id = :message_id:',
                    'bind' => [
                        'user_id'    => $nUserId,
                        'message_id' => $sMessageId
                    ]
                ]);
                if ( !$oShortPostsMessage ) {
                    throw new Exception(
                        ResponseError::getError(ResponseError::PARAM_ERROR),
                        ResponseError::PARAM_ERROR
                    );
                }
                $oShortPostsMessage->user_is_read = 'Y';
                $oShortPostsMessage->save();
            }
        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success();
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/shortposts/simpleDetail
     * @api {get} /user/shortposts/simpleDetail 简单详情
     * @apiName shortposts-simpleDetail
     * @apiGroup ShortPosts
     * @apiDescription 简单详情  获取是否关注发帖人 是否收藏该动态
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String}  posts_id 动态id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {string='Y','N'} d.is_follow 是否关注发布人
     * @apiSuccess {string='Y','N'} d.is_collect 是否收藏动态
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function simpleDetailAction( $nUserId = 0 )
    {
        $sPostsId = $this->getParams('posts_id');
        try {
            if ( empty($sPostsId) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                $oShortPostsDelete = ShortPostsDelete::findFirst($sPostsId);
                if ( !$oShortPostsDelete ) {
                    throw new Exception(
                        ResponseError::getError(ResponseError::PARAM_ERROR),
                        ResponseError::PARAM_ERROR
                    );
                } else {
                    throw new Exception(
                        ResponseError::getError(ResponseError::POSTS_DELETE),
                        ResponseError::POSTS_DELETE
                    );
                }
            }
            //  是否关注发帖人
            $isFollow = UserFollow::findFirst([
                'user_id=:user_id: and to_user_id=:to_user_id:',
                'bind' => [
                    'user_id'    => $nUserId,
                    'to_user_id' => $oShortPosts->short_posts_user_id,
                ]
            ]);

            // 是否收藏动态
            $oShortPostsCollect = ShortPostsCollect::findFirst([
                'short_posts_id = :short_posts_id: AND user_id = :user_id:',
                'bind' => [
                    'short_posts_id' => $sPostsId,
                    'user_id'        => $nUserId
                ]
            ]);

            $row = [
                'is_follow'  => $isFollow ? 'Y' : 'N',
                'is_collect' => $oShortPostsCollect ? 'Y' : 'N',
            ];

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.1hjp.com/v1/user/shortposts/buy
     * @api {post} /user/shortposts/buy 购买动态
     * @apiName shortposts-buy
     * @apiGroup ShortPosts
     * @apiDescription 购买动态
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "c": 0,
     *         "d": "",
     *         "m": "请求成功",
     *         "t": 1534911421
     *     }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function buyAction( $nUserId = 0 )
    {
        $sPostsId = $this->getParams('posts_id');
        try {
            if ( empty($sPostsId) ) {
                throw new Exception(
                    ResponseError::getError(ResponseError::PARAM_ERROR),
                    ResponseError::PARAM_ERROR
                );
            }
            $oShortPosts = ShortPosts::findFirst($sPostsId);
            if ( !$oShortPosts ) {
                $oShortPostsDelete = ShortPostsDelete::findFirst($sPostsId);
                if ( !$oShortPostsDelete ) {
                    throw new Exception(
                        ResponseError::getError(ResponseError::PARAM_ERROR),
                        ResponseError::PARAM_ERROR
                    );
                } else {
                    throw new Exception(
                        ResponseError::getError(ResponseError::POSTS_DELETE),
                        ResponseError::POSTS_DELETE
                    );
                }
            }
            if ( $oShortPosts->short_posts_price == 'free' ) {
                $oUser = User::findFirst($nUserId);
                $row   = [
                    'user_coin' => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
                ];
                $this->success($row);
            }
            // 判断是否买过
            $oShortPostsBuy = ShortPostsBuy::findFirst([
                'user_id = :user_id: AND short_posts_id = :short_posts_id:',
                'bind' => [
                    'user_id'        => $nUserId,
                    'short_posts_id' => $sPostsId
                ]
            ]);
            if ( $oShortPostsBuy ) {
                $oUser = User::findFirst($nUserId);
                $row   = [
                    'user_coin' => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
                ];
                $this->success($row);
            }

            $sLogNumber = date('YmdHis') . '000000' . mt_rand(10, 99) . mt_rand(100, 999);
            // 开始购买

            // 获得收益的主播如果没有设置则为当前发布的主播
            $oGetDotUserId = $oShortPosts->short_posts_get_user_id;
            if ( !$oGetDotUserId ) {
                $oGetDotUserId = $oShortPosts->short_posts_user_id;
            }

            $oShortPostsUser = User::findFirst($oGetDotUserId);
            $groupId         = $oShortPostsUser->user_group_id;

            if ( $oShortPostsUser->user_is_anchor == 'Y' ) {
                $oAnchor = Anchor::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $oGetDotUserId ]
                ]);
                $nRatio  = $oAnchor->getCoinToDotRatio($oShortPostsUser, Anchor::RATIO_POSTS);
            } else {
                $oPhotographer = Photographer::findFirst([
                    'user_id=:user_id:',
                    'bind' => [ 'user_id' => $oGetDotUserId ]
                ]);
                $nRatio        = 0;
                if ( $oPhotographer ) {
                    $nRatio = $oPhotographer->getCoinToDotRatio($oShortPostsUser, Photographer::RATIO_POSTS);
                }
                $groupId = 0;
            }
            // Start a transaction
            $oUser = User::findFirst($nUserId);
            $this->db->begin();
            $nCoin = $oShortPosts->short_posts_price;

            $nDot = sprintf('%.4f', $nCoin * ($nRatio / 100));

            //如果是VIP 判断打折
            if ( $oUser->user_member_expire_time > time() && $oShortPosts->short_posts_type == ShortPosts::TYPE_EXHIBITION ) {
                $vipInfo = VipLevel::getVipInfo($oUser->user_vip_level);
                $nCoin   = sprintf('%.2f', $nCoin * $vipInfo->vip_level_exhibition_discount / 10);
            }

            $consumeFreeCoin = 0;
            $consumeCoin     = 0;
            if ( $oUser->user_free_coin <= 0 ) {
                // 直接扣充值币
                $consumeCoin = $nCoin;

            } else if ( $oUser->user_free_coin < $nCoin ) {
                //扣一部分充值币 扣光赠送币
                $consumeFreeCoin = $oUser->user_free_coin;
                $consumeCoin     = $nCoin - $oUser->user_free_coin;
            } else {
                $consumeFreeCoin = $nCoin;
            }

            if ( $nCoin == 0 ) {
                // 打折后免费
                $getDot     = 0;
                $getFreeDot = $nDot;
            } else {
                $getDot     = sprintf('%.4f', $consumeCoin * ($nRatio / 100));
                $getFreeDot = round($nDot - $getDot, 4);
            }


            if ( $nCoin > 0 ) {
                $sql = 'update `user` set user_free_coin = user_free_coin - :consume_free_coin,user_consume_free_total = user_consume_free_total + :consume_free_coin
,user_coin = user_coin - :consume_coin,user_consume_total = user_consume_total + :consume_coin
where user_id = :user_id AND user_free_coin >= :consume_free_coin AND user_coin >= :consume_coin';
                $this->db->execute($sql, [
                    'consume_free_coin' => $consumeFreeCoin,
                    'consume_coin'      => $consumeCoin,
                    'user_id'           => $nUserId,
                ]);
                if ( $this->db->affectedRows() <= 0 ) {
                    // 赠送币 不够钱
                    $this->db->rollback();
                    throw new Exception(ResponseError::getError(ResponseError::USER_COIN_NOT_ENOUGH), ResponseError::USER_COIN_NOT_ENOUGH);
                }
            }


            // 添加帖子购买记录
            $oShortPostsBuy                 = new ShortPostsBuy();
            $oShortPostsBuy->short_posts_id = $sPostsId;
            $oShortPostsBuy->user_id        = $nUserId;
            $oShortPostsBuy->get_dot        = $nDot;
            $oShortPostsBuy->send_coin      = $nCoin;
            $oShortPostsBuy->log_number     = $sLogNumber;
            if ( $oShortPostsBuy->save() === FALSE ) {
                $this->db->rollback();
                throw new Exception(
                    sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oShortPostsBuy->getMessages())),
                    ResponseError::OPERATE_FAILED
                );
            }

            if ( $nCoin > 0 ) {
                // 记录用户流水
                $oUserFinanceLog                   = new UserFinanceLog();
                $oUserFinanceLog->user_amount_type = UserFinanceLog::AMOUNT_COIN;
                $oUserFinanceLog->user_id          = $nUserId;

                $oUserFinanceLog->consume_category_id    = UserConsumeCategory::POSTS_PAY;
                $oUserFinanceLog->consume                = -$nCoin;
                $oUserFinanceLog->remark                 = '动态打赏';
                $oUserFinanceLog->flow_id                = $oShortPostsBuy->id;
                $oUserFinanceLog->flow_number            = $sLogNumber;
                $oUserFinanceLog->type                   = 1;
                $oUserFinanceLog->group_id               = $groupId;
                $oUserFinanceLog->target_user_id         = $oGetDotUserId;
                $oUserFinanceLog->user_current_amount    = $oUser->user_coin + $oUser->user_free_coin - $nCoin;
                $oUserFinanceLog->user_last_amount       = $oUser->user_coin + $oUser->user_free_coin;
                $oUserFinanceLog->user_current_user_coin = $oUser->user_coin - $consumeCoin;
                $oUserFinanceLog->user_last_user_coin    = $oUser->user_coin;
                $oUserFinanceLog->user_current_free_coin = $oUser->user_free_coin - $consumeFreeCoin;
                $oUserFinanceLog->user_last_free_coin    = $oUser->user_free_coin;
                if ( $oUserFinanceLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }

            }

            if ( $nDot > 0 ) {

                // 给主播/摄影师充钱
                $sql = 'update `user` set user_dot = user_dot + :total_dot,user_collect_total = user_collect_total + :get_dot
,user_collect_free_total = user_collect_free_total + :get_free_dot
 where user_id = :user_id';
                $this->db->execute($sql, [
                    'total_dot'    => $nDot,
                    'get_dot'      => $getDot,
                    'get_free_dot' => $getFreeDot,
                    'user_id'      => $oGetDotUserId,
                ]);
                if ( $this->db->affectedRows() <= 0 ) {
                    $this->db->rollback();
                    throw new Exception(
                        ResponseError::getError(ResponseError::OPERATE_FAILED),
                        ResponseError::OPERATE_FAILED
                    );
                }
                $getUser = User::findFirst($oGetDotUserId);
                // 记录主播/摄影师流水
                $oUserFinanceLog                      = new UserFinanceLog();
                $oUserFinanceLog->user_amount_type    = UserFinanceLog::AMOUNT_DOT;
                $oUserFinanceLog->user_id             = $oGetDotUserId;
                $oUserFinanceLog->user_current_amount = $oShortPostsUser->user_dot + $nDot;
                $oUserFinanceLog->user_last_amount    = $oShortPostsUser->user_dot;
                $oUserFinanceLog->consume_category_id = $getUser->user_is_anchor == 'Y' ? UserConsumeCategory::ANCHOR_POSTS_INCOME : UserConsumeCategory::PHOTOGRAPHER_POSTS_INCOME;
                $oUserFinanceLog->consume             = +$nDot;
                $oUserFinanceLog->remark              = '动态收益';
                $oUserFinanceLog->flow_id             = $oShortPostsBuy->id;
                $oUserFinanceLog->flow_number         = $sLogNumber;
                $oUserFinanceLog->type                = 1;
                $oUserFinanceLog->group_id            = $groupId;
                $oUserFinanceLog->consume_source      = -$nCoin;
                $oUserFinanceLog->target_user_id      = $nUserId;
                if ( $oUserFinanceLog->save() === FALSE ) {
                    $this->db->rollback();
                    throw new Exception(
                        sprintf('%s[%s]', ResponseError::getError(ResponseError::OPERATE_FAILED), implode(',', $oUserFinanceLog->getMessages())),
                        ResponseError::OPERATE_FAILED
                    );
                }

                if ( $groupId ) {
                    // 有公会的主播  需要给公会长加钱
                    $oGroup = Group::findFirst($groupId);
                    if ( $oGroup ) {
                        $divid_type    = $oGroup->divid_type;
                        $divid_precent = $oGroup->divid_precent;
                        if ( $divid_type == 0 ) {
                            //主播收益分成
                            $groupMoney = round($nDot * $divid_precent / 100, 2);
                        } else {
                            //主播流水分成  还需要除以一个 充值比例转换值 10
                            $groupMoney = round($nCoin * $divid_precent / 100 / 10, 2);
                        }
                        $sql = 'update `group` set money = money + :money where id = :group_id';
                        $this->db->execute($sql, [
                            'money'    => $groupMoney,
                            'group_id' => $groupId,
                        ]);
                    }
                }

            }

            $oShortPosts->short_posts_buy_num   += 1;
            $oShortPosts->short_posts_dot_count += $nDot;
            $oShortPosts->save();

            $this->db->commit();
            $oUser       = User::findFirst($nUserId);
            $row['user'] = [
                'user_coin' => sprintf('%.2f', $oUser->user_coin + $oUser->user_free_coin),
            ];

            // 添加动态消息
            $oShortPostsMessage                       = new ShortPostsMessage();
            $oShortPostsMessage->short_posts_id       = $sPostsId;
            $oShortPostsMessage->message_type         = ShortPostsMessage::MESSAGE_TYPE_GIFT;
            $oShortPostsMessage->user_id              = $oGetDotUserId;
            $oShortPostsMessage->send_user_id         = $nUserId;
            $oShortPostsMessage->message_content      = '动态收益';
            $oShortPostsMessage->message_target_extra = serialize([
                'extra_content' => $oShortPosts->short_posts_word,
                'extra_time'    => $oShortPosts->short_posts_check_time,
                'dot'           => $nDot,
            ]);
            $oShortPostsMessage->save();


            // 主播每日统计
            $oAnchorStatService = new AnchorStatService($oGetDotUserId);
            $oAnchorStatService->save(AnchorStatService::POSTS_INCOME, $nDot);

            // 用户活动消费榜
            $oActivityUserService = new ActivityUserService();
            $oActivityUserService->save($nUserId, $nCoin);

            // 主播周榜 今日收入
            $oAnchorTodayDotService = new AnchorTodayDotService($oGetDotUserId);
            $oAnchorTodayDotService->save($nDot);

            if ( $nCoin ) {
                // 开始# 亲密值
                $intimateMultiple = Kv::get(Kv::COIN_TO_INTIMATE) ?? 1;
                $intimateValue    = $nCoin * $intimateMultiple;
                if ( $intimateValue > 0 ) {
                    $oUserIntimateLog                              = new UserIntimateLog();
                    $oUserIntimateLog->intimate_log_user_id        = $nUserId;
                    $oUserIntimateLog->intimate_log_anchor_user_id = $oGetDotUserId;
                    $oUserIntimateLog->intimate_log_type           = UserIntimateLog::TYPE_BUY_POSTS;
                    $oUserIntimateLog->intimate_log_value          = $nCoin * $intimateMultiple;
                    $oUserIntimateLog->intimate_log_coin           = $nCoin;
                    $oUserIntimateLog->intimate_log_dot            = $nDot;
                    $oUserIntimateLog->save();
                }
                // 结束# 亲密值
            }


        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


    /**
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.yuyin-tv.com/v1/user/shortposts/buyList
     * @api {get} /user/shortposts/buyList 动态购买列表
     * @apiName shortposts-buylist
     * @apiGroup ShortPosts
     * @apiDescription 动态购买列表
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} sign  签名
     * @apiParam (正常请求){String} timestamp  时间戳
     * @apiParam (正常请求){String} posts_id 动态id
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug){String} posts_id 动态id
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.items
     * @apiSuccess {number} d.items.user_id  用户id
     * @apiSuccess {String} d.items.user_nickname  用户昵称
     * @apiSuccess {String} d.items.user_avatar  用户头像
     * @apiSuccess {number} d.page
     * @apiSuccess {number} d.pagesize
     * @apiSuccess {number} d.pagetotal
     * @apiSuccess {number} d.total
     * @apiSuccess {number} d.prev
     * @apiSuccess {number} d.next
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *   {
     *       "c": 0,
     *       "m": "请求成功",
     *       "d": {
     *               "items": [{
     *                   "user_id": "41",
     *                   "user_nickname": "1181732245amxij",
     *                   "user_avatar": "http:\/\/tvax4.sinaimg.cn\/crop.0.0.40.40.180\/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg"
     *           }],
     *           "page": 1,
     *           "pagesize": 20,
     *           "pagetotal": 1,
     *           "total": 1,
     *           "prev": 1,
     *           "next": 1
     *       },
     *       "t": 1554779495
     *   }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function buyListAction( $nUserId = 0 )
    {

        $sPostsId  = $this->getParams('posts_id');
        $nPage     = $this->getParams('page', 'int', 0);
        $nPagesize = $this->getParams('pagesize', 'int', 20);
        try {
            $columns = 'u.user_id,u.user_nickname,u.user_avatar';
            $builder = $this->modelsManager->createBuilder()
                ->from([ 'b' => ShortPostsBuy::class ])
                ->columns($columns)
                ->join(User::class, 'u.user_id = b.user_id', 'u')
                ->where('b.short_posts_id = :short_posts_id:', [ 'short_posts_id' => $sPostsId ])
                ->orderBy('b.create_time desc');
            $row     = $this->page($builder, $nPage, $nPagesize);

        } catch ( Exception $e ) {
            $this->error($e->getCode(), $e->getMessage());
        }
        $this->success($row);
    }


}