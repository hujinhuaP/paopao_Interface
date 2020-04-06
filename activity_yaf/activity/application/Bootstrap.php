<?php
/**
 * @name Bootstrap
 * @author root
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    public function _initConfig() {
		//把配置保存起来
		$arrConfig = Yaf_Application::app()->getConfig();
		Yaf_Registry::set('config', $arrConfig);
	}

	public function _initPlugin(Yaf_Dispatcher $dispatcher) {
		//注册一个插件
		$objSamplePlugin = new SamplePlugin();
		$dispatcher->registerPlugin($objSamplePlugin);
	}

	public function _initRoute(Yaf_Dispatcher $dispatcher) {
		//在这里注册自己的路由协议,默认使用简单路由
	}

    public function _initView(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的view控制器，例如smarty,firekylin
//        Yaf_Dispatcher::getInstance()->disableView(); //因为要用smarty引擎作为渲染，所以关闭yaf自身的自动渲染功能
        Yaf_Loader::import( APPLICATION_PATH ."/application/library/smarty-3.1.33/libs/Adapter.php");//引入yaf与smarty的适配文件
        $smarty = new Smarty_Adapter(null, Yaf_Application::app()->getConfig()->smarty);//实例化引入文件的类
        Yaf_Dispatcher::getInstance()->setView($smarty);//渲染模板
    }
}
