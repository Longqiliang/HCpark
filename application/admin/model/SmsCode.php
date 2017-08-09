<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/8/16
 * Time: 上午11:00
 */

namespace app\admin\model;

use think\Model;

class SmsCode extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'status' => 0
    ];
}