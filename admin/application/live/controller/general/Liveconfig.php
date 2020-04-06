<?php 
namespace app\live\controller\general;

use app\common\controller\Backend;
use think\Exception;
use app\live\model\live\Kv;

/**
 * 直播配置
 *
 * @icon fa fa-circle-o
 */
class Liveconfig extends Backend
{
	/**
	 * edit 编辑
	 */
	public function edit($ids = NULL)
	{
		$aKey = [
			// 直播房间公告
		    Kv::KEY_NOTICE_ANCHOR_ROOM,
		    // 用户房间公告
		    Kv::KEY_NOTICE_USER_ROOM,
		    // 主播时长收益比例
			Kv::COIN_TO_DOT_RATIO_TIME,
            // 主播礼物收益比例
            Kv::COIN_TO_DOT_RATIO_GIFT,
            // 是否开启邀请注册奖励
            Kv::INVITE_REGISTER_FLG,
            // 邀请注册奖励金币数
            Kv::INVITE_REGISTER_COIN,
            // 是否开启邀请充值奖励
            Kv::INVITE_RECHARGE_FLG,
            // 邀请充值奖励比例
            Kv::INVITE_RECHARGE_RADIO,
            // 是否开启注册奖励
            Kv::REGISTER_REWARD_FLG,
            // 注册奖励金币数
            Kv::REGISTER_REWARD_COIN,
            // 视频收益比例
            Kv::COIN_TO_DOT_RATIO_VIDEO,
            // 聊天收益比例
            Kv::COIN_TO_DOT_RATIO_CHAT,
            //是否开启主播邀请充值奖励  1为开启
            Kv::INVITE_ANCHOR_RECHARGE_FLG,
            //主播邀请充值奖励比例
            Kv::INVITE_ANCHOR_RECHARGE_RADIO,
            // 匹配价格
            Kv::CHAT_MATCH_PRICE,
            //主播邀请充值奖励比例
            Kv::INVITE_ANCHOR_FIRST_RECHARGE_RADIO,
            // 注册赠送免费匹配时长
            Kv::REGISTER_FREE_MATCH_TIMES,
            // 邀请注册奖励"现金"
            Kv::INVITE_REGISTER_CASH,
            // 邀请用户充值奖励"现金"比例
            Kv::INVITE_RECHARGE_RADIO_CASH,
            // 邀请用户购买VIP奖励"现金"比例
            Kv::INVITE_VIP_RADIO_CASH,
            // 用户邀请主播提现奖励"现金"比例
            Kv::INVITE_WITHDRAW_RADIO_CASH,
            // 用户匹配单个主播间隔时间
            Kv::USER_MATCH_SINGLE_ANCHOR_INTERVAL,
            // 诱导用户开启
            Kv::GUIDE_MSG_FLG,
            //分享获得免费时长开关
            Kv::FIRST_SHARE_REWARD_FLG,
            //分享获得免费时长
            Kv::FIRST_SHARE_REWARD_MATCH_TIMES,
            //第一次分享获得免费时长有效期（从注册时间开始算起）
            Kv::FIRST_SHARE_REWARD_EXPIRE_HOUR,
            //是否开启免费时长弹窗
            Kv::FREE_TIME_SHOW
        ];

		$rows = Kv::many($aKey);

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $registerRewardFlg = $params[Kv::REGISTER_REWARD_FLG] ?? 0;
            $firstShareRewardFlg = $params[Kv::FIRST_SHARE_REWARD_FLG] ?? 'N';
            if($registerRewardFlg == 1 && $firstShareRewardFlg == 'Y'){
                $this->error('注册奖励不能和首次分享奖励同时开启');
            }

            foreach ($params as $key => $value) {
            	$data[] = [
            		'kv_key' => $key,
            		'kv_value' => $value,
            	];
            }

            $row = new Kv();
            $row->validate(
                [
                    'kv_key'  => 'require',
                ],
                [
                    'kv_key.require' => __('Parameter %s can not be empty', ['kv_key']),
                ]
            );

            Kv::where('kv_key', 'in', array_keys($params))->delete();

            if ($row->saveAll($data) === false) {
                $this->error($row->getError());
            } else {
                $this->success();
            }
        }

        foreach ($aKey as $sKey) {
        	$row[$sKey] = isset($rows[$sKey]) ? $rows[$sKey] : '';
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
	}
}