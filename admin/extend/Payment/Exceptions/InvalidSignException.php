<?php 

/*
 +------------------------------------------------------------------------+
 | Payment                                                                |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | InvalidSignException                                                   |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace Payment\Exceptions;

use Payment\Exceptions\Exception;

class InvalidSignException extends Exception
{
	public $raw;

	public function __construct($message, $code, $raw = '')
	{
		parent::__construct($message, intval($code));
		$this->raw = $raw;
	}
}