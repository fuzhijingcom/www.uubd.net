<?php

namespace Common\Controller;
use Common\Controller\AppframeController;

class WeixinbaseController extends AppframeController {
    function _initialize() {
        header("Content-type:text/html;charset=utf-8");

        //微信页面登录初始化
        $this->assign("waitSecond", 1);

        //目前情况：在微信情况下：只有session('weixin_openid')
        //现在增加session('user')
        $weixin_openid = session('weixin_openid');
        if(!$weixin_openid || !session('customer')){
            $this->wxlogin();
        }
       
        
    }


    // public function _empty() {
    //     $this->error('页面不存在！');
    //     exit;
    // }


    public function wxlogin(){
        $appid = AppID;
        $appsecret = AppSecret;
        if(!$appid || !$appsecret){
            $this->error("请检查配置文件，appid和appsecret不能为空");
        }

        $login = new \Common\Login();
        $userinfo = $login->UserInfo(); 

        $weixin_openid = $userinfo['openid'];
        session('weixin_openid',$weixin_openid);
        //保存openid

        //从数据库查找顾客信息
        $customer = D('Customer');
        $info = $customer->getCustomerInfoByOpenId($weixin_openid);
        if(!$info){
            //不存在，新用户，注册
            $subscriber_info['weixin_openid'] = $userinfo['openid'];
            $subscriber_info['avatar'] = $userinfo['head_pic'];
            $subscriber_info['sex'] = $userinfo['sex'];
            $subscriber_info['nick'] = $userinfo['nickname'];
            $res = $customer->addNewCustomer($subscriber_info);
            if($res['sign'] == 1){
                $info = $customer->getCustomerInfoByOpenId($weixin_openid);
                //历史原因，更新user_id
                $this->updateUser_id($info['id']);
                session('customer',$info);
            }
            if($res['sign'] == -1){
                $this->error("add new customer error");
            }
        }else{
            session('customer',$info);
        }

       

    }
   
    public function updateUser_id($id){
        M('customer')->where(array('id'=>$id))->setField(array('user_id'=>$id));
    }

}