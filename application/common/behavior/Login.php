<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼
 * Date: 16/8/25
 * Time: 上午10:08
 */

namespace app\common\behavior;

use app\admin\model\ActionLog;

class Login
{
    public function memberLogin(&$params) {
        $remark = '用户:'.$params['nickname'].'登入后台。';
        $this->saveLog($params['id'], 'Member', $remark, '管理员登入');
    }

    public function anchorLogin(&$params) {
        $remark = '用户:'.$params['nickname'].'登入后台。';
        $this->saveLog($params['id'], 'Anchor', $remark, '主播登入');
    }

    public function companyLogin(&$params) {
        $remark = '用户:'.$params['nickname'].'登入后台。';
        $this->saveLog($params['id'], 'Company', $remark, '厂商登入');
    }

    private function saveLog($userId, $model, $remark, $actionName) {
        $data = [
            'user_id' => $userId,
            'model' => $model,
            'remark' => $remark,
            'action_name' => $actionName
        ];

        ActionLog::create($data);
    }

}