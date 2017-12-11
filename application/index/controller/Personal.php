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
use app\index\model\ParkCompany;
use app\index\model\Patent;
use app\index\model\TrademarkAdvisory;
use app\index\model\TrademarkInquire;
use app\index\model\WechatUser;
use app\index\model\WechatDepartment;
use app\index\model\PersonalMessage;
use app\index\model\CompanyService;
use app\index\model\CompanyApplication;
use function PHPSTORM_META\type;
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
use think\Exception;
use think\Session;
use wechat\TPWechat;
use think\Loader;
use think\log;
use app\index\model\CopyrightSoftwrite;
use app\index\model\CopyrightSoft;
use app\index\model\CopyrightArt;
use app\common\behavior\Service;

class Personal extends Base
{
    public function index()
    {
        $user_id = session("userId");
        $user = new  WechatUser();
        $userinfo = $user->checkUserExist($user_id);
        $company_check = $userinfo['manage'] == 1 ? 'yes' : 'no';
        $news_check = $userinfo['tagid'] == 1 ? 'yes' : 'no';
        $userinfo['company_check'] = $company_check;
        $userinfo['news_check'] = $news_check;
        $this->assign("party_branch", $userinfo['party_branch']);
        $this->assign("userinfo", $userinfo);
        return $this->fetch();
    }

    /*个人信息*/
    public function personalinfo()
    {
        $user_id = session('userId');
        $wu = new  WechatUser();
        $info = $wu->where('userid', $user_id)->find();
        $can_change = "yes";
        switch ($info['department']) {
            //希垦运营
            case 76:
                $can_change = "no";
                break;
            //希垦物业
            case 86:
                $can_change = "no";
                break;
            //人工运营
            case 87:
                $can_change = "no";
                break;
            //人工物业
            case 90:
                $can_change = "no";
                break;
            //人工游客
            case 91:
                break;
            //希垦游客
            case 78:
                break;
        }
        $people_card = "";
        $car_card = "";
        //从车卡中取身份证号和车牌号
        $car = CarparkService::where(['user_id' => $user_id, 'park_id' => $info['park_id']])->order("create_time desc")->select();
        if (!empty($info['car_card'])) {
            $car_card = $info['car_card'];
        } else {
            if ($car) {
                $car_card = $car[0]["car_card"];
            }
        }
        if (!empty($info['people_card'])) {
            $people_card = $info['people_card'];
        } else {
            if ($car) {
                $people_card = $car[0]["people_card"];
            }
        }

        $office = !empty($info['company_address']) ? $info['company_address'] : "";
        //从送水地点中取 办公地址
        $water = WaterService::where(['userid' => $user_id, 'park_id' => $info['park_id']])->order("create_time desc")->select();
        if (empty($office)) {
            if ($water) {
                $water = explode("幢", $water[0]['address']);
                $office = $water[1];
            }
        }

        if ($can_change == "no") {
            //希垦所有的企业
            $departmentXK = WechatDepartment::where(['parentid' => ['in', [4, 73, 74]]])->select();
            //滨江人工智能产业园所有企业
            $departmentBj = WechatDepartment::where(['parentid' => ['in', [92, 80]], 'id' => ['neq', 1]])->select();

        } else {
            //希垦所有的企业
            $departmentXK = WechatDepartment::where('parentid', 4)->select();
            //滨江人工智能产业园所有企业
            $departmentBj = WechatDepartment::where('parentid', 92)->select();

        }


        $park_xk = ['park_id' => 3, 'park_name' => '希垦科技园', 'departmentlist' => array()];
        $park_bj = ['park_id' => 80, 'park_name' => '人工智能产业园', 'departmentlist' => array()];
        $xk = $this->departmentData($departmentXK, $park_xk);
        $bj = $this->departmentData($departmentBj, $park_bj);
        $departmentlist = array();
        if ($info['park_id'] == 3) {
            $info['park_name'] = "希垦科技园区";
            array_push($departmentlist, $xk);
            array_push($departmentlist, $bj);

        } else {
            $info['park_name'] = "人工智能产业园区";
            array_push($departmentlist, $bj);
            array_push($departmentlist, $xk);
        };
        $data = [
            'name' => $info['name'],
            'avatar' => $info['avatar'],
            'sex' => $info['gender'] == 1 ? "男" : "女",
            'mobile' => $info['mobile'],
            'department' => isset($info->departmentName->name) ? $info->departmentName->name : "",
            'department_id' => $info['department'],
            'header' => $info['header'],
            'park_name' => $info['park_name'],
            'park_id' => $info['park_id'],
            'user_id' => $user_id,
            'people_card' => $people_card,
            'car_card' => $car_card,
            'office' => $office,
            'departmentlist' => $departmentlist,
            'can_change' => $can_change
        ];
        //$list = WechatDepartment::where("parentid",1)->order('id asc')->select();
        /*foreach($list as $k=>$v){
            $parkArr[$k] = $v['id'];
        }*/
        //echo  json_encode($data['department']);
        $this->assign('info', json_encode($data));
        return $this->fetch();
    }

