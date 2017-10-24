<?php
namespace app\index\controller;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;
class Index  extends  Controller
{
    /** 测试专用 页面地址列表 **/
    public function index(){

        return $this->fetch();
    }
    /**
     * 注册入口，登记用户手机号码等资料（注册到居民组）
     * @return mixed
     */
    public function register() {

        if (IS_POST) {
            Loader::import('wechat\TPQYWechat', EXTEND_PATH);
            $weObj = new TPWechat(config('register'));
            $mobile = input('mobile');
            $name = input('name');
            $gender = input('gender');
            $department = input('departmentId');

            if (empty($mobile)) { $this->error('手机号码不能空'); }
            if (empty($name)) { $this->error('用户名不能空'); }

            $newUser = [
                "userid" => $mobile,
                "name"=> $name,
                "mobile" => $mobile,
                'department'=> [$department],
                "gender" => $gender,
                "enable" => 1,
            ];
            $result = $weObj->createUser($newUser);
//            var_dump($weObj->errCode.'||'.$weObj->errMsg);
//            var_dump($result);
            if($result && $result['errcode'] == 0) {
                // 跳转到微信插件二维码
                return  $this ->success('恭喜您，注册成功！');
            } else {
                if($weObj->errCode == 60104) {
                    // 手机号码已经存在
                    return    $this ->success('该手机已注册！');
                } else {

                    return   $this->error('关注错误:'.$weObj->errCode.'||'.$weObj->errMsg);
                }
            }
        } else {

            return $this->fetch();
        }
    }

//二维码
    public  function  registercode(){
        $this->assign('info',$_SERVER['HTTP_HOST']);

        return $this->fetch();

    }

//二维码
    public  function  code(){
        $this->assign('info',$_SERVER['HTTP_HOST']);

        return $this->fetch();

    }
}
