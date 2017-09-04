<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/2
 * Time: 下午4:22
 */

namespace app\index\controller;

use app\common\model\ParkRent;
use app\common\model\ParkRoom;
use app\index\model\Park;

class Roomrent extends Base
{
    /*我要租房页面*/
    public function rent()
    {
        $id = input('id');
        $parkId = session("park_id");
        $park = Park::where('id', $parkId)->find();
        $roomInfo = ParkRent::where('id', $id)->find();
        $room = ParkRoom::where('id', $roomInfo['room_id'])->find();
        $data = [
            'position' => $room['build_block'] . $room['room'] . "室",
            'area' => $roomInfo['area'],
            'price' => $roomInfo['price'],
            'park' => $park['name'],
            'address' => $park['address'],
            'moblie' => $park['property_phone'],
            'img' => json_decode($roomInfo['img']),
            'panorama' => json_decode($roomInfo['panorama']),

        ];
        //return dump($data);
        $this->assign('info', json_encode($data));

        return $this->fetch();
    }

    /*租房详细列表*/
    public function rentList()
    {
        $data = [];
        $parkId = session("park_id");
        $build = input('build');
        if ($build) {
            $build = "A";
        }
        $map = ['park_id' => $parkId];
        $parkInfo = Park::where('id', $parkId)->find();
        $parkRent = new ParkRent();
        $list = $parkRent->where($map)->order('id desc')->limit(6)->select();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data[$k] = [
                'img' => json_decode($v['img']),
                'panorama' => json_decode($v['panorama']),
                'area' => $v['area']."㎡",
                'price' => $v['price']."元/㎡·天",
                'name' => $parkInfo['name'],
                'id' => $v['id'],
                'room' => $room['build_block']."幢".$room['room']."室"
            ];
        }
        $this->assign('list', json_encode($data));
        //return dump($data);

        return $this->fetch();
    }

    /*楼盘列表下拉刷新*/
    public function moreList()
    {
        $len = input('lenth');
        $parkId = session("park_id");
        $build = input('build');
        if ($build) {
            $build = "A";
        }
        $map = ['park_id' => $parkId, 'build_block' => $build];
        $parkInfo = Park::where('id', $parkId)->find();
        $parkRent = new ParkRent();
        $list = $parkRent->where($map)->order('id desc')->limit($len, 6)->select();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data[$k] = [
                'img' => json_decode($v['img']),
                'panorama' => json_decode($v['panorama']),
                'area' => $v['area'],
                'price' => $v['price'],
                'name' => $parkInfo['name'],
                'id' => $v['id'],
            ];
        }

        return json_encode($data);


    }

    /*楼盘表*/
    public function housesList()
    {
        $parkRent = new ParkRent();
        $list = $parkRent->order('id desc')->select();
        $roomArray = [];
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $roomArray[$k]['room'] = $room['room'];
            $roomArray[$k]['id'] = $v['id'];
        }
        //return dump($roomArray);
        $this->assign('list', json_encode($roomArray));

        return $this->fetch();

    }


}