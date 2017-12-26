<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/2
 * Time: 上午10:24
 */

namespace app\common\model;

use think\Model;

class ParkRoom extends Model
{

    /**
     * 重写楼房信息表
     */
    public function companyRoom()
    {
        $setArr = [
            '3' => ['A', 'B'],
            '80' => ['A', 'B', 'C', 'D']
        ];
        $newData = [];
        $parkRoom = new ParkRoom();
        $park = new Park();
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
                    $roomList = $parkRoom->where(['floor' => $v, 'build_block' => $element, 'del' => 0, 'park_id' => $number])->order("room asc")->select();
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
                $newData[$parkInfo['name']][$element . '幢'] = $newArr;
            }
        }
        return $newData;
    }

    /**
     * 获取园区楼盘详细信息
     * @param $parkid
     * @return array
     */
    public function getFloorInfo($parkid)
    {
        if ($parkid == 3) {
            $setArr = ['3' => ['A', 'B']];
        } elseif ($parkid == 80) {
            $setArr = ['80' => ['A', 'B', 'C', 'D']];
        }
        $newData = [];
        $parkRoom = new ParkRoom();
        $parkFloor = new ParkFloor();
        $peoplerent = new PeopleRent();
        $park = new Park();
        $roomArray = [];
        foreach ($setArr as $k => $v) {
            $number = $k;
            $parkInfo = $park->where(['id' => $number])->find();
            $newData[$parkInfo['name']] = [];
            foreach ($v as $k1 => $v1) {
                $element = $v1;
                $newArr = [];
                $floor = [];
                $map = ['park_id' => $number, 'build' => $element];
                //获取楼层信息
                $list = $parkFloor->where($map)->distinct(true)->field('fid')->order('fid asc')->select();
                foreach ($list as $k => $v) {
                    $floor[$k] = $v['fid'];
                }
                //return ($floor);
                //每层楼房间数目
                foreach ($floor as $k => $v) {
                    $roomList = $parkRoom->where(['floor' => $v, 'build_block' => $element, 'del' => 0, 'park_id' => $number])->order("room asc")->select();
                    //return $roomList;
                    //判断房间状态，空置，已租，已约，下架
                    foreach ($roomList as $k1 => $v1) {
                        $roomArray[$k][$k1] = ['room' => $v1['room'], 'room_id' => $v1['id'], 'area' => $v1['area'], 'company' => '', 'relevance' => '', 'contract' => ''];
                        $roomArray[$k] = array_slice($roomArray[$k], 0, $k1 + 1);
                        //下架状态
                        if ($v1['manage'] == 2) {
                            $roomArray[$k][$k1]['status'] = 4;
                        } else {
                            //已租状态 显示面积，公司名称，关联房间号
                            if ($v1['company_id'] != 0) {
                                $roomArray[$k][$k1]['status'] = 3;
                                $roomArray[$k][$k1]['company'] = $v1['company'];
                                //关联房间号
                                $relevance = $parkRoom->where(['company_id' => $v1['company_id'], 'park_id' => $parkid])->select();
                                if ($relevance) {
                                    $roomArray[$k][$k1]['relevance'] = [];
                                    foreach ($relevance as $key => $value) {
                                        $roomArray[$k][$k1]['relevance'][$key] = $value['room'];
                                    }
                                    $roomArray[$k][$k1]['relevance'] = json_encode($roomArray[$k][$k1]['relevance']);
                                }
                            } else {
                                //空置跟已经预约的状态
                                $peopleStatus = $peoplerent->where(['room_id' => $v1['id']])->find();
                                if ($peopleStatus) {
                                    $roomArray[$k][$k1]['status'] = 2;
                                    $peopleArr = $peoplerent->where(['room_id' => $v1['id']])->select();
                                    if (!empty($peopleArr)) {
                                        foreach ($peopleArr as $k2 => $v2) {
                                            if ($v2['status'] == 1) {
                                                $roomArray[$k][$k1]['contract'] = "未联系";
                                            }
                                        }
                                    }
                                } else {
                                    $roomArray[$k][$k1]['status'] = 1;
                                }
                            }
                        }
                    }
                }
                //return $roomArray;
                foreach ($floor as $k => $v) {
                    $newArr[$k]['floor'] = $v;
                    if (!empty($roomArray[$k])) {
                        $newArr[$k]['rooms'] = $roomArray[$k];
                    }
                }
                $newData[$parkInfo['name']][$element] = $newArr;
            }
        }

        return $newData;
    }


}