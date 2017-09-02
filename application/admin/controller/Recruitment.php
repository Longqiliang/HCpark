<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/2
 * Time: 上午9:53
 */
namespace app\admin\controller;

use app\common\model\EnterpriseRecruitment as EnterpriseModel;

class Recruitment extends Admin
{
    public function index()
    {
        $parkid =session("user_auth")['park_id'];
        $search = input('search');
        $map=array(
            'park_id'=>$parkid,
            'status'=>['neq',-1],
        );
        if ($search != '') {
            $map['position'] = ['like','%'.$search.'%'];
        }
        $list = EnterpriseModel::where($map)->order('create_time  desc')->paginate();

        $this->assign('list',$list);
        return $this->fetch();
    }

    public  function add ()
    {
        if (IS_POST){
            $id=input('id');
            $parkid = session('user_auth')['park_id'];
            $_POST['park_id']=$parkid;
            $AboutModel=new EnterpriseModel;
            if ($id) {
                $result = $AboutModel->allowField(true)->validate(true)->save($_POST, ['id' => input('id')]);
            } else {
                unset($_POST['id']);
                $result = $AboutModel->allowField(true)->validate(true)->save($_POST);
            }

            if ($result) {

                return $this->success('添加成功', url('Recruitment/index'));
            } elseif ($result === 0) {

                return  $this->error('没有更新内容');
            } else {

                return   $this->error($AboutModel->getError());
            }
        }else {
            $result = EnterpriseModel::where('id','eq',input('id'))->find();
            $this->assign('res',$result);
            return $this->fetch();
        }
    }

//逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = EnterpriseModel::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('Recruitment/index'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }



}