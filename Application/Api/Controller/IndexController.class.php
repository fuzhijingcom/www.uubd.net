<?php

/**
 * 商家管理模块
 */
namespace Api\Controller;
use Think\Controller\MapiController;

class IndexController extends MapiController {

    /**
     * 获取广告
     */
    public function get_ad(){
        switch($this->_method){
            case "get":
                //获取首页广告
                $adinfo = D("Ad")->GetJoinInfo("`ad`",array('LEFT JOIN __AD_PLACE__ AS ap ON ap.placeid = ad.placeid'),"ap.label='ad_index_label' AND ad.isshow=1 AND ad.adtype=0 AND (ad.begintime=0 OR ad.begintime<=".time().") AND (ad.endtime=0 OR ad.endtime>".time().")","ad.picpath,ad.linkurl,ad.title,ad.description,ad.adid");
                if($adinfo) {
                    $adinfo['picurl'] = SITE_ATTACHMENT_URL . $adinfo['picpath'];
                    unset($adinfo['picpath']);
                }
                $this->response(array("obj" => $adinfo,"list" => array(), "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
                break;
            case "post":
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 获取接龙活动列表
     */
    public function get_activity_list(){
        switch($this->_method){
            case "get":
                /*$page = intval($_GET['page']) ? intval($_GET['page']) : 1;
                $rows = intval($_GET['number']) ? intval($_GET['number']) : 6;
                $infolist = D("Activity")->GetList("(`start_time`=0 OR `start_time`<=".time().") AND (`end_time`=0 OR `end_time`>".time().")",$page,$rows,array("list"=>"DESC"),"thumb,act_name,start_time,end_time,act_id");*/
                $infolist = D("Activity")->GetAll("(`start_time`=0 OR `start_time`<=".time().") AND (`end_time`=0 OR `end_time`>".time().")",array("list"=>"DESC"),"thumb,act_name,start_time,end_time,act_id");
                foreach ($infolist as $key => $info){
                    $infolist[$key]['start_date'] = date("m.d H:i",$info['start_time']);
                    $infolist[$key]['end_date'] = date("m.d H:i",$info['end_time']);
                    $infolist[$key]['thumb_url'] = SITE_ATTACHMENT_URL.$info['thumb'];
                    $infolist[$key]['url'] = SITE_API_URL."/activity/info/".$info['act_id'];
                    unset($infolist[$key]['start_time']);
                    unset($infolist[$key]['end_time']);
                    unset($infolist[$key]['thumb']);
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