<?php

/**
 * 个人信息管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Common\Validation;
use Think\Controller\MapiController;

class UserController extends MapiController {

    /**
     * 个人信息
     */
    public function get_info(){
        switch($this->_method){
            case "get":
                $info = D("User")->GetInfo("`userid`=".ACPopedom::getID());
                $info['nickname'] = base_decode($info['nickname']);
                //得到账户余额
                $account = D("Account")->GetInfo("`userid`=".ACPopedom::getID());
                $info['balance'] = floatval($account['balance']);
                //得到优惠券
                $coupon = D("CouponRecord")->GetTotal("`member_id`=".ACPopedom::getID()." AND `is_used`=0 AND `valid`>".time());
                $info['coupon'] = $coupon;
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

    /**
     * 编辑个人信息
     */
    public function edit(){
        switch($this->_method){
            case "get":
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
     * 设置支付密码
     */
    public function set_account_password(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                break;
            case "put":
                $password = trim($this->_request['password']);
                $repassword = trim($this->_request['repassword']);
                if(empty($password)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "请输入支付密码"), "json", 200);
                }
                if($password != $repassword){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "两次输入的支付密码不一致"), "json", 200);
                }
                $password = ACPopedom::mixAccountPass($password);
                $rs = D("Account")->Edit("`userid`=".ACPopedom::getID(),array("password"=>$password));
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "设置失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "设置成功"), "json", 200);
                break;
            case "delete":
                break;
        }
    }

    /**
     * 重设支付密码
     */
    public function reset_account_password(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                break;
            case "put":
                $oldpassword = trim($this->_request['oldpassword']);
                $password = trim($this->_request['password']);
                $repassword = trim($this->_request['repassword']);
                if(empty($oldpassword)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "请输入原始支付密码"), "json", 200);
                }
                //判断支付密码是否正确
                $info = D("Account")->GetInfo("`userid`=".ACPopedom::getID());
                if($info['password'] != ACPopedom::mixAccountPass($oldpassword)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "原始支付密码不正确"), "json", 200);
                }
                if(empty($password)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "请设置支付密码"), "json", 200);
                }
                if($password != $repassword){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10004, "status_msg" => "两次输入的支付密码不一致"), "json", 200);
                }
                $password = ACPopedom::mixAccountPass($password);
                $rs = D("Account")->Edit("`userid`=".ACPopedom::getID(),array("password"=>$password));
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "设置失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "设置成功"), "json", 200);
                break;
            case "delete":
                break;
        }
    }

    /**
     * 找回密码
     */
    public function find_set_password(){
        switch($this->_method){
            case "get":
                break;
            case "post":
                break;
            case "put":
                $mobile = trim($this->_request['mobile']);
                $code = trim($this->_request['code']);
                $password = trim($this->_request['password']);
                if(empty($mobile) || !Validation::IsMobileNumber($mobile)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "请输入正确的手机号码"), "json", 200);
                }
                //判断手机号码是否存在
                $rs = D("User")->GetInfo("`username`='".$mobile."'");
                if(empty($rs)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10002, "status_msg" => "手机号码不存在"), "json", 200);
                }
                if(!ACPopedom::setCache($mobile)){
                    return array("status_code" => 10006, "status_msg" => "验证码已过期");
                }
                if(ACPopedom::setCache($mobile) != $code){
                    return array("status_code" => 10007, "status_msg" => "验证码不正确");
                }
                if(empty($password)){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10003, "status_msg" => "请设置支付密码"), "json", 200);
                }
                $password = ACPopedom::mixAccountPass($password);
                $rs = D("Account")->Edit("`userid`=".ACPopedom::getID(),array("password"=>$password));
                if(!$rs){
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 1, "status_msg" => "设置失败"), "json", 200);
                }
                $this->response(array("obj" => array(),"list" => array(), "status_code" => 0, "status_msg" => "设置成功"), "json", 200);
                break;
            case "delete":
                break;
        }
    }
}