<?php 
namespace app\live\controller\general;

use app\common\controller\Backend;
use think\Exception;
use app\live\model\live\Kv;

/**
 * 分销配置
 *
 * @icon fa fa-circle-o
 */
class Distribution extends Backend
{
	/**
	 * edit 编辑
	 */
	public function edit($ids = NULL)
	{
		$aKey = [
			// 一级邀请比例
		    Kv::KEY_INVITE_RATIO_1,
		    // 二级邀请比例
		    Kv::KEY_INVITE_RATIO_2,
		    // 三级邀请比例
		    Kv::KEY_INVITE_RATIO_3,
		];

		$rows = Kv::many($aKey);

        if ($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            foreach ($params as $key => $value) {
            	$data[] = [
            		'kv_key' => $key,
            		'kv_value' => (int)$value,
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

        foreach ($aKey as $sKey) {
        	$row[$sKey] = isset($rows[$sKey]) ? $rows[$sKey] : '';
        }

        $this->view->assign("row", $row);
        return $this->view->fetch();
	}
}