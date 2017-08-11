<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/9
 * Time: 上午9:21
 */

namespace app\index\model;
use com\wechat\TPQYWechat;
use think\Model;

class Feedback extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,

    ];
    protected $dateFormat="Y-m-d";
    public  function  userName() {

        return $this->hasOne("WechatUser","userid","create_user");
    }
}