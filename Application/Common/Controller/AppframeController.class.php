<?php
namespace Common\Controller;
use Think\Controller;

class AppframeController extends Controller {
    function _initialize() {
        $this->assign("waitSecond", 3);
    }

    /**
     * 空操作
     */
    public function _empty() {
        $this->error('该页面不存在！');
        exit;
    }
}