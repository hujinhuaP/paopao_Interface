<?php
/**
 * This file is part of the Smarty package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Load and register Smarty Autoloader
 */
include_once dirname(__FILE__) . '/Autoloader.php';

Smarty_Autoloader::register(true);
