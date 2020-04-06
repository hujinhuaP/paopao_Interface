<?php

namespace app\models;

/**
 * DeviceActiveLog 设备激活记录
 */
class DeviceActiveLog extends ModelBase
{
    public $id;
    public $device_active_device_no;
    public $device_active_invite_code;
    public $device_active_agent_id;
    public $device_active_create_time;
    public $device_active_update_time;

    public function beforeCreate()
    {
        $this->device_active_create_time = time();
        $this->device_active_update_time = time();
    }

    public function beforeUpdate()
    {
        $this->device_active_update_time = time();
    }

}