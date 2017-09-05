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
use app\common\model\PeopleRent;
use app\index\model\Park;

class Roomrent extends Base
{
    /*我要租房页面*/
    public function rent()
    {
        $id = input('id');
        $parkId = session("park_id");
        $park = Park::where('id', $parkId)->find();
        $roomInfo = ParkRent::where('room_id', $id)->find();
        $room = ParkRoom::where('id', $id)->find();
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
        if ($data['img']) {
            foreach ($data['img'] as $k => $v) {
                $small_img = $this->getThumb($v, 375, 188);
                $data['img'][$k] = $small_img;
            }
        }
        if ($data['panorama']) {
            foreach ($data['panorama'] as $k => $v) {
                $small_img = $this->getThumb($v, 375, 188);
                $data['panorama'][$k] = $small_img;
            }
        }
        //return dump($data);
        $this->assign('info', json_encode($data));

        return $this->fetch();
    }

    /*租房详细列表*/
    public function rentList()
    {
        $data = [];
        $data1 = [];
        $parkId = session("park_id");
        $map = ['park_id' => $parkId, "build_block" => "A"];
        $parkInfo = Park::where('id', $parkId)->find();
        $parkRent = new ParkRent();
        $list = $parkRent->where($map)->order('id desc')->limit(6)->select();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data[$k] = [
                'img' => json_decode($v['img']),
                'panorama' => json_decode($v['panorama']),
                'area' => $v['area'] . "㎡",
                'price' => $v['price'] . "元/㎡·天",
                'name' => $parkInfo['name'],
                'id' => $v['id'],
                'room' => $room['build_block'] . "幢" . $room['room'] . "室"
            ];
            if ($data[$k]['img']) {
                foreach ($data[$k]['img'] as $k1 => $v1) {
                    $small_img = $this->getThumb($v1, 170, 120);
                    $data[$k]['img'][$k1] = $small_img;
                }
            }
            if ($data[$k]['panorama']) {
                foreach ($data[$k]['panorama'] as $k1 => $v1) {
                    $small_img = $this->getThumb($v1, 170, 120);
                    $data[$k]['panorama'][$k1] = $small_img;
                }
            }
        }
        $map1 = ['park_id' => $parkId, "build_block" => "B"];
        $list1 = $parkRent->where($map1)->order('id desc')->limit(6)->select();
        foreach ($list1 as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data1[$k] = [
                'img' => json_decode($v['img']),
                'panorama' => json_decode($v['panorama']),
                'area' => $v['area'] . "㎡",
                'price' => $v['price'] . "元/㎡·天",
                'name' => $parkInfo['name'],
                'id' => $v['id'],
                'room' => $room['build_block'] . "幢" . $room['room'] . "室"
            ];
            if ($data1[$k]['img']) {
                foreach ($data1[$k]['img'] as $k1 => $v1) {
                    $small_img = $this->getThumb($v1, 170, 120);
                    $data1[$k]['img'][$k1] = $small_img;
                }
            }
            if ($data1[$k]['panorama']) {
                foreach ($data1[$k]['panorama'] as $k1 => $v1) {
                    $small_img = $this->getThumb($v1, 170, 120);
                    $data1[$k]['panorama'][$k1] = $small_img;
                }
            }
        }
        //return dump($data);
        $this->assign('name', $parkInfo['name']);
        $this->assign('list', json_encode($data));
        $this->assign('list1', json_encode($data1));

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
        if ($list) {
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
                if ($data[$k]['img']) {
                    foreach ($data[$k]['img'] as $k1 => $v1) {
                        $small_img = $this->getThumb($v1, 100, 100);
                        $data[$k]['img'][$k1] = $small_img;
                    }
                }
                if ($data[$k]['panorama']) {
                    foreach ($data[$k]['panorama'] as $k1 => $v1) {
                        $small_img = $this->getThumb($v1, 100, 100);
                        $data[$k]['panorama'][$k1] = $small_img;
                    }
                }
            }

            $this->success(['code' => 1, 'data' => json_encode($data)]);
        } else {

            $this->error(['code' => 0, 'data' => "没有更多数据了"]);
        }

    }

    /*楼盘表*/
    public function housesList()
    {
        $floor = [];
        $floor1 = [];
        $newArr = [];
        $newArr1 = [];
        $parkId = session('park_id');
        $parkInfo = Park::where('id', $parkId)->find();
        $parkName = $parkInfo['name'];
        $parkRoom = new ParkRoom();
        $map = [
            'park_id' => $parkId,
            'build_block' => "A",
        ];
        $list = $parkRoom->where($map)->distinct(true)->field('floor')->select();
        foreach ($list as $k => $v) {
            $floor[$k] = $v['floor'];
        }
        foreach ($floor as $k => $v) {
            $roomList = $parkRoom->where(['floor' => $v, 'build_block' => "A", 'del' => 0])->order("room asc")->select();
            foreach ($roomList as $k1 => $v1) {
                if ($v1['company_id']) {
                    $status = false;
                } else {
                    $status = true;
                }
                $roomArray[$k][$k1] = ['room' => $v1['room'], 'empty' => $status, 'department_id' => $v1['company_id'], 'id' => $v1['id']];
            }

        }
        foreach ($floor as $k => $v) {
            $newArr[$k]['floor'] = $v;
            $newArr[$k]['rooms'] = $roomArray[$k];
        }
        $map1 = [
            'park_id' => $parkId,
            'build_block' => "B",
        ];
        $list1 = $parkRoom->where($map1)->distinct(true)->field('floor')->select();
        foreach ($list1 as $k => $v) {
            $floor1[$k] = $v['floor'];
        }
        foreach ($floor1 as $k => $v) {
            $roomList1 = $parkRoom->where(['floor' => $v, 'build_block' => "B", 'del' => 0])->order("room asc")->select();
            foreach ($roomList1 as $k1 => $v1) {
                if ($v1['company_id']) {
                    $status1 = false;
                } else {
                    $status1 = true;
                }
                $roomArray1[$k][$k1] = ['room' => $v1['room'], 'empty' => $status1, 'department_id' => $v1['company_id'], 'id' => $v1['id']];
            }

        }
        foreach ($floor1 as $k => $v) {
            $newArr1[$k]['floor'] = $v;
            $newArr1[$k]['rooms'] = $roomArray1[$k];
        }
        $resArr = array_merge(["$parkName A" => $newArr], ["$parkName B" => $newArr1]);
        $this->assign('list', json_encode($resArr));

        return $this->fetch();


    }

    /*预约信息*/
    public function peopleRent()
    {
        $data = input('post.');
        $people = new PeopleRent();
        $res = $people->save($data);
        if ($res) {

            $this->success('提交成功');
        } else {

            $this->error("提交失败");
        }

    }


}