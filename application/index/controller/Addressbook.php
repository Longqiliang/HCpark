<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * QQ: 2205446834@qq.com
 * Date: 2017/8/9
 * Time: 14:39
 */

namespace app\index\controller;
use app\index\model\WechatDepartment;
use app\index\model\WechatUser;

class Addressbook extends Base
{
    /*   通讯录首页*/
    public function index()
    {
        $user = WechatUser::where('userid', session("userId"))->find();
        $hand = WechatDepartment::where('parentid', 0)->find();
        //全部的部门
        $department = $this->findChild($hand);
        $sd = $this->mydeparment();

        if($user['department']==1){
            $this->assign('department', $department);


        }else {
            if(count($sd)!=4){
                return $this->error("该用户所属部门结构不为4层（例：智新则地/希垦园区/运营团队/其他员工）");
            }


            //所在园区id
            $parkid = $sd[1];
            $parkdeparment = WechatDepartment::where('parentid', $parkid)->select();
            //所在园区下的所有部门id
            $alldepartmentid = array();
            foreach ($parkdeparment as $deparment) {
                switch ($deparment['name']) {
                    case '服务团队':
                        $alldepartmentid['serviceid'] = $deparment['id'];
                        break;
                    case '运营团队':
                        $alldepartmentid['operationid'] = $deparment['id'];
                        $manager = WechatDepartment::where('parentid', $alldepartmentid['operationid'])->select();
                        //总经理的id
                        foreach ($manager as $manage2) {
                            if ($manage2['name'] == '总经理') {
                                $alldepartmentid['manageid'] = $manage2['id'];
                            }
                        }
                        break;
                    case '企业':
                        $alldepartmentid['companyid'] = $deparment['id'];
                        break;
                }
            }
            //所在部门id(如服务部门，运营，企业)
            $departmentid = $sd[2];
            //用户为总经理
            if ($sd[3] == $alldepartmentid['manageid']) {
                $this->assign('department', $department);
            } //用户不为忠经理，进行其他判断
            else {
                $hand2 = WechatDepartment::where('parentid', 0)->find();
                $hand2['child'] = array();
                $map = [
                    'id' => $parkid,
                    'parentid' => $hand2['id']
                ];
                $park = WechatDepartment::where($map)->find();
                $park['child'] = array();
                switch ($departmentid) {
                    //该用户是服务团队（可见：运营除总经理；服务团队；）
                    case $alldepartmentid['serviceid']:
                        $a = array($alldepartmentid['serviceid'], $alldepartmentid['operationid']);
                        $map = ['parentid' => $park['id']];
                        $map['id'] = array(array('in', $a), array('neq', $alldepartmentid['manageid']));
                        $park = $this->findChild2($park, $map);
                        // array_unshift($aa,$server);
                        //array_unshift($aa,$operation);
                        //$park['child']=$aa;
                        $hand2['child'] = array($park);
                        break;
                    //该用户是运营团队 （其他员工）
                    case $alldepartmentid['operationid']:
                        $map = ['parentid' => $park['id']];
                        $map['id'] = array('neq', $alldepartmentid['manageid']);
                        $park = $this->findChild3($park, $map);
                        $hand2['child'] = array($park);
                        break;
                    //该用户是企业团队(可见：服务团队和自己企业)
                    case $alldepartmentid['companyid']:
                        $a = array($alldepartmentid['serviceid'], $sd[0], $sd[1], $sd[2], $sd[3]);
                        $serverchild = WechatDepartment::where('parentid', $alldepartmentid['serviceid'])->select();
                        //加入所有服务部门下的部门id。
                        foreach ($serverchild as $child) {
                            array_unshift($a, $child['id']);
                        }
                        //将$a[]中的所有为一个查询约束id in
                        $park = $this->myfindChild($park, $a);
                        $hand2['child'] = array($park);
                        break;
                }
                $this->assign('department', $hand2);

            }
        }
        /*    echo json_encode($hand2);*/
        $this->assign('user', $user);
        return $this->fetch();
    }
    //全部部门的方法
    public function findChild($hand)
    {
        $child = WechatDepartment::where('parentid', $hand['id'])->select();
        if ($child) {
            $hand['child'] = $child;
            foreach ($hand['child'] as $child) {
                $child = $this->findChild($child);
            }
            return $hand;
        } else {
            $hand['child'] = "";
            return $hand;
        }
    }
    //只看服务和运营的方法（除去总经理）
    public function findChild2($hand,$map)
    {
        $child = WechatDepartment::where($map)->select();
        if ($child) {
            $hand['child'] = $child;
            foreach ($hand['child'] as $child) {
                $m=array();
                $m['id']=$map['id'][1];
                $m['parentid']=$child['id'];
                $child = $this->findChild2($child,$m);
            }
            return $hand;
        } else {
            $hand['child'] = "";
            return $hand;
        }
    }
    //看全部（除去总经理）
    public function findChild3($hand,$map)
    {
        $child = WechatDepartment::where($map)->select();
        if ($child) {
            $hand['child'] = $child;
            foreach ($hand['child'] as $child) {
                $map['parentid']=$child['id'];
                $child = $this->findChild3($child,$map);
            }
            return $hand;
        } else {
            $hand['child'] = "";
            return $hand;
        }
    }
    //自己部门的findchild方法
    // $hand： 根部门 $id: 约束id=[1,2,3] 例where in id
    public function myfindChild($hand, $id)
    {
        $map = [
            'parentid' => $hand['id']
        ];
        $s1 = implode(",", $id);
        // echo json_encode($s1);
        // echo json_encode($hand['id']);
        $wd = new WechatDepartment();
        $map['id'] = array('in', $s1);
        $child = $wd->where($map)->select();
        /* echo $wd->getLastSql();*/
        if ($child) {
            $hand['child'] = $child;
            foreach ($hand['child'] as $child) {
                $child = $this->myfindChild($child, $id);
            }
            return $hand;
        } else {
            $hand['child'] = "";
            return $hand;
        }
    }
    //只看自己部门的方法(return 所有上级部门id （一条分支上的所有id）)
    public function mydeparment()
    {
        $wd = new  WechatDepartment();
        $user = WechatUser::where('userid', session("userId"))->find();
        /* 该登录用户的上级部门*/
        //echo json_encode($user);

        $dep = WechatDepartment::where('id', $user['department'])->find();
        //echo json_encode($dep);

        $did2 = array();
        /*   所有上级部门id*/

        $did=$wd->findallfartherbychild($dep, $did2);

        return $did;
    }
    //只看自己部门的方法(return 所有上级部门id （一条分支上的所有id）)
    public function breadcrumbs($id)
    {
        $wd = new  WechatDepartment();

        /* 该登录用户的上级部门*/
        $dep = WechatDepartment::where('id', $id)->find();
        $did2 = array();
        /*   所有上级部门id*/
        $did=$wd->findallfartherbychild($dep, $did2);
        return $did;
    }

    /*   通讯录用户列表*/
    public function userlist()
    {
        $id=input('id');
        $userlist=WechatUser::where('department',$id)->select();
        $this->assign("list",$userlist);
        $sd = $this->breadcrumbs($id);

        //面包屑
        $ad =array();
        foreach ($sd as $id)
        {
            $parkdeparment=WechatDepartment::where('id',$id)->find();
            array_push($ad,array('id'=>$id,'name'=> $parkdeparment['name'] ));
        }
        $this->assign("mbx",$ad);
        return $this->fetch();
    }
    /*   通讯录用户详情*/
    public function userinfo()
    {

        $id = input('userid');
        $userinfo = WechatUser::where('userid', $id)->find();
        $this->assign("info", $userinfo);
        return $this->fetch();
    }





}