<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/22
 * Time: 下午3:25
 */

namespace app\index\model;


use think\Model;

class FunctionRoomRecord extends  Model
{
    protected $dateFormat="Y-m-d";
    public function user()
    {


        return $this->hasOne('WechatUser', 'userid', 'create_user');

    }


}