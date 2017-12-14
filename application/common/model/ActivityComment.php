<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午10:49
 */

namespace app\common\model;


use think\Model;

class ActivityComment extends Model
{
    protected $type = [

        'create_time' => 'strtotime'
    ];
    public function activity()
    {
        return $this->hasOne('activity', 'id', 'activity_id');
    }


}