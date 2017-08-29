<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/29
 * Time: 上午10:43
 */
namespace app\common\model;

use think\Model;

class PartyComment extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'status' => 1
    ];
    protected $dateFormat="Y-m-d";

    public function wechatuser(){

        return $this->hasOne("WechatUser","userid","user_id");
    }






}