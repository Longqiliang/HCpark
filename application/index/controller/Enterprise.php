<?php
/**
 * Created by PhpStorm.
 * User: Butaier
 * Date: 2017/8/14
 * Time: 14:09
 */
namespace app\index\controller;
use app\index\model\ParkCompany;
use app\index\model\ParkProduct;
use app\common\behavior\Service;
use app\index\model\ParkRoom;
use app\common\model\ParkRent;
use app\common\model\PeopleRent;
use app\common\model\Park;
//园区企业
class Enterprise extends Base{

    //企业列表
    public function index() {

        $park_id=session('park_id');
        $service =new Service();
        $parkcompany = new ParkCompany();
        $list = $parkcompany->where(['park_id'=>$park_id,'company_id'=>['notin',[78,91]]])->select();
        $data =$this->rentlist();
        $this->assign('room',json_encode($data));
        $this->assign('list',json_encode($list));
        return $this->fetch();
    }

    //引导页
    public function info() {

        $id = input('id');
        $CompanyProduct= new ParkProduct();
        $map=[
         'company_id'=>$id,
         'type'=>'1',
         'status'=>0
        ];
        //企业产品
        $product =  $CompanyProduct->where($map)->select();
        //企业服务
         $map['type']=2;
         $service =  $CompanyProduct->where($map)->select();
         $this->assign('product_num',count($product));
         $this->assign('service_num',count($service));
         $this->assign('id',$id);
         return $this->fetch();
    }

    //企业详情
    public function detail(){
        $id = input('id');
        $parkcompany= new ParkCompany();
        $CompanyProduct= new ParkProduct();
        $map=[
            'company_id'=>$id,
            'type'=>1,
            'status'=>0

        ];
        //企业产品
        $product =  $CompanyProduct->where($map)->select();
        //企业服务
        $map['type']=2;
        $service =  $CompanyProduct->where($map)->select();
         foreach ($service as $v){
             $v['content']=preg_replace("/<(.*?)>/","",$v['content']);
         }
        foreach ($product as $v){
            $v['content']=preg_replace("/<(.*?)>/","",$v['content']);
        }
        $info = $parkcompany->where('id',$id)->find();

       $result=[

           'present'=>$info['present'],
           'about_us'=>$info['about_us'],
           'name'=>$info['name'],
           'mobile'=>$info['mobile'],
           'img'=>$info['img'],
           'service_num'=>count($service),
           'product_num'=>count($product),
           'service'=>$service,
           'product'=>$product


       ];

        $this->assign('info',json_encode($result));
        return $this->fetch();

    }

  //企业服务或者企业产品
     public  function  product(){
        $id=input('id');
        $product=new ParkProduct();
        $info=  $product->where('id',$id)->find();
        $data['name']=$info['name'];
       $data['content']=$info['content'];
        $this->assign('info',$data);
        return $this->fetch();
     }


    public function rentlist()
    {
        $park_id = session('park_id');
        if($park_id==3){
            $setArr = [
                '3' => ['A', 'B'],
            ];

        }else{
            $setArr = [
                '80' => ['A', 'B', 'C', 'D'],

            ];

        }

        $newData = [];
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
                    $roomList = $parkRoom->where(['floor' => $v, 'build_block' => $element, 'del' => 0, 'park_id' => $number])->order("room asc")->select();
                    //判断房间是否出租
                    foreach ($roomList as $k1 => $v1) {
                        $res = ParkRent::where(['room_id' => $v1['id'], 'manage' => 0, 'status' => 0])->find();
                        if (!$res) {
                            $status = 0;
                            $roomsId = 0;
                        } else {
                            $rent = PeopleRent::where(['rent_id'=> $res['id'],'status'=>array('neq',-1)])->select();
                            if ($rent) {

                                if ($res['park_id'] == 3) {
                                    $status = 1;
                                } else {
                                    $status = 2;
                                }
                            } else {
                                $status = 1;
                            }
                            $roomsId = $res['room_id'];
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
                $map1 = ['park_id' => $number, "build_block" => $element, 'status' => 0, 'manage' => 0];
                $rentList = $parkRent->where($map1)->order('id desc')->limit(6)->select();
                if ($rentList) {
                    foreach ($rentList as $k => $v) {
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