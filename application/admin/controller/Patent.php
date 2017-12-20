<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/29
 * Time: 上午10:09
 */

namespace app\admin\controller;

use app\common\model\Patent as PatentModel;
class Patent extends  Admin
{
    //专利申请（首页）
    public function  index(){
        $patent = new PatentModel();
        $reult = $patent->getPatentbyParkid();
        $count = \app\common\model\Patent::getNumforUndone();
        $this->assign('count',$count);
        $this->assign('list',$reult);
        return $this->fetch();
    }


    //专利申请
    public function del()
    {
        $id = input('ids/a');
        $patent = new PatentModel();
        $res = $patent->where(['id' => ['in', $id]])->Update(['status' => -1]);
        if ($res) {
            return $this->success("删除成功");
        } else {
            return $this->error("删除失败", '', $patent->getError());
        }
    }

    //专利申请详情页
    public function show()
    {   $patent = new PatentModel();
        if (IS_POST) {
             //审核
            $data=input('');
             $res = $patent->check($data);
             if($res){
                 return $this->success('审核成功','patent/index');

             }else{
                 return $this->error('审核失败');
             }

        } else {

            $id = input('id');

            $info = $patent->getDetailbyId($id);
            $this->assign('info', $info);
            return $this->fetch();
        }
    }
    //导出
    public  function  out(){
    $id = input('id');
    $patent = new PatentModel();
    $patent->out($id);
    //return $this->success('成功','',$a);
    }



}