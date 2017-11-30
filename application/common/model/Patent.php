<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/29
 * Time: 上午10:08
 */

namespace app\common\model;


use org\PhpZip;
use think\Model;
use app\index\controller\Service;
use PhpOffice\PhpWord\PhpWord;

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

    public function getPatentbyParkid()
    {
        $parkid = session("user_auth")['park_id'];
        $map = [
            'park_id' => $parkid,
            'status' => ['neq', -1],
        ];
        $result = $this->where($map)->order('status asc')->paginate(12, false, ['query' => request()->param()]);
        //1 发明型专利。2 实用型专利。3外观设计
        int_to_string($result, $map = array('status' => array(1 => '审核成功', 0 => '未审核', 2 => '审核失败'), 'type' => array(1 => '发明型专利', 2 => '实用型专利', 3 => '外观设计')));
        return $result;
    }

    public static function getNumforUndone()
    {
        $parkid = session('user_auth')['park_id'];
        $map = ['park_id' => $parkid, 'status' => 0];
        $num = Patent::where($map)->count();
        return $num;
    }


    public function getDetailbyId($id)
    {

        $info = $this->find($id);
        $info['id_card'] = json_decode($info['id_card']);
        if ($info['type'] == 3) {
            $info['product_img'] = json_decode($info['product_img']);
        }
        $type_text = array(1 => '发明型专利', 2 => '实用型专利', 3 => '外观设计');
        $status_text = array(1 => '审核成功', 0 => '未审核', 2 => '审核失败');
        $info['type_text'] = $type_text[$info['type']];
        $info['status_text'] = $status_text[$info['status']];
        $info['img_text'] = array(0 => "俯视图", 1 => "仰视图", 2 => "立体图", 3 => "主视图", 4 => "后视图", 5 => "左视图", 6 => "右视图");
        return $info;


    }


    public function check($data)
    {
        if (empty($data)) {
            return false;
        }
        $info = $this->find($data['id']);
        $info['status'] = $data['type'];
        $info['end_time'] = time();

        if ($data['type'] == 2) {
            if (empty($data['reply'])) {
                return false;
            } else {
                $info['reply'] = $data['reply'];
            }
        }
        $re = $info->save();
        if ($re) {
            //推送
            $this->sendMessage($data['type'], $data['id']);
            return true;
        } else {

            return false;
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
                if (!empty($pantent['reply'])) {
                    $message['description'] = $message['description'] . "备注：" . $pantent['reply'] . "点击查看详情";
                }

                //审核失败推用户
                $service->commonSend(4, $message, $pantent['create_user'], 21);
                break;
            case 3:


                $message = [
                    "title" => "专利申请提示",
                    "description" => "您有一条新的专利申请服务待处理，点击查看详情",
                    "url" => 'https://' . $_SERVER['HTTP_HOST'] . '/index/service/historyDetail/appid/21/can_check/no/id/' . $pantent['id']
                ];
                //推送给运营
                $service->commonSend(1, $message, '', 21);
                break;
        }
    }
    //压缩导出
    public function out($id)
    {
        $list = $this->find($id);
        $type_text = array(1 => '发明专利', 2 => '实用新型', 3 => '外观设计');
        $list['type_text'] = $type_text[$list['type']];

        $personBasePath = "static/template/";
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($personBasePath . '企业专利申请基本信息确认表模版.docx');
        /* $properties = $phpword->getDocumentProperties();
         $properties->setTitle($file);*/
        $templateProcessor->setValue('patent_name', $list['patent_name']);
        $templateProcessor->setValue('type_text', $list['type_text']);
        $templateProcessor->setValue('inventor', $list['inventor']);
        $templateProcessor->setValue('applicant', $list['applicant']);
        $templateProcessor->setValue('contact', $list['contact']);
        $templateProcessor->setValue('contact_address', $list['contact_address']);
        $templateProcessor->setValue('contact_number', $list['contact_number']);
        $templateProcessor->setValue('explanation', $list['explanation']);
        $tmpFileName = md5(time() . 'Heier');
        //$templateProcessor->saveAs($tmpFileName . '.docx');
        // $fileName=iconv("UTF-8","GB2312",$personBasePath."莫干山镇民宿办证申请表.docx");
        $fileName = $personBasePath . "企业专利申请基本信息确认表.docx";
        $templateProcessor->saveAs($fileName);
        $zip = new \ZipArchive();
        $down_name = date("Ymd", time()) ." ". $list['patent_name'].'.zip';
        $zipfile = $personBasePath . $down_name;
        $res = $zip->open($zipfile, \ZipArchive::CREATE);
        $list['id_card'] = json_decode($list['id_card']);
        $list['product_img'] = !empty($list['product_img']) ? json_decode($list['product_img']) : array();

        copy('http://' . $_SERVER['HTTP_HOST'] . $list['id_card'][0], $personBasePath . "身份证正面.jpg");
        copy('http://' . $_SERVER['HTTP_HOST'] . $list['id_card'][1], $personBasePath . "身份证背面.jpg");

        if ($list['type'] == 3) {
            //俯视图，仰视图，立体图，主视图，后视图，左视图，右视图）
            copy('http://' . $_SERVER['HTTP_HOST'] . $list['product_img'][0], $personBasePath . "产品_俯视图.jpg");
            copy('http://' . $_SERVER['HTTP_HOST'] . $list['product_img'][1], $personBasePath . "产品_仰视图.jpg");
            copy('http://' . $_SERVER['HTTP_HOST'] . $list['product_img'][2], $personBasePath . "产品_立体图.jpg");
            copy('http://' . $_SERVER['HTTP_HOST'] . $list['product_img'][3], $personBasePath . "产品_主视图.jpg");
            copy('http://' . $_SERVER['HTTP_HOST'] . $list['product_img'][4], $personBasePath . "产品_后视图.jpg");
            copy('http://' . $_SERVER['HTTP_HOST'] . $list['product_img'][5], $personBasePath . "产品_左视图.jpg");
            copy('http://' . $_SERVER['HTTP_HOST'] . $list['product_img'][6], $personBasePath . "产品_右视图.jpg");
            $images = [
                "身份证正面.jpg" => $personBasePath . "身份证正面.jpg",
                "身份证背面.jpg" => $personBasePath . "身份证背面.jpg",
                "产品_俯视图.jpg" => $personBasePath . "产品_俯视图.jpg",
                "产品_仰视图.jpg" => $personBasePath . "产品_仰视图.jpg",
                "产品_立体图.jpg" => $personBasePath . "产品_立体图.jpg",
                "产品_主视图.jpg" => $personBasePath . "产品_主视图.jpg",
                "产品_后视图.jpg" => $personBasePath . "产品_后视图.jpg",
                "产品_左视图.jpg" => $personBasePath . "产品_左视图.jpg",
                "产品_右视图.jpg" => $personBasePath . "产品_右视图.jpg",
            ];
        } else {
            $images = [
                "身份证正面.jpg" => $personBasePath . "身份证正面.jpg",
                "身份证背面.jpg" => $personBasePath . "身份证背面.jpg",
            ];

        }

        if ($res == true) {
            $zip->addFile($fileName);
            $zip->renameName($fileName, "企业专利申请基本信息确认表.docx");
           foreach ($images as $k =>$v){
               $zip->addFile($v);
               $zip->renameName($v, $k);
           }
            $zip->close();
        }
        header('Content-Description: File Transfer');
        Header("content-type:application/x-zip-compressed");
        header('Content-Disposition: attachment; filename=' . basename($zipfile));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipfile));
        ob_clean();   //清空但不关闭输出缓存
        flush();
        @readfile($zipfile);
        @unlink($zipfile);//删除打包的临时zip文件。文件会在用户下载完成后被删除*/
    }


}