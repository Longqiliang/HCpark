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

//园区企业
class Enterprise extends Base{

    //企业列表
    public function index() {
        $park_id=session('park_id');
        $parkcompany = new ParkCompany();
        $list = $parkcompany->where('park_id',$park_id)->select();
        $this->assign('list',json_encode($list));
        return $this->fetch();

    }

    //引导页
    public function info() {

        $id = input('id');
        $CompanyProduct= new ParkProduct();
        $map=[
         'company_id'=>$id,
         'type'=>'1'
     ];
        echo json_encode($map);
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
            'type'=>1
        ];
        //企业产品
        $product =  $CompanyProduct->where($map)->select();
        //企业服务
        $map['type']=2;
        $service =  $CompanyProduct->where($map)->select();

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

}