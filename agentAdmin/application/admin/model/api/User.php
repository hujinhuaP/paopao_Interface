<?php

namespace app\admin\model\api;

use think\Model;
use think\Session;

class User extends ApiModel
{

    /** @type string 手机登录 */
    const LOGIN_TYPE_PHONE = 'phone';
    /** @type string QQ登录 */
    const LOGIN_TYPE_QQ    = 'qq';
    /** @type string 微信登录 */
    const LOGIN_TYPE_WX    = 'wx';
    /** @type string 微博登录 */
    const LOGIN_TYPE_WB    = 'wb';

    /** @type string 手机注册 */
    const REGISTER_TYPE_PHONE = 'phone';
    /** @type string QQ注册 */
    const REGISTER_TYPE_QQ    = 'qq';
    /** @type string 微信注册 */
    const REGISTER_TYPE_WX    = 'wx';
    /** @type string 微博注册 */
    const REGISTER_TYPE_WB    = 'wb';

    public static function getInviteCode() {
        $sLetter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sNumber = '0123456789';
        $sCode = $sLetter{rand(0, 25)};
        for ($i = 0; $i < 5; $i ++) {
            $sCode .= $sNumber{rand(0, 9)};
        }
        $isExsit = User::where('user_invite_code','eq',$sCode)->find();

        if (! empty($isExsit)) {
            return self::getInviteCode();
        }

        return $sCode;
    }
}
