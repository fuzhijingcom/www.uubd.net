<?php

/**
 * 收货地址管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Think\Controller\MapiController;

class AddressController extends MapiController {

    /**
     * 获取收货地址信息列表
     */
    public function get_info_list(){
        switch($this->_method){
            case "get":
                $addresslist = D("UserAddress")->GetAll("`user_id`=".ACPopedom::getID());
                foreach ($addresslist as $key => $address){
                    $addresslist[$key]['address'] = $address['province'].$address['city'].$address['district'].$address['address'];
                }
                $this->response(array("obj" => array(),"list" => $addresslist, "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
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
     * 添加收货地址
     */
    public function add(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                $data['user_id'] = ACPopedom::getID();
                $data['consignee'] = trim($_POST['consignee']);
                $data['province'] = trim($_POST['province']);
                $data['city'] = trim($_POST['city']);
                $data['district'] = trim($_POST['district']);
                $data['address'] = trim($_POST['address']);
                $data['mobile'] = trim($_POST['mobile']);
                $data['is_default'] = intval($_POST['is_default']) ? 1 : 0;
                $data['is_pickup'] = intval($_POST['is_pickup']) ? 1 : 0;
                if(empty($data['province'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "请选择省份信息"), "json", 200);
                }
                if(empty($data['address'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "请填写详细地址"), "json", 200);
                }
                if(empty($data['consignee'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "请填写收货人信息"), "json", 200);
                }
                if(empty($data['mobile'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10004, "status_msg" => "请填写收货人电话"), "json", 200);
                }
                D("UserAddress")->startTrans();
                $rs = D("UserAddress")->Edit("`user_id`=".ACPopedom::getID(),array("is_default"=>0));
                $rr = D("UserAddress")->Add($data);
                if($rs && $rr){
                    D("UserAddress")->commitTrans();
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "添加成功"), "json", 200);
                }else{
                    D("UserAddress")->rollbackTrans();
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "添加失败"), "json", 200);
                }
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 编辑地址信息
     */
    public function edit(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                break;
            case "put":
                $address_id = intval($_GET['address_id']);
                $data['consignee'] = trim($this->_request['consignee']);
                $data['province'] = trim($this->_request['province']);
                $data['city'] = trim($this->_request['city']);
                $data['district'] = trim($this->_request['district']);
                $data['address'] = trim($this->_request['address']);
                $data['mobile'] = trim($this->_request['mobile']);
                $data['is_default'] = intval($this->_request['is_default']) ? 1 : 0;
                if(empty($data['province'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "请选择省份信息"), "json", 200);
                }
                if(empty($data['address'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "请填写详细地址"), "json", 200);
                }
                if(empty($data['consignee'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "请填写收货人信息"), "json", 200);
                }
                if(empty($data['mobile'])){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10004, "status_msg" => "请填写收货人电话"), "json", 200);
                }
                D("UserAddress")->startTrans();
                $rs = D("UserAddress")->Edit("`user_id`=".ACPopedom::getID(),array("is_default"=>0));
                $rr = D("UserAddress")->Edit("`address_id`=".$address_id." AND `user_id`=".ACPopedom::getID(),$data);
                if($rs && $rr){
                    D("UserAddress")->commitTrans();
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "更新成功"), "json", 200);
                }else{
                    D("UserAddress")->rollbackTrans();
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "更新失败"), "json", 200);
                }
                break;
            case "delete":
                break;
        }
    }

    /**
     * 删除地址信息
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
                $address_id = intval($_GET['address_id']);
                $rs = D("UserAddress")->Delete("`address_id`=".$address_id." AND `user_id`=".ACPopedom::getID());
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "删除失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "删除成功"), "json", 200);
                break;
        }
    }

    /**
     * 设置默认信息
     */
    public function set_default(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                $address_id = intval($_GET['address_id']);
                $rs = D("UserAddress")->GetInfo("`address_id`=".$address_id." AND `user_id`=".ACPopedom::getID());
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "收货地址信息不存在"), "json", 200);
                }
                //$data['is_default'] = intval($_POST['is_default']) ? 1 : 0;
                D("UserAddress")->startTrans();
                $rs = D("UserAddress")->Edit("`user_id`=".ACPopedom::getID(),array("is_default"=>0));
                $rr = D("UserAddress")->Edit("`address_id`=".$address_id." AND `user_id`=".ACPopedom::getID(),array("is_default"=>1));
                if($rs && $rr){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "设置成功"), "json", 200);
                    D("UserAddress")->commitTrans();
                }else{
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "设置失败"), "json", 200);
                    D("UserAddress")->rollbackTrans();
                }
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 获取自取地址列表
     */
    public function get_pickup_list(){
        switch($this->_method){
            case "get":
                $act_id = intval($_GET['act_id']);
                $addresslist = D("ActivityPickInfo")->GetAll("`act_id`=".$act_id);
                foreach ($addresslist as $key => $address){
                    $addresslist[$key]['pick_date'] = date("m月d日 H点",$address['pick_time']);
                }
                $this->response(array("obj" => array(),"list" => $addresslist, "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
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