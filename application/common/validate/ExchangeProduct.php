<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/16
 * Time: 上午9:45
 */

namespace app\common\validate;


use think\Validate;

class ExchangeProduct extends  Validate
{
    protected $rule = [
        'front_cover'  =>  'require',
        'title' =>  'require',
        'content'  =>  'require',
        'price'  =>  'require',
        'num'  =>  'require',
        'left'=>'require',  ];

    protected $message = [
        'front_cover.require'  =>  '请添加封面图片！',
        'title.require' =>  '请添加商品名称！',
        'content.require'  =>  '请填写商品详情！',
        'price.require'  =>  '请填写商品价格！',
        'num.require'  =>  '请填写商品总数量',
        'left.require'=>'请填写商品的剩余数量'
    ];


}