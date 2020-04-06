<?php
/**
 * 接口文档以前的版本备注 放入添加进此文件中
 */

/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getPrivateChat
 * @api {get} /live/anchor/getPrivateChat 首页主播
 * @apiName getPrivateChat
 * @apiGroup Index
 * @apiDescription 首页主播
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.items  内容
 * @apiSuccess {String} d.items.user_nickname  昵称
 * @apiSuccess {String} d.items.user_avatar  头像
 * @apiSuccess {String} d.items.user_intro  简介
 * @apiSuccess {String} d.items.user_profession  职业
 * @apiSuccess {number} d.items.anchor_chat_status  聊天状态
 * @apiSuccess {number} d.items.user_id  用户id
 * @apiSuccess {number} d.items.user_sex 性别
 * @apiSuccess {String} d.items.user_video_cover  视频封面
 * @apiSuccess {number} d.page
 * @apiSuccess {number} d.pagesize
 * @apiSuccess {number} d.pagetotal
 * @apiSuccess {number} d.total
 * @apiSuccess {number} d.prev
 * @apiSuccess {number} d.next
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *         "m": "请求成功",
 *         "d": {
 *             "items": [{
 *                 "user_nickname": "118啦咯",
 *                 "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/09\/1533807443523.png",
 *                 "user_intro": "徒孙气质红哦你好给力了",
 *                 "user_profession": "工程师",
 *                 "anchor_chat_status": "3",
 *                 "user_id": "166",
 *                 "user_sex": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533694327924.png"
 *         }, {
 *                 "user_nickname": "泡泡08271736169",
 *                 "user_avatar": "http:\/\/lebolive-1255651273.file.myqcloud.com\/avatar.jpg",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "1",
 *                 "user_id": "230",
 *                 "user_sex": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/27\/1970dd6937d27862b745642fabbc6af5"
 *         }, {
 *                 "user_nickname": "神秘",
 *                 "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg\/132",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "0",
 *                 "user_id": "170",
 *                 "user_sex": "1",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/06\/1533540159917.png"
 *         }],
 *         "page": 1,
 *         "pagesize": 50,
 *         "pagetotal": 1,
 *         "total": 3,
 *         "prev": 1,
 *         "next": 1
 *         },
 *         "t": 1535600607
 *     }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */


/**
 * 挂断一对一私聊
 * 主播收钱
 *
 * 1. 视频接通10s内挂断，扣取用户金币，但不予主播结算
 * 2. 视频通话如果主播主动关闭，该视频通话最后一分钟，不予结算，但同样扣取用户金币。
 * 若用户主动关闭，不受该逻辑影响
 *
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/hangUpChat
 * @api {get} /live/anchor/hangUpChat 挂断聊天
 * @apiName hangUpChat
 * @apiGroup Chat
 * @apiDescription 挂断聊天
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} chat_log 聊天id
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} chat_log 聊天id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {number} d.amount  时间收益
 * @apiSuccess {number} d.coin_amount  时间消费
 * @apiSuccess {number} d.duration  时长
 * @apiSuccess {String} d.is_follow  是否关注
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "c": 0,
 *          "m": "请求成功",
 *          "d": {
 *              "amount": "0.8000",
 *              "coin_amount": 20,
 *              "duration": 50,
 *              "is_follow": "Y"
 *          },
 *          "t": 1536662604
 *      }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 * */


/**
 * 挂断一对一私聊
 * 主播收钱
 *
 * 1. 视频接通10s内挂断，扣取用户金币，但不予主播结算
 * 2. 视频通话如果主播主动关闭，该视频通话最后一分钟，不予结算，但同样扣取用户金币。
 * 若用户主动关闭，不受该逻辑影响
 *
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/hangUpChat
 * @api {get} /live/anchor/hangUpChat 挂断聊天
 * @apiName hangUpChat
 * @apiGroup Chat
 * @apiDescription 挂断聊天
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} chat_log 聊天id
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} chat_log 聊天id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object} d.gift  礼物收益
 * @apiSuccess {number} d.gift.coin  礼物消费金币
 * @apiSuccess {number} d.gift.dot  礼物收益
 * @apiSuccess {number} d.amount  时间收益
 * @apiSuccess {number} d.coin_amount  时间消费
 * @apiSuccess {number} d.duration  时长
 * @apiSuccess {String} d.is_follow  是否关注
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "c": 0,
 *          "m": "请求成功",
 *          "d": {
 *                  "gift": {
 *                      "coin": "30",
 *                      "dot": "1.2"
 *              },
 *              "amount": "0.8000",
 *              "coin_amount": 20,
 *              "duration": 50,
 *              "is_follow": "Y"
 *          },
 *          "t": 1536662604
 *      }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 * */


