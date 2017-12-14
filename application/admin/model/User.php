<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/2
 * Time: 16:44
 */
namespace app\admin\model;

use think\Model;

/**
 * 前端用户模型
 * Class User
 * @package app\admin\model
 */
class User extends Model
{
    protected  $type = [
        'username'=>'string',
        'nickname'=>'string',
        'phone'=>'string',
        'email'=>'string'
    ];
    protected $autoWriteTimestamp = 'datetime';
    /**
     * 更新用户数据
     *
     * @param $data
     * @return mixed
     */
    public function updateUserData($data)
    {
        $result['email'] = $data['email'];
        $res = $this->isUpdate(true)->where('id', $data['id'])->update($result);
        return $res;
    }
    /**
     * 获取用户数据
     *
     * @param $username
     * @param $nickname
     * @param $phone
     * @param $email
     * @param string $status
     * @return \think\paginator\Collection
     */
    public function getUserByData($username, $nickname, $phone, $email, $status = "")
    {
        $map = "";
        if (!empty($username)) {
            $map['username'] = array('eq', $username);
        }
        if (!empty($nickname)) {
            $map['nickname'] = array('eq', $nickname);
        }
        if (!empty($phone)) {
            $map['phone'] = array('eq', $phone);
        }
        if (!empty($email)) {
            $map['email'] = array('eq', $email);
        }
        if (!empty($status)) {
            $map['status'] = array('eq', $status);
        } else {
            $map['status'] = array('neq', -1);
        }
        $data = $this->where($map)->paginate(10);
        return $data;
    }
    /**
     * 新增用户
     * @param $username
     * @param $password
     * @param nickname
     * @param email
     * @param phone
     * @return false|int
     */
    public function addNewUser($username, $password, $email, $phone)
    {
        $data['username'] = $username;
        $data['password'] = $this->_getPassword($password);
        $data['email']    = $email;
        $data['mobile']    = $phone;
        $res = $this->validate('user.insert')->isUpdate(false)->save($data);
        if ($res) {
            //新增成功返回id
            return $this->id;
        } else {
            return false;
        }
    }
    /**
     * 更新用户
     * @param $id
     * @param $status
     * @param $nickname
     * @param $password
     * @param $remark
     * @return boolean
     */
    public function updateUser($id, $status, $nickname, $password, $remark)
    {
        $update_data = array();
        if (!empty($password)) {
            $update_data['password'] = $this->_getPassword($password);
        }
        if (!empty($nickname)) {
            $update_data['nickname'] = $nickname;
        }
        if (!empty($status)) {
            $update_data['status'] = $status;
        }
        if (!empty($remark)) {
            $update_data['remark'] = $remark;
        }
        return $this->validate('user.updateuser')->isUpdate(true)->where('id', $id)->update($update_data);
    }
    /**
     * 启用用户
     *
     * @param $id
     * @return int|string
     */
    public function accessUser($id)
    {
        $data['status'] = 1;
        return $this->where('id', $id)->update($data);
    }
    /**
     * 禁用用户
     *
     * @param $id
     * @return int|string
     */
    public function forbidUser($id)
    {
        $data['status'] = 0;
        return $this->where('id', $id)->update($data);
    }
    /**
     * 删除用户
     *
     * @param $id
     * @return int|string
     */
    public function deleteUser($id)
    {
        $data['status'] = -1;
        return $this->isUpdate(true)->where('id', $id)->update($data);
    }
    /**
     * 设置密码
     * @param $uid
     * @param $password
     * @return mixed
     */
    public function setPassword($uid, $password)
    {
        $data['password'] = $this->_getPassword($password);
        return $this->validate('user.updatepassword')->isUpdate(false)->where('id', $uid)->save($data);
    }
    /**
     * 状态字段
     *
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = array('-1'=>'删除', '0'=>'禁用', '启用');
        if (isset($status[$data['status']])) {
            return $status[$data['status']];
        } else {
            Log::error('status 不存在 data:'.json_encode($data));
            return "";
        }
    }
    /**
     * 关联用户
     * @return \think\model\Relation
     */
    public function profile()
    {
        return $this->belongsTo('UserProfile', 'id', 'uid');
    }
    /**
     * 获取密码
     * @param $password
     * @return string
     */
    public function getPassword($password)
    {
        $password = sha1("ztly".$password);
        return $password;
    }
}