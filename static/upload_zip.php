<?php
//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
date_default_timezone_set("Asia/chongqing");
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');

include "Uploader.class.php";

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);

$config = array(
    "pathFormat" => $CONFIG['filePathFormat'],
    "maxSize" => $CONFIG['fileMaxSize'],
    "allowFiles" => ['.zip'],
);

$fieldName = 'file';
$base64 = "upload";
/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

/* 返回数据 */
$ret = $up->getFileInfo();

if ($ret['state'] == 'SUCCESS') {
    $result = json_encode([
        'code' => 1,
        'msg' => 'success',
        'data' => [
            'url'      => $CONFIG['cdnurl'].$ret['url'],
            'title'    => (string)$ret['title'],
            'original' => (string)$ret['original'],
            'type'     => (string)$ret['type'],
            'size'     => (string)$ret['size'],
        ],
        'url' => $CONFIG['cdnurl'].$ret['url'],
        'wait' => 3,
    ], JSON_UNESCAPED_UNICODE);
} else {
    $result = json_encode([
        'code' => 10000,
        'msg' => $ret['state'],
        'data' => [],
    ], JSON_UNESCAPED_UNICODE);
}

/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state'=> 'callback参数不合法'
        ), JSON_UNESCAPED_UNICODE);
    }
} else {
    echo $result;
}