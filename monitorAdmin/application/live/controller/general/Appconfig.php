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
		$aKey = [
			// 客服联系方式
		    Kv::KEY_CONTACT_US,
		    // 金币名称
		    Kv::KEY_COIN_NAME,
		    // 收益名称
		    Kv::KEY_DOT_NAME,
            /*游戏接口秘钥*/
		    Kv::KEY_API_SECRET,
		    Kv::PRIVATE_PRICE_MAX,
		    Kv::PRIVATE_PRICE_MIN,
            Kv::APPLE_ONLINE,
            Kv::CHARGE_VIDEO_PRICE_MIN,
            Kv::CHARGE_VIDEO_PRICE_MAX,
            Kv::CHAT_FREE_COUNT,
            Kv::VIP_VIDEO_DISCOUNT,
            Kv::CHAT_PRICE,
            Kv::NEW_USER_VIDEO_PLAY_TIME,
		];

		$rows = Kv::many($aKey);

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

        foreach ($aKey as $sKey) {
        	$row[$sKey] = isset($rows[$sKey]) ? $rows[$sKey] : '';
        }

        $this->view->assign("row", $row);
        $this->view->assign("apple_online", $row['apple_online']);
        return $this->view->fetch();
	}
}