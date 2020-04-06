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

    public function userAccount()
    {
        return $this->hasOne('user_account','user_id','user_id',[],'inner');
    }

    public function userCertification()
    {
        return $this->hasOne('user_certification','user_id','user_id',[],'left');
    }


}
