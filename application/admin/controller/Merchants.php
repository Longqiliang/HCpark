<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/6
 * Time: 下午7:53
 */

namespace app\admin\controller;

use app\common\model\MerchantsCompany;
use app\common\model\MerchantsDiary;
use app\common\model\MerchantsRecord;
use app\common\model\MerchantsPlan;
use app\common\model\WechatUser;

class Merchants extends Admin
{
    //招商公司
    public function index()
    {
        $search = input('search');

        if (!empty($search)) {
            $map['company'] = array('like', '%' . $search . '%');
        }
        $park_id = session('user_auth.park_id');
        $mCompany = new MerchantsCompany();
        $wechatUser = new WechatUser();
        $data = [
            'park_id' => $park_id,
            'tagid' => 4

        ];
        $user = $wechatUser->where($data)->select();
        $userId = array();
        $userlist=array();
        foreach ($user as $value) {
            $data=[
                'user_id'=>$value['userid'],
                'name'=>$value['name']

            ];
            array_push($userlist,$data);
            array_push($userId, $value['userid']);

        }
        $map['user_id'] = array('in', $userId);
        $map['status']=array('gt',-1);
        $all_company = $mCompany->where($map)->paginate();
        foreach ($all_company as $value) {
            $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
            $value['park_id'] = $park_id;
            $value['create_time'] = !empty($value['create_time'])?date('Y-m-d',$value['create_time']):"";
            $value['update_time'] =  !empty($value['update_time'])?date('Y-m-d',$value['update_time']):"";
        }
        int_to_string($all_company, array('status' => array(1 => '招商中', 2 => '已招商')));

        $this->assign('userlist',$userlist);
        $this->assign('search', $search);
        $this->assign('list', $all_company);
        return $this->fetch();

    }

    //编辑和新增招商公司
    public function editCompany()
    {
        $id = input('id');
        $data = input('');
        $mCompany = new MerchantsCompany();
        //新增
        if (empty($id)) {
            $info = $mCompany->allowField(true)->save($data);
        } //编辑
        else {
            unset($data['id']);
            $info = $mCompany->allowField(true)->save($data, ['id' => $id]);
        }
        if ($info) {
            return $this->success("成功");
        } else {
            return $this->error('失败','',$mCompany->getError());
        }

    }

    //查看招商日志
    public function showRecord()
    {
        $mRecord = new MerchantsRecord;
        $id = input('id');
        $list = $mRecord->where('merchants_id', $id)->paginate();
        $this->assign('list', $list);
        return $this->fetch();
    }

    //招商日志详情
    public function recordInfo()
    {
        $mRecord = new MerchantsRecord();
        $Record_id = input('id');
        $info = $mRecord->where('id', $Record_id)->find();
        $info['company_name'] = isset($info->merchantsCompany->company) ? $info->merchantsCompany->company : "";
        $info['merchants_user'] = isset($info->merchantsCompany->user->name) ? $info->merchantsCompany->user->name : "";
        $info['merchants_area'] = isset($info->merchantsCompany->merchants_area) ? $info->merchantsCompany->merchants_area : "";
        $info['merchants_money'] = isset($info->merchantsCompany->merchants_money) ? $info->merchantsCompany->merchants_money : "";
        unset($info['merchantsCompany']);
        echo json_encode($info);
        $this->assign('info', $info);
        return $this->fetch();
    }



    //招商人员
    public function user()
    {
        $search = input('search');
        if (!empty($search)) {
            $map['company'] = array('like', '%' . $search . '%');
        }
        $park_id = session('user_auth.park_id');
        $wechatUser = new WechatUser();
        $data = [
            'park_id' => $park_id,
            'tagid' => 4
        ];
        $user = $wechatUser->where($data)->paginate();

        $this->assign('search', $search);
        $this->assign('list', $user);
        return $this->fetch();
    }

    //用户招商计划
    public function merchantsPlan()
    {
        $mPlan = new  MerchantsPlan();
        $user_id = input('user_id');
        $list = $mPlan->where('user_id', $user_id)->order('time desc')->paginate();
        foreach ($list as $value) {
            $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
        }
        $this->assign('list', $list);
        return $this->fetch();
    }

    //用户工作日志
    public function showDiary()
    {
        $mDiary = new  MerchantsDiary();
        $user_id = input('user_id');
        $list = $mDiary->where('user_id', $user_id)->order('create_time desc')->paginate();
        foreach ($list as $value) {
            $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
        }
        $this->assign('list', $list);
        return $this->fetch();
    }
    //删除招商公司
    public  function  deleteCompany(){
        $ids = input('ids/a');
        $mCompany = new MerchantsCompany();

        $result = $mCompany->where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('Merchants/index'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }

    }
    //删除工作日志
    public  function  deleteDiary(){


    }
    //删除招商日志
    public  function  deleteRecord(){


    }


}