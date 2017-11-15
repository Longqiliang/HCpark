<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/15
 * Time: 下午2:02
 */

namespace app\admin\controller;
use app\common\model\ExchangeRecord;
use app\common\model\ExchangeProduct;
use app\index\model\WechatUser;


class Shop extends Admin
{
    /**
     * 主页列表
     */
    public function index()
    {
        $park_id = session('user_auth')['park_id'];
        $search= input('search');

        $map = array(
            'status' => array('eq', 0),
            'park_id'=>$park_id
        );
        if(!empty($search)){
            $map['search']=$search;
        }
        $ProductModel = new ExchangeProduct();
        $list = $ProductModel->where($map)->order('id desc')->paginate(12);
        int_to_string($list, array(
            'status' => array(0 => "正常"),
        ));
        $this->assign('list', $list);
        $this->assign('search', $search);
        return $this->fetch();
    }

    /*
     * 商品添加
     */
    public function add()
    {
        if (IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if (empty($data['id'])) {
                unset($data['id']);
            }
            $data['create_time'] = time();
            $data['left'] = $data['num'];
            $productModel = new ExchangeProduct();
            $info = $productModel->validate('product')->save($data);
            if ($info) {
                return $this->success("新增成功", Url('Shop/index'));
            } else {
                return $this->error($productModel->getError());
            }
        } else {
            $a = array('1'=>'a','2'=>'b','3'=>'c','4'=>'d','5'=>'e','6'=>'f','7'=>'g','8'=>'h','9'=>'i','10'=>'j','11'=>'k','12'=>'l','13'=>'m','14'=>'n','15'=>'o');
            $front_pic = array_rand($a,1);
            $this->assign('front_pic',$front_pic);
            $this->assign('msg', '');
            return $this->fetch('goodedit');
        }
    }
    /*兑换记录*/
    public function recordinfo()
    {
        $id = input('id');
        $recordlist = ExchangeRecord::where(array('product_id' => $id, 'status' => array('neq', -1)))->order('status asc')->paginate(12);
        int_to_string($recordlist, array('status' => array(0 => "等待兑换", 1 => "兑换完成")));
        foreach ($recordlist as $child) {
            $child['title'] = isset($child->productinfo->title) ? $child->productinfo->title : "";
            $child['nickname'] = isset($child->user->name) ? $child->user->name : "";
        }
        $this->assign("list", $recordlist);
        echo json_encode($recordlist);
        return $this->fetch();
    }

    /*确认兑换*/
    public function checkrecord()
    {
        $id = input('id');
        $data = [
            'status' => 1
        ];
        $recordinfo = ExchangeRecord::where('id', $id)->update($data);
        if ($recordinfo) {

            return $this->success('兑换成功');
        } else {
            return $this->error('兑换失败');
        }

    }

    /*删除兑换记录*/
    public function delrecord()
    {
        $id = input('id');

        $data = [
            'status' => -1

        ];
        $recordinfo = ExchangeRecord::where('id', $id)->update($data);
        if ($recordinfo) {

            return $this->success('删除成功');
        } else {
            return $this->error('删除失败');
        }

    }

    /**
     * 商品修改
     */
    public function goodedit()
    {
        if (IS_POST) {
            $data = input('post.');
            $productModel = new ExchangeProduct();
            $info = $productModel->validate('product')->save($data, ['id' => input('id')]);
            if ($info) {
                return $this->success("修改成功", Url("Shop/index"));
            } else {
                return $this->get_update_error_msg($productModel->getError());
            }
        } else {
            $id = input('id');
            $msg = ExchangeProduct::get($id);

            $this->assign('msg', $msg);
            return $this->fetch();
        }
    }

    /**
     * 删除功能
     */
    public function del()
    {
        $id = input('id');
        $data['status'] = '-1';
        $info = ExchangeProduct::where('id', $id)->update($data);
        if ($info) {
            return $this->success("删除成功");
        } else {
            return $this->error("删除失败");
        }
    }

    public function user()
    {
        $UserProfile = new  WechatUser();
        $Record = new ExchangeRecord();
        $userlist = $UserProfile->paginate(12);

            int_to_string($userlist, array
            ('status' => array(0 => '禁用', 1 => '启用')));

            $this->assign("list", $userlist);

        return $this->fetch();
    }

    public function user_record($id)
    {

        $recordinfo = ExchangeRecord::where(array('userid' => $id, 'status' => array('neq', -1)))->order('status asc')->paginate(12);
        int_to_string($recordinfo, array('status' => array(0 => "等待兑换", 1 => "兑换完成")));
        foreach ($recordinfo as $child) {
            $child['title'] = isset($child->productinfo->title) ? $child->productinfo->title : "";
            $child['name'] = isset($child->user->name) ? $child->user->name : "";
        }
        $this->assign("list", $recordinfo);
        return $this->fetch('recordinfo');


    }


}