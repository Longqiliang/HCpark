<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 17:55
 */
namespace app\index\controller;

use app\index\model\WechatUser;
use app\index\model\CompanyApplication;
//企业服务
class Service extends Base{


  //服务列表
    public function index() {
        $park_id=session('park_id');
        $app= new  CompanyApplication();
        $list=$app->where('park_id',$park_id)->select();
       $this->assign('list',$list);
        return $this->fetch();

    }

    //选择服务
    public function  onCheck(){
        $data=input('');
        $user_id = session('userId');
        $UserModel = new  WechatUser();
        $user=$UserModel->where('userid',$user_id)->find();
        $info['name']=$user['name'];
        $info['mobile']=$user['mobile'];
        $info['company']=$user->departmentName->name;
        $this->assign('user',$info);
        return $this->fetch($data['path']);

    }

}