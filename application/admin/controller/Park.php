<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/17
 * Time: 下午5:57
 */

namespace app\admin\controller;

use app\common\model\Park as ParkModel;
use app\common\model\ParkIntention;
use app\common\model\ParkRoom;
use app\common\model\PeopleRent;
use app\index\model\WechatDepartment;
use app\common\model\ParkRent;
use app\common\model\PartyNews;
use think\Db;
use think\Exception;
use think\Image;
use app\common\model\ParkFloor;


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
        $parkid = session("user_auth")['park_id'];
        $parkRent = new ParkRent();
        $id = input("id");
        $parkRoom = new ParkRoom();
        if (IS_POST) {
            if (input('uid')) {
                $data = input('post.');
                $like = mb_substr($data['company_id'], 0, 6);
                $info = WechatDepartment::where(['name' => ['like', "%$like%"]])->find();
                $data['company'] = $data['company_id'];
                if ($info) {
                    $data['company_id'] = $info['id'];
                } else {
                    $data['company_id'] = "";
                }
                unset($data['uid']);
                $res = $parkRoom->validate(true)->where('id', input('uid'))->update($data);
                if ($res) {
                    $rents = $parkRent->where('room_id', input('uid'))->find();

                    if ($rents) {
                        $parkRent->where(['id' => $rents['id'], 'status' => 0])->update(['status' => -1]);
                    }
                    $this->success("修改成功");
                } else {
                    $this->error("修改失败");
                }
            } else {
                $data = input('post.');
                $maps = [
                    'build_block' => $data['build_block'],
                    'room' => $data['room'],
                    'park_id' => $parkid,
                ];
                $roomId = ParkRoom::where($maps)->find();
                if ($roomId) {

                    $this->error("该信息已存在，无法添加");
                }
                $data['park_id'] = session("user_auth")['park_id'];
                $data['status'] = 1;
                $data['company'] = $data['company_id'];
                $like = mb_substr($data['company_id'], 0, 6);
                $info = WechatDepartment::where(['name' => ['like', "%$like%"]])->find();
                if ($info && $data['company_id'] != "") {
                    $data['company_id'] = $info['id'];
                } else {
                    $data['company_id'] = 0;
                }
                unset($data['uid']);
                $res = $parkRoom->validate(true)->save($data);
                if ($res) {
                    $lastId = $parkRoom->getLastInsID();
                    $rents = $parkRent->where(['room_id' => $lastId, 'status' => 0])->find();
                    if ($rents) {
                        $parkRent->where('id', $rents['id'])->update(['status' => -1]);
                    }
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
        $this->assign('park_id', $parkid);
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
        if ($search) {
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
        $map = ['park_id' => session("user_auth")['park_id'], 'status' => 0];
        if ($search) {
            $res = ParkRoom::where('room', $search)->select();
            foreach ($res as $k => $v) {
                $seachArr[$k] = $v['id'];
            }
            $map['room_id'] = ['in', $seachArr];
        }
        $list = $parkRent->where($map)->order('id desc')->paginate();
        foreach ($list as $k => $v) {
            $room = ParkRoom::where('id', $v['room_id'])->find();
            $v['room_id'] = $room['room'];
            $v['build'] = $room['build_block'];
            if (is_numeric($v['price'])) {
                $v['price'] = number_format($v['price'], 2, '.', '') . "元/㎡·天";
            }

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
        $parkId = session("user_auth")['park_id'];
        $id = input('id');
        $parkRoom = new ParkRoom();
        $parkRent = new ParkRent();
        if (IS_POST) {
            if (input('id')) {
                $data = input('post.');
                //return $data;
                unset($data['floor']);
                $rooms = $parkRoom->where(['room' => input('room'), 'build_block' => input('build_block'), 'park_id' => $parkId])->find();
                if ($rooms['company']) {

                    $this->error(" 该房间已有企业入住，无法添加出租信息");
                }
                $data['room_id'] = $rooms['id'];
                unset($data['room']);
                if ($data['img']) {
                    foreach ($data['img'] as $k => $v) {
                        $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                    }
                }

                if ($data['img']) {
                    foreach ($data['img'] as $k1 => $v1) {
                        if (is_file(PUBLIC_PATH . $v1)) {
                            $path = str_replace(".", "_s.", $v1);
                            $image = Image::open(PUBLIC_PATH . $v1);
                            $image->thumb(1125, 563)->save(PUBLIC_PATH . $path);
                            $data['imgs'][$k1] = $path;
                        } else {
                            $data['imgs'][$k1] = $data['img'][$k1];
                        }
                    }
                    $data['imgs'] = json_encode($data['imgs']);
                } else {
                    $data['imgs'] = json_encode($data['img']);
                }
                $data['img'] = json_encode($data['img']);

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
                $rooms = $parkRoom->where(['room' => input('room'), 'build_block' => input('build_block'), 'del' => 0, 'park_id' => $parkId])->find();
                if (!$rooms['id']) {
                    $this->error('该楼室不存在');
                } elseif ($rooms['company']) {

                    $this->error(" 该房间已有企业入住，无法添加出租信息");
                }
                $rents = $parkRent->where(['room_id' => $rooms['id'], 'status' => 0, 'park_id' => $parkId])->find();
                if ($rents['id']) {
                    $this->error('该信息已存在');
                }
                $data['room_id'] = $rooms['id'];
                $data['park_id'] = session("user_auth")['park_id'];
                unset($data['room']);
                if ($data['img']) {
                    foreach ($data['img'] as $k => $v) {
                        $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                    }
                }
                if ($data['img']) {
                    foreach ($data['img'] as $k1 => $v1) {
                        if (is_file(PUBLIC_PATH . $v1)) {
                            $path = str_replace(".", "_s.", $v1);
                            $image = Image::open(PUBLIC_PATH . $v1);
                            $image->thumb(1125, 563)->save(PUBLIC_PATH . $path);
                            $data['imgs'][$k1] = $path;
                        } else {
                            $data['imgs'][$k1] = $data['img'][$k1];
                        }
                    }
                    $data['imgs'] = json_encode($data['imgs']);
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
            $this->assign('park_id', $parkId);
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
        $map = ['park_id' => $parkid, 'status' => array('neq', -1)];
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

    /*删除预约信息*/
    public function moveToTrashs2()
    {
        $ids = input('ids/a');
        $result = PeopleRent::where('id', 'in', $ids)->update(['status' => -1]);

        if ($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }

    /*通知公告列表*/
    public function noticeList()
    {
        $parkid = session('user_auth')['park_id'];
        $map = ['status' => 1, 'park_id' => $parkid];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like', '%' . $search . '%'];
        }
        $list = PartyNews::where($map)->order('id desc')->paginate();
        $this->assign('list', $list);

        return $this->fetch();

    }


    /*通知公告添加*/
    public function notice()
    {
        $parkid = session('user_auth')['park_id'];
        if (IS_POST) {
            $partyBuilding = new PartyNews();
            if (input('id')) {
                $_POST['park_id'] = $parkid;
                $_POST['update_time'] = time();
                $result = $partyBuilding->validate(true)->save($_POST, ['id' => input('id')]);
                if ($result) {

                    return $this->success("修改成功！");
                } else {

                    return $this->error("修改失败");
                }
            } else {
                $_POST['park_id'] = $parkid;
                $_POST['create_time'] = time();
                $_POST['status'] = 1;
                unset($_POST['id']);
                $result = $partyBuilding->validate(true)->save($_POST);
                if ($result) {

                    return $this->success("添加成功！");
                } else {

                    return $this->error($partyBuilding->getError());
                }
            }

        } else {
            $news = PartyNews::where('id', 'eq', input('id'))->find();
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

    /**/
    public function manage()
    {
        $id = input('id');
        $uid = input('uid');
        $parkrent = new ParkRent();
        $res = $parkrent->where('id', $id)->update(['manage' => $uid]);
        if ($res) {

            $this->success('设置成功');
        } else {

            $this->error("设置失败");
        }

    }

    /*租房意向表*/
    public function intention()
    {
        $park_id = session('user_auth')['park_id'];
        $list = ParkIntention::where(['park_id' => $park_id, 'status' => ['>', -1]])->order('create_time desc')->paginate();
        $this->assign('list', $list);
        return $this->fetch();
    }

    /*修改租房意向状态*/
    public function changeState()
    {
        $id = input('id');
        $res = ParkIntention::where('id', $id)->update(['status' => 1]);
        if ($res) {

            $this->success("修改成功");
        } else {

            $this->error("修改失败");
        }
    }

    /*删除租房意向信息*/
    public function moveToTrashsss()
    {
        $ids = input('ids/a');
        $result = ParkIntention::where('id', 'in', $ids)->update(['status' => -1]);

        if ($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }

    /**
     * 楼盘表信息
     */
    public function houselist()
    {
        $park_id = session('user_auth')['park_id'];
        $floorInfo = $this->getFloor();

        $this->assign("floorInfo", $floorInfo);
        $this->assign('park_id', $park_id);
        return $this->fetch();
    }

    /**
     * 添加楼层
     */
    public function addfloor()
    {
        $data = input();
        $floor = new ParkFloor();
        $build = input('build');
        $park_id = session('user_auth')['park_id'];
        $map = ['fid' => input('fid'), 'build' => $build, 'park_id' => $park_id];
        $re = $floor->where($map)->find();
        if ($re) {

            return $this->error("该信息已经存在，无法添加");
        }
        $res = $floor->allowField(true)->save($data);
        if ($res) {
            $floorArr = $this->floor($park_id, $build);

            return $this->success("添加成功", '', ['floor' => $floorArr]);
        } else {

            return $this->error("添加失败");
        }
    }

    /**
     * 删除楼层信息
     */
    public function delfloor()
    {
        $parkRoom = new ParkRoom();
        $parkFloor = new ParkFloor();
        $parkId = session("user_auth")['park_id'];
        $id = input('id');
        $build = input('build_block');
        $floor = input('floor');
        if ($id == 1) {
            //删除一层楼的信息
            $map = ['park_id' => $parkId, 'build_block' => $build, 'floor' => $floor];
            $res = $parkRoom->where($map)->update(['del' => -1]);
            if ($res) {

                return $this->success('删除成功');
            } else {

                return $this->error("删除失败");
            }
        } else {
            //删除楼层
            $map = ['park_id' => $parkId, 'build' => $build, 'fid' => $floor];
            $res = $parkFloor->where($map)->delete();
            $floorArr = $this->floor($parkId, $build);
            if ($res) {

                return $this->success('删除成功', '', ['floor' => $floorArr]);
            } else {

                return $this->error("删除失败");
            }
        }
    }

    /**
     * 添加房屋出租信息
     */
    public function addRents()
    {
        $parkId = session("user_auth")['park_id'];
        $id = input('id');
        $parkRoom = new ParkRoom();
        $parkRent = new ParkRent();
        if (input('id')) {
            $data = input('post.');
            unset($data['floor']);
            $rooms = $parkRoom->where(['room' => input('room'), 'build_block' => input('build_block'), 'park_id' => $parkId])->find();
            if ($rooms['company']) {

                $this->error(" 该房间已有企业入住，无法添加出租信息");
            }
            $data['room_id'] = $rooms['id'];
            unset($data['room']);
            if ($data['img']) {
                foreach ($data['img'] as $k => $v) {
                    $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                }
            }
            if ($data['img']) {
                foreach ($data['img'] as $k1 => $v1) {
                    if (is_file(PUBLIC_PATH . $v1)) {
                        $path = str_replace(".", "_s.", $v1);
                        $image = Image::open(PUBLIC_PATH . $v1);
                        $image->thumb(1125, 563)->save(PUBLIC_PATH . $path);
                        $data['imgs'][$k1] = $path;
                    } else {
                        $data['imgs'][$k1] = $data['img'][$k1];
                    }
                }
                $data['imgs'] = json_encode($data['imgs']);
            } else {
                $data['imgs'] = json_encode($data['img']);
            }
            $data['img'] = json_encode($data['img']);

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
            $rooms = $parkRoom->where(['room' => input('room'), 'build_block' => input('build_block'), 'del' => 0, 'park_id' => $parkId])->find();
            if (!$rooms['id']) {
                $this->error('该楼室不存在');
            } elseif ($rooms['company']) {

                $this->error(" 该房间已有企业入住，无法添加出租信息");
            }
            $rents = $parkRent->where(['room_id' => $rooms['id'], 'status' => 0, 'park_id' => $parkId])->find();
            if ($rents['id']) {
                $this->error('该信息已存在');
            }
            $data['room_id'] = $rooms['id'];
            $data['park_id'] = session("user_auth")['park_id'];
            unset($data['room']);
            if ($data['img']) {
                foreach ($data['img'] as $k => $v) {
                    $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
                }
            }
            if ($data['img']) {
                foreach ($data['img'] as $k1 => $v1) {
                    if (is_file(PUBLIC_PATH . $v1)) {
                        $path = str_replace(".", "_s.", $v1);
                        $image = Image::open(PUBLIC_PATH . $v1);
                        $image->thumb(1125, 563)->save(PUBLIC_PATH . $path);
                        $data['imgs'][$k1] = $path;
                    } else {
                        $data['imgs'][$k1] = $data['img'][$k1];
                    }
                }
                $data['imgs'] = json_encode($data['imgs']);
            }
            $data['img'] = json_encode($data['img']);

            $res = $parkRent->save($data);
            if ($res) {

                $this->success("添加成功");
            } else {

                $this->error("添加失败");
            }
        }
    }

    /**
     * 添加房屋信息
     */
    public function addrooms()
    {
        $parkid = session("user_auth")['park_id'];
        $id = input("id");
        $parkRoom = new ParkRoom();
        $data = input('post.');
        if (!empty($data['img'])) {
            foreach ($data['img'] as $k => $v) {
                $data['img'][$k] = str_replace("http://" . $_SERVER['HTTP_HOST'], "", $v);
            }
            $data['img'] = json_encode($data['img']);
        }
        if (!empty($data['imgs'])){
            $data['imgs'] = json_encode($data['imgs']);
        }
        if (input('uid')) {
            unset($data['uid']);
            $res = $parkRoom->validate(true)->where('id', input('uid'))->update($data);
            if ($res) {

                $floor = $this->getFloor();
                $this->success("修改成功", "", ($floor));
            } else {
                $this->error("修改失败");
            }
        } else {

            $maps = [
                'build_block' => $data['build_block'],
                'room' => $data['room'],
                'park_id' => $parkid,
                'del' => 0
            ];
            $roomId = ParkRoom::where($maps)->find();
            if ($roomId) {

                $this->error("该信息已存在，无法添加");
            }

            $data['park_id'] = session("user_auth")['park_id'];
            $data['status'] = 1;
            $data['manage'] = 2;
            unset($data['uid']);
            $res = $parkRoom->validate(true)->allowField(true)->save($data);

            if ($res) {
                $floor = $this->getFloor();
                $this->success("添加成功", '', ($floor));
            } else {
                $this->error($parkRoom->getError());
            }
        }
    }

    /**
     * 获取楼层详细信息信息公共方法
     * @return  array
     */
    public function getFloor()
    {
        $park_id = session('user_auth')['park_id'];
        $parkRoom = new ParkRoom();
        $data = $parkRoom->getFloorInfo($park_id);

        return ($data);
    }

    /**
     * 获取楼层信息
     * @return array
     */
    protected function floor($park, $build)
    {
        $parkFloor = new ParkFloor();
        $map = ['park_id' => $park, 'build' => $build];
        $list = $parkFloor->where($map)->order('fid asc')->select();
        $data = [];
        foreach ($list as $k => $v) {
            $data[$k] = $v['fid'];
        }

        return ($data);
    }

    /**
     *点击获取楼房信息的公共方法
     */
    public function floorInfo()
    {
        $parkRoom = new ParkRoom();
        $peoplerent = new PeopleRent();
        $park_id = session('user_auth')['park_id'];
        if ($park_id ==3){
            $parkName = "希垦科技园";
        }elseif ($park_id ==80){
            $parkName = "人工智能产业园";
        }
        $roomId = input('room_id');
        $map = ['id' => $roomId];
        $info = $parkRoom->where($map)->find();
        $info['parkName'] = $parkName;
        $info['company_type'] = config('company_type');
        if (empty($info['img'])){
            $info['img'] = [];
        }else{
            $info['img'] = json_decode($info['img']);
        }
        if (empty($info['imgs'])){
            $info['imgs'] = [];
        }else{
            $info['imgs'] = json_decode($info['imgs']);
        }
        if ($info['manage'] == 2) {
            $info['status'] = 4;
        } else {
            //已租状态 显示面积，公司名称，关联房间号
            if ($info['company_id'] != 0) {
                $info['status'] = 3;
                //关联房间号
                $relevance = $parkRoom->where(['company_id' => $info['company_id'], 'park_id' => $park_id])->select();
                if (!empty($relevance)) {
                    foreach ($relevance as $key => $value) {
                       $array[$key] = $value['room'];
                    }
                    $info['relevance'] = $array ;
                }
            } else {
                //空置跟已经预约的状态
                $peopleStatus = $peoplerent->where(['room_id' => $info['id']])->find();
                if ($peopleStatus) {
                    $info['status'] = 2;
                    $peopleArr = $peoplerent->where(['room_id' => $info['id'],'status' => 1])->select();
                    if (!empty($peopleArr)) {
                        $info['people'] = $peopleArr;
                    }
                } else {
                    $info['status'] = 1;
                }
            }
        }

        return $this->success('','',json_encode($info));
    }

    /**
     * 上下架处理
     */
    public function setmanage()
    {
        $manage = input('manage');
        $room = input('room');
        $build = input('build_block');
        $parkId = session("user_auth")['park_id'];
        $parkRoom = new ParkRoom();
        $map = ['build_block' => $build, 'park_id' => $parkId, 'room' => $room];
        if ($manage == 1) {
            $res = $parkRoom->where($map)->update(['manage' => 1]);
            if ($res) {
                $floor = $this->getFloor();

                return $this->success("上架成功",'',($floor));
            } else {

                return $this->error("上架失败");
            }
        } else {
            $res = $parkRoom->where($map)->update(['manage' => 2, 'company' => null, 'company_id' => 0, 'type' => null]);
            if ($res) {
                $floor = $this->getFloor();

                return $this->success("下架成功",'',$floor);
            } else {

                return $this->error("下架失败");
            }
        }
    }

    /**
     * 保存房间信息（空置->已租）
     */
    public function saveroom()
    {
        $datas = input();
        $room = input('room');
        $build = input('build_block');
        $parkId = session("user_auth")['park_id'];
        $parkRoom = new ParkRoom();
        $map = ['build_block' => $build, 'park_id' => $parkId, 'room' => $room];
        $company = input('company');
        $data = [
            'img' => json_encode($datas['img']),
            'imgs' =>json_encode($datas['imgs']),
            'panorama' =>input('panorama'),
            'price' => input('price'),
            'area' => input('area'),
        ];
        if ($company){
            $data['company'] = $company;
            $data['type'] = input('type');
            $like = mb_substr($company, 0, 6);
            $info = WechatDepartment::where(['name' => ['like', "%$like%"]])->find();
            if ($info) {
                $data['company_id'] = $info['id'];
            } else {
                $data['company_id'] = 0;
            }
        }
        $res = $parkRoom->where($map)->update($data);
        if ($res) {
            $floor = $this->getFloor();

            return $this->success("保存成功",'',($floor));
        } else {

            return $this->error("保存失败");
        }

    }

    /**
     * 修改联系状态
     */
    public function changeStatus()
    {
        $id = input('id');
        $res = PeopleRent::where('id', $id)->update(['status' => 2]);
        if ($res) {

            return $this->success("修改成功");
        } else {

            return $this->error("修改失败");
        }
    }

    /**
     * 2已约，3已租，4下架 删除
     */
    public function delete()
    {
        $status = input('status');
        $roomId = input('room_id');
        $parkRoom = new ParkRoom();
        $map = ['id' => $roomId];
        $id = input('id');
        if ($status == 2) {
            $res = PeopleRent::where('id', $id)->delete();
        } elseif ($status == 3) {
            $res = $parkRoom->where($map)->update(['manage' => 2, 'company' => null, 'company_id' => 0, 'type' => null]);
        } else {
            $res = $parkRoom->where($map)->delete();
        }
        if ($res) {
            $floor = $this->getFloor();

            return $this->success("删除成功",'',($floor));
        } else {

            return $this->error("删除失败");
        }
    }
    /**
     * 保存base64图片
     * @param $key
     * @param $value
     * @param $user_id
     * @return int
     */
    public function setPic()
    {
        $value = input('img');
        $img = base64_decode($value);
        $str = '/uploads/picture/'.date('Y-m-d', time());
        $temp_path = ROOT_PATH.'/public/'.$str;
        if (!is_dir($temp_path)) {
            //不存在则新建
            createDir($temp_path);
        }
        $str1 = "/".sha1(time().rand(10000,99999)).".png";
        $path = $temp_path.$str1; //真实地址
        $res = file_put_contents($path, $img);//返回的是字节数
        if ($res) {

            return $str.$str1;
        } else {

            return ;
        }
    }

    /**
     * 已约->已租
     */
    public function save(){
        $parkRoom = new ParkRoom();
        $data = input('post.');
        $company = input('company');
        $like = mb_substr($company, 0, 6);
        $info = WechatDepartment::where(['name' => ['like', "%$like%"]])->find();
        if ($info) {
            $data['company_id'] = $info['id'];
        } else {
            $data['company_id'] = 0;
        }
        $map = ['id' => $data['id']];
        $res = $parkRoom->where($map)->update($data);
        if ($res) {
            $floor = $this->getFloor();

            return $this->success("保存成功",'',($floor));
        } else {

            return $this->error("保存失败");
        }

    }
    /**
     *
     */
    public function flooredit(){
        $parkRoom = new ParkRoom();
        Db::startTrans();
        try{
            $res1 = $parkRoom->where('id',1)->find();
            $res2 = $parkRoom->where('id',2)->find();
            if ($res1 && $res2){
                echo json_encode($res1);
                Db::commit();
            }

        }catch(Exception $e){
            Db::rollback();

            return  $e->getMessage();
        }

    }



}