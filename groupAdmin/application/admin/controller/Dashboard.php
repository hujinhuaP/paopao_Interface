<?php

namespace app\admin\controller;

use app\admin\model\api\User;
use app\admin\model\api\Group as GroupModel;
use app\common\controller\Backend;
use think\Config;
use think\Session;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        $totalUser   = User::where('user_group_id=' . Session::get('admin.group_id'))->count();
        $totalAnchor = User::where('user_group_id=' . Session::get('admin.group_id') . ' AND user_is_anchor = "Y"')->count();

        $this->view->assign([
            'total_user'   => $totalUser,
            'total_anchor' => $totalAnchor,
            'group'        => Session::get('admin.group'),
            'group_money'  =>  GroupModel::get(Session::get('admin.group_id'))->money
        ]);
        return $this->view->fetch();
    }


    /**
     * 查看
     */
    public function indexBack()
    {
        $seventtime = \fast\Date::unixtime('day', -7);
        $paylist    = $createlist = [];
        for ( $i = 0; $i < 7; $i++ ) {
            $day              = date("Y-m-d", $seventtime + ($i * 86400));
            $createlist[$day] = mt_rand(20, 200);
            $paylist[$day]    = mt_rand(1, mt_rand(1, $createlist[$day]));
        }
        $hooks            = config('addons.hooks');
        $uploadmode       = isset($hooks['upload_config_init']) && $hooks['upload_config_init'] ? implode(',', $hooks['upload_config_init']) : 'local';
        $addonComposerCfg = ROOT_PATH . '/vendor/karsonzhang/fastadmin-addons/composer.json';
        Config::parse($addonComposerCfg, "json", "composer");
        $config       = Config::get("composer");
        $addonVersion = isset($config['version']) ? $config['version'] : __('Unknown');
        $this->view->assign([
            'totaluser'        => 35200,
            'totalviews'       => 219390,
            'totalorder'       => 32143,
            'totalorderamount' => 174800,
            'todayuserlogin'   => 321,
            'todayusersignup'  => 430,
            'todayorder'       => 2324,
            'unsettleorder'    => 132,
            'sevendnu'         => '80%',
            'sevendau'         => '32%',
            'paylist'          => $paylist,
            'createlist'       => $createlist,
            'addonversion'     => $addonVersion,
            'uploadmode'       => $uploadmode
        ]);

        return $this->view->fetch();
    }

}
