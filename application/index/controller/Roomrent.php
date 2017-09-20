<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/2
 * Time: 下午4:22
 */

namespace app\index\controller;

use app\common\model\ParkIntention;
use app\common\model\ParkRent;
use app\common\model\ParkRoom;
use app\common\model\PeopleRent;
use app\index\model\Park;
use think\Db;
use think\Image;

class Roomrent extends Base
{
    /*我要租房页面*/
    public function rent()
    {
        $parkId = session("park_id");
        $park = Park::where('id', $parkId)->find();
        $roomId = input("room_id");
        $rentId = input("rent_id");
        if ($roomId) {
            $roomInfo = ParkRent::where('room_id', $roomId)->find();
            $room = ParkRoom::where('id', $roomId)->find();
        } else {
            $roomInfo = ParkRent::where('id', $rentId)->find();
            $room = ParkRoom::where('id', $roomInfo['room_id'])->find();
        }
        $data = [
            'position' => $room['build_block'] . $room['room'] . "室",
            'area' => $roomInfo['area'] . "㎡",
            'price' => $roomInfo['price'] . "元/㎡·天",
            'park' => $park['name'],
            'address' => $park['address'],
            'moblie' => $park['property_phone'],
            'img' => json_decode($roomInfo['img']),
            'imgs' => json_decode($roomInfo['imgs']),
            'panorama' => $roomInfo['panorama'],
            'rent_id' => $roomInfo['id'],

        ];



        $a=array();
        foreach ($data['img'] as $v){
           $v=session('requserUrl').$v;
           array_push($a,$v);
        }
        $data['img']=$a;

        $b=array();
        foreach ($data['imgs'] as $v2){
            $v2=session('requserUrl').$v2;
            array_push($b,$v2);
        }
        $data['imgs']=$b;
        /* if ($data['img']) {
             foreach ($data['img'] as $k1 => $v1) {
                 if (is_file(PUBLIC_PATH . $v1)) {
                     $path = str_replace(".", "_s.", $v1);
                     $image = Image::open(PUBLIC_PATH . $v1);
                     $image->thumb(355, 188)->save(PUBLIC_PATH . $path);
                     $data['imgs'][$k1] = $path;
                 } else {
                     $data['imgs'][$k1] = $data['img'][$k1];
                 }
             }
         }*/


        $this->assign('info', json_encode($data));

        return $this->fetch();
    }

    /*租房详细列表*/
    /*public function rentList()
    {
        $data = [];
        $data1 = [];
        $type = input('type');
        $parkId = session("park_id");
        $map = ['park_id' => $parkId, "build_block" => "A"];
        $parkInfo = Park::where('id', $parkId)->find();
        $parkRent = new ParkRent();
        $list = $parkRent->where($map)->order('id desc')->limit(6)->select();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data[$k] = [
                'img' => json_decode($v['img']),
                'panorama' => $v['panorama'],
                'area' => $v['area'] . "㎡",
                'price' => $v['price'] . "元/㎡·天",
                'name' => $parkInfo['name'],
                'id' => $v['id'],
                'room' => $room['build_block'] . "幢" . $room['room'] . "室"
            ];
            if ($data[$k]['img']) {
                foreach ($data[$k]['img'] as $k1 => $v1) {
                    $path = str_replace(".", "_s.", $v1);
                    $image = Image::open(PUBLIC_PATH . $v1);
                    $image->thumb(170, 120)->save(PUBLIC_PATH . $path);
                    $data[$k]['img'][$k1] = $path;
                }
            }
        }
        $map1 = ['park_id' => $parkId, "build_block" => "B"];
        $list1 = $parkRent->where($map1)->order('id desc')->limit(6)->select();
        foreach ($list1 as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data1[$k] = [
                'img' => json_decode($v['img']),
                'panorama' => $v['panorama'],
                'area' => $v['area'] . "㎡",
                'price' => $v['price'] . "元/㎡·天",
                'name' => $parkInfo['name'],
                'id' => $v['id'],
                'room' => $room['build_block'] . "幢" . $room['room'] . "室"
            ];
            if ($data[$k]['img']) {
                foreach ($data[$k]['img'] as $k1 => $v1) {
                    if (is_file(PUBLIC_PATH . $v1)) {
                        $path = str_replace(".", "_s.", $v1);
                        $image = Image::open(PUBLIC_PATH . $v1);
                        $image->thumb(170, 120)->save(PUBLIC_PATH . $path);
                        $data[$k]['img'][$k1] = $path;
                    }
                }
            }
        }
        $parkName = $parkInfo['name'];
        $resArr = array_merge(["$parkName A幢" => $data], ["$parkName B幢" => $data1]);
        $this->assign("type", $type);
        $this->assign('list', json_encode($resArr));


        return $this->fetch();
    }*/

