<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * QQ: 2205446834@qq.com
 * Date: 2017/6/2
 * Time: 13:35
 */

namespace app\index\controller;


use app\index\model\CarparkService;
use app\index\model\Collect as CollectModel;
use app\index\model\WechatUser;
use app\index\model\WechatDepartment;
use app\index\model\PersonalMessage;
use app\index\model\CompanyService;
use app\index\model\CompanyApplication;
use think\Db;
use app\index\model\FeePayment;
use app\index\model\PropertyServer;
use app\index\model\WaterService;
use app\index\model\CarparkRecord;
use app\index\model\ElectricityService;
use app\index\model\AdvertisingService;
use app\index\model\AdvertisingRecord;
use app\index\model\FunctionRoomRecord;
use app\index\model\LedRecord;
use app\index\model\BroadbandPhone;
use app\common\model\OperationalAuthority;


class Personal extends Base
{
    public function index()
    {
        $user_id = session("userId");
        $user = new  WechatUser();
        $partyinfo = $user->where(['userid' => $user_id])->find();
        $userinfo = $user->checkUserExist($user_id);
        $this->assign("party_branch", $partyinfo['party_branch']);
        $this->assign("userinfo", $userinfo);

        return $this->fetch();
    }

    /*个人信息*/
    public function personalinfo()
    {


        $user_id = session('userId');
        $wu = new  WechatUser();
        $info = $wu->where('userid', $user_id)->find();
        $data = [
            'name' => $info['name'],
            'avatar' => $info['avatar'],
            'sex' => $info['gender'] == 1 ? "男" : "女",
            'mobile' => $info['mobile'],
            'department' => isset($info->departmentName->name) ? $info->departmentName->name : "",
            'header' => $info['header']
        ];

        $this->assign('info', $data);
        return $this->fetch();
    }

