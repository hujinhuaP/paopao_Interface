<?php

/**
 * @name IndexController
 * @author root
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 * @property \Db_Mysql db
 */
class ActivityController extends BaseController
{

    /**
     * 特定时间抽中的特定礼物排行榜
     */
    public function specialGiftRankAction()
    {
        $startDate = '2019-12-16';
        $startTime = strtotime($startDate);
        $endDate   = '2019-12-29';
        $endTime   = strtotime($endDate) + 86399;

        $activityTitle  = '平安圣诞狂欢夜';
        $specialGiftId  = 134;
        $specialGiftId2 = 135;

        $this->getView()->assign("rank", $this->_getSpecialGiftRank($specialGiftId, $startTime, $endTime));
        $this->getView()->assign("rank2", $this->_getSpecialGiftRank($specialGiftId2, $startTime, $endTime));
        $this->getView()->assign("activityTitle", $activityTitle);
    }


    /**
     * @param $specialGiftId
     * @param $startTime
     * @param $endTime
     * @return mixed|multitype
     * 特定时间 特定礼物  送礼人数  1个人只算1个
     */
    private function _getSpecialGiftRank( $specialGiftId, $startTime, $endTime )
    {
        $specialGiftResultKey = sprintf('specialGiftRank:%s', $specialGiftId);
        $this->redis->set($specialGiftResultKey, '');
        $rankJson = $this->redis->get($specialGiftResultKey);
        if ( !$rankJson ) {
            $sql = "select t.total_number,u.user_id,u.user_nickname,u.user_avatar from 
(select count(DISTINCT user_id) as total_number,anchor_user_id from `yuyin_live`.user_gift_log  where live_gift_id = :gift_id 
AND user_gift_log_create_time BETWEEN :start_time AND :end_time
group by anchor_user_id order by total_number desc) t 
inner join `yuyin_live`.`user` as u on t.anchor_user_id = u.user_id limit 5";

            $rank = $this->db->fetchAll($sql, [
                'start_time' => $startTime,
                'end_time'   => $endTime,
                'gift_id'    => $specialGiftId,
            ]);

            foreach ( $rank as &$item ) {
                $item['user_nickname'] = mb_substr($item['user_nickname'], 0, 1) . '****';
            }
            $rankJson = json_encode($rank);
            $this->redis->set($specialGiftResultKey, $rankJson);
            $this->redis->expire($specialGiftResultKey, 60);
        }
        $rank = json_decode($rankJson, TRUE);
        return $rank;
    }


    /**
     * @param $user
     * 活动期间内 累计充值  累计收益获得魅力
     */
    public function coinAndDotStatAction( $user )
    {
        $startTime = strtotime('2019-12-28');
        $endTime   = strtotime('2020-02-08 23:59:59');

        $userAvatar = '/static/imgs/coinanddot/default.png';
        $totalRechargeCoin = 0;
        $totalGetDot       = 0;
        if ( $user ) {

            $userSql = 'select user_avatar from `yuyin_live`.user where user_id = :user_id';
            $userData = $this->db->fetchRow($userSql,[
                'user_id' => $user
            ]);
            if($userData){
                $userAvatar = $userData['user_avatar'];

                // 活动期间充值
                $rechargeSql  = 'select sum(user_recharge_order_coin) as total_coin from `yuyin_live`.user_recharge_order 
where user_recharge_order_status = "Y" and user_id = :user_id AND user_recharge_order_update_time BETWEEN :start_time AND :end_time';
                $rechargeData = $this->db->fetchRow($rechargeSql, [
                    'user_id'    => $user,
                    'start_time' => $startTime,
                    'end_time'   => $endTime
                ]);
                if ( $rechargeData ) {
                    $totalRechargeCoin = $rechargeData['total_coin'] ?? 0;
                }

                // 活动期间获得佣金
                $getDotSql  = "select sum(consume) as total_dot from `yuyin_live`.user_finance_log 
where user_id = :user_id AND user_amount_type = 'dot' AND consume > 0 and create_time between :start_time AND :end_time";
                $getDotData = $this->db->fetchRow($getDotSql, [
                    'user_id'    => $user,
                    'start_time' => $startTime,
                    'end_time'   => $endTime
                ]);
                if ( $getDotData ) {
                    $totalGetDot = $getDotData['total_dot'] ?? 0;
                }
            }


        }


        $this->getView()->assign([
            'totalRechargeCoin' => $totalRechargeCoin,
            'totalGetDot'       => $totalGetDot * 100,
            'userAvatar'       => $userAvatar,
        ]);
    }


}
