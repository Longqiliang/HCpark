<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/8/4
 * Time: 上午11:07
 */

namespace app\admin\controller;

use EasyWeChat\Foundation\Application;
use app\common\model\ParkRoom;

class Service
{
    public function index() {
        $app = new Application(config('wechat'));
        $server = $app->server;

        $server->setMessageHandler(function ($message) {
            $openId = $message->FromUserName; // 用户的 openid
            $type = $message->MsgType; // 消息类型：event, text....

            return "您好！欢迎关注我!".$openId;
        });
        $response = $server->serve();
        $response->send();


    }


    public  function  test(){

        $ParkRoom = new ParkRoom();
        $re = $ParkRoom->getFloorInfo(3);
        echo json_encode($re);
    }
}