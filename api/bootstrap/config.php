<?php 

use Phalcon\Config;

/**
 * Read the configuration
 */
$sConfigPath     = APP_PATH.'app/configs';
$sConfigDevPath  = APP_PATH.'app/configs/dev';
$aConfigFiles    = scandir($sConfigPath);
$aConfigDevFiles = scandir($sConfigPath);
$aConfig         = [];

foreach ($aConfigFiles as &$sFile) {
    $sExt = '.'.pathinfo($sFile, PATHINFO_EXTENSION);
    if ($sExt === '.php') {
        $aConfig[str_replace($sExt, '', $sFile)] = require $sConfigPath.'/'.$sFile;
    }
}

if (!empty($aConfigDevFiles)) {
    foreach ($aConfigDevFiles as &$sFile) {
        $sExt = '.'.pathinfo($sFile, PATHINFO_EXTENSION);
        if ($sExt === '.php') {
            $aConfig[str_replace($sExt, '', $sFile)] = require $sConfigPath.'/'.$sFile;
        }
    }
}

return new Config($aConfig);