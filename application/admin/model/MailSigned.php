<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/7/8
 * Time: 下午4:55
 */

namespace app\admin\model;

use think\Model;

class MailSigned extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'effective_time' => 60 * 60 * 24 * 2, // 默认2天
        'status'=>1,
    ];
}