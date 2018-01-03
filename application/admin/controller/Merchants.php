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
        $this->assign('park', $park_id);
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
        //1.开始招商时间有没有写
        if (empty($data['create_time'])) {
            return $this->error('开始招商时间未填');
        }
        //2.开始招商时间格式对不对
        $data['create_time'] = strtotime($data['create_time']);
        $is = $this->is_timestamp($data['create_time']);
        if (!$is) {
            return $this->error('开始招商时格式不对');
        }
        //3.完成招商时间有没有写
        if (empty($data['update_time']) && $data['status'] == 2) {
            return $this->error('完成招商时间未填');
        }
        //4.完成招商时间格式对不对
        if (!empty($data['update_time'])) {
            $data['update_time'] = strtotime($data['update_time']);
            $is = $this->is_timestamp($data['update_time']);
            if (!$is) {
                return $this->error('完成招商时间格式不对');
            }

        }
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
            $value['status_text'] = $value['status'] == 1 ? "招商中" : "已招商";
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
        //echo json_encode($info);
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
        //本月结束时间
        $endThismonth = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        //本月 开始时间
        $startThismonth = mktime(0, 0, 0, date('m'), 1, date('Y'));
        $user = $wechatUser->where($data)->paginate();

        foreach ($user as $value) {
            $diary = $value->merchantsDiary;
            $diarylist = array();
            $Company = $value->merchantsCompany;
            $companylist = array();
            foreach ($diary as $value2) {
                if ($value2['create_time'] > $startThismonth && $value['create_time'] < $endThismonth) {
                    array_push($diarylist, $value2);
                }
            }
            foreach ($Company as $value3) {
                if ($value3['update_time'] > $startThismonth && $value['update_time'] < $endThismonth && !empty($value3['update_time'])) {
                    array_push($companylist, $value3);
                }

            }
            unset($value['merchantsDiary']);
            unset($value['merchantsCompany']);
            $value['diary_num'] = count($diarylist);
            $value['company_num'] = count($companylist);
        }
        $this->assign('search', $search);
        $this->assign('list', $user);
        return $this->fetch();
    }

    //用户招商计划
    public
    function merchantsPlan()
    {
        $mPlan = new  MerchantsPlan();
        $weuser = new WechatUser();
        $user_id = input('user_id');
        $user = $weuser->where('userid', $user_id)->find();
        //echo json_encode($user['name']);
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
        $parkid = session("user_auth")['park_id'];
        $list = $mDiary->where(['user_id' => $user_id, 'park_id' => $parkid])->order('create_time desc')->paginate();
        foreach ($list as $value) {
            $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
            $work_today = '';
            $arrange_tomorrow = '';
            if (is_array(json_decode($value['work_today']))) {
                foreach (json_decode($value['work_today']) as $value1) {
                    $work_today .= $value1 . '   ';
                }
            }


            if (is_array(json_decode($value['arrange_tomorrow']))) {
                foreach (json_decode($value['arrange_tomorrow']) as $value2) {
                    $arrange_tomorrow .= $value2 . '  ';
                }
            }
            $value['work_today'] = $work_today;
            $value['arrange_tomorrow'] = $arrange_tomorrow;
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

            $is = $this->is_timestamp($data['time']);
            if (!$is) {
                $this->error('招商时间格式不正确');
            }
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
    public
    function deleteCompany()
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
    public
    function deleteDiary()
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
    public
    function deleteRecord()
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
    public
    function deletePlan()
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
    public
    function is_timestamp($timestamp)
    {
        if (strtotime(date('d-m-Y H:i:s', $timestamp)) === $timestamp) {
            return $timestamp;
        } else return false;
    }
}