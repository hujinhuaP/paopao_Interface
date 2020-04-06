<?php

/**
 * @apiVersion 1.3.0
 * @apiName notify-scroll_msg
 * @api {get} / 001-190904滚屏推送
 * @apiGroup Notify
 * @apiDescription  高等级用户上线滚屏推送
 * @apiSuccess {number} c 返回码
 * @apiSuccess {object} data 数据
 * @apiSuccess {String='high_level_user_online(高等级用户上线)','start_snatch(开始抢聊)','new_guard(新守护)','guard_level_up(守护等级提升)','notice_gift(赠送通知礼物)','recharge_vip(充值VIP)'} data.type 类型
 * @apiSuccess {object} data.info
 * @apiSuccess {String} data.info.user_nickname 用户昵称
 * @apiSuccess {String} data.info.user_avatar 用户头像
 * @apiSuccess {String} data.info.user_level 用户等级
 * @apiSuccess {String} data.info.anchor_user_nickname 主播昵称
 * @apiSuccess {String} data.info.anchor_user_avatar 主播头像
 * @apiSuccess {String} data.info.anchor_user_level 主播等级
 * @apiSuccess {String} data.info.gift_logo 礼物LOGO
 * @apiSuccess {String} data.info.gift_name 礼物名称
 * @apiSuccess {String} data.info.gift_id 礼物ID
 * @apiSuccess {String} data.info.title 标题
 * @apiSuccess {String} data.info.content 内容
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "scroll_msg",
 *        "msg": "飘屏消息",
 *        "data": {
 *                "type": "high_level_user_online",
 *                "info": {
 *                    "user_nickname": "1181732245amxij11151741020",
 *                    "user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
 *                    "user_level": "7",
 *                    "anchor_user_nickname": "1181732245amxij11151741020",
 *                    "anchor_user_avatar": "http://tvax4.sinaimg.cn/crop.0.0.40.40.180/007dqVi7ly8ftm2u9xgx9j3014014a9t.jpg",
 *                    "anchor_user_level": "7",
 *                    "title": "等级特权用户",
 *                    "content": "Steven09131112487 上线啦，美女们赶紧去陪一下"
 *            }
 *        }
 *    }
 */

/**
 * @apiVersion 1.3.0
 * @apiName video_chat_pay_success
 * @api {get} / 聊天付费成功
 * @apiGroup Notify
 * @apiDescription  聊天付费成功推送
 * @apiSuccess {number} c 返回码
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "video_chat_pay_success",
 *        "msg": "支付成功",
 *        "data": {
 *              "user_coin": "100",    // 剩余金币
 *              "guard_free_times": "10"  //剩余守护免费通话时长
 *        }
 *    }
 */
/**
 * @apiVersion 1.3.0
 * @apiName notify-send_chat
 * @api {get} / 私信
 * @apiGroup Notify
 * @apiDescription  私信推送
 * @apiSuccess {String} type 类型
 * @apiSuccess {String} msg 消息
 * @apiSuccess {Object} data 内容
 * @apiSuccess {Object} data.dialog 对话
 * @apiSuccess {String} data.dialog.is_say_hi 是否需要打招呼类（自身没有回复过该用户）
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "send_chat",
 *        "msg": "私信",
 *        "data": {
 *                       "pushContent": "https:\/\/lebolive-1255651273.image.myqcloud.com\/image\/2018\/08\/08\/1533700478894.png",
 *                       "chat_room_id": "251_168",
 *                       "dialog": {
 *                               "from_user": {
 *                                   "user_id": "168",
 *                                   "user_nickname": "啦啦啦",
 *                                   "user_level": "1",
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
 *                               "pay_coin": 0,
 *                               "is_say_hi": "Y",
 *                               "to_user": {
 *                                   "user_id": "251",
 *                                   "user_nickname": "Dawn09101048222",
 *                                   "user_level": "1",
 *                                   "user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1106652113\/9012BAEA9B36E6AE8846D0EFE9C05A13\/100",
 *                                   "user_is_member": "Y"
 *                               }
 *                       },
 *                       "guide_id": '0'
 *        }
 *    }
 */


/**
 * @apiVersion 1.3.0
 * @apiName notify-privateinvite_chat_game
 * @api {get} / 邀请聊天游戏通知
 * @apiGroup Notify
 * @apiDescription  邀请聊天游戏通知
 * @apiSuccess {String} type 类型
 * @apiSuccess {String} msg 消息
 * @apiSuccess {Object} data 内容
 * @apiSuccess {String} data.game_id 游戏ID
 * @apiSuccess {String} data.chat_log 聊天ID
 * @apiSuccess {String} data.chat_game_content 游戏内容
 * @apiSuccess {String} data.chat_game_price 游戏价格
 * @apiSuccess {String} data.chat_game_category_name 游戏类型名
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "invite_chat_game",
 *        "msg": "邀请聊天游戏",
 *        "data": {
 *                "game_id": "10",
 *                "chat_log": "4493"
 *                "chat_game_content": "内容"
 *                "chat_game_price": "100"
 *                "chat_game_category_name": "大冒险"
 *        }
 *    }
 */

