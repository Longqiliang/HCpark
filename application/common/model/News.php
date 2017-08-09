<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/21
 * Time: 15:16
 */

namespace app\common\model;

use think\Model;

class News extends Model
{
    protected $insert = [
        'status' => 1,
        'create_time' => NOW_TIME,
        'update_time' => NOW_TIME,
        'create_user' => UID,
    ];

    protected $update = ['update_time' => NOW_TIME];
}