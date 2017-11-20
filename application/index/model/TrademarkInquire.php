<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/20
 * Time: 上午9:11
 */

namespace app\index\model;


use think\Model;

class TrademarkInquire extends  Model
{

    protected $type = [
        'create_time' => 'strtotime',
        'end_time' => 'strtotime'
    ];


    public function user()
    {

        return  $this->hasOne('WechatUser', 'userid', 'userid');

    }

}