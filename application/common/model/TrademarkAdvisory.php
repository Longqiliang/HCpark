<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/17
 * Time: 上午9:19
 */

namespace app\common\model;


use think\Model;

class TrademarkAdvisory extends  Model
{

    protected  $type =[
        'create_time'=>'strtotime',
        'end_time'=>'strtotime'
    ];
    /**
     * 未完成的数量
     */
     public  static function  getNumforUndone(){
         $parkid = session('user_auth')['park_id'];
         $map['status'] = 0;
         $map['park_id']=$parkid;
         $count = TrademarkAdvisory::where($map)->count();
         return $count;

     }




}