GREEN LIVE API (绿播API)
========================

### API接口使用phalcon框架开发  ###

## **版本迭代** ##
https://api.greenlive.com/[版本1]/[模块]/[控制器]/[方法]

## **主要角色** ##
* 用户
* 主播


## 调用方式 ##

* 1.请求结构
	**服务地址**

		GREENLIVE API 的服务接入地址，如下所示。

		系统模块		接入地址
		用户			account.greenlive.1booker.com
		直播			api.greenlive.1booker.com
		...
		其他新的业务	xxx.greenlive.1booker.com
	
	**通信协议**

		支持通过 HTTP 或 HTTPS 通道进行请求通信。
		为了获得更高的安全性，推荐您使用 HTTPS 通道发送请求。
		涉及密码传送或者密钥的接口，必须使用 HTTPS 协议调用 API，例如指定 用户登录 中的 Password 参数时。

	**请求方法**

		支持 HTTP GET 方法发送请求，这种方式下请求参数需要包含在请求的 URL 中。

	**请求参数**

		每个请求都需要指定要执行的操作，即 Action 参数（例如 StartInstance），
		以及每个操作都需要包含的公共请求参数和指定操作所特有的请求参数。

	**字符编码**

		请求及返回结果都使用 UTF-8 字符集进行编码。

* 2.公共参数
	
	**公共请求参数是指每个接口都需要使用到的请求参数。**

		名称			类型	是否必须	描述
		app_os     		String	是			APP当前操作系统，例如：Android，IOS，PC。
		app_version 	String	是			APP版本当前版本 。
		access_token 	String	是			系统分配的访问token，AccessToken的组成方式：加密(用户id)-加密(用户的token) ，前端不要解密出来。
		format	  		String	否			返回值的类型，支持 JSON 、JSONP。默认为 JSON。
		callback  		String	否			JSONP返回值的Callback字符串，默认为 JSONP 。

	**公共返回参数**

		用户发送的每次接口调用请求，无论成功与否，系统都会返回一个唯一识别码 RequestId 给用户。

