<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * QQ: 2205446834@qq.com
 * Date: 2017/8/9
 * Time: 14:48
 */

namespace app\index\model;

use think\Model;
class WechatDepartment extends Model
{
    protected $dateFormat="Y-m-d";
    public  function  findallfartherbychild($deparment,$sd){

        array_unshift($sd,$deparment['id']);
        if($deparment['parentid']!=0){
            $parent=WechatDepartment::where('id',$deparment['parentid'])->find();
            /*  array_unshift($sd,$deparment['id']);*/
            return   $this->findallfartherbychild($parent,$sd);
        }
        else {
            return $sd;
        }
    }

    public  function room(){

       return $this->hasMany('ParkRoom','company_id','id');

    }



}


