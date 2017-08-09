<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼 <183700295@qq.com>
 * Date: 16/3/22
 * Time: 09:55
 */

namespace app\admin\controller;

use think\Config as ConfigLib;
use app\admin\model\Config as ConfigModel;
use think\Url;

class Config extends Admin {
    /**
     * 配置管理
     */
    public function index(){
        /* 查询条件初始化 */
        $map = array('status' => 1);
        if(input('group')) {
            $map['group'] = input('group');
        }
        if(input('name')){
            $map['name'] = array('like', '%'.(string)input('name').'%');
        }
        $list = ConfigModel::where($map)->paginate();

        // 记录当前列表页的cookie
        cookie('__forward__',$_SERVER['REQUEST_URI']);

        $this->assign('group',ConfigLib::get('CONFIG_GROUP_LIST'));
        $this->assign('group_id', input('group',0));
        $this->assign('list', $list);

        return $this->fetch();
    }

    /**
     * 新增配置
     */
    public function add(){
        if(IS_POST){
            $ConfigModel = new ConfigModel();
            unset($_POST['id']);
            $ConfigModel->data($_POST);
            $result = $ConfigModel->validate(true)->save();

            if($result){
                cache('db_config_data', null);
                $this->success('新增成功', Url::build('index'));
            } else {
                $this->error($ConfigModel->getError());
            }
        } else {
            $this->assign('info', null);
            return $this->fetch('edit');
        }
    }

    /**
     * 编辑配置
     */
    public function edit(){
        if(IS_POST){
            $ConfigModel = new ConfigModel();
            $result = $ConfigModel->validate(true)->isUpdate(true)->save($_POST);

            if($result){
                cache('db_config_data',null);
                //记录行为
                //action_log('update_config','config',$data['id'],UID);
                $this->success('更新成功', cookie('__forward__'));
            } else {
                $this->error($ConfigModel->getError());
            }
        } else {
            $info = ConfigModel::get(input('id'));
            if(false === $info){
                $this->error('获取配置信息错误');
            }
            $this->assign('info', $info);
            return $this->fetch();
        }
    }

    /**
     * 批量保存配置
     */
    public function save($config){
        if($config && is_array($config)){
            $ConfigModel = new ConfigModel();
            foreach ($config as $name => $value) {
                $map = array('name' => $name);
                $ConfigModel->where($map)->setField('value', $value);
            }
        }
        cache('db_config_data', null);
        $this->success('保存成功！');
    }

    /**
     * 删除配置
     */
    public function del(){
        $id = array_unique((array)input('id/a', 0));
        if ( empty($id) ) {
           $this->error('请选择要操作的数据!');
        }

        if(ConfigModel::destroy($id)){
            cache('db_config_data',null);
            //记录行为
            //action_log('update_config','config',$id,UID);
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    // 获取某个标签的配置参数
    public function group() {
        $id = input('id', 1);
        $list = ConfigModel::all(function($query) use($id) {
            $query->where(['status'=>1, 'group'=>$id])->field('id,name,title,extra,value,remark,type')->order('sort');
        });
        $this->assign('list',$list);
        $this->assign('id',$id);

        return $this->fetch();
    }

    /**
     * 配置排序
     */
    public function sort(){
        if (IS_POST) {
            $ids = input('ids');
            $ids = explode(',', $ids);
            foreach ($ids as $key=>$value){
                $result = ConfigModel::where('id', $value)->update(['sort' => $key+1]);
            }
            if($result !== false){
                $this->success('排序成功！',cookie('__forward__'));
            }else{
                $this->error('排序失败！');
            }
        } else {
            $ids = input('ids');

            //获取排序的数据
            $map = array('status'=>array('gt',-1));
            if(!empty($ids)){
                $map['id'] = array('in',$ids);
            }elseif(input('group')){
                $map['group'] = input('group');
            }
            $list = ConfigModel::all(function($query) use($map) {
                $query->where($map)->field('id,title')->order('sort asc,id asc');
            });
            $this->assign('list', $list);

            return $this->fetch();
        }
    }
}