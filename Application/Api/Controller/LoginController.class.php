<?php
/**
 * 登录模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Org\Net\Http;
use Think\Controller\MapiController;
use Tools\Program;

class LoginController extends MapiController {

    /**
     * 微信授权登录处理
     */
    public function wechat(){
        
        switch($this->_method){
            case "get":
                break;
            case "post":
                $code = trim($_POST['code']);
                $encryptedData = trim($_POST['encryptedData']);
                $iv = trim($_POST['iv']);
                $appid = C("AUTH_APPID");
                $secret = C("AUTH_APPSECRET");
                //授权
                $apiData = Http::CurlRequst("https://api.weixin.qq.com/sns/jscode2session", array("appid" => $appid, "secret" => $secret, "js_code" => $code, "grant_type" => "authorization_code"), "GET");

                $apiData = json_decode($apiData,true);
                if(!isset($apiData['session_key']))
                {
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 102, "status_msg" => "curl error"), "json", 200);
                }
                import("Vendor.Wechat.Program");
                $Program = new Program();
                $userInfo = $Program->getUserInfo($appid,$apiData['session_key'],$encryptedData,$iv);
                $userInfo = json_decode($userInfo,true);
                if(!$userInfo)
                {
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 105, "status_msg" => "userInfo not"), "json", 200);
                }
                $data['openid'] = $userInfo['openId'];
                $data['nickname'] = base_encode($userInfo['nickName']);
                $data['avator'] = $userInfo['avatarUrl'];
                $data['unionid'] = isset($userInfo['unionId']) ? $userInfo['unionId'] : "";
                $data['gender'] = $userInfo['gender'];
                $data['posttime'] = time();
                $data['logintime'] = time();
                $data['loginip'] = get_client_ip();
                $data['role_type'] = 1;
                $data['status'] = 1;
                $data['ip'] = get_client_ip();
                $data['groupid'] = 1;
                $rs = ACPopedom::login($data);
                $data['token'] = $rs['token'];
                $this->response(array("obj" => $data, "list" => array(), "status_code" => $rs['status_code'], "status_msg" => $rs['status_msg']), "json", 200);
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 手机号+验证码登录
     */
    public function mobile(){
        switch($this->_method){
            case "get":
                $code = trim($_GET['code']);
                $encryptedData = trim($_GET['encryptedData']);
                $iv = trim($_GET['iv']);
                $appid = C("AUTH_APPID");
                $secret = C("AUTH_APPSECRET");
                //授权
                $apiData = Http::CurlRequst("https://api.weixin.qq.com/sns/jscode2session", array("appid" => $appid, "secret" => $secret, "js_code" => $code, "grant_type" => "authorization_code"), "GET");
                $apiData = json_decode($apiData,true);
                if(!isset($apiData['session_key']))
                {
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 102, "status_msg" => "curl error"), "json", 200);
                }
                import("Vendor.Wechat.Program");
                $Program = new Program();
                $userInfo = $Program->getUserInfo($appid,$apiData['session_key'],$encryptedData,$iv);
                $userInfo = json_decode($userInfo,true);
                if(!$userInfo)
                {
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 105, "status_msg" => "userInfo not"), "json", 200);
                }
                $data['openid'] = $userInfo['openId'];
                $data['nickname'] = base_encode($userInfo['nickName']);
                $data['avator'] = $userInfo['avatarUrl'];
                $data['unionid'] = isset($userInfo['unionId']) ? $userInfo['unionId'] : "";
                $data['gender'] = $userInfo['gender'];
                $data['posttime'] = time();
                $data['logintime'] = time();
                $data['loginip'] = get_client_ip();
                $data['role_type'] = 1;
                $data['status'] = 1;
                $data['ip'] = get_client_ip();
                $data['groupid'] = 1;

                //判断是否存在
                $info = D("User")->GetInfo("`openid`='".$data['openid']."'");
                if(empty($info)){
                    D("User")->startTrans();
                    $userid = D("User")->Add($data);
                    $rs = D("Account")->Add(array("userid"=>$userid,"balance"=>0,"password"=>"","status"=>1));
                    if($userid && $rs){
                        $this->response(array("obj" => $userInfo, "list" => array(), "status_code" => 0, "status_msg" => "授权成功"), "json", 200);
                    }else{
                        D("User")->rollbackTrans();
                        $this->response(array("obj" => array(), "list" => array(), "status_code" => 1, "status_msg" => "授权失败"), "json", 200);
                    }
                }else{
                    $this->response(array("obj" => array(), "list" => array(), "status_code" => 1, "status_msg" => "授权失败"), "json", 200);
                }
                break;
            case "post":
                $mobile = trim($_POST['mobile']);
                $password = trim($_POST['code']);
                $openid = trim($_POST['openid']);
                $result = ACPopedom::login_mobile($mobile,$password,$openid);
                $obj['token'] = $result['token'];
                $this->response(array("obj"=>$obj,"list"=>array(),"status_code"=>$result['status_code'],"status_msg"=> $result['status_msg']),"json",200);
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }

    /**
     * 检查是否登录
     */
    public function check_login(){
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
     * 退出登录
     */
    public function logout(){
        switch($this->_method){
            case "get":
                $http_token = $_SERVER['HTTP_TOKEN'];
                if(empty($http_token)){
                    $this->response(array("obj"=>array(),"list"=>array(),"status_code"=>1,"status_msg"=> "登录信息有误"),"json",200);
                }
                ACPopedom::logout($http_token);
                $this->response(array("obj"=>array(),"list"=>array(),"status_code"=>0,"status_msg"=> "退出成功"),"json",200);
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