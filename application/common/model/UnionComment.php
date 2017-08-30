<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/30
 * Time: 上午9:21
 */
namespace app\common\model;

use think\Model;

class UnionComment extends Model
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