<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/12/19
 * Time: 13:39
 */

namespace app\admin\model;


use think\Model;

class CardType extends Model {
    protected $insert = [
        'status' => 1,
    ];
    protected $autoWriteTimestamp = 'datetime';

}