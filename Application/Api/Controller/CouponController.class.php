<?php

/**
 * 优惠券管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Common\Validation;
use Think\Controller\MapiController;

class CouponController extends MapiController {

    /**
     * 获取优惠券列表
     */
    public function get_list(){
        switch($this->_method){
            case "get":
                $order_id = intval($_GET['order_id']);
                if($order_id){
                    //得到订单信息
                    $order_info = D("Order")->GetInfo("`order_id`=".$order_id." AND `buyer_id`=".ACPopedom::getID()." AND `exchange_type`=0");
                    if($order_info){
                        $order_charge = sprintf("%.2f",($order_info['total_amount']-$order_info['coupon_charge']-$order_info['packet_charge']-$order_info['member_discount']-$order_info['discount']));
                        $infolist = D("CouponRecord")->RunSql("SELECT * FROM ".C("DB_MALL.db_prefix")."coupon_record WHERE `member_id`=".ACPopedom::getID()." AND `valid`>".time()." AND `is_used`=0 AND (`sale_type`=0 OR (`sale_type`=1 AND `total_charge`>".$order_charge.")) ORDER BY posttime DESC");
                        //$infolist = D("CouponRecord")->GetAll("`member_id`=".ACPopedom::getID()." AND `valid`>".time()." AND `is_used`=0 AND (`sale_type`=0 OR (`sale_type`=1 AND `total_charge`>".$order_charge."))",array("posttime"=>"DESC"));
                    }else{
                        $infolist = array();
                    }
                }else{
                    $infolist = D("CouponRecord")->GetAll("`member_id`=".ACPopedom::getID()." AND `valid`>".time()." AND `is_used`=0",array("posttime"=>"DESC"));
                }
                foreach ($infolist as $key => $info){
                    if(intval($info['sale_type'])==0){
                        $infolist[$key]['coupon_type'] = "代金券";
                    }else{
                        $infolist[$key]['coupon_type'] = "满减券";
                    }
                    $infolist[$key]['begin_date'] = date("Y.m",$info['posttime']);
                    $infolist[$key]['end_date'] = date("Y.m",$info['valid']);
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
     * 领取优惠券
     */
    public function pickup(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                $coupon_id = intval($_GET['coupon_id']);
                $mobile = trim($_POST['mobile']);
                //得到优惠券信息
                $coupon_info = D("Coupon")->GetInfo("`coupon_id`=".$coupon_id);
                if(empty($coupon_info)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "优惠券信息不存在"), "json", 200);
                }
                if (!Validation::IsMobileNumber($mobile)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "请输入正确的手机号码"), "json", 200);
                }
                //判断手机号码是否存在
                $info = D("User")->GetInfo("`username`='".$mobile."'");
                if(empty($info)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "该手机号码暂未绑定，无法领取"), "json", 200);
                }
                $data['coupon_sn'] = $coupon_info['coupon_sn'];
                $data['userid'] = 0;
                $data['coupon_id'] = $coupon_info['coupon_id'];
                $data['coupon_title'] = $coupon_info['title'];
                $data['sale_type'] = $coupon_info['sale_type'];
                $data['total_charge'] = $coupon_info['total_charge'];
                $data['coupon_charge'] = $coupon_info['coupon_charge'];
                $data['member_id'] = ACPopedom::getID();
                $data['valid'] = strtotime("+".$coupon_info['valid']." month",$coupon_info['posttime'])+86400;
                $data['posttime'] = time();
                $data['is_used'] = 0;
                $data['usetime'] = 0;
                $rs = D("CouponRecord")->Add($data);
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "领取失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "领取成功"), "json", 200);
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }
}