* 3.返回结果
	
	**调用 API 服务后返回数据采用统一格式：**
		* JSON返回例子：
	```js
		{
			"c": 0,
			"m": "success",
			"d": {},
			"RequestId": "",
			"comsume": ""
		}
	```
		* JSONP返回例子：
	```js
		JSONP({
			"c": 0,
			"m": "success",
			"d": {},
			"RequestId": "",
			"comsume": ""
		})
	```
		* XML返回例子：



	**公共错误码**
		* 返回的c为 0，代表调用成功。
		* 返回的c不为 0，代表调用失败。

		错误代码	HTTP 状态码		描述
		0			200				请求成功。
		...
		5xx         200             服务器错误

	```php
		/** @var int 成功错误码 */
	const SUCCESS              = 0;

	/** @var int 错误错误码 */
	const FAIL                      = 10000;
	const ACCESS_TOKEN_INVALID      = 10001;
	const PARAM_ERROR               = 10002;
	const NETWORK_ERROR             = 10003;
	const OPERATE_FAILED            = 10004;
	const LIVE_START_ERROR          = 10005;
	const LIVE_END_ERROR            = 10006;
	const USER_FORBID               = 10007;
	const ANCHOR_FORBID             = 10008;
	const ANCHOR_NOT_EXISTS         = 10009;
	const USER_NOT_EXISTS           = 10010;
	const USER_CERTIFICATION_FAIL   = 10011;
	const USER_NOT_CERTIFICATION    = 10012;
	const USER_CERTIFICATION_CHECK  = 10013;
	const USER_IS_CERTIFICATION     = 10014;
	const VERIFY_CODE_ERROR         = 10015;
	const VERIFY_CODE_NOT_EXPIRE    = 10016;
	const ACCOUNT_EXISTS            = 10017;
	const ACCOUNT_NOT_EXISTS        = 10018;
	const ACCOUNT_ERROR             = 10019;
	const PASSWORD_ERROR            = 10020;
	const ACCOUNT_OR_PASSWORD_ERROR = 10021; 
	const LOGIN_ERROR               = 10022;
	const VERIFY_CODE_EXPIRE   		= 10023;
	const USER_COIN_NOT_ENOUGH      = 10024;
	const COMBO_NOT_EXISTS          = 10025;
	const USER_VIP_EXPIRE           = 10026;
	const ANCHOR_FORBID_EXPIRE      = 10027;
	const LIVE_TRAILER_END          = 10028;
	const GIFT_NOT_EXISTS           = 10029;
	const IS_FOLLOW                 = 10030;
	const CANCEL_FOLLOW             = 10031;
	const INVITE_CODE_ERROR         = 10032;
	const INVITE_USER_EXISTS        = 10033;
	const NOT_IS_ROOM_ADMIN         = 10034;
	const SIGNED                    = 10035;
	const USER_NOT_BIND_PHONE       = 10036;
	const PASSWORD_FORMAT_ERROR     = 10037;
	const RECEIVED                  = 10038;
	const USER_HAS_BIND_PHONE       = 10039;
	const USER_PROHIBIT_TALK        = 10040;
	const USER_KICK                 = 10041;
	const USERNAME_EXISTS           = 10042;
	const LIVE_FEE_ERROR            = 10043;
	const BANWORD                   = 10044;

	/** @var array 错误码提示信息 */
	protected static $aError = [
		self::SUCCESS                   => '请求成功',
		self::FAIL                      => '请求失败',
		self::PARAM_ERROR               => '参数错误',
		self::NETWORK_ERROR             => '网络错误',
		self::ACCESS_TOKEN_INVALID      => '登录状态失效',
		self::OPERATE_FAILED            => '操作失败',
		self::BANWORD                   => '包含被禁用的关键字',
		
		self::LIVE_START_ERROR          => '直播已开始',
		self::LIVE_END_ERROR            => '直播已结束',
		self::LIVE_TRAILER_END          => '直播试看已结束',

		self::GIFT_NOT_EXISTS           => '礼物不存在',
		
		self::ANCHOR_FORBID             => '主播已被禁播',
		self::ANCHOR_FORBID_EXPIRE      => '停播期时间',
		self::ANCHOR_NOT_EXISTS         => '主播不存在',
		
		self::USER_FORBID               => '用户被禁',
		self::USER_NOT_EXISTS           => '用户不存在',
		self::USER_CERTIFICATION_FAIL   => '实名认证失败',
		self::USER_COIN_NOT_ENOUGH      => '用户余额不足',
		self::USER_VIP_EXPIRE           => '用户会员已过期',
		self::USER_CERTIFICATION_CHECK  => '实名认证正在审核',
		self::USER_NOT_CERTIFICATION    => '没有实名认证',
		self::USER_IS_CERTIFICATION     => '已实名认证',
		self::VERIFY_CODE_ERROR         => '验证码错误',
		self::VERIFY_CODE_EXPIRE        => '验证码已过期',
		self::VERIFY_CODE_NOT_EXPIRE    => '验证码还没有过期',
		self::ACCOUNT_EXISTS            => '账号已存在',
		self::ACCOUNT_NOT_EXISTS        => '账号不存在',
		self::ACCOUNT_ERROR             => '账号错误',
		self::PASSWORD_ERROR            => '密码错误',
		self::PASSWORD_FORMAT_ERROR     => '密码长度为6-16位',
		self::ACCOUNT_OR_PASSWORD_ERROR => '账号或密码错误',
		self::LOGIN_ERROR               => '登录失败',
		self::COMBO_NOT_EXISTS          => '套餐不存在',
		self::IS_FOLLOW                 => '已关注',
		self::CANCEL_FOLLOW             => '已取消关注',
		self::INVITE_CODE_ERROR         => '邀请码不存在',
		self::INVITE_USER_EXISTS        => '你的上级邀请用户已存在',
		self::INVITE_USER_EXISTS        => '你的上级邀请用户已存在',
		self::NOT_IS_ROOM_ADMIN         => '你不是该房间的管理员',
		self::SIGNED                    => '已签到',
		self::USER_NOT_BIND_PHONE       => '未绑定手机号码',
		self::USER_HAS_BIND_PHONE       => '手机号已被绑定',
		self::RECEIVED                  => '已领取',
		self::USER_PROHIBIT_TALK        => '你已被禁言',
		self::USER_KICK                 => '你已被踢出直播间',
		self::USERNAME_EXISTS           => '用户名已存在',
		self::LIVE_FEE_ERROR            => '直播银币不能超过%d',
	];
	```




GREEN LIVE (绿播)
=================

###绿播服务端系统分成几个子系统，每个子系统是一个独立的系统，可以每个子系统独立部署到不同的服务器中

## **系统架构**

* api.greenlive.com - 接口项目
* www.greenlive.com - web项目
* cli.greenlive.com - 后台脚本项目
* admin.fengchao - 管理后台项目
* h5.greenlive.com - h5项目
* ...