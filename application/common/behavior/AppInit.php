<?php
namespace app\common\behavior;

use think\Request;
use think\Route;

class AppInit
{
    public function run(&$param){
        // 站点初始化
        $this->initialization();
        // 注册路由
        if (config('url_route_on')) {
            $this->router();
        }
    }
    private function router(){
        $router_rule['test']='index/Index/test';
        $router_rule['test2']='index/Index/test2';
        Route::rule($router_rule);
    }

    /**
     * 变量
     */
    private function initialization() {
        // 定义废除的一些常量
        define('NOW_TIME', $_SERVER['REQUEST_TIME']);

        $request = Request::instance();
        define('REQUEST_METHOD', $request->method());
        define('IS_GET', REQUEST_METHOD == 'GET' ? true : false);
        define('IS_POST', REQUEST_METHOD == 'POST' ? true : false);
        define('IS_PUT', REQUEST_METHOD == 'PUT' ? true : false);
        define('IS_DELETE', REQUEST_METHOD == 'DELETE' ? true : false);
        define('IS_AJAX', $request->isAjax());
        define('__EXT__', $request->ext());
    }
    
}