    //
    public function departmentData($department, $data)
    {
        foreach ($department as $key => $value) {
            $list = array();
            if (isset($value->room)) {
                foreach ($value->room as $room) {
                    array_push($list, $room['room']);
                }
            }
            $map = [
                'department_id' => $value['id'],
                'depart_name' => $value['name'],
                'roomlist' => $list
            ];
            array_push($data['departmentlist'], $map);
        }
        return $data;
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

    /*企业管理*/
    public function companyManage()
    {
        $userid = session('userId');
        $user = new WechatUser();
        if (IS_POST) {
            $data = input('');
            //改权限
            if ($data['type'] == 1) {
                unset($data['type']);
                $re = $user->save($data, ['userid' => $data['userid']]);
                if ($re || $re === 0) {
                    return $this->success('修改成功', '', $re);
                } else {
                    return $this->error('修改失败', '', WechatUser::getError());
                }
            } elseif ($data['type'] == 2) {
                $re = DB::execute("update  tb_park_company   set company_code=? where company_id =?", [$data['company_code'], $data['department']]);
                if ($re || $re === 0) {
                    return $this->success('修改成功', '', $re);
                } else {
                    return $this->error('修改失败', '', DB::getError());
                }
            }
        } else {

            $userlist = Db::query("select userid,name,header,avatar,mobile,fee_status,water_status,department from tb_wechat_user  where department = (select department from tb_wechat_user where userid=?) ", [$userid]);
            $park_company = new ParkCompany();
            foreach ($userlist as $key => $value) {
                if (empty($value['header'])) {
                    if (!empty($value['avatar'])) {
                        $userlist[$key]['header'] = $value['avatar'];
                    }
                }
            }

            $cpmpany = $park_company->where('company_id', $userlist[0]['department'])->find();

            $this->assign('company_code', $cpmpany['company_code']);
            $this->assign('userlist', json_encode($userlist));


            return $this->fetch();
        }
    }

    /* 我的审核（用于审核新闻的推送功能）*/
    public function myCheck()
    {
        if (IS_POST) {
            // type 2  未审核 1 已审核*/ 加载更多
            $length = input('length');
            $type = input('type');
            if ($type == 2) {
                $news = DB::query("select * from tb_news       where status =0  and type<=3 order by  create_time DESC  limit ?,?", [(int)$length, 6]);
                $a = $news;
                return $this->success('success', '', json_encode($news));
            } elseif ($type == 1) {
                $news = DB::query("select * from tb_news       where status >0  and type<=3 order by  create_time DESC limit ?,?", [(int)$length, 6]);
                $a = $news;
                return $this->success('success', '', json_encode($news));
            }
        } else {
            //未审核
            $uncheck = DB::query("select * from tb_news where status =0  and type<=3 order by  create_time DESC  limit 6");

            //已审核
            $checked = DB::query("select * from tb_news where status >0  and type<=3 order by  create_time DESC  limit 6");
            $this->assign('checked', json_encode($checked));
            $this->assign('uncheck', json_encode($uncheck));
            return $this->fetch();
        }

    }

    //新闻审核详情页
    public function newsCheck()
    {
        if (IS_POST) {
            $data = input('');
            //通过
            if ($data['type'] == 1) {
                $status = 1;
            } //不通过
            elseif ($data['type'] == 2) {
                $status = 2;
            }
            $news = DB::execute("update  tb_news   set status=? where id =?", [$status, $data['id'],]);
            if ($news) {
                return $this->success('成功');
            } else {
                return $this->error('失败');
            }
        } else {
            $id = input('id');
            $news = DB::query("select * from tb_news where id =?  and type<=3  ", [$id]);
            $this->assign('info', json_encode($news[0]));
            return $this->fetch();
        }
    }

    /*费用缴纳 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getFee($type, $departmentId)
    {
        $park_id = session('park_id');
        $service = new  Service();
        $map = ['company_id' => $departmentId, 'status' => ['neq', -1]];
        $list1 = array();
        if ($type == 3) {
            $list = FeePayment::where($map)->order('create_time desc')->field('id,type as service_name,status,create_time,company_id')->select();
            $appid = 1;
            $can_check = 'no';
        } else {
            $list = FeePayment::where(['status' => ['neq', -1]])->order('create_time desc')->field('id,type as service_name,status,create_time,name,company_id')->select();
            $appid = 1;
            $can_check = 'yes';
        }
        foreach ($list as $value) {
            if ($service->findParkid($value['company_id']) == $park_id) {
                array_push($list1, $value);
            }
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
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

        return $list1;
    }

    /*物业报修 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getProperty($type)
    {
        $types = [1 => '物业报修（空调报修）', 2 => "物业报修（电梯报修）", 3 => "物业报修（其他报修）"];
        $userid = session('userId');
        $park_id = session('park_id');
        if ($type == 3) {
            $list2 = PropertyServer::where(['type' => ['<', 4], 'user_id' => $userid, 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('proof,id,type as service_name,status,create_time')->select();
            $appid = 2;
            $can_check = 'no';
        } else {
            $list2 = PropertyServer::where(['type' => ['<', 4], 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('proof,id,type as service_name,status,create_time')->select();
            $appid = 2;
            $can_check = 'yes';
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        foreach ($list2 as $k => $v) {
            $v['service_name'] = $types[$v['service_name']];
            $v['create_time'] = date("Y-m-d", $v['create_time']);

            $list2[$k]['url'] = $url . $v['id'];
            if ($v['status'] == 0) {
                $list2[$k]['status_text'] = '审核中';
            } elseif ($v['status'] == 1) {
                $list2[$k]['status_text'] = '确认接单';
            } elseif ($v['status'] == 2) {
                $list2[$k]['status_text'] = '取消订单';
            } elseif ($v['status'] == 3) {
                $list2[$k]['status_text'] = '完成服务';
            }

            $list2[$k]['app_id'] = $appid;
        }
        return $list2;
    }

    /*饮水服务 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getWater($type)
    {
        $park_id = session('park_id');
        $userid = session('userId');
        $map = [
            'status' => array('neq', -1),
            'userid' => $userid,
            'park_id' => $park_id
        ];
        if ($type == 3) {
            $list3 = WaterService::where($map)->order('create_time desc')->field('id,status,create_time,userid')->select();
            $appid = 3;
            $can_check = 'no';
        } else {
            $list3 = WaterService::where(['status' => array('neq', -1), 'park_id' => $park_id])->order('create_time desc')->field('id,name,status,create_time,userid')->select();
            $appid = 3;
            $can_check = 'yes';
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        foreach ($list3 as $k => $v) {
            $v['service_name'] = "饮水服务";
            $list3 [$k]['url'] = $url . $v['id'];
            $v['create_time'] = date('Y-m-d', $v['create_time']);
            if ($v['status'] == 0) {
                $list3[$k]['status_text'] = '审核中';
            } elseif ($v['status'] == 1) {
                $list3[$k]['status_text'] = '确认接单';
            } elseif ($v['status'] == 2) {
                $list3[$k]['status_text'] = '取消订单';
            } else {
                $list3[$k]['status_text'] = '确认送达';
            }
            $list3[$k]['app_id'] = $appid;
        }
        return $list3;
    }

    /*保洁服务 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getClearn($type)
    {
        $park_id = session('park_id');
        $userid = session('userId');
        if ($type == 3) {
            $list4 = PropertyServer::where(['type' => ['=', 4], 'user_id' => $userid, 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('id,type as service_name,status,create_time')->select();
            $appid = 4;
            $can_check = 'no';
        } else {
            $list4 = PropertyServer::where(['type' => ['=', 4], 'status' => ['>=', 0], 'park_id' => $park_id])->order('create_time desc')->field('id,type as service_name,status,create_time')->select();
            $appid = 4;
            $can_check = 'yes';
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
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
        return $list4;


    }

    /*车卡服务 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getCar($type)
    {
        $park_id = session('park_id');
        $userid = session('userId');
        $list5 = array();
        if ($type == 3) {
            $list = CarparkService::where(['user_id' => $userid, 'status' => array('neq', -1)])->select();
            $appid = 6;
            $can_check = 'no';
        } else {
            $list = CarparkService::where(['status' => array('neq', -1), 'park_id' => $park_id])->select();
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
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
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
        return $list5;
    }

    /*充电柱服务 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getElectricity($type)
    {
        $park_id = session('park_id');
        $user_id = session('userId');

        $electricityService = new ElectricityService;
        $map = [
            'user_id' => $user_id,
            'status' => array('neq', -1),
            'park_id' => $park_id
        ];
        if ($type == 3) {
            $list6 = $electricityService->where($map)->order('create_time desc')->select();
            $appid = 7;
            $can_check = 'no';
        } else {
            $list6 = $electricityService->where(['status' => array('neq', -1), 'park_id' => $park_id])->order('create_time desc')->select();
            $appid = 7;
            $can_check = 'yes';
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/id/';
        int_to_string($list6, array('type' => array(1 => '充电柱办理(新柱办理)', 2 => '充电柱办理(旧柱续费)'), 'status' => array(0 => '审核中', 1 => '审核成功', 2 => '审核失败')));
        foreach ($list6 as $k => $value) {
            $value['service_name'] = $value['type_text'];
            $list6[$k]['url'] = $url . $value['id'];
            if ($list6[$k]['status'] == 0) {
                $list6[$k]['status_text'] = '审核中';
            } elseif ($list6[$k]['status'] == 1) {
                $list6[$k]['status_text'] = '审核成功';
            } else {
                $list6[$k]['status_text'] = '审核失败';
            }
            $list6[$k]['app_id'] = $appid;
        }
        return $list6;


    }

    /*公共区域服务 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getPublicArea($type)
    {
        $ad = new AdvertisingRecord();
        $fs = new FunctionRoomRecord();
        $led = new LedRecord();
        $park_id = session('park_id');
        $userid = session('userId');
        //大厅广告记录
        if ($park_id == 3) {
            $server_id = 1;
        } elseif ($park_id == 80) {
            //滨江园区还没有
            $server_id = 99;
        }
        $data1 = array();
        $time = array();
        $create_time = array();
        if ($type == 3) {
            $list = $ad->where(['create_user' => $userid, 'status' => array('in', [0, 2]), 'service_id' => $server_id])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'no';
            $type2 = 1;
        } else {
            $list = $ad->where(['status' => array('in', [0, 2]), 'service_id' => $server_id])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'yes';
            $type2 = 1;
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $type2 . '/create_time/';
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
                'service_name' => "设备服务（大厅广告位预约）",
                'url' => $url . strtotime($onetime),
                'app_id' => 8,
                'status' => $map[0]['status']
            ];


            if ($map[0]['status'] == 0) {
                $re['status_text'] = '已取消';
            } else if ($map[0]['status'] == 1) {
                $re['status_text'] = '选定未付款';
            } else if ($map[0]['status'] == 2) {
                $re['status_text'] = '审核成功';
            }

            array_push($data1, $re);

        }

        //二楼多功能厅
        $data2 = array();
        $time = array();
        $create_time = array();
        if ($park_id == 3) {
            $server_id = 2;
        } elseif ($park_id == 80) {
            //滨江园区还没有
            $server_id = 99;
        }

        if ($type == 3) {
            $list = $fs->where(['create_user' => $userid, 'status' => array('in', [0, 2]), 'service_id' => $server_id])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'no';
            $type2 = 2;
        } else {
            $list = $fs->where(['status' => array('in', [0, 2]), 'service_id' => $server_id])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'yes';
            $type2 = 2;
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $type2 . '/create_time/';
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
                'service_name' => "设备服务（多功能厅预约）",
                'url' => $url . strtotime($onetime),
                'app_id' => 8,
                'status' => $map[0]['status']

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
        if ($park_id == 3) {
            $server_id = 3;
        } elseif ($park_id == 80) {
            //滨江园区还没有
            $server_id = 99;
        }

        if ($type == 3) {
            $list = $led->where(['create_user' => $userid, 'status' => array('in', [0, 2]), 'service_id' => $server_id])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'no';
            $type2 = 3;

        } else {
            $list = $led->where(['status' => array('in', [0, 2]), 'service_id' => $server_id])->order('create_time desc')->select();
            $appid = 8;
            $can_check = 'yes';
            $type2 = 3;
        }
        $url = '/index/service/historyDetail/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $type2 . '/create_time/';
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
                'service_name' => "设备服务（大堂LED屏预约）",
                'create_time' => date('Y-m-d', strtotime($onetime)),
                'url' => $url . strtotime($onetime),
                'app_id' => 8,
                'status' => $map[0]['status']
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

        return array_merge($data1, $data2, $data3);
    }


    /*企业服务 type 1 运营身份  2 物业身份  3 用户本身 */
    public function getCompany($type)
    {
        $park_id = session('park_id');
        $userid = session('userId');
        switch ($type) {
            case 1:
                //企业服务（园区）
                $company_list = Db::table('tb_company_service')
                    ->alias('s')
                    ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
                    ->join('__WECHAT_USER__ b', 's.user_id=b.userid')
                    ->field('a.name as service_name,s.status,s.create_time,a.app_id')
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
                return $company_list;
                break;
            case 2:

                return array();
                break;
            case 3:
                $list = Db::table('tb_company_service')
                    ->alias('s')
                    ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
                    ->field('a.name as service_name,s.status,s.create_time,s.app_id')
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
                return $list;
                break;
        }
    }

    //商标查询type 1 运营身份  2 物业身份  3 用户本身
    public function getTrademarkInquire($type)
    {
        $park_id = session('park_id');
        $userid = session('userId');
        $appid = 12;
        $can_check = "yes";
        $list = array();
        switch ($type) {
            case 1:
                $list = TrademarkInquire::where(['status' => ['neq', -1], 'park_id' => $park_id])->select();
                foreach ($list as $value) {
                    if ($value['status'] == 0) {
                        $value['status_text'] = '未联系';
                    } else {
                        $value['status_text'] = '已联系';
                    }
                    $map = [
                        'service_name' => '商标查询',
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => $value['status_text'],
                        'status' => $value['status'],
                        'app_id' => $appid,
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/1/id/' . $value['id']
                    ];
                    array_push($list, $map);
                }
                return $list;
                break;
            case 2:
                return array();
                break;
            case 3:
                $list2 = TrademarkInquire::where(['status' => ['neq', -1], 'userid' => $userid])->select();
                foreach ($list2 as $value) {
                    if ($value['status'] == 0) {
                        $value['status_text'] = '未联系';
                    } else {
                        $value['status_text'] = '已联系';
                    }
                    $map = [
                        'service_name' => '商标查询',
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => $value['status_text'],
                        'status' => $value['status'],
                        'app_id' => $appid,
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/1/id/' . $value['id'],
                    ];
                    array_push($list, $map);
                }
                return $list;
                break;
        }
    }

    //商标咨询type 1 运营身份  2 物业身份  3 用户本身
    public function getTrademarkAdvisory($type)
    {
        $park_id = session('park_id');
        $userid = session('userId');
        $appid = 12;
        $can_check = "yes";
        $list = array();
        switch ($type) {
            case 1:
                $list = TrademarkAdvisory::where(['status' => ['neq', -1], 'park_id' => $park_id])->select();
                foreach ($list as $value) {
                    if ($value['status'] == 0) {
                        $value['status_text'] = '未联系';
                    } else {
                        $value['status_text'] = '已联系';
                    }
                    $map = [
                        'service_name' => '商标咨询',
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => $value['status_text'],
                        'status' => $value['status'],
                        'app_id' => $appid,
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/2/id/' . $value['id'],
                    ];
                    array_push($list, $map);
                }
                return $list;
                break;
            case 2:
                return array();
                break;
            case 3:
                $list2 = TrademarkAdvisory::where(['status' => ['neq', -1], 'userid' => $userid])->select();
                foreach ($list2 as $value) {
                    if ($value['status'] == 0) {
                        $value['status_text'] = '未联系';
                    } else {
                        $value['status_text'] = '已联系';
                    }
                    $map = [
                        'service_name' => '商标咨询',
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => $value['status_text'],
                        'app_id' => $appid,
                        'status' => $value['status'],
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/2/id/' . $value['id'],
                    ];
                    array_push($list, $map);

                }
                return $list;
                break;
        }
    }

    //专利申请
    public function getPatent($type)
    {
        $park_id = session('park_id');
        $userid = session('userId');
        $appid = 21;
        $can_check = "yes";
        $list = array();
        switch ($type) {
            case 1:
                $list = Patent::where(['status' => ['neq', -1], 'park_id' => $park_id])->select();
                int_to_string($list, $map = array('type' => array(1 => '发明型专利', 2 => '实用型专利', 3 => '外观设计'), 'status' => array(0 => '审核中', 1 => '审核成功', 2 => '审核失败')));
                foreach ($list as $value) {
                    $map = [
                        'service_name' => $value['type_text'],
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => $value['status_text'],
                        'status' => $value['status'],
                        'app_id' => $appid,
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $value['type'] . '/id/' . $value['id'],
                    ];
                    array_push($list, $map);
                }
                return $list;
                break;
            case 2:
                return array();
                break;
            case 3:
                $list2 = Patent::where(['status' => ['neq', -1], 'create_user' => $userid])->select();
                int_to_string($list2, $map = array('type' => array(1 => '发明专利', 2 => '实用型专利', 3 => '外观设计'), 'status' => array(0 => '审核中', 1 => '审核成功', 2 => '审核失败')));
                foreach ($list2 as $value) {
                    $map = [
                        'service_name' => isset($value['type_text']) ? $value['type_text'] : "",
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => isset($value['status_text']) ? $value['status_text'] : "",
                        'status' => $value['status'],
                        'app_id' => $appid,
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $value['type'] . '/id/' . $value['id'],
                    ];
                    array_push($list, $map);
                }
                return $list;
                break;
        }
    }

    public function getCopyRight($type)
    {
        $appid = 22;
        $can_check = "yes";
        $copysoftwrite = new CopyrightSoftwrite();
        $copyart = new CopyrightArt();
        $copysoft = new CopyrightSoft();
        $list = array();
        switch ($type) {
            case 1:
                $copyartlist = $copyart->getCoypright(2);
                $copysoftwritelist = $copysoftwrite->getCoypright(2);
                $copysoftlist = $copysoft->getCoypright(2);
                $list = array_merge($copyartlist, $copysoftlist, $copysoftwritelist);
                foreach ($list as $value) {
                    $map = [
                        'service_name' => $value['type_text'],
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => $value['status_text'],
                        'status' => $value['status'],
                        'app_id' => $appid,
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $value['type'] . '/id/' . $value['id'],
                    ];
                    array_push($list, $map);
                }
                return $list;
                break;
            case 2:
                return array();
                break;
            case 3:
                $copyartlist = $copyart->getCoypright(1);
                $copysoftwritelist = $copysoftwrite->getCoypright(1);
                $copysoftlist = $copysoft->getCoypright(1);
                $list2 = array_merge($copyartlist, $copysoftlist, $copysoftwritelist);
                foreach ($list2 as $value) {
                    $map = [
                        'service_name' => $value['type_text'],
                        'create_time' => date("Y-m-d", $value['create_time']),
                        'status_text' => $value['status_text'],
                        'status' => $value['status'],
                        'app_id' => $appid,
                        'id' => $value['id'],
                        'url' => '/index/service/historyDetailCompany/appid/' . $appid . '/can_check/' . $can_check . '/type/' . $value['type'] . '/id/' . $value['id'],
                    ];
                    array_push($list, $map);
                }
                return $list;
                break;
        }
    }

    /*我的服务*/
    public function service()
    {
        $userid = session('userId');
        $park_id = session('park_id');
        $userinfo = WechatUser::where(['userid' => $userid])->find();
        $service = new \app\common\behavior\Service();
        //$type :1 运营 2 物业 3 普通企业（当前用户身份权限）
        $type = 3;
        if ($park_id == 3) {
            $userPower = 76;
        } elseif ($park_id == 80) {
            $userPower = 87;
        }
        if ($userinfo['department'] == $userPower) {
            $type = 1;
        } elseif ($userinfo['department'] == 86 || $userinfo['department'] == 90) {
            $type = 2;
        }
        $appids = empty($userinfo->Operational->appids) ? array() : json_decode($userinfo->Operational->appids);
        //费用缴纳
        $list1 = $this->getFee($type, $userinfo['department']);
        //物业报修
        $list2 = $this->getProperty($type);
        //饮水服务
        $list3 = $this->getWater($type);
        //室内保洁
        $list4 = $this->getClearn($type);
        //车卡服务
        $list5 = $this->getCar($type);
        //充电柱办公
        $list6 = $this->getElectricity($type);
        //公共服务
        $list7 = $this->getPublicArea($type);
        //根据权限来显示
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
                    $allList = array_merge($list7, $allList);
                }
                if (in_array(10, $appids)) {
                    //企业服务
                    $company_list = $this->getCompany($type);
                    //商标查询（园区）
                    $TrademarkInquire = $this->getTrademarkInquire($type);
                    //商标咨询（园区）
                    $TrademarkAdvisory = $this->getTrademarkAdvisory($type);
                    //专利申请（园区）
                    $Patent = $this->getPatent($type);
                    //版权登记（园区）
                    $copy = $this->getCopyRight($type);
                }
                $company_list = array_merge($Patent, $TrademarkAdvisory, $TrademarkInquire, $copy, $company_list);
                $company_list = empty($company_list) ? array() : $company_list;
                $this->assign('company', json_encode($company_list));
                break;
            case 2:
                $allList = array_merge($list2, $list3, $list4);
                $this->assign('company', '[]');
                break;
            case 3:
                //企业服务
                $company_list = $this->getCompany($type);
                //商标查询（用户）
                $TrademarkInquire = $this->getTrademarkInquire($type);
                //商标咨询（用户）
                $TrademarkAdvisory = $this->getTrademarkAdvisory($type);
                //专利申请（用户）
                $Patent = $this->getPatent($type);
                //版权登记(用户)
                $copy = $this->getCopyRight($type);
                //全部合并（企业服务）
                $company_list = array_merge($Patent, $TrademarkAdvisory, $TrademarkInquire, $copy, $company_list);
                //(物业服务)
                if ($userinfo['fee_status'] == 1) {
                    $allList = array_merge($list1, $allList);
                }
                $allList = array_merge($allList, $list2, $list3, $list4, $list5, $list6, $list7);
                //按时间排序
                $company_list = list_sort_by($company_list, 'create_time', 'desc');
                //按状态排序
                $company_list = list_sort_by($company_list, 'status', 'asc');
                $this->assign('company', json_encode($company_list));
                break;
        }
        //把数组按时间排序
        usort($allList, function ($a, $b) {
            $al = strtotime($a['create_time']);
            $bl = strtotime($b['create_time']);
            if ($al == $bl)
                return 0;
            return ($al > $bl) ? -1 : 1;
        });
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

