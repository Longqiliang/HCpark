<?php
/**
 * Created by PhpStorm.
 * User: Scy
 * QQ: 329880211@qq.com
 * Date: 2016/12/29
 * Time: 9:43
 */

namespace app\index\controller;



use app\common\model\Picture;

class CardPicture extends Base
{
    public function deal()
    {
        set_time_limit(0);
        ini_set('gd.jpeg_ignore_warning', 1);
        ini_set("memory_limit","-1");
        $pic_model = new Picture();
        $list = $pic_model->getCardPath();
        $result['msg'] = "处理完毕";
        if (empty($list)) {

            return sendSuccessmessage($result);
        } else {
            foreach($list as $value) {
                if (isset($value['path']) && !empty($value['path']) && file_exists(ROOT_PATH.'public'.$value['path']))
                {
                    $str = explode(".", $value['path']);
                    if (isset($str[1])) {
                        $new_path = ROOT_PATH.'public'.$str[0]."_thumb.".$str[1];
                        if (!file_exists($new_path)) {
                            reduce_pic(ROOT_PATH.'public'.$value['path'], $new_path);
                        }
                    }
                }
            }
        }

        return sendSuccessmessage($result);
    }
}