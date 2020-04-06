<?php
	/******************************************************
	 ***************       微信红包操作     ****************
	 **************** author:Lee 2018-08-22 ***************
	 *
	 * scene_id说明：
	 * 场景ID，可选，发放红包使用场景，红包金额大于200时必传。
	 * PRODUCT_1:商品促销
	 * PRODUCT_2:抽奖
	 * PRODUCT_3:虚拟物品兑奖
	 * PRODUCT_4:企业内部福利
	 * PRODUCT_5:渠道分润
	 * PRODUCT_6:保险回馈
	 * PRODUCT_7:彩票派奖
	 * PRODUCT_8:税务刮奖
	 *
	 * 返回值说明：
	 * json格式取result结果success表示成功、fail表示失败、如果返回空值默认成功
	 *****************************************************/

	class Reward
	{

		public function __construct(){
			$this->company_name = '泡泡科技';														//公司名称
			$this->mch_id 		= '1511147451';														//商户号
			$this->wxappid 		= 'wxdc8664fd2c614cb7';												//公众帐户appid
			$this->key 			= 'eM7AW3fQSUQZhl1nwvR35I2PElAqvUnc';
			//支付密钥
			$this->send_url 	= 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';	//提交地址
		}

		public function init($reward_param)
		{
			//此处写校验逻辑
			$result = $this->_verifyMoney($reward_param['money']);
			if($result != 'success') {
				return $this->_returnVal($result);
			}

			//调用红包
			$reward_param['total_num'] = 1;				//提现人数
			$reward_param['scene_id'] = 'PRODUCT_4';	//类型 固定值
			$reward_param['wishing'] = $reward_param['act_name'] = $reward_param['remark'] = '提现红包';		//附加提示
			$result = $this->_redBag($reward_param);
			if ($result == 'success') {
				return $this->_returnVal('success');
			}else{
				return $this->_returnVal($result);
			}
		}

		/**
		 * 微信红包
		 */
		private function _redBag($param)
		{
			$senddata = [
				'nonce_str' => $this->_randStr(),			//32位随机字符串
				'mch_billno' => $param['orderid'],
				'mch_id' => $this->mch_id,
				'wxappid' => $this->wxappid,
				'send_name' => $this->company_name,
				're_openid' => $param['openid'],
				'total_amount' => $param['money'],			//付款金额 单位：分
				'total_num' => $param['total_num'],			//发放总人数
				'wishing' => $param['wishing'],				//祝福语
				'client_ip' => $this->_getServiceIP(),		//接口服务器ip
				'act_name' => $param['act_name'],			//活动名称
				'remark' => $param['remark'],				//备注
				'scene_id' => $param['scene_id']
			];
			$senddata['sign'] = $this->_createSign($senddata);        //签名
			$post_data = $this->_xmlTemplate($senddata);
			$response = $this->_sendWechatData($this->send_url, $post_data);
			//$result = $GLOBALS["HTTP_RAW_POST_DATA"];
			$postObj = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
			$result_code = $postObj->result_code;
			//file_put_contents("/data/wwwroot/lebo/charge/redbag.txt", print_r($postObj, true) . "\r\n\r\n", FILE_APPEND);
			if ($result_code == 'SUCCESS') {
				return 'success';
			} else {
				return $postObj->err_code_des;
			}
		}

		/**
		 * 生成加密sign
		 */
		private function _createSign($array)
		{
			ksort($array);
			$result = '';
			foreach ($array as $k => $v) {
				if ($result != '') {
					$result .= '&';
				}
				$result .= $k . '=' . $v;
			}
			$result .= '&key=' . $this->key . '';
			return strtoupper(md5($result));
		}

		/**
		 * 向微信服务器发送数据模板
		 * $data = array('test'=>'testdata','test1'=>'test1data');
		 */
		private function _xmlTemplate($data)
		{
			$postdata = '';
			$postdata .= '<xml>';
			$postdata .= '<sign><![CDATA[' . $data['sign'] . ']]></sign>';
			$postdata .= '<mch_billno><![CDATA[' . $data['mch_billno'] . ']]></mch_billno>';
			$postdata .= '<mch_id><![CDATA[' . $data['mch_id'] . ']]></mch_id>';
			$postdata .= '<wxappid><![CDATA[' . $data['wxappid'] . ']]></wxappid>';
			$postdata .= '<send_name><![CDATA[' . $data['send_name'] . ']]></send_name>';
			$postdata .= '<re_openid><![CDATA[' . $data['re_openid'] . ']]></re_openid>';
			$postdata .= '<total_amount><![CDATA[' . $data['total_amount'] . ']]></total_amount>';
			$postdata .= '<total_num><![CDATA[' . $data['total_num'] . ']]></total_num>';
			$postdata .= '<wishing><![CDATA[' . $data['wishing'] . ']]></wishing>';
			$postdata .= '<client_ip><![CDATA[' . $data['client_ip'] . ']]></client_ip>';
			$postdata .= '<act_name><![CDATA[' . $data['act_name'] . ']]></act_name>';
			$postdata .= '<remark><![CDATA[' . $data['remark'] . ']]></remark>';
			$postdata .= '<scene_id><![CDATA[' . $data['scene_id'] . ']]></scene_id>';
			$postdata .= '<nonce_str><![CDATA[' . $data['nonce_str'] . ']]></nonce_str>';
			$postdata .= '</xml>';
			return $postdata;
		}

		/**
		 * 向微信服务器发送数据
		 * $url = 'http://www.test.com/test/test';
		 * $post_data = array('test'=>'testdata','test1'=>'test1data');
		 */
		private function _sendWechatData($url, $post_data, $second = 30, $aHeader = array())
		{
			$ch = curl_init();
			//超时时间
			curl_setopt($ch, CURLOPT_TIMEOUT, $second);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//这里设置代理，如果有的话
			//curl_setopt($ch,CURLOPT_PROXY, '10.206.30.98');
			//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

			//以下两种方式需选择一种
			$certpath = '/data/wwwroot/lebo/charge/wechat/reward_cert/';
			//第一种方法，cert 与 key 分别属于两个.pem文件
			//默认格式为PEM，可以注释
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLCERT, $certpath . 'apiclient_cert.pem');
			//默认格式为PEM，可以注释
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLKEY, $certpath . 'apiclient_key.pem');

			//第二种方式，两个文件合成一个.pem文件
			//curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');

			if (count($aHeader) >= 1) {
				curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
			}

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			$data = curl_exec($ch);
			if ($data) {
				curl_close($ch);
				return $data;
			} else {
				$error = curl_errno($ch);
				echo "call faild, errorCode:$error\n";
				curl_close($ch);
				return false;
			}
		}

		/**
		 * 返回值
		 */
		private function _returnVal($result = '', $code = 0)
		{
			return json_encode(
				array(
					'result' => $result,
					'code' => $code
				)
			);
			exit();
		}

		/**
		 * 生成随机字符串
		 * $length (int)长度
		 */
		private function _randStr($length = 32)
		{
			$str = array(
				'1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
				'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j',
				'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't',
				'u', 'v', 'w', 'x', 'y', 'z'
			);
			$result = '';
			for ($i = 0; $i < $length; $i++) {
				$result .= $str[mt_rand(0, (count($str) - 1))];
			}
			return $result;
		}

		/**
		 * 获取服务器IP
		 */
		private function _getServiceIP($domain = '127.0.0.1')
		{
			return gethostbyname($_SERVER['SERVER_NAME']);
		}

		/**
		 * 校验金额合法性
		 */
		private function _verifyMoney($money)
		{
			if($money<100){
				return '提现金额不能少于1元';
			}
			if($money>49900){
				return '提现金额不能超过499元';
			}
			return 'success';
		}

	}
