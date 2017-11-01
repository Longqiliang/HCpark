<?php

namespace app\index\controller;

use app\index\model\WechatDepartment;
use app\index\model\WechatUser;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;

class Index extends Controller
{


    /** 测试专用 页面地址列表 **/
    public function index()
    {

        return $this->fetch();
    }

    /**
     * 注册入口，登记用户手机号码等资料（注册到居民组）
     * @return mixed
     */
    public function register()
    {
        if (IS_POST) {
            Loader::import('wechat\TPQYWechat', EXTEND_PATH);
            $weObj = new TPWechat(config('register'));
            $mobile = input('mobile');
            $name = input('name');
            $gender = input('gender');
            $department=input('department');
            if (empty($mobile)) {
                $this->error('手机号码不能空');
            }
            if (empty($name)) {
                $this->error('用户名不能空');
            }

            $newUser = [
                "userid" => $mobile,
                "name" => $name,
                "mobile" => $mobile,
                'department' => [$department],
                "gender" => $gender,
                "enable" => 1,
            ];
            $result = $weObj->createUser($newUser);
//            var_dump($weObj->errCode.'||'.$weObj->errMsg);
//            var_dump($result);
            $tableUser =$newUser;
            $tableUser['department']=$department;
            $tableUser['company_address']=input('room');
            WechatUser::save($tableUser);

            if ($result && $result['errcode'] == 0) {


                // 跳转到微信插件二维码
                return $this->success('恭喜您，注册成功！');
            } else {
                if ($weObj->errCode == 60104) {
                    // 手机号码已经存在
                    return $this->success('该手机已注册！');
                } else {

                    return $this->error('关注错误:' . $weObj->errCode . '||' . $weObj->errMsg);
                }
            }
        } else {
            $park_id = input('park_id');
            if ($park_id == 3) {

                $parentid = 4;

            } elseif ($park_id == 80) {
                $parentid = 92;
            }
            $departmentlist = WechatDepartment::where('parentid', $parentid)->select();
            foreach ($departmentlist as $key => $value) {

                $list = array();
                if (isset($value->room)) {
                    foreach ($value->room as $value) {
                        array_push($list, $value['room']);

                    }
                    $departmentlist[$key]['roomlist'] = $list;
                    unset($departmentlist[$key]['room']);
                }
            }
            $park=WechatDepartment::where('id',$park_id)->find();
            $parkinfo=[
                'name'=>$park['name'],
                'park_id'=>$park['id']
            ];
            $this->assign('department', json_encode($departmentlist));
            $this->assign('park', $parkinfo);
            return $this->fetch();
        }

    }


//二维码
    public function registercode()
    {
        $this->assign('info', $_SERVER['HTTP_HOST']);

        return $this->fetch();

    }

//二维码
    public function code()
    {
        $this->assign('info', $_SERVER['HTTP_HOST']);

        return $this->fetch();

    }
}
