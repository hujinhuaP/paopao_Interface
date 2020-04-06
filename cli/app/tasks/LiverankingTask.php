<?php

namespace app\tasks;

use Phalcon\Exception;

/**
 * LiverankingTask 直播排行榜
 */
class LiverankingTask extends MainTask
{
	/**
	 * mainAction 
	 */
    public function mainAction()
    {
    	while (1) {
            try {
                echo date('r')." Updateing ";
//                $this->dayHandle();
                echo "■■";
                usleep(300000);
                $this->weekHandle();
                echo "■■";
                usleep(300000);
//                $this->allHandle();
                echo "■■";
                usleep(300000);
                $this->anchorRankingHandle();
                echo " OK.\n";
            } catch (\Phalcon\Db\Exception $e) {
                try{
                    $this->db->connect();
                }catch(\PDOException $e){
                    echo $e;
                }
            }catch(\PDOException $e) {
                try{
                    $this->db->connect();
                }catch(\PDOException $e){
                    echo $e;
                }
            } catch(Exception $e) {
                echo $e;
            }  catch (\Exception $e) {
                echo $e;
            }
            sleep(30);
    	}
    }

    public function weekRankAction()
    {
        $this->weekHandle();
    }

    /**
     * dayHandle 日榜
     */
    private function dayHandle()
    {

        $sQuerySql = "SELECT user_id as anchor_user_id,
target_user_id as user_id,
SUM(consume_source) live_gift_coin,
SUM(consume) live_gift_dot 
FROM `user_finance_log` WHERE create_time>:time AND user_amount_type = 'dot' and consume > 0 and target_user_id> 0 group by user_id,target_user_id ORDER BY consume desc";
//    	$sQuerySql = 'SELECT anchor_user_id,user_id,SUM(live_gift_coin*live_gift_number) live_gift_coin,SUM(live_gift_dot*live_gift_number) live_gift_dot FROM `user_gift_log` WHERE `user_gift_log_create_time`>:time AND user_gift_log_status="Y" group by user_id,anchor_user_id';

    	$result = $this->db->query($sQuerySql, [
    		'time' => strtotime('today'),
    	]);

    	$result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

    	$sInsertSql = '';
        $data = [];

    	foreach ($result->fetchAll() as $key => $row) {

            // 主播不能给自己刷礼物，
            if ($row['anchor_user_id'] == $row['user_id']) {
                continue;
            }

    		$sInsertSql != '' && $sInsertSql .= '),(';
    		$sInsertSql .= sprintf(':anchor_user_id_%d,:user_id_%d,:live_gift_coin_%d,:live_gift_dot_%d,"day",:create_time_%d,:update_time_%d', $key, $key, $key, $key, $key, $key);
			$data['anchor_user_id_'.$key] = $row['anchor_user_id'];
			$data['user_id_'.$key]        = $row['user_id'];
            $data['live_gift_coin_'.$key] = abs($row['live_gift_coin']);
			$data['live_gift_dot_'.$key]  = abs($row['live_gift_dot']);
			$data['create_time_'.$key]    = time();
			$data['update_time_'.$key]    = time();
    	}

    	if ($sInsertSql != '') {
    		$sInsertSql = sprintf('REPLACE INTO user_gift_rank(anchor_user_id,user_id,live_gift_coin,live_gift_dot,user_gift_rank_type,user_gift_rank_create_time,user_gift_rank_update_time) VALUES (%s)', $sInsertSql);
        	$this->db->execute($sInsertSql, $data);

            // 删除没有更新的数据
            $sDeleteSql = 'DELETE FROM user_gift_rank WHERE user_gift_rank_type="day" AND user_gift_rank_update_time<=:time';
            $this->db->execute($sDeleteSql, [
                'time' => strtotime(date('Y-m-d 23:59:59', strtotime('-1 day'))),
            ]);
    	}
    	
    }

    /**
     * weekHandle 周榜
     */
    private function weekHandle()
    {
        if(time() > strtotime(date('2019-02-18'))){
            return;
        }
        $sQuerySql = "SELECT user_id as anchor_user_id,target_user_id as user_id,SUM(consume_source) live_gift_coin,SUM(consume) live_gift_dot 
FROM `user_finance_log` WHERE create_time>:time AND user_amount_type = 'dot' and consume > 0 and target_user_id> 0 group by user_id,target_user_id ORDER BY consume desc";

    	$result = $this->db->query($sQuerySql, [
    		'time' => strtotime(date('Y-m-d 23:59:59', strtotime('last day this week')))+1,
    	]);

    	$result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);



    	$sInsertSql = '';
    	foreach ($result->fetchAll() as $key => $row) {

            // 主播不能给自己刷礼物
            if ($row['anchor_user_id'] == $row['user_id']) {
                continue;
            }

    		$sInsertSql != '' && $sInsertSql .= '),(';
    		$sInsertSql .= sprintf(':anchor_user_id_%d,:user_id_%d,:live_gift_coin_%d,:live_gift_dot_%d,"week",:create_time_%d,:update_time_%d', $key, $key, $key, $key, $key, $key);
			$data['anchor_user_id_'.$key] = $row['anchor_user_id'];
			$data['user_id_'.$key]        = $row['user_id'];
			$data['live_gift_coin_'.$key] = abs($row['live_gift_coin']);
            $data['live_gift_dot_'.$key]  = abs($row['live_gift_dot']);
			$data['create_time_'.$key]    = time();
			$data['update_time_'.$key]    = time();
    	}

