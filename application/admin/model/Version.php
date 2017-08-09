<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2016/11/28
 * Time: 下午2:09
 */

namespace app\admin\model;

use think\Model;

class Version extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
        'branch' => 'master'
    ];
}