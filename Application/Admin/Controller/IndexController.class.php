<?php
namespace Admin\Controller;
use Think\Controller\MasterController;
use Admin\Common\ACPopedom;

class IndexController extends MasterController {

    /**
     * 后台管理首页
     */
    public function index(){
        $this->assign("SystemName",SystemName);
        $this->display();
    }

    /**
     * 左边菜单
     */
    public function menu(){
        $popedom = ACPopedom::getPopedomGroup ();
        $this->assign ( 'menulist', $popedom );
        $this->display();
    }

    /**
     * 顶部
     */
    public function top(){
        $this->assign("Master",ACPopedom::getMaster());
        $this->display();
    }

    /**
     * 右侧
     */
    public function info(){
        $this->display();
    }
}