<?php

/**
 * 订单管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Think\Controller\MapiController;

class OrderController extends MapiController {

    /**
     * 获取订单信息列表
     */
    public function get_list(){
        switch($this->_method){
            case "get":
                $status = intval($_GET['status']);
                if($status==3){//已完成
                    $where = "`buyer_id`=".ACPopedom::getID()." AND `is_delete`=0 AND `pay_status`=1 AND `status`=1 AND `exchange_type`=0";
                }elseif ($status==2){//待收货
                    $where = "`buyer_id`=".ACPopedom::getID()." AND `is_delete`=0 AND `pay_status`=1 AND `status`=4 AND `exchange_type`=0";
                }elseif ($status==1){//待付款
                    $where = "`buyer_id`=".ACPopedom::getID()." AND `is_delete`=0 AND `pay_status`=0 AND `exchange_type`=0";
                }else{//全部订单
                    $where = "`buyer_id`=".ACPopedom::getID()." AND `is_delete`=0 AND `exchange_type`=0";
                }
                /*$page = intval($_GET['page']) ? intval($_GET['page']) : 1;
                $rows = intval($_GET['number']) ? intval($_GET['number']) : 6;
                $infolist = D("Order")->GetJoinList("o",array("LEFT JOIN __ACTIVITY__ a ON a.act_id=o.act_id"),"o.`buyer_id`=".ACPopedom::getID()." AND o.`is_delete`=0",$page,$rows,array("o.posttime"=>"DESC"),"o.*,a.act_name");*/
                $infolist = D("Order")->GetAll($where,array("posttime"=>"DESC"));
                foreach ($infolist as $key => $info){
                    //得到订单商品数量
                    $goodslist = D("OrderGoods")->GetJoinAll("og",array("LEFT JOIN __GOODS__ AS g on g.goodsid =og.goods_id"),"og.`order_id`=".$info['order_id'],array("og.id"=>"ASC"),"g.title,og.goods_id,og.goods_size,og.goods_color,og.goods_number");
                    foreach ($goodslist as $kk => $goods){
                        //得到商品图片
                        $image = D("GoodsAttachment")->GetInfo("`goodsid`=".$goods['goods_id']." AND `is_default`=1");
                        $goodslist[$kk]['thumb_url'] = SITE_ATTACHMENT_URL.$image['picpath'];
                    }
                    if( count($goodslist)>1 ){
                        if( strlen($goodslist[0]['title'])>10 ){
                            $order_name = mb_substr($goodslist[0]['title'], 0, 10, 'utf8')."等";
                        }else{
                            $order_name = $goodslist[0]['title']."等";
                        }

                    }else{
                        if( strlen($goodslist[0]['title'])>10 ){
                            $order_name = mb_substr($goodslist[0]['title'], 0, 10, 'utf8');
                        }else{
                            $order_name = $goodslist[0]['title'];
                        }
                    }
                    $order_name = str_replace(" ", "", $order_name);
                    $infolist[$key]['goodstotal'] = count($goodslist);
                    $infolist[$key]['goods_list'] = $goodslist;
                    $infolist[$key]['order_name'] = $order_name;
                    $infolist[$key]['post_date'] = date("Y.m.d",$info['posttime']);
                    if($info['paytime'] && $info['pay_status']){
                        $infolist[$key]['pay_status_label'] = "已付款";
                        $infolist[$key]['pay_time'] = date("Y-m-d H:i:s",$info['paytime']);
                        if($info['status']==3){
                            $infolist[$key]['status_label'] = "待发货";
                        }
                        if($info['status']==4){
                            $infolist[$key]['status_label'] = "待收货";
                        }
                        if($info['status']==6){
                            $infolist[$key]['status_label'] = "已收货";
                        }
                        if($info['status']==1){
                            $infolist[$key]['status_label'] = "完成订单";
                        }
                        if($info['status']==2){
                            $infolist[$key]['status_label'] = "订单已取消";
                        }
                        if($info['status']==5){
                            $infolist[$key]['status_label'] = "已退货";
                        }
                    }else{
                        $infolist[$key]['pay_time'] = "未付款";
                        if($info['status']==2){
                            $infolist[$key]['pay_status_label'] = "订单已取消";
                            $infolist[$key]['status_label'] = "订单已取消";
                        }else{
                            $infolist[$key]['pay_status_label'] = "待付款";
                            $infolist[$key]['status_label'] = "待付款";
                        }
                    }
                    if($info['paytype']==1){
                        $infolist[$key]['payment_name'] = $this->_config['account_name']."支付";
                    }elseif($info['paytype']==2){
                        $infolist[$key]['payment_name'] = "微信支付";
                    }else{
                        $infolist[$key]['payment_name'] = "待支付";
                    }
                    if($info['ordertype']==1){
                        $infolist[$key]['ordertype'] = "商家订单";
                    }else{
                        $infolist[$key]['ordertype'] = "平台订单";
                    }
                    $infolist[$key]['order_charge'] = sprintf("%.2f",($info['total_amount']-$info['coupon_charge']-$info['packet_charge']-$info['member_discount']-$info['discount']));
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
     * 添加订单
     */
    public function add(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                $array_cart_id = explode(",",$_POST['cart_id']);
                $address_id = intval($_POST['address_id']);
                $coupon_id = intval($_POST['coupon_id']);
                $goods_id = intval($_POST['goods_id']);
                $goods_number = intval($_POST['goods_number']);
                $goods_price = floatval($_POST['goods_price']);
                $goods_color = trim($_POST['color']);
                $goods_size = trim($_POST['size']);

                //得到收获地址信息
                $address_info = D("UserAddress")->GetInfo("`address_id`=".$address_id);
                if(empty($address_info)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "收获地址信息不存在"), "json", 200);
                }
                //得到优惠券信息
                if($coupon_id) {
                    $coupon_info = D("CouponRecord")->GetInfo("`member_id`=" . ACPopedom::getID() . " AND `coupon_id`=" . $coupon_id);
                    if (empty($coupon_info)) {
                        $this->response(array("obj" => array(), "list" => array(), "status_code" => 10003, "status_msg" => "优惠券信息不存在"), "json", 200);
                    }
                    if ($coupon_info['valid'] < time()) {
                        $this->response(array("obj" => array(), "list" => array(), "status_code" => 10004, "status_msg" => "该优惠券已经失效"), "json", 200);
                    }
                    if ($coupon_info['is_used'] == 1) {
                        $this->response(array("obj" => array(), "list" => array(), "status_code" => 10005, "status_msg" => "该优惠券已经使用"), "json", 200);
                    }
                    $coupon_charge = $coupon_info['coupon_charge'];
                }else{
                    $coupon_charge = 0;
                }
                $total_amount = 0;
                $error_msg = array();
                $is_execute = true;
                $excute_sql = array();

                if($goods_id && $goods_number && $goods_price && $goods_color && $goods_size){
                    $total_amount += $goods_price * $goods_number;
                    $excute_sql[] = "INSERT INTO ".C("DB_MALL.db_prefix")."order_goods(`order_id`,`goods_id`,`goods_price`,`goods_number`,`goods_color`,`goods_size`) VALUES(ORDER_ID,".$goods_id.",".$goods_price.",".$goods_number.",'".$goods_color."','".$goods_size."')";
                }else{
                    if (empty($array_cart_id)){
                        $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "商品信息有误"), "json", 200);
                    }
                    foreach ($array_cart_id as $key => $cart_id){
                        $cart_info = D("Cart")->GetInfo("`id`=".intval($cart_id));
                        if(empty($cart_info)){
                            $error_msg[] = "商品购物车信息不存在";
                        }
                        $total_amount += $cart_info['goods_price'] * intval($cart_info['goods_number']);
                        $excute_sql[] = "DELETE FROM ".C("DB_MALL.db_prefix")."cart WHERE `id`=".intval($cart_id);
                        $excute_sql[] = "INSERT INTO ".C("DB_MALL.db_prefix")."order_goods(`order_id`,`goods_id`,`goods_price`,`goods_number`,`goods_color`,`goods_size`) VALUES(ORDER_ID,".intval($cart_info['goods_id']).",".$cart_info['goods_price'].",".intval($cart_info['goods_number']).",'".$cart_info['goods_color']."','".$cart_info['goods_size']."')";
                    }
                }
                $data['order_sn'] = GeneralRandSN();
                $data['exchange_type'] = 0;
                $data['seller_id'] = 0;
                $data['buyer_id'] = ACPopedom::getID();
                $data['total_amount'] = $total_amount;
                $data['posttime'] = time();
                $data['status'] = 0;
                $data['ordertype'] = 0;
                $data['paytype'] = 0;
                $data['paytime'] = 0;
                $data['pay_status'] = 0;
                $data['coupon_id'] = $coupon_id;
                $data['coupon_charge'] = $coupon_charge;
                $data['packet_id'] = 0;
                $data['packet_charge'] = 0;
                $data['member_discount'] = 0;
                $data['discount'] = 0;
                $data['consignee'] = $address_info['consignee'];
                $data['province'] = $address_info['province'];
                $data['mobile'] = $address_info['mobile'];
                $data['city'] = $address_info['city'];
                $data['district'] = $address_info['district'];
                $data['address'] = $address_info['address'];
                $data['shipping_charge'] = floatval($_POST['shipping_charge']);
                $data['receive_time'] = 0;
                $data['order_remark'] = trim($_POST['remark']);
                $data['is_delete'] = 0;
                if(!empty($error_msg)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10004, "status_msg" => implode("<br/>",$error_msg)), "json", 200);
                }
                D("Order")->startTrans();
                $order_id = D("Order")->Add($data);
                foreach ($excute_sql as $sql){
                    $sql = str_replace("ORDER_ID",$order_id,$sql);
                    $rr = D("Order")->ExecuteSql($sql);
                    if(!$rr){
                        $is_execute = false;
                    }
                }
                if($order_id && $is_execute == true){
                    D("Order")->commitTrans();
                    $this->response(array("obj" => array("order_id"=>$order_id),"list" => array(), "status_code" => 0, "status_msg" => "下单成功"), "json", 200);
                }else{
                    D("Order")->rollbackTrans();
                    $this->response(array("obj" => array("order_id"=>0),"list" => array(), "status_code" => 1, "status_msg" => "下单失败"), "json", 200);
                }
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 删除订单信息
     */
    public function delete(){
        switch($this->_method){
            case "get":
                $order_id = intval(I('order_id'));
                //订单基本信息
                $orderinfo = D("Order")->GetInfo("order_id=".$order_id." AND `exchange_type`=0 AND `buyer_id`=".ACPopedom::getID());
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $rs = D("Order")->Edit("`order_id`=".$order_id,array("is_delete"=>1));
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "删除失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "删除成功"), "json", 200);
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
     * 获取订单详情
     */
    public function get_info(){
        switch($this->_method){
            case "get":
                $order_id = intval(I('order_id'));
                //订单基本信息
                $orderinfo = D("Order")->GetInfo("order_id=".$order_id." AND `exchange_type`=0 AND `buyer_id`=".ACPopedom::getID());
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $goodslist = D("OrderGoods")->GetJoinAll("og",array("LEFT JOIN __GOODS__ AS g on g.goodsid =og.goods_id"),"og.`order_id`=".$order_id,array("og.id"=>"ASC"),"g.title AS goods_name,og.*");
                if(empty($goodslist)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "该订单没有任何商品信息"), "json", 200);
                }
                foreach ($goodslist as $k => $goods){
                    //得到商品图片
                    $image = D("GoodsAttachment")->GetInfo("`goodsid`=".$goods['goods_id']." AND is_default=1");
                    $goodslist[$k]['thumb_url'] = SITE_ATTACHMENT_URL.$image['picpath'];
                    $goodslist[$k]['goods_charge'] = sprintf("%.2f",$goods['goods_number']*$goods['goods_price']);
                }
                $orderinfo['post_date'] = date("Y.m.d H:i:s",$orderinfo['posttime']);
                if($orderinfo['pay_status']==1){
                    $orderinfo['pay_date'] = date("Y.m.d H:i:s",$orderinfo['paytime']);
                }
                if($orderinfo['status']==2){
                    $orderinfo['cancel_date'] = date("Y.m.d H:i:s",$orderinfo['cancel_time']);
                }
                $orderinfo['order_charge'] = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));
                $this->response(array("obj" => $orderinfo,"list" => $goodslist, "status_code" => 0, "status_msg" => "获取订单详情"), "json", 200);
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
     * 申请退货
     */
    public function returns(){
        switch($this->_method){
            case "get":
                $order_id = intval(I('order_id'));
                //订单基本信息
                $orderinfo = D("Order")->GetInfo("order_id=".$order_id." AND `exchange_type`=0 AND `buyer_id`=".ACPopedom::getID()." AND `status`=6");
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $rs = D("Order")->Edit("`order_id`=".$order_id,array("status"=>5));
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "操作失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "操作成功"), "json", 200);
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
     * 申请退款
     */
    public function return_charge(){
        switch($this->_method){
            case "get":
                $order_id = intval(I('order_id'));
                //订单基本信息
                $orderinfo = D("Order")->GetInfo("order_id=".$order_id." AND `exchange_type`=0 AND `buyer_id`=".ACPopedom::getID()." AND `pay_status`=1");
                if(empty($orderinfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "订单信息不存在"), "json", 200);
                }
                $data['order_id'] = $order_id;
                $data['order_sn'] = $orderinfo['order_sn'];
                $data['posttime'] = time();
                $data['return_charge'] = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));
                $data['return_status'] = 0;
                $data['check_remark'] = "";
                $data['check_userid'] = 0;
                $data['check_posttime'] = 0;
                $rs = D("OrderReturnCharge")->Add($data);
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "操作失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "操作成功"), "json", 200);
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