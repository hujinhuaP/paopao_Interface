<?php

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | Pay                                                                    |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Contracts;

interface PayProviderInterface
{
	/**
	 * pay
	 * 
	 * @param  string $gateway
	 * @param  array $params
	 * @return PayProviderInterface
	 */
    public function pay($gateway, $params);

    /**
     * find    Query an order.
     * 
     * @param  array $order
     * @return 
     */
    public function find($order);

    /**
     * refund  Refund an order.
     * 
     * @param  array $order
     * @return
     */
    public function refund($order);

    /**
     * cancel  Cancel an order.
     * 
     * @param  string|array $order
     * @return
     */
    public function cancel($order);

    /**
     * close   Close an order.
     * 
     * @param  string|array $order
     * @return 
     */
    public function close($order);

    /**
     * verify  Verify a request.
     * 
     * @return 
     */
    public function verify();

    /**
     * success Echo success to server.
     *
     * @return 
     */
    public function success();
}