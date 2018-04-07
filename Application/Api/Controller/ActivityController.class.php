<?php

/**
 * 接龙活动管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Think\Controller\MapiController;

class ActivityController extends MapiController {

    /**
     * 获取活动信息列表
     */
    public function get_info_list(){
        switch($this->_method){
            case "get":
                $infolist = D("ActivityGoods")->GetJoinAll("ag",array('LEFT JOIN __GOODS__ AS g ON g.goodsid = ag.goods_id'),"ag.act_id=".intval($_GET['act_id']),array("ag.id"=>"DESC"),"ag.sale_price,ag.buy_limit,ag.goods_name,ag.goods_id,g.description");
                if(empty($infolist)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "信息不存在"), "json", 200);
                }
                foreach ($infolist as $key => $info){
                    //得到商品图片
                    $image = D("GoodsAttachment")->GetInfo("`goodsid`=".$info['goods_id']." AND is_default=1");
                    $infolist[$key]['thumb_url'] = SITE_ATTACHMENT_URL.$image['picpath'];
                    $infolist[$key]['goods_number'] = 0;
                    //得到当前价格
                    $sale_price = json_decode($info['sale_price'],true);
                    $level = ACPopedom::getLevelID();
                    if(!$level){
                        $first = array_shift($sale_price);
                        $infolist[$key]['price'] = $first;
                    }else{
                        $infolist[$key]['price'] = $sale_price[$level];
                    }
                    unset($infolist[$key]['sale_price']);
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

    /**
     * 获取参与人数列表
     */
    public function get_member_list(){
        switch($this->_method){
            case "get":
                $infolist = D("ActivityMember")->GetJoinAll("am",array('LEFT JOIN __USER__ AS u ON u.userid = am.user_id'),"am.act_id=".intval($_GET['act_id']),array("am.posttime"=>"DESC"),"am.posttime,u.username,u.nickname,u.avator");
                if(empty($infolist)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "信息不存在"), "json", 200);
                }
                $total = 0;
                foreach ($infolist as $key => $info){
                    $total += 1;
                    $infolist[$key]['user_name'] = $info['nickname'] ? $info['nickname'] : $info['username'];
                    $infolist[$key]['join_date'] = date("Y.m.d H:i:s",$info['posttime']);
                    unset($infolist[$key]['nickname']);
                    unset($infolist[$key]['username']);
                    unset($infolist[$key]['posttime']);
                }
                $infolist = array_slice($infolist,0,10);
                $this->response(array("obj" => array("total"=>$total),"list" => $infolist, "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
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
     * 获取活动详情
     */
    public function get_info(){
        switch($this->_method){
            case "get":
                $info = D("Activity")->GetInfo("`act_id`=".intval($_GET['act_id']),"act_name,thumb,act_desc");
                if (empty($info)) {
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "信息不存在"), "json", 200);
                }
                $info['thumb_url'] = SITE_ATTACHMENT_URL.$info['thumb'];
                /*$info['pick_date'] = date("m月d日 H点",$info['pick_time']);
                $info['pick_address'] = $info['province'].$info['city'].$info['district'].$info['pick_address'];*/
                //unset($info['thumb'],$info['province'],$info['city'],$info['district'],$info['pick_time']);
                $this->response(array("obj" => $info,"list" => array(), "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
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