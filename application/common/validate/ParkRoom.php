<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/2
 * Time: 上午10:27
 */
namespace app\common\validate;
use think\Validate;
class ParkRoom extends Validate{

    protected $rule = [
        'floor'  =>  'require',
        'room' => 'require',

    ];

    protected $message = [
        'floor.require' => '楼层必须添加',
        'room.require' => '房间号必须添加',

    ];
}