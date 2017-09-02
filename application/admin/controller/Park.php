<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/17
 * Time: 下午5:57
 */
namespace app\admin\controller;
use app\common\model\Park as ParkModel;


class Park extends Admin
{
     /*园区信息*/
    public function index(){
        $parkid =3;
        if (IS_POST){
            $park = new ParkModel();
            $res=$park->allowField(true)->validate(true)->save(input("post."),['id'=>$parkid]);
            if ($res){
                $this->success("保存成功");
            }else{
                $this->error($park->getError());
            }
        }

        $info=ParkModel::where(['id'=>$parkid])->find();

        $this->assign('info',$info);
        return $this->fetch();
    }

    public function parkProfile(){

        echo json_encode(input(''));exit;



    }






}