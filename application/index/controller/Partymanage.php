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
use think\Image;
use app\common\model\PartyNews;
use app\common\model\ParkRent;
use app\index\model\WechatUser as WechatModel;

class Partymanage extends Base
{
    /** 园区管理首页 **/
    public function index()
    {
        $userid = session('userId');
        $park_id = session('park_id');
        $user = WechatUser::where('userid', 'eq', $userid)->field('department,tagid')->find();
        $map = [
            'status' => 1,
        ];
        if (IS_POST) {
            $park_id = input('park_id');
            if ($park_id == 1) {
                $news = PartyNews::where($map)->order('create_time desc')->field('id,title')->limit(4)->select();
            } else {
                $news = PartyNews::where($map)->where('park_id', $park_id)->order('create_time desc')->field('id,title')->limit(4)->select();
            }

            return $this->success('成功', '', json_encode($news));

        }
        //所有园区领导,能看到所有园区
        if ($user['department'] == 1 && $user['tagid'] == 1) {

            $res = Park::field('id,name')->select();
            $news = PartyNews::where($map)->order('create_time desc')->field('id,title')->limit(4)->select();
            $all = [
                'id' => "1",
                'name' => "全部"
            ];
            array_unshift($res, $all);
        } else {
            //只能看到自己园区
            $res = Park::where('id', 'eq', $park_id)->field('id,name')->select();
            $news = PartyNews::where($map)->where('park_id', session("park_id"))->order('create_time desc')->field('id,title')->limit(4)->select();
        }

        $this->assign('news', json_encode($news));
        $this->assign('res', json_encode($res));

        return $this->fetch();
    }


    /** 园区内部通告列表 **/
    public function notify()
    {
        //首页所选园区ID
        $parkid = input('park_id');
        if ($parkid == '1') {
            $map = [
                'status' => 1,
            ];
        } else {
            $map = [
                'park_id' => $parkid,
                'status' => 1,
            ];
        }
        $list = PartyNews::where($map)->order('create_time desc')->field('id,title,views,create_time,park_id')->limit(6)->select();
        $this->assign('list', json_encode($list));
        $this->assign('park_id', json_encode($parkid));
        return $this->fetch();
    }

    /*园区内部通告下拉刷新*/
    public function getMoreList()
    {
        $len = input("length");
        $parkid = input('park_id');
        if ($parkid == '1') {
            $map = [
                'status' => 1,
            ];
        } else {
            $map = [
                'park_id' => $parkid,
                'status' => 1,
            ];
        }

        $list = PartyNews::where($map)
            ->order("create_time desc")
            ->limit($len, 6)
            ->select();
        if ($list) {

            return json(['code' => 1, 'data' => $list]);
        } else {

            return json(['code' => 0, 'msg' => "没有更多内容了"]);
        }
    }

    /** 内部通告详情页 **/
    public function detail()
    {
        // 添加阅读量
        PartyNews::where('id', input('id'))->setInc('views');
        $id = input('id');
        $map = [
            'id' => $id,
            'status' => 1,
        ];
        $res = PartyNews::where($map)->find();

        $this->assign('res', json_encode($res));
        return $this->fetch();
    }