/**
 * indexAction 获取用户信息
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/index
 * @api {get} /user/profile/index 获取用户信息
 * @apiName 获取用户信息-profile-index
 * @apiGroup Profile
 * @apiDescription 获取用户信息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {string} d.user_id 用户id
 * @apiSuccess {string} d.user_nickname 用户昵称
 * @apiSuccess {string} d.user_avatar 头像
 * @apiSuccess {string} d.user_coin 金币
 * @apiSuccess {string} d.user_dot 佣金
 * @apiSuccess {string} d.user_consume_total 用户总消费
 * @apiSuccess {string} d.user_collect_total 用户总收礼
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_birth 生日
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_lat 纬度
 * @apiSuccess {string} d.user_lng 经度
 * @apiSuccess {string} d.user_invite_code 邀请码
 * @apiSuccess {string} d.user_invite_total 邀请总数
 * @apiSuccess {string} d.user_follow_total 关注数
 * @apiSuccess {string} d.user_fans_total 粉丝数
 * @apiSuccess {string} d.user_is_certification 是否认证
 * @apiSuccess {string} d.user_is_anchor 是否主播
 * @apiSuccess {string} d.user_phone 手机号码
 * @apiSuccess {string} d.user_token_expire_time token过期时间戳
 * @apiSuccess {string} d.user_member_expire_time 会员过期时间戳
 * @apiSuccess {string} d.user_is_member 是否为会员
 * @apiSuccess {string} d.access_token access_token
 * @apiSuccess {string} d.anchor_ranking 主播排名
 * @apiSuccess {string} d.ws_url 弃用
 * @apiSuccess {string} d.user_constellation 用户星座
 * @apiSuccess {string} d.user_img 用户图集
 * @apiSuccess {string} d.user_video 用户视频
 * @apiSuccess {string} d.user_video_cover 用户视频封面
 * @apiSuccess {string} d.user_home_town 用户家乡
 * @apiSuccess {string} d.user_hobby 爱好
 * @apiSuccess {string} d.user_profession 用户职业
 * @apiSuccess {string} d.user_emotional_state 用户情感状况
 * @apiSuccess {string} d.user_income 用户收入
 * @apiSuccess {string} d.user_height 用户身高
 * @apiSuccess {string} d.user_register_time 注册时间
 * @apiSuccess {object} d.tim 腾讯云TIM
 * @apiSuccess {string} d.tim.sign 腾讯云签名
 * @apiSuccess {string} d.tim.account 腾讯云账号
 * @apiSuccess {string} d.tim.account_type 腾讯云账号类型
 * @apiSuccess {string} d.tim.app_id 腾讯云app_id
 * @apiSuccess {object} d.match_center_info 匹配信息
 * @apiSuccess {string} d.match_center_info.room_id 匹配大厅房间id
 * @apiSuccess {number} d.match_center_info.match_price 匹配价格
 * @apiSuccess {string} d.h5_pay_url H5充值地址
 * @apiSuccess {string} d.h5_vip_url H5购买VIP地址
 * @apiSuccess {string} d.customer_service_id 客服id
 * @apiSuccess {number} d.guide_video_time 诱导视频时长
 * @apiSuccess {object} d.anchor_info 主播信息
 * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
 * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
 * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
 * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
 * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
 * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
 * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
 * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
 * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
 * @apiSuccess {object} d.user_signin  签到信息
 * @apiSuccess {String} d.user_signin.is_signin  是否签到 Y 是 N 否
 * @apiSuccess {String} d.user_signin.tips  签到提示
 * @apiSuccess {object} d.unread 消息未读
 * @apiSuccess {number} d.unread.total  总未读数
 * @apiSuccess {String} d.unread.user_chat  聊天未读数
 * @apiSuccess {String} d.unread.system_message 系统消息未读数
 * @apiSuccess {number} d.unread.video_message 小视频消息未读数
 * @apiSuccess {number} d.unread.video_chat_message 视频聊天未读数
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *   {
 *       "c": 0,
 *       "m": "请求成功",
 *       "d": {
 *           "user_id": "172",
 *           "user_nickname": "18823369189",
 *           "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
 *           "user_sex": "1",
 *           "user_coin": "10.00",
 *           "user_dot": "100.00",
 *           "user_consume_total": "0.00",
 *           "user_collect_total": "0.00",
 *           "user_intro": "",
 *           "user_birth": "",
 *           "user_lat": "0.000000",
 *           "user_lng": "0.000000",
 *           "user_invite_code": "W21309",
 *           "user_invite_total": "0",
 *           "user_follow_total": "0",
 *           "user_fans_total": "0",
 *           "user_is_certification": "Y",
 *           "user_is_anchor": "Y",
 *           "user_phone": "188****9189",
 *           "user_token_expire_time": "1536483157",
 *           "user_member_expire_time": "1533867150",
 *           "user_is_member": "O",
 *           "access_token": "xYNJYC957HNtAWMSN3CIhrroVKfsspC4RQBM6e7XFQAzOTi.ysbTTL9_HwvlVJuvsEbGKC7IFCCTncwNjN3Q",
 *           "anchor_ranking": "0",
 *           "ws_url": "?uid=172&token=&extra=eyJ1c2VyIjp7InVzZXJfaWQiOiIxNzIiLCJ1c2VyX2F2YXRhciI6Imh0dHA6XC9cL2xlYm9saXZlLTEyNTU2NTEyNzMuZmlsZS5teXFjbG91ZC5jb21cL2F2YXRhci5qcGciLCJ1c2VyX25pY2tuYW1lIjoiMTg4MjMzNjkxODkiLCJ1c2VyX3NleCI6IjEiLCJ1c2VyX2lzX21lbWJlciI6Ik8ifX0%3D",
 *           "user_constellation": "",
 *           "user_img": null,
 *           "user_video": "",
 *           "user_video_cover": "",
 *           "user_home_town": "",
 *           "user_hobby": "",
 *           "user_profession": "",
 *           "user_emotional_state": "",
 *           "user_income": "",
 *           "user_height": "",
 *           "apple_online": 1,
 *           "user_register_time": "1533623873",
 *           "tim": {
 *                   "sign": "eJxNjV9PgzAUR78Lz8b1zwrDZA84NzSOBSNBY5Y0SAu7TqCWwhjG7y7iFn095-zu-bSi9eNlkqZVUxpujkpaVxayLkYMQpYGMpB6gNghJ5woBYInhlMt-tW12PNR-cRThBCz8ey8kZ0CLXmSmd9jjDEyJCfbSl1DVQ6CIMwwoQj9SQOFHCd06hJMMT3-g3zAwfJhcbfCgR32u*fsJfLKTQdt*BZEjn2T*03cm1v9Gl-7e*fozNzUAy8q4LB69yWpl6HshPtx2E682Nw-Fe12sl4o3O9k1jeV6fAmn8*tr29ib1g8",
 *                   "account": "172",
 *                   "account_type": "20760",
 *                   "app_id": "1400056182"
 *           },
 *           "match_center_info": {
 *                   "room_id": "matchCenterRoomDev",
 *                   "match_price": 10
 *           },
 *            "h5_pay_url": "http://dev.charge.sxypaopao.com/pay.php?uid=172",
 *            "h5_vip_url": "http://dev.charge.sxypaopao.com/vip.php?uid=172",
 *            "customer_service_id": "181",
 *            "guide_video_time": 15,
 *            "anchor_info": {
 *                   "anchor_tip": "",
 *                   "anchor_character": "",
 *                   "anchor_good_topic": "",
 *                   "anchor_dress": "",
 *                   "anchor_stature": "",
 *                   "anchor_images": "",
 *                   "anchor_check_img": ""
 *           },
 *              "user_signin": {
 *                  "is_signin": "Y",
 *              	"tips": "今天还没有签到哦！"
 *              },
 *              "unread": {
 *                  "total": "0",
 *              	"user_chat": "",
 *              	"system_message": "",
 *              	"video_message": "0",
 *              	"video_chat_message": "0"
 *              }
 *       },
 *       "t": 1534921313
 *   }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getPrivateChat
 * @api {get} /live/anchor/getPrivateChat 首页主播
 * @apiName getPrivateChat
 * @apiGroup Index
 * @apiDescription 首页主播
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求) {String="index(首页)","all(所有)","hot(热门)"} type  类型
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug) {String="index(首页)","all(所有)","hot(热门)"} type  类型
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.items  内容
 * @apiSuccess {String} d.items.user_nickname  昵称
 * @apiSuccess {String} d.items.user_avatar  头像
 * @apiSuccess {String} d.items.user_intro  简介
 * @apiSuccess {String} d.items.user_profession  职业
 * @apiSuccess {number} d.items.anchor_chat_status  聊天状态
 * @apiSuccess {number} d.items.user_id  用户id
 * @apiSuccess {number} d.items.user_sex 性别
 * @apiSuccess {number} d.items.user_birth 生日
 * @apiSuccess {String} d.items.user_video_cover  视频封面
 * @apiSuccess {String} d.items.user_video   视频地址
 * @apiSuccess {number} d.items.is_follow  是否关注  0为 未关注 其他为已关注
 * @apiSuccess {String} d.items.anchor_tip
 * @apiSuccess {String} d.items.anchor_character
 * @apiSuccess {String} d.items.anchor_good_topic
 * @apiSuccess {String} d.items.anchor_dress
 * @apiSuccess {String} d.items.anchor_stature
 * @apiSuccess {String} d.items.anchor_emotional_state
 * @apiSuccess {number} d.items.price
 * @apiSuccess {String} d.items.anchor_local
 * @apiSuccess {String} d.items.anchor_title
 * @apiSuccess {String} d.items.share_url
 * @apiSuccess {number} d.page
 * @apiSuccess {number} d.pagesize
 * @apiSuccess {number} d.pagetotal
 * @apiSuccess {number} d.total
 * @apiSuccess {number} d.prev
 * @apiSuccess {number} d.next
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *         "m": "请求成功",
 *         "d": {
 *             "items": [{
 *                 "user_nickname": "118啦咯",
 *                 "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/09\/1533807443523.png",
 *                 "user_intro": "徒孙气质红哦你好给力了",
 *                 "user_profession": "工程师",
 *                 "anchor_chat_status": "3",
 *                 "user_id": "166",
 *                 "user_sex": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533694327924.png"
 *         }, {
 *                 "user_nickname": "泡泡08271736169",
 *                 "user_avatar": "http:\/\/lebolive-1255651273.file.myqcloud.com\/avatar.jpg",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "1",
 *                 "user_id": "230",
 *                 "user_sex": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/27\/1970dd6937d27862b745642fabbc6af5"
 *         }, {
 *                 "user_nickname": "神秘",
 *                 "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg\/132",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "0",
 *                 "user_id": "170",
 *                 "user_sex": "1",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/06\/1533540159917.png"
 *         }],
 *         "page": 1,
 *         "pagesize": 50,
 *         "pagetotal": 1,
 *         "total": 3,
 *         "prev": 1,
 *         "next": 1
 *         },
 *         "t": 1535600607
 *     }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

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
 * @apiSuccess {String} d.carousel.carousel_target_type 跳转类型  状态，externally 外链 inviteFriend";//邀请好友 recharge";//充值 todaySignIn";//签到
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

/**
 * sendAction 发送聊天
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/send
 * @api {post} /user/chat/send 发送消息
 * @apiName 发送聊天
 * @apiGroup Chat
 * @apiDescription 发送消息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} chat_room_id 聊天id
 * @apiParam (正常请求){String} to_user_id 接收方用户id
 * @apiParam (正常请求){String} content 内容  根据类型传值
 * @apiParam (正常请求){String} pay 是否确认付费  确认为1
 * @apiParam (正常请求){String='sayHi(打招呼)','normal(普通消息)'} type 聊天类型
 * @apiParam (正常请求){String='word(文字)','image(图片)','video(视频)','voice(语音)'} msg_type 消息类型
 * @apiParam (正常请求){String} extra 额外信息（接收方 收到时 同样返回）
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} chat_room_id 聊天id
 * @apiParam (debug){String} to_user_id 接收方用户id
 * @apiParam (debug){String} content 内容  根据类型传值
 * @apiParam (debug){String} pay 是否确认付费  确认为1
 * @apiParam (debug){String='sayHi(打招呼)','normal(普通消息)'} type 聊天类型
 * @apiParam (debug){String='word(文字)','image(图片)','video(视频)','voice(语音)'} msg_type 消息类型
 * @apiParam (debug){String} extra 额外信息（接收方 收到时 同样返回）
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

/**
 * indexAction 获取用户信息
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/index
 * @api {get} /user/profile/index 获取用户信息
 * @apiName 获取用户信息-profile-index
 * @apiGroup Profile
 * @apiDescription 获取用户信息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {string} d.user_id 用户id
 * @apiSuccess {string} d.user_nickname 用户昵称
 * @apiSuccess {string} d.user_avatar 头像
 * @apiSuccess {string} d.user_coin 金币
 * @apiSuccess {string} d.user_dot 佣金
 * @apiSuccess {string} d.user_consume_total 用户总消费
 * @apiSuccess {string} d.user_collect_total 用户总收礼
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_birth 生日
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_lat 纬度
 * @apiSuccess {string} d.user_lng 经度
 * @apiSuccess {string} d.user_invite_code 邀请码
 * @apiSuccess {string} d.user_invite_total 邀请总数
 * @apiSuccess {string} d.user_follow_total 关注数
 * @apiSuccess {string} d.user_fans_total 粉丝数
 * @apiSuccess {string} d.user_is_certification 是否认证
 * @apiSuccess {string} d.user_is_anchor 是否主播
 * @apiSuccess {string} d.user_phone 手机号码
 * @apiSuccess {string} d.user_token_expire_time token过期时间戳
 * @apiSuccess {string} d.user_member_expire_time 会员过期时间戳
 * @apiSuccess {string} d.user_is_member 是否为会员
 * @apiSuccess {string} d.access_token access_token
 * @apiSuccess {string} d.anchor_ranking 主播排名
 * @apiSuccess {string} d.ws_url 弃用
 * @apiSuccess {string} d.user_constellation 用户星座
 * @apiSuccess {string} d.user_img 用户图集
 * @apiSuccess {string} d.user_video 用户视频
 * @apiSuccess {string} d.user_video_cover 用户视频封面
 * @apiSuccess {string} d.user_home_town 用户家乡
 * @apiSuccess {string} d.user_hobby 爱好
 * @apiSuccess {string} d.user_profession 用户职业
 * @apiSuccess {string} d.user_emotional_state 用户情感状况
 * @apiSuccess {string} d.user_income 用户收入
 * @apiSuccess {string} d.user_height 用户身高
 * @apiSuccess {string} d.user_register_time 注册时间
 * @apiSuccess {object} d.tim 腾讯云TIM
 * @apiSuccess {string} d.tim.sign 腾讯云签名
 * @apiSuccess {string} d.tim.account 腾讯云账号
 * @apiSuccess {string} d.tim.account_type 腾讯云账号类型
 * @apiSuccess {string} d.tim.app_id 腾讯云app_id
 * @apiSuccess {object} d.match_center_info 匹配信息
 * @apiSuccess {string} d.match_center_info.room_id 匹配大厅房间id
 * @apiSuccess {number} d.match_center_info.match_price 匹配价格
 * @apiSuccess {string} d.h5_pay_url H5充值地址
 * @apiSuccess {string} d.h5_vip_url H5购买VIP地址
 * @apiSuccess {string} d.customer_service_id 客服id
 * @apiSuccess {number} d.guide_video_time 诱导视频时长
 * @apiSuccess {object} d.anchor_info 主播信息
 * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
 * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
 * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
 * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
 * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
 * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
 * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
 * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
 * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
 * @apiSuccess {object} d.user_signin  签到信息
 * @apiSuccess {String} d.user_signin.is_signin  是否签到 Y 是 N 否
 * @apiSuccess {String} d.user_signin.tips  签到提示
 * @apiSuccess {object} d.unread 消息未读
 * @apiSuccess {number} d.unread.total  总未读数
 * @apiSuccess {String} d.unread.user_chat  聊天未读数
 * @apiSuccess {String} d.unread.system_message 系统消息未读数
 * @apiSuccess {number} d.unread.video_message 小视频消息未读数
 * @apiSuccess {number} d.unread.video_chat_message 视频聊天未读数
 * @apiSuccess {number} d.free_times 免费匹配时长（分钟）
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *   {
 *       "c": 0,
 *       "m": "请求成功",
 *       "d": {
 *           "user_id": "172",
 *           "user_nickname": "18823369189",
 *           "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
 *           "user_sex": "1",
 *           "user_coin": "10.00",
 *           "user_dot": "100.00",
 *           "user_consume_total": "0.00",
 *           "user_collect_total": "0.00",
 *           "user_intro": "",
 *           "user_birth": "",
 *           "user_lat": "0.000000",
 *           "user_lng": "0.000000",
 *           "user_invite_code": "W21309",
 *           "user_invite_total": "0",
 *           "user_follow_total": "0",
 *           "user_fans_total": "0",
 *           "user_is_certification": "Y",
 *           "user_is_anchor": "Y",
 *           "user_phone": "188****9189",
 *           "user_token_expire_time": "1536483157",
 *           "user_member_expire_time": "1533867150",
 *           "user_is_member": "O",
 *           "access_token": "xYNJYC957HNtAWMSN3CIhrroVKfsspC4RQBM6e7XFQAzOTi.ysbTTL9_HwvlVJuvsEbGKC7IFCCTncwNjN3Q",
 *           "anchor_ranking": "0",
 *           "ws_url": "?uid=172&token=&extra=eyJ1c2VyIjp7InVzZXJfaWQiOiIxNzIiLCJ1c2VyX2F2YXRhciI6Imh0dHA6XC9cL2xlYm9saXZlLTEyNTU2NTEyNzMuZmlsZS5teXFjbG91ZC5jb21cL2F2YXRhci5qcGciLCJ1c2VyX25pY2tuYW1lIjoiMTg4MjMzNjkxODkiLCJ1c2VyX3NleCI6IjEiLCJ1c2VyX2lzX21lbWJlciI6Ik8ifX0%3D",
 *           "user_constellation": "",
 *           "user_img": null,
 *           "user_video": "",
 *           "user_video_cover": "",
 *           "user_home_town": "",
 *           "user_hobby": "",
 *           "user_profession": "",
 *           "user_emotional_state": "",
 *           "user_income": "",
 *           "user_height": "",
 *           "apple_online": 1,
 *           "user_register_time": "1533623873",
 *           "tim": {
 *                   "sign": "eJxNjV9PgzAUR78Lz8b1zwrDZA84NzSOBSNBY5Y0SAu7TqCWwhjG7y7iFn095-zu-bSi9eNlkqZVUxpujkpaVxayLkYMQpYGMpB6gNghJ5woBYInhlMt-tW12PNR-cRThBCz8ey8kZ0CLXmSmd9jjDEyJCfbSl1DVQ6CIMwwoQj9SQOFHCd06hJMMT3-g3zAwfJhcbfCgR32u*fsJfLKTQdt*BZEjn2T*03cm1v9Gl-7e*fozNzUAy8q4LB69yWpl6HshPtx2E682Nw-Fe12sl4o3O9k1jeV6fAmn8*tr29ib1g8",
 *                   "account": "172",
 *                   "account_type": "20760",
 *                   "app_id": "1400056182"
 *           },
 *           "match_center_info": {
 *                   "room_id": "matchCenterRoomDev",
 *                   "match_price": 10
 *           },
 *            "h5_pay_url": "http://dev.charge.sxypaopao.com/pay.php?uid=172",
 *            "h5_vip_url": "http://dev.charge.sxypaopao.com/vip.php?uid=172",
 *            "customer_service_id": "181",
 *            "guide_video_time": 15,
 *            "anchor_info": {
 *                   "anchor_tip": "",
 *                   "anchor_character": "",
 *                   "anchor_good_topic": "",
 *                   "anchor_dress": "",
 *                   "anchor_stature": "",
 *                   "anchor_images": "",
 *                   "anchor_check_img": ""
 *           },
 *              "user_signin": {
 *                  "is_signin": "Y",
 *                "tips": "今天还没有签到哦！"
 *              },
 *              "unread": {
 *                  "total": "0",
 *                "user_chat": "",
 *                "system_message": "",
 *                "video_message": "0",
 *                "video_chat_message": "0"
 *              },
 *            "free_times": "1"
 *       },
 *       "t": 1534921313
 *   }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

/**
 * @apiVersion 1.8.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getPrivateChat
 * @api {get} /live/anchor/getPrivateChat 首页主播
 * @apiName getPrivateChat
 * @apiGroup Index
 * @apiDescription 首页主播
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求) {String="index(首页)","all(所有)","hot(热门)","follow(关注)"} type  类型
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug) {String="index(首页)","all(所有)","hot(热门)","follow(关注)"} type  类型
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.items  内容
 * @apiSuccess {String} d.items.user_nickname  昵称
 * @apiSuccess {String} d.items.user_avatar  头像
 * @apiSuccess {String} d.items.user_intro  简介
 * @apiSuccess {String} d.items.user_profession  职业
 * @apiSuccess {number} d.items.anchor_chat_status  聊天状态
 * @apiSuccess {number} d.items.user_id  用户id
 * @apiSuccess {number} d.items.user_sex 性别
 * @apiSuccess {number} d.items.user_birth 生日
 * @apiSuccess {String} d.items.user_video_cover  视频封面
 * @apiSuccess {String} d.items.user_video   视频地址
 * @apiSuccess {number} d.items.is_follow  是否关注  0为 未关注 其他为已关注
 * @apiSuccess {String} d.items.anchor_tip
 * @apiSuccess {String} d.items.anchor_character
 * @apiSuccess {String} d.items.anchor_good_topic
 * @apiSuccess {String} d.items.anchor_dress
 * @apiSuccess {String} d.items.anchor_stature
 * @apiSuccess {String} d.items.anchor_emotional_state
 * @apiSuccess {number} d.items.price
 * @apiSuccess {String} d.items.anchor_local
 * @apiSuccess {String} d.items.anchor_title
 * @apiSuccess {String} d.items.share_url
 * @apiSuccess {number} d.page
 * @apiSuccess {number} d.pagesize
 * @apiSuccess {number} d.pagetotal
 * @apiSuccess {number} d.total
 * @apiSuccess {number} d.prev
 * @apiSuccess {number} d.next
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *         "m": "请求成功",
 *         "d": {
 *             "items": [{
 *                 "user_nickname": "118啦咯",
 *                 "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/09\/1533807443523.png",
 *                 "user_intro": "徒孙气质红哦你好给力了",
 *                 "user_profession": "工程师",
 *                 "anchor_chat_status": "3",
 *                 "user_id": "166",
 *                 "user_sex": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533694327924.png"
 *         }, {
 *                 "user_nickname": "泡泡08271736169",
 *                 "user_avatar": "http:\/\/lebolive-1255651273.file.myqcloud.com\/avatar.jpg",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "1",
 *                 "user_id": "230",
 *                 "user_sex": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/27\/1970dd6937d27862b745642fabbc6af5"
 *         }, {
 *                 "user_nickname": "神秘",
 *                 "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg\/132",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "0",
 *                 "user_id": "170",
 *                 "user_sex": "1",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/06\/1533540159917.png"
 *         }],
 *         "page": 1,
 *         "pagesize": 50,
 *         "pagetotal": 1,
 *         "total": 3,
 *         "prev": 1,
 *         "next": 1
 *         },
 *         "t": 1535600607
 *     }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

/**
 * indexAction 获取用户信息
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/index
 * @api {get} /user/profile/index 获取用户信息
 * @apiName 获取用户信息-profile-index
 * @apiGroup Profile
 * @apiDescription 获取用户信息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {string} d.user_id 用户id
 * @apiSuccess {string} d.user_nickname 用户昵称
 * @apiSuccess {string} d.user_avatar 头像
 * @apiSuccess {string} d.user_coin 金币
 * @apiSuccess {string} d.user_dot 佣金
 * @apiSuccess {string} d.user_consume_total 用户总消费
 * @apiSuccess {string} d.user_collect_total 用户总收礼
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_birth 生日
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_lat 纬度
 * @apiSuccess {string} d.user_lng 经度
 * @apiSuccess {string} d.user_invite_code 邀请码
 * @apiSuccess {string} d.user_invite_total 邀请总数
 * @apiSuccess {string} d.user_follow_total 关注数
 * @apiSuccess {string} d.user_fans_total 粉丝数
 * @apiSuccess {string} d.user_is_certification 是否认证
 * @apiSuccess {string} d.user_is_anchor 是否主播
 * @apiSuccess {string} d.user_phone 手机号码
 * @apiSuccess {string} d.user_token_expire_time token过期时间戳
 * @apiSuccess {string} d.user_member_expire_time 会员过期时间戳
 * @apiSuccess {string} d.user_is_member 是否为会员
 * @apiSuccess {string} d.access_token access_token
 * @apiSuccess {string} d.anchor_ranking 主播排名
 * @apiSuccess {string} d.ws_url 弃用
 * @apiSuccess {string} d.user_constellation 用户星座
 * @apiSuccess {string} d.user_img 用户图集
 * @apiSuccess {string} d.user_video 用户视频
 * @apiSuccess {string} d.user_video_cover 用户视频封面
 * @apiSuccess {string} d.user_home_town 用户家乡
 * @apiSuccess {string} d.user_hobby 爱好
 * @apiSuccess {string} d.user_profession 用户职业
 * @apiSuccess {string} d.user_emotional_state 用户情感状况
 * @apiSuccess {string} d.user_income 用户收入
 * @apiSuccess {string} d.user_height 用户身高
 * @apiSuccess {string} d.user_register_time 注册时间
 * @apiSuccess {object} d.tim 腾讯云TIM
 * @apiSuccess {string} d.tim.sign 腾讯云签名
 * @apiSuccess {string} d.tim.account 腾讯云账号
 * @apiSuccess {string} d.tim.account_type 腾讯云账号类型
 * @apiSuccess {string} d.tim.app_id 腾讯云app_id
 * @apiSuccess {object} d.match_center_info 匹配信息
 * @apiSuccess {string} d.match_center_info.room_id 匹配大厅房间id
 * @apiSuccess {number} d.match_center_info.match_price 匹配价格
 * @apiSuccess {string} d.h5_pay_url H5充值地址
 * @apiSuccess {string} d.h5_vip_url H5购买VIP地址
 * @apiSuccess {string} d.customer_service_id 客服id
 * @apiSuccess {number} d.guide_video_time 诱导视频时长
 * @apiSuccess {object} d.anchor_info 主播信息
 * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
 * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
 * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
 * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
 * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
 * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
 * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
 * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
 * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
 * @apiSuccess {object} d.user_signin  签到信息
 * @apiSuccess {String} d.user_signin.is_signin  是否签到 Y 是 N 否
 * @apiSuccess {String} d.user_signin.tips  签到提示
 * @apiSuccess {object} d.unread 消息未读
 * @apiSuccess {number} d.unread.total  总未读数
 * @apiSuccess {String} d.unread.user_chat  聊天未读数
 * @apiSuccess {String} d.unread.system_message 系统消息未读数
 * @apiSuccess {number} d.unread.video_message 小视频消息未读数
 * @apiSuccess {number} d.unread.video_chat_message 视频聊天未读数
 * @apiSuccess {number} d.free_times 免费匹配时长（分钟）
 * @apiSuccess {object} d.first_share_reward 首次邀请奖励信息
 * @apiSuccess {number} d.first_share_reward.free_times 赠送的免费时长
 * @apiSuccess {number} d.first_share_reward.total_over_time_hour 总过期时间（小时）
 * @apiSuccess {number} d.first_share_reward.over_time_second 剩余过期时间（秒）
 * @apiSuccess {object} d.share 分享信息
 * @apiSuccess {String} d.share.logo logo（暂不使用）
 * @apiSuccess {String} d.share.content 文案（暂不使用）
 * @apiSuccess {String} d.share.url 分享地址
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *   {
 *       "c": 0,
 *       "m": "请求成功",
 *       "d": {
 *           "user_id": "172",
 *           "user_nickname": "18823369189",
 *           "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
 *           "user_sex": "1",
 *           "user_coin": "10.00",
 *           "user_dot": "100.00",
 *           "user_consume_total": "0.00",
 *           "user_collect_total": "0.00",
 *           "user_intro": "",
 *           "user_birth": "",
 *           "user_lat": "0.000000",
 *           "user_lng": "0.000000",
 *           "user_invite_code": "W21309",
 *           "user_invite_total": "0",
 *           "user_follow_total": "0",
 *           "user_fans_total": "0",
 *           "user_is_certification": "Y",
 *           "user_is_anchor": "Y",
 *           "user_phone": "188****9189",
 *           "user_token_expire_time": "1536483157",
 *           "user_member_expire_time": "1533867150",
 *           "user_is_member": "O",
 *           "access_token": "xYNJYC957HNtAWMSN3CIhrroVKfsspC4RQBM6e7XFQAzOTi.ysbTTL9_HwvlVJuvsEbGKC7IFCCTncwNjN3Q",
 *           "anchor_ranking": "0",
 *           "ws_url": "?uid=172&token=&extra=eyJ1c2VyIjp7InVzZXJfaWQiOiIxNzIiLCJ1c2VyX2F2YXRhciI6Imh0dHA6XC9cL2xlYm9saXZlLTEyNTU2NTEyNzMuZmlsZS5teXFjbG91ZC5jb21cL2F2YXRhci5qcGciLCJ1c2VyX25pY2tuYW1lIjoiMTg4MjMzNjkxODkiLCJ1c2VyX3NleCI6IjEiLCJ1c2VyX2lzX21lbWJlciI6Ik8ifX0%3D",
 *           "user_constellation": "",
 *           "user_img": null,
 *           "user_video": "",
 *           "user_video_cover": "",
 *           "user_home_town": "",
 *           "user_hobby": "",
 *           "user_profession": "",
 *           "user_emotional_state": "",
 *           "user_income": "",
 *           "user_height": "",
 *           "apple_online": 1,
 *           "user_register_time": "1533623873",
 *           "tim": {
 *                   "sign": "eJxNjV9PgzAUR78Lz8b1zwrDZA84NzSOBSNBY5Y0SAu7TqCWwhjG7y7iFn095-zu-bSi9eNlkqZVUxpujkpaVxayLkYMQpYGMpB6gNghJ5woBYInhlMt-tW12PNR-cRThBCz8ey8kZ0CLXmSmd9jjDEyJCfbSl1DVQ6CIMwwoQj9SQOFHCd06hJMMT3-g3zAwfJhcbfCgR32u*fsJfLKTQdt*BZEjn2T*03cm1v9Gl-7e*fozNzUAy8q4LB69yWpl6HshPtx2E682Nw-Fe12sl4o3O9k1jeV6fAmn8*tr29ib1g8",
 *                   "account": "172",
 *                   "account_type": "20760",
 *                   "app_id": "1400056182"
 *           },
 *           "match_center_info": {
 *                   "room_id": "matchCenterRoomDev",
 *                   "match_price": 10
 *           },
 *            "h5_pay_url": "http://dev.charge.sxypaopao.com/pay.php?uid=172",
 *            "h5_vip_url": "http://dev.charge.sxypaopao.com/vip.php?uid=172",
 *            "customer_service_id": "181",
 *            "guide_video_time": 15,
 *            "anchor_info": {
 *                   "anchor_tip": "",
 *                   "anchor_character": "",
 *                   "anchor_good_topic": "",
 *                   "anchor_dress": "",
 *                   "anchor_stature": "",
 *                   "anchor_images": "",
 *                   "anchor_check_img": ""
 *           },
 *              "user_signin": {
 *                  "is_signin": "Y",
 *                "tips": "今天还没有签到哦！"
 *              },
 *              "unread": {
 *                  "total": "0",
 *                "user_chat": "",
 *                "system_message": "",
 *                "video_message": "0",
 *                "video_chat_message": "0"
 *              },
 *            "free_times": "1",
 *            "first_share_reward": {
 *                "free_times": 0,
 *                "total_over_time_hour": 0,
 *                "over_time_second": 0
 *            },
 *             "share": {
 *                 "logo": "http://dev.api.sxypaopao.com/assets/images/logo.png",
 *                 "url": "http://dev.h5.sxypaopao.com/register?channelCode=gGgdfpvuzWOBWncQ&invite_code=P52215",
 *                 "content": "我在泡泡直播"
 *             }
 *       },
 *       "t": 1534921313
 *   }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

/**
 * sendAction 发送聊天
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/send
 * @api {post} /user/chat/send 发送消息
 * @apiName 发送聊天
 * @apiGroup Chat
 * @apiDescription 发送消息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} chat_room_id 聊天id
 * @apiParam (正常请求){String} to_user_id 接收方用户id
 * @apiParam (正常请求){String} content 内容  根据类型传值 如果是诱导 则传诱导id 第三次诱导 msg_type 需要传image
 * @apiParam (正常请求){String} pay 是否确认付费  确认为1
 * @apiParam (正常请求){String='sayHi(打招呼)','normal(普通消息)','guide(诱导)'} type 聊天类型
 * @apiParam (正常请求){String='word(文字)','image(图片)','video(视频)','voice(语音)'} msg_type 消息类型
 * @apiParam (正常请求){String} extra 额外信息（接收方 收到时 同样返回）
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} chat_room_id 聊天id
 * @apiParam (debug){String} to_user_id 接收方用户id
 * @apiParam (debug){String} content 内容  根据类型传值
 * @apiParam (debug){String} pay 是否确认付费  确认为1
 * @apiParam (debug){String='sayHi(打招呼)','normal(普通消息)'} type 聊天类型
 * @apiParam (debug){String='word(文字)','image(图片)','video(视频)','voice(语音)'} msg_type 消息类型
 * @apiParam (debug){String} extra 额外信息（接收方 收到时 同样返回）
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {String} d.pushContent
 * @apiSuccess {String} d.chat_room_id
 * @apiSuccess {object[]} d.dialog
 * @apiSuccess {object} d.dialog.from_user
 * @apiSuccess {number} d.dialog.from_user.user_id
 * @apiSuccess {String} d.dialog.from_user.user_nickname
 * @apiSuccess {String} d.dialog.from_user.user_avatar
 * @apiSuccess {String} d.dialog.from_user.user_is_member
 * @apiSuccess {String} d.dialog.msg
 * @apiSuccess {number} d.dialog.time
 * @apiSuccess {number} d.dialog.dialog_id
 * @apiSuccess {String='word(文字)','image(图片)','video(视频)','voice(语音)','video_chat(视频聊天)'} d.dialog.type
 * @apiSuccess {String} d.dialog.source_url
 * @apiSuccess {String} d.dialog.extra
 * @apiSuccess {String} d.dialog.video_chat_status  视频聊天状态
 * @apiSuccess {String} d.dialog.video_chat_duration  视频聊天时长
 * @apiSuccess {String} d.dialog.video_chat_has_callback  视频聊天是否已回拨  Y 为已回拨 N 为未回拨
 * @apiSuccess {number} d.dialog.income
 * @apiSuccess {object} d.dialog.to_user
 * @apiSuccess {number} d.dialog.to_user.user_id
 * @apiSuccess {String} d.dialog.to_user.user_nickname
 * @apiSuccess {String} d.dialog.to_user.user_avatar
 * @apiSuccess {String} d.dialog.to_user.user_is_member
 * @apiSuccess {String} d.guide_id
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *           {
 *               "c": 0,
 *               "m": "请求成功",
 *               "d": {
 *                       "pushContent": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533700478894.png",
 *                       "chat_room_id": "251_168",
 *                       "dialog": {
 *                               "from_user": {
 *                                   "user_id": "168",
 *                                   "user_nickname": "啦啦啦",
 *                                   "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/e1u7Ut4rUff6QDfsRXuTjJwpuqaEBeyBL8FC7bIu6fcuXkogvUBRYLVCIRFLQicgwxVVC3dibibSbkxM88BXsQVSA\/132",
 *                                   "user_is_member": "N"
 *                               },
 *                               "msg": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533700478894.png",
 *                               "time": 1542003726,
 *                               "dialog_id": "2456",
 *                               "type": "image",
 *                               "source_url": null,
 *                               "extra": "",
 *                               "income": 0,
 *                               "to_user": {
 *                                   "user_id": "251",
 *                                   "user_nickname": "Dawn09101048222",
 *                                   "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9012BAEA9B36E6AE8846D0EFE9C05A13\/100",
 *                                   "user_is_member": "Y"
 *                               }
 *                       },
 *                       "guide_id": '0'
 *               },
 *               "t": "1542003727"
 *           }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getPrivateChat
 * @api {get} /live/anchor/getPrivateChat 首页主播
 * @apiName getPrivateChat
 * @apiGroup Index
 * @apiDescription 首页主播  审核状态中，不展示离线的用户
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} local  城市
 * @apiParam (正常请求) {String="index(首页)","all(所有)","hot(热门)","follow(关注)","new(新人)","guide(诱导)","nearby(附近)"} type  类型
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} local  城市
 * @apiParam (debug) {String="index(首页)","all(所有)","hot(热门)","follow(关注)","new(新人)","guide(诱导)","nearby(附近)"} type  类型
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.items  内容
 * @apiSuccess {String} d.items.user_nickname  昵称
 * @apiSuccess {String} d.items.user_avatar  头像
 * @apiSuccess {String} d.items.user_intro  简介
 * @apiSuccess {String} d.items.user_profession  职业
 * @apiSuccess {number} d.items.anchor_chat_status  聊天状态
 * @apiSuccess {number} d.items.user_id  用户id
 * @apiSuccess {number} d.items.user_sex 性别
 * @apiSuccess {number} d.items.user_birth 生日
 * @apiSuccess {String} d.items.user_video_cover  视频封面
 * @apiSuccess {String} d.items.user_video   视频地址
 * @apiSuccess {number} d.items.is_follow  是否关注  0为 未关注 其他为已关注
 * @apiSuccess {String} d.items.anchor_tip
 * @apiSuccess {String} d.items.anchor_character
 * @apiSuccess {String} d.items.anchor_good_topic
 * @apiSuccess {String} d.items.anchor_dress
 * @apiSuccess {String} d.items.anchor_stature
 * @apiSuccess {String} d.items.anchor_emotional_state
 * @apiSuccess {number} d.items.price
 * @apiSuccess {String} d.items.anchor_local
 * @apiSuccess {String} d.items.anchor_title
 * @apiSuccess {String} d.items.share_url
 * @apiSuccess {String} d.items.anchor_guard_id  主播守护id
 * @apiSuccess {number} d.page
 * @apiSuccess {number} d.pagesize
 * @apiSuccess {number} d.pagetotal
 * @apiSuccess {number} d.total
 * @apiSuccess {number} d.prev
 * @apiSuccess {number} d.next
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *         "m": "请求成功",
 *         "d": {
 *             "items": [{
 *                 "user_nickname": "118啦咯",
 *                 "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/09\/1533807443523.png",
 *                 "user_intro": "徒孙气质红哦你好给力了",
 *                 "user_profession": "工程师",
 *                 "anchor_chat_status": "3",
 *                 "user_id": "166",
 *                 "user_sex": "2",
 *                 "anchor_guard_id": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533694327924.png"
 *         }, {
 *                 "user_nickname": "泡泡08271736169",
 *                 "user_avatar": "http:\/\/lebolive-1255651273.file.myqcloud.com\/avatar.jpg",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "1",
 *                 "user_id": "230",
 *                 "user_sex": "2",
 *                 "anchor_guard_id": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/27\/1970dd6937d27862b745642fabbc6af5"
 *         }, {
 *                 "user_nickname": "神秘",
 *                 "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg\/132",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "0",
 *                 "user_id": "170",
 *                 "user_sex": "1",
 *                 "anchor_guard_id": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/06\/1533540159917.png"
 *         }],
 *         "page": 1,
 *         "pagesize": 50,
 *         "pagetotal": 1,
 *         "total": 3,
 *         "prev": 1,
 *         "next": 1
 *         },
 *         "t": 1535600607
 *     }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/gift/list
 * @api {get} /live/gift/list 礼物列表
 * @apiName 礼物列表
 * @apiGroup Gift
 * @apiDescription 礼物列表
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.gift_list 礼物列表
 * @apiSuccess {number} d.gift_list.id  礼物id
 * @apiSuccess {String} d.gift_list.name 礼物名称
 * @apiSuccess {number} d.gift_list.coin 礼物价格
 * @apiSuccess {String} d.gift_list.icon  图标
 * @apiSuccess {number} d.gift_list.vip_coin  VIP价格
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *     	"m": "请求成功",
 *     	"d": {
 *                 "gift_list": [{
 *                     "id": "4",
 *     			    "name": "棒棒糖",
 *     			    "coin": "30",
 *     			    "icon": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180329\/1522286516428231.png",
 *     			    "vip_coin": "30",
 *     			    "type": "1"
 *     		}, {
 *                     "id": "76",
 *     			    "name": "钻戒",
 *     			    "coin": "199",
 *     			    "icon": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/20180608\/1528435802176916.gif",
 *     			    "vip_coin": "199",
 *     			    "type": "2"
 *     		}]
 *     	},
 *     	"t": 1535956013
 *     }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/search/index
 * @api {get} /live/search/index 主页搜索
 * @apiName search-index
 * @apiGroup Live
 * @apiDescription 主页搜索
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} kw 关键词
 * @apiParam (正常请求){Number} page 页码
 * @apiParam (正常请求){Number} pagesize 页数
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} kw 关键词
 * @apiParam (debug){Number} page 页码
 * @apiParam (debug){Number} pagesize 页数
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

/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/config
 * @api {get} /user/app/config app系统配置
 * @apiName app-config
 * @apiGroup User
 * @apiDescription app系统配置
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String='android','ios'} app_os 操作系统
 * @apiParam (正常请求){String} app_name app名称flg
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String='android','ios'} app_os 操作系统
 * @apiParam (debug){String} app_name app名称flg
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {String} d.marketAudit
 * @apiSuccess {String} d.coin
 * @apiSuccess {String} d.dot
 * @apiSuccess {String} d.guide_msg_flg   是否开启用户诱导  Y为开启 N为不开启
 * @apiSuccess {object} d.version
 * @apiSuccess {String} d.version.name
 * @apiSuccess {number} d.version.code
 * @apiSuccess {String} d.version.content
 * @apiSuccess {String} d.version.is_force
 * @apiSuccess {String} d.version.download_url
 * @apiSuccess {number} d.version.create_time
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *       {
 *           "c": 0,
 *           "m": "请求成功",
 *           "d": {
 *                   "marketAudit": "N",
 *                   "coin": "金币",
 *                   "dot": "佣金",
 *                   "guide_msg_flg": "Y",
 *                   "version": {
 *                           "name": "v1.7.2",
 *                           "code": "44",
 *                           "content": "ssssss",
 *                           "is_force": "N",
 *                           "download_url": "https:\/\/www.baidu.com\/huanggua",
 *                           "create_time": "1540550198"
 *                       }
 *           },
 *           "t": "1542007936"
 *       }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * 挂断一对一私聊
 * 主播收钱
 *
 * 1. 视频接通10s内挂断，扣取用户金币，但不予主播结算
 * 2. 视频通话如果主播主动关闭，该视频通话最后一分钟，不予结算，但同样扣取用户金币。
 * 若用户主动关闭，不受该逻辑影响
 *
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/hangUpChat
 * @api {get} /live/anchor/hangUpChat 挂断聊天
 * @apiName hangUpChat
 * @apiGroup Chat
 * @apiDescription 挂断聊天
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} chat_log 聊天id
 * @apiParam (正常请求){String='manual(手动),auto(自动)'} hang_up_type 挂断类型
 * @apiParam (正常请求){String} detail 挂断原因
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} chat_log 聊天id
 * @apiParam (debug){String='manual(手动),auto(自动)'} hang_up_type 挂断类型
 * @apiParam (debug){String} detail 挂断原因
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object} d.gift  礼物收益
 * @apiSuccess {number} d.gift.coin  礼物消费金币
 * @apiSuccess {number} d.gift.dot  礼物收益
 * @apiSuccess {object} d.chat_game  聊天游戏收益
 * @apiSuccess {number} d.chat_game.coin  聊天游戏金币
 * @apiSuccess {number} d.chat_game.dot  聊天游戏收益
 * @apiSuccess {object} d.total  总收益
 * @apiSuccess {number} d.total.coin  聊天游戏金币
 * @apiSuccess {number} d.total.dot  聊天游戏收益
 * @apiSuccess {number} d.free_times  使用免费时长
 * @apiSuccess {number} d.amount  时间收益
 * @apiSuccess {number} d.coin_amount  时间消费
 * @apiSuccess {number} d.duration  时长
 * @apiSuccess {String} d.is_follow  是否关注
 * @apiSuccess {String} d.snatch_status  抢聊状态  Y 成功，N 失败，C 等待中
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "c": 0,
 *          "m": "请求成功",
 *          "d": {
 *                  "gift": {
 *                      "coin": "30",
 *                      "dot": "1.2"
 *              },
 *                  "chat_game": {
 *                      "coin": "30",
 *                      "dot": "1.2"
 *              },
 *                  "total": {
 *                      "coin": "30",
 *                      "dot": "1.2"
 *              },
 *              "free_times": "10",
 *              "amount": "0.8000",
 *              "coin_amount": 20,
 *              "duration": 50,
 *              "is_follow": "Y",
 *              "snatch_status": "Y"
 *          },
 *          "t": 1536662604
 *      }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 * */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/startPrivateChat
 * @api {get} /live/anchor/startPrivateChat 邀请一对一聊天
 * @apiName startPrivateChat
 * @apiGroup Chat
 * @apiDescription 邀请一对一聊天
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} user_id 主播用户id
 * @apiParam (正常请求){String} dialog_id 消息id（回拨时传）
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} user_id 主播用户id
 * @apiParam (debug){String} dialog_id 消息id（回拨时传）
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {string} d.push_url 腾讯云推流地址
 * @apiSuccess {string} d.chat_log 聊天id
 * @apiSuccess {Object} d.wangsu 网宿内容
 * @apiSuccess {string} d.wangsu.push_url 推流地址
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *         "d": "",
 *         "m": "请求成功",
 *         "t": 1534911421
 *     }
 *  邀请私聊推送
 *    {
 *        "type": "private_chat",
 *        "msg": "私聊消息",
 *        "data": {
 *                "is_user_call": "Y",
 *                "chat_log": "4493",
 *                "f_user_id": "318",
 *                "f_user_nickname": "渐入佳境",
 *                "f_user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1107915107\/63F3F098E6FAC4B5C210CA2458C66BE6\/100",
 *                "f_user_level": "1",
 *                "anchor_video_url": "https:\/\/lebolive-1255651273.image.myqcloud.com\/video\/2018\/11\/27\/output-2018-11-27-17:45:40-643.mp4",
 *                "play_rtmp": "rtmp:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
 *                "play_flv": "http:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2.flv?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
 *                "play_m3u8": "http:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2.m3u8?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
 *                "wangsu": {
 *                    "push_url": ""
 *                }
 *        }
 *    }
 *
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {私聊_
 *       "error": "UserNotFound"
 *     }
 */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getAnchorInfo
 * @api {get} /live/anchor/getAnchorInfo 获取主播个人主页信息
 * @apiName getAnchorInfo
 * @apiGroup Anchor
 * @apiDescription 获取主播个人主页信息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} user_id 主播用户id
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} user_id 主播用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {number} d.user_nickname   昵称
 * @apiSuccess {String} d.user_avatar  头像
 * @apiSuccess {String} d.user_level  用户等级
 * @apiSuccess {number} d.user_sex 性别
 * @apiSuccess {number} d.anchor_ranking  排名
 * @apiSuccess {number} d.user_id  用户id
 * @apiSuccess {String} d.is_follow 是否关注
 * @apiSuccess {String} d.user_intro  简介
 * @apiSuccess {String} d.user_constellation  用户星座
 * @apiSuccess {String} d.user_emotional_state 用户情感
 * @apiSuccess {String} d.coin_name  虚拟币名称
 * @apiSuccess {String} d.user_hobby  用户爱好
 * @apiSuccess {String} d.user_profession 用户职业
 * @apiSuccess {number} d.price  价格
 * @apiSuccess {String} d.anchor_local  地址
 * @apiSuccess {number} d.anchor_title 标题
 * @apiSuccess {String} d.user_video  视频
 * @apiSuccess {String} d.user_video_cover 视频封面
 * @apiSuccess {String} d.anchor_video_check_status  视频审核状态
 * @apiSuccess {String} d.anchor_image_check_status   图片审核状态
 * @apiSuccess {object} d.anchor_info  主播信息
 * @apiSuccess {String} d.anchor_info.anchor_guard_id  守护id
 * @apiSuccess {String} d.anchor_info.anchor_tip  标签
 * @apiSuccess {String} d.anchor_info.anchor_character  个性
 * @apiSuccess {String} d.anchor_info.anchor_good_topic 擅长
 * @apiSuccess {String} d.anchor_info.anchor_dress  爱穿
 * @apiSuccess {String} d.anchor_info.anchor_stature 身材
 * @apiSuccess {String} d.anchor_info.anchor_images  图片
 * @apiSuccess {String} d.anchor_info.anchor_check_img 审核图片
 * @apiSuccess {String} d.anchor_info.anchor_emotional_state  主播情感
 * @apiSuccess {number} d.anchor_info.anchor_connection_rate  接通率
 * @apiSuccess {String} d.app_share_url
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
/**
 * cardAction 获取用户名片信息
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/card
 * @api {get} /user/profile/card 获取用户名片信息
 * @apiName 用户名片-profile-card
 * @apiGroup Profile
 * @apiDescription 获取用户名片信息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} user_id 用户id
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} user_id 用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {number} d 数据内容
 * @apiSuccess {number} d.user_id  用户id
 * @apiSuccess {String} d.user_nickname  昵称
 * @apiSuccess {String} d.user_avatar 头像
 * @apiSuccess {number} d.user_sex 性别
 * @apiSuccess {number} d.user_consume_total 消费
 * @apiSuccess {number} d.user_collect_total 收益
 * @apiSuccess {String} d.user_intro 简介
 * @apiSuccess {String} d.user_birth  生日
 * @apiSuccess {number} d.user_follow_total  关注
 * @apiSuccess {number} d.user_fans_total 粉丝
 * @apiSuccess {String} d.user_is_anchor 是否是主播
 * @apiSuccess {number} d.anchor_ranking 主播排行榜
 * @apiSuccess {String} d.is_follow 是否关注
 * @apiSuccess {String} d.chat_room_id 聊天房间id
 * @apiSuccess {String} d.is_card_effect 卡片特效
 * @apiSuccess {number} d.is_online 在线
 * @apiSuccess {String} d.user_img 用户图册
 * @apiSuccess {number} d.user_register_time 用户注册时间
 * @apiSuccess {String} d.user_home_town 家乡
 * @apiSuccess {String} d.user_constellation 用户星座
 * @apiSuccess {String} d.user_hobby 爱好
 * @apiSuccess {String} d.user_profession 用户职业
 * @apiSuccess {String} d.user_emotional_state 用户情感状况
 * @apiSuccess {String} d.user_income 用户收入
 * @apiSuccess {String} d.user_height 用户身高
 * @apiSuccess {String} d.is_black  是否黑名单
 * @apiSuccess {String} d.share_url 分享地址
 * @apiSuccess {object} d.gift_img 礼物图册
 * @apiSuccess {number} d.total_gift 礼物数量
 * @apiSuccess {String} d.user_is_member 是否为会员
 * @apiSuccess {object} d.anchor_info 主播信息
 * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
 * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
 * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
 * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
 * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
 * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
 * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
 * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
 * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
 * @apiSuccess {string} d.anchor_info.anchor_video 封面视频
 * @apiSuccess {string} d.anchor_info.anchor_video_cover 视频封面
 * @apiSuccess {string} d.anchor_info.anchor_chat_price 通话价格
 * @apiSuccess {string} d.anchor_info.anchor_chat_status 主播聊天状态
 * @apiSuccess {object} d.anchor_info.anchor_guard 主播守护
 * @apiSuccess {string} d.anchor_info.anchor_guard.user_id 守护用户id
 * @apiSuccess {string} d.anchor_info.anchor_guard.user_nickname 守护用户昵称
 * @apiSuccess {string} d.anchor_info.anchor_guard.user_avatar 守护用户头像
 * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_coin 守护金币
 * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_level 守护等级
 * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_level_name 守护等级名称
 * @apiSuccess {object} d.anchor_images_list  图片集
 * @apiSuccess {string} d.anchor_images_list.img_src  图片地址
 * @apiSuccess {string} d.anchor_images_list.visible_type 可见类型  normal vip (vip用户查看时 会变为normal)
 * @apiSuccess {object} d.guard_list  守护中的列表
 * @apiSuccess {number} d.guard_list.user_id    用户id
 * @apiSuccess {String} d.guard_list.user_nickname 用户昵称
 * @apiSuccess {String} d.guard_list.user_avatar 用户头像
 * @apiSuccess {number} d.guard_list.total_coin  用户守护值
 * @apiSuccess {number} d.guard_list.current_level  守护等级
 * @apiSuccess {String} d.guard_list.current_level_name   守护等级名称
 * @apiSuccess {String} d.guard_list.guard_status  守护状态  Y 为守护中， N 为守护过
 * @apiSuccessExample Success-Response:
 *  {
 *      "c": 0,
 *      "m": "请求成功",
 *      "d": {
 *              "user_id": "171",
 *              "user_nickname": "L--Steven",
 *              "user_avatar": "http://tvax3.sinaimg.cn/crop.0.0.512.512.180/881b51bbly8fs6z7jhuajj20e80e8mxl.jpg",
 *              "user_sex": "2",
 *              "user_consume_total": "2253.00",
 *              "user_collect_total": "3.20",
 *              "user_intro": "",
 *              "user_birth": "",
 *              "user_follow_total": "1",
 *              "user_fans_total": "0",
 *              "user_is_anchor": "Y",
 *              "anchor_ranking": "0",
 *              "is_follow": "N",
 *              "chat_room_id": "172_171",
 *              "is_card_effect": "N",
 *              "is_online": 1,
 *              "user_img": null,
 *              "user_register_time": "1533554656",
 *              "user_home_town": "",
 *              "user_constellation": "",
 *              "user_hobby": "",
 *              "user_profession": "",
 *              "user_emotional_state": "",
 *              "user_income": "",
 *              "user_height": "",
 *              "is_black": false,
 *              "share_url": "http://dev.h5.sxypaopao.com/shareuser?user_id=171",
 *              "gift_img": [],
 *              "total_gift": 0,
 *              "user_is_member": "N",
 *              "anchor_info": {
 *                  "anchor_tip": "",
 *                  "anchor_character": "",
 *                  "anchor_good_topic": "",
 *                  "anchor_dress": "",
 *                  "anchor_check_img": "",
 *                  "anchor_emotional_state": "",
 *                  "anchor_images": "",
 *                  "anchor_stature": ""
 *                  "anchor_connection_rate": "",
 *                  "anchor_video_cover": "",
 *                  "anchor_video": "",
 *                  "anchor_chat_price": "",
 *                  "anchor_chat_status": "",
 *                  "anchor_guard": {
 *                      "user_id": "".
 *                      "user_nickname": "",
 *                      "user_avatar": "",
 *                      "anchor_guard_coin": "",
 *                      "anchor_guard_level": "",
 *                      "anchor_guard_level_name": "",
 *                   }
 *              },
 *              "anchor_images_list": [
 *                  {
 *                      "img_src": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/23/1534997938083.png",
 *                      "visible_type": "normal"
 *                  },
 *                  {
 *                      "img_src": "https://lebolive-1255651273.image.myqcloud.com/image/2018/10/30/1540865312180.png",
 *                      "visible_type": "normal"
 *                  }
 *              ],
 *              "guard_list": [
 *                  {
 *                      "user_id": "258",
 *                      "user_nickname": "LYXXMY一样",
 *                      "user_avatar": "http://thirdqq.qlogo.cn/qqapp/1106652113/23F1690D0AD8715603EA3D3E1AF30D19/100",
 *                      "total_coin": "370",
 *                      "current_level": "1",
 *                      "current_level_name": "初级守护",
 *                      "guard_status": "Y"
 *                  }
 *              ]
 *      },
 *      "t": 1534924613
 *  }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/config
 * @api {get} /user/app/config app系统配置
 * @apiName app-config
 * @apiGroup User
 * @apiDescription app系统配置
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String='android','ios'} app_os 操作系统
 * @apiParam (正常请求){String} app_name app名称flg
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String='android','ios'} app_os 操作系统
 * @apiParam (debug){String} app_name app名称flg
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {String} d.marketAudit
 * @apiSuccess {String} d.coin
 * @apiSuccess {String} d.dot
 * @apiSuccess {String} d.guide_msg_flg   是否开启用户诱导  Y为开启 N为不开启
 * @apiSuccess {String='Y','N'} d.activity_notice_flg   是否开启活动提醒
 * @apiSuccess {String} d.activity_show_url   活动跳转地址
 * @apiSuccess {object} d.version
 * @apiSuccess {String} d.version.name
 * @apiSuccess {number} d.version.code
 * @apiSuccess {String} d.version.content
 * @apiSuccess {String} d.version.is_force
 * @apiSuccess {String} d.version.download_url
 * @apiSuccess {number} d.version.create_time
 * @apiSuccess {object[]} d.user_level_config    用户等级信息
 * @apiSuccess {number} d.user_level_config.level  等级值
 * @apiSuccess {number} d.user_level_config.color_r  等级背景颜色 R
 * @apiSuccess {number} d.user_level_config.color_g  等级背景颜色 G
 * @apiSuccess {number} d.user_level_config.color_b 等级背景颜色 B
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *       {
 *           "c": 0,
 *           "m": "请求成功",
 *           "d": {
 *                   "marketAudit": "N",
 *                   "coin": "金币",
 *                   "dot": "佣金",
 *                   "guide_msg_flg": "Y",
 *                   "activity_notice_flg": "Y",
 *                   "activity_show_url": "https://www.baidu.com/",
 *                   "version": {
 *                           "name": "v1.7.2",
 *                           "code": "44",
 *                           "content": "ssssss",
 *                           "is_force": "N",
 *                           "download_url": "https:\/\/www.baidu.com\/huanggua",
 *                           "create_time": "1540550198"
 *                       },
 *                     "user_level_config": [{
 *                         "level": "1",
 *                             "color_r": 107,
 *                             "color_g": 255,
 *                             "color_b": 245
 *                         }, {
 *                         "level": "2",
 *                             "color_r": 228,
 *                             "color_g": 140,
 *                             "color_b": 255
 *                         }, {
 *                         "level": "3",
 *                             "color_r": 255,
 *                             "color_g": 157,
 *                             "color_b": 92
 *                     }]
 *           },
 *           "t": "1542007936"
 *       }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * indexAction 获取用户信息
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/index
 * @api {get} /user/profile/index 获取用户信息
 * @apiName 获取用户信息-profile-index
 * @apiGroup Profile
 * @apiDescription 获取用户信息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {string} d.user_id 用户id
 * @apiSuccess {string} d.user_nickname 用户昵称
 * @apiSuccess {string} d.user_avatar 头像
 * @apiSuccess {string} d.user_coin 金币
 * @apiSuccess {string} d.user_dot 佣金
 * @apiSuccess {string} d.user_consume_total 用户总消费
 * @apiSuccess {string} d.user_collect_total 用户总收礼
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_birth 生日
 * @apiSuccess {string} d.user_intro 个人简介
 * @apiSuccess {string} d.user_lat 纬度
 * @apiSuccess {string} d.user_lng 经度
 * @apiSuccess {string} d.user_invite_code 邀请码
 * @apiSuccess {string} d.user_invite_total 邀请总数
 * @apiSuccess {string} d.user_follow_total 关注数
 * @apiSuccess {string} d.user_fans_total 粉丝数
 * @apiSuccess {string} d.user_is_certification 是否认证
 * @apiSuccess {string} d.user_is_anchor 是否主播
 * @apiSuccess {string} d.user_phone 手机号码
 * @apiSuccess {string} d.user_token_expire_time token过期时间戳
 * @apiSuccess {string} d.user_member_expire_time 会员过期时间戳
 * @apiSuccess {string} d.user_is_member 是否为会员
 * @apiSuccess {string} d.access_token access_token
 * @apiSuccess {string} d.anchor_ranking 主播排名
 * @apiSuccess {string} d.ws_url 弃用
 * @apiSuccess {string} d.user_constellation 用户星座
 * @apiSuccess {string} d.user_img 用户图集
 * @apiSuccess {string} d.user_video 用户视频
 * @apiSuccess {string} d.user_video_cover 用户视频封面
 * @apiSuccess {string} d.user_home_town 用户家乡
 * @apiSuccess {string} d.user_hobby 爱好
 * @apiSuccess {string} d.user_profession 用户职业
 * @apiSuccess {string} d.user_emotional_state 用户情感状况
 * @apiSuccess {string} d.user_income 用户收入
 * @apiSuccess {string} d.user_height 用户身高
 * @apiSuccess {string} d.user_register_time 注册时间
 * @apiSuccess {object} d.tim 腾讯云TIM
 * @apiSuccess {string} d.tim.sign 腾讯云签名
 * @apiSuccess {string} d.tim.account 腾讯云账号
 * @apiSuccess {string} d.tim.account_type 腾讯云账号类型
 * @apiSuccess {string} d.tim.app_id 腾讯云app_id
 * @apiSuccess {object} d.match_center_info 匹配信息
 * @apiSuccess {string} d.match_center_info.room_id 匹配大厅房间id
 * @apiSuccess {number} d.match_center_info.match_price 匹配价格
 * @apiSuccess {string} d.h5_pay_url H5充值地址
 * @apiSuccess {string} d.h5_vip_url H5购买VIP地址
 * @apiSuccess {string} d.customer_service_id 客服id
 * @apiSuccess {number} d.guide_video_time 诱导视频时长
 * @apiSuccess {object} d.anchor_info 主播信息
 * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
 * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
 * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
 * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
 * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
 * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
 * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
 * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
 * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
 * @apiSuccess {object} d.user_signin  签到信息
 * @apiSuccess {String} d.user_signin.is_signin  是否签到 Y 是 N 否
 * @apiSuccess {String} d.user_signin.tips  签到提示
 * @apiSuccess {object} d.unread 消息未读
 * @apiSuccess {number} d.unread.total  总未读数
 * @apiSuccess {String} d.unread.user_chat  聊天未读数
 * @apiSuccess {String} d.unread.system_message 系统消息未读数
 * @apiSuccess {number} d.unread.video_message 小视频消息未读数
 * @apiSuccess {number} d.unread.video_chat_message 视频聊天未读数
 * @apiSuccess {number} d.free_times 免费匹配时长（分钟）
 * @apiSuccess {object} d.first_share_reward 首次邀请奖励信息
 * @apiSuccess {number} d.first_share_reward.free_times 赠送的免费时长
 * @apiSuccess {number} d.first_share_reward.total_over_time_hour 总过期时间（小时）
 * @apiSuccess {number} d.first_share_reward.over_time_second 剩余过期时间（秒）
 * @apiSuccess {object} d.share 分享信息
 * @apiSuccess {String} d.share.logo logo（暂不使用）
 * @apiSuccess {String} d.share.content 文案（暂不使用）
 * @apiSuccess {String} d.share.url 分享地址
 * @apiSuccess {String} d.b_chat_room 广播大群id
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *   {
 *       "c": 0,
 *       "m": "请求成功",
 *       "d": {
 *           "user_id": "172",
 *           "user_nickname": "18823369189",
 *           "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
 *           "user_sex": "1",
 *           "user_coin": "10.00",
 *           "user_dot": "100.00",
 *           "user_consume_total": "0.00",
 *           "user_collect_total": "0.00",
 *           "user_intro": "",
 *           "user_birth": "",
 *           "user_lat": "0.000000",
 *           "user_lng": "0.000000",
 *           "user_invite_code": "W21309",
 *           "user_invite_total": "0",
 *           "user_follow_total": "0",
 *           "user_fans_total": "0",
 *           "user_is_certification": "Y",
 *           "user_is_anchor": "Y",
 *           "user_phone": "188****9189",
 *           "user_token_expire_time": "1536483157",
 *           "user_member_expire_time": "1533867150",
 *           "user_is_member": "O",
 *           "access_token": "xYNJYC957HNtAWMSN3CIhrroVKfsspC4RQBM6e7XFQAzOTi.ysbTTL9_HwvlVJuvsEbGKC7IFCCTncwNjN3Q",
 *           "anchor_ranking": "0",
 *           "ws_url": "?uid=172&token=&extra=eyJ1c2VyIjp7InVzZXJfaWQiOiIxNzIiLCJ1c2VyX2F2YXRhciI6Imh0dHA6XC9cL2xlYm9saXZlLTEyNTU2NTEyNzMuZmlsZS5teXFjbG91ZC5jb21cL2F2YXRhci5qcGciLCJ1c2VyX25pY2tuYW1lIjoiMTg4MjMzNjkxODkiLCJ1c2VyX3NleCI6IjEiLCJ1c2VyX2lzX21lbWJlciI6Ik8ifX0%3D",
 *           "user_constellation": "",
 *           "user_img": null,
 *           "user_video": "",
 *           "user_video_cover": "",
 *           "user_home_town": "",
 *           "user_hobby": "",
 *           "user_profession": "",
 *           "user_emotional_state": "",
 *           "user_income": "",
 *           "user_height": "",
 *           "apple_online": 1,
 *           "user_register_time": "1533623873",
 *           "tim": {
 *                   "sign": "eJxNjV9PgzAUR78Lz8b1zwrDZA84NzSOBSNBY5Y0SAu7TqCWwhjG7y7iFn095-zu-bSi9eNlkqZVUxpujkpaVxayLkYMQpYGMpB6gNghJ5woBYInhlMt-tW12PNR-cRThBCz8ey8kZ0CLXmSmd9jjDEyJCfbSl1DVQ6CIMwwoQj9SQOFHCd06hJMMT3-g3zAwfJhcbfCgR32u*fsJfLKTQdt*BZEjn2T*03cm1v9Gl-7e*fozNzUAy8q4LB69yWpl6HshPtx2E682Nw-Fe12sl4o3O9k1jeV6fAmn8*tr29ib1g8",
 *                   "account": "172",
 *                   "account_type": "20760",
 *                   "app_id": "1400056182"
 *           },
 *           "match_center_info": {
 *                   "room_id": "matchCenterRoomDev",
 *                   "match_price": 10
 *           },
 *            "h5_pay_url": "http://dev.charge.sxypaopao.com/pay.php?uid=172",
 *            "h5_vip_url": "http://dev.charge.sxypaopao.com/vip.php?uid=172",
 *            "customer_service_id": "181",
 *            "guide_video_time": 15,
 *            "anchor_info": {
 *                   "anchor_tip": "",
 *                   "anchor_character": "",
 *                   "anchor_good_topic": "",
 *                   "anchor_dress": "",
 *                   "anchor_stature": "",
 *                   "anchor_images": "",
 *                   "anchor_check_img": ""
 *           },
 *              "user_signin": {
 *                  "is_signin": "Y",
 *                "tips": "今天还没有签到哦！"
 *              },
 *              "unread": {
 *                  "total": "0",
 *                "user_chat": "",
 *                "system_message": "",
 *                "video_message": "0",
 *                "video_chat_message": "0"
 *              },
 *            "free_times": "1",
 *            "first_share_reward": {
 *                "free_times": 0,
 *                "total_over_time_hour": 0,
 *                "over_time_second": 0
 *            },
 *             "share": {
 *                 "logo": "http://dev.api.sxypaopao.com/assets/images/logo.png",
 *                 "url": "http://dev.h5.sxypaopao.com/register?channelCode=gGgdfpvuzWOBWncQ&invite_code=P52215",
 *                 "content": "我在泡泡直播"
 *             }
 *       },
 *       "t": 1534921313
 *   }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/config
 * @api {get} /user/app/config app系统配置
 * @apiName app-config
 * @apiGroup User
 * @apiDescription app系统配置
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String='android','ios'} app_os 操作系统
 * @apiParam (正常请求){String} app_name app名称flg
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String='android','ios'} app_os 操作系统
 * @apiParam (debug){String} app_name app名称flg
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {String} d.marketAudit
 * @apiSuccess {String} d.coin
 * @apiSuccess {String} d.dot
 * @apiSuccess {String} d.guide_msg_flg   是否开启用户诱导  Y为开启 N为不开启
 * @apiSuccess {String} d.user_say_hi_flg   是否开启用户一键打招呼功能  Y为开启 N为不开启
 * @apiSuccess {String='Y','N'} d.activity_notice_flg   是否开启活动提醒
 * @apiSuccess {String} d.activity_show_url   活动跳转地址
 * @apiSuccess {object} d.version
 * @apiSuccess {String} d.version.name
 * @apiSuccess {number} d.version.code
 * @apiSuccess {String} d.version.content
 * @apiSuccess {String} d.version.is_force
 * @apiSuccess {String} d.version.download_url
 * @apiSuccess {number} d.version.create_time
 * @apiSuccess {object[]} d.user_level_config    用户等级信息
 * @apiSuccess {number} d.user_level_config.level  等级值
 * @apiSuccess {number} d.user_level_config.color_r  等级背景颜色 R
 * @apiSuccess {number} d.user_level_config.color_g  等级背景颜色 G
 * @apiSuccess {number} d.user_level_config.color_b 等级背景颜色 B
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *       {
 *           "c": 0,
 *           "m": "请求成功",
 *           "d": {
 *                   "marketAudit": "N",
 *                   "coin": "金币",
 *                   "dot": "佣金",
 *                   "guide_msg_flg": "Y",
 *                   "activity_notice_flg": "Y",
 *                   "activity_show_url": "https://www.baidu.com/",
 *                   "user_say_hi_flg": "Y",
 *                   "version": {
 *                           "name": "v1.7.2",
 *                           "code": "44",
 *                           "content": "ssssss",
 *                           "is_force": "N",
 *                           "download_url": "https:\/\/www.baidu.com\/huanggua",
 *                           "create_time": "1540550198"
 *                       },
 *                     "user_level_config": [{
 *                         "level": "1",
 *                             "color_r": 107,
 *                             "color_g": 255,
 *                             "color_b": 245
 *                         }, {
 *                         "level": "2",
 *                             "color_r": 228,
 *                             "color_g": 140,
 *                             "color_b": 255
 *                         }, {
 *                         "level": "3",
 *                             "color_r": 255,
 *                             "color_g": 157,
 *                             "color_b": 92
 *                     }]
 *           },
 *           "t": "1542007936"
 *       }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */

