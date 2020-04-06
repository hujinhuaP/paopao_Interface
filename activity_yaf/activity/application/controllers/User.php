<?php

class UserController extends BaseController
{

    /**
     * @param int $user我的VIP 等级
     */
    public function vipLevelAction( $user = 0 )
    {
        $userLevel = 0;
        $userExp   = 0;
        if($user > 0){
            $userResult  = $this->db->fetchRow('select user_vip_level,user_vip_exp from yuyin_live.user where user_id =' . intval($user));
            if($userResult){
                $userLevel = intval($userResult['user_vip_level']);
                $userExp = intval($userResult['user_vip_exp']);
            }
        }
        // VIP等级列表
        $vipData      = $this->_getVipInfo();
        $totalLevel   = count($vipData) - 1;
        $currentLevel = [];
        $nextLevel    = $vipData[ $totalLevel ];
        foreach ( $vipData as $key => $item ) {
            if ( $userLevel == $item['vip_level_value'] ) {
                $currentLevel = $item;
                if ( isset($vipData[ $key + 1 ]) ) {
                    $nextLevel = $vipData[ $key + 1 ];
                }
                break;
            }
        }

        $singleLevelPercent = 100 / $totalLevel;

        $existVIPPercent     = $userLevel * 100 / $totalLevel;
        $showLevelInfo  = '你已达到最高等级';
        $currentLevelPercent = 0;
        if ( $currentLevel['vip_level_value'] < $nextLevel['vip_level_value'] ) {
            $dissExp        = $nextLevel['vip_level_min_exp'] - $userExp;
            $existsLevelExp = $userExp - $currentLevel['vip_level_min_exp'];
            $showLevelInfo  = sprintf('还差%d升级%s,新特权即将开启', $dissExp, $nextLevel['vip_level_name']);
            $currentLevelPercent = $existsLevelExp / ($nextLevel['vip_level_min_exp'] - $currentLevel['vip_level_min_exp']);
        }


        $totalPercent = $singleLevelPercent * $currentLevelPercent + $existVIPPercent;

        $this->getView()->assign([
            'user_exp'        => $userExp,
            'show_level_info' => $showLevelInfo,
            'vipData'         => $vipData,
            'totalPercent'    => sprintf('%.2f', $totalPercent)
        ]);
    }

    private function _getVipInfo()
    {
        $vipCacheKey = 'vip_data';
        if ( !$this->redis->exists($vipCacheKey) ) {
            $sql  = "select * from yuyin_live.vip_level order by vip_level_value asc";
            $data = $this->db->fetchAll($sql);
            foreach ( $data as $key => &$item ) {
                $item['vip_level_max_exp'] = -1;
                if ( isset($data[ $key + 1 ]) ) {
                    $item['vip_level_max_exp'] = $data[ $key + 1 ]['vip_level_min_exp'] - 1;
                    $item['exp_show']          = sprintf('%d-%d', intval($item['vip_level_min_exp']), intval($item['vip_level_max_exp']));
                } else {
                    $item['exp_show'] = sprintf('%d以上', intval($item['vip_level_min_exp']));
                }
                if ( $item['vip_level_exhibition_discount'] == 0 ) {
                    $item['vip_level_exhibition_discount_show'] = '免费';
                } elseif ( $item['vip_level_exhibition_discount'] == 10 ) {
                    $item['vip_level_exhibition_discount_show'] = '无特权';
                } else {
                    $item['vip_level_exhibition_discount_show'] = sprintf("%s折", $item['vip_level_exhibition_discount']);
                }

                if ( $item['vip_level_video_chat_discount'] == 0 ) {
                    $item['vip_level_video_chat_discount_show'] = '免费';
                } elseif ( $item['vip_level_video_chat_discount'] == 10 ) {
                    $item['vip_level_video_chat_discount_show'] = '无特权';
                } else {
                    $item['vip_level_video_chat_discount_show'] = sprintf("%s折", $item['vip_level_video_chat_discount']);
                }
            }
            $this->redis->set($vipCacheKey, json_encode($data));
            $this->redis->expire($vipCacheKey, 60);
        }
        $vipData = $this->redis->get($vipCacheKey);
        return json_decode($vipData, TRUE);
    }



    /**
     * @param string $app
     * 砸蛋玩法
     */
    public function eggAction( $app = 'voice' )
    {
        $eggGoodsData = $this->_getEggGoods();

        $this->getView()->assign("total_count", count($eggGoodsData));
        $this->getView()->assign("eggGoodsData", $eggGoodsData);
        $this->getView()->assign("agreement_name", '砸蛋玩法');
    }

    private function _getEggGoods()
    {
        $eggGoodsKey = 'egg_goods';
        if ( !$this->redis->exists($eggGoodsKey) ) {
            $sql = "select * from yuyin_live.egg_goods where egg_goods_point > 0 order by egg_goods_show_sort asc";
            $data = $this->db->fetchAll($sql);

            foreach ($data as &$item){
                switch ($item['egg_goods_category']){
                    case 'coin':
                        $item['egg_goods_value_show'] = sprintf('%d币',$item['egg_goods_value']);
                        break;
                    case 'vip':
                        $item['egg_goods_value_show'] = sprintf('%d天',$item['egg_goods_value']);
                        break;
                    case 'diamond':
                    default:
                        $item['egg_goods_value_show'] = sprintf('%d钻石',$item['egg_goods_value']);
                        break;
                }
                $bracketsPos = strpos($item['egg_goods_name'],'（');
                if($bracketsPos){
                    $item['egg_goods_name'] = substr($item['egg_goods_name'],0,$bracketsPos);
                }
            }
            $this->redis->set($eggGoodsKey, json_encode($data));
            $this->redis->expire($eggGoodsKey, 600);
        }
        $eggGoodsData = $this->redis->get($eggGoodsKey);
        return json_decode($eggGoodsData, TRUE);

    }

}
