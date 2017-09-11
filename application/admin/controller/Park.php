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
use app\common\model\PartyNews;


class Park extends Admin
{
    /*园区信息添加及修改*/
    public function index()
    {
        $parkid = session("user_auth")['park_id'];
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
                $like = mb_substr($data['company_id'],0,6);
                $info =  WechatDepartment::where(['name'=> ['like',"%$like%"]])->find();
                $data['company'] = $data['company_id'];
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
                $maps = [
                    'build_block' => $data['build_block'],
                    'room' => $data['room'],
                ];
                $roomId = ParkRoom::where($maps)->find();
                if ($roomId){

                    $this->error("该信息已存在，无法添加");
                }
                $data['park_id'] = session("user_auth")['park_id'];
                $data['status'] = 1;
                $data['company'] = $data['company_id'];
                $like = mb_substr($data['company_id'],0,6);
                $info = WechatDepartment::where(['name'=> ['like',"%$like%"]])->find();
                if ($info && $data['company_id'] !="") {
                    $data['company_id'] = $info['id'];
                } else {
                    $data['company_id'] = 0;
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
        $search = input('search');
        $map = [
            "park_id" => session("user_auth")['park_id'],
            'del' => 0,
        ];
        if ($search){
            $map['room'] = $search;
        }
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
        $result = ParkRoom::where('id', 'in', $ids)->update(['del' => -1]);

        if ($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }

    /*房屋出租信息*/
    public function roomRent()
    {
        $search = input('search');
        $parkRent = new ParkRent();
        $map = ['park_id' => session("user_auth")['park_id'],'status' => 0];
        if ($search){
            $res = ParkRoom::where('room',$search)->select();
            foreach($res as $k=>$v){
                $seachArr[$k] = $v['id'];
            }
            $map['room_id'] = ['in',$seachArr] ;
        }
        $list = $parkRent->where($map)->order('id desc')->paginate();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $v['room_id'] = $room['room'];
            $v['build'] = $room['build_block'];
        }
        $map = ['id' => session("user_auth")['park_id']];
        $park = ParkModel::where(['id' => session("user_auth")['park_id']])->find();
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
                $data['img'] = json_encode($data['img']);
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
                $rooms = $parkRoom->where(['room' => input('room'), 'build_block' => input('build_block'),'del' =>0])->find();
                //return  dump($rooms['id']);
                if (!$rooms['id']){
                    $this->error('该楼室不存在');
                }
                $rents = $parkRent->where(['room_id'=>$rooms['id'],'status' => 0])->find();
                if ($rents['id']){
                    $this->error('该信息已存在');
                }
                $data['room_id'] = $rooms['id'];
                $data['park_id'] = session("user_auth")['park_id'];
                unset($data['room']);
                foreach ($data['img'] as $k => $v) {
                    $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                }
                $data['img'] = json_encode($data['img']);
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
    public function moveToTrashs()
    {
        $ids = input('ids/a');
        $result = ParkRent::where('id', 'in', $ids)->update(['status' => -1]);

        if ($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }
    /*通知公告列表*/
    public function noticeList(){
        $parkid = session('user_auth')['park_id'];
        $map = ['status'=> 1,'park_id'=>$parkid];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = PartyNews::where($map)->order('id desc')->paginate();
        $this->assign('list', $list);

        return $this->fetch();

    }


    /*通知公告添加*/
    public function notice(){
        $parkid = session('user_auth')['park_id'];
        if (IS_POST){
            $partyBuilding =new PartyNews();
            if(input('id')) {
                $_POST['park_id']=$parkid;
                $_POST['update_time'] =time();
                $result = $partyBuilding->validate(true)->save($_POST, ['id'=>input('id')]);
                if ($result){

                    return $this->success("修改成功！");
                }else{

                    return $this->error("修改失败");
                }
            }else{
                $_POST['park_id']=$parkid;
                $_POST['create_time'] =time();
                $_POST['status'] = 1;
                unset($_POST['id']);
                $result =  $partyBuilding->validate(true)->save($_POST);
                if ($result){

                    return $this->success("添加成功！");
                }else{

                    return $this->error($partyBuilding->getError());
                }
            }

        }else{
            $news = PartyNews::where('id','eq',input('id'))->find();
            $this->assign('news', $news);
            return $this->fetch();
        }
    }
    /*删除通知公告信息*/
    public function moveToTrashss()
    {
        $ids = input('ids/a');
        $result = PartyNews::where('id', 'in', $ids)->update(['status' => -1]);

        if ($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }



}