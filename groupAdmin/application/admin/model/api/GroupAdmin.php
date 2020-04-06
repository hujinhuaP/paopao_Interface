<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class GroupAdmin extends ApiModel
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    /**
     * 重置用户密码
     * @author baiyouwen
     */
    public function resetPassword($uid, $NewPassword)
    {
        $passwd = $this->encryptPassword($NewPassword,$this->salt);
        $ret = $this->where(['id' => $uid])->update(['agent_auth' => $passwd]);
        return $ret;
    }

    // 密码加密
    public function encryptPassword($password, $salt = '', $encrypt = 'md5')
    {
        return $encrypt($password . $salt);
    }
}
