<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/15
 * Time: 上午9:44
 */
namespace app\admin\controller;

use app\common\model\ParkCompany;
use app\common\model\WechatDepartment as WechatDepartmentModel;
use app\common\model\ParkProduct;

class Company extends Admin
{
    /*企业详细列表*/
    public function index (){
        $company_type=input('company_type')==null?-1:input('company_type');
        //echo  $company_type;
        //dump($type);
       // echo $type[0];
        $search = input('search');
        $parkid  =session("user_auth")['park_id'];
        $type = config('company_type');
        $map = ['park_id'=>$parkid];
        if (!empty($search)) {
            $map['name'] = ['like', "%$search%"];
        }
        if ($company_type!=-1) {
            $map['type'] =input('company_type');
        }
        $companyList = ParkCompany::where($map)->order('id  asc')->paginate(12,false,['query' => request()->param()]);

        foreach ($companyList as $k=>$v){
            $v['present'] = mb_substr(strip_tags($v['present']),0,30);
            if ($v['type'] >= 0){
                $v['type'] = $type[$v['type']];
            }else{
                $v['type'] = '暂未分类';
            }
        }
        $this->assign('checkType',$company_type);
        $this->assign('list',$companyList);

        return  $this->fetch();
    }
    /*企业详情展示*/
    public  function add(){
        $id = input('id');
        $companyInfo = ParkCompany::get($id);
        $is_https= substr_count($companyInfo['site'], 'https');
        $is_http= substr_count($companyInfo['site'], 'http');
        $a ='http';
        if($is_https==1){
            $companyInfo['site']= str_replace("https://","",$companyInfo['site']);
            $a ='https';
        }
        if($is_http==1){
            $companyInfo['site']= str_replace("http://","",$companyInfo['site']);
        }

        $this->assign('protocol',$a);
        $this->assign('companyInfo',$companyInfo);

        return $this->fetch();
    }
    /*同步企业信息*/
    public function getCompany(){
        $deleteId=[];
        $parkid =session("user_auth")['park_id'];
        $parkCompany =new ParkCompany();
        $companyList=WechatDepartmentModel::where(['parentid'=>4])->select();
        foreach ($companyList as $k=>$v){
            $data=[
                'id'=>$v['id'],
              'name'=>$v['name'],
              'park_id'=>$parkid,
              'company_id'=>$v['id'],
            ];
            $number[$k]=$v['id'];
            $isUpdate = false;
            if (ParkCompany::get($data['id'])) {
                $res=$parkCompany->where('id',$data['id'])->update($data);

            }else{
                $res=$parkCompany->data($data,true)->isUpdate($isUpdate)->save();
            }
        }
            $parkNumber=ParkCompany::where(['park_id'=>$parkid])->select();
            foreach($parkNumber as $k=>$v){
                $companyNumber[$k]=$v['id'];
            }
            foreach ($companyNumber as $v){
                if (!in_array(intval($v), $number)){
                    $deleteId[] =$v;
                }
            }
            foreach($deleteId as $v){
                ParkCompany::where(['id'=> $v])->delete();
            }

            return $this->success('同步成功');
    }

    /*获取企业的服务或产品*/
    public function getCompanyserver(){
        $id=input("id");
        $res =ParkProduct::get($id);
        return  $res;
    }
    /*修改企业的产品或服务*/
    public function updateCompany(){
        $id=input("id");
        //return  $_POST;
        $parkProduct=new ParkProduct();
        $res=$parkProduct->where(['id'=>$id])->update(input('post.'));
        if ($res){

            return $this->success("修改成功");
        }else{

            return $this->error("修改失败");
        }
    }
    /*园区产品或服务的添加*/
    public function edit(){
        $parkcompany =new ParkProduct();
        unset($_POST['file']);
        $_POST['create_time']=time();
        //return  $_POST;
        $res=$parkcompany->validate(true)->save($_POST);
        if ($res){

            $this->success("添加成功");
        }else{

            $this->error($parkcompany->getError());
        }
    }
    /*修改企业信息*/
    public function changeinfo(){
        $id=input("id");
        if(empty(input('img'))){
            $_POST['img'] =$_POST['images'];
        }
        unset($_POST['images']);
        $res=ParkCompany::where(['id'=>$id])->update($_POST);
        if ($res){

            return $this->success("修改成功",'company/index');
        }else{

            return $this->error("修改失败");
        }
    }
    /*产品服务列表*/
    public function product(){
        $id = input('id');
        $type = input('type');
        $list = ParkProduct::where(['company_id'=>$id,'status'=>0,'type'=>$type])
                            ->order('create_time desc')
                            ->paginate(12,false,['query' => request()->param()]);
        $this->assign('list',$list);
        $this->assign('type',$type);
        $this->assign('companyId',$id);
        return $this->fetch();
    }


    /*删除产品或服务*/
    public function moveToTrash() {
        $ids = input('ids/a');
        $result = ParkProduct::where('id', 'in', $ids)->update(['status' => -1]);

        if($result) {

            return $this->success('删除成功');

        } else {

            return $this->error('删除失败');
        }
    }

    /**
     * 企业信息导入
     * @return mixed
     */
    public function import(){

        return $this->fetch();

    }
    public function importIn(){
        $company_type = config("company_type");
        $company_size_type = config("company_size_type");
        $file_name = input('name');
        $park_company_model = new ParkCompany();
        /*设置上传路径*/
        $savePath = ROOT_PATH . '/public/';
        $inputFileType = \PHPExcel_IOFactory::identify($savePath . $file_name);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);//创建读取类
        $objReader->setReadDataOnly(true);//设置只读取数据
        $objPHPExcel = $objReader->load($savePath . $file_name);//读取数据；
        $cellName = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];
        $currSheet = $objPHPExcel->getSheet();//获取当前工作表
        $columnH = $currSheet->getHighestColumn();//获取最大列标识数
        $columnCnt = array_search($columnH, $cellName);//获取总列数
        $rowCnt = $currSheet->getHighestRow();   //获取总行数
        $data = [];
        $alert_data = "";
        for ($row = 2; $row <= $rowCnt; $row++) {
            for ($colum = 0; $colum <= $columnCnt; $colum++) {
                $cellId = $cellName[$colum] . $row;
                $cellValue = $currSheet->getCell($cellId)->getValue();
                $data[$row][$colum] = $cellValue;
            }
        }
        foreach($data as $key => $value){
            $result = $this->checkCompanyIsExist($value[0]);
            if ($result){
                $update_data = [
                    'type' => array_keys($company_type,$value[1])[0],
                    'size_type' => array_keys($company_size_type,$value[2])[0],
                    'mobile' => $value[3],
                    'wechat_number' => $value[4],
                    'site' => $value[5]
                ];
                $park_company_model->where(['id' => $result])->update($update_data);
            }else{
                $alert_data .= $value[0]."\n";
            }
        }

        return $this->success('导入成功','',$alert_data);
    }

    /**
     * 检测企业是否存在
     * @param $name
     * @return bool
     */
    public function checkCompanyIsExist($name){
        $park_company_model = new ParkCompany();
        $res = $park_company_model->where(['name' => $name])->find();
        if ($res){

            return $res['id'];
        }else{

            return false;
        }

    }

}