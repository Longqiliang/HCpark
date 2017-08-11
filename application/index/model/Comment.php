<?php
/**
 * Created by PhpStorm.
 * User: aion
 * Date: 2017/5/10
 * Time: 下午9:15
 */

namespace app\index\model;


use think\Model;

class Comment extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'status' => 1
    ];
    protected $dateFormat="Y-m-d";
}