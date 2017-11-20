<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/20
 * Time: 上午9:12
 */

namespace app\index\model;


use think\Model;

class TrademarkAdvisory extends  Model
{


    protected  $type =[
        'create_time'=>'strtotime',
        'end_time'=>'strtotime'
    ];


}