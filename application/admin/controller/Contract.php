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
use think\Image;

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
                foreach($data['img'] as $k=>$v){
                    $data['img'][$k]=str_replace("http://".$_SERVER['HTTP_HOST'],"",$v);
                }
                if ($data['img']) {
                    foreach ($data['img'] as $k1 => $v1) {
                        if (is_file(PUBLIC_PATH . $v1)) {
                            $path = str_replace(".", "_s.", $v1);
                            $image = Image::open(PUBLIC_PATH . $v1);
                            $image->thumb(355, 188)->save(PUBLIC_PATH . $path);
                            $data['imgs'][$k1] = $path;
                        } else {
                            $data['imgs'][$k1] = $data['img'][$k1];
                        }
                    }
                }
                $update = [
                    'img' =>json_encode($data['img']),
                    'imgs' => json_encode($data['imgs']),
                    'company' =>input('company'),
                    'type' =>input("type"),
                    'remark' =>input('remark'),
                    'number' =>input('number'),
                    'other_name' => input('other_name'),
                ];
                $like = mb_substr($update['company'], 0, 6);
                $department = WechatDepartment::where(['name' => ['like', "%$like%"]])->find();
                if ($department){
                    $result = CompanyContract::where(['type'=>$update['type'],'department_id'=>$department['id'],'id'=>['neq',input("id")],'status'=>0,'other_name'=>$update['other_name']])->find();
                    if ($result){

                        $this->error("已存在合同信息");
                    }


                }
                if ($department){
                    $update['department_id'] = $department['id'];
                }

                $res = $contract->where('id',input('id'))->update($update);
                if ($res){

                    $this->success("修改成功");
                }else{

                    $this->error("修改失败");
                }
            }else{
                $data = input('post.');
                $like = mb_substr($data['company'], 0, 6);
                $department = WechatDepartment::where(['name' => ['like', "%$like%"]])->find();
                if ($department){
                    $result = CompanyContract::where(['type'=>$data['type'],'department_id'=>$department['id'],'id'=>['neq',input("id")],'status'=>0,'other_name'=>$data['other_name']])->find();
                    if ($result){

                        $this->error("已存在合同信息");
                    }



                }
                if ($department){
                    $data['department_id'] = $department['id'];
                    $data['company'] = $department['name'];
                }
                foreach($data['img'] as $k=>$v){
                    $data['img'][$k]=str_replace("http://".$_SERVER['HTTP_HOST'],"",$v);
                }
                if ($data['img']) {
                    foreach ($data['img'] as $k1 => $v1) {
                        if (is_file(PUBLIC_PATH . $v1)) {
                            $path = str_replace(".", "_s.", $v1);
                            $image = Image::open(PUBLIC_PATH . $v1);
                            $image->thumb(355, 188)->save(PUBLIC_PATH . $path);
                            $data['imgs'][$k1] = $path;
                        } else {
                            $data['imgs'][$k1] = $data['img'][$k1];
                        }
                    }
                }
                $data['create_time'] = time();
                $data['park_id'] = session("user_auth")['park_id'];

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