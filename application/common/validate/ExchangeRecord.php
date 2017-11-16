<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/16
 * Time: 上午9:45
 */

namespace app\common\validate;


use think\Validate;

class ExchangeRecord extends  Validate
{

    protected $rule = [
        'title' =>  'require',
        'content'  =>  'require',
        'publisher'  =>  'require',
    ];

    protected $message = [
        'title.require' =>  '请添加标题！',
        'content.require'  =>  '请填写内容！',
        'publisher.require'  =>  '请填写发布人名称！',
    ];


}