    /*楼盘列表下拉刷新*/
    public function moreList()
    {
        $len = input('length');
        $parkId = input("park_id");
        $build = input('build');
        if (!$build) {
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
                    'img' => json_decode($v['imgs']),
                    'panorama' => $v['panorama'],
                    'area' => $v['area'],
                    'price' => $v['price'],
                    'name' => $parkInfo['name'],
                    'id' => $v['id'],
                    'room' => $room['build_block'] . "幢" . $room['room'] . "室"
                ];
            }

            return ['code' => 1, 'data' => json_encode($data)];
        } else {

            return ['code' => 0, 'data' => "没有更多数据了"];
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
        if ($parkId == 3) {
            $common = "（公共区域)";
        } else {
            $common = "";
        }
        $parkInfo = Park::where('id', $parkId)->find();
        $parkRoom = new ParkRoom();
        $map = [
            'park_id' => $parkId,
            'build_block' => "A",
        ];
        $list = $parkRoom->where($map)->distinct(true)->field('floor')->order('floor desc')->select();
        foreach ($list as $k => $v) {
            $floor[$k] = $v['floor'];
        }
        foreach ($floor as $k => $v) {
            $roomList = $parkRoom->where(['floor' => $v, 'build_block' => "A", 'del' => 0])->order("room asc")->select();
            foreach ($roomList as $k1 => $v1) {
                $res = ParkRent::where(['room_id' => $v1['id'], 'manage' => 0, 'status' => 0])->find();
                if (!$res) {
                    $status = false;
                } else {
                    $status = true;
                }

                $roomArray[$k][$k1] = ['room' => $v1['room'], 'empty' => $status, 'department_id' => $v1['company_id'], 'id' => $v1['id']];
            }

        }
        foreach ($floor as $k => $v) {
            $newArr[$k]['floor'] = $v;
            $newArr[$k]['combine'] = false;
            $newArr[$k]['rooms'] = $roomArray[$k];
        }
        $map1 = [
            'park_id' => $parkId,
            'build_block' => "B",
        ];
        $list1 = $parkRoom->where($map1)->distinct(true)->field('floor')->order('floor desc')->select();
        foreach ($list1 as $k => $v) {
            $floor1[$k] = $v['floor'];
        }
        foreach ($floor1 as $k => $v) {
            $roomList1 = $parkRoom->where(['floor' => $v, 'build_block' => "B", 'del' => 0])->order("room asc")->select();
            foreach ($roomList1 as $k1 => $v1) {
                $res = ParkRent::where(['room_id' => $v1['id'], 'manage' => 0, 'status' => 0])->find();
                if (!$res) {
                    $status1 = false;
                } else {
                    $status1 = true;
                }
                $roomArray1[$k][$k1] = ['room' => $v1['room'], 'empty' => $status1, 'department_id' => $v1['company_id'], 'id' => $v1['id']];
            }

        }
        foreach ($floor1 as $k => $v) {
            $newArr1[$k]['floor'] = $v;
            $newArr1[$k]['combine'] = false;
            $newArr1[$k]['rooms'] = $roomArray1[$k];
        }


        //rentlist
        $data = [];
        $data1 = [];
        $type = input('type');
        $parkId = session("park_id");
        $map = ['park_id' => $parkId, "build_block" => "A", 'status' => 0, 'manage' => 0];
        $parkInfo = Park::where('id', $parkId)->find();
        $parkRent = new ParkRent();
        $list = $parkRent->where($map)->order('id desc')->limit(6)->select();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data[$k] = [
                'img' => json_decode($v['imgs']),
                'panorama' => $v['panorama'],
                'area' => $v['area'] . "㎡",
                'price' => $v['price'] . "元/㎡·天",
                'name' => $parkInfo['name'],
                'id' => $v['id'],
                'room' => $room['build_block'] . "幢" . $room['room'] . "室"
            ];

        }
        $map1 = ['park_id' => $parkId, "build_block" => "B", 'status' => 0, 'manage' => 0];
        $list1 = $parkRent->where($map1)->order('id desc')->limit(6)->select();
        foreach ($list1 as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $data1[$k] = [
                'img' => json_decode($v['imgs']),
                'panorama' => $v['panorama'],
                'area' => $v['area'] . "㎡",
                'price' => $v['price'] . "元/㎡·天",
                'name' => $parkInfo['name'],
                'id' => $v['id'],
                'room' => $room['build_block'] . "幢" . $room['room'] . "室"
            ];
        }
        $parkName = $parkInfo['name'];
        $list = [
            " A幢" => ['houselist' => $newArr, 'rentlist' => $data],
            " B幢" => ['houselist' => $newArr1, 'rentlist' => $data1],
        ];
 /*       $list3 = [
        " 1幢" => ['houselist' => $newArr, 'rentlist' => array()],
        " 2幢" => ['houselist' => $newArr1, 'rentlist' => $data1],
    ];*/
        $list1 = ["$parkName" => $list];
        $this->assign('type', $type);
        $this->assign('commonArea', $common);
        $this->assign('list', json_encode($list1));


        return $this->fetch();


    }

    /*预约信息*/
    public function peopleRent()
    {
        if (IS_POST) {
            $data = input('post.');
            $data['status'] = 1;
            $data['park_id'] = session("park_id");
            $people = new PeopleRent();
            $res = $people->save($data);
            if ($res) {
                $msg = "您已申请成功;稍后我们的工作人员会电话联系您！";
                $this->success('提交成功', '', $msg);
            } else {

                $this->error("提交失败");
            }
        }

        return $this->fetch();
    }

    /*全景照片*/
    public function panorama()
    {
        $link = input('link');
        $this->assign('link', json_encode($link));

        return $this->fetch();
    }

    /*拼数组*/
    public function gaoshiqing()
    {
        $ParkRoom = new ParkRoom();
        $subQuery = $ParkRoom->where(['company_id' => ['>', 0], 'build_block' => "A"])->order('floor desc')->buildSql();
        //dump($subQuery );exit;
        //echo $subQuery;
        $list = Db::table($subQuery . ' a')->distinct(true)->field('a.company_id')->select();
        foreach ($list as $k => $v) {
            $arr[$k] = $v['company_id'];
        }
        //dump($arr);
        foreach ($arr as $k => $v) {
            $room = ParkRoom::where('company_id', $v)->order('room asc')->select();
            foreach ($room as $k1 => $v1) {
                $rooms[$k][$k1] = ['floor' => $v1['floor'], 'room' => $v1['room'], 'deparment_id' => $v];
                $roomsss[$k][$k1] = $v1['room'];
            };
        }
        //$rooms用来循环生成楼盘表；

        //接下来需要一个算法生成区间房间号
        foreach ($roomsss as $k => $v) {
            $roomsss[$k] = $this->getArea($v);
        }
        foreach ($rooms as $k => $v) {
            $rooms[$k][0]['number'] = $roomsss[$k];
        }
        foreach ($rooms as $k => $v) {
            $rooms[$k] = $v[0];
        }
        for ($i = 1; $i < 14; $i++) {
            foreach ($rooms as $k => $v) {
                if ($v['floor'] == $i) {
                    /*$finallArr[$i] = [
                        'floor' =>$i,
                        'rooms' =>['room'=>$v['room'],'department_id'=>$v['deparment_id'],'number'=>$v['number']],
                    ];*/
                    $finallArr[$i]['floor'] = $i;
                    $finallArr[$i]['rooms'][$k] = ['room' => $v['room'], 'department_id' => $v['deparment_id'], 'number' => $v['number']];
                    sort($finallArr[$i]['rooms']);
                }

            }
        }
        rsort($finallArr);
        echo json_encode($finallArr);


    }

    /*数组生成区间*$arr是一个一维数组*/
    function getArea($arr)
    {
        // $arr = ["201","202","204","207"];
        $news = [];
        $string = "";
        for ($k = 1; $k < count($arr); $k++) {
            if ($arr[$k] - 1 != ($arr[$k - 1])) {
                $news[] = $k;
            }
        }
        //return dump($news);
        if ($news) {
            array_unshift($news, 0);
            array_push($news, count($arr));
            foreach ($news as $k => $v) {
                if ($k < count($news) - 1) {
                    $newArr[] = array_slice($arr, $news[$k], $news[$k + 1] - $news[$k]);
                }

            }
            foreach ($newArr as $k => $v) {
                if (count($v) > 1) {
                    $string .= reset($v) . "-" . end($v) . ",";
                } else {
                    $string .= reset($v) . ",";
                }
            }

            return $string;
        } else {
            if (count($arr) > 1) {

                $string .= reset($arr) . "-" . end($arr);
            } else {

                $string .= $arr[0];
            }

            return $string;
        }

    }

    /*租房意向申请*/
    public function intention(){
        if (IS_POST){
            $data = input('post.');
            $data['create_time'] = time();
            $data['park_id'] = session('park_id');
            $data['status'] = 0;

            $parkIntention = new ParkIntention();
            $res = $parkIntention->allowField(true)->save($data);
            if ($res){

                $this->success('添加成功');
            }else{

                $this->error("添加失败");
            }
        }else{

            return $this->fetch();
        }

    }

