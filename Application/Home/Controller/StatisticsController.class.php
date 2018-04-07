<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class StatisticsController extends HomebaseController {

    /**
     * 查询订单已完成的订单的商品信息！
     */
    public function getSoldGoods() {
        $statistics = new \Lib\Youzan\Trades();

        $start_created = '2016-07-07 10:00:00';
        $end_created = '2016-07-27 10:00:00';
        header('Content-type:text/html;charset=utf8');
        $result = $statistics->getFishedSoldGoods($start_created, $end_created);
        foreach($result as $value){
            dump($value);
        }
    }

    /**
     * 导出交易记录
     */
    public function printTrade() {
        $statistics = new \Lib\Youzan\Trades();

        $result = $statistics->getTrades();

        echo '<pre>';
        header('Content-type:text/html;charset=utf8');
        foreach($result as $v){
            dump($v);die;
        }
    }

    /**
     * 根据 月份 或 天数 获取商品销售记录表并导出Excel
     */
    public function printGoods() {
        $times = I('get.times', '', 'trim,strip_tags,htmlspecialchars');
        $time = getTime($times);

        $start_created = $time['start'];
        $end_created = $time['end'];

        if(!$start_created || !$end_created){
            exit("<script>alert('参数错误！');history.back(-1);</script>");
        }
        $trade = new \Lib\Youzan\Trades();
        $result = $trade->getFishedSoldGoods($start_created, $end_created);
        if(!$result){
            exit("<script>alert('无符合条件的数据！');history.back(-1);</script>");
        }
        $fileName = $start_created.'-'.$end_created.' 销售表';
        $headArr = array('商品编号','商品总销量','商品SKU','商品名称','线上编号','商品款式','商品售价','商品实际购买价','商品数量','是否在售','商品销售总额');
        $data = array();
        foreach ($result as $value) {
            $data[] = array(
                'num_iid'   =>  $value['num_iid'],
                'sold_num'  =>  $value['sold_num'],
                'sku_id'    =>  $value['sku_id'],
                'title'     =>  $value['title'],
                'selling_id'=>  $value['selling_id'],
                'style'     =>  $value['style'],
                'price'     =>  $value['price_str'],
                'payment'   =>  $value['payment_str'],
                'quantity'  =>  $value['quantity'],
                'is_listing'=>  $value['is_listing']=='1'?'是':'否',
                'total_fee' =>  $value['total_fee'],
            );
        }
        $format = array(
            'align'  => array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1','H1','I1','J1','K1','L1'),
            'length' => array('A' => 15, 'B' => 15, 'C' => 15, 'D' => 60, 'E' => 45, 'F' => 35,'G' => 10,'H' => 20,'I' => 10, 'J' => 10 , 'K' => 15),
        );
        $sheetName = 'statisticsSheet';
        exportExcel($fileName,$headArr,$data,$sheetName,$format);
    }
}