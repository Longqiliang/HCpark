<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/17
 * Time: 下午5:57
 */

namespace app\admin\controller;

use app\common\model\Park as ParkModel;
use app\common\model\ParkRoom;
use app\common\model\PeopleRent;
use app\index\model\WechatDepartment;
use app\common\model\ParkRent;


class Park extends Admin
{
    /*园区信息添加及修改*/
    public function index()
    {
        $parkid = 3;
        if (IS_POST) {
            $park = new ParkModel();
            $res = $park->allowField(true)->validate(true)->save(input("post."), ['id' => $parkid]);
            if ($res) {
                $this->success("保存成功");
            } else {
                $this->error($park->getError());
            }
        }

        $info = ParkModel::where(['id' => $parkid])->find();

        $this->assign('info', $info);

        return $this->fetch();
    }

    /*楼盘管理*/
    public function roomManage()
    {
        $id = input("id");
        $parkRoom = new ParkRoom();
        if (IS_POST) {
            if (input('uid')) {
                $data = input('post.');
                $info = WechatDepartment::where('name', $data['company_id'])->find();
                //$data['company'] = $data['company_id'];
                if ($info) {
                    $data['company_id'] = $info['id'];
                } else {
                    $data['company_id'] = "";
                }
                unset($data['uid']);
                $res = $parkRoom->validate(true)->where('id', input('uid'))->update($data);
                if ($res) {
                    $this->success("修改成功");
                } else {
                    $this->error("修改失败");
                }
            } else {
                $data = input('post.');
                $data['park_id'] = session("user_auth")['park_id'];
                $data['status'] = 1;
                $data['company'] = $data['company_id'];
                $info = WechatDepartment::where('name', $data['company_id'])->find();
                if ($info) {
                    $data['company_id'] = $info['id'];
                } else {
                    $data['company_id'] = "";
                }
                unset($data['uid']);
                $res = $parkRoom->validate(true)->save($data);
                if ($res) {
                    $this->success("添加成功");
                } else {
                    $this->error($parkRoom->getError());
                }
            }

        }
        $companyInfo = $parkRoom->where('id', $id)->find();
        if ($companyInfo) {
            $department = WechatDepartment::where('id', $companyInfo['company_id'])->find();
            $companyInfo['company_id'] = $department['name'];
        }
        $park = ParkModel::where('id', session("user_auth")['park_id'])->find();
        $parkName = $park['name'];
        $this->assign('parkName', $parkName);
        $this->assign("info", $companyInfo);

        return $this->fetch();
    }

    public function roomList()
    {
        $map = [
            "park_id" => session("user_auth")['park_id'],
        ];
        $list = ParkRoom::where($map)->order('id desc')->paginate();
        foreach ($list as $k => $v) {
            $department = WechatDepartment::where('id', $v['company_id'])->find();
            $v['company_id'] = $department['name'];
        }
        $park = ParkModel::where('id', session("user_auth")['park_id'])->find();
        $parkName = $park['name'];
        $this->assign('parkName', $parkName);
        $this->assign('list', $list);
        //return dump($_SESSION);
        return $this->fetch();
    }

    /*删除房间管理信息*/
    public function moveToTrash()
    {
        $ids = input('ids/a');
        $result = ParkRoom::where('id', 'in', $ids)->update(['status' => -1]);

        if ($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }

    /*房屋出租信息*/
    public function roomRent()
    {
        $parkRent = new ParkRent();
        $list = $parkRent->order('id desc')->paginate();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $v['room_id'] = $room['build_block'] . $room['room'];
        }
        $park = ParkModel::where('id', session("user_auth")['park_id'])->find();
        $parkName = $park['name'];;
        $this->assign('parkName', $parkName);
        $this->assign('list', $list);

        return $this->fetch();
    }

    /*添加房屋出租信息*/
    public function addRent()
    {
        $id = input('id');
        $parkRoom = new ParkRoom();
        $parkRent = new ParkRent();
        if (IS_POST) {
            if (input('id')) {
                $data = input('post.');
                unset($data['floor']);
                $rooms = $parkRoom->where(['room' => input('room'), 'build_block' => input('build_block')])->find();
                $data['room_id'] = $rooms['id'];
                unset($data['room']);
                foreach ($data['img'] as $k => $v) {
                    $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                }
                foreach ($data['panorama'] as $k => $v) {
                    $data['panorama'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                }
                $data['img'] = json_encode($data['img']);
                $data['panorama'] = json_encode($data['panorama']);
                //return dump($data);
                $res = $parkRent->where('id', $id)->update($data);
                if ($res) {

                    $this->success("修改成功");
                } else {

                    $this->error("修改失败");
                }
            } else {
                $data = input('post.');
                unset($data['id']);
                unset($data['floor']);
                $rooms = $parkRoom->where(['room' => input('room'), 'build_block' => input('build_block')])->find();
                $data['room_id'] = $rooms['id'];
                $data['park_id'] = session("user_auth")['park_id'];
                unset($data['room']);
                foreach ($data['img'] as $k => $v) {
                    $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                }
                foreach ($data['panorama'] as $k => $v) {
                    $data['panorama'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                }
                $data['img'] = json_encode($data['img']);
                $data['panorama'] = json_encode($data['panorama']);
                $res = $parkRent->save($data);
                if ($res) {

                    $this->success("添加成功");
                } else {

                    $this->error("添加失败");
                }
            }
        } else {
            $list = $parkRent->where('id', $id)->find();
            $data = $list;
            if ($list) {
                $roomId = $list['room_id'];
                $roomInfo = ParkRoom::where('id', $roomId)->find();
                $data['floor'] = $roomInfo['floor'];
                $data['room'] = $roomInfo['room'];
            }
            $park = ParkModel::where('id', session("user_auth")['park_id'])->find();
            $parkName = $park['name'];
            $this->assign("info", $data);
            $this->assign('parkName', $parkName);
            $this->assign('panorama', json_decode($data['panorama']));
            $this->assign('img', json_decode($data['img']));

            return $this->fetch();
        }
    }

    /*园区简介*/
    public function profile()
    {
        $parkid = session('user_auth')['park_id'];
        if (IS_POST) {
            $park = new ParkModel();
            $res = $park->allowField(true)->save(input("post."), ['id' => $parkid]);
            if ($res) {
                $this->success("保存成功");
            } else {
                $this->error($park->getError());
            }
        }

        $info = ParkModel::where(['id' => $parkid])->find();

        $this->assign('info', $info);

        return $this->fetch();

    }

    /*预约信息*/
    public function roomOrder()
    {
        $parkid = session('user_auth')['park_id'];
        $park = ParkModel::where('id', session("user_auth")['park_id'])->find();
        $parkName = $park['name'];
        $map = ['park_id' => $parkid];
        $list = PeopleRent::where($map)->order('id desc')->paginate();

        foreach ($list as $k => $v) {
            $parkRent = ParkRent::where('id', $v['rent_id'])->find();
            $parkRoom = ParkRoom::where('id', $parkRent['room_id'])->find();
            $v['rent_id'] = $parkName . $parkRoom['build_block'] . "幢" . $parkRoom['room'] . "室";
        }
        int_to_string($list, ['status' => [1 => "未联系", 2 => "已联系"]]);
        $this->assign('list', $list);

        return $this->fetch();
    }

    /*修改联系状态*/
    public function changeStatus()
    {
        $id = input('id');
        $res = PeopleRent::where('id', $id)->update(['status' => 2]);
        if ($res) {

            $this->success("修改成功");
        } else {

            $this->error("修改失败");
        }
    }
    /*删除预约信息*/

}