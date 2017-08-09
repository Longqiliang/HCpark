<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * QQ: 2205446834@qq.com
 * Date: 2017/8/9
 * Time: 15:15
 */

namespace app\admin\controller;


use app\common\model\WechatDepartment ;
use app\common\model\WechatUser ;
use think\Db;
class Addressbook extends Admin
{

    //用户
    public function wechatUser(){

      $wechatuser= new  WechatUser();
        $search_user = input('search_user');
        $search_department =input('search_department');
        $map=[];
        if ($search_user != '') {
            $map['u.name'] = ['like','%'.$search_user.'%'];
        }else if($search_department != '') {
            $map['d.name'] = ['like', '%' . $search_department . '%'];
        }

        $list= $wechatuser->where($map)->order('id desc')->paginate(12);

        $page = $list->render();
        $info =$list->all();
        $sex=[1=>"男",2=>"女"];
        $status =[1=>"已激活",2=>"已禁用",4=>"未激活"];
        foreach($info as $key=>$val){
            $info[$key]['extattr']=json_decode($info[$key]['extattr']);
            $info[$key]['gender'] =$sex[$val['gender']];
            $info[$key]['status'] =  $status[$val['status']];
            $info[$key]['dname']=isset($val->departmentName->name)?$val->departmentName->name:"";
        }

        $this->assign('page',$page);
        $this->assign('info',$info);

        return $this->fetch();

    }
    //部门
    public function wechatDepartment(){
        $WechatDepartment=new WechatDepartment();
        // 默认从第3级智新泽地园区开始查找
        $id=3;
        if(input('id')){
            $id=input('id');
        }
        $info = $WechatDepartment->where('parentid', 'eq', $id)->order('id asc')->paginate(12);
        $this->assign('info', $info);
        return $this->fetch();
    }


    //编辑
    public function edit(){


    }

    //删除
    public function del(){


    }
}