<?php

namespace app\index\controller;

use app\index\model\WechatUser;
use think\Controller;
use wechat\TPWechat;
use wechat\TPQYWechat;
use think\Loader;
use app\common\model\CompanyContract;
use app\index\model\Park;
use app\index\model\WechatTag;
use think\Db;
use app\index\model\MerchantsPlan;
use app\index\model\MerchantsDiary;
use app\index\model\MerchantsRecord;
use app\index\model\MerchantsCompany;
use app\common\model\FeePayment;

use org\ImageImagick;

class Partymanage extends Base
{
    /** 园区管理首页 **/
    public function index()
    {
        $userid = session('userId');
        $park_id = session('park_id');
        $res = Db::table('tb_wechat_user')
            ->alias('u')
            ->join('__WECHAT_DEPARTMENT__ d', 'u.department=d.id')
            ->field('d.id,u.tagid')
            ->where('u.userid', 'eq', $userid)
            ->find();
        $id = input('id');//选择园区
        $departmentid = $res['id'];//部门ID
        $tagid = $res['tagid'];//标签ID
        //所有园区领导,能看到所有园区
        if ($departmentid == 1 && $tagid == 1) {
            $res = Park::field('id,name')->select();
            if (input('id')) {
                $res = Park::where('id', 'eq', $id)->field('id,name')->select();

            }
        } else {
            //只能看到自己园区
            $res = Park::where('id', 'eq', $park_id)->field('id,name')->select();
        }

        $this->assign('res', json_encode($res));
        return $this->fetch();
    }

    /** 园区统计 **/
    public function statistics()
    {
        //首页所选园区ID
        $id = input('id');

        //园区统计
        $res = Park::where('id', 'eq', $id)->field('id,name,address,images,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->select();

        $this->assign('res', json_encode($res));
        return $this->fetch();
    }

    /***合同管理*/
    public function contract()
    {
        $data[0] = CompanyContract::where(["park_id" => session("park_id"), 'type' => 1])->count();
        $data[1] = CompanyContract::where(["park_id" => session("park_id"), 'type' => 2])->count();
        $contract[0] = $data[0] + $data[1];
        $contract[1] = $data[0];
        $contract[2] = $data[1];
        $array = [
            'total' => ['name' => "总合同数", 'count' => $contract[0]],
            'rent' => ['name' => "租赁合同", 'count' => $contract[1]],
            'property' => ['name' => "物业合同", 'count' => $contract[2]]
        ];
        return json_encode($array);
        $this->assign('info', json_encode($array));

        return $this->fetch();
    }

    /*合同列表*/
    public function managelist()
    {
        $type = input("type");
        $list = CompanyContract::where(["park_id" => session("park_id"), 'type' => $type])
            ->order("create_time desc")
            ->limit(6)
            ->select();
        $name = "";
        switch ($type) {
            case 1 :
                $name = "租赁合同";
                break;
            case 2 :
                $name = "物业合同";
                break;
            case 3 :
                $name = "其他合同";
                break;
        };
        $this->assign('list', json_encode($list));
        $this->assign('name', $name);

        return $this->fetch();
    }

    /*合同下拉刷新*/
    public function listManage()
    {
        $type = input("type");
        $len = input("length");
        $list = CompanyContract::where(["park_id" => session("park_id"), 'type' => $type])
            ->order("create_time desc")
            ->limit($len, 6)
            ->select();
        if ($list) {

            return json(['code' => 1, 'data' => json_encode($list)]);
        } else {

            return json(['code' => 0, 'msg' => "没有更多内容了"]);
        }

    }

    /*合同详情*/
    public function manageDetail()
    {
        $id = input('id');
        $manageInfo = CompanyContract::get($id);
        $info = [
            'extra' => $manageInfo['remark'],
            'img' => json_decode($manageInfo['img']),
            'imgs' => []
        ];

        if ($info['img']) {
            foreach ($info['img'] as $k => $v) {
                $small_img = $this->getThumb($v, 100, 100);
                $info['imgs'][$k] = $small_img;
            }
        }
        //return  dump($info);
        $this->assign('info', json_encode($info));

        return $this->fetch();
    }

    /*招商管理*/
    public function merchants()
    {
        $userid = session('userId');
        $weuser = new WechatUser();
        $mCompany = new MerchantsCompany();
        $mRecord = new MerchantsRecord();
        $mDiary = new MerchantsDiary();
        $mPlan = new MerchantsPlan();
        $park_id = input('park_id');
        $user = $weuser->where('userid', $userid)->find();
        $is_boss = $user['tagid'] == 1 ? "yes" : "no";
        $this->assign('is_boss', $is_boss);
        //总领导
        if ($user['department'] == 1 && $user['tagid'] == 1) {
            $all_company = $mCompany->select();
            $doing = array();
            $finish = array();
            if ($park_id == 1) {
                foreach ($all_company as $value) {
                    if ($value['status'] == 1) {
                        array_push($doing, $value);

                    } else {
                        array_push($finish, $value);
                    }
                }
            } else {
                foreach ($all_company as $value) {
                    if ($value['status'] == 1 && $value->user->park_id == $park_id) {
                        array_push($doing, $value);

                    } else if ($value['status'] == 2 && $value->user->park_id == $park_id) {
                        array_push($finish, $value);
                    }
                }
            }

            //个人统计
            if ($park_id == 1) {
                $map = [
                    'tagid' => 4
                ];
            } else {
                $map = [
                    'tagid' => 4,
                    'park_id' => $park_id
                ];
            }

            $merchantUser = $weuser->where($map)->select();
            $this->assign('personal_statistics', $merchantUser);

            $merchantUserid = array();
            foreach ($merchantUser as $value) {
                array_push($merchantUserid, $value['userid']);
            }
            $data = [
                'user_id' => array('in', $merchantUserid)

            ];
            //工作日志
            $diaryList = $mDiary->where($data)->order('create_time desc')->select();
            foreach ($diaryList as $value) {
                $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
            }


        } else {
            //工作日志
            $diaryList = $mDiary->where('user_id', $userid)->order('create_time desc')->select();
            //招商进度
            $all_company = $mCompany->where('user_id', $userid)->select();
            $doing = array();
            $finish = array();
            foreach ($all_company as $value) {
                if ($value['status'] == 1) {
                    array_push($doing, $value);

                } else if ($value['status'] == 2) {
                    array_push($finish, $value);
                }

            }
            //TODO 招商人员的 个人统计

        }

        //招商进度
        $this->assign('undone', json_encode($doing));
        $this->assign('finish', json_encode($finish));
        //工作日志
        $this->assign('diaryList', $diaryList);
        //招商统计
        $this->assign('undone_num', count($doing));
        $this->assign('finish', count($finish));
        return $this->fetch();
    }

    //企业详情
    public function companyInfo()
    {
        $id = input('id');
        $mCompany = new MerchantsCompany();
        $mRecord = new MerchantsRecord();
        $Company = $mCompany->where('id', $id)->find();
        $Record = $mRecord->where('merchants_id', $id)->order('create_time')->select();
        $this->assign('company', $Company);
        $this->assign('records', $Record);
        return $this->fetch();

    }
    //个人统计
    public function personalStats()
    {
        return $this->fetch();
    }

    //个人统计详情
    public function statisticsInfo($user_id)
    {


        $this->fetch();
    }


    //工作日志详情
    public function diaryInfo()
    {
        $id = input('id');
        $mDiary = new MerchantsDiary();
        $info = $mDiary->where('id', $id)->find();
        $info['user_name'] = isset($info->user->name) ? $info->user->name : "";
        $this->assign('info', $info);
        return $this->fetch();

    }


    /*园区列表*/
    public function parkList()
    {
        $park = new Park();
        $list = $park->select();
        foreach ($list as $k => $v) {
            $data[$k] = [
                'name' => $v['name'],
                'address' => $v['address'],

            ];
        }
        $this->assign('list', $data);

        return $this->fetch();
    }

    /*公司合同列表*/
    public function serviceContract()
    {
        $type = input('type');
        $departmentId = input('id');
        $map = [
            'type' => $type,
            'department_id' => $departmentId,
        ];
        if ($type == 3) {
            $info = CompanyContract::where($map)->select();
        }
        $info = CompanyContract::where($map)->find();

        $this->assign('info', $info);

        return $this->fetch();
    }

    /*公司缴费记录*/
    public function feeRecord()
    {
        $departmentId = input('id');
        $map = ['company_id' => $departmentId];
        $list = FeePayment::where($map)->order('id desc')->limit(6);
        foreach ($list as $k => $v) {
            $info[$k] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'status' => $v['status'],
                'time' => $v['create_time'],
                'pay' => $v['fee'],
            ];
        }
        $this->assign("info", json_encode($info));
        return $this->fetch();
    }

    /*加载更多缴费记录*/
    public function moreRecode()
    {
        $departmentId = input('id');
        $len = input("length");
        $map = ['company_id' => $departmentId];
        $list = FeePayment::where($map)->order('id desc')->limit($len, 6);
        if ($list) {
            foreach ($list as $k => $v) {
                $info[$k] = [
                    'id' => $v['id'],
                    'name' => $v['name'],
                    'status' => $v['status'],
                    'time' => $v['create_time'],
                    'pay' => $v['fee'],
                ];
            }

            $this->success(['code' => 1, 'data' => json_encode($info)]);
        } else {

            $this->error(['code' => 0, 'data' => "没有更多了"]);
        }


    }


}
