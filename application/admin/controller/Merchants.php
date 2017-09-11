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
        $userlist = array();
        foreach ($user as $value) {
            $data = [
                'user_id' => $value['userid'],
                'name' => $value['name']

            ];
            array_push($userlist, $data);
            array_push($userId, $value['userid']);

        }
        $map['user_id'] = array('in', $userId);
        $map['status'] = array('gt', -1);
        $all_company = $mCompany->where($map)->paginate();
        foreach ($all_company as $value) {
            $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
            $value['park_id'] = $park_id;
            $value['create_time'] = !empty($value['create_time']) ? date('Y-m-d', $value['create_time']) : "";
            $value['update_time'] = !empty($value['update_time']) ? date('Y-m-d', $value['update_time']) : "";
        }
        int_to_string($all_company, array('status' => array(1 => '招商中', 2 => '已招商')));
        $this->assign('park',$park_id);
        $this->assign('userlist', $userlist);
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
            return $this->error('失败', '', $mCompany->getError());
        }

    }

    //查看招商日志
    public function showRecord()
    {
        $mRecord = new MerchantsRecord;
        $id = input('id');
        $list = $mRecord->where('merchants_id', $id)->paginate();
        foreach ($list as $value) {
            $value['merchants_date'] = !empty($value['merchants_date']) ? date('Y-m-d', $value['merchants_date']) : "";
            $value['create_time'] = !empty($value['create_time']) ? date('Y-m-d', $value['create_time']) : "";
        }
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
        $weuser = new WechatUser();
        $user_id = input('user_id');
        $user = $weuser->where('userid', $user_id)->find();
        echo json_encode($user['name']);
        $this->assign('name', $user['name']);
        $this->assign('userid', $user['userid']);

        $list = $mPlan->where('user_id', $user_id)->order('time desc')->paginate();
        foreach ($list as $value) {
            $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
            $value['time'] = date('Y-m', $value['time']);
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

    //招商人员指定招商计划 编辑和修改
    public function editPlan()
    {
        $id = input('id');
        $data = input('');

        $mPlan = new MerchantsPlan();
        //新增
        if (empty($id)) {
            $data['time'] = strtotime($data['time']);
            $map = [
                'user_id' => $data['user_id'],
                'time' => $data['time']

            ];
            $is_has = $mPlan->where($map)->select();
            if (count($is_has) > 0) {

                return $this->error('当前时间已经计划');
            }
            $info = $mPlan->allowField(true)->save($data);
        } //编辑
        else {
            unset($data['id']);
            $data['time'] = strtotime($data['time']);
            $is = $this->is_timestamp($data['time']);
            if ($is) {
                $info = $mPlan->allowField(true)->save($data, ['id' => $id]);
            } else {
                $this->error('招商时间格式不正确');
            }

        }
        if ($info) {
            return $this->success("成功");
        } else {

            return $this->error('失败', '', $mPlan->getError());
        }

    }

    //删除招商公司
    public function deleteCompany()
    {
        $ids = input('ids/a');
        $mCompany = new MerchantsCompany();

        $result = $mCompany->where('id', 'in', $ids)->update(['status' => -1]);
        if ($result) {
            return $this->success('删除成功');
        } elseif (!$result) {
            return $this->error('删除失败');
        }

    }

    //删除工作日志
    public function deleteDiary()
    {
        $ids = input('ids/a');
        $mDiary = new MerchantsDiary();

        $result = $mDiary->where('id', 'in', $ids)->delete();
        if ($result) {
            return $this->success('删除成功');
        } elseif (!$result) {
            return $this->error('删除失败');
        }

    }

    //删除招商日志
    public function deleteRecord()
    {
        $ids = input('ids/a');
        $mRecord = new MerchantsRecord();

        $result = $mRecord->where('id', 'in', $ids)->delete();
        if ($result) {
            return $this->success('删除成功');
        } elseif (!$result) {
            return $this->error('删除失败');
        }

    }

    //删除招商指标
    public function deletePlan()
    {
        $ids = input('ids/a');
        $mPlan = new MerchantsPlan();

        $result = $mPlan->where('id', 'in', $ids)->delete();
        if ($result) {
            return $this->success('删除成功');
        } elseif (!$result) {
            return $this->error('删除失败');
        }

    }

    //判断是不是时间戳
    public function is_timestamp($timestamp)
    {
        if (strtotime(date('d-m-Y H:i:s', $timestamp)) === $timestamp) {
            return $timestamp;
        } else return false;
    }
}