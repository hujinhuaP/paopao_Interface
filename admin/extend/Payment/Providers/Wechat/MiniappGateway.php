<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | MiniappGateway                                                         |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Providers\Wechat;

use Payment\Providers\Wechat\Support;

class MiniappGateway extends MpGateway
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
        $payload['appid'] = $this->config['miniapp_id'];
        $payload['trade_type'] = $this->getTradeType();
        return parent::pay($endpoint, $payload);
    }
}