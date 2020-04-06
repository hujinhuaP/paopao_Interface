<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | WapGateway                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Providers\Wechat;

use Payment\Providers\Wechat\Support;

class WapGateway extends Gateway
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
        $payload['trade_type'] = $this->getTradeType();
        $data = $this->preOrder('pay/unifiedorder', $payload);
        $url = is_null($this->config['return_url']) ? $data['mweb_url'] : $data['mweb_url'].
                        '&redirect_url='.urlencode($this->config['return_url']);
        return $this->buildPayHtml($url);
    }
    /**
     * Get trade type config.
     *
     * @return string
     */
    protected function getTradeType()
    {
        return 'MWEB';
    }

    /**
     * buildPayHtml 
     * 
     * @param  styring $url
     * @return string
     */
    protected function buildPayHtml($url)
    {
        return sprintf('<!DOCTYPE html>
                <html>
                    <head>
                        <meta charset="UTF-8" />
                        <meta http-equiv="refresh" content="0;url=%1$s" />
                        <title>Redirecting to %1$s</title>
                    </head>
                    <body>
                        Redirecting to <a href="%1$s">%1$s</a>.
                    </body>
                </html>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'));
    }
}