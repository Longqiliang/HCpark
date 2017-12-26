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
use app\index\model\WechatUser;
use think\Image;
use app\index\controller\Service;

class Roomrent extends Base
{
    /*我要租房页面*/
    public function rent()
    {
        $roomId = input("room_id");
        $room = ParkRoom::where('id', $roomId)->find();
        $parkId = $room['park_id'];
        $park = Park::where('id', $parkId)->find();
        $data = [
            'position' => $room['build_block'] . $room['room'] . "室",
            'area' => $room['area'] . "㎡",
            'price' => $room['price'] . "元/㎡·天",
            'park' => $park['name'],
            'address' => $park['address'],
            'moblie' => $park['business_phone'],
            'img' => json_decode($room['img']),
            'imgs' => json_decode($room['imgs']),
            'panorama' => $room['panorama'],
            'rent_id' => $room['id'],
        ];
        if (floatval($room['price']) == 0) {
            $data['price'] = $room['price'];

        }
        $userid = session('userId');
        $user = WechatUser::where('userid', $userid)->find();
        $userinfo = [
            'name' => $user['name'],
            'mobile' => $user['mobile']
        ];
        $this->assign('user', json_encode($userinfo));
        $this->assign('info', json_encode($data));

        return $this->fetch();
    }


    /*楼盘列表下拉刷新*/
    public function moreList()
    {
        $len = input('length');
        $build = input('build');
        if (!$build) {
            $build = "A";
        }
        $parkName = input('name');
        $park = new Park();
        $parkId = $park->where(['name' => ['like', "%$parkName%"]])->find();
        $map = ['park_id' => $parkId['id'], 'build_block' => $build, 'manage' => 1, 'company_id' => ['eq', 0]];
        $parkInfo = Park::where('id', $parkId)->find();
        $parkRoom = new ParkRoom();
        $list = $parkRoom->where($map)->order('id desc')->limit($len, 6)->select();
        if ($list) {
            foreach ($list as $k => $v) {
                $data[$k] = [
                    'img' => json_decode($v['imgs']),
                    'panorama' => $v['panorama'],
                    'area' => $v['area'] . "㎡",
                    'price' => $v['price'] . "元/㎡·天",
                    'name' => $parkInfo['name'],
                    'id' => $v['id'],
                    'room' => $v['build_block'] . "幢" . $v['room'] . "室"
                ];
                if (floatval($v['price']) == 0) {
                    $data[$k]['price'] = $v['price'];
                }
            }

            return ['code' => 1, 'data' => json_encode($data)];
        } else {

            return ['code' => 0, 'data' => "没有更多数据了"];
        }

    }

    /*楼盘表*/
    public function housesList()
    {
        /* $floor = [];
         $floor1 = [];
         $newArr = [];
         $newArr1 = [];
         $parkId = session('park_id');
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
         $list1 = ["$parkName" => $list];
         $this->assign('type', $type);*/
        $userid = session('userId');
        $user = WechatUser::where('userid', $userid)->find();
        $userinfo = [
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'park_id' => $user['park_id'],
        ];
        $this->assign('user', json_encode($userinfo));

        $list1 = $this->rentlist();
        //return json_encode($list1);
        //echo json_encode($list1);
        $this->assign('list', json_encode($list1));
        return $this->fetch();


    }

