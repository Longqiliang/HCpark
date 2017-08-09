<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\Mail;
use app\admin\model\MailSigned;
use app\admin\model\Member;
use app\admin\model\Sms;
use app\admin\model\Config;
use think\Controller;

/**
 * 后台公共控制器
 */
class Base extends Controller
{
    protected function _initialize() {
        /* 读取数据库中的配置 */
        $config = cache('db_config_data');
        if(!$config) {
            $configModel = new Config();
            $config = $configModel->lists();
            cache('db_config_data',$config);
        }
        config($config); //添加配置
    }
    /**
     * 后台用户登录
     */
    public function login($username = null, $password = null, $verify = null) {
        if(IS_POST) {
            $member = Member::where('username', input('username'))->find();
            if(!empty($member) && $member['status']){
                $memberMobile = new Member();
                /* 验证用户密码 */
                if(think_ucenter_md5($password, config('uc_auth_key')) === $member['password']){
                    $memberMobile->login($member['id']);
                    $this->success('验证成功', url('index/index'));
                } else {
                    $this->error('帐号或者密码错误!');
                }
            } else {
                $this->error('帐号禁用或者不存在');
            }
        } else {
            if(is_login()){
                $this->redirect('Index/index');
            }else{
                /* 读取数据库中的配置 */
                $config	= cache('db_config_data');
                if(!$config) {
                    $config	= Config::all();
                    cache('db_config_data',$config);
                }
                config($config); //添加配置

                return $this->fetch();
            }
        }
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            $member = new Member();
            $member->logout();
            //$this->success('退出成功！', Url::build('login'));
            $this->redirect('login');
        } else {
            $this->redirect('login');
        }
    }

    /**
     * 提交密码找回,发送确认邮件
     * @return mixed
     */
    public function forgotPassword() {
        if(IS_POST) {
            $member = Member::where('email', input('email'))->find();
            if($member) {
                $code = random(32, 'all');
                $data = [
                    'user_id' => $member['id'],
                    'user_type' => 1,
                    'type' => 2,
                    'signed' => $code
                ];
                $forgotPassword = new MailSigned();
                $result = $forgotPassword->save($data);
                if($result) {
                    // 发送邮件
                    $mail = new Mail();
                    $send = $mail->sendForgotPassword($member['email'], $code);
                    if($send) {
                        $this->success('发送成功', url('Base/login'));
                    } else {
                        $this->error('邮件错误, 发送失败!');
                    }
                } else {
                    $this->error($forgotPassword->getError());
                }
            } else {
                $this->error('没有该注册邮箱');
            }
        } else {
            return $this->fetch();
        }
    }

    public function reset() {
        if(IS_POST) {
            $password = input('password');
            $rePassword = input('rePassword');

            if($password == '') {
                $this->error('密码不能为空');
            }
            if($password != $rePassword) {
                $this->error('密码和确认密码不一致,请重新输入');
            }

            $forgotPassword = MailSigned::where(['signed'=>input('code')])->find();
            // 更新密码
            $member = new Member();
            $result = $member->validate('member.password')->save(['password'=>$password], ['id'=>$forgotPassword['user_id']]);
            if($result) {
                // 使找回你密码signed无效
                MailSigned::where(['user_id'=>$forgotPassword['user_id'], 'user_type'=>1])->delete();
                $this->success('重设成功', 'Base/login');
            } else {
                $this->error($member->getError());
            }
        } else {
            $map = [
                'signed' => input('code'),
                'status' => 1,
            ];
            $forgotPassword = MailSigned::where($map)->find();
            if(empty($forgotPassword) || $forgotPassword['create_time'] + $forgotPassword['effective_time'] <= NOW_TIME){
                $this->error('链接不存在或者已失效!', url('Base/login'));
            }
            $this->assign('code', input('code'));
            return $this->fetch();
        }
    }

    public function active() {
        $map = [
            'signed' => input('code'),
            'status' => 1,
            'type' => 1
        ];

        $mailSigned = MailSigned::where($map)->find();
        if(empty($mailSigned) || $mailSigned['create_time'] + $mailSigned['effective_time'] <= NOW_TIME){
//            $this->error('链接不存在或者已失效!', url('Base/active'));
            $this->assign('active', 0);
        } else {
            Member::where('id', $mailSigned['member_id'])->update(['status'=>1]);
            $this->assign('active', 1);
        }

        return $this->fetch();
    }

    public function register() {
        return $this->fetch();
    }

    public function error_404() {
        return $this->fetch('404');
    }

    public function error_500() {
        return $this->fetch('500');
    }

    public function email() {
        return $this->fetch();
    }

    public function smsCallback() {
        $xmlData = file_get_contents("php://input");
        $data = (array)simplexml_load_string($xmlData);
        if(!empty($data[0])) {
            Sms::where('sms_id', $data['smsid'])->update(['status' => $data['status']]);
        }

        return xml(['retcode'=>0], 200, [], ['root_node'=>'response']);
    }

    public function skinconfig() {
        return $this->fetch();
    }

    public function mdskin() {
        return $this->fetch();
    }
}
