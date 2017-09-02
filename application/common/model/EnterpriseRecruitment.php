<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/2
 * Time: 上午9:56
 */
namespace app\common\model;

use think\Model;

class EnterpriseRecruitment extends Model
{
    protected $insert = [
        'status' => 1,
        'create_time' => NOW_TIME,
        'update_time' => NOW_TIME,
    ];

    protected $update = ['update_time' => NOW_TIME];
}