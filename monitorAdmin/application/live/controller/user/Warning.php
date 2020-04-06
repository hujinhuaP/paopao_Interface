<?php

namespace app\live\controller\user;

use app\live\library\Redis;
use app\live\model\live\Kv;
use think\Exception;
use app\common\controller\Backend;


/**
 * 对账以及警告管理
 *
 * @Authors yeah_lsj@yeah.net
 */
class Warning extends Backend
{
	/**
	 * edit 编辑
	 * 
	 * @param  int $ids
	 */
	public function edit($ids='')
	{
        $aKey = [
            // 虚拟币名称
            Kv::KEY_COIN_NAME,
            // 虚拟币阀值
            Kv::COIN_THRESHOLD,
            //管理员手机号
            Kv::ADMIN_PHONE
        ];
        $data = Kv::many($aKey);
        $row['config']['coin_name'] = $data[Kv::KEY_COIN_NAME];

        $row['config']['coin_threshold'] = $data[Kv::COIN_THRESHOLD];
        $row['config']['admin_phone'] = $data[Kv::ADMIN_PHONE];
        $redis = new Redis();
        $static = $redis->get('static');
        if($static){
            $row['static'] = json_decode($static,true);
        }
        $row['config']['normal_status'] = '正常';
        if(isset($row['static']) && $row['static']['deviation'] >= $row['config']['coin_threshold']){
            $row['config']['normal_status'] = '异常';
        }

        $this->view->assign('row', $row);
        return $this->view->fetch();
	}

    /**
     * multi 批量修改
     * 
     * @param  int $ids
     */
    public function multi($ids='')
    {
        $params = $this->request->param();
        switch($params['type']){
            case 1:
                $row = Kv::where("kv_key='".Kv::FREEZE_RECHARGE."'")->find();
                $row->kv_value = 0;
                $row->save();
                $row = Kv::where("kv_key='".Kv::FREEZE_WITHDRAWALS."'")->find();
                $row->kv_value = 0;
                $row->save();
                break;
            case 2:
                $row = Kv::where("kv_key='".Kv::FREEZE_WITHDRAWALS."'")->find();
                $row->kv_value = 1;
                $row->save();
                break;
            case 3:
                $row = Kv::where("kv_key='".Kv::FREEZE_RECHARGE."'")->find();
                $row->kv_value = 1;
                $row->save();
                break;
            case 4:

                if($params['coin_threshold'] <= 0){
                    $this->error(__('Threshold must lt zero'));
                }
                if(!preg_match("/^1[34578]{1}\d{9}$/",$params['admin_phone'])){
                    $this->error(__('Please fill in the correct phone number'));
                }
                $row = Kv::where("kv_key='".Kv::COIN_THRESHOLD."'")->find();
                $row->kv_value = $params['coin_threshold'];
                $row->save();
                $row = Kv::where("kv_key='".Kv::ADMIN_PHONE."'")->find();
                $row->kv_value = $params['admin_phone'];
                $row->save();
                break;
        }
        $this->success();
    }

}