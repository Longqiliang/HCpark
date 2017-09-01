<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/28
 * Time: 下午4:31
 */
namespace app\admin\controller;
use app\common\model\UnionLoabour as UnionLoabourModel;

class UnionLoabour extends Admin
{
    /*工会信息首页*/
    public function index() {
        $parkid = session('user_auth')['park_id'];
        $map = ['status'=> 1,'park_id'=>$parkid];
        $search = input('search');
        if ($search != '') {
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = UnionLoabourModel::where($map)->order('create_time desc')->paginate();

        $this->assign('list', $list);

        return $this->fetch();
    }
    /*修改*/
    public function add() {
        $parkid = session('user_auth')['park_id'];

        if(IS_POST) {
            $union = new UnionLoabourModel();
            if(input('id')) {
                $_POST['status'] = 1;
                $_POST['park_id']=$parkid;
                $result = $union->validate(true)->save($_POST, ['id'=>input('id')]);

            } else {
                $_POST['park_id']=$parkid;
                unset($_POST['id']);
                $result = $union->validate(true)->save($_POST);
            }

            if ($result) {
                $this->success('添加成功', url('UnionLoabour/index'));
            } elseif ($result === 0) {
                $this->error('没有更新内容');
            } else {
                $this->error($union->getError());
            }

        } else {
            $union = UnionLoabourModel::where('id','eq',input('id'))->find();
            $this->assign('res', $union);

            return $this->fetch();
        }
    }


    /*逻辑删除*/
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = UnionLoabourModel::where('id', 'in', $ids)->update(['status' => -1]);

        if($result) {

            return $this->success('删除成功', url('UnionLoabour/index'));

        } elseif(!$result) {

            return $this->error('删除失败');
        }
    }







}