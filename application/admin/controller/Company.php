<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/15
 * Time: 上午9:44
 */
namespace app\admin\controller;

use app\common\model\ParkCompany;
use app\common\model\WechatDepartment as WechatDepartmentModel;
use app\common\model\ParkProduct;

class Company extends Admin
{
    /*企业详细列表*/
    public function index (){
        $parkid =session("user_auth")['park_id'];
        $companyList=ParkCompany::where(['park_id'=>$parkid])->order('id  asc')->paginate();



       /// dump($companyList);
        $this->assign('list',$companyList);

        return  $this->fetch();
    }
    /*企业详情的添加及修改*/
    public  function add(){
        $id =input('id');
        $companyInfo=ParkCompany::get($id);
        $list = ParkProduct::where(['type'=>1])
                ->order('create_time desc')
                ->paginate();

        $list1=ParkProduct::where(['type'=>2])
            ->order('create_time desc')
            ->paginate();
        $this->assign('companyInfo',$companyInfo);
        $this->assign('list',$list);
        $this->assign('list1',$list1);
        return $this->fetch();
    }
    /*同步企业信息*/
    public function getCompany(){
        $deleteId=[];
        $parkid =session("user_auth")['park_id'];
        $parkCompany =new ParkCompany();
        $companyList=WechatDepartmentModel::where(['parentid'=>4])->select();
        foreach ($companyList as $k=>$v){
            $data=[
                'id'=>$v['id'],
              'name'=>$v['name'],
              'park_id'=>$parkid,
              'company_id'=>$v['id'],
            ];
            $number[$k]=$v['id'];
            $isUpdate = false;
            if (ParkCompany::get($data['id'])) {
                $isUpdate = true;
            }
            $res=$parkCompany->data($data,true)->isUpdate($isUpdate)->save();

        }
            $parkNumber=ParkCompany::where(['park_id'=>$parkid])->select();
            foreach($parkNumber as $k=>$v){
                $companyNumber[$k]=$v['id'];
            }
            foreach ($companyNumber as $v){
                if (!in_array(intval($v), $number)){
                    $deleteId[] =$v;
                }
            }
            foreach($deleteId as $v){
                ParkCompany::where(['id'=> $v])->delete();
            }

            return $this->success('同步成功');


    }
    /*修改企业简介*/
    public function send(){
        $id =input("id");
        $content = input('content');
        $res=ParkCompany::update(['present'=>$content],['id'=>$id]);
        if ($res){

            return $this->success("添加成功");
        }else{

            return  $this->error("添加失败");
        }

    }
    /*修改企业关于我们*/
    public function sendAbout(){

        $id =input("id");
        $about = input('abouts');
        $res=ParkCompany::update(['about_us'=>$about],['id'=>$id]);
        if ($res){

            return $this->success("添加成功");
        }else{

            return  $this->error("添加失败");
        }

    }
    /*获取企业的服务或产品*/
    public function getCompanyserver(){
        $id=input("id");
        $res =ParkProduct::get($id);
        return  $res;
    }
    /*修改企业的产品或服务*/
    public function updateCompany(){
        $id=input("id");
        //return  $_POST;
        $res=ParkProduct::where(['id'=>$id])->update(input('post.'));
        if ($res){

            return $this->success("修改成功");
        }else{

            return $this->error("修改失败");
        }

    }
    public function edit(){
        $parkcompany =new ParkProduct();
        unset($_POST['file']);
        $_POST['create_time']=time();
        //return  $_POST;
        $res=$parkcompany->save($_POST);
        if ($res){

            $this->success("添加成功");
        }else{

            $this->error("添加失败");
        }

    }

    public function companyMobile(){
        $id=input("id");
       // return dump($_POST);
        if(empty(input('img'))){
            $_POST['img'] =$_POST['images'];

        }
        unset($_POST['images']);
        $res=ParkCompany::where(['id'=>$id])->update($_POST);
        if ($res){

            return $this->success("修改成功");
        }else{

            return $this->error("修改失败");
        }



    }







}