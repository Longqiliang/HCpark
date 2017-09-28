<?php
namespace app\admin\controller;

use app\common\model\CompanyApplication as CompanyModel;
use app\common\model\WechatDepartment;
use think\Db;

class CompanyApplication extends Admin
{
    public function index()
    {
        //模糊查询
        $search = input('search');
        if ($search != '') {
            $list = CompanyModel::where('name', 'like', '%' . $search . '%')->order('id desc')->paginate();
        } else {
            $list = CompanyModel::order('id desc')->paginate();
        }
        //遍历园区
        $park = WechatDepartment::where('parentid', '1')->field('id,name')->select();

        foreach ($list as $key => $val) {
            $val['park_id'] = json_decode($val['park_id'], true);
            $a = array();
            if (!empty($val['park_id'])) {
                foreach ($val['park_id'] as $k => $v) {
                    foreach ($park as $k2 => $v2) {
                        if ($v == $v2['id']) {
                            $a[$k2] = $v2['name'];
                        }
                    }
                }
            }
            $val['park_id'] = $a;
        }
        int_to_string($list, array(
            'type' => array(1 => '企业服务', 0 => '物业服务',2=>'人才服务'),
        ));
        int_to_string($list, array(
            'has_alipay' => array(1 => '支持', 2 => '不支持'),
        ));
        int_to_string($list, array(
            'has_bank' => array(1 => '支持', 2 => '不支持'),
        ));
        //分页
        $page = $list->render();
        $this->assign('page', $page);
        //所属园区

        $this->assign('park', $park);
        //列表
        $this->assign('list', $list);
        return $this->fetch();
    }


    //编辑应用
    public function add()
    {
        if (IS_POST) {
            $company = new CompanyModel();
            if (isset($_POST['park_id']) && $_POST['park_id']) {
                $_POST['park_id'] = json_encode($_POST['park_id']);
            } else {
                $_POST['park_id'] = '';
            }
            if (input('id')) {
                $result = $company->allowField(true)->validate(true)->save($_POST, ['id' => input('id')]);
            } else {
                unset($_POST['id']);
                $result = $company->allowField(true)->validate(true)->save($_POST);
            }
            if ($result) {
                $this->success('添加成功', url('CompanyApplication/index'));
            } elseif ($result === 0) {
                $this->error('没有更新内容');
            } else {
                $this->error($company->getError());
            }
        } else {
            $id = input('id');
            $check = WechatDepartment::where('parentid', '1')->field('id,name')->select();
            $res = CompanyModel::where('id', input('id'))->find();
            if ($res['park_id']) {
                $res['park_id'] = json_decode($res['park_id'], true);

                foreach ($check as $k => $v) {
                    foreach ($res['park_id'] as $key => $val) {
                        if (isset($val) && $v['id'] == $val) {
                            $v['park_id'] = $val;
                        }
                    }
                }
            }
            $this->assign('list', $res);
            $this->assign('check', $check);
            return $this->fetch();
        }
    }

    public function del()
    {

        $company = new CompanyModel();
        $result = $company->where('id', 'in', $_POST['ids'])->delete();
        if ($result) {
            return $this->success('删除成功', url('CompanyApplication/index'));
        } elseif (!$result) {

            return $this->error('删除失败');
        }
    }


}