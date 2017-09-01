<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/1
 * Time: 下午5:21
 */
namespace app\common\model;

use think\Model;

class ServiceInformation extends Model
{
    protected $insert = [
        'status' => 1,
        'create_time' => NOW_TIME,
        'update_time' => NOW_TIME,
    ];

    protected $update = ['update_time' => NOW_TIME];
}