    /*个人收藏*/
    public function collection()
    {
        $user_id = session("userId");
        $list1 = CollectModel::where(['user_id' => $user_id, 'type' => 1])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list1 as $k => $v) {
            $list1[$k]['title'] = $v->News->title;
            $list1[$k]['views'] = $v->News->views;
            $list1[$k]['image'] = $v->News->front_cover;
        }
        $list2 = CollectModel::where(['user_id' => $user_id, 'type' => 2])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list2 as $k => $v) {
            $list2[$k]['title'] = $v->News->title;
            $list2[$k]['views'] = $v->News->views;
            $list2[$k]['source'] = $v->News->source;
        }

        $list3 = CollectModel::where(['user_id' => $user_id, 'type' => 3])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list3 as $k => $v) {
            $list3[$k]['title'] = $v->News->title;
            $list3[$k]['views'] = $v->News->views;
            $list3[$k]['source'] = $v->News->source;
        }
        $list4 = CollectModel::where(['user_id' => $user_id, 'type' => ['in', [4, 5]]])
            ->order('create_time desc')
            ->limit(6)->select();
        foreach ($list4 as $k => $v) {
            $list4[$k]['title'] = $v->News->title;
            $list4[$k]['views'] = $v->News->views;
            $list4[$k]['source'] = $v->News->source;
        }

        $this->assign('list1', $list1);
        $this->assign('list2', $list2);
        $this->assign('list3', $list3);
        $this->assign('list4', $list4);

        return $this->fetch();

    }

    /**党员信息*/
    public function party()
    {
        $user_id = session("userId");
        $party_user = new  WechatUser();
        if (IS_POST) {
            $data = input('');
            $data['education'] = $data['education'] - 1;
            $result = $party_user->save($data, ['userid' => $user_id]);
            if ($result !== false) {

                return $this->success('成功');
            } else {

                return $this->error('失败');
            }
        } else {
            $userInfo = $party_user->where(['userid' => $user_id])->find();
            if ($userInfo) {
                unset($userInfo['mobile']);
                unset($userInfo['userid']);
                $userInfo['education'] = $userInfo['education'] + 1;
                $result['data'] = $userInfo;
            }
            $this->assign("data", json_encode($result));
            return $this->fetch();
        }

    }

    //党员编辑
    public function editParty()
    {
        $user_id = session("userId");
        $party_user = new  WechatUser();
        $userInfo = $party_user->where(['userid' => $user_id])->find();
        $userInfo['education'] = $userInfo['education'] + 1;
        unset($userInfo['mobile']);
        unset($userInfo['userid']);
        $this->assign("data", $userInfo);

        return $this->fetch();
    }

    /**
     * 设置头像
     */
    public function setHeader()
    {
        $userId = session('userId');
        $header = input('header');
        $map = array(
            'header' => $header,
        );
        $wu = new  WechatUser();
        $info = $wu->where('userid', $userId)->update($map);
        if ($info) {
            return $this->success("修改成功");
        } else {
            return $this->error("修改失败");
        }
    }

    /*我的消息*/
    public function message()
    {
        $personMessage = new PersonalMessage();
        $user_id = session('userId');
        $park_id = session('park_id');
        $user = WechatUser::where('userid', $user_id)->find();
        //运营人员
        if ($user['department'] == 76) {
            $map = [
                'park_id' => $park_id,
                'type' => array('neq', 3)
            ];
            $list = $personMessage->where($map)->limit(0, 6)->select();

        } //物业人员
        elseif ($user['tagid'] == 2) {
            $map = [
                'park_id' => $park_id,
                'type' => array('eq', 2)
            ];
            $list = $personMessage->where($map)->limit(0, 6)->select();
        } //用户自己
        else {

            $map = [
                'park_id' => $park_id,
                'type' => array('eq', 3),
                'userid' => $user_id
            ];
            $list = $personMessage->where($map)->limit(0, 6)->select();
        }
        $this->assign('list', json_encode($list));
        return $this->fetch();
    }

    /*我的消息上拉加载*/
    public function messageMore()
    {
        $personMessage = new PersonalMessage();
        $user_id = session('userId');
        $park_id = session('park_id');
        $user = WechatUser::where('userid', $user_id)->find();
        $num = input('num');
        //运营人员
        if ($user['department'] == 76) {
            $map = [
                'park_id' => $park_id,
                'type' => array('neq', 3)
            ];
            $list = $personMessage->where($map)->limit($num, 6)->select();

        } //物业人员
        elseif ($user['tagid'] == 2) {
            $map = [
                'park_id' => $park_id,
                'type' => array('eq', 2)
            ];
            $list = $personMessage->where($map)->limit($num, 6)->select();
        } //用户自己
        else {

            $map = [
                'park_id' => $park_id,
                'type' => array('eq', 3),
                'userid' => $user_id
            ];
            $list = $personMessage->where($map)->limit($num, 6)->select();
        }
        if ($list) {

            return $this->success('成功', '', json_encode($list));
        } else {

            return $this->error('失败');
        }


    }


    /*我的服务*/
    public function service()
    {
        $userid = session('userId');
        $park_id = session('park_id');
        $userinfo = WechatUser::where(['userid' => $userid])->find();
        $service = new \app\common\behavior\Service();
        //$type :1 运营 2 物业 3 普通企业
        $type = 3;
        if ($park_id == 3) {
            $userPower = 76;
        } else {
            $userPower = 76;
        }
        if ($userinfo['department'] == $userPower) {
            $type = 1;
        } elseif ($userinfo['tagid'] == 2) {
            $type = 2;
        }
        $appids = empty($userinfo->Operational->appids) ? array() : json_decode($userinfo->Operational->appids);

        //费用缴纳

        $departmentId = $userinfo['department'];
        $map = ['company_id' => $departmentId, 'status' => ['neq', -1]];
        $list1 = array();
        if ($type == 3) {
            $list = FeePayment::where($map)->order('create_time desc')->field('id,type as service_name,status,create_time,company_id')->select();
            $appid = 1;
            $can_check = 'no';
        } else {
            $list = FeePayment::where(['status' => ['in', [1,2,3]]])->order('create_time desc')->field('id,type as service_name,status,create_time,name,company_id')->select();
            $appid = 1;
            $can_check = 'yes';
        }
        foreach ($list as $value) {
            if ($service->findParkid($value['company_id']) == $park_id) {
                array_push($list1, $value);
            }
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        $types = [1 => '费用缴纳（水电费)', 2 => "费用缴纳（物业费)", 3 => "费用缴纳（房租费)", 4 => "费用缴纳（公耗费)"];
        foreach ($list1 as $k => $v) {
            $v['service_name'] = $types[$v['service_name']];
            $list1[$k]['url'] = $url . $v['id'];
            if ($v['status'] == 0) {
                $list1[$k]['status_text'] = '未缴费';
            } elseif ($v['status'] == 1) {
                $list1[$k]['status_text'] = '审核中';
            } elseif ($v['status'] == 2) {
                $list1[$k]['status_text'] = '审核成功';
            } else {
                $list1[$k]['status_text'] = '审核失败';
            }
            $list1[$k]['app_id'] = $appid;
        };

        //物业报修
        $types = [1 => '物业报修（空调报修）', 2 => "物业报修（电梯报修）", 3 => "物业报修（其他报修）"];

        if ($type == 3) {
            $list2 = PropertyServer::where(['type' => ['<', 4], 'user_id' => $userid, 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('proof,id,type as service_name,status,create_time')->select();
            $appid = 2;
            $can_check = 'no';
        } else {
            $list2 = PropertyServer::where(['type' => ['<', 4], 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('proof,id,type as service_name,status,create_time')->select();
            $appid = 2;
            $can_check = 'yes';
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        foreach ($list2 as $k => $v) {
            $v['service_name'] = $types[$v['service_name']];
            $v['create_time'] = date("Y-m-d", $v['create_time']);

            $list2[$k]['url'] = $url . $v['id'];
            if ($v['status'] == 0) {
                $list2[$k]['status_text'] = '审核中';
            } elseif ($v['status'] == 1) {
                $list2[$k]['status_text'] = '审核成功';
            } elseif ($v['status'] == 2) {
                $list2[$k]['status_text'] = '审核失败';
            }
            if (!empty($v['proof'])) {
                $list2[$k]['status_text'] = '已上传凭证';
            }
            $list2[$k]['app_id'] = $appid;
        }

        //饮水服务
        $map = [
            'status' => array('neq', -1),
            'userid' => $userid,
        ];
        $list3 = array();
        if ($type == 3) {
            $list = WaterService::where($map)->order('create_time desc')->field('id,status,create_time,userid')->select();
            $appid = 3;
            $can_check = 'no';
        } else {
            $list = WaterService::where(['status' => array('neq', -1)])->order('create_time desc')->field('id,name,status,create_time,userid')->select();
            $appid = 3;
            $can_check = 'yes';
        }

        foreach ($list as $value) {
            if (isset($value->user->park_id)) {
                if ($value->user->park_id == $park_id) {
                    array_push($list3, $value);
                }
            }

        }

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';


        foreach ($list3 as $k => $v) {
            $v['service_name'] = "饮水服务";
            $list3 [$k]['url'] = $url . $v['id'];

            if ($v['status'] == 0) {
                $list3[$k]['status_text'] = '审核中';
            } elseif ($v['status'] == 1) {
                $list3[$k]['status_text'] = '审核成功';
            } elseif ($v['status'] == 2) {
                $list3[$k]['status_text'] = '审核失败';
            } else {
                $list3[$k]['status_text'] = '确认送水';
            }
            $list3[$k]['app_id'] = $appid;
        }
        //室内保洁
        if ($type == 3) {
            $list4 = PropertyServer::where(['type' => ['=', 4], 'user_id' => $userid, 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('id,type as service_name,status,create_time')->select();
            $appid = 4;
            $can_check = 'no';
        } else {
            $list4 = PropertyServer::where(['type' => ['=', 4], 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('id,type as service_name,status,create_time')->select();
            $appid = 4;
            $can_check = 'yes';
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        foreach ($list4 as $k => $v) {
            $v['service_name'] = "保洁服务";
            $list4[$k]['url'] = $url . $v['id'];
            $v['create_time'] = date("Y-m-d", $v['create_time']);
            if ($v['status'] == 0) {
                $list4[$k]['status_text'] = '审核中';
            } elseif ($v['status'] == 1) {
                $list4[$k]['status_text'] = '审核成功';
            } elseif ($v['status'] == 2) {
                $list4[$k]['status_text'] = '审核失败';
            }
            $list4[$k]['app_id'] = $appid;
        }

        //车卡服务
        $list5 = array();
        if ($type == 3) {
            $list = CarparkService::where(['user_id' => $userid, 'status' => array('neq', -1)])->select();
            $appid = 6;
            $can_check = 'no';
        } else {
            $list = CarparkService::where(['status' => array('neq', -1)])->select();
            $appid = 6;
            $can_check = 'yes';
        }
        foreach ($list as $value) {
            if (isset($value->user->park_id)) {
                if ($value->user->park_id == $park_id) {
                    array_push($list5, $value);
                }
            }

        }


        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        foreach ($list5 as $k => $v) {
            $list5[$k]['service_name'] = $v['type'] == 1 ? "车卡服务（新卡办理）" : "车卡服务（旧卡续费）";
            $list5[$k]['create_time'] = date("Y-m-d", $v['create_time']);
            $list5[$k]['url'] = $url . $v['id'];
            if ($list5[$k]['status'] == 0) {
                $list5[$k]['status_text'] = '审核中';
            } elseif ($list5[$k]['status'] == 1) {
                $list5[$k]['status_text'] = '审核成功';
            } else {
                $list5[$k]['status_text'] = '审核失败';
            }
            $list5[$k]['app_id'] = $appid;
        }

        //充电柱办公
        $list6 = array();
        $electricityService = new ElectricityService;
        $user_id = session('userId');
        $map = [
            'user_id' => $user_id,
            'status' => array('neq', -1)
        ];
        if ($type == 3) {
            $list = $electricityService->where($map)->order('create_time desc')->select();
            $appid = 7;
            $can_check = 'no';
        } else {
            $list = $electricityService->where(['status' => array('neq', -1)])->order('create_time desc')->select();
            $appid = 7;
            $can_check = 'yes';
        }
        foreach ($list as $value) {
            if (isset($value->user->park_id)) {
                if ($value->user->park_id == $park_id) {
                    array_push($list6, $value);
                }
            }

        }


        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        int_to_string($list6, array('type' => array(1 => '充电柱办公(新柱办理)', 2 => '充电柱办公(旧柱续费)'), 'status' => array(0 => '审核中', 1 => '审核成功', 2 => '审核失败')));
        foreach ($list6 as $k => $value) {
            $value['service_name'] = $value['type_text'];
            $list6[$k]['url'] = $url . $value['id'];
            if ($list6[$k]['status'] == 0) {
                $list6[$k]['status_text'] = '审核中';
            } elseif ($list5[$k]['status'] == 1) {
                $list6[$k]['status_text'] = '审核成功';
            } else {
                $list6[$k]['status_text'] = '审核失败';
            }
            $list6[$k]['app_id'] = $appid;
        }
        //公共服务
        //大厅广告记录
        $ad = new AdvertisingRecord();
        $fs = new FunctionRoomRecord();
        $led = new LedRecord();

        $data1 = array();
        $time = array();
        $create_time = array();
        if ($type == 3) {
            $list = $ad->where(['create_user' => $user_id, 'status' => array('in', [0,2])])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'no';
            $type2 = 1;
        } else {
            $list = $ad->where(['status' => array('in', [0,2])])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'yes';
            $type2 = 1;
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $type2 . '/create_time/';
        //所有的创建时间
        foreach ($list as $l) {
            if (isset($l->user->park_id)) {
                if ($l->user->park_id == $park_id) {
                    array_push($create_time, $l['create_time']);
                }
            }
        }
        //数组去重
        $time = array_values(array_unique($create_time));

        foreach ($time as $onetime) {
            $map = array();
            foreach ($list as $info) {
                if ($info['create_time'] == $onetime) {
                    array_push($map, $info);
                }
            }
            $re = [
                'create_time' => date('Y-m-d', strtotime($onetime)),
                'service_name' => "公共区服务（大厅广告位预约）",
                'url' => $url . strtotime($onetime),
                'app_id'=>8,
                'status'=>$map[0]['status']
            ];


            if ($map[0]['status'] == 0) {
                $re['status_text'] = '已取消';
            } else if ($map[0]['status'] == 1) {
                $re['status_text'] = '选定未付款';
            } else if( $map[0]['status'] == 2){
                $re['status_text'] = '审核成功';
            }

            array_push($data1, $re);

        }

        //二楼多功能厅
        $data2 = array();
        $time = array();
        $create_time = array();
        if ($type == 3) {
            $list = $fs->where(['create_user' => $user_id, 'status' => array('in', [0,2])])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'no';
            $type2 = 2;
        } else {
            $list = $fs->where(['status' => array('in', [0,2])])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'yes';
            $type2 = 2;
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $type2 . '/create_time/';
        //所有的创建时间
        foreach ($list as $l) {
            if (isset($l->user->park_id)) {
                if ($l->user->park_id == $park_id) {
                    array_push($create_time, $l['create_time']);
                }
            }
        }
        //数组去重
        $time = array_values(array_unique($create_time));

        foreach ($time as $onetime) {
            $map = array();
            foreach ($list as $info) {
                if ($info['create_time'] == $onetime) {
                    array_push($map, $info);
                }
            }
            $re = [
                'create_time' => date('Y-m-d', strtotime($onetime)),
                'service_name' => "公共区服务（多功能厅预约）",
                'url' => $url . strtotime($onetime),
                'app_id'=>8,
                'status'=>$map[0]['status']

            ];
            if ($map[0]['status'] == 0) {
                $re['status_text'] = '已取消';
            } else if ($map[0]['status'] == 1) {
                $re['status_text'] = '审核中';

            } else {
                $re['status_text'] = '审核成功';
            }

            array_push($data2, $re);
        }

        //大堂led屏
        $data3 = array();
        $time = array();
        $create_time = array();
        if ($type == 3) {
            $list = $led->where(['create_user' => $user_id, 'status' => array('in', [0,2])])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'no';
            $type2 = 3;

        } else {
            $list = $led->where(['status' => array('in', [0,2])])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'yes';
            $type2 = 3;
        }
        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $type2 . '/create_time/';
        //所有的创建时间
        foreach ($list as $l) {
            if (isset($l->user->park_id)) {
                if ($l->user->park_id == $park_id) {
                    array_push($create_time, $l['create_time']);
                }
            }
        }
        //数组去重
        $time = array_values(array_unique($create_time));

        foreach ($time as $onetime) {
            $map = array();
            foreach ($list as $info) {
                if ($info['create_time'] == $onetime) {
                    array_push($map, $info);
                }
            }
            $re = [
                'service_name' => "公共区服务（大堂led屏预约）",
                'create_time' => date('Y-m-d', strtotime($onetime)),
                'url' => $url . strtotime($onetime),
                'app_id'=>8,
                'status'=>$map[0]['status']
            ];
            if ($map[0]['status'] == 0) {
                $re['status_text'] = '已取消';
            } else if ($map[0]['status'] == 1) {
                $re['status_text'] = '审核中';
            } else {
                $re['status_text'] = '审核成功';
            }

            array_push($data3, $re);

        }

        /*//电话宽带
        $userid = session('userId');
        //      echo  $userid;
        $map = [
            'status' => array('neq', -1),
            'user_id' => $userid,
        ];
        $list7 = BroadbandPhone::where($map)->order('create_time desc')->select();

        foreach ($list7 as $k => $v) {
            $list7[$k] = [
                'create_time' => $v['create_time'],
                'status' => $v['status'],
                'service_name' => "电话宽带",
            ];
        }*/
        $allList = array();
        switch ($type) {
            case 1:
                if (in_array(1, $appids)) {
                    $allList = array_merge($list1, $allList);
                }
                if (in_array(2, $appids)) {
                    $allList = array_merge($list2, $allList);
                }
                if (in_array(3, $appids)) {
                    $allList = array_merge($list3, $allList);
                }
                if (in_array(4, $appids)) {
                    $allList = array_merge($list4, $allList);
                }
                if (in_array(6, $appids)) {
                    $allList = array_merge($list5, $allList);
                }
                if (in_array(7, $appids)) {
                    $allList = array_merge($list6, $allList);
                }
                if (in_array(8, $appids)) {
                    $allList = array_merge($data1, $data2, $data3, $allList);
                }
                if (in_array(10, $appids)) {
                    //企业服务
                    $company_list = Db::table('tb_company_service')
                        ->alias('s')
                        ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
                        ->join('__WECHAT_USER__ b', 's.user_id=b.userid')
                        ->field('a.name as service_name,s.status,s.create_time')
                        ->where('s.status', 'neq', -1)
                        ->where('b.park_id', 'eq', $park_id)
                        ->order('create_time desc')
                        ->select();

                    foreach ($company_list as $value) {
                        $value['create_time'] = date("Y-m-d", $value['create_time']);

                        if ($value['status'] == 0) {
                            $value['status_text'] = '进行中';
                        } else {
                            $value['status_text'] = '已完成';
                        }
                    }
                }
                $company_list = empty($company_list) ? array() : $company_list;
                $this->assign('company', json_encode($company_list));
                break;
            case 2:
                $allList = array_merge($list2, $list3, $list4);
                $this->assign('company', '[]');
                break;
            case 3:
                if ($userinfo['fee_status'] == 1) {
                    $allList = array_merge($list1, $allList);
                }

                $allList = array_merge($allList, $list2, $list3, $list4, $list5, $list6, $data1, $data2, $data3);
                //企业服务

                $list = Db::table('tb_company_service')
                    ->alias('s')
                    ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
                    ->field('a.name as service_name,s.status,s.create_time')
                    ->where('s.user_id', 'eq', $userid)
                    ->where('s.status', 'neq', -1)
                    ->order('create_time desc')
                    ->select();

                foreach ($list as $value) {
                    $value['create_time'] = date("Y-m-d", $value['create_time']);

                    if ($value['status'] == 0) {
                        $value['status_text'] = '进行中';
                    } else {
                        $value['status_text'] = '已完成';
                    }
                }
                $this->assign('company', json_encode($list));
                break;
        }

        //合并所有物业服务的数组
        //$list = array_merge($list1, $list2, $list3, $list4, $list5, $list6, $data1, $data2, $data3, $list7);

        //把数组按时间排序
        usort($allList, function ($a, $b) {
            $al = strtotime($a['create_time']);
            $bl = strtotime($b['create_time']);
            if ($al == $bl)
                return 0;
            return ($al > $bl) ? -1 : 1;
        });

        /* foreach ($allList as $k => $value) {
             if ($value['status'] == 0) {
                 $allList[$k]['status_text'] = '进行中';
             } elseif ($value['status'] == 1) {
                 $allList[$k]['status_text'] = '审核成功';
             } elseif ($value['status'] == 3) {
                 $allList[$k]['status_text'] = '确认送水';
             } else {
                 $allList[$k]['status_text'] = '审核失败';
             }
         }*/
        //echo json_encode($allList);
        //echo json_encode($type);
        $this->assign('property', json_encode($allList));

        return $this->fetch();
    }

    /*我的收藏下拉刷新*/
    public function listmore()
    {
        $len = input("length");
        $type = input("type");
        $user_id = session("userId");
        if ($type == 1) {
            $list1 = CollectModel::where(['user_id' => $user_id, 'type' => 1])
                ->order('create_time desc')
                ->limit($len, 6)->select();
            if ($list1) {
                foreach ($list1 as $k => $v) {
                    $list1[$k]['title'] = $v->News->title;
                    $list1[$k]['views'] = $v->News->views;
                    $list1[$k]['image'] = $v->News->front_cover;
                    unset($list1[$k]['News']);
                }

                return json(['code' => 1, 'data' => $list1]);
            } else {

                return json(['code' => 0, 'msg' => "没有更多内容了"]);
            }

        } else if ($type == 2) {
            $list2 = CollectModel::where(['user_id' => $user_id, 'type' => 2])
                ->order('create_time desc')
                ->limit($len, 6)->select();
            if ($list2) {
                foreach ($list2 as $k => $v) {
                    $list2[$k]['title'] = $v->News->title;
                    $list2[$k]['views'] = $v->News->views;
                    unset($list2[$k]['News']);
                }
                return json(['code' => 1, 'data' => $list2]);
            } else {

                return json(['code' => 0, 'msg' => "没有更多内容了"]);
            }
        } else if ($type == 3) {
            $list3 = CollectModel::where(['user_id' => $user_id, 'type' => 3])
                ->order('create_time desc')
                ->limit($len, 6)->select();
            if ($list3) {
                foreach ($list3 as $k => $v) {
                    $list3[$k]['title'] = $v->News->title;
                    $list3[$k]['views'] = $v->News->views;
                    unset($list3[$k]['News']);
                }

                return json(['code' => 1, 'data' => $list3]);
            } else {
                return json(['code' => 0, 'msg' => "没有更多内容了"]);
            }
        } else {
            $list4 = CollectModel::where(['user_id' => $user_id, 'type' => ['>', 3]])
                ->order('create_time desc')
                ->limit($len, 6)->select();
            if ($list4) {
                foreach ($list4 as $k => $v) {
                    $list4[$k]['title'] = $v->News->title;
                    $list4[$k]['views'] = $v->News->views;
                    unset($list4[$k]['News']);
                }

                return json(['code' => 1, 'data' => $list4]);
            } else {

                return json(['code' => 0, 'msg' => "没有更多内容了"]);
            }
        }
    }
}