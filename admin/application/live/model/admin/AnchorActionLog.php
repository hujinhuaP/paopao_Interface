<?php

namespace app\live\model\admin;

use app\live\model\AdminModel as Model;

class AnchorActionLog extends Model
{

    /** @var string 设置红人 */
    const ACTION_TYPE_HOT_MAN  = 'hot_man';
    /** @var string 设置热门 */
    const ACTION_TYPE_HOT_TIME = 'hot_time';
    /** @var string 设置签约 */
    const ACTION_TYPE_SIGN       = 'sign';
    /** @var string 首页显示 */
    const ACTION_TYPE_SHOW_INDEX = 'show_index';
    /** @var string 新人热推 */
    const ACTION_TYPE_NEW_HOT  = 'new_hot';
    /** @var string 积极 */
    const ACTION_TYPE_POSITIVE = 'positive';
    /** @var string 高颜值 */
    const ACTION_TYPE_BEAUTY   = 'beauty';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';


    public static function record($action_type, $user_id, $change_to_status)
    {
        $admin    = \think\Session::get('admin');
        $admin_id = $admin ? $admin->id : 0;
        $username = $admin ? $admin->username : __('Unknown');
        $params   = request()->param();
        return self::create([
            'action_type'      => $action_type,
            'user_id'          => $user_id,
            'remark'           => $params['remark'] ?? '',
            'admin_id'         => $admin_id,
            'admin_name'       => $username,
            'change_to_status' => $change_to_status
        ]);
    }

    public function admin()
    {
        return $this->belongsTo('Admin', 'admin_id')->setEagerlyType(0);
    }

}
