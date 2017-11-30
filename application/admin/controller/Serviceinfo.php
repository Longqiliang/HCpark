<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/9/1
 * Time: 下午5:10
 */
namespace app\admin\controller;

use app\common\model\ServiceInformation as ServiceModel;
use app\common\behavior\Service;
class Serviceinfo extends Admin
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
            $map['title'] = ['like','%'.$search.'%'];
        }
        $list = ServiceModel::where($map)->order('create_time  desc')->paginate(12,false,['query' => request()->param()]);

        $this->assign('list',$list);
        return $this->fetch();
    }

    public  function add ()
    {
        if (IS_POST){

            $id=input('id');
            $parkid = session('user_auth')['park_id'];
            $_POST['park_id']=$parkid;
            $AboutModel=new ServiceModel;
            if ($id) {
                $result = $AboutModel->allowField(true)->validate(true)->save($_POST, ['id' => input('id')]);
            } else {
                unset($_POST['id']);
                $result = $AboutModel->allowField(true)->validate(true)->save($_POST);
            }

            if ($result) {

                return $this->success('添加成功', url('Serviceinfo/index'));
            } elseif ($result === 0) {

                return  $this->error('没有更新内容');
            } else {

                return   $this->error($AboutModel->getError());
            }
        }else {
            $result = ServiceModel::where('id','eq',input('id'))->find();
            $this->assign('res',$result);
            return $this->fetch();
        }
    }
    public  function  send(){

        $info = ServiceModel::get(input('id'));

        if ($info) {
            $des = msubstr(str_replace('&nbsp;','',strip_tags($info['content'])), 0, 36);
            $message= [
                'title' => $info['title'],
                'description' => $des,
                'url' => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/infoDetail/id/' . input('id'),
                'picurl' => empty($info['front_cover'])?'http://' . $_SERVER['HTTP_HOST'] .'/index/images/news/news.jpg':'http://'.$_SERVER['HTTP_HOST'] .$info['front_cover']
            ];

            $result =  Service::sendNewsNews($message,'15706844655');
//            var_dump($result);
//            var_dump($weObj->errCode.'|'.$weObj->errMsg);
            if ($result['errcode'] == 0 ) {
                ServiceModel::where('id', input('id'))->update(['is_send' => 1]);
                return $this->success('推送成功');
            } else {
                return $this->error('推送失败');
            }
        } else {
            $this->error('参数错误');
        }






    }

//逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = ServiceModel::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('Serviceinfo/index'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }



}