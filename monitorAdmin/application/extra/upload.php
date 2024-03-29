<?php

//上传配置
return [
    /**
     * 上传地址,默认是本地上传
     */
    'uploadurl' => 'http://static.sxypaopao.com/qcloud.php?action=uploadAdminImg',
    'editoruploadurl' => 'http://static.sxypaopao.com/qcloud.php?__url='.urlencode(sprintf('http://%s/live/ajax/json?json=', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']: '')),
    'uploadurl_zip' => 'http://static.sxypaopao.com/qcloud.php?action=uploadAdminZip',
    'uploadurl_gif' => 'http://static.sxypaopao.com/qcloud.php?action=uploadAdminGif',
    'uploadurl_mp3' => 'http://static.sxypaopao.com/qcloud.php?action=uploadAdminMusic',
    'uploadurl_local_img' => 'http://static.sxypaopao.com/upload_img.php',
    'uploadurl_video' => 'http://static.sxypaopao.com/qcloud.php?action=uploadAdminVideo',

    /**
     * CDN地址
     */
    'cdnurl'    => sprintf('http://%s/', $_SERVER['HTTP_HOST'] ?? 'http://static.sxypaopao.com'),
    /**
     * 文件保存格式
     */
    'savekey'   => '/uploads/{year}{mon}{day}/{filemd5}{.suffix}',
    /**
     * 最大可上传大小
     */
    'maxsize'   => '10mb',
    /**
     * 可上传的文件类型
     */
    'mimetype'  => '*',
    /**
     * 是否支持批量上传
     */
    'multiple'  => false,
];
