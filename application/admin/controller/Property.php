<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/16
 * Time: 下午2:08
 */
namespace app\admin\controller;

use app\common\model\CompanyServer ;

class Property extends Admin
{
    /*报修服务列表*/
    public function index(){

        $parkid =session("user_auth")['park_id'];
        $list=CompanyServer::where(['park_id'=> $parkid,'type'=>['<',4]])->paginate();

        $this->assign("list",$list);
        return $this->fetch();
    }
    /*保洁服务列表*/
    public function clear(){
        $parkid =session("user_auth")['park_id'];
        $list=CompanyServer::where(['park_id'=> $parkid,'type'=>4])->paginate();

        $this->assign("list",$list);
        return $this->fetch();



    }


}