<?php
/**
 * 商品信息处理——调用有赞接口实现
 */
namespace Lib\Youzan;

if (!defined('YOUZAN_FUNCTION_ROOT')) {
    define('YOUZAN_FUNCTION_ROOT', dirname(__FILE__) . '/');
    require(YOUZAN_FUNCTION_ROOT . 'functions.php');
}

class Activity {

    /**
     * 核销优惠码的核销码
     * @param $couponCode
     * @return mixed
     */
    public function checkCouponCode($couponCode) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'code' => $couponCode,
        );
        outputDebugLog($params,8);

        return $youzan->checkCouponCode($params);
//        return $youzan->getCouponId($params);
    }
    
    /**
     * 核销优惠码的核销码
     * @param $couponCode
     * @return mixed
     */
    public function getCouponInfo($couponCode) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'code' => $couponCode,
        );

        return $youzan->getCouponInfo($params);
    }

    /**
     * 给用户加积分
     * @param $uid
     * @param $point
     * @return mixed
     */
    public function addPoint($uid,$point){
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'points' => $point,
            'kdt_id' => '14848545',
            'fans_id' => $uid,
        );
        outputDebugLog($params,8);

        return $youzan->addPoint($params);
    }

    /**
     * 获取店铺信息:
     * @return mixed
     */
    public function getKid(){
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();
        
        return $youzan->getKid();
    }

    /**
     * 获取所有未结束的优惠列表
     * @return mixed
     */
    public function getCouponList(){
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        return $youzan->getCouponList();
    }
    
    
    public function getYouzanUserInfo($openid){
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();
        $params = array(
//            'weixin_openid' => $openid,
            'user_id' => $openid,
        );

        return $youzan->getYouzanUserInfo($params);
    }
}