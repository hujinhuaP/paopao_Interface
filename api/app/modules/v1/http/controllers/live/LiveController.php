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

use app\models\AppList;
use app\models\Carousel;
use app\http\controllers\ControllerBase;
use app\models\User;

/**
* LiveController 直播
*/
class LiveController extends ControllerBase
{
    use \app\services\UserService;
    /**
     * 根据经纬度和半径计算出范围
     * 
     * @param string $lat 纬度
     * @param String $lng 经度
     * @param float  $radius 半径
     * @return Array 范围数组
     */
    private function calcScope($lat, $lng, $radius)
    {
        $degree = (24901*1609)/360.0;
        $dpmLat = 1/$degree;

        $radiusLat = $dpmLat*$radius;
        $minLat = $lat - $radiusLat;       // 最小纬度
        $maxLat = $lat + $radiusLat;       // 最大纬度

        $mpdLng = $degree*cos($lat * (pi()/180));
        $dpmLng = 1 / $mpdLng;
        $radiusLng = $dpmLng*$radius;
        $minLng = $lng - $radiusLng;      // 最小经度
        $maxLng = $lng + $radiusLng;      // 最大经度

        $scope = [
            'minLat' =>  $minLat,
            'maxLat' =>  $maxLat,
            'minLng' =>  $minLng,
            'maxLng' =>  $maxLng,
        ];
        return $scope;
    }

    /**
     * bannerAction 轮播图
     *
     * @apiVersion 1.3.0
     * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/live/banner
     * @api {get} /live/live/banner 轮播图
     * @apiName banner
     * @apiGroup Live
     * @apiDescription 轮播图
     * @apiParam (正常请求){String} access_token  token值
     * @apiParam (正常请求){String} type  类型  4 首页（快聊顶部） 6 匹配大厅
     * @apiParam (debug) {String} debug  debug
     * @apiParam (debug) {String} cli_api_key  debug
     * @apiParam (debug) {String} uid  用户id
     * @apiParam (debug) {String} type  类型  4 首页（快聊顶部） 6 匹配大厅
     * @apiSuccess {number} c 返回码
     * @apiSuccess {string} m 返回文字信息
     * @apiSuccess {number} t 服务器当前时间戳
     * @apiSuccess {Object} d 内容
     * @apiSuccess {object[]} d.carousel
     * @apiSuccess {number} d.carousel.carousel_id
     * @apiSuccess {String} d.carousel.carousel_url  图片地址
     * @apiSuccess {String} d.carousel.carousel_href  跳转地址
     * @apiSuccess {String="externally(外链),inviteFriend(邀请好友),recharge(充值),todaySignIn(签到),download(直接下载)"} d.carousel.carousel_target_type 跳转类型
     * @apiSuccessExample Success-Response:
     *     HTTP/1.1 200 OK
     *         {
     *             "c": 0,
     *             "m": "请求成功",
     *             "d": {
     *                     "carousel": [
     *                     {
     *                         "carousel_id": "12",
     *                         "carousel_url": "https://lebolive-1255651273.image.myqcloud.com/image/20180612/1528795861401611.jpg",
     *                         "carousel_href": "https://b.eqxiu.com/s/8LZ3LPT8?from=sqq",
     *                         "carousel_target_type": "externally"
     *                     }
     *                 ]
     *             },
     *             "t": 1537242758
     *         }
     * @apiError UserNotFound The id of the User was not found.
     * @apiErrorExample Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     */
    public function bannerAction($nUserId=0)
    {

        $type = $this->getParams('type','int',1);
        $appName = $this->getParams('app_name');
        if(!in_array($type,[1,2,3,4,5,6])){
            $this->error(10045);
        }
        $extra_where = " AND carousel_and_show = 'Y'";
        if($this->getParams('app_os') != 'Android'){
            $extra_where = " AND carousel_ios_show = 'Y'";
        }
        $appId = 1;
        if($appName){
            $oAppList = AppList::findFirst([
                'app_flg=:app_flg:',
                'bind' => [ 'app_flg' => $appName ]
            ]);
            if($oAppList){
                $appId = $oAppList->id;
            }
        }
        $oUser = User::findFirst($nUserId);
        $adAgentArr = $this->getADAgent();
        if(!$oUser->user_invite_agent_id || !in_array($oUser->user_invite_agent_id,$adAgentArr)){
            // 该用户不可以看广告
            $extra_where .= ' AND is_ad = "N"';
        }
        $extra_where .= " AND (carousel_app_id = 0 or carousel_app_id = $appId)";
        // 轮播图
        $row['carousel'] = Carousel::find([
            "carousel_category_id=:carousel_category_id: $extra_where and carousel_status='Y' order by carousel_sort asc",
            'bind' => [
                'carousel_category_id' => $type
            ],
            'columns' => 'carousel_id,carousel_url,carousel_href,carousel_target_type',
        ])->toArray();

        foreach($row['carousel'] as &$item){
            if($item['carousel_target_type'] == 'recharge' && $this->getParams('app_os') != 'Android'){
                $item['carousel_href'] = 'http://charge.sxypaopao.com/pay.php?uid='.$nUserId;
                $item['carousel_target_type'] = 'externally';
            }
            if($item['carousel_href'] == 'http://test.sxypaopao.com'){
                $item['carousel_href'] = 'http://test.sxypaopao.com/index/index/index/user/'.$nUserId;
                $item['carousel_target_type'] = 'externally';
            }
            if($item['carousel_href'] == 'http://activity.sxypaopao.com'){
                $item['carousel_href'] = 'http://activity.sxypaopao.com/index/index/index/user/'.$nUserId;
                $item['carousel_target_type'] = 'externally';
            }
            if(strpos($item['carousel_href'],'activity/coinanddotstat') !== false){
                $item['carousel_href'] .= '/user/'.$nUserId;
                $item['carousel_target_type'] = 'externally';
            }
        }

        $this->success($row);
    }
}
