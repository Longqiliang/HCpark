<?php
/**
 * Created by PhpStorm.
 * User: shen
 * Date: 2017/8/30
 * Time: 下午1:40
 */
namespace app\admin\controller;

use app\common\model\CompanyService as CompanyServiceModel;
use think\Db;
class CompanyService extends Admin
{
    public function index()
    {
        $search = input('search');
        $map['s.status']  = ['neq',-1];
        if ($search != '') {
            $map['s.company'] = ['like','%'.$search.'%'];
        }
        $parkid =session("user_auth")['park_id'];

        $list = Db::table('tb_company_service')
            ->alias('s')
            ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
            ->join('__WECHAT_USER__ c', 'c.userid=s.user_id')
            ->field('a.name,s.id,s.company,s.people,s.mobile,s.remark,s.status,s.create_time')
            ->where($map)
            ->where('c.park_id',$parkid)
            ->paginate(12,false,['query' => request()->param()]);
        $map['s.status']=0;
        $list2 = Db::table('tb_company_service')
            ->alias('s')
            ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
            ->join('__WECHAT_USER__ c', 'c.userid=s.user_id')
            ->field('a.name,s.id,s.company,s.people,s.mobile,s.remark,s.status,s.create_time')
            ->where($map)
            ->where('c.park_id',$parkid)
            ->select();
        $total=count($list2);
        $this->assign('search',$search);
        $this->assign('total',$total);
        $this->assign('list',$list);
        return $this->fetch();
    }

//详情页
    public function detail(){
        $id=input('id');
        $result = Db::table('tb_company_service')
            ->alias('s')
            ->join('__COMPANY_APPLICATION__ a', 's.app_id=a.app_id')
            ->field('a.name,s.id,s.company,s.people,s.mobile,s.remark,s.status,s.create_time')
            ->where('s.id','eq',$id)
            ->find();

        $this->assign('res',$result);
        return $this->fetch();
    }

//逻辑删除
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = CompanyServiceModel::where('id', 'in', $ids)->update(['status' => -1]);
        if($result) {
            return $this->success('删除成功', url('CompanyService/index'));
        } elseif(!$result) {
            return $this->error('删除失败');
        }
    }

    //完成
    public function complete() {
        $id = input('id');
        $result = CompanyServiceModel::where('id', 'in', $id)->update(['status' => 1]);
        if ($result){

            $this->success("已联系");
        }else{

            $this->error("失败");
        }
    }



}