<?php
namespace Api\Common;
use Common\Validation;
/**
 *
 * 权限功能检测类
 * @author hp
 *
 */
final class ACPopedom {

    /**
     * 检查token是否合法
     * @param $token
     */
    static public function checkToken($http_token){
        if (self::getID() && self::getUserName() && self::getUniqid()) {
            $_token = base_encode(encrypt(json_encode(array("userid"=>intval(self::getID()),'openid'=>self::getWechatOpenid(),"uniqid"=>self::getUniqid())),C("SESSION_PASSPORT_AUTH")));
            if($http_token != $_token){
                return false;
            }
            return true;
        }else {
            return false;
        }
    }

    /**
     * 刷新token
     * @param $http_token
     */
    static public function refreshToken($http_token){
        $uniqid = uniqid();
        $_http_token = decrypt(base_decode($http_token),C("SESSION_PASSPORT_AUTH"));
        $_http_token = json_decode($_http_token,true);
        $userid = intval($_http_token['userid']);
        $info = D("User")->GetInfo("`userid`=".$userid);
        if(empty($info)){
            return false;
        }
        $token = base_encode(encrypt(json_encode(array("userid"=>intval($info["userid"]),'openid'=>$info['openid'],"uniqid"=>$uniqid)),C("SESSION_PASSPORT_AUTH")));
        self::setCache(md5(intval($info['userid']))."__uid__",$info['userid'],DT_CACHE_TIME);
        self::setCache(md5(intval($info['userid']))."__level__",$info['groupid'],DT_CACHE_TIME);
        self::setCache(md5(intval($info['userid']))."__username__",base_decode($info['nickname']),DT_CACHE_TIME);
        self::setCache(md5(intval($info['userid']))."__auth__",$token,DT_CACHE_TIME);
        self::setCache(md5(intval($info['userid']))."__uniqid__",$uniqid,DT_CACHE_TIME);
        self::setCache(md5(intval($info['userid']))."__openid__",$info['openid'],DT_CACHE_TIME);
        return array("status_code" => 0, "status_msg" => "Token刷新成功！", "token" => $token, "userid" => $info['userid'],"username" => base_decode($info['nickname']),"expire"=>C('EXPIRE_TIME'));
    }

