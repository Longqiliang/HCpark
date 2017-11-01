<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/9/2
 * Time: 上午10:24
 */
namespace app\common\model;

use think\Model;

class ParkRoom extends Model{

    /**
     * 重写楼房信息表
     */
    public function companyRoom(){
        $setArr = [
            '3' => ['A','B'],
            '80' => ['A','B','C','D']
        ];
        $newData = [];
        $parkRoom = new ParkRoom();
        $park = new Park();
        foreach($setArr as $k=>$v){
            $number = $k;
            $parkInfo = $park->where(['id'=>$number])->find();
            $newData[$parkInfo['name']] = [];
            foreach($v as $k1=>$v1){
                $element = $v1;
                $newArr = [];
                $floor = [] ;
                $map = ['park_id'=>$number,'build_block' => $element,'del' => 0 ];
                //获取楼层信息
                $list = $parkRoom->where($map)->distinct(true)->field('floor')->order('floor desc')->select();
                foreach ($list as $k => $v) {
                    $floor[$k] = $v['floor'];
                }
                //每层楼房间数目
                foreach ($floor as $k => $v) {
                    $roomList = $parkRoom->where(['floor' => $v, 'build_block' => $element, 'del' => 0 ,'park_id' => $number])->order("room asc")->select();
                    //判断房间是否出租
                    foreach ($roomList as $k1 => $v1) {
                        $res = ParkRent::where(['room_id' => $v1['id'], 'manage' => 0, 'status' => 0 ])->find();
                        if (!$res) {
                            $status = 0;
                            $roomsId = 0;
                        } else {
                            $rent =PeopleRent::where('rent_id',$res['id'])->select();
                            if($rent){
                                $status = 2;
                            }else{
                                $status = 1;
                            }
                            $roomsId = $res['room_id'];
                        }
                        $roomArray[$k][$k1] = ['room' => $v1['room'], 'empty' => $status, 'id' => $v1['company_id'], 'room_id' => $roomsId];
                        $roomArray[$k] = array_slice($roomArray[$k],0,$k1+1);
                    }
                }
                foreach ($floor as $k => $v) {
                    $newArr[$k]['floor'] = $v;
                    $newArr[$k]['rooms'] = $roomArray[$k];
                }
                $newData[$parkInfo['name']][$element.'幢'] = $newArr;
            }
        }
        return $newData;
    }




}