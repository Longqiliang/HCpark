<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/30
 * Time: 下午2:17
 */
namespace app\common\validate;
use think\Validate;

class ParkProduct extends Validate{

    protected $rule = [
        'name' =>  'require',
        'detail' => 'require',
        'content'  =>  'require|min:20',
    ];

    protected $message = [
        'name.require' => '名称必须添加',
        'detail.require' => '简介必须添加',
        'content.min' => '内容字数偏少，请继续添加',
    ];






}