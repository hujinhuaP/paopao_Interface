<?php 

/*
 +------------------------------------------------------------------------+
 | Green Live                                                             |
 +------------------------------------------------------------------------+
 | Copyright (c) 2017-2017 Green Live Team (https://www.greenlive.com)    |
 +------------------------------------------------------------------------+
 | 默认控制器                                                             |
 +------------------------------------------------------------------------+
 |                                      |
 +------------------------------------------------------------------------+
 */

namespace app\http\controllers;

/**
* IndexController
*/
class IndexController extends ControllerBase
{
	
	public function indexAction()
	{
		echo 'Welcome !';
	}

    public function testAction()
    {
        $strArr = $this->config->application->zego->app_sign;
        $strArr = explode(',',$strArr);
        $str2 = '';
        $str2Arr = [];
        foreach ($strArr as $item){
            $item = hexdec($item);
            $str2Arr[] = $item;
        }
        echo implode(',',$str2Arr);die;

        echo '<pre>';
        echo '转成字符串<br/>';
        var_dump($str2);

        echo '转成Byte<br/>';
        $data = $this->_getByte($str2);
        var_dump($data);


        die;
	}

}