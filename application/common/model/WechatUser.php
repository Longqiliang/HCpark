<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 2017/5/10
 * Time: 下午7:22
 */

namespace app\common\model;

use think\Model;

class WechatUser extends Model
{
    public function departmentName() {

        return $this->hasOne("WechatDepartment","id","department");
    }
}