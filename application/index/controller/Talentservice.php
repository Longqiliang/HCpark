<?php
namespace app\index\controller;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;
use app\common\model\CompanyContract;
class Talentservice extends Base
{
    /**人才管理**/
    /** 首页 **/
    public function index(){

        return $this->fetch();
    }
    public function detail(){

        return $this->fetch();
    }

}
