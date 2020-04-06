<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class Agent extends ApiModel
{

    /**
     * 用户充值提成比例
     */
    const MARKING_RECHARGE_DISTRIBUTION_PROFITS = 30;
    /**
     * 用户购买VIP提成比例
     */
    const MARKING_VIP_DISTRIBUTION_PROFITS = 30;

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    /**
     * 重置用户密码
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        $passwd = $this->encryptPassword($NewPassword,$this->invite_code);
        $ret = $this->where(['agent_id' => $uid])->update(['agent_auth' => $passwd]);
        return $ret;
    }

    // 密码加密
    public function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }


    public function createInviteCode() {
        $invite_code = createNoncestr(10);
        $existData          = $this->where("invite_code", $invite_code)->find();
        if(!$existData){
            return $invite_code;
        }
        $this->createInviteCode();
    }

}
