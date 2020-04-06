<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | AppGateway                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Providers\Wechat;

use Payment\Providers\Wechat\Support;

class AppGateway extends Gateway
{

	/**
	 * pay 
	 * 
	 * @param  string $gateway 
	 * @param  array  $payload 
	 * @return string
	 */
    public function pay($endpoint, array $payload)
    {
        $payload['appid'] = $this->config['app_id'];
        $payload['trade_type'] = $this->getTradeType();
        $data = $this->preOrder('pay/unifiedorder', $payload);

        $payRequest = [
            'appid'     => $payload['appid'],
            'partnerid' => $payload['mch_id'],
            'prepayid'  => $data['prepay_id'],
            'timestamp' => strval(time()),
            'noncestr'  => Support::random(),
            'package'   => 'Sign=WXPay',
        ];
        $payRequest['sign'] = Support::generateSign($payRequest, $this->config['key']);
        return json_encode($payRequest);
    }
    /**
     * Get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'APP';
    }
}