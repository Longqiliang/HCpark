<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼 <183700295@qq.com>
 * Date: 16/5/11
 * Time: 15:43
 */
namespace app\admin\model;

use think\Model;

class AuthGroupAccess extends Model
{
    public function member() {
        return $this->hasOne('Member', 'id', 'uid');
    }

    public function group() {
        return $this->hasOne('AuthGroup', 'id', 'group_id');
    }

}