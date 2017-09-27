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
        $search = input('search');
        $parkid  =session("user_auth")['park_id'];
        $map = ['park_id'=>$parkid];
        if (!empty($search)) {
            $map['name'] = ['like', "%$search%"];
        }
        $companyList = ParkCompany::where($map)->order('id  asc')->paginate();
        foreach ($companyList as $k=>$v){
            $v['present'] = mb_substr(strip_tags($v['present']),0,30);
        }
       /// dump($companyList);
        $this->assign('list',$companyList);

        return  $this->fetch();
    }
    /*企业详情展示*/
    public  function add(){
        $id = input('id');
        $companyInfo = ParkCompany::get($id);
        $this->assign('companyInfo',$companyInfo);

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
                $res=$parkCompany->where('id',$data['id'])->update($data);

            }else{
                $res=$parkCompany->data($data,true)->isUpdate($isUpdate)->save();
            }
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
        $parkProduct=new ParkProduct();
        $res=$parkProduct->where(['id'=>$id])->update(input('post.'));
        if ($res){

            return $this->success("修改成功");
        }else{

            return $this->error("修改失败");
        }

    }
    /*园区产品或服务的添加*/
    public function edit(){
        $parkcompany =new ParkProduct();
        unset($_POST['file']);
        $_POST['create_time']=time();
        //return  $_POST;
        $res=$parkcompany->validate(true)->save($_POST);
        if ($res){

            $this->success("添加成功");
        }else{

            $this->error($parkcompany->getError());
        }

    }
    /*修改企业信息*/
    public function changeinfo(){
        $id=input("id");
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
    /*产品服务列表*/
    public function product(){
        $id = input('id');
        $type = input('type');
        $list = ParkProduct::where(['company_id'=>$id,'status'=>0,'type'=>$type])
                            ->order('create_time desc')
                            ->paginate();
        $this->assign('list',$list);
        $this->assign('type',$type);
        $this->assign('companyId',$id);
        return $this->fetch();
    }


    /*删除产品或服务*/
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = ParkProduct::where('id', 'in', $ids)->update(['status' => -1]);

        if($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }

}