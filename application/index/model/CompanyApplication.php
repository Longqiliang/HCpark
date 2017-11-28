<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/15
 * Time: 上午11:26
 */

namespace app\index\model;


use think\Model;

class CompanyApplication extends  Model
{

  public  function getCompanyType($appid){
      $companyapp = $this->where('app_id',$appid)->find();
      return $companyapp['type'];
  }





}