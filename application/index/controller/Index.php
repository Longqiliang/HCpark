<?php

namespace app\index\controller;

use app\index\model\WechatDepartment;
use app\index\model\WechatUser;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;
use app\index\model\ExchangePoint;
use app\common\model\ParkCompany;
use  app\common\behavior\Service;

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
            $wechatUser = new WechatUser();
            $weObj = new TPWechat(config('register'));
            $mobile = input('mobile');
            $name = input('name');
            $gender = input('gender');
            $department = input('department');
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
            $tableUser = $newUser;
            $tableUser['department'] = $department;
            $tableUser['company_address'] = input('room');
            unset($tableUser['enable']);
            unset($tableUser['userid']);
            $is_user = $wechatUser->where('userid', $mobile)->find();
            if ($is_user) {
                $tableUser['status'] = 1;
                $wechatUser->save(['status' => 1, 'company_address' => input('room')], ['userid' => $mobile]);
            } else {
                //注册后获得1个积分，重复注册不会重复获得；
                $tableUser['score'] = 2;
                $wechatUser->save($tableUser);
                //并记录
                $point = new  ExchangePoint();
                $server = new  Service();
                $park_id = $server->findParkid($department);
                $department = (int)$department;
                $map = [
                    'userid' => $mobile,
                    'content' => '注册登录',
                    'score' => 2,
                    'create_time' => time(),
                    'park_id' => $park_id,
                    'status'=>0,
                    'top_company'=>json_encode([$department]),
                    'type'=>2
                ];
                $point->save($map);

            }
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
            $parentid = 4;
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
            $park = WechatDepartment::where('id', $park_id)->find();
            $parkinfo = [
                'name' => $park['name'],
                'park_id' => $park['id']
            ];
            $this->assign('department', json_encode($departmentlist));
            $this->assign('park', json_encode($parkinfo));
            return $this->fetch();
        }

    }

    //验证
    public function verification()
    {
        $department = input('department');
        $park_company = new ParkCompany();
        $company = $park_company->where('company_id', $department)->find();
        if ($company['company_code'] == input('company_code')) {
            $this->success("验证码成功！");

        } else {

            $this->error("验证码错误！");
        }
    }

    //推荐关注
    public function recommend()
    {
        $data= input('');
        $this->assign('park_id', $data['park_id']);
        return $this->fetch();
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

    //缺省页
    public function defaultpage()
    {
        return $this->fetch();
    }
}
