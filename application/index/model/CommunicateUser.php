<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 上午9:25
 */

namespace app\index\model;


use think\Model;

class CommunicateUser extends  Model
{
    protected $insert = [
        'create_time' => NOW_TIME,

    ];
}