/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/level
 * @api {get} /user/profile/level 用户等级
 * @apiName profile-level
 * @apiGroup Profile
 * @apiDescription 用户等级
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {number} d.user_id   用户id
 * @apiSuccess {String} d.rank_hide   排行榜是否隐藏
 * @apiSuccess {String} d.user_avatar   用户头像
 * @apiSuccess {String} d.user_nickname   用户昵称
 * @apiSuccess {number} d.user_level  用户等级
 * @apiSuccess {number} d.user_exp  用户经验
 * @apiSuccess {number} d.this_level_exp  当前等级的经验
 * @apiSuccess {number} d.next_level_exp   下一等级所需经验
 * @apiSuccess {number} d.high_level_user_online   上线通知 所需等级
 * @apiSuccess {number} d.hide_rank_level   开启排行榜隐藏功能的等级
 * @apiSuccess {number='Y','N'} d.has_old_exp   是否有经验可以领取
 * @apiSuccess {object[]} d.rule
 * @apiSuccess {String} d.rule.content  规则内容
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *         {
 *             "c": 0,
 *             "m": "请求成功",
 *             "d": {
 *                     "user_id": "311",
 *                     "rank_hide": "Y",
 *                     "user_avatar": "https://lebolive-1255651273.image.myqcloud.com/image/2018/12/07/1544150678158.png",
 *                     "user_level": "4",
 *                     "user_nickname": "11111",
 *                     "user_exp": 568990,
 *                     "this_level_exp": 24000,
 *                     "next_level_exp": 18880,
 *                     "high_level_user_online": "4",
 *                     "hide_rank_level": "3",
 *                     "has_old_exp": "Y",
 *                     "rule": [
 *                         {
 *                             "content": "每消耗1金币即可获得10金币"
 *                         },
 *                         {
 *                             "content": "每日签到任务可获得经验值"
 *                         }
 *                     ]
 *             },
 *             "t": "1545982324"
 *         }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * 主播看到的用戶列表
 * 先显示10个新VIP（即最近充值VIP 24小时内）
 * 再显示10个老VIP（VIP充值超过 24小时）
 * 再显示30个普通用户
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/users
 * @api {get} /live/anchor/users 主播看到的用户列表
 * @apiName users
 * @apiGroup Anchor
 * @apiDescription 主播看到的用户列表
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
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
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/app/config
 * @api {get} /user/app/config app系统配置
 * @apiName app-config
 * @apiGroup User
 * @apiDescription app系统配置
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String='android','ios'} app_os 操作系统
 * @apiParam (正常请求){String} app_name app名称flg
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String='android','ios'} app_os 操作系统
 * @apiParam (debug){String} app_name app名称flg
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {String} d.become_anchor_guide_image  申请主播引导图片
 * @apiSuccess {String} d.invite_user_guide_image 邀请用户引导图片
 * @apiSuccess {String} d.marketAudit
 * @apiSuccess {String} d.coin
 * @apiSuccess {String} d.dot
 * @apiSuccess {String} d.guide_msg_flg   是否开启用户诱导  Y为开启 N为不开启
 * @apiSuccess {String} d.user_say_hi_flg   是否开启用户一键打招呼功能  Y为开启 N为不开启
 * @apiSuccess {String='Y','N'} d.activity_notice_flg   是否开启活动提醒
 * @apiSuccess {String} d.activity_show_url   活动跳转地址
 * @apiSuccess {object} d.version
 * @apiSuccess {String} d.version.name
 * @apiSuccess {number} d.version.code
 * @apiSuccess {String} d.version.content
 * @apiSuccess {String} d.version.is_force
 * @apiSuccess {String} d.version.download_url
 * @apiSuccess {number} d.version.create_time
 * @apiSuccess {object[]} d.user_level_config    用户等级信息
 * @apiSuccess {number} d.user_level_config.level  等级值
 * @apiSuccess {number} d.user_level_config.color_r  等级背景颜色 R
 * @apiSuccess {number} d.user_level_config.color_g  等级背景颜色 G
 * @apiSuccess {number} d.user_level_config.color_b 等级背景颜色 B
 * @apiSuccess {number} d.apply_anchor_wechat 主播认证联系微信
 * @apiSuccess {number} d.customer_service_mobile 客服电话
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *       {
 *           "c": 0,
 *           "m": "请求成功",
 *           "d": {
 *                   "become_anchor_guide_image": "http://lebolive-1255651273.file.myqcloud.com/static/images/icon_mine_join_yuyin.png",
 *                   "invite_user_guide_image": "http://lebolive-1255651273.file.myqcloud.com/static/images/icon_mine_invite.png",
 *                   "marketAudit": "N",
 *                   "coin": "金币",
 *                   "dot": "佣金",
 *                   "guide_msg_flg": "Y",
 *                   "activity_notice_flg": "Y",
 *                   "activity_show_url": "https://www.baidu.com/",
 *                   "user_say_hi_flg": "Y",
 *                   "version": {
 *                           "name": "v1.7.2",
 *                           "code": "44",
 *                           "content": "ssssss",
 *                           "is_force": "N",
 *                           "download_url": "https:\/\/www.baidu.com\/huanggua",
 *                           "create_time": "1540550198"
 *                       },
 *                     "user_level_config": [{
 *                         "level": "1",
 *                             "color_r": 107,
 *                             "color_g": 255,
 *                             "color_b": 245
 *                         }, {
 *                         "level": "2",
 *                             "color_r": 228,
 *                             "color_g": 140,
 *                             "color_b": 255
 *                         }, {
 *                         "level": "3",
 *                             "color_r": 255,
 *                             "color_g": 157,
 *                             "color_b": 92
 *                     }],
 *                      "apply_anchor_wechat": "haca5487",
 *                      "customer_service_mobile": "13238879702"
 *           },
 *           "t": "1542007936"
 *       }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * indexAction 充值
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/recharge/index
 * @api {get} /user/recharge/index 充值列表
 * @apiName recharge-index
 * @apiGroup Recharge
 * @apiDescription 充值列表
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object} d.user  用户信息
 * @apiSuccess {number} d.user.user_id   用户id
 * @apiSuccess {String} d.user.user_avatar 用户头像
 * @apiSuccess {number} d.user.user_nickname  用户昵称
 * @apiSuccess {number} d.user.user_coin 用户金币
 * @apiSuccess {String} d.user.user_is_first_recharge  用户是否首次充值
 * @apiSuccess {object[]} d.recharge_combo  充值套餐
 * @apiSuccess {number} d.recharge_combo.recharge_combo_id 套餐id
 * @apiSuccess {String} d.recharge_combo.apple_id  苹果商品id
 * @apiSuccess {number} d.recharge_combo.recharge_combo_fee  支付价格
 * @apiSuccess {number} d.recharge_combo.recharge_combo_coin 获得金币
 * @apiSuccess {number} d.recharge_combo.recharge_combo_give_coin  赠送金币（暂无用）
 * @apiSuccess {number} d.recharge_combo.first_reward_vip_day  首充赠送VIP天数
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *       {
 *           "c": 0,
 *           "m": "请求成功",
 *           "d": {
 *                   "user": {
 *                       "user_id": "172",
 *                       "user_avatar": "http://lebolive-1255651273.file.myqcloud.com/avatar.jpg",
 *                       "user_nickname": "18823369189",
 *                       "user_coin": 10,
 *                       "user_is_first_recharge": "Y"
 *                   },
 *                   "recharge_combo": [
 *                   {
 *                       "recharge_combo_id": "23",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "6.00",
 *                       "recharge_combo_coin": "60",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   },
 *                   {
 *                       "recharge_combo_id": "8",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "30.00",
 *                       "recharge_combo_coin": "300",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   },
 *                   {
 *                       "recharge_combo_id": "9",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "118.00",
 *                       "recharge_combo_coin": "1180",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   },
 *                   {
 *                       "recharge_combo_id": "10",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "188.00",
 *                       "recharge_combo_coin": "1880",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   },
 *                   {
 *                       "recharge_combo_id": "11",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "288.00",
 *                       "recharge_combo_coin": "2880",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   },
 *                   {
 *                       "recharge_combo_id": "12",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "998.00",
 *                       "recharge_combo_coin": "9980",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   },
 *                   {
 *                       "recharge_combo_id": "24",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "1380.00",
 *                       "recharge_combo_coin": "13800",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   },
 *                   {
 *                       "recharge_combo_id": "27",
 *                       "apple_id": "",
 *                       "recharge_combo_fee": "2000.00",
 *                       "recharge_combo_coin": "20000",
 *                       "recharge_combo_give_coin": "0",
 *                       "first_reward_vip_day": "0"
 *                   }
 *               ]
 *           },
 *           "t": 1537330776
 *       }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * cardAction 获取用户名片信息
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/card
 * @api {get} /user/profile/card 获取用户名片信息
 * @apiName 用户名片-profile-card
 * @apiGroup Profile
 * @apiDescription 获取用户名片信息
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} user_id 用户id
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} user_id 用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {number} d 数据内容
 * @apiSuccess {number} d.user_id  用户id
 * @apiSuccess {String} d.user_nickname  昵称
 * @apiSuccess {String} d.user_avatar 头像
 * @apiSuccess {number} d.user_sex 性别
 * @apiSuccess {number} d.user_consume_total 消费
 * @apiSuccess {number} d.user_collect_total 收益
 * @apiSuccess {String} d.user_intro 简介
 * @apiSuccess {String} d.user_birth  生日
 * @apiSuccess {number} d.user_follow_total  关注
 * @apiSuccess {number} d.user_fans_total 粉丝
 * @apiSuccess {String} d.user_is_anchor 是否是主播
 * @apiSuccess {number} d.anchor_ranking 主播排行榜
 * @apiSuccess {String} d.is_follow 是否关注
 * @apiSuccess {String} d.chat_room_id 聊天房间id
 * @apiSuccess {String} d.is_card_effect 卡片特效
 * @apiSuccess {number} d.is_online 在线
 * @apiSuccess {String} d.user_img 用户图册
 * @apiSuccess {number} d.user_register_time 用户注册时间
 * @apiSuccess {String} d.user_home_town 家乡
 * @apiSuccess {String} d.user_constellation 用户星座
 * @apiSuccess {String} d.user_hobby 爱好
 * @apiSuccess {String} d.user_profession 用户职业
 * @apiSuccess {String} d.user_emotional_state 用户情感状况
 * @apiSuccess {String} d.user_income 用户收入
 * @apiSuccess {String} d.user_height 用户身高
 * @apiSuccess {String} d.is_black  是否黑名单
 * @apiSuccess {String} d.share_url 分享地址
 * @apiSuccess {object} d.gift_img 礼物图册
 * @apiSuccess {number} d.total_gift 礼物数量
 * @apiSuccess {String} d.user_is_member 是否为会员
 * @apiSuccess {object} d.anchor_info 主播信息
 * @apiSuccess {string} d.anchor_info.anchor_tip 主播标签
 * @apiSuccess {string} d.anchor_info.anchor_character 主播性格
 * @apiSuccess {string} d.anchor_info.anchor_good_topic 主播擅长话题
 * @apiSuccess {string} d.anchor_info.anchor_dress 主播爱穿
 * @apiSuccess {string} d.anchor_info.anchor_stature 主播身材
 * @apiSuccess {string} d.anchor_info.anchor_check_img 主播审核图片
 * @apiSuccess {string} d.anchor_info.anchor_images 主播图片
 * @apiSuccess {string} d.anchor_info.anchor_emotional_state 情感状态
 * @apiSuccess {string} d.anchor_info.anchor_connection_rate 接通率
 * @apiSuccess {string} d.anchor_info.anchor_video 封面视频
 * @apiSuccess {string} d.anchor_info.anchor_video_cover 视频封面
 * @apiSuccess {string} d.anchor_info.anchor_chat_price 通话价格
 * @apiSuccess {string} d.anchor_info.anchor_chat_status 主播聊天状态
 * @apiSuccess {object} d.anchor_info.anchor_guard 主播守护
 * @apiSuccess {string} d.anchor_info.anchor_guard.user_id 守护用户id
 * @apiSuccess {string} d.anchor_info.anchor_guard.user_nickname 守护用户昵称
 * @apiSuccess {string} d.anchor_info.anchor_guard.user_avatar 守护用户头像
 * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_coin 守护金币
 * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_level 守护等级
 * @apiSuccess {string} d.anchor_info.anchor_guard.anchor_guard_level_name 守护等级名称
 * @apiSuccess {object} d.anchor_images_list  图片集
 * @apiSuccess {string} d.anchor_images_list.img_src  图片地址
 * @apiSuccess {string} d.anchor_images_list.visible_type 可见类型  normal vip (vip用户查看时 会变为normal)
 * @apiSuccess {object} d.guard_list  守护中的列表
 * @apiSuccess {number} d.guard_list.user_id    用户id
 * @apiSuccess {String} d.guard_list.user_nickname 用户昵称
 * @apiSuccess {String} d.guard_list.user_avatar 用户头像
 * @apiSuccess {number} d.guard_list.total_coin  用户守护值
 * @apiSuccess {number} d.guard_list.current_level  守护等级
 * @apiSuccess {String} d.guard_list.current_level_name   守护等级名称
 * @apiSuccess {String} d.guard_list.guard_status  守护状态  Y 为守护中， N 为守护过
 * @apiSuccess {number} d.no_income_free_time  剩余不计算收益的免费时长数
 * @apiSuccess {String='guard(守护)'} d.no_income_free_time_type  不计算收益的免费时长类型
 * @apiSuccessExample Success-Response:
 *  {
 *      "c": 0,
 *      "m": "请求成功",
 *      "d": {
 *              "user_id": "171",
 *              "user_nickname": "L--Steven",
 *              "user_avatar": "http://tvax3.sinaimg.cn/crop.0.0.512.512.180/881b51bbly8fs6z7jhuajj20e80e8mxl.jpg",
 *              "user_sex": "2",
 *              "user_consume_total": "2253.00",
 *              "user_collect_total": "3.20",
 *              "user_intro": "",
 *              "user_birth": "",
 *              "user_follow_total": "1",
 *              "user_fans_total": "0",
 *              "user_is_anchor": "Y",
 *              "anchor_ranking": "0",
 *              "is_follow": "N",
 *              "chat_room_id": "172_171",
 *              "is_card_effect": "N",
 *              "is_online": 1,
 *              "user_img": null,
 *              "user_register_time": "1533554656",
 *              "user_home_town": "",
 *              "user_constellation": "",
 *              "user_hobby": "",
 *              "user_profession": "",
 *              "user_emotional_state": "",
 *              "user_income": "",
 *              "user_height": "",
 *              "is_black": false,
 *              "share_url": "http://dev.h5.sxypaopao.com/shareuser?user_id=171",
 *              "gift_img": [],
 *              "total_gift": 0,
 *              "user_is_member": "N",
 *              "anchor_info": {
 *                  "anchor_tip": "",
 *                  "anchor_character": "",
 *                  "anchor_good_topic": "",
 *                  "anchor_dress": "",
 *                  "anchor_check_img": "",
 *                  "anchor_emotional_state": "",
 *                  "anchor_images": "",
 *                  "anchor_stature": ""
 *                  "anchor_connection_rate": "",
 *                  "anchor_video_cover": "",
 *                  "anchor_video": "",
 *                  "anchor_chat_price": "",
 *                  "anchor_chat_status": "",
 *                  "anchor_guard": {
 *                      "user_id": "".
 *                      "user_nickname": "",
 *                      "user_avatar": "",
 *                      "anchor_guard_coin": "",
 *                      "anchor_guard_level": "",
 *                      "anchor_guard_level_name": "",
 *                   }
 *              },
 *              "anchor_images_list": [
 *                  {
 *                      "img_src": "https://lebolive-1255651273.image.myqcloud.com/image/2018/08/23/1534997938083.png",
 *                      "visible_type": "normal"
 *                  },
 *                  {
 *                      "img_src": "https://lebolive-1255651273.image.myqcloud.com/image/2018/10/30/1540865312180.png",
 *                      "visible_type": "normal"
 *                  }
 *              ],
 *              "guard_list": [
 *                  {
 *                      "user_id": "258",
 *                      "user_nickname": "LYXXMY一样",
 *                      "user_avatar": "http://thirdqq.qlogo.cn/qqapp/1106652113/23F1690D0AD8715603EA3D3E1AF30D19/100",
 *                      "total_coin": "370",
 *                      "current_level": "1",
 *                      "current_level_name": "初级守护",
 *                      "guard_status": "Y"
 *                  }
 *              ]
 *      },
 *      "t": 1534924613
 *  }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * 挂断一对一私聊
 * 主播收钱
 *
 * 1. 视频接通10s内挂断，扣取用户金币，但不予主播结算
 * 2. 视频通话如果主播主动关闭，该视频通话最后一分钟，不予结算，但同样扣取用户金币。
 * 若用户主动关闭，不受该逻辑影响
 *
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/hangUpChat
 * @api {get} /live/anchor/hangUpChat 挂断聊天
 * @apiName hangUpChat
 * @apiGroup Chat
 * @apiDescription 挂断聊天
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} chat_log 聊天id
 * @apiParam (正常请求){String='manual(手动),auto(自动)'} hang_up_type 挂断类型
 * @apiParam (正常请求){String} detail 挂断原因
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} chat_log 聊天id
 * @apiParam (debug){String='manual(手动),auto(自动)'} hang_up_type 挂断类型
 * @apiParam (debug){String} detail 挂断原因
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object} d.gift  礼物收益
 * @apiSuccess {number} d.gift.coin  礼物消费金币
 * @apiSuccess {number} d.gift.dot  礼物收益
 * @apiSuccess {object} d.chat_game  聊天游戏收益
 * @apiSuccess {number} d.chat_game.coin  聊天游戏金币
 * @apiSuccess {number} d.chat_game.dot  聊天游戏收益
 * @apiSuccess {object} d.total  总收益
 * @apiSuccess {number} d.total.coin  聊天游戏金币
 * @apiSuccess {number} d.total.dot  聊天游戏收益
 * @apiSuccess {number} d.free_times  使用免费时长
 * @apiSuccess {number} d.no_income_free_time  使用的不计算收益的免费时长数
 * @apiSuccess {String='guard(守护)'} d.no_income_free_time_type  使用的不计算收益的免费时长类型
 * @apiSuccess {number} d.amount  时间收益
 * @apiSuccess {number} d.coin_amount  时间消费
 * @apiSuccess {number} d.duration  时长
 * @apiSuccess {String} d.is_follow  是否关注
 * @apiSuccess {String} d.snatch_status  抢聊状态  Y 成功，N 失败，C 等待中
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "c": 0,
 *          "m": "请求成功",
 *          "d": {
 *                  "gift": {
 *                      "coin": "30",
 *                      "dot": "1.2"
 *              },
 *                  "chat_game": {
 *                      "coin": "30",
 *                      "dot": "1.2"
 *              },
 *                  "total": {
 *                      "coin": "30",
 *                      "dot": "1.2"
 *              },
 *              "free_times": "10",
 *              "amount": "0.8000",
 *              "coin_amount": 20,
 *              "duration": 50,
 *              "is_follow": "Y",
 *              "snatch_status": "Y"
 *          },
 *          "t": 1536662604
 *      }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 * */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/chatgame/anchor
 * @api {get} /live/chatgame/anchor 主播选择的游戏
 * @apiName chatgame-anchor
 * @apiGroup Chatgame
 * @apiDescription 主播选择的游戏
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} anchor_user_id 主播用户id
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} anchor_user_id 主播用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.list
 * @apiSuccess {number} d.list.game_id   游戏id
 * @apiSuccess {String} d.list.chat_game_content  游戏内容
 * @apiSuccess {number} d.list.chat_game_price  价格
 * @apiSuccess {number} d.list.chat_game_category_id  分类id
 * @apiSuccess {String} d.list.category_name  分类名称
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *      {
 *          "c": 0,
 *          "m": "请求成功",
 *          "d": {
 *                  "list": [{
 *                      "game_id": "1",
 *                      "chat_game_content": "今天几点起床？",
 *                      "chat_game_price": "100",
 *                      "chat_game_category_id": "1",
 *                      "category_name": "真心话"
 *              }]
 *          },
 *          "t": "1545817549"
 *      }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/live/anchor/getPrivateChat
 * @api {get} /live/anchor/getPrivateChat 首页主播
 * @apiName getPrivateChat
 * @apiGroup Index
 * @apiDescription 首页主播  审核状态中，不展示离线的用户  添加主播标签搜索 年龄搜索
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String} local  城市
 * @apiParam (正常请求) {String="index(首页)","all(所有)","hot(热门)","follow(关注)","new(新人)","guide(诱导)","nearby(附近)"} type  类型
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String} local  城市
 * @apiParam (debug) {String="index(首页)","all(所有)","hot(热门)","follow(关注)","new(新人)","guide(诱导)","nearby(附近)"} type  类型
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.items  内容
 * @apiSuccess {String} d.items.user_nickname  昵称
 * @apiSuccess {String} d.items.user_avatar  头像
 * @apiSuccess {String} d.items.user_level  等级
 * @apiSuccess {String} d.items.user_intro  简介
 * @apiSuccess {String} d.items.user_profession  职业
 * @apiSuccess {number} d.items.anchor_chat_status  聊天状态
 * @apiSuccess {number} d.items.user_id  用户id
 * @apiSuccess {number} d.items.user_sex 性别
 * @apiSuccess {number} d.items.user_birth 生日
 * @apiSuccess {String} d.items.user_video_cover  视频封面
 * @apiSuccess {String} d.items.user_video   视频地址
 * @apiSuccess {number} d.items.is_follow  是否关注  0为 未关注 其他为已关注
 * @apiSuccess {String} d.items.anchor_tip
 * @apiSuccess {String} d.items.anchor_character
 * @apiSuccess {String} d.items.anchor_good_topic
 * @apiSuccess {String} d.items.anchor_dress
 * @apiSuccess {String} d.items.anchor_stature
 * @apiSuccess {String} d.items.anchor_emotional_state
 * @apiSuccess {number} d.items.price
 * @apiSuccess {String} d.items.anchor_local
 * @apiSuccess {String} d.items.anchor_title
 * @apiSuccess {String} d.items.share_url
 * @apiSuccess {String} d.items.anchor_guard_id  主播守护id
 * @apiSuccess {String} d.items.today_guard_free_times  当天守护剩余可免费聊天时长
 * @apiSuccess {number} d.page
 * @apiSuccess {number} d.pagesize
 * @apiSuccess {number} d.pagetotal
 * @apiSuccess {number} d.total
 * @apiSuccess {number} d.prev
 * @apiSuccess {number} d.next
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *         "m": "请求成功",
 *         "d": {
 *             "items": [{
 *                 "user_nickname": "118啦咯",
 *                 "user_avatar": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/09\/1533807443523.png",
 *                 "user_level": "1",
 *                 "user_intro": "徒孙气质红哦你好给力了",
 *                 "user_profession": "工程师",
 *                 "anchor_chat_status": "3",
 *                 "user_id": "166",
 *                 "user_sex": "2",
 *                 "anchor_guard_id": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533694327924.png"
 *         }, {
 *                 "user_nickname": "泡泡08271736169",
 *                 "user_avatar": "http:\/\/lebolive-1255651273.file.myqcloud.com\/avatar.jpg",
 *                 "user_level": "1",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "1",
 *                 "user_id": "230",
 *                 "user_sex": "2",
 *                 "anchor_guard_id": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/27\/1970dd6937d27862b745642fabbc6af5"
 *         }, {
 *                 "user_nickname": "神秘",
 *                 "user_avatar": "http:\/\/thirdwx.qlogo.cn\/mmopen\/vi_32\/Q0j4TwGTfTKkYqhRB1yzqQsZh7DwA0IvFAX2Ks9DCFcdXn5KUrquT2qpouY7k1qbfTpmDBiaxIz37Zaic4rKFqHg\/132",
 *                 "user_level": "1",
 *                 "user_intro": "",
 *                 "user_profession": "",
 *                 "anchor_chat_status": "0",
 *                 "user_id": "170",
 *                 "user_sex": "1",
 *                 "anchor_guard_id": "2",
 *                 "user_video_cover": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/06\/1533540159917.png"
 *         }],
 *         "page": 1,
 *         "pagesize": 50,
 *         "pagetotal": 1,
 *         "total": 3,
 *         "prev": 1,
 *         "next": 1
 *         },
 *         "t": 1535600607
 *     }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/message/notify
 * @api {get} /user/message/notify 通知消息列表
 * @apiName message-notify
 * @apiGroup Message
 * @apiDescription 通知消息列表
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
 * @apiSuccess {object} d.system_dialog    系统消息最新一条
 * @apiSuccess {String} d.system_dialog.content   内容
 * @apiSuccess {number} d.system_dialog.unread    未读数
 * @apiSuccess {number} d.system_dialog.time        时间
 * @apiSuccess {String} d.system_dialog.type    类型
 * @apiSuccess {object} d.short_video   小视频最新消息
 * @apiSuccess {number} d.short_video.num   未读数
 * @apiSuccess {number} d.short_video.time  最新时间
 * @apiSuccess {String} d.short_video.msg   消息内容
 * @apiSuccess {String} d.short_video.type  类型
 * @apiSuccess {object} d.short_posts    动态最新消息
 * @apiSuccess {number} d.short_posts.unread   未读数
 * @apiSuccess {String} d.short_posts.last_message_type  消息类型
 * @apiSuccess {String} d.short_posts.last_message_content  消息短内容
 * @apiSuccess {number} d.short_posts.last_message_time  时间戳
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *       {
 *           "c": 0,
 *           "m": "请求成功",
 *           "d": {
 *                   "system_dialog": {
 *                       "content": "哈哈哈哈哈哈哈哈关注了你",
 *                       "unread": "2",
 *                       "time": "1551766120",
 *                   },
 *                   "short_video": {
 *                           "num": "0",
 *                           "time": "0",
 *                           "msg": "",
 *                           "type": ""
 *                   },
 *                   "short_posts": {
 *                           "unread": "14",
 *                           "last_message_type": "comment",
 *                           "last_message_content": "您的动态收到一条评论",
 *                           "last_message_time": "1551940670"
 *                   }
 *           },
 *           "t": "1553077305"
 *       }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * dialogAction 对话详情
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/chat/dialog
 * @api {get} /user/chat/dialog 对话详情
 * @apiName chat-dialog
 * @apiGroup Chat
 * @apiDescription  对话详情
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (正常请求){String}  chat_room_id 聊天房间id
 * @apiParam (正常请求){String}  dialog_id 最后一个id
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiParam (debug){String}  chat_room_id 聊天房间id
 * @apiParam (debug){String}  dialog_id 最后一个id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {object[]} d.user_dialog
 * @apiSuccess {object} d.user_dialog.from_user
 * @apiSuccess {number} d.user_dialog.from_user.user_id
 * @apiSuccess {String} d.user_dialog.from_user.user_nickname
 * @apiSuccess {number} d.user_dialog.from_user.user_level
 * @apiSuccess {String='normal(普通)','once(阅后即焚)','destroy(已焚毁)'} d.user_dialog.from_user.send_read_type
 * @apiSuccess {String} d.user_dialog.from_user.user_avatar
 * @apiSuccess {String} d.user_dialog.from_user.user_is_member
 * @apiSuccess {object} d.user_dialog.to_user
 * @apiSuccess {number} d.user_dialog.to_user.user_id
 * @apiSuccess {String} d.user_dialog.to_user.user_nickname
 * @apiSuccess {number} d.user_dialog.to_user.user_level
 * @apiSuccess {String='normal(普通)','once(阅后即焚)','destroy(已焚毁)'} d.user_dialog.to_user.send_read_type
 * @apiSuccess {String} d.user_dialog.to_user.user_avatar
 * @apiSuccess {String} d.user_dialog.to_user.user_is_member
 * @apiSuccess {String} d.user_dialog.msg
 * @apiSuccess {number} d.user_dialog.time
 * @apiSuccess {number} d.user_dialog.dialog_id
 * @apiSuccess {String} d.user_dialog.type
 * @apiSuccess {String} d.user_dialog.source_url
 * @apiSuccess {String} d.user_dialog.extra
 * @apiSuccess {number} d.user_dialog.income
 * @apiSuccess {number} d.user_dialog.pay_coin
 * @apiSuccess {number} d.user_dialog.video_chat_status
 * @apiSuccess {number} d.user_dialog.video_chat_duration
 * @apiSuccess {String} d.user_dialog.video_chat_has_callback
 * @apiSuccess {object} d.user
 * @apiSuccess {String} d.user.is_follow
 * @apiSuccess {String} d.user.has_reply
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "c": 0,
 *         "m": "请求成功",
 *         "d": {
 *                 "user_dialog": [{
 *                     "from_user": {
 *                         "user_id": "318",
 *                         "user_nickname": "渐入佳境",
 *                         "user_level": "1",
 *                         "send_read_type": "noce",
 *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1107915107\/63F3F098E6FAC4B5C210CA2458C66BE6\/100",
 *                         "user_is_member": "N"
 *                 },
 *                 "to_user": {
 *                         "user_id": "320",
 *                         "user_nickname": "—",
 *                         "user_level": "1",
 *                         "send_read_type": "noce",
 *                         "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/4D6E1DCB3B3823B4E4075970C929E3DC\/100",
 *                         "user_is_member": "N"
 *                 },
 *                 "msg": "对方邀请您视频聊天",
 *                 "time": "1543819438",
 *                 "dialog_id": "2858",
 *                 "type": "video_chat",
 *                 "source_url": "",
 *                 "extra": "a:2:{s:17:\"video_chat_status\";s:6:\"1\";s:19:\"video_chat_duration\";i:0;}",
 *                 "income": "0.00",
 *                 "pay_coin": "0.00",
 *                 "video_chat_status": "1",
 *                 "video_chat_duration": "0",
 *                 "video_chat_has_callback": "Y"
 *             }],
 *             "user": {
 *                     "is_follow": "N",
 *                     "has_reply": true
 *             }
 *         },
 *         "t": "1543980263"
 *     }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */
