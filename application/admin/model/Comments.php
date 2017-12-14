<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2017/1/3
 * Time: 9:39
 */

namespace app\admin\model;

use think\Model;

class Comments extends Model
{
    public function user(){
        return $this->hasOne('User','id','uid');
    }
    public function getUserHeader(){

        return $this->hasOne("WechatUser",'userid','uid')->field('id,name,avatar,header');
    }
}