    /**
     * 重写租房信息
     */
    public function rentlist()
    {
        $park_id = session('park_id');
        if ($park_id == 3) {
            $setArr = [
                '3' => ['A', 'B'],
                '80' => ['A', 'B', 'C', 'D']
            ];
        } else {
            $setArr = [
                '80' => ['A', 'B', 'C', 'D'],
                '3' => ['A', 'B']
            ];
        }
        $newData = [];
        $roomArray = [];
        $parkRoom = new ParkRoom();
        $park = new Park();
        $parkRent = new ParkRent();
        foreach ($setArr as $k => $v) {
            $number = $k;
            $parkInfo = $park->where(['id' => $number])->find();
            $newData[$parkInfo['name']] = [];
            foreach ($v as $k1 => $v1) {
                $element = $v1;
                $newArr = [];
                $floor = [];
                $map = ['park_id' => $number, 'build_block' => $element, 'del' => 0];
                //获取楼层信息
                $list = $parkRoom->where($map)->distinct(true)->field('floor')->order('floor desc')->select();
                foreach ($list as $k => $v) {
                    $floor[$k] = $v['floor'];
                }
                //每层楼房间数目
                foreach ($floor as $k => $v) {
                    $roomList = $parkRoom->where(['floor' => $v, 'build_block' => $element, 'del' => 0, 'park_id' => $number, 'manage' => 1])->order("room asc")->select();
                    //判断房间是否出租
                    foreach ($roomList as $k1 => $v1) {
                        //分园区，希垦没有已约的状态
                        if ($v1['manage'] == 1 && $v1['company_id'] == 0) {
                            $rent = PeopleRent::where(['room_id' => $v1['id'], 'status' => array('neq', -1)])->select();
                            if ($rent) {

                                if ($v1['park_id'] == 3) {
                                    $status = 1;
                                } else {
                                    $status = 2;
                                }
                            } else {
                                $status = 1;
                            }
                            $roomsId = $v1['id'];
                        } else {
                            $status = 0;
                            $roomsId = 0;
                        }
                        $roomArray[$k][$k1] = ['room' => $v1['room'], 'empty' => $status, 'id' => $v1['company_id'], 'room_id' => $roomsId];
                        $roomArray[$k] = array_slice($roomArray[$k], 0, $k1 + 1);
                    }
                }
                foreach ($floor as $k => $v) {
                    $newArr[$k]['floor'] = $v;
                    $newArr[$k]['rooms'] = $roomArray[$k];
                }
                //rentList 找出所有出租信息
                $map1 = ['park_id' => $number, "build_block" => $element, 'status' => 1, 'manage' => 1, 'company_id' => ['eq', 0]];
                $rentList = $parkRoom->where($map1)->order('id desc')->limit(6)->select();
                if ($rentList) {
                    foreach ($rentList as $k => $v) {
                        $data[$k] = [
                            'img' => json_decode($v['imgs']),
                            'panorama' => $v['panorama'],
                            'area' => $v['area'] . "㎡",
                            'price' => $v['price'] . "元/㎡·天",
                            'name' => $parkInfo['name'],
                            'id' => $v['id'],
                            'room' => $v['build_block'] . "幢" . $v['room'] . "室"
                        ];
                        if (floatval($v['price']) == 0) {
                            $data[$k]['price'] = $v['price'];
                        }
                    }
                    $data = array_slice($data, 0, $k + 1);
                } else {
                    $data = [];
                }
                $newData[$parkInfo['name']][$element . '幢'] = ['houselist' => $newArr, 'rentlist' => $data];
            }
        }

        return ($newData);


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
                //todo： 推送点击到详情页面代码
                $message = [
                    "title" => "租房服务提示",
                    "description" => "您有新的租房申请，请点击查看。",
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/Roomrent/record/type/1/id/' . $people->getLastInsID()
                ];
                //推送给运营
                $service = new Service();
                $reult = $service->commonSend(1, $message);
                if ($reult) {
                    $this->success('提交成功', '', $msg);
                } else {
                    return $this->error("推送失败");
                }


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
    public function intention()
    {
        if (IS_POST) {
            $data = input('post.');
            $data['create_time'] = time();
            $data['park_id'] = session('park_id');
            $data['status'] = 0;

            $parkIntention = new ParkIntention();
            $res = $parkIntention->allowField(true)->save($data);
            if ($res) {
                //todo： 推送点击到详情页面代码
                $message = [
                    "title" => "租房意向服务提示",
                    "description" => "您有新的租房意向，请点击查看。",
                    "url" => 'http://' . $_SERVER['HTTP_HOST'] . '/index/Roomrent/record/type/2/id/' . $parkIntention->getLastInsID()
                ];
                //推送给运营
                $service = new Service();
                $reult = $service->commonSend(1, $message);
                if ($reult) {
                    $this->success('添加成功');
                } else {
                    return $this->error("推送失败");
                }

            } else {

                $this->error("添加失败");
            }
        } else {

            return $this->fetch();
        }

    }

//    记录
    public function record()
    {
        $id = input('id');
        $type = input('type');
        $peopleRent = new PeopleRent();
        $parkIntention = new ParkIntention();
        if (IS_POST) {
            $info = $peopleRent->where('id', $id)->find();
            $info['status'] = 2;
            $info->save();
            return $this->success('成功');
        } else {

            //我要租房
            if ($type == 1) {
                $info = $peopleRent->where('id', $id)->find();
                $info['area'] = isset($info->roominfo->area) ? $info->roominfo->area : "";
                $info['price'] = isset($info->roominfo->price) ? $info->roominfo->price : "";
                $info['room'] = isset($info->roominfo->room->room) ? $info->roominfo->room->build_block . "幢" . $info->roominfo->room->room : "";

                unset($info['roominfo']);

            } //租房意向
            else {
                $info = $parkIntention->where('id', $id)->find();
            }

            $info['type'] = $type;
            $this->assign('info', json_encode($info));
            return $this->fetch();
        }

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
        foreach ($floor as $k => $v) {
            $map['floor'] = $v;
            $roomList[$k]['floor'] = $v;
            $roomList[$k]['rooms'] = $parkRoom->where($map)->order('room  asc')->field('room,id')->select();

        }
        $area = $this->gaoshiqing();
        for ($i = 13; $i > 0; $i--) {
            foreach ($area as $k => $v) {
                if ($v['floor'] == $i) {
                    $newArr[] = ['floor' => $i, 'rooms' => $v['rooms']];
                }
            }
            foreach ($roomList as $k => $v) {
                if ($v['floor'] == $i) {
                    $newArr[] = ['floor' => $i, 'rooms' => $v['rooms']];
                }
            }
        }

        foreach ($newArr as $k => $v) {
            if ($k == (count($newArr) - 1)) {
                break;
            }
            if ($newArr[$k]['floor'] == $newArr[$k + 1]['floor']) {
                $newArr[$k]['rooms'] = array_merge($newArr[$k]['rooms'], $newArr[$k + 1]['rooms']);
            }
        }
        foreach ($newArr as $k => $v) {
            if ($k == (count($newArr) - 1)) {
                break;
            }
            if ($newArr[$k]['floor'] == $newArr[$k + 1]['floor']) {
                $newArr1[] = $k + 1;
            }
        }
        foreach ($newArr1 as $k => $v) {
            unset($newArr[$v]);
        }
        $asp = "";
        foreach ($newArr as $k => $v) {
            foreach ($v['rooms'] as $k1 => $v1) {

                if ($k1 == (count($v['rooms']) - 1)) {
                    break;
                }

                if ($v['rooms'][$k1]['room'] > $v['rooms'][$k1 + 1]['room']) {
                    $asp = $v['rooms'][$k1 + 1]['room'];
                    $v['rooms'][$k1 + 1]['room'] = $v['rooms'][$k1]['room'];
                    $v['rooms'][$k1]['room'] = $asp;
                }
            }
        }
        echo json_encode($newArr);


    }


}