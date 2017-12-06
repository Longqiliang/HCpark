<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/12/6
 * Time: 上午10:07
 */

namespace app\index\model;


use think\Model;
use app\index\model\CompanyApplication;
use app\index\controller\Service;
class CopyrightArt extends Model
{


    protected $insert = [
        'create_time' => NOW_TIME,
        'status' => 1,
    ];


    protected $type = [

        'create_time' => 'strotrtime',
        'end_time' => 'strotrtime'
    ];

//当前用户的所有艺术
    public function copyHistory()
    {

        $userid = session('userId');
        $map = [
            'userid' => $userid,
            'status' => array('neq', -1),
        ];
        $list = $this->where($map)->field('id,status,create_time,end_time,contact_staff,contact_number,2 as type ')->select();
        return $list;
    }
    //审核通过／不通过
    public function check($type, $id, $data)
    {
        if ($type == 1) {
            //审核通过
            $info = $this->where('id',$id)->update(['status'=>1,'end_time'=>time()]);

        } else {
            //审核不通过
            $info = $this->where('id',$id)->update(['status'=>$type,'end_time'=>time(),'reply'=>$data['reply']]);
        }

        $service = new Service();
        $copy =$this->find($id);
        if($info){
            //推送
             $message = [
                 "title" => "版权申请提示",
                 "description" => "您的美术作品版权申请园区已确认，请您携带相关材料前往希垦科技园A幢2楼园区知识产权服务中心办理，点击查看详情",
                 "url" => 'https://' . $_SERVER['HTTP_HOST'] .'/index/service/historyDetailCompany/appid/22/can_check/no/type/1/id/' .$id
             ];
             //审核成功推用户
             $service->commonSend(4, $message,$copy['userid'] , 22);
            return true;
         }else {

            $message = [
                "title" => "版权申请提示",
                "description" => "您的美术作品版权申请园区审核失败，请您核对信息后重新提交，",
                "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetailCompany/appid/22/can_check/no/type/1/id/' .$id
            ];
            //点击查看详情
            if(!empty($copy['reply'])){
                $message['description'] = $message['description']."备注：".$copy['reply']."点击查看详情";
            }
            //审核失败推用户
            $service->commonSend(4, $message, $copy['userid'], 22);
             return false;
         }
    }









    //提交申请的时候审核数据
    public function _checkData($data)
    {
        if (empty($data)) {
            return false;
        }
        if (isset($data['art_name']) || isset($data['works_description']) || isset($data['originality_description']) || isset($data['proudct_img']) || isset($data['contact_staff']) || isset($data['contact_number'])) {

            return false;
        }

        if (isset($data['art_name']) || isset($data['works_description']) || isset($data['originality_description']) || isset($data['proudct_img']) || isset($data['contact_staff']) || isset($data['contact_number'])) {

            return false;
        }


        return true;

    }

    //获取艺术作品详情页
    public function copyHistoryDetail($id, $appid)
    {
        $info = $this::get($id);
        $app = CompanyApplication::Where('app_id', $appid)->find();
        $info['type_name'] = "艺术作品";
        $info['product_img'] = json_decode($info['product_img']);
        $info['app_name'] = $app['name'];
        $info['type_check'] = 1;
        return $info;
    }


}