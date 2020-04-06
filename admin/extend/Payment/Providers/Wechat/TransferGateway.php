<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | TransferGateway                                                        |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Providers\Wechat;

use Payment\Providers\Wechat\Support;

class TransferGateway extends Gateway
{

    /**
     * Pay an order.
     *
     * @param string $endpoint
     * @param array  $payload
     *
     * @return Collection
     */
    public function pay($endpoint, array $payload)
    {
        $payload['mch_appid'] = $payload['app_id'];
        $payload['mchid'] = $payload['mch_id'];
        $payload['spbill_create_ip'] = Support::getServerIP();
        unset($payload['appid'], $payload['mch_id'], $payload['trade_type'], $payload['notify_url']);
        return Support::requestApi(
            'mmpaymkttransfers/promotion/transfers',
            $payload,
            $this->config['key'],
            $this->config['cert_client'],
            $this->config['cert_key'],
            $this->config['rootca']
        );
    }
    /**
     * Get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return '';
    }
}