    	if ($sInsertSql != '') {
    		$sInsertSql = sprintf('REPLACE INTO user_gift_rank(anchor_user_id,user_id,live_gift_coin,live_gift_dot,user_gift_rank_type,user_gift_rank_create_time,user_gift_rank_update_time) VALUES (%s)', $sInsertSql);
        	$this->db->execute($sInsertSql, $data);
        	var_dump("更新成功周榜:".$this->db->affectedRows());

            // 删除没有更新的数据
            $sDeleteSql = 'DELETE FROM user_gift_rank WHERE user_gift_rank_type="week" AND user_gift_rank_update_time<=:time';
            $this->db->execute($sDeleteSql, [
                'time' => strtotime(date('Y-m-d 23:59:59', strtotime('-1 day'))),
            ]);
        }
   		
    }

    /**
     * allHandle 总榜
     */
    private function allHandle()
    {
        $sQuerySql = "SELECT user_id as anchor_user_id,
target_user_id as user_id,
SUM(consume_source) live_gift_coin,
SUM(consume) live_gift_dot 
FROM `user_finance_log` WHERE create_time>:time AND user_amount_type = 'dot' and consume > 0 and target_user_id> 0 group by user_id,target_user_id ORDER BY consume desc";
//    	$sQuerySql = 'SELECT anchor_user_id,user_id,SUM(live_gift_coin*live_gift_number) live_gift_coin,SUM(live_gift_dot*live_gift_number) live_gift_dot FROM `user_gift_log` WHERE user_gift_log_status="Y" AND user_gift_log_create_time>=:time group by user_id,anchor_user_id';

    	$result = $this->db->query($sQuerySql, [
            'time' => strtotime('-6 months'),
        ]);

    	$result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

    	$sInsertSql = '';
    	foreach ($result->fetchAll() as $key => $row) {

            // 主播不能给自己刷礼物，
            if ($row['anchor_user_id'] == $row['user_id']) {
                continue;
            }

    		$sInsertSql != '' && $sInsertSql .= '),(';
    		$sInsertSql .= sprintf(':anchor_user_id_%d,:user_id_%d,:live_gift_coin_%d,:live_gift_dot_%d,"all",:create_time_%d,:update_time_%d', $key, $key, $key, $key, $key, $key, $key);
			$data['anchor_user_id_'.$key] = $row['anchor_user_id'];
			$data['user_id_'.$key]        = $row['user_id'];
            $data['live_gift_coin_'.$key] = abs($row['live_gift_coin']);
            $data['live_gift_dot_'.$key]  = abs($row['live_gift_dot']);
			$data['create_time_'.$key]    = time();
			$data['update_time_'.$key]    = time();
    	}

    	if ($sInsertSql != '') {
    		$sInsertSql = sprintf('REPLACE INTO user_gift_rank(anchor_user_id,user_id,live_gift_coin,live_gift_dot,user_gift_rank_type,user_gift_rank_create_time,user_gift_rank_update_time) VALUES (%s)', $sInsertSql);
        	$this->db->execute($sInsertSql, $data);

            // 删除没有更新的数据
            $sDeleteSql = 'DELETE FROM user_gift_rank WHERE user_gift_rank_type="all" AND user_gift_rank_update_time<=:time';
            $this->db->execute($sDeleteSql, [
                'time' => strtotime(date('Y-m-d 23:59:59', strtotime('-1 day'))),
            ]);
        }
    }

    /**
     * anchorRankingHandle 主播排行榜
     */
    public function anchorRankingHandle()
    {
    	$sQuerySql = 'SELECT anchor_user_id,SUM(live_gift_dot) live_gift_dot FROM `user_gift_rank` WHERE user_gift_rank_type="week" group by anchor_user_id ORDER BY live_gift_dot DESC';

    	$result = $this->db->query($sQuerySql);

    	$result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

    	$sInsertSql = '';
        $aAnchorRanking = [];
        $aAnchor = [];
        $data = [];
    	foreach ($result->fetchAll() as $key => $row) {

    		$sInsertSql != '' && $sInsertSql .= '),(';
    		$sInsertSql .= sprintf(':anchor_user_id_%d,:anchor_ranking_%d', $key, $key);
			$data['anchor_user_id_'.$key] = $row['anchor_user_id'];
			$data['anchor_ranking_'.$key] = $key+1;
            $aAnchorRanking[$row['anchor_user_id']] = $key+1;
    	}


        if ($sInsertSql != '') {
            // 查询旧的数据
            $anchor = $this->db->query('SELECT * FROM anchor WHERE user_id in ('. implode(',', array_keys($aAnchorRanking)) .')');
            $anchor->setFetchMode(\Phalcon\Db::FETCH_ASSOC);

            // 更新新的数据
            $this->db->execute('UPDATE anchor SET anchor_ranking=0 WHERE anchor_ranking>0');
    		$sInsertSql = sprintf('INSERT INTO anchor(user_id,anchor_ranking) VALUES (%s) ON DUPLICATE KEY UPDATE anchor_ranking=VALUES(anchor_ranking)', $sInsertSql);
        	$this->db->execute($sInsertSql, $data);
            // 这里还要用websocket通知主播的排名更新
//            foreach ($anchor->fetchAll() as $key => $row) {
//                if (isset($aAnchorRanking[$row['user_id']]) && $aAnchorRanking[$row['user_id']] != $row['anchor_ranking']) {
//                    $url = APP_API_URL.'live/pay/notifyAnchor';
//                    $param = [
//                        'anchor_user_id' => $row['user_id']
//                    ];
//                    $flg = $this->httpRequest($url, $param);
//                    var_dump($flg);
//                }
//            }
            
        }
    }
}