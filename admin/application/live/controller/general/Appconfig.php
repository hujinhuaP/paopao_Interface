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
class Appconfig extends Backend
{
	/**
	 * edit 编辑
	 */
	public function edit($ids = NULL)
	{

		$oKv = Kv::all();
        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

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
        foreach ($oKv as $kvItem) {
            $sKey = $kvItem->kv_key;
        	$row[$sKey] = $kvItem->kv_value;
        }

        $this->view->assign("row", $row);
        $this->view->assign("apple_online", $row['apple_online']);
        $this->view->assign("anchor_reply_normal_user", $row['anchor_reply_normal_user']);
        return $this->view->fetch();
	}
}