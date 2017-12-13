<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午10:49
 */

namespace app\common\model;


use think\Model;

class Activity extends Model{
    protected $type = [
        'end_time' => 'strtotime',
        'start_time' => 'strtotime'
    ];

    public function user()
    {

        return $this->hasMany('ActivityComment', 'activity_id', 'id');

    }


    public function getListbyParkid()
    {

        $park_id = session('park_id');
        $list = $this->where(['park_id' => $park_id, 'status' => ['gt', 0]])->order('start_time asc')->select();
        return $list;
    }

}