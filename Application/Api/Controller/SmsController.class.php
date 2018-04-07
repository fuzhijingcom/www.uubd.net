<?php

/**
 * 发送手机验证码模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Common\Validation;
use Think\Controller\MapiController;
use Tools\Sms;

class SmsController extends MapiController {

    /**
     * 发送手机验证码
     */
    public function send(){
        switch($this->_method){
            case "get":
                import("Vendor.Sms.Sms");
                $mobile = trim($_GET['mobile']);
                if(!Validation::checkTelephone($mobile)){
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 10001, "status_msg" => "请输入正确的手机号码"), "json", 200);
                }
                set_time_limit(0);
                $code = GeneralRandCode(4,false);
                header('Content-Type: text/plain; charset=utf-8');
                $accessKeyId = ""; // AccessKeyId
                $accessKeySecret = ""; // AccessKeySecret
                $SignName = "";
                $TemplateCode = "";
                $response = Sms::sendSms($accessKeyId,$accessKeySecret,$mobile,$SignName,$TemplateCode,$code);
                if ($response->Code=="OK") {
                    ACPopedom::setCache($mobile,$code,60);
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 0, "status_msg" => "验证码已经发送成功"), "json", 200);
                } else {
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 1, "status_msg" => "验证码发送失败"), "json", 200);
                }
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