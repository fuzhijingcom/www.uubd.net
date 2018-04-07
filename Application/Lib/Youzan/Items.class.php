<?php
/**
 * 商品信息处理——调用有赞接口实现
 */
namespace Lib\Youzan;

if (!defined('YOUZAN_FUNCTION_ROOT')) {
    define('YOUZAN_FUNCTION_ROOT', dirname(__FILE__) . '/');
    require(YOUZAN_FUNCTION_ROOT . 'functions.php');
}

class Items {
    public function getItemInfo($num_iid) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'num_iid' => $num_iid,
        );

        return $youzan->getItem($params);
    }
}