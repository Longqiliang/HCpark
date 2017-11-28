<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/11/28
 * Time: 上午11:22
 */

namespace app\index\model;


use think\Model;

//专利
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

    //接口参数校验
    public function _checkData($type, $data)
    {
        if (empty($type) || empty($data)) {
            return false;
        } else {
            // 发明专利 实用新型
            if (isset($data['patent_name']) ||
                isset($data['inventor']) ||
                isset($data['applicant']) ||
                isset($data['contact']) ||
                isset($data['contact_address']) ||
                isset($data['contact_number']) ||
                isset($data['id_card'])
            ) {
                return false;
            }
            if (empty($data['patent_name']) ||
                empty($data['inventor']) ||
                empty($data['applicant']) ||
                empty($data['contact']) ||
                empty($data['contact_address']) ||
                empty($data['contact_number']) ||
                empty($data['id_card'])
            ) {
                return false;
            }
            //外观设计
            if ($type == 3) {
                if (isset($data['explanation']) ||
                    isset($data['product_img'])

                ) {
                    return false;
                }
                if (empty($data['explanation']) ||
                    empty($data['product_img'])
                ) {
                    return false;
                }
            }
            return true;
        }
    }

    public function patentHistory()
    {
        $userid = session('userId');
        $park_id = session('park_id');
        $patent = $this->where(['create_user' => $userid, 'park_id' => $park_id])->select();
        foreach ($patent as $k => $v) {
            $info[$k] = [
                'id' => $v['id'],
                'type' => $v['type'],
                'time' => date('Y-m-d', $v['create_time']),
                'status' => $v['status'],
                'name' => $v['patent_name']
            ];
        }
        return $info;

    }

    public function patentHistoryDetail($id, $appid)
    {

        $info = $this::get($id);
        $app = CompanyApplication::Where('app_id', $appid)->find();
        if ($info['type'] == 3) {
            $info['type_name'] = "外观设计";
            $info['product_img'] = json_decode($info['product_img']);
        } elseif ($info['type'] == 1) {

            $info['type_name'] = "发明专利";
        } elseif ($info['type'] == 2) {
            $info['type_name'] = "实用新型";
        }
        $info['id_card'] = json_decode($info['id_card']);
        $info['app_name'] = $app['name'];
        return $info;
    }


}