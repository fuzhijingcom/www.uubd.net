<?php

/**
 * 幻灯片管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Think\Controller\MapiController;

class SlideController extends MapiController {

    /**
     * 获取幻灯片信息列表
     */
    public function get_list(){
        switch($this->_method){
            case "get":
                $infolist = D("Slide")->GetAll("`isshow`=1",array("list"=>"DESC"));
                //$videolist = array();
                //$imagelist = array();
                foreach ($infolist as $key => $info){
                    $pathinfo = pathinfo($info['imagepath']);
                    $infolist[$key]['url'] = SITE_ATTACHMENT_URL.$info['imagepath'];
                    if(in_array($pathinfo['extension'],array("wav","mp4"))){
                        //$videolist[] = $info;
                        $infolist[$key]['type'] = "video";
                    }else{
                        $infolist[$key]['type'] = "image";
                    }
                }
                $this->response(array("obj" => array(),"list" => $infolist, "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
                break;
            case "post":
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }
}