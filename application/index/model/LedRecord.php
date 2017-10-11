<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/23
 * Time: 下午3:57
 */

namespace app\index\model;


use think\Model;

class LedRecord extends  Model
{

    public function user()
    {


        return $this->hasOne('WechatUser', 'userid', 'create_user');

    }
}