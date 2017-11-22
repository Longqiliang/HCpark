<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/15
 * Time: 上午9:28
 */

namespace app\index\model;


use think\Model;
use app\index\model\ExchangeProduct;

class ExchangeRecord extends Model
{

    //查询该用户兑换过的积分记
    public function getRecordList($uid)
    {
        $map = ['userid' => $uid,
            'status' => ['neq', -1]
        ];
        $Record = $this->where($map)->order("status asc ,create_time asc ")->select();
        foreach ($Record as $value) {
            $value['front_cover'] = isset($value->productInfo->front_cover) ? $value->productInfo->front_cover : "";
            $value['title'] = isset($value->productInfo->title) ? $value->productInfo->title : "";
        }

        return $Record;
    }
    //查询兑换详情
    public function getRecordInfo($id)
    {
        $Record = $this->where('id', $id)->find();
        $Record['front_cover'] = isset($Record->productInfo->front_cover) ? $Record->productInfo->front_cover : "";
        $Record['title'] = isset($Record->productInfo->title) ? $Record->productInfo->title : "";
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