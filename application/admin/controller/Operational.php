<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/10/9
 * Time: 下午2:29
 */

namespace app\admin\controller;

use app\common\model\WechatUser;
use app\common\model\OperationalAuthority;
use app\common\behavior\Service;
use function PHPSTORM_META\type;
use think\Db;

//运营权限管理
class Operational extends Admin
{
    //该园区全部运营人员(tb_operational_authority)
    public function index()
    {
        $park_id = session('user_auth')['park_id'];
        $wechatUser = new WechatUser();
        $OperationalAuthority = new OperationalAuthority();
        //$list = Db::query('select * from tb_wechat_user where department =76 and park_id=?', [$park_id]);
        $list = $wechatUser->where(['department' => 76, 'park_id' => $park_id])->paginate();
        $Operational = $OperationalAuthority->select();
        $user = array();
        $dele = array();
        foreach ($Operational as $value) {
            $type = 1;

            foreach ($list as $value2) {
                if ($value['userid'] == $value2['userid']) {
                    $type = 2;
                }
            }
            if ($type == 1) {
                array_push($dele, $value['userid']);
            }
        }
        foreach ($list as $value) {
            $type = 1;
            foreach ($Operational as $value2) {
                if ($value['userid'] == $value2['userid']) {
                    $type = 2;
                }
            }
            if ($type == 1) {
                $map = ['userid' => $value['userid']];
                array_push($user, $map);
            }
        }
        $add = $OperationalAuthority->saveAll($user);
        if (count($dele) > 0) {
            $delete = $OperationalAuthority->where(['userid'=>array('in',$dele)])->delete();
        }

        $this->assign('list', $list);
        return $this->fetch();
    }


    public function edit()
    {
        $user_id = input('userid');
        $park_id = session('user_auth')['park_id'];
        $op = new OperationalAuthority();
        if (IS_POST) {
            $data = input('');
            $appids = array();
            $checklist = Db::query('select * from tb_company_application ');
            $check = array();
            foreach ($checklist as $value) {
                $value['park_id'] = json_decode($value['park_id']);
                if (in_array($park_id, $value['park_id']) && $value['type'] == 1) {
                    array_push($check, $value['app_id']);
                }
            }
            $is_company = 1;
            $data['appids'] = isset($data['appids']) ? $data['appids'] : array();
            foreach ($data['appids'] as $appid) {
                if ($appid == 100) {
                    $is_company = 2;
                } else {
                    array_push($appids, $appid);
                }

            }
            if ($is_company == 2) {
                if (count($appids) > 0) {
                    $appids = array_merge($appids, $check);
                } else {
                    $appids = $check;
                }
            }
            $reult = $op->where('userid', $data['userid'])->find();
            $reult['appids'] = json_encode($appids);
            $re = $reult->save();
            if ($re) {
                return $this->success("保存成功");
            } else {
                return $this->error("保存失败");
            }
        } else {
            $list = $op->where('userid', $user_id)->find();
            $checklist = Db::query('select * from tb_company_application ');
            $check = array();
            foreach ($checklist as $value) {
                $value['park_id'] = json_decode($value['park_id']);
                if (in_array($park_id, $value['park_id']) && $value['type'] == 0) {
                    $map = [
                        'id' => $value['app_id'],
                        'name' => $value['name']
                    ];
                    array_push($check, $map);
                }
            }
            $map = [
                'id' => 100,
                'name' => "企业服务",
            ];
            array_push($check, $map);
            $list['appids'] = empty($list['appids']) ? array() : json_decode($list['appids']);
            $company = 0;
            foreach ($list['appids'] as $appid) {
                foreach ($check as $key => $value) {
                    if ($value['id'] == $appid) {
                        $check[$key]['check'] = 'true';
                    }
                }
                if (9 < $appid && $appid < 19) {
                    $company = 1;
                }
            }
            if ($company == 1) {
                foreach ($check as $key => $value) {
                    if ($value['id'] == 100) {
                        $check[$key]['check'] = 'true';
                    }
                }

            }
            $user_name = isset($list->user->name) ? $list->user->name : "";
            $this->assign('use_name', $user_name);
            $this->assign('userid', $user_id);
            $this->assign('check', $check);
            return $this->fetch();
        }

    }
}