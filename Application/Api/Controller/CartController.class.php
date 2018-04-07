<?php

/**
 * 购物车管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Think\Controller\MapiController;

class CartController extends MapiController {

    /**
     * 获取购物车信息列表
     */
    public function get_list(){
        switch($this->_method){
            case "get":
                $infolist = D("Cart")->GetAll("user_id=".ACPopedom::getID(),array("id"=>"DESC"),"goods_name,goods_price,goods_number,goods_id,id,goods_color,goods_size");
                if(empty($infolist)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "购物车为空"), "json", 200);
                }
                $total_price = 0;
                foreach ($infolist as $key => $info){
                    //得到商品图片
                    $image = D("GoodsAttachment")->GetInfo("`goodsid`=".$info['goods_id']." AND is_default=1");
                    $infolist[$key]['thumb_url'] = SITE_ATTACHMENT_URL.$image['picpath'];
                    $infolist[$key]['total_price'] = sprintf("%.2f",$info['goods_price']*$info['goods_number']);
                    $total_price += sprintf("%.2f",$info['goods_price']*$info['goods_number']);
                }
                $this->response(array("obj" => array("total_price"=>sprintf("%.2f",$total_price)),"list" => $infolist, "status_code" => 0, "status_msg" => "购物车列表"), "json", 200);
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
     * 添加购物车
     */
    public function add(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                $goods_id = intval($_POST['goods_id']);
                $goods_color = trim($_POST['goods_color']);
                $goods_size = trim($_POST['goods_size']);
                $goods_number = intval($_POST['goods_number']);
                $goodInfo = D("Goods")->GetInfo("`goodsid`=".$goods_id);
                if(empty($goodInfo)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "商品信息不存在"), "json", 200);
                }
                //判断商品颜色是否存在
                $color_info = D("GoodsColor")->GetInfo("`name`='".$goods_color."'");
                if(empty($color_info)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "商品颜色信息不存在"), "json", 200);
                }
                if(!in_array($color_info['color_id'],json_decode($goodInfo['color'],true))){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "商品颜色信息不存在"), "json", 200);
                }
                //判断商品尺寸是否存在
                $size_info = D("GoodsSize")->GetInfo("`name`='".$goods_size."'");
                if(empty($size_info)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10004, "status_msg" => "商品大小信息不存在"), "json", 200);
                }
                if(!in_array($size_info['size_id'],json_decode($goodInfo['size'],true))){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10005, "status_msg" => "商品大小信息不存在"), "json", 200);
                }
                //得到购物车信息
                $cartInfo = D("Cart")->GetInfo("`user_id`=".ACPopedom::getID()." AND `goods_id`=".$goods_id);
                if(empty($cartInfo)){
                    $data['user_id'] = ACPopedom::getID();
                    $data['session_id'] = session_id();
                    $data['goods_id'] = $goods_id;
                    $data['goods_name'] = $goodInfo['title'];
                    $data['goods_price'] = $goodInfo['price'];
                    $data['goods_number'] = $goods_number;
                    $data['goods_color'] = $goods_color;
                    $data['goods_size'] = $goods_size;
                    $data['add_time'] = time();
                    $rs = D("Cart")->Add($data);
                }else{
                    $rs = D("Cart")->Edit("`user_id`=".ACPopedom::getID()." AND `goods_id`=".$goods_id,array("goods_price"=>$goodInfo['price'],"goods_number"=>$goods_number));
                }
                if(!$rs){
                    $this->response(array("obj" => array("cart_number"=>$goods_number,"cart_price"=>sprintf("%.2f",$goods_number*$goodInfo['price'])),"list" => array(), "status_code" => 1, "status_msg" => "商品加入购物车失败"), "json", 200);
                }
                $this->response(array("obj" => array("cart_number"=>$goods_number,"cart_price"=>sprintf("%.2f",$goods_number*$goodInfo['price'])),"list" => array(), "status_code" => 0, "status_msg" => "商品加入购物车成功"), "json", 200);
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 编辑购物车信息
     */
    public function edit(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                break;
            case "put":
                $cart_id = intval($_GET['cart_id']);
                $goods_number = intval($this->_request['goods_number']);
                $cart_info = D("Cart")->GetInfo("`id`=".$cart_id." AND `user_id`=".ACPopedom::getID());
                if(empty($cart_info)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "购物车信息不存在"), "json", 200);
                }
                if(!$goods_number){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "购物车商品数量不能为0"), "json", 200);
                }
                $rs = D("Cart")->Edit("`id`=".$cart_id." AND `user_id`=".ACPopedom::getID(),array("goods_number"=>$goods_number));
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "操作失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "操作成功"), "json", 200);
                break;
            case "delete":
                break;
        }
    }

    /**
     * 删除购物车信息
     */
    public function delete(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                break;
            case "put":
                break;
            case "delete":
                $cart_id = intval($_GET['cart_id']);
                $rs = D("Cart")->Delete("`id`=".$cart_id." AND `user_id`=".ACPopedom::getID());
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "删除失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "删除成功"), "json", 200);
                break;
        }
    }
}