<?php

/**
 * @name RechargeController
 * @author root
 * @desc 充值控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class RechargeController extends BaseController
{
    private $_key = 'hzjkb24';

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/activity/index/index/index/name/root 的时候, 你就会发现不同
     */
    public function wechatAction($user = 0)
    {
        // 判断在微信中打开
        // 根据用户ID 或者手机号判断用户信息
        $userResult = [];
        $hasFirstCombo = TRUE;
        if($user){
            $userFlg = intval($user);
            $userResult = $this->db->fetchRow("select user_id,user_coin,user_free_coin,user_nickname,user_avatar,user_total_coin from `user` where user_id = $userFlg");
            if($userResult && $userResult['user_total_coin'] > 0){
                $hasFirstCombo = FALSE;
            }
        }

        // 获取充值套餐列表
        $comboWhereSql = 'where user_recharge_combo_apple_id = ""';
        if( $hasFirstCombo === FALSE ){
            $comboWhereSql .= ' AND user_recharge_is_first = "N" ';
        }
        $comboSql = 'select * from user_recharge_combo ' . $comboWhereSql;
        $comboResult    = $this->db->fetchAll($comboSql);


        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        $this->getView()->assign("userResult", $userResult);
        $this->getView()->assign("comboResult", $comboResult);
        return TRUE;
    }


    // 提交订单 H5
    public function createOrderAction()
    {
        $sPayType = $this->getRequest()->getPost("pay_type");
        switch ($sPayType){
            case 'wechat':
                // 微信支付
                break;
            case 'alipay':
                // 支付宝
                break;
            default:
                echo 404;
                return FALSE;
        }
    }




}
