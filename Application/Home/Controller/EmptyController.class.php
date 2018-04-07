<?php

namespace Home\Controller;
use Common\Controller\AppframeController;

class EmptyController extends AppframeController{
    public function index(){
        $this->error('页面不存在！', C('NGINX_ROOT') . U('Home/Index/login'));
    }
}