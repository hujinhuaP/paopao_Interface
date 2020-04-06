<?php 

require('QcloudUploader.php');

date_default_timezone_set("Asia/chongqing");
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' && isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] != '') {
   return;
}

function getParams($key)
{
	if (isset($_GET[$key])) {
		return $_GET[$key];
	} elseif (isset($_POST[$key])) {
		return $_POST[$key];
	} else {
		return NULL;
	}
}

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);

$QcloudUploader = new QcloudUploader($CONFIG);
$action = getParams('action').'Action';
if (method_exists($QcloudUploader, $action)) {
	$QcloudUploader->$action();
} else {
	die('action error');
}