    /**
     * 更改园区（只在 滨江模拟与希垦模拟中切换）
     */
    public function changeDepartment()
    {
        $park_id = input('park_id');
        $userId = input('user_id');
        $people_card = input('people_card');
        $car_card = input('car_card');
        $name = input('user_name');
        $gender = input('gender');
        $park_company = new ParkCompany();
        //echo $userId;
        $department = input('departmentId');
        $room = input('room');
        //return $department;
        if (empty($department)) {
            if ($park_id == 3) {
                $department = 78;
            } elseif ($park_id == 80) {
                $department = 91;
            }
        }
        $data = [
            'userid' => $userId,
            'department' => [$department],
        ];
        $map = [
            'department' => $department,
            'park_id' => $park_id,
            'company_address' => $room
        ];
        if (!empty($people_card)) {
            $map['people_card'] = $people_card;
        }
        if (!empty($car_card)) {
            $map['car_card'] = $car_card;
        }
        if (!empty($name)) {
            $map['name'] = $name;
            $data['name'] = $name;
        }
        if (!empty($gender)) {
            $map['gender'] = $gender;
            $data['gender'] = $gender;
        }
        Loader::import('wechat\TPWechat', EXTEND_PATH);
        $wechat = new TPWechat(config('party'));
        $user = new WechatUser();
        $map['manage'] = 0;
        $map['water_status'] = 0;
        $map['fee_status']=0;
        $result = $user->where(['userid' => $userId])->update($map);
        $res = $wechat->updateUser($data);
        if ($res) {


            Session::set('park_id', $park_id);
            $this->success("修改成功", '', session('park_id'));
        } else {

            $this->error("修改失败");
        }
    }

    //验证
    public function verification()
    {
        $department = input('department');
        $park_company = new ParkCompany();
        $company = $park_company->where('company_id', $department)->find();
        if ($company['company_code'] == input('company_code')) {
            $this->success("验证码成功");

        } else {

            $this->error("验证码错误");
        }
    }

    //推荐关注
    public function recommend()
    {
        $park_id = session('park_id');
        $this->assign('park_id', json_encode($park_id));
        return $this->fetch();
    }

    public function version()
    {
        return $this->fetch();
    }

}