<?php

/**
 * 支付管理模块
 */
namespace Api\Controller;
use Think\Controller\MapiController;
use Api\Common\ACPopedom;
use Tools\WxPayApi;
use Tools\WxPayNotifyCallBack;
use Tools\JsApiPay;
use Tools\WxPayData;
use Tools\WxPayUnifiedOrder;

class PayController extends MapiController {

    private $_JsApiPay = null;
    private $_WxPayUnifiedOrder = null;
    private $_config = array();

    public function __construct(){
        import("Vendor.WxPay.WxPayApi");
        import("Vendor.WxPay.WxPayNotifyCallBack");
        import("Vendor.WxPay.JsApiPay");
        import("Vendor.WxPay.WxPayUnifiedOrder");
        $this->_JsApiPay = new JsApiPay();
        $this->_WxPayUnifiedOrder = new WxPayUnifiedOrder();
        $this->_config = get_config_cache("system@config");
        if(empty($this->_config) || $this->_config && empty($this->_config['account_name'])){
            $this->_config['account_name'] = "平台币";
        }
        parent::__construct();
    }

    /**
     * 微信支付订单
     */
    public function wechat(){
        switch($this->_method){
            case "get":
                //得到订单信息
                $orderinfo = D("Order")->GetInfo("`order_id`=".intval($_GET['order_id'])." AND `pay_status`=0 AND `exchange_type`=0 AND `buyer_id`=".ACPopedom::getID());
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $rs = get_config_cache("system@config");
                if(intval($rs['order_invalid_time']) && time() - $orderinfo['posttime'] > intval($rs['order_invalid_time'])*3600){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "该订单已失效，无法完成支付"), "json", 200);
                }
                $goodslist = D("OrderGoods")->GetJoinAll("og",array("LEFT JOIN __GOODS__ AS g on g.goodsid =og.goods_id"),"og.`order_id`=".intval($_GET['order_id']),array("og.id"=>"ASC"),"g.title");
                if(empty($goodslist)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "该订单没有任何商品信息"), "json", 200);
                }
                if( count($goodslist)>1 ){
                    if( strlen($goodslist[0]['title'])>10 ){
                        $order_name = mb_substr($goodslist[0]['title'], 0, 10, 'utf8')."等商品";
                    }else{
                        $order_name = $goodslist[0]['title']."等商品";
                    }

                }else{
                    if( strlen($goodslist[0]['title'])>10 ){
                        $order_name = mb_substr($goodslist[0]['title'], 0, 10, 'utf8');
                    }else{
                        $order_name = $goodslist[0]['title'];
                    }
                }
                $order_name = str_replace(" ", "", $order_name);
                $order_charge = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));
                //②、统一下单
                $this->_WxPayUnifiedOrder->SetBody( '【微信支付】'.$order_name );
                $this->_WxPayUnifiedOrder->SetAttach( ACPopedom::getID() );
                $this->_WxPayUnifiedOrder->SetOut_trade_no(  $orderinfo['order_sn']."_".time() );
                $this->_WxPayUnifiedOrder->SetTotal_fee( intval($order_charge*100) );
                $this->_WxPayUnifiedOrder->SetTime_start( date("YmdHis") );
                $this->_WxPayUnifiedOrder->SetTime_expire(date("YmdHis", time() + 600) );
                $this->_WxPayUnifiedOrder->SetGoods_tag( $order_name );
                $this->_WxPayUnifiedOrder->SetNotify_url( SITE_API_URL.'/pay/notice/' );
                //$this->_WxPayUnifiedOrder->SetNotify_url( SITE_URL.'/notice.php' );
                $this->_WxPayUnifiedOrder->SetTrade_type("JSAPI" );
                $this->_WxPayUnifiedOrder->SetOpenid( ACPopedom::getWechatOpenid() );
                $order = WxPayApi::unifiedOrder($this->_WxPayUnifiedOrder);
                $jsApiParameters = $this->_JsApiPay->GetJsApiParameters($order);
                $obj['jsApiParameters'] = json_decode($jsApiParameters,true);
                $obj['jump_success_url'] = SITE_API_URL.'/pay/success/'.intval($_GET['order_id']);
                $obj['jump_fail_url'] = SITE_API_URL.'/pay/fail/'.intval($_GET['order_id']);
                $obj['jump_unknow_url'] = SITE_API_URL.'/pay/unknow/'.intval($_GET['order_id']);
                $this->response(array("obj" => $obj,"list" => array(), "status_code" => 0, "status_msg" => "调起支付成功"), "json", 200);

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
     * 余额支付
     */
    public function balance(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                $order_id = intval(I('order_id'));
                $password = trim(I("password"));
                //得到订单信息
                $orderinfo = D("Order")->GetInfo("`order_id`=".$order_id." AND `pay_status`=0 AND `exchange_type`=0 AND `buyer_id`=".ACPopedom::getID());
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                if(empty($password)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "支付密码为空，无法完成支付"), "json", 200);
                }
                $rs = get_config_cache("system@config");
                if(intval($rs['order_invalid_time']) && time() - $orderinfo['posttime'] > intval($rs['order_invalid_time'])*3600){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "该订单已失效，无法完成支付"), "json", 200);
                }
                $order_charge = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));
                //获取用户平台币余额
                $accountinfo = D("Account")->GetInfo("`userid`=".ACPopedom::getID());
                $balance = floatval($accountinfo['balance']);
                if($balance<$order_charge){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10004, "status_msg" => "账号余额不足，无法完成支付"), "json", 200);
                }
                if(ACPopedom::mixAccountPass($password) != $accountinfo['password']){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10005, "status_msg" => "支付密码不正确，无法完成支付"), "json", 200);
                }
                //处理支付流程
                $excute_sql = array();
                $is_execute = true;
                //更新订单状态
                $excute_sql[] = "UPDATE ".C("DB_MALL.db_prefix")."order SET `paytime`=".time().",`pay_status`=1,`status`=3,`paytype`=1 WHERE `order_id`=".$orderinfo['order_id'];
                $ordergoods = M("OrderGoods",C("DB_MALL.db_prefix"),"DB_MALL")->where("`order_id`=".$orderinfo['order_id'])->field("*")->select();
                foreach($ordergoods as $goods){
                    //更新商品销量
                    $excute_sql[] = "UPDATE ".C("DB_MALL.db_prefix")."goods SET `sales`=`sales`+".intval($goods['goods_number'])." WHERE `goodsid`=".intval($goods['goods_id']);
                }
                //判断是否有优惠券
                if($orderinfo['coupon_id']){
                    //更新优惠券信息
                    $excute_sql[] = "UPDATE ".C("DB_MALL.db_prefix")."coupon_record SET `is_used`=1,`usetime`=".time()." WHERE `coupon_id`=".intval($orderinfo['coupon_id'])." AND `member_id`=".$orderinfo['buyer_id'];
                }
                //添加平台币日志
                $excute_sql[] = "INSERT INTO ".C("DB_MALL.db_prefix")."account_log(`userid`,`to_userid`,`money`,`balance`,`order_sn`,`paycard_id`,`type`,`ip`,`posttime`,`remark`) VALUES(0,".intval($orderinfo['buyer_id']).",".$order_charge.",'".$orderinfo['order_sn']."',".$orderinfo['coupon_id'].",1,".get_client_ip().",".time().",'订单支付')";
                //添加支付日志
                $excute_sql[] = "INSERT INTO ".C("DB_MALL.db_prefix")."pay_log(`transaction_id`,`order_sn`,`userid`,`paytype`,`charge`,`status`,`ip`,`posttime`,`remark`) VALUES('".date("Ymdhis")."','".$orderinfo['order_sn']."',".$orderinfo['buyer_id'].",1,".$order_charge.",1,'".get_client_ip()."',".time().",'支付成功')";
                M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->startTrans();
                foreach ($excute_sql as $sql){
                    $rr = M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->execute($sql);
                    if(!$rr){
                        $is_execute = false;
                    }
                }
                if($is_execute == true){
                    M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->commit();
                    $this->response(array("obj" => array("order_charge"=>$order_charge,"order_id"=>$order_id,"order_sn"=>$orderinfo['order_sn']),"list" => array(), "status_code" => 0, "status_msg" => "支付成功"), "json", 200);
                }else{
                    M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->rollback();
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "支付失败"), "json", 200);
                }
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 微信充值平台币
     */
    public function recharge(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                $charge = floatval($_POST['charge']);
                if(!$charge){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "充值金额有误"), "json", 200);
                }
                $data['order_sn'] = GeneralRandSN();
                $data['exchange_type'] = 1;
                $data['seller_id'] = 0;
                $data['buyer_id'] = ACPopedom::getID();
                $data['total_amount'] = $charge;
                $data['posttime'] = time();
                $data['status'] = 0;
                $data['ordertype'] = 0;
                $data['paytype'] = 0;
                $data['paytime'] = 0;
                $data['pay_status'] = 0;
                $data['coupon_id'] = 0;
                $data['coupon_charge'] = 0;
                $data['packet_id'] = 0;
                $data['packet_charge'] = 0;
                $data['member_discount'] = 0;
                $data['discount'] = 0;
                $data['consignee'] = "";
                $data['province'] = "";
                $data['mobile'] = "";
                $data['city'] = "";
                $data['district'] = "";
                $data['address'] = "";
                $data['shipping_charge'] = 0;
                $data['receive_time'] = 0;
                $data['order_remark'] = "平台币充值";
                $data['is_delete'] = 0;
                $order_id = D("Order")->Add($data);
                if(!$order_id){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "系统出错，请重试"), "json", 200);
                }
                $order_name = "微信充值平台币";
                //②、统一下单
                $this->_WxPayUnifiedOrder->SetBody( '【微信充值平台币】' );
                $this->_WxPayUnifiedOrder->SetAttach( ACPopedom::getID() );
                $this->_WxPayUnifiedOrder->SetOut_trade_no(  $data['order_sn']."_".time() );
                $this->_WxPayUnifiedOrder->SetTotal_fee( intval($charge*100) );
                $this->_WxPayUnifiedOrder->SetTime_start( date("YmdHis") );
                $this->_WxPayUnifiedOrder->SetTime_expire(date("YmdHis", time() + 600) );
                $this->_WxPayUnifiedOrder->SetGoods_tag( $order_name );
                $this->_WxPayUnifiedOrder->SetNotify_url( SITE_API_URL.'/pay/notice/' );
                //$this->_WxPayUnifiedOrder->SetNotify_url( SITE_URL.'/notice.php' );
                $this->_WxPayUnifiedOrder->SetTrade_type("JSAPI" );
                $this->_WxPayUnifiedOrder->SetOpenid( ACPopedom::getWechatOpenid() );
                $order = WxPayApi::unifiedOrder($this->_WxPayUnifiedOrder);
                $jsApiParameters = $this->_JsApiPay->GetJsApiParameters($order);
                $obj['jsApiParameters'] = json_decode($jsApiParameters,true);
                $obj['jump_success_url'] = SITE_API_URL.'/pay/success/'.$order_id;
                $obj['jump_fail_url'] = SITE_API_URL.'/pay/fail/'.$order_id;
                $obj['jump_unknow_url'] = SITE_API_URL.'/pay/unknow/'.$order_id;
                $this->response(array("obj" => $obj,"list" => array(), "status_code" => 0, "status_msg" => "调起支付成功"), "json", 200);
                break;
            case "put":
                break;
            case "delete":
                break;
        }

    }
    /**
     * 支付成功
     */
    public function success(){
        switch($this->_method){
            case "get":
                $order_id = intval(I('order_id'));
                //订单基本信息
                $orderinfo = D("Order")->GetInfo("order_id=".$order_id);
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $order_charge = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));
                $this->response(array("obj" => array("order_charge"=>$order_charge,"order_id"=>$order_id,"order_sn"=>$orderinfo['order_sn']),"list" => array(), "status_code" => 0, "status_msg" => "支付成功"), "json", 200);
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
     * 支付失败
     */
    public function fail(){
        switch($this->_method){
            case "get":
                $order_id = intval(I('order_id'));
                //订单基本信息
                $orderinfo = D("Order")->GetInfo("order_id=".$order_id);
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $order_charge = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));
                $this->response(array("obj" => array("order_charge"=>$order_charge,"order_id"=>$order_id,"order_sn"=>$orderinfo['order_sn']),"list" => array(), "status_code" => 0, "status_msg" => "支付失败"), "json", 200);
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
     * 未知错误
     */
    public function unknow(){
        switch($this->_method){
            case "get":
                $order_id = intval(I('order_id'));
                //订单基本信息
                $orderinfo = D("Order")->GetInfo("order_id=".$order_id);
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $order_charge = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));
                $this->response(array("obj" => array("order_charge"=>$order_charge,"order_id"=>$order_id,"order_sn"=>$orderinfo['order_sn']),"list" => array(), "status_code" => 0, "status_msg" => "未知错误"), "json", 200);
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
     * 支付通知
     */
    public function notice(){
        import("Vendor.WxPay.WxPayNotifyCallBack");
        //write_file("/data/wwwroot/sh.seejiajia.com/mbjl-backend/Attachment/aa.txt",time());
        $WxPayNotifyCallBack = new WxPayNotifyCallBack();
        $WxPayNotifyCallBack->Handle(false);
        //write_file("/data/wwwroot/sh.seejiajia.com/mbjl-backend/Attachment/a.txt",time());
    }

}