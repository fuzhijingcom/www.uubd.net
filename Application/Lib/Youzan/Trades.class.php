<?php
/**
 * 订单处理——调用有赞接口实现
 */
namespace Lib\Youzan;

if (!defined('YOUZAN_FUNCTION_ROOT')) {
    define('YOUZAN_FUNCTION_ROOT', dirname(__FILE__) . '/');
    require(YOUZAN_FUNCTION_ROOT . 'functions.php');
}

class Trades {
    private $_fields;

    public function __construct() {
        // 设置获取的默认字段
        $this->_fields = 'tid,status,type,created,update_time,pay_time,pay_type,orders,created';
    }

    /**
     * 获取一条订单的具体内容
     * @param $tid
     * @param string $fields
     * @return mixed
     */
    public function getTrade($tid, $fields = '') {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'tid' => $tid
        );

        if (! empty($fields)) {
            $params['fields'] = $fields;
        }

        return $youzan->getTrade($params);
    }

    /**
     * 获取交易单中的商品列表
     * @param string $start_created 订单创建时间
     * @param string $end_created   订单结束时间
     * @param string $fields        指定返回字段
     * @param array $options        更多参数（待拓展）
     * @return array|bool           返回数组或 false
     */
    public function getTrades($start_created = '', $end_created = '', $fields = '', $options = array()) {
        set_time_limit(0);
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array();

        if (! empty($options) && is_array($options)) {
            $params = $options;
        }

        if (! empty($start_created)) {
            $params['start_created'] = $start_created;
        }

        if (! empty($end_created)) {
            $params['end_created'] = $end_created;
        }

        if (! empty($fields)) {
            $params['fields'] = $fields;
        }

        $params['page_no'] = 1;
        $params['page_size'] = 100;
        $params['use_has_next'] = true;

        $result = array();

        do {
            $get_result = $youzan->getTradesSold($params);

            if (isset($get_result['response'])) {
                $result[] = $get_result['response']['trades'];

                ++ $params['page_no'];
            } else {
                return false;
            }
        } while ($get_result['response']['has_next']);


        $return_data = array();
        foreach ($result as $trades) {
            foreach($trades as $trade) {
                $return_data[] = $trade;
            }
        }

        return $return_data;
    }

    public function getFishedSoldGoods($start_created = '', $end_created = '', $fields = '') {
        set_time_limit(0);
        $stock = new \Lib\Youzan\Stock();

        $fields = (empty($fields)) ? $this->_fields : $fields;

        $get_trades = $this->getTrades($start_created, $end_created, $fields);

        $goods = array();

        foreach ($get_trades as $trade) {

            //获取订单交易状态
            $status = getTradeStatusFromYouzan($trade['status']);

            if ($status) {
                foreach ($trade['orders'] as $order) {
                    $sku_unique_code = $order['sku_unique_code'];

                    $num_iid = $order['num_iid'];

                    if (empty($sku_unique_code)) {
                        $sku_unique_code = $num_iid;
                    }

                    $sold_num = intval($order['num']);
                    $sku_id = $order['sku_id'];
                    $title = $order['title'];
                    $selling_id = getSellingId($title);
                    //获取一特定SKU的商品下的所有库存量和款式
                    $info = $stock->getQuantityAndStyleFromSku($num_iid, $sku_id);
                    $style = $info['style'];
                    $quantity = $info['quantity'];
                    $is_listing = $info['is_listing'];

                    $sku_properties_name = $order['sku_properties_name'];

                    if (empty($style)) {
                        $style = $sku_properties_name;
                    }

                    $price_str = $order['price'];
                    $price = floatval($price_str);
                    $payment_str = $order['payment'];
                    $payment = floatval($payment_str);

                    if (isset($goods[$sku_unique_code])) {
                        $total_fee = $sold_num * $payment + $goods[$sku_unique_code]['total_fee'];

                        $sold_num = $goods[$sku_unique_code]['sold_num'] + $sold_num;

                        $goods[$sku_unique_code]['sold_num'] = $sold_num;
                        $goods[$sku_unique_code]['total_fee'] = $total_fee;

                        if (! isset($goods[$sku_unique_code]['price_arr'][$price_str])) {
                            $goods[$sku_unique_code]['price_arr'][$price_str] = $price_str;
                            $goods[$sku_unique_code]['price_str'] .= '|' . $price_str;
                        }

                        if (! isset($goods[$sku_unique_code]['payment_arr'][$payment_str])) {
                            $goods[$sku_unique_code]['payment_arr'][$payment_str] = $payment_str;
                            $goods[$sku_unique_code]['payment_str'] .= '|' . $payment_str;
                        }

                    } else {
                        $total_fee = $sold_num * $payment;

                        $goods[$sku_unique_code] = array(
                            'num_iid' => $num_iid,
                            'sold_num' => $sold_num,
                            'sku_id' => $sku_id,
                            'title' => $title,
                            'selling_id' => $selling_id,
                            'style' => $style,
                            'price' => $price,
                            'payment' => $payment,
                            'quantity' => $quantity,
                            'is_listing' => $is_listing,
                            'total_fee' => $total_fee,

                            'price_str' => $price_str,
                            'price_arr' => array(
                                $price_str => $price_str,
                            ),

                            'payment_str' => $payment_str,
                            'payment_arr' => array(
                                $payment_str => $payment_str,
                            )
                        );
                    }

                }
                unset($order);
            }
        }
        return $goods;
    }

    public function getFishedSoldGoodsList($start_created = '', $end_created = '') {
        $stock = new \Lib\Youzan\Stock();

        $fields = 'tid,status,type,created,update_time,pay_time,pay_type,orders,created';

        $get_trades = $this->getTrades($start_created, $end_created, $fields);

        $goods = array();

        foreach ($get_trades as $trade) {
            $status = getTradeStatusFromYouzan($trade['status']);

            if ($status) {
                $tid = $trade['tid'];
                $created = $trade['created'];

                foreach ($trade['orders'] as $order) {
                    $sku_unique_code = $order['sku_unique_code'];

                    $num_iid = $order['num_iid'];

                    if (empty($sku_unique_code)) {
                        $sku_unique_code = $num_iid;
                    }

                    $sold_num = intval($order['num']);
                    $sku_id = $order['sku_id'];
                    $title = $order['title'];
                    $selling_id = getSellingId($title);
                    $goods_classify = getGoodsClassify($title);

                    $info = $stock->getQuantityAndStyleFromSku($num_iid, $sku_id);
                    $style = $info['style'];
                    $quantity = $info['quantity'];
                    $is_listing = $info['is_listing'];


                    $sku_properties_name = $order['sku_properties_name'];

                    if (empty($style)) {
                        $style = $sku_properties_name;
                    }

                    $price_str = $order['price'];
                    $price = floatval($price_str);
                    $payment_str = $order['payment'];
                    $payment = floatval($payment_str);


                    $total_fee = $sold_num * $payment;

                    $goods[] = array(
                        'tid' => $tid,
                        'num_iid' => $num_iid,
                        'sold_num' => $sold_num,
                        'sku_id' => $sku_id,
                        'title' => $title,
                        'selling_id' => $selling_id,
                        'style' => $style,
                        'goods_classify' => $goods_classify,
                        'price' => $price,
                        'payment' => $payment,
                        'quantity' => $quantity,
                        'is_listing' => $is_listing,
                        'total_fee' => $total_fee,
                        'sku_unique_code' => $sku_unique_code,
                        'created' => $created,
                    );
                }

                unset($order);
            }
        }
        return $goods;
    }
}