<?php
/**
 * 根据有赞接口,处理用户相关信息的方法
 */
namespace Lib\Youzan;

if (!defined('YOUZAN_FUNCTION_ROOT')) {
    define('YOUZAN_FUNCTION_ROOT', dirname(__FILE__) . '/');
    require(YOUZAN_FUNCTION_ROOT . 'functions.php');
}

class Customer{
    /**
     * 获取有赞用户信息
     * @param $subscriber_info
     * @return array
     */
    public function getYouzanInfo($subscriber_info){
        /** 实例化有赞接口类 */
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();
        $params = array(
            'weixin_openid' => $subscriber_info['weixin_openid'],
        );
        
        /** 调用有赞获取单个用户数据的接口 */
        $yz_info = $youzan->getYouzanUserInfo($params);

        /** 判断接口返回结果 */
        if($yz_info['error_response']){
            // 接口返回错误结果
            // 添加日志记录错误
            $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'Association subscriber'.PHP_EOL.var_export($subscriber_info,true);
            wxlogg('Yz_interface_error', __METHOD__, $log_data);
            // 特殊的user_id 方便后续排查
            $subscriber_info['user_id'] = 12345666; 
        }else{
            // 接口返回正确结果
            $subscriber_info['user_id'] = $yz_info['response']['user']['user_id'];
        }
        return $subscriber_info;
    }
}