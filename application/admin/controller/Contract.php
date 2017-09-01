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
            "park_id" => session("user_auth")['park_id'],
            'status' => 0
        ];
        $list = CompanyContract::where($map)->order("create_time desc")->paginate();
        int_to_string($list,['type'=>[1=>"租赁合同",2=>"物业合同",3=>"其他合同",]]);
        $this->assign("list",$list);
        return $this->fetch();
    }
    public function add(){

        $id = input('id');
        $contract = new CompanyContract();
        if (IS_POST){
            if (input("id")){
                $data = input('post.');
                $update = [
                    'img' =>json_encode($data['img']),
                    'company' =>input('company'),
                    'type' =>input("type"),
                    'remark' =>input('remark'),
                    'number' =>input('number')

                ];
                $department = WechatDepartment::where("name",input('company'))->find();
                if ($department){
                    $update['department_id'] = $department['id'];
                }

                $res = $contract->where('id',input('id'))->update( $update);
                if ($res){

                    $this->success("修改成功");
                }else{

                    $this->error("修改失败");
                }
            }else{
                $data = input('post.');
                $data['create_time'] = time();
                $data['park_id'] = session("user_auth")['park_id'];
                $department = WechatDepartment::where("name",input('company'))->find();
                if ($department){
                    $data['department_id'] = $department['id'];
                }
                $data['img'] = json_encode($data['img']);
                unset($data['id']);
                $res = $contract->allowField(true)->save($data);
                if ($res){

                    $this->success("添加成功");
                }else{

                    $this->error("添加失败");
                }
            }

        }else{
            $contractInfo = $contract->get($id);
            $this->assign('img',json_decode($contractInfo['img']));
            $this->assign("info",$contractInfo);

            return $this->fetch();
        }
    }
    /*删除新闻*/
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = CompanyContract::where('id', 'in', $ids)->update(['status' => -1]);

        if($result) {

            return $this->success('删除成功');

        } elseif(!$result) {

            return $this->error('删除失败');
        }
    }








}