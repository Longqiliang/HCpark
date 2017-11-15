<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/15
 * Time: 上午9:28
 */

namespace app\index\model;


use think\Model;

class ExchangeRecord extends  Model
{
//查询总的消耗的积分
    public  function  getRecord($uid){
        $score=0;
        $map =['userid'=>$uid,
               'status'=>['neq',-1]
            ];
        $Record = $this->where($map)->select();  // 获取兑换过得积分
        foreach($Record as $value){
            $score += $value['content'];
        }
        return $score;
    }
    //查询该用户兑换过的积分记录
    public  function  getRecordList($uid){
        $map =['userid'=>$uid,
            'status'=>['neq',-1]
        ];
        $Record = $this->where($map)->select();  // 获取兑换过得积分
        return $Record;
    }

    //查询兑换详情
    public  function  getRecordInfo($id){
        $Record = $this->where('id',$id)->find();  // 获取兑换过得积分
        return $Record;

    }


    /**
     * @return \think\model\Relation
     */
    public function productInfo()
    {
        return $this->hasOne('ExchangeProduct', 'id', 'product_id');
    }



}