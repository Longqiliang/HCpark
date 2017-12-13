<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午10:17
 */

namespace app\index\model;


use think\Model;

class ActivityComment extends  Model
{protected  $insert = [

    'create_time'=>NOW_TIME

];

}