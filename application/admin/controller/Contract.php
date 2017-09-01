<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 下午3:21
 */
namespace app\admin\controller;

use app\common\model\CompanyContract;
use app\common\model\WechatDepartment;

class Contract extends Admin {
    /*合同列表页*/
    public function index(){


        $map = [
            "park_id" => session("user_auth")['park_id']
        ];
        $list = CompanyContract::where($map)->order("create_time desc")->paginate();
        $this->assign("list",$list);
        return $this->fetch();
    }
    public function add(){
        $id = input('id');
        $contract = new CompanyContract();
        if (IS_POST){
            $data = input('post.');
            $data['create_time'] = time();
            $data['park_id'] = session("user_auth")['park_id'];
            $department = WechatDepartment::where("name",input('company'))->find();
            if ($department){
                $data['department_id'] = $department['id'];
            }
            $data['img'] = json_encode($data['img']);
            //return $data;
            $res = $contract->allowField(true)->save($data);
            if ($res){

                $this->success("添加成功");
            }else{

                $this->error("添加失败");
            }
            return $data;
        }else{
            $contractInfo = $contract->get("$id");

            $this->assign("info",$contractInfo);
        }


        return $this->fetch();
    }

}