    /** 园区统计 **/
    public function statistics()
    {
        //首页所选园区ID
        $id = input('park_id');
        if ($id == '1') {
            //园区统计
            $res = Park::field('id,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->find();
            $list = Park::field('id,name,address,images')->select();

        } else {
            $res = Park::where('id', 'eq', $id)->field('id,area_total,area_use,area_other,scale_one,scale_two,scale_three,type_one,type_two,type_three')->find();
            $list = Park::where('id', 'eq', $id)->field('id,name,address,images')->select();
        }

        $this->assign('list', json_encode($list));
        $this->assign('res', json_encode($res));
//        echo json_encode($list);
        return $this->fetch();
    }

    /***合同管理*/
    public function contract()
    {
        $type = input('type');
        $data[0] = CompanyContract::where(["park_id" => session("park_id"), 'type' => 1, 'status' => 0])->count();
        $data[1] = CompanyContract::where(["park_id" => session("park_id"), 'type' => 2, 'status' => 0])->count();
        $data[2] = CompanyContract::where(["park_id" => session("park_id"), 'type' => ['>', 2], 'status' => 0])->count();
        $contract[0] = $data[0] + $data[1] + $data[2];
        $contract[1] = $data[0];
        $contract[2] = $data[1];
        $contract[3] = $data[2];
        $array = [
            'total' => ['name' => "总合同数", 'count' => $contract[0]],
            'rent' => ['name' => "租赁合同", 'count' => $contract[1]],
            'property' => ['name' => "物业合同", 'count' => $contract[2]],
            'other' => ['name' => "其他合同", 'count' => $contract[3]],
        ];
//        return json_encode($array);
        $this->assign('type', $type);
        $this->assign('info', json_encode($array));

        return $this->fetch();
    }

    /*合同列表*/
    public function managelist()
    {
        $id = input('id');
        $type = input("type");
        $list = CompanyContract::where(["park_id" => session("park_id"), 'type' => $type, 'status' => 0])
            ->order("create_time desc")
            ->limit(7)
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
        $list = CompanyContract::where(["park_id" => session("park_id"), 'type' => $type, 'status' => 0])
            ->order("create_time desc")
            ->limit($len, 7)
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
            'imgs' => json_decode($manageInfo['imgs']),
            'number' => $manageInfo['number'],
            'create_time' => $manageInfo['create_time'],
        ];
        $a = array();
        foreach ($info['img'] as $v) {
            $v = "http://" . $_SERVER['HTTP_HOST'] . $v;
            array_push($a, $v);
        }
        $info['img'] = $a;

        $b = array();
        foreach ($info['imgs'] as $v2) {
            $v2 = "http://" . $_SERVER['HTTP_HOST'] . $v2;
            array_push($b, $v2);
        }
        $info['imgs'] = $b;
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
        if ($user['tagid'] == 1) {
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
                //echo json_encode($all_company);
                foreach ($all_company as $value) {
                    $user_park = isset($value->user->park_id) ? $value->user->park_id : 1;
                    if ($value['status'] == 1 && $user_park == $park_id) {
                        array_push($doing, $value);

                    } else if ($value['status'] == 2 && $user_park == $park_id) {
                        array_push($finish, $value);
                    }
                }
            }
            //招商统计图表所需数据格式
            $finish2 = $this->merchantsComment($finish, 1);
            $doing2 = $this->merchantsComment($doing, 2);
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
            // echo "领导权限";
        } //个人权限
        else {
            //echo "个人权限";
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
            //招商统计图表所需数据格式
            $finish2 = $this->merchantsComment($finish, 1);
            $doing2 = $this->merchantsComment($doing, 2);
            foreach ($all_company as $value) {
                if ($value['status'] == 1) {
                    array_push($doing, $value);

                } else if ($value['status'] == 2) {
                    array_push($finish, $value);
                }
            }
            //TODO 招商人员的 个人统计
            //参数赋值
            //年
            $year = date('Y');
            //月，输出2位整型，不够2位右对齐
            $month = date('m');

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

    /*工作日志*/
    public function workDiary()
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
        if ($user['tagid'] == 1) {
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
            // echo "领导权限";
        } //个人权限
        else {
            //echo "个人权限";
            //工作日志
            $diaryList = $mDiary->where('user_id', $userid)->order('create_time desc')->select();
            foreach ($diaryList as $value) {
                $value['user_name'] = isset($value->user->name) ? $value->user->name : "";
                unset($value['user']);
            }
        }
        //工作日志
        $this->assign('diaryList', json_encode($diaryList));

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
        foreach ($data as $key => $value) {
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
        $mRecord = new MerchantsRecord();
        $Record_id = input('id');
        $info = $mRecord->where('id', $Record_id)->find();
        $info['company_name'] = isset($info->merchantsCompany->company) ? $info->merchantsCompany->company : "";
        $info['merchants_user'] = isset($info->merchantsCompany->user->name) ? $info->merchantsCompany->user->name : "";
        $info['merchants_area'] = isset($info->merchantsCompany->merchants_area) ? $info->merchantsCompany->merchants_area : "";
        $info['merchants_money'] = isset($info->merchantsCompany->merchants_money) ? $info->merchantsCompany->merchants_money : "";
        unset($info['merchantsCompany']);
        //echo json_encode($info);
        $this->assign('info', json_encode($info));
        return $this->fetch();
    }

//写招商日志
    public function merchantsRecord()
    {
        $user_id = session('user_id');
        $mCompany = new MerchantsCompany();
        $mRecord = new MerchantsRecord();
        if (IS_POST) {
            $data = input('');
            $merchants_id = $data['merchants_id'];
            unset($data['merchaants_id']);
            $data['merchants_date'] = $data['merchants_date'] / 1000;
            $merchants_area = $data['merchants_area'];
            $merchants_money = $data['merchants_money'];
            $data['img'] = empty($data['img']) ? "" : json_encode($data['img']);
            unset($data['merchants_area']);
            unset($data['merchants_money']);
            $list = $mRecord->save($data);
            if ($data['status'] == 2) {
                $map = [
                    'update_time' => $data['merchants_date'],
                    'merchants_area' => $merchants_area,
                    'merchants_money' => $merchants_money,
                    'status' => 2
                ];
                $result = $mCompany->save($map, ['id' => $merchants_id]);
            }
            if ($list) {
                return $this->success("完成", '', $mCompany->getLastSql());
            } else {
                return $this->error("失败");
            }
        } else {
            $merchaants_id = input('id');
            $info = $mCompany->where('id', $merchaants_id)->find();
            $info['merchants_user'] = isset($info->user->name) ? $info->user->name : "";
            $this->assign('info', json_encode($info));
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
            $time = date('Y-m-d', $value['merchants_date']);
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
        $map['user_id'] = $user_id;
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
        $map['user_id'] = $user_id;
        $list2 = $mCompany->where($map)->select();
        //招商实际回款
        $finish_price = 0;
        //招商实际面积
        $finish_area = 0;
        foreach ($list2 as $value) {
            $finish_area += $value['merchants_area'];
            $finish_price += $value['merchants_money'];
        }
        $data = [
            'total' => $days,
            'dairy_num' => $num,
            'price' => $price,
            'area' => $area,
            'finish_price' => $finish_price,
            'finish_area' => $finish_area,
            'records' => $myRecord,
            'diary' => $myDiary,
            'userid' => $user_id
        ];
        return $data;
    }

    //招商个人统计
    public function statisticsInfo()
    {
        $id = input('userid');
        $userid = isset($id) ? $id : session('userId');
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
            $user = $weuser->where('userid', session('userId'))->find();
            $is_boss = $user['tagid'] == 1 ? "yes" : "no";
            $this->assign('is_boss', $is_boss);
            if (empty($year)) {
                $date_str = date('Y-m-d', time());
                //封装成数组
                $arr = explode("-", $date_str);
                //参数赋值
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
        $user_id = empty(input('user_id')) ? session('userId') : input('user_id');
        $park_id = session('park_id');
        $id = input('id');
        $mDiary = new MerchantsDiary();
        $weuser = new WechatUser();
        if (IS_POST) {
            $data = input('');
            $data['user_id'] = $user_id;
            $data['work_today'] = empty($data['work_today']) ? '[]' : json_encode($data['work_today']);
            $data['arrange_tomorrow'] = empty($data['arrange_tomorrow']) ? '[]' : json_encode($data['arrange_tomorrow']);
            $data['create_time'] = empty(input('create_time')) ? mktime(0, 0, 0, date('m'), date('d'), date('Y')) : input('create_time') / 1000;
            //以前的日志不能修改
            //如果修改（create_time！=null）：1.今日修改（今日填写） 2：以前补写
            if ($data['create_time'] == mktime(0, 0, 0, date('m'), date('d'))) {
                if (!isset($data['id'])) {
                    //今日新增
                    $data['is_supplement'] = 2;
                    $data['park_id'] = $park_id;
                    $reult = $mDiary->allowField(true)->save($data);

                } else {
                    //今日修改
                    $reult = $mDiary->allowField(true)->isUpdate(true)->save($data);
                }
            } else {
                //以前补写
                $data['is_supplement'] = 1;
                $data['park_id'] = $park_id;
                $reult = $mDiary->allowField(true)->save($data);
            }
            if ($reult) {
                return $this->success("yes", '', time() * 1000);
            } else {
                return $this->error("no");
            }
        } else {
            $user = $weuser->where('userid', session('userId'))->find();
            $is_boss = $user['tagid'] == 1 ? "yes" : "no";
            $this->assign('is_boss', $is_boss);
            $diary['user_id'] = $user_id;
            $diary['user_name'] = $user['name'];

            //领导或者个人查看日志
            if (!empty($id)) {
                $info = $mDiary->where('id', $id)->find();
                $diary = [
                    'id' => $info['id'],
                    'user_name' => isset($info->user->name) ? $info->user->name : "",
                    /* 'img' => json_decode($info['img']),*/
                    'user_id' => $info['user_id'],
                    'work_today' => json_decode($info['work_today']),
                    'arrange_tomorrow' => json_decode($info['arrange_tomorrow']),
                    'feed_back' => $info['feed_back'],
                    'supplement' => $info['supplement'],
                    'is_supplement' => $info['is_supplement'],
                    'create_time' => $info['create_time'] * 1000,
                    'update_time' => $info['update_time'] * 1000,
                ];

            } else {

            }
            $list = $mDiary->where('user_id', $user_id)->select();
            $time = array();
            foreach ($list as $value) {
                $map = [
                    'is_supplement' => $value['is_supplement'],
                    'create_time' => $value['create_time'] * 1000,
                    'update_time' => $value['update_time'] * 1000
                ];
                array_push($time, $map);
            }
            //当前日志详情
            //echo json_encode($diary);
            $this->assign('info', json_encode($diary));
            //echo json_encode($diary);
            //该用户总共写的日志
            $this->assign('list', json_encode($time));
            return $this->fetch();
        }

    }

    public function changeDiary()
    {
        $user_id = input('user_id');
        $time = input('time') / 1000;
        $mDiary = new MerchantsDiary();
        $date_str = date('Y-m-d', $time);
        $wechat = new WechatModel();
        $user_name = $wechat->where('userid', $user_id)->find();

        //封装成数组
        $arr = explode("-", $date_str);
        //参数赋值
        $year = $arr[0];
        //月
        $month = $arr[1];
        //天
        $day = $arr[2];
        $begindate = mktime(0, 0, 0, $month, $day, $year);
        $enddate = mktime(23, 59, 59, $month, $day, $year);
        $map['user_id'] = $user_id;
        $map['create_time'] = array('between', array($begindate, $enddate));
        $info = $mDiary->where($map)->find();


        if (!$info) {
            $info['user_id'] = $user_id;
            $info['user_name'] = $user_name['name'];
            $info['create_time'] = $begindate * 1000;
        } else {
            $info['arrange_tomorrow'] = json_decode($info['arrange_tomorrow']);
            $info['work_today'] = json_decode($info['work_today']);
            $info['user_name'] = $user_name['name'];
            $info['create_time'] = $info['create_time'] * 1000;
        }

        return json_encode($info);

    }


    /*园区列表*/
    public function parkList()
    {
        $park = new Park();
        $id = input('id');
        $list = $park->select();
        foreach ($list as $k => $v) {
            $data[$k] = [
                'name' => $v['name'],
                'address' => $v['address'],
            ];
        }
        $this->assign('list', $data);
        $this->assign('id', $id);
        return $this->fetch();
    }

    /*公司详情列表*/
    public
    function detailList()
    {
        $id = input('id');

        $this->assign('id', $id);

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
        $manageInfo = CompanyContract::where($map)->find();
        $info = [
            'extra' => $manageInfo['remark'],
            'img' => json_decode($manageInfo['img']),
            'imgs' => json_decode($manageInfo['imgs']),
            'number' => $manageInfo['number'],
            'create_time' => $manageInfo['create_time'],
        ];

        /*if ($info['img']) {
            foreach ($info['img'] as $k1 => $v1) {
                if (is_file(PUBLIC_PATH . $v1)) {
                    $path = str_replace(".", "_s.", $v1);
                    $image = Image::open(PUBLIC_PATH . $v1);
                    $image->thumb(355, 188)->save(PUBLIC_PATH . $path);
                    $info['imgs'][$k1] = $path;
                }
            }
        }*/
        //return  dump($info);
        $this->assign('info', json_encode($info));


        return $this->fetch();
    }

    /*公司缴费记录*/
    public function feeRecord()
    {
        $info = [];
        $type = [1 => "水电费", 2 => "物业费", 3 => "房租费", 4 => "公耗费"];
        $departmentId = input('id');
        $map = ['company_id' => $departmentId];
        $list = FeePayment::where($map)->order('id desc')->limit(6)->select();
        foreach ($list as $k => $v) {
            $info[$k] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'status' => $v['status'],
                'time' => $v['create_time'],
                'pay' => $v['fee'],
                'title' => $type[$v['type']]
            ];
        }
        $this->assign("info", json_encode($info));
        return $this->fetch();
    }

    /*加载更多缴费记录*/
    public function moreRecode()
    {
        $info = [];
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
        /*$floor = [];
        $floor1 = [];
        $newArr = [];
        $newArr1 = [];
        $parkId = session('park_id');
        $parkInfo = Park::where('id', $parkId)->find();
        $parkName = $parkInfo['name'];
        $parkRoom = new ParkRoom();
        $map = [
            'park_id' => $parkId,
            'build_block' => "A",
        ];
        $list = $parkRoom->where($map)->distinct(true)->field('floor')->order('floor desc')->select();
        foreach ($list as $k => $v) {
            $floor[$k] = $v['floor'];
        }
        foreach ($floor as $k => $v) {
            $roomList = $parkRoom->where(['floor' => $v, 'build_block' => "A", 'del' => 0])->order("room asc")->select();
            foreach ($roomList as $k1 => $v1) {
                $res = ParkRent::where(['room_id' => $v1['id'], 'manage' => 0, 'status' => 0])->find();
                if (!$res) {
                    $status = false;
                    $roomsId = 0;
                } else {
                    $status = true;
                    $roomsId = $res['room_id'];
                }
                $roomArray[$k][$k1] = ['room' => $v1['room'], 'empty' => $status, 'id' => $v1['company_id'], 'room_id' => $roomsId];
            }
        }
        foreach ($floor as $k => $v) {
            $newArr[$k]['floor'] = $v;
            $newArr[$k]['combine'] = false;
            $newArr[$k]['rooms'] = $roomArray[$k];
        }
        $map1 = [
            'park_id' => $parkId,
            'build_block' => "B",
        ];
        $list1 = $parkRoom->where($map1)->distinct(true)->field('floor')->order('floor desc')->select();
        foreach ($list1 as $k => $v) {
            $floor1[$k] = $v['floor'];
        }
        foreach ($floor1 as $k => $v) {
            $roomList1 = $parkRoom->where(['floor' => $v, 'build_block' => "B", 'del' => 0])->order("room asc")->select();
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
            $newArr1[$k]['combine'] = false;
            $newArr1[$k]['rooms'] = $roomArray1[$k];
        }

        $resArr = array_merge(["A幢" => $newArr], ["B幢" => $newArr1]);
        $resArr1 = ["$parkName" => $resArr];*/
        $parkRoom = new ParkRoom();
        $resArr1 = $parkRoom->companyRoom();

        $this->assign('list', json_encode($resArr1));


        return $this->fetch();


    }

    /*公司其他合同列表*/
    public function otherList()
    {
        $data = [];
        $id = input('id');
        $map = ['department_id' => $id, 'type' => 3];
        $list = CompanyContract::where($map)->order('id  desc')->select();
        foreach ($list as $k => $v) {
            $data[$k] = [
                'id' => $v['id'],
                'name' => $v['other_name'],
            ];
        }
        $this->assign("id", $id);
        $this->assign('list', json_encode($data));

        return $this->fetch();
    }

    //微信js-sdk调试
    public function wxjssdk()
    {
        $url = input('url');
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $weObj = new TPWechat(config('parkmanage'));


        $signature = $weObj->getJsSign($url);

        if (!$signature) {

            return "获取signature失败" . $weObj->errCode . '|' . $weObj->errMsg;;
        }
        $signature['imgurl'] = 'http://xk.0519ztnet.com/';

        return json_encode($signature);
    }


}
