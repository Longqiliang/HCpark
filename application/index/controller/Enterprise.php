<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 14:09
 */

namespace app\index\controller;

use app\common\model\ParkFloor;
use app\index\model\ParkCompany;
use app\index\model\ParkProduct;
use app\common\behavior\Service;
use app\index\model\ParkRoom;
use app\common\model\ParkRent;
use app\common\model\PeopleRent;
use app\common\model\Park;
use app\index\model\WechatUser;
use MongoDB\Driver\WriteError;

//园区企业
class Enterprise extends Base
{


    //企业列表
    public function index()
    {

        $park_id = session('park_id');
        $user_id = session('userId');
        $user = new WechatUser();
        $service = new Service();
        $parkcompany = new ParkCompany();
        $userinfo = $user->where('userid', $user_id)->find();
        $userinfo['top_company'] = is_array(json_decode($userinfo['top_company']))?json_decode($userinfo['top_company']):[];
        $top = array_reverse($userinfo['top_company']);
        $list = $parkcompany->where(['park_id' => $park_id, 'company_id' => ['notin', [78, 91]]])->select();
        $data = $this->rentlist();
        $this->assign('room', json_encode($data));
        $this->assign('list', json_encode($list));
        $this->assign('top_company', json_encode($top));
        return $this->fetch();
    }

    public function TopCompany()
    {
        $user = new WechatUser();
        $userinfo = $user->select();
        echo json_encode($userinfo);
        foreach ($userinfo as $value) {
            $top_company = json_encode([$value['department']]);
            $user->where('userid', $value['userid'])->update(['top_company' => $top_company]);
        }

    }

    public function editTopCompany()
    {
        $data = input('');
        $userid = session('userId');
        $user = new WechatUser();
        $userinfo = $user->where('userid', $userid)->find();
        $top =  is_array(json_decode($userinfo['top_company']))?json_decode($userinfo['top_company']):[];
        //新增置顶企业
        if ($data['type'] == 1) {
            array_push($top, $data['department']);

            $top_company = json_encode($top);
            $userinfo['top_company'] =$top_company;
        } elseif ($data['type'] == 2) {

            $result = array_udiff($top, [$data['department']],"myfunction");

            $userinfo['top_company'] = json_encode($result);

        } else {
            return $this->error('参数缺失');
        }
        $re = $userinfo->save();

        if ($re) {

            return $this->success('成功');
        } else {

            return $this->error('11'.$user->getError());
        }


    }
    //从企业页面进入楼盘表并自动定位企业房间位置
    public  function floorList(){
        $department = input('department');
        $parkRoom = new ParkRoom();
        $data =$this->rentlist();

        $info = $parkRoom->where(['manage'=>1,'company_id'=>$department])->order('id desc')->find();
        $map=['department_id'=>$department,'park_id'=>$info['park_id'],'build_block'=>$info['build_block']];
       // echo json_encode($data);
        $this->assign('room',json_encode($data));
        $this->assign('info',json_encode($map));
        return $this->fetch();
    }





    //引导页
    public function info()
    {

        $id = input('id');
        $CompanyProduct = new ParkProduct();
        $map = [
            'company_id' => $id,
            'type' => '1',
            'status' => 0
        ];
        //企业产品
        $product = $CompanyProduct->where($map)->select();
        //企业服务
        $map['type'] = 2;
        $service = $CompanyProduct->where($map)->select();
        $this->assign('product_num', count($product));
        $this->assign('service_num', count($service));
        $this->assign('id', $id);
        return $this->fetch();
    }

    //企业详情
    public function detail()
    {
        $id = input('id');
        $parkcompany = new ParkCompany();
        $CompanyProduct = new ParkProduct();
        $map = [
            'company_id' => $id,
            'type' => 1,
            'status' => 0

        ];
        //企业产品
        $product = $CompanyProduct->where($map)->select();
        //企业服务
        $map['type'] = 2;
        $service = $CompanyProduct->where($map)->select();
        foreach ($service as $v) {
            $v['content'] = preg_replace("/<(.*?)>/", "", $v['content']);
        }
        foreach ($product as $v) {
            $v['content'] = preg_replace("/<(.*?)>/", "", $v['content']);
        }
        $info = $parkcompany->where('id', $id)->find();

        $result = [
            'present' => $info['present'],
            'about_us' => $info['about_us'],
            'name' => $info['name'],
            'mobile' => $info['mobile'],
            'img' => $info['img'],
            'service_num' => count($service),
            'product_num' => count($product),
            'service' => $service,
            'product' => $product,
            'department_id'=>$id,
            'site'=>$info['site'],
            'wechat_name'=>$info['wechat_name'],
            'wechat_img'=>$info['wechat_img'],
            'wechat_description'=>$info['wechat_description'],
            'wechat_number'=>$info['wechat_number'],

        ];

        $this->assign('info', json_encode($result));
        return $this->fetch();

    }

    //企业服务或者企业产品
    public function product()
    {
        $id = input('id');
        $product = new ParkProduct();
        $info = $product->where('id', $id)->find();
        $data['name'] = $info['name'];
        $data['content'] = $info['content'];
        $this->assign('info', $data);
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
            ];
        } else {
            $setArr = [
                '80' => ['A', 'B', 'C', 'D'],
            ];
        }
        $roomArray = [[]];
        $newData = [];
        $parkRoom = new ParkRoom();
        $park = new Park();
        $parkRent = new ParkRent();
        $parkFloor = new ParkFloor();
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
                //每层楼房间数目
                foreach ($floor as $k => $v) {
                    $roomList = $parkRoom->where(['floor' => $v, 'build_block' => $element, 'del' => 0, 'park_id' => $number, 'manage' => 1])->order("room asc")->select();
                    if (count($roomList)) {
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
                            $roomArray[$k][$k1] = ['room' => $v1['room'], 'empty' => $status, 'id' => $v1['company_id'], 'room_id' => $roomsId,'panorama'=>$v1['panorama']];
                            $roomArray[$k] = array_slice($roomArray[$k], 0, $k1 + 1);
                        }
                    } else {
                        $roomArray[$k] = [];
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

        return $newData;


    }

}