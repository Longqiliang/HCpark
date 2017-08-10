<?php
/**
 * Created by PhpStorm.
 * User: aion
 * Date: 2017/5/10
 * Time: 下午9:34
 */

namespace app\index\model;


use think\Model;

class Collect extends Model
{
    protected $insert = [
        'create_time' => NOW_TIME,
    ];

   /*
   $map=[
      'tb_news.type'=>1,
      'tb_collect.user_id'=>$user_id
   ]
   return newsinfo
   */
    public function  shownewslist($map){
        $collect = new  Collect();
        return $news = $collect->join("tb_news","tb_news.id = tb_collect.target_id " )->where($map)->field('tb_news.title,tb_collect.create_time,tb_news.front_cover,tb_news.views,tb_news.id')->order('tb_news.id' )->select();
    }

    public function news(){

        return $this->hasOne('News',"id","target_id")->field('title,views,front_cover');
    }

}