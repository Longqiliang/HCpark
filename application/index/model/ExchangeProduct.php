<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/15
 * Time: 上午9:30
 */

namespace app\index\model;
use think\Model;
use app\index\model\ExchangeRecord;

class ExchangeProduct extends  Model
{
    public  function getAllProductOrderById($park_id){
        $product = $this->where(['status' =>0,'park_id'=>$park_id])->order('id desc')->select();
        // 重新排序 将兑光的排在最后
        foreach($product as $key => $value){
            if ($value->left == 0){
                $temp = $value;
                unset($product[$key]);
                array_push($product,$temp);
            }
        }
        return   $product;
    }

    public  function getPoductInfoById($id){

        $product = $this->where(['id' =>$id])->find();

        return   $product;
    }


    public  function getLockProduct($id){

        $product = $this->where(['id' =>$id])->lock(true)->find();

        return   $product;
    }

    public  function  updateLeft($id,$num){
        $result = $this->where(['id' =>$id ])->update(['left' => $num]);
        return $result;



    }

}