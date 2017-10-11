<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/18
 * Time: 下午2:59
 */

namespace app\index\model;


use think\Model;

class AdvertisingRecord extends Model
{
    protected $dateFormat="Y-m-d";
    public function user()
    {


        return $this->hasOne('WechatUser', 'userid', 'create_user');

    }


}