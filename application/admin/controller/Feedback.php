<?php
/**
 * Created by PhpStorm.
 * User: aion
 * Date: 2017/6/7
 * Time: 下午9:57
 */

namespace app\admin\controller;

use app\common\model\Feedback as FeedbackModel;

class Feedback extends Admin
{
    public function index() {
        $list = FeedbackModel::where('park_id',session('user_auth')['park_id'])->order('id desc')->paginate(12);
        $this->assign('list', $list);

        return $this->fetch();
    }


    //反馈详情
    public function  detail() {
        $map = [
            'id' =>input('id'),
            'park_id'=>session('user_auth')['park_id'],
        ];

        $list = FeedbackModel::where($map)->find();
        if (!$list){
            $this->error("非法访问");
        }
        $this->assign('info',$list);
        return $this->fetch();
    }

    public function reply() {

        if(IS_POST){
            $map = [
                'id' =>input('id')
            ];
            $msg=[
                'reply'=>input('reply'),
                'status'=>1,
                'park_id'=>session('user_auth')['park_id'],
            ];

            $feedbackModel=new FeedbackModel;
            $result =$feedbackModel->validate(true)->save($msg, ['id'=>input('id')]);
            if($result){
                return $this->success('回复成功',Url('index'));
            }elseif($result === 0) {
                $this->error('没有更新内容');
            }else{
                return $this->error($feedbackModel->getError($result));
            }

        }else {
            $map = [
                'id' => input('id'),
                'park_id'=>session('user_auth')['park_id'],
            ];
            $list = FeedbackModel::where($map)->find();
            if (!$list){
                $this->error("非法访问");
            }
            $this->assign('info', $list);
            return $this->fetch();
        }
    }
    public function del() {
        $result = FeedbackModel::where('id', input('id'))->delete();
        if($result) {
            $this->redirect('index');
        } else {
            $this->error('删除失败');
        }
    }
}