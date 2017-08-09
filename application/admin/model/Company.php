<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/6/22
 * Time: 下午3:57
 */

namespace app\admin\model;

use think\Config;
use think\Model;

class Company extends Model
{
    protected $autoWriteTimestamp = true;
    protected $insert = [
        'status' => 1,
        'reg_time' => NOW_TIME,
        'reg_ip','password'
    ];

    /**
     * 正确登入的信息处理
     * @param $id
     * @return bool
     */
    public function login($id) {
        $anchor = $this->get($id);
//        if(!$anchor || $anchor['status'] != 1) {
//            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
//            return false;
//        }

        /* 更新登录信息 */
        $data = array(
            'login' => array('exp', '`login`+1'),
            'last_login_time' => time(),
            'last_login_ip'   => get_client_ip(1),
        );
        $this->save($data, ['id' => $anchor['id']]);

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'id'       => $anchor['id'],
            'username' => empty($anchor['nickname']) ? $anchor['username']: $anchor['nickname'],
            'header'   => $anchor['header'],
        );

        session('company_auth', $auth);
        session('company_auth_sign', data_auth_sign($auth));

        return true;
    }

    protected function setRegIpAttr(){
        return request()->ip(1);
    }

    protected function setPasswordAttr($vaule){
        return think_ucenter_md5($vaule, Config::get('company_auth_key'));
    }
}