//    记录
    public function record()
    {
        return $this->fetch();
    }
    /*楼房信息*/
    public function gaoshiqings()
    {
        $parkId = session('park_id');
        $parkInfo = Park::where('id', $parkId)->find();
        $parkName = $parkInfo['name'];
        $parkRoom = new ParkRoom();
        $map = [
            'park_id' => $parkId,
            'build_block' => "A",
            'company_id' => 0,
            'del' => 0
        ];
        $list = $parkRoom->where($map)->distinct(true)->field('floor')->order('floor desc')->select();
        foreach ($list as $k => $v) {
            $floor[$k] = $v['floor'];
        }
        //dump($floor);
        foreach($floor as $k=>$v){
            $map['floor'] = $v ;
            $roomList[$k]['floor'] = $v;
            $roomList[$k]['rooms'] = $parkRoom->where($map)->order('room  asc')->field('room,id')->select();

        }
        $area = $this->gaoshiqing();
        for ($i=13;$i>0;$i--){
            foreach($area as $k=>$v){
                if ($v['floor'] == $i){
                    $newArr[] =['floor'=>$i,'rooms'=>$v['rooms']];
                }
            }
            foreach($roomList as $k=>$v){
                if ($v['floor'] == $i){
                    $newArr[] =['floor'=>$i,'rooms'=>$v['rooms']];
                }
            }
        }

        foreach ($newArr as $k=>$v){
            if ($k ==(count($newArr)-1)){
               break;
            }
            if ($newArr[$k]['floor'] ==$newArr[$k+1]['floor']){
                    $newArr[$k]['rooms'] = array_merge($newArr[$k]['rooms'],$newArr[$k+1]['rooms']);
                }
        }
        foreach ($newArr as $k=>$v){
            if ($k ==(count($newArr)-1)){
                break;
            }
            if ($newArr[$k]['floor'] ==$newArr[$k+1]['floor']){
                $newArr1[] = $k+1;
            }
        }
        foreach ( $newArr1 as $k=>$v){
            unset($newArr[$v]);
        }
        $asp = "";
        foreach ($newArr as $k=>$v){
           foreach ($v['rooms'] as $k1=>$v1){

               if ($k1 == (count($v['rooms'])-1)){break;}

               if ($v['rooms'][$k1]['room'] > $v['rooms'][$k1+1]['room']){
                   $asp = $v['rooms'][$k1+1]['room'] ;
                   $v['rooms'][$k1+1]['room'] = $v['rooms'][$k1]['room'];
                   $v['rooms'][$k1]['room'] =$asp;
               }
           }
        }
        echo json_encode($newArr);







    }













}