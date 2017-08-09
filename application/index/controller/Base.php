<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/8
 * Time: 下午2:53
 */

namespace app\index\controller;


use think\Controller;
use think\Loader;
use wechat\TPWechat;
use app\index\model\WechatUser as WechatUserModel;
use app\admin\model\Config as ConfigModel;

class Base extends Controller
{
    protected function _initialize(){
        session('userId', '18969030101');//测试
//        session('thirdUserId', '1001');

        /* 读取数据库中的配置 */
        $config = cache('db_config_data');
        if(!$config) {
            $configModel = new ConfigModel();
            $config = $configModel->lists();
            cache('db_config_data',$config);
        }
        config($config); //添加配置

        session('requestUri', 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
        $userId = session('userId');

        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('mall'));
        if(empty($userId)) {
            $redirect_uri = config("login_url");
            $url = $weObj->getOauthRedirect($redirect_uri);
            $this->redirect($url);
        }

        // 2获取jsapi_ticket
//        $jsApiTicket = cache('jsapiticket');
//        if(empty($jsApiTicket) || $jsApiTicket=='') {
//            cache('jsapiticket', $weObj->getJsTicket(), 7000); // 官方7200,设置7000防止误差
//        }
    }

    /**
     * 获取企业号签名
     */
    public function jssdk(){
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('wechat'));
        $url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $jsSign = $weObj->getJsSign($url);
        $this->assign("jsSign", $jsSign);
    }

}