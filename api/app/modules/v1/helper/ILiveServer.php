<?php

namespace app\helper;

/*
 +------------------------------------------------------------------------+
 | C Live                                                                 |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 C Live Team (https://xxxxxxxxxx.com)           |
 +------------------------------------------------------------------------+
 | This source file is subject to the ...                                 |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */
interface ILiveServer
{
    /**
     * playUrl 获取播放地址
     *
     * @date    2017-08-07T18:47:36+0800
     *
     * @version 0.1
     *
     * @param   string                   $format [description]
     *
     * @return  string
     */
    public function playUrl($format='');

    /**
     * pushUrl 获取推流地址
     *
     * @date    2017-08-07T18:47:56+0800
     *
     * @version 0.1
     *
     * @return  string
     */
    public function pushUrl();

}