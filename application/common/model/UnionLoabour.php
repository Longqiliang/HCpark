<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/28
 * Time: 下午2:34
 */
namespace app\common\model;

use think\Model;

class UnionLoabour extends Model
{
    protected $insert = [
        'status' => 1,
        'create_time' => NOW_TIME,
        'update_time' => NOW_TIME,
        'create_user' => UID,
    ];

    protected $update = ['update_time' => NOW_TIME];
}