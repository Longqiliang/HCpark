<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午10:17
 */

namespace app\index\model;


use think\Model;

class Activity extends Model
{
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
        $list = $this->where(['park_id' => $park_id, 'status' => ['gt', 1]])->order('start_time asc')->select();
        $data = array();
        foreach ($list as $value){
            $map=[
                'id'=>$value['id'],
                'status'=>$value['status'],
                'name'=>$value['name'],
                'start_time'=>$value['start_time'],
                'end_time'=>$value['end_time'],
                'front_cover'=>$value['front_cover']
            ];
            array_push($data,$map);

        }


        return $data;
    }


}