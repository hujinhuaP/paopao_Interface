<?php

//上传配置
return [
    /**
     * 上传地址,默认是本地上传
     */
    'uploadurl'           => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?action=uploadAdminImg', APP_URL_PREFIX),
//    'editoruploadurl'     => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?__url=' . urlencode(sprintf('http://%s/live/ajax/json?json=', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')), APP_URL_PREFIX),
    'editoruploadurl'     => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?__url=',APP_URL_PREFIX) . urlencode(sprintf('http://%s/live/ajax/json?json=', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')),
    'uploadurl_zip'       => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?action=uploadAdminZip', APP_URL_PREFIX),
    'uploadurl_gif'       => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?action=uploadAdminGif', APP_URL_PREFIX),
    'uploadurl_mp3'       => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?action=uploadAdminMusic', APP_URL_PREFIX),
    'uploadurl_local_img' => sprintf('http://%sstatic.sxypaopao.com/upload_img.php', APP_URL_PREFIX),
    'uploadurl_video'     => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?action=uploadAdminVideo', APP_URL_PREFIX),
    'uploadurl_svga'      => sprintf('http://%sstatic.sxypaopao.com/qcloud.php?action=uploadAdminSvga', APP_URL_PREFIX),

    /**
     * CDN地址
     */
    'cdnurl'              => sprintf('http://%s/', $_SERVER['HTTP_HOST'] ?? 'http://static.sxypaopao.com'),
    /**
     * 文件保存格式
     */
    'savekey'             => '/uploads/{year}{mon}{day}/{filemd5}{.suffix}',
    /**
     * 最大可上传大小
     */
    'maxsize'             => '10mb',
    /**
     * 可上传的文件类型
     */
    'mimetype'            => '*',
    /**
     * 是否支持批量上传
     */
    'multiple'            => FALSE,
];