/**
 * @apiVersion 1.3.0
 * @apiName notify-close_chat_status
 * @api {get} / 关闭聊天状态通知
 * @apiGroup Notify
 * @apiDescription  关闭聊天状态通知  此处有极光推送  extra 中 type  close_chat_status
 * @apiSuccess {String} type 类型
 * @apiSuccess {String} msg 消息
 * @apiSuccess {Object} data 内容
 * @apiSuccess {String} data.content 提示内容
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "close_chat_status",
 *        "msg": "邀请聊天游戏",
 *        "data": {
 *                "content": "您连续未接听10个用户拨打的视频通话，系统自动为您设置成“忙碌”状态，若需接单请前往重新开启"
 *        }
 *    }
 */

/**
 * @apiVersion 1.3.0
 * @apiName notify-notification_msg
 * @api {get} / 发送系统消息
 * @apiGroup Notify
 * @apiDescription  发送系统消息
 * @apiSuccess {String} type 类型
 * @apiSuccess {String} msg 消息
 * @apiSuccess {Object} data 内容
 * @apiSuccess {String} data.content 提示内容
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "notification_msg",
 *        "msg": "系统公告",
 *        "data": {
 *                "content": "您好"
 *        }
 *    }
 */


/**
 * @apiVersion 1.3.0
 * @apiName notify-intimate_level_up
 * @api {get} / 亲密度等级提升
 * @apiGroup Notify
 * @apiDescription  邀请通话
 * @apiSuccess {String} type 类型
 * @apiSuccess {String} msg 消息
 * @apiSuccess {Object} data 内容
 * @apiSuccess {Number} data.intimate_user_id 用户id
 * @apiSuccess {Number} data.intimate_anchor_user_id 主播用户id
 * @apiSuccess {Number} data.intimate_level 等级
 * @apiSuccess {String} data.intimate_level_name 等级名称
 * @apiSuccess {Number} data.intimate_value 值
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "intimate_level_up",
 *        "msg": "私聊消息",
 *        "data": {
 *                       "intimate_user_id": "100",
 *                       "intimate_anchor_user_id": "168",
 *                       "intimate_level": "1",
 *                       "intimate_level_name": "sssssss",
 *                       "intimate_value": "10000",
 *        }
 *    }
 */



/**
 * @apiVersion 1.2.0
 * @apiName notify-private_chat
 * @api {get} / 001-190819邀请通话
 * @apiGroup Notify
 * @apiDescription  邀请通话
 * @apiSuccess {String} type 类型
 * @apiSuccess {String} msg 消息
 * @apiSuccess {Object} data 内容
 * @apiSuccess {String} data.price 价格
 * @apiSuccess {String} data.is_free_match_flg 是否是免费匹配时长
 * @apiSuccess {String} data.free_match_over_time 超时时间
 * @apiSuccessExample Success-Response:
 * {
 *        "type": "private_chat",
 *        "msg": "私聊消息",
 *        "data": {
 *                "no_income_free_time": "10",   // 剩余的不计算收益的免费时长数
 *                "no_income_free_time_type": "guard",   // 剩余的不计算收益的免费时长类型 guard ：守护
 *                "is_user_call": "Y",
 *                "chat_log": "4493",
 *                "f_user_id": "318",
 *                "f_user_nickname": "渐入佳境",
 *                "f_user_avatar": "http:\/\/thirdqq.qlogo.cn\/qqapp\/1107915107\/63F3F098E6FAC4B5C210CA2458C66BE6\/100",
 *                "f_user_level": "1",
 *                "anchor_video_url": "https:\/\/lebolive-1255651273.image.myqcloud.com\/video\/2018\/11\/27\/output-2018-11-27-17:45:40-643.mp4",
 *                "price": "50",
 *                "is_free_match_flg": "Y",
 *                "free_match_over_time": "20",
 *                "play_rtmp": "rtmp:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
 *                "play_flv": "http:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2.flv?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
 *                "play_m3u8": "http:\/\/tencent.play.sxypaopao.com\/live\/34574_yuyin_318_4493_2.m3u8?bizid=34574&txSecret=eee9e2bd39ffd77590adb7d1b9bf600d&txTime=5C07F5FF",
 *                "wangsu": {
 *                    "push_url": ""
 *                }
 *        }
 *    }
 **/


/**
 * @apiVersion 1.4.0
 * @apiName notify-room_start_video_chat
 * @api {get} / 从房间带去1V1视频
 * @apiGroup Notify
 * @apiDescription  邀请通话
 * @apiSuccess {String} type 类型
 * @apiSuccess {String} msg 消息
 * @apiSuccess {Object} data 内容
 * @apiSuccess {Number} data.room_id 房间ID
 * @apiSuccess {Number} data.anchor_user_id 主播用户ID
 * @apiSuccess {Number} data.anchor_user_nickname 主播昵称
 * @apiSuccess {Number} data.user_id 用户ID
 * @apiSuccess {Number} data.user_nickname 用户昵称
 * @apiSuccessExample Success-Response:
 *    {
 *        "type": "room_start_video_chat",
 *        "msg": "私聊消息",
 *        "data": {
 *                       "room_id": "100",
 *                       "anchor_user_id": "168",
 *                       "anchor_user_nickname": "1",
 *                       "user_id": "1687",
 *                       "user_nickname": "10000",
 *        }
 *    }
 */