    /**
     *
     * 功能:登陆
     * @param $data
     */
    static public function login($data){//账号,验证码
        //判断是否存在
        $info = D("User")->GetInfo("`openid`='".$data['openid']."'");
        if(empty($info)){
            D("User")->startTrans();
            $userid = D("User")->Add($data);
            $rs = D("Account")->Add(array("userid"=>$userid,"balance"=>0,"password"=>"","status"=>1));
            if($userid && $rs){
                D("User")->commitTrans();
                $uniqid = uniqid();
                //更新登录时间
                D("User")->Edit("`userid`=".intval($userid),array("logintime"=>time(),"loginip"=>get_client_ip()));
                $token = base_encode(encrypt(json_encode(array("userid"=>intval($userid),'openid'=>$data['openid'],"uniqid"=>$uniqid)),C("SESSION_PASSPORT_AUTH")));
                self::setCache(md5(intval($userid))."__uid__",intval($userid),DT_CACHE_TIME);
                self::setCache(md5(intval($userid))."__level__",1,DT_CACHE_TIME);
                self::setCache(md5(intval($userid))."__username__",base_decode($data['nickname']),DT_CACHE_TIME);
                self::setCache(md5(intval($userid))."__auth__",$token,DT_CACHE_TIME);
                self::setCache(md5(intval($userid))."__uniqid__",$uniqid,DT_CACHE_TIME);
                self::setCache(md5(intval($userid))."__openid__",$data['openid'],DT_CACHE_TIME);
                return array("status_code" => 0, "status_msg" => "登陆成功！", "token" => $token, "userid" => intval($userid),"username" => base_decode($data['nickname']),"expire"=>C('EXPIRE_TIME'));
            }else{
                D("User")->rollbackTrans();
                return array("status_code" => 1, "status_msg" => "登陆失败！", "token" => "", "userid" => 0,"username" => base_decode($data['nickname']),"expire"=>C('EXPIRE_TIME'));
            }
        }else{
            /*$rs = D("User")->GetInfo("`openid`='".$data['openid']."'");
            if(empty($rs)){
                return array("status_code"=>100001,"status_msg"=>"账号不存在，登陆失败!");
            }*/
            $uniqid = uniqid();
            //更新登录时间
            D("User")->Edit("`userid`=".intval($info['userid']),array("logintime"=>time(),"loginip"=>get_client_ip()));
            $token = base_encode(encrypt(json_encode(array("userid"=>intval($info["userid"]),'openid'=>$info['openid'],"uniqid"=>$uniqid)),C("SESSION_PASSPORT_AUTH")));
            self::setCache(md5(intval($info['userid']))."__uid__",$info['userid'],DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__level__",$info['groupid'],DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__username__",base_decode($info['nickname']),DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__auth__",$token,DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__uniqid__",$uniqid,DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__openid__",$info['openid'],DT_CACHE_TIME);
            return array("status_code" => 0, "status_msg" => "登陆成功！", "token" => $token, "userid" => $info['userid'],"username" => base_decode($info['nickname']),"expire"=>C('EXPIRE_TIME'));
        }
    }


    /**
     * 手机+验证码登录
     * @param $master
     * @param $code
     * @param $openid
     */
    static public function login_mobile($master,$code,$openid){
        if(!Validation::IsMobileNumber($master)){
            return array("status_code" => 10003, "status_msg" => "登录账号只允许手机号码");
        }
        if(empty($code)){
            return array("status_code" => 10004, "status_msg" => "手机验证码为空");
        }
        if(empty($openid)){
            return array("status_code" => 10005, "status_msg" => "授权信息有误");
        }
        if(!self::setCache($master)){
            return array("status_code" => 10006, "status_msg" => "验证码已过期");
        }
        if(self::setCache($master) != $code){
            return array("status_code" => 10007, "status_msg" => "验证码不正确");
        }
        $info = D("User")->GetInfo("`openid`='".$openid."' AND `role_type`=1");
        if(empty($info)){
            return array("status_code" => 10008, "status_msg" => "用户授权信息不存在");
        }
        if(empty($info['username'])){
            $rs = D("User")->Edit("`openid`='".$openid."'",array("username"=>$master));
        }else{
            if($info['username'] != $master){
                return array("status_code" => 10009, "status_msg" => "用户登录账户有误");
            }
            $rs = true;
        }
        if($rs){
            $uniqid = uniqid();
            //更新登录时间
            D("User")->Edit("`userid`=".intval($info['userid']),array("logintime"=>time(),"loginip"=>get_client_ip()));
            $token = base_encode(encrypt(json_encode(array("userid"=>intval($info["userid"]),'openid'=>$info['openid'],"uniqid"=>$uniqid)),C("SESSION_PASSPORT_AUTH")));
            self::setCache(md5(intval($info['userid']))."__uid__",$info['userid'],DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__level__",$info['groupid'],DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__username__",base_decode($info['nickname']),DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__auth__",$token,DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__uniqid__",$uniqid,DT_CACHE_TIME);
            self::setCache(md5(intval($info['userid']))."__openid__",$info['openid'],DT_CACHE_TIME);
            return array("status_code" => 0, "status_msg" => "登陆成功！", "token" => $token, "userid" => $info['userid'],"username" => base_decode($info['nickname']),"expire"=>C('EXPIRE_TIME'));
        }else{
            return array("status_code" => 1, "status_msg" => "登陆失败！", "token" => "", "userid" => 0,"username" => base_decode($data['nickname']),"expire"=>C('EXPIRE_TIME'));
        }
    }

    /**
     *
     * 功能:退出
     */
    static public function logout($token){
        self::setCache(md5(intval($token['userid']))."__uid__","");
        self::setCache(md5(intval($token['userid']))."__level__","");
        self::setCache(md5(intval($token['userid']))."__username__","");
        self::setCache(md5(intval($token['userid']))."__auth__","");
        self::setCache(md5(intval($token['userid']))."__uniqid__","");
        self::setCache(md5(intval($token['userid']))."__openid__","");
        return true;
    }


    /**
     * 获取授权用户ID
     */
    static public function getID(){
        $token = decrypt(base_decode($_SERVER['HTTP_TOKEN']),C("SESSION_PASSPORT_AUTH"));
        $token = json_decode($token,true);
        return intval(self::getCache(md5(intval($token['userid']))."__uid__"));
    }

    /**
     * 获取授权用户等级ID
     */
    static public function getLevelID(){
        $token = decrypt(base_decode($_SERVER['HTTP_TOKEN']),C("SESSION_PASSPORT_AUTH"));
        $token = json_decode($token,true);
        return intval(self::getCache(md5(intval($token['userid']))."__level__"));
        //return 14;
    }

    /**
     * 获取授权信息
     */
    static public function getAuth(){
        $token = decrypt(base_decode($_SERVER['HTTP_TOKEN']),C("SESSION_PASSPORT_AUTH"));
        $token = json_decode($token,true);
        return self::getCache(md5(intval($token['userid']))."__auth__");
    }

    /**
     * 获取登录uniqid
     */
    static public function getUniqid(){
        $token = decrypt(base_decode($_SERVER['HTTP_TOKEN']),C("SESSION_PASSPORT_AUTH"));
        $token = json_decode($token,true);
        return self::getCache((md5(intval($token['userid']))."__uniqid__"));
    }

    /**
     * 获取微信openid
     */
    static public function getWechatOpenid(){
        $token = decrypt(base_decode($_SERVER['HTTP_TOKEN']),C("SESSION_PASSPORT_AUTH"));
        $token = json_decode($token,true);
        return self::getCache((md5(intval($token['userid']))."__openid__"));
    }


    /**
     * 获取用户名
     * @return mixed
     */
    static public function getUserName(){
        $token = decrypt(base_decode($_SERVER['HTTP_TOKEN']),C("SESSION_PASSPORT_AUTH"));
        $token = json_decode($token,true);
        return self::getCache(md5(intval($token['userid']))."__username__");
        //return "邹生";
    }

    /**
     * 设置缓存信息
     * @param $key
     * @param $val
     * @param int $expire
     */
    static function setCache($key,$val,$expire=DT_CACHE_TIME){
        //session(array("name"=>$key,'path'=>"/","expire"=>$expire));
        //session($key,$val);
        //cookie($key,$val,$expire);
        //$_SESSION[$key] = $val;
        if(empty($key)){
            return false;
        }
        if(is_array($val)){
            $val = implode(",",$val);
        }
        $Cache = \Think\Cache::getInstance("file");
        return $Cache->set($key,$val,$expire);
    }



    /**
     * 获得缓存信息
     * @param $key
     * @return int|mixed
     */
    static public function getCache($key){
        //return session($key) ? session($key) : 0;
        //return cookie($key) ? cookie($key) : 0;
        //return $_SESSION[$key] ? $_SESSION[$key] : 0;
        if(empty($key)){
            return;
        }
        $Cache = \Think\Cache::getInstance("file");
        return $Cache->get($key);
    }

    /**
     * MD5加密
     * 参数:$str
     * 返回:密文
     */
    static public function mixPass($str) {
        //return substr ( md5 ( $str ), 5, 16 );
        return md5($str);
    }

    /**
     * 设置支付密码
     * @param $str
     * @return bool|string
     */
    static public function mixAccountPass($str){
        return substr ( md5 ( $str ), 5, 16 );
    }
}
?>