/**
 * @apiVersion 1.3.0
 * @apiSampleRequest http://dev.api.sxypaopao.com/v1/user/profile/level
 * @api {get} /user/profile/level 用户等级
 * @apiName profile-level
 * @apiGroup Profile
 * @apiDescription 用户等级
 * @apiParam (正常请求){String} access_token  token值
 * @apiParam (debug) {String} debug  debug
 * @apiParam (debug) {String} cli_api_key  debug
 * @apiParam (debug) {String} uid  用户id
 * @apiSuccess {number} c 返回码
 * @apiSuccess {string} m 返回文字信息
 * @apiSuccess {number} t 服务器当前时间戳
 * @apiSuccess {Object} d 内容
 * @apiSuccess {number} d.user_id   用户id
 * @apiSuccess {String} d.rank_hide   排行榜是否隐藏
 * @apiSuccess {String} d.user_avatar   用户头像
 * @apiSuccess {String} d.user_nickname   用户昵称
 * @apiSuccess {number} d.user_level  用户等级
 * @apiSuccess {number} d.user_exp  用户经验
 * @apiSuccess {number} d.this_level_exp  当前等级的经验
 * @apiSuccess {number} d.next_level_exp   下一等级所需经验
 * @apiSuccess {number} d.high_level_user_online   上线通知 所需等级
 * @apiSuccess {number} d.hide_rank_level   开启排行榜隐藏功能的等级
 * @apiSuccess {number='Y','N'} d.has_old_exp   是否有经验可以领取
 * @apiSuccess {object[]} d.rule
 * @apiSuccess {String} d.rule.content  规则内容
 * @apiSuccess {object[]} d.icon_rule   带标签规则显示
 * @apiSuccess {String} d.icon_rule.icon   图标
 * @apiSuccess {String} d.icon_rule.title   标题
 * @apiSuccess {String} d.icon_rule.detail  详情
 * @apiSuccess {object[]} d.level_reward   等级奖励列表
 * @apiSuccess {String} d.level_reward.level_value   等级值
 * @apiSuccess {String} d.level_reward.reward_coin   奖励金币数
 * @apiSuccess {String='Y(已经领取)','C(等待领取)','N(不能领取)'} d.level_reward.reward_flg  领取状态
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *         {
 *             "c": 0,
 *             "m": "请求成功",
 *             "d": {
 *                     "user_id": "311",
 *                     "rank_hide": "Y",
 *                     "user_avatar": "https://lebolive-1255651273.image.myqcloud.com/image/2018/12/07/1544150678158.png",
 *                     "user_level": "4",
 *                     "user_nickname": "11111",
 *                     "user_exp": 568990,
 *                     "this_level_exp": 24000,
 *                     "next_level_exp": 18880,
 *                     "high_level_user_online": "4",
 *                     "hide_rank_level": "3",
 *                     "has_old_exp": "Y",
 *                     "rule": [
 *                         {
 *                             "content": "每消耗1金币即可获得10金币"
 *                         },
 *                         {
 *                             "content": "每日签到任务可获得经验值"
 *                         }
 *                     ],
 *                     "icon_rule": [{
 *                         "icon": "http:\/\/youbo-1252571077.coscd.myqcloud.com\/gift\/cheers.png",
 *                             "title": "与小姐姐视频通话",
 *                             "detail": "提升等级最快方式"
 *                         }, {
 *                         "icon": "http:\/\/youbo-1252571077.coscd.myqcloud.com\/gift\/love_beauty.png",
 *                             "title": "赠送礼物",
 *                             "detail": "可获得更多经验值"
 *                         }, {
 *                         "icon": "http:\/\/static.greenlive.1booker.com\/upload\/image\/20171122\/1511316322286418.png",
 *                             "title": "成为会员",
 *                             "detail": "会员期间享受经验值200%加速"
 *                         }, {
 *                         "icon": "http:\/\/youbo-1252571077.coscd.myqcloud.com\/gift\/crystal_shoes.png",
 *                             "title": "日常签到",
 *                             "detail": "每天签到可获得经验值，还有金币奖励哦"
 *                         }],
 *                         "level_reward": [
 *                             {
 *                                 "level_value": "5",
 *                                 "reward_coin": "10",
 *                                 "reward_flg": "Y"
 *                             },
 *                             {
 *                                 "level_value": "10",
 *                                 "reward_coin": "20",
 *                                 "reward_flg": "C"
 *                             },
 *                             {
 *                                 "level_value": "15",
 *                                 "reward_coin": "30",
 *                                 "reward_flg": "N"
 *                             }
 *                         ]
 *             },
 *             "t": "1545982324"
 *         }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */






