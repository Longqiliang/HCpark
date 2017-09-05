<?php

namespace app\index\controller;

use app\common\model\ParkRoom;
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
use app\index\model\News;

class Partymanage extends Base
{
    /** 园区管理首页 **/
    public function index()
    {
        $userid = session('userId');
        $park_id = session('park_id');
        $user = WechatUser::where('userid', 'eq', $userid)->field('department,tagid')->find();
        $map = [
            'type' => 2,
            'status' => 1,
        ];
        //所有园区领导,能看到所有园区
        if ($user['department'] == 1 && $user['tagid'] == 1) {
            $res = Park::where('status', 'eq', 1)->field('id,name')->select();
            $news = News::where($map)->order('create_time desc')->field('id,title')->find();
        } else {
            //只能看到自己园区
            $res = Park::where('id', 'eq', $park_id)->where('status', 'eq', 1)->field('id,name')->select();
            $news = News::where($map)->where('park_id', session("park_id"))->order('create_time desc')->field('id,title')->find();
        }

        $this->assign('res', json_encode($res));
        return $this->fetch();
    }

    /** 园区内部通告列表 **/
    public function newslist()
    {
        //首页所选园区ID
        $parkid = input('id');
        if ($parkid == 'a') {
            $map = [
                'type' => 2,
                'status' => 1,
            ];
        } else {
            $map = [
                'park_id' => $parkid,
                'type' => 2,
                'status' => 1,
            ];
        }
        $list = News::where($map)->order('create_time desc')->field('id,title,views,create_time,park_id')->select();
        $this->assign('list', json_encode($list));
        return $this->fetch();
    }

    /** 内部通告详情页 **/
    public function newsdetail()
    {
        $id = input('id');
        $map = [
            'id' => $id,
            'type' => 2,
            'status' => 1,
        ];
        $res = News::where($map)->find();

        $this->assign('res', json_encode($res));
        return $this->fetch();
    }

    /** 园区统计 **/
    public function statistics()
    {
        //首页所选园区ID
        $id = input('id');
        if ($id == 'a') {
            //园区统计
            $res = Park::field('id,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->find();
            $list = Park::field('id,name,address,images')->select();

        } else {
            $res = Park::where('id', 'eq', $id)->field('id,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->find();
            $list = Park::where('id', 'eq', $id)->field('id,name,address,images')->find();
        }

        $this->assign('list', json_encode($list));
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
                $small_img = $this->getThumb($v, 163, 230);
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
        $mDiary = new MerchantsDiary();
        $park_id = input('park_id');
        $user = $weuser->where('userid', $userid)->find();
        $is_boss = $user['tagid'] == 1 ? "yes" : "no";
        $this->assign('is_boss', $is_boss);
        //领导权限
        if ( $user['tagid'] == 1) {
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
            //招商统计图表所需数据格式
              $finish2=$this->merchantsComment($finish,1);
              $doing2 =$this->merchantsComment($doing,2);

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
                unset($value['user']);
            }
            echo "领导权限";
        }
        //个人权限
        else {
              echo "个人权限";
            //工作日志
            $diaryList = $mDiary->where('user_id', $userid)->order('create_time desc')->select();
            foreach ($diaryList as $value) {
                $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
                unset($value['user']);
            }
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
            $date_str = date('Y-m-d', time());
            //封装成数组
            $arr = explode("-", $date_str);
            //参数赋值
            //年
            $year = $arr[0];
            //月，输出2位整型，不够2位右对齐
            $month = sprintf('%02d', $arr[1]);
            $personal = $this->statisticsCommon($userid, $year, $month);

        }
        $personallist = isset($merchantUser) ? $merchantUser : "";
        $personalinfo = isset($personal) ? $personal : "";
        //招商进度
        $this->assign('undone', json_encode($doing));
        $this->assign('finish', json_encode($finish));
        //招商统计图
        $this->assign('undone2', json_encode($doing2));
        $this->assign('finish2', json_encode($finish2));
        //工作日志
        $this->assign('diaryList', json_encode($diaryList));
        //招商统计
        $this->assign('undone_num', count($doing));
        $this->assign('finish_num', count($finish));
        //招商个人统计(个人)
        $this->assign('personalinfo', $personalinfo);
        //招商个人统计（领导）
        $this->assign('personallist', json_encode($personallist));
        return $this->fetch();
    }

    //招商统计图表所需数据格式
    public function merchantsComment($mCompany, $type)
    {
        $data = [
            '01' => array(),
            '02' => array(),
            '03' => array(),
            '04' => array(),
            '05' => array(),
            '06' => array(),
            '07' => array(),
            '08' => array(),
            '09' => array(),
            '10' => array(),
            '11' => array(),
            '12' => array(),
        ];
        //今年开始的时间戳
        $begindate = mktime(0, 0, 0, 1, 1, date('Y'));
        //已完成招商分月份
        if ($type == 1) {
            foreach ($mCompany as $value) {
                //今年
                if ($value['update_time'] > $begindate) {
                    //月
                    $month = date('Y', $value['update_time']);

                    array_push($data[$month], $value);
                }

            }
            //未完成招商分月份
        } else {
            foreach ($mCompany as $value) {
                //今年
                if (strtotime($value['create_time']) > $begindate) {
                    $date_str = date('Y-m-d', strtotime($value['create_time']));
                    //封装成数组
                    $arr = explode("-", $date_str);
                    //参数赋值
                    //月，输出2位整型，不够2位右对齐
                    $month = sprintf('%02d', $arr[1]);
                    array_push($data[$month], $value);
                }
            }
        }
      foreach ($data as $key=>$value) {
          $data[$key] = count($data[$key]);
      }


      return $data;
    }
    //企业详情
    public function companyInfo()
    {
        $id = input('id');
        $mCompany = new MerchantsCompany();
        $mRecord = new MerchantsRecord();
        $Company = $mCompany->where('id', $id)->find();
        $Record = $mRecord->where('merchants_id', $id)->order('merchants_date desc')->select();
        $this->assign('company', $Company);
        $this->assign('records', json_encode($Record));
        return $this->fetch();
    }

    //查看招商日志详情
    public function recordDetail()
    {
        $user_id = session('userId');
        $mRecord = new MerchantsRecord();
        $mCompany = new MerchantsCompany();
        $Record_id = input('id');
        $info = $mRecord->where('id', $Record_id)->find();
        $info['company_name'] = isset($info->merchantsCompany->company) ? $info->merchantsCompany->company : "";
        $info['merchants_user'] = isset($info->merchantsCompany->user->name) ? $info->merchantsCompany->user->name : "";
        $this->assign('info', json_encode($info));
        return $this->fetch();
    }

    //写招商日志
    public function merchantsRecord()
    {
        $user_id = session('user_id');
        $merchaants_id = input('merchaants_id');
        $mCompany = new MerchantsCompany();
        $mRecord = new MerchantsRecord();
        if (IS_POST) {
            $data = input('');
            $list = $mRecord->save($data);
            if ($data['status'] == 2) {
                $map = [
                    'update_time' => $data['merchants_date'],
                    'merchants_area' => $data['merchants_area'],
                    'merchants_money' => $data['merchants_money'],
                    'status' => 2
                ];
                $result = $mCompany->where('id', $merchaants_id)->update($map);
            }
            if ($list) {
                return $this->success("完成");
            } else {
                return $this->error("失败");
            }
        } else {
            $info = $mCompany->where('id', $merchaants_id)->find();
            $this->assign('info', $info);
            return $this->fetch();

        }


    }


    //个人统计详情获取数据的公共方法
    public function statisticsCommon($user_id, $year, $month)
    {
        $mDiary = new MerchantsDiary();
        $mPlan = new MerchantsPlan();
        $mCompany = new MerchantsCompany();
        $mRecord = new MerchantsRecord();

        //年
        if (empty($month)) {
            $begindate = mktime(0, 0, 0, 1, 1, $year);
            $enddate = mktime(0, 0, 0, 1, 1, $year + 1) - 1;

        } //月
        else {
            $begindate = mktime(0, 0, 0, $month, 1, $year);
            $enddate = mktime(0, 0, 0, $month + 1, 1, $year) - 1;
        }
        //招商日志填报情况
        $days = ceil(abs($enddate - $begindate) / 86400);
        $merchants_ids = array();
        $ids = $mCompany->where('user_id', $user_id)->select();
        foreach ($ids as $value) {
            array_push($merchants_ids, $value['id']);
        }
        $map['merchants_id'] = array('in', $merchants_ids);
        $map['merchants_date'] = array('between', array($begindate, $enddate));
        //招商日志
        $myRecord = $mRecord->where($map)->select();
        $record_num = array();
        foreach ($myRecord as $value) {
            $time = date('Y-m-d', strtotime($value['merchants_date']));
            array_push($record_num, $time);
        }
        $num = count(array_values(array_unique($record_num)));
        unset($map['merchants_id']);
        unset($map['merchants_date']);
        //工作日志
        $map['create_time'] = array('between', array($begindate, $enddate));
        $map['user_id'] = $user_id;
        $myDiary = $mDiary->where($map)->select();
        //清空map
        $map = [];
        $map['time'] = array('between', array($begindate, $enddate));
        $list = $mPlan->where($map)->select();
        //招商计划回款
        $price = 0;
        //招商计划面积
        $area = 0;
        foreach ($list as $value) {
            $area += $value['plan_area'];
            $price += $value['plan_price'];
        }
        unset($map['time']);
        $map['update_time'] = array('between', array($begindate, $enddate));
        $list2 = $mCompany->where($map)->select();
        //招商实际回款
        $finish_price = 0;
        //招商实际面积
        $finish_area = 0;
        foreach ($list2 as $value) {
            $finish_area += $value['merchants_area'];
            $finish_price += $value['merchants_price'];
        }
        $data = [
            'total' => $days,
            'dairy_num' => $num,
            'price' => $price,
            'area' => $area,
            'finish_price' => $finish_price,
            'finish_area' => $finish_area,
            'records' => $myRecord,
            'diary' => $myDiary
        ];
        return $data;
    }

    //招商个人统计
    public function statisticsInfo()
    {
        $userid = session('userId');
        if (IS_POST) {
            $year = input('year');
            $month = input('month');
            $personalinfo = $this->statisticsCommon($userid, $year, $month);
            if ($personalinfo) {
                return $this->success("成功", '', $personalinfo);

            } else {
                return $this->error("失败");
            }
        } else {
            $weuser = new WechatUser();
            $user = $weuser->where('userid', $userid)->find();
            $is_boss = $user['tagid'] == 1 ? "yes" : "no";
            $this->assign('is_boss', $is_boss);
            if (empty($year)) {
                $date_str = date('Y-m-d', time());
                //封装成数组
                $arr = explode("-", $date_str);
                //参数赋值
                //年
                $year = $arr[0];
                //月，输出2位整型，不够2位右对齐
                $month = sprintf('%02d', $arr[1]);
            }
            $personalinfo = $this->statisticsCommon($userid, $year, $month);
            $this->assign('personalinfo', json_encode($personalinfo));
            return $this->fetch();
        }
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

    //写日志
    public function writeDiary()
    {
        $user_id = session('userId');
        $mDiary = new MerchantsDiary();
        if (IS_POST) {
            $data = input('');
            $data['user_id'] = $user_id;
            $reult = $mDiary->save($data);
            if ($reult) {
                return $this->success("yes");
            } else {
                return $this->error("no");
            }
        }
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

    /*企业楼房表*/
    public function companyFloor()
    {
        $parkId = session('park_id');
        $parkRoom = new ParkRoom();
        $map = [
            'park_id' => $parkId,
            'build_block' => "A",
        ];
        $list = $parkRoom->where($map)->distinct(true)->field('floor')->select();
        foreach ($list as $k => $v) {
            $floor[$k] = $v['floor'];
        }
        foreach ($floor as $k => $v) {
            $roomList = $parkRoom->where(['floor' => $v, 'build_block' => "A", 'del' => 0])->select();
            foreach ($roomList as $k1 => $v1) {
                if ($v1['company_id']) {
                    $status = false;
                } else {
                    $status = true;
                }
                $roomArray[$k][$k1] = ['room' => $v1['room'], 'empty' => $status, 'id' => $v1['company_id']];
            }

        }
        foreach ($floor as $k => $v) {
            $newArr[$k]['floor'] = $v;
            $newArr[$k]['rooms'] = $roomArray[$k];
        }
        $map1 = [
            'park_id' => $parkId,
            'build_block' => "B",
        ];
        $list1 = $parkRoom->where($map1)->distinct(true)->field('floor')->select();
        //return  dump($list1);
        foreach ($list1 as $k => $v) {
            $floor1[$k] = $v['floor'];
        }
        //return dump($floor1);
        foreach ($floor1 as $k => $v) {
            $roomList1 = $parkRoom->where(['floor' => $v, 'build_block' => "B", 'del' => 0])->select();
            foreach ($roomList1 as $k1 => $v1) {
                if ($v1['company_id']) {
                    $status1 = false;
                } else {
                    $status1 = true;
                }
                $roomArray1[$k][$k1] = ['room' => $v1['room'], 'empty' => $status1, 'id' => $v1['company_id']];
            }

        }
        foreach ($floor1 as $k => $v) {
            $newArr1[$k]['floor'] = $v;
            $newArr1[$k]['rooms'] = $roomArray1[$k];
        }
        $this->assign('list', json_encode($newArr));
        $this->assign('list1', json_encode($newArr1));
        echo json_encode($newArr);
        echo json_encode($newArr1);

        return $this->fetch();


    }

}
