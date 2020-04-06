<?php

namespace app\live\controller\document;

use app\common\controller\Backend;
use app\live\model\live\UserNameConfig;
use think\cache\driver\Redis;
use think\Exception;

use app\live\model\live\AboutUs as AboutUsModel;

/**
 * 用户昵称库
 *
 */
class Nickname extends Backend
{
    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('live.user_name_config');
    }
	/**
	 * index 列表
	 */
	public function index()
	{
		if ($this->request->isAjax())
        {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total  = $this->model->where($where)->order($sort, $order)->count();
            $list   = $this->model->where($where)->order($sort, $order)->limit($offset, $limit)->select();
            $result = [
                "total" => $total,
                "rows"  => $list
            ];
            return json($result);
        }

        return $this->view->fetch();
	}


	/**
	 * add 添加
	 */
	public function add()
	{

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            $content = explode(',', str_replace(['，','、'], ',', $params['content']));

            foreach ($content as $value) {

                if ( !$value || mb_strlen($value) > 5 || $this->model::where(['user_name_config_value' => $value,'user_name_config_type' => $params['user_name_config_type']])->find()) {
                    continue;
                }


                $row = new UserNameConfig();
                $row->user_name_config_value = trim($value);
                $row->user_name_config_type = $params['user_name_config_type'];
                $row->validate(
                    [
                        'user_name_config_value' => 'require',
                    ],
                    [
                        'user_name_config_value.require' =>  __('Parameter %s can not be empty', ['内容']),
                    ]
                );

                if ($row->save($row->getData()) === false) {
                    $this->error($row->getError());
                }
            }
            $oRedis = new Redis(\think\Config::get('redis'));
            $key = sprintf('user_name_config:%s',$params['user_name_config_type']);
            $oRedis->rm($key);
            $this->success();
        }

        return $this->view->fetch();
	}


	/**
	 * delete 删除
	 * 
	 * @param  string $ids
	 */
	public function delete($ids='')
	{
        $this->model::where('user_name_config_id', 'in', $ids)->delete();
        $oRedis = new Redis(\think\Config::get('redis'));
        $key = 'user_name_config:prefix';
        $key2 = 'user_name_config:suffix';
        $oRedis->rm($key);
        $oRedis->rm($key2);
		$this->success();
	}

	/**
	 * multi 批量操作
	 * @param  string $ids
	 */
	public function multi($ids='')
	{
		
	}
}