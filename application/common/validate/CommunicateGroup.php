<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/7
 * Time: 上午11:23
 */

namespace app\common\validate;


use think\Validate;

class CommunicateGroup extends  Validate
{
    protected $rule = [
        'group_name' =>  'require|max:40',
        'content' => 'require|max:120',
        'park_id' => 'require',
        'status' =>'require'
        ];

    protected $message = [
        'group_name.require' => '群名称必须添加',
        'group_name.max' => '群名称最大40个字符',
        'content.require' => '群简介必须添加',
        'content.max' => '群简介最大120个字符',
        'park_id' => '所属园区必须添加',
        'status' =>'状态必须添加'
        ];
}