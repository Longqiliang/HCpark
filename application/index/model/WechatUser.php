<?php
namespace app\index\model;

use think\Model;

class WechatUser extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
    ];

    public function checkUserExist($userId) {
        return $this->where('userId', $userId)->find();
    }

    public function departmentName() {

        return $this->hasOne("WechatDepartment","id","department");
    }
}