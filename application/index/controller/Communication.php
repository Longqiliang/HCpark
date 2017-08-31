<?php
/**
 * Created by PhpStorm.
 * User: ztos
 * Date: 2017/8/31
 * Time: 上午9:22
 */

namespace app\index\controller;




class Communication extends Base
{
   /*首页*/
    public function index()
    {
        return $this->fetch();
    }

    /*个人*/
    public  function  personal(){

        return $this->fetch();
    }

    /*申请加入*/
    public  function join(){

        return $this->fetch();
    }

    /*帖子列表*/
    public  function  postsList(){

        return $this->fetch();
    }

    /*帖子详情*/
    public  function postDetails(){

        return $this->fetch();
    }

    /*写帖子*/
    public  function writePost(){

        return $this->fetch();
    }

    /*我的申请*/
    public  function myApplication(){

        return $this->fetch();
    }
    /*我的审核*/
    public  function myCheck(){

        return $this->fetch();
    }
    /*我的发布*/
    public  function myRelease(){

        return $this->fetch();
    }

    /*我的评论*/
    public  function myComment(){

        return $this->fetch();
    }
}