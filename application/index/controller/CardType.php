<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/14
 * Time: 1:52
 */

namespace app\index\controller;

use app\index\model\CardType as TypeModel;

class CardType extends Base
{
    public function getTypeList()
    {
        $type_model = new TypeModel();
        $data = $type_model->getTypeList();
        $result['data'] = $data;

        return sendSuccessmessage($result);
    }

    public function checkTypeIsExist($id)
    {
        $type_model = new TypeModel();
        
        return $type_model->getTypeById($id);
    }
}