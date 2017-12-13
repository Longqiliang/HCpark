<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/28
 * Time: 上午11:22
 */

namespace app\index\model;


use think\Model;
use app\index\controller\Service;

//专利
class Patent extends Model
{
    protected $type = [
        'create_time' => 'strtotime',
        'end_time' => 'strtotime'
    ];
    protected $insert = [

        'create_time' => NOW_TIME,
        'status' => 0,

    ];

    //接口参数校验
    public function _checkData($type, $data)
    {
        if (empty($type) || empty($data)) {
            return false;
        } else {
            // 发明专利 实用新型
            if (!isset($data['patent_name']) ||
                !isset($data['inventor']) ||
                !isset($data['applicant']) ||
                !isset($data['contact']) ||
                !isset($data['contact_address']) ||
                !isset($data['contact_number']) ||
                !isset($data['id_card'])
            ) {
                return false;
            }
            if (empty($data['patent_name']) ||
                empty($data['inventor']) ||
                empty($data['applicant']) ||
                empty($data['contact']) ||
                empty($data['contact_address']) ||
                empty($data['contact_number']) ||
                empty($data['id_card'])
            ) {
                return false;
            }
            //外观设计
            if ($type == 3) {
                if (!isset($data['explanation']) ||
                    !isset($data['product_img'])

                ) {
                    return false;
                }
                if (empty($data['explanation']) ||
                    empty($data['product_img'])
                ) {
                    return false;
                }
            }
            return true;
        }
    }

    public function patentHistory()
    {
        $userid = session('userId');
        $park_id = session('park_id');
        $patent = $this->where(['create_user' => $userid, 'park_id' => $park_id])->select();
        foreach ($patent as $k => $v) {
            $info[$k] = [
                'id' => $v['id'],
                'type' => $v['type'],
                'time' => date('Y-m-d', $v['create_time']),
                'status' => $v['status'],
                'name' => $v['patent_name']
            ];
        }
        return $info;

    }

    public function patentHistoryDetail($id, $appid)
    {

        $info = $this::get($id);
        $app = CompanyApplication::Where('app_id', $appid)->find();
        if ($info['type'] == 3) {
            $info['type_name'] = "外观设计";
            $info['product_img'] = json_decode($info['product_img']);
        } elseif ($info['type'] == 1) {

            $info['type_name'] = "发明专利";
        } elseif ($info['type'] == 2) {
            $info['type_name'] = "实用新型";
        }
        $info['id_card'] = json_decode($info['id_card']);
        $info['app_name'] = $app['name'];
        return $info;
    }

    //审核 or 修改   type（1. 审核成功 2.审核失败 3。修改）
    public function check($type, $id, $data)
    {
        $pantent = $this->get($id);
        switch ($type) {
            case 1:
                $pantent['status'] = 1;
                $pantent['end_time'] = time();
                $res = $pantent->save();
                if ($res) {
                    $this->sendMessage($type, $id);
                    return true;

                } else {
                    return false;
                }
                break;
            case 2:
                $pantent['status'] = 2;
                $pantent['end_time'] = time();
                $pantent['reply'] = $data['reply'];
                $res = $pantent->save();
                if ($res) {

                    $this->sendMessage($type, $id);
                    return true;

                } else {
                    return false;
                }
                break;
            case 3:
                isset($data['id']);
                $re = $this->_checkData($data['type'], $data);
                if ($re == false) {
                    return false;
                }
                $data['create_time'] = time();
                $data['end_time'] = "";
                $res = $this->where('id', $id)->update($data);
                if ($res == 0 || $res) {
                    $this->sendMessage($type, $id);

                    return true;

                } else {
                    return false;
                }
                break;
        }
    }


    //审核三种情况的推送（1。审核成功推用户  2。审核失败推用户  3。修改成功推运营     ）
    public function sendMessage($type, $id)
    {
        $pantent = $this->get($id);
        $service = new Service();
        switch ($type) {
            case 1:
                $message = [
                    "title" => "专利申请提示",
                    "description" => "您的发明专利申请园区已确认，请您携带相关材料前往希垦科技园A幢2楼园区知识产权服务中心办理，点击查看详情",
                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetailCompany/appid/21/can_check/no/type/' . $pantent['type'] . '/id/' . $pantent['id']
                ];
                //审核成功推用户
                $service->commonSend(4, $message, $pantent['create_user'], 21);

                break;
            case 2:
                $message = [
                    "title" => "专利申请提示",
                    "description" => "您的发明专利申请园区审核失败，请您核对信息后重新提交，",
                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetailCompany/appid/21/can_check/no/type/' . $pantent['type'] . '/id/' . $pantent['id']
                ];
                //点击查看详情
                if(!empty($pantent['reply'])){
                    $message['description'] = $message['description']."备注：".$pantent['reply']."点击查看详情";
                }

                //审核失败推用户
                $service->commonSend(4, $message, $pantent['create_user'], 21);
                break;
            case 3:


                $message = [
                    "title" => "专利申请提示",
                    "description" => "您有一条新的专利申请服务待处理，点击查看详情",
                    "url" => 'https://' . $_SERVER['HTTP_HOST'] .'/index/service/historyDetail/appid/21/can_check/no/id/' . $pantent['id']
                ];
                //推送给运营
                $service->commonSend(1, $message, '', 21);
                break;
        }


    }


}