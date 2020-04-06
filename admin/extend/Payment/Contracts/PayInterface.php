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

interface PayInterface
{
	/**
	 * pay
	 * 
	 * @param  string $gateway
	 * @param  array  $payload
	 * @return
	 */
    public function pay($gateway, array $payload);
}