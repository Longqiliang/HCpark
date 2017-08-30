<?php
namespace app\index\controller;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;
class Partymanage extends Base
{
    /** 园区管理首页 **/
    public function index(){

        return $this->fetch();
    }

    /** 园区统计 **/
    public function statistics(){

        return $this->fetch();
    }
}
