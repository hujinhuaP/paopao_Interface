<?php
/**
 * @apiDefine Certification 实名认证
 */
/**
 * @apiDefine Profile 个人信息
 */
/**
 * @apiDefine Chat 聊天
 */
/**
 * @apiDefine VideoChat 视频聊天
 */
/**
 * @apiDefine Index 首页
 */
/**
 * @apiDefine Guard 守护
 */
/**
 * @apiDefine Chatgame 聊天游戏
 */
/**
 * @apiDefine Notify 通知
 */
/**
 * @apiDefine ShortPosts 动态
 */
/**
 * @apiDefine Public 公共
 */
/**
 * @apiDefine Message 消息
 */
/**
 * @apiDefine Room 聊天室
 */


/**
 * @apiDefine EnterRoomTemplate
 * @apiSuccess {object} d.room_owner_info  房主信息
 * @apiSuccess {number} d.room_owner_info.user_id    用户id
 * @apiSuccess {number} d.room_owner_info.user_number    用户编号
 * @apiSuccess {String} d.room_owner_info.user_nickname   用户昵称
 * @apiSuccess {String} d.room_owner_info.user_avatar   用户头像
 * @apiSuccess {number} d.room_owner_info.user_avatar_frame   用户头像框
 * @apiSuccess {String} d.room_owner_info.user_birth  用户生日
 * @apiSuccess {String} d.room_owner_info.user_gender  用户性别
 * @apiSuccess {number} d.room_owner_info.user_rich_level  用户财富等级
 * @apiSuccess {number} d.room_owner_info.user_charm_level  用户魅力等级
 * @apiSuccess {String='Y','N'} d.room_owner_info.user_on_room_flg  房主是否在房间
 * @apiSuccess {number} d.room_id  房间id
 * @apiSuccess {String} d.room_name  房间名称
 * @apiSuccess {number} d.room_number  房间编号
 * @apiSuccess {number} d.room_online_count  房间在线人数
 * @apiSuccess {number} d.room_background  房间背景
 * @apiSuccess {number} d.room_category 房间类型
 * @apiSuccess {number} d.room_auth_flg 房间是否加密
 * @apiSuccess {number} d.room_heart_stat_open_flg 房间是否开启甜心值
 * @apiSuccess {String} d.room_live_model  房间排麦模式
 * @apiSuccess {String} d.room_notice_word  房间公告
 * @apiSuccess {String} d.room_notice_title  房间标题
 * @apiSuccess {String} d.room_welcome_word  房间欢迎语
 * @apiSuccess {String} d.room_welcome_word  房间欢迎语
 * @apiSuccess {object[]} d.room_seat   座位信息（从主持到8号麦序 最多9个 按照room_seat_number排序）
 * @apiSuccess {number} d.room_seat.user_id  用户id
 * @apiSuccess {String} d.room_seat.user_nickname  用户昵称
 * @apiSuccess {String} d.room_seat.user_avatar  用户头像
 * @apiSuccess {number} d.room_seat.user_avatar_frame  用户头像框
 * @apiSuccess {number} d.room_seat.room_seat_number  麦序  0为主持位  其他按顺序排位
 * @apiSuccess {number} d.room_seat.room_seat_voice_flg  是否开了声音
 * @apiSuccess {number} d.room_seat.room_seat_open_flg  是否开启了
 * @apiSuccess {number} d.room_seat.room_seat_count_down_duration  倒计时时长秒
 * @apiSuccess {number} d.room_seat.room_seat_heart_value  甜心值
 * @apiSuccess {number} d.room_seat.room_seat_like_number  选择的心动用户
 * @apiSuccess {number} d.user_enter_room_scroll_rich_level  用户进入房间有飘屏的财富等级
 * @apiSuccess {number} d.user_enter_room_scroll_charm_level  用户进入房间有飘屏的魅力等级
 * @apiSuccess {number="0",'1','2'} d.room_step  房间当前步骤
 * @apiSuccess {number} d.room_step_duration  心动选择剩余时间
 * @apiSuccess {Object} d.room_team_battles_value  团战值
 * @apiSuccess {number} d.room_team_battles_value.red_value  红队值
 * @apiSuccess {number} d.room_team_battles_value.blue_value  蓝队值
 * @apiSuccess {Object} d.room_friend_hat_info  相亲帽子信息
 * @apiSuccess {number} d.room_friend_hat_info.male_hat_seat_number  男方帽子座位序号
 * @apiSuccess {number} d.room_friend_hat_info.male_hat_flg  男方帽子等级
 * @apiSuccess {number} d.room_friend_hat_info.female_hat_seat_number  女方帽子座位序号
 * @apiSuccess {number} d.room_friend_hat_info.female_hat_flg  女方帽子等级
 * @apiSuccess {number} d.room_total_rich  房间总贡献值
 * @apiSuccess {number} d.room_seat_wait_count  上麦排队人数
 * @apiSuccess {number} d.room_seat_wait_count  上麦排队人数
 * @apiSuccess {String} d.collect_flg  是否收藏
 * @apiSuccess {String} d.wait_flg  是否在等待位
 * @apiSuccess {String} d.room_total_radio_guard  房间总电台守护人数
 * @apiSuccess {String='N(普通用户)','S(超级管理员)'} d.user_group_flg  用户分组表示
 * @apiSuccess {String='normal(普通用户)','super_admin(超级管理员)','owner(房主)','admin(管理员)'} d.user_role_flg  房间角色
 * @apiSuccess {String='(普通用户)','gold(黄金守护)','silver(白银守护)','bronze(青铜守护)'} d.user_radio_guard_top_flg  最高守护等级
 * @apiSuccess {Object} d.fast_chat  快速回复
 * @apiSuccess {String} d.fast_chat.fast_chat_content  快速回复内容
 * @apiSuccess {String} d.agora_token  声网token
 * @apiSuccess {String} d.egg_open_flg  砸蛋是否开启
 * @apiSuccess {object[]} d.count_down_list   倒计时列表
 * @apiSuccess {number} d.count_down_list.duration  时长秒
 * @apiSuccess {String} d.count_down_list.title  显示标题
 * @apiSuccess {object[]} d.user  用户信息
 * @apiSuccess {number} d.user.user_mount  用户座驾ID
 * @apiSuccess {String} d.user.user_mount_source  用户座驾资源
 * @apiSuccess {String} d.user.user_mount_name  用户座驾名称
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *   {
 *    "c": 0,
 *    "m": "请求成功",
 *    "d": {
 *        "room_owner_info": {
 *            "user_id": "13",
 *            "user_number": "1311111",
 *            "user_nickname": "绥化的阿施",
 *            "user_avatar": "",
 *            "user_avatar_frame": "0",
 *            "user_birth": "",
 *            "user_gender": "unset",
 *            "user_rich_level": "1",
 *            "user_charm_level": "1",
 *            "user_on_room_flg": "Y"
 *        },
 *        "room_id": "1",
 *        "room_name": "我的小房间",
 *        "room_number": "22011581",
 *        "room_online_count": "0",
 *        "room_background": "1",
 *        "room_category": "normal",
 *        "room_live_model": "auth",
 *        "room_notice_word": "",
 *        "room_notice_title": "",
 *        "room_welcome_word": "",
 *        "room_seat": [{
 *            "user_id": "13",
 *            "user_nickname": "绥化的阿施",
 *            "user_avatar": "",
 *            "user_avatar_frame": "0",
 *            "room_seat_number": "0",
 *            "room_seat_voice_flg": "Y",
 *        }],
 *        "room_team_battles_value" : {
 *              "red_value": 100,
 *              "blue_value": 100,
 *        }
 *        "user_enter_room_scroll_rich_level": "100",
 *        "user_enter_room_scroll_charm_level": "100",
 *        "room_total_coin": "10",
 *        "room_total_rich": "100",
 *        "collect_flg": "N",
 *        "wait_flg": "N",
 *        "user_group_flg": "N",
 *        "fast_chat": [{
 *            "fast_chat_content": "绥化的阿施"
 *        }],
 *        "system_notice" : "官方公告",
 *        "share": {
 *            "title": "绥化的阿施",
 *            "content": "绥化的阿施",
 *            "image": "",
 *            "url": "https://www.baidu.com",
 *        },
 *        "agora_token" : "sdfasfswfsdfsfsdf",
 *        "count_down_list": [{
 *            "duration": 30,
 *            "title": "30秒",
 *        }],
 *    },
 *    "t": "1558010433"
 *   }
 * @apiError UserNotFound The id of the User was not found.
 * @apiErrorExample Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 */