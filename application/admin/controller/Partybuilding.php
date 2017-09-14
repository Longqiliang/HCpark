<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/28
 * Time: 下午1:37
 */
namespace app\admin\controller;
use app\common\model\PartyBuilding as PartyBuildingModel;

class Partybuilding extends Admin{
    /*列表展示*/
    public function index(){
        $parkid = session('user_auth')['park_id'];
        $map = ['status'=> 1,'park_id'=>$parkid];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = PartyBuildingModel::where($map)->order('id desc')->paginate();
        $this->assign('list', $list);

        return $this->fetch();


    }
    /*党建新闻的添加及修改*/
    public function add(){
        $parkid = session('user_auth')['park_id'];
        if (IS_POST){
            $partyBuilding =new PartyBuildingModel();
            if(input('id')) {
                $_POST['park_id']=$parkid;
                $_POST['update_time'] =time();
                $result = $partyBuilding->validate(true)->save($_POST, ['id'=>input('id')]);
                if ($result){

                    return $this->success("修改成功！");
                }else{

                    return $this->error("修改失败");
                }
            }else{
                $_POST['park_id']=$parkid;
                $_POST['create_time'] =time();
                $_POST['status'] = 1;
                unset($_POST['id']);
                $result =  $partyBuilding->validate(true)->save($_POST);
                if ($result){

                    return $this->success("添加成功！");
                }else{

                    return $this->error($partyBuilding->getError());
                }
            }

        }else{
            $news = PartyBuildingModel::where('id','eq',input('id'))->find();
            $this->assign('news', $news);
            return $this->fetch();
        }
    }


    /*删除新闻*/
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = PartyBuildingModel::where('id', 'in', $ids)->update(['status' => -1]);

        if($result) {

            return $this->success('删除成功');

        } elseif(!$result) {

            return $this->error('删除失败');
        }
    }






}