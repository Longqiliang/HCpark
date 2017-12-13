<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/13
 * Time: 上午10:46
 */

namespace app\admin\controller;

use app\common\model\ActivityComment;
use app\common\model\Activity as ActivityModel;
class Activity extends Admin
{
    /**
     * 主页列表
     */
    public function index()
    {
        $park_id = session('user_auth')['park_id'];
        $search= input('search');

        $map = array(
            'status' => array('gt',-1),
            'park_id'=>$park_id
        );
        if(!empty($search)){
            $map['name']=['like','%'.$search.'%'];
        }
        $activity = new ActivityModel();
        $list = $activity->where($map)->order('start_time asc')->paginate(12,false,['query' => request()->param()]);
        int_to_string($list, array(
            'status' => array(0 => "禁用",1=>'预报名',2=>'开始报名'),
        ));
        $this->assign('list', $list);
        $this->assign('search', $search);
        return $this->fetch();
    }

    /*
     * 活动添加
     */
    public function add()
    {
        if (IS_POST) {
            $data = input('');
            $activity = new ActivityModel();
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if (empty($data['id'])) {
                unset($data['id']);
                $info = $activity->validate(false)->
                save($data);
            }else{
                $info = $activity->validate(false)->save($data,['id'=>$data['id']]);
            }
            if ($info) {
                return $this->success("保存成功", Url('Shop/index'));
            } else {
                return $this->error($activity->getError());
            }
        } else {
            $a = array('1'=>'a','2'=>'b','3'=>'c','4'=>'d','5'=>'e','6'=>'f','7'=>'g','8'=>'h','9'=>'i','10'=>'j','11'=>'k','12'=>'l','13'=>'m','14'=>'n','15'=>'o');
            $front_pic = array_rand($a,1);
            $this->assign('front_pic',$front_pic);
            $id = input('id');
            $msg = ActivityModel::get($id);

            $this->assign('info', $msg);

            return $this->fetch();
        }
    }
    /*报名记录*/
    public function recordinfo()
    {
        $id = input('id');
        $search = input('search');
        $map=[
            'activity_id' => $id,

        ];

        $Commentlist = ActivityComment::where($map)->paginate(12,false,['query' => request()->param()]);
        int_to_string($Commentlist, array('status' => array(0 => "未审核", 1 => "审核成功", 2=>"审核成功")));

        //echo ExchangeRecord::getLastSql();
        $this->assign('activity_id',$id);
        $this->assign("list", $Commentlist);
        $this->assign('search',$search);

        return $this->fetch();
    }



    /*删除报名记录*/
    public function delSign()
    {
        $id = input('id/a');

        $data = [
            'status' => -1
        ];
        $recordinfo = ActivityComment::where(['id'=>array('in',$id)])->delete();
        if ($recordinfo) {

            return $this->success('删除成功');
        } else {
            return $this->error('删除失败','',ExchangeRecord::getLastSql());
        }

    }

    /**
     * 删除商品功能
     */
    public function del(){
        $id = input('id/a');
        $data['status'] = '-1';
        $info = ActivityModel::where(['id'=>array('in',$id)])->update($data);
        if($info) {
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }
    }

}