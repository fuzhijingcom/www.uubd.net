<?php
namespace Home\Controller;

class TradeController extends StockbaseController{

    //导出销售详情表
    public function exportTradeInfo() {
        header('Content-Type:text/html;charset=utf8');
        $data = I('exportParams', '', 'trim,strip_tags');
        $array = json_decode($data,true);

        $where = "1";
        //起始时间
        $start_time = $array['start_time'].' 00:00:00';
        //结束时间
        $end_time = $array['end_time'].' 23:59:59';
        //获取订单状态
        $status = getTradeStatus($array['status']);
        //导出表格
        $exportAll = $array['exportAll'];
        //是否只导出交易过的商品
        $only_trade = $array['only_trade'];

        if($status === false){
            echo "<script>alert('订单状态超出范围！');history.back(-1)</script>";
            return false;
        }

        if($array['classify'] != ''){
            $classify = array();
            foreach($array['classify'] as $key => $value){
                if($value==1){
                    //获取查询的眼镜类别
                    $key = getGoodsNameByVariable($key);
                    $classify[$key] = $value;
                }
            }
        }
        if(!$classify){
            echo "<script>alert('请选择要导出的眼镜类型！');history.back(-1);</script>";
            return false;
        }

        //拼接时间范围条件
        $where .= " and td.created > '{$start_time}' and td.created < '{$end_time}'";

        if($status !== true){
            //拼接订单状态条件
            $where .= " and td.status in ('{$status}')";
        }else{
            $where .= " and td.status != '{$status[0]}' and td.status != '{$status[1]}'";
        }

        $classify_txt = '';
        foreach($classify as $key => $value){
            $classify_txt .= "'".$key . "',";
        }

        $classify_txt = rtrim($classify_txt,',');

        if($classify_txt != ''){
            //拼接 分类 条件
            $where .= " and il.category in ($classify_txt )";
        }

        $fields = array(
            'il.num_iid'    =>  'num_iid',
            'il.title'      =>  'title',
            'il.category'   =>  'classify',
            'sg.selling_id' =>  'selling_id',
            'sga.style_full' => 'style_full',
            'il.style'      =>  'style',
            'sg.goods_id'   =>  'goods_id',
            's.quantity'    =>  'quantity',
            'sga.is_listing' =>  'is_listing',
            'SUM(itl.sold_num)'  =>  'sold_num',
            'SUM(itl.total_fee)' =>  'total_price',

        );

        //查询交易过的商品
        $ItemTradeListModel = M('ItemList');
        $result = $ItemTradeListModel ->alias('il')
            ->field($fields)
            ->join('left join item_trade_list as itl on il.id = itl.item_id')
            ->join('left join tradedetail as td on itl.tid = td.tid')
            ->join('left join stock_goods_attr as sga on il.attr_id = sga.attr_id')
            ->join('left join stock as s on sga.attr_id = s.attr_id')
            ->join('left join stock_goods as sg on sga.g_id = sg.g_id')
            ->join('left join stock_category as sc on sg.cat_id = sc.cat_id')
            ->where($where)
            ->group('sga.attr_id')
            ->select();

        $sql = $ItemTradeListModel->getLastSql();
        // echo $result;exit;
        if(!$result){
            echo "<script>alert('无符合条件的数据！');history.back(-1);</script>";
            return false;
        }

        if(!$only_trade){
            $where = "il.category != '测试' and il.category != '补差价' and il.category != '加工费'";

            $classify = array();
            foreach($array['classify'] as $key => $value){
                if($value==1){
                    //获取查询的眼镜类别
                    $key = getGoodsNameByVariable($key);
                    $classify[$key] = $value;
                }
            }
            $classify_txt = '';
            foreach($classify as $key => $value){
                $classify_txt .= "'".$key . "',";
            }

            $classify_txt = rtrim($classify_txt,',');

            if($classify_txt != ''){
                //拼接 分类 条件
                $where .= " and il.category in ($classify_txt)";
            }


            $fields1 = array(
                'il.num_iid'    =>  'num_iid',
                'il.title'      =>  'title',
                'il.category'   =>  'classify',
                'sg.selling_id' =>  'selling_id',
                'il.style'      =>  'style',
                'sga.style_full'=>'style_full',
                'sg.goods_id'   =>  'goods_id',
                's.quantity'    =>  'quantity',
                'sga.is_listing' =>  'is_listing',
            );
            //查询所有商品
            $result1 = $ItemTradeListModel ->alias('il')
                ->field($fields1)
                ->join('left join stock_goods_attr as sga on il.attr_id = sga.attr_id')
                ->join('left join stock s on sga.attr_id = s.attr_id')
                ->join('left join stock_goods as sg on sga.g_id = sg.g_id')
                ->join('left join stock_category as sc on sg.cat_id = sc.cat_id')
                ->where($where)
                ->group('sga.attr_id')
                ->select();

            foreach($result as $key => $value){
                foreach ($result1 as $key1 => $value1){
                    if($value['selling_id'].$value['style'] == $value1['selling_id'] .$value1['style']){
                        unset($result1[$key1]);
                        $result1[] = $value;
                    }
                }
            }
            unset($result);
            $result = $result1;
        }

        $data = array();
        if(!$exportAll){
            //导出状态为 true ，导出集成在一张表不分工作区
            foreach ($result as $key => $value){
                $data[] = array(
                    'num_iid' => $value['num_iid'],
                    'goods_style_full' => $value['selling_id'].$value['style_full'],
                    'sold_num' => $value['sold_num']?$value['sold_num']:0,
                    'title' => $value['title'],
                    'classify' => $value['classify'],
                    'selling_id' => $value['selling_id'],
                    'style' => $value['style'],
                    'goods_id'=>$value['goods_id'],
                    'quantity' => empty($value['quantity'])?'0':$value['quantity'],
                    'is_listing' => empty($value['is_listing'])?'否':$value['is_listing']==''?'否':'是',
                    'total_price'=>$value['total_price']?$value['total_price']:'0',

                );
            }
        }else{
            //导出状态为 false ，导出在一张表，按分类区分工作区
            foreach ($result as $key => $value){
                $data[$value['classify']][$key] = array(
                    'num_iid' => $value['num_iid'],
                    'goods_style_full'=>$value['selling_id'].$value['style_full'],
                    'sold_num' => $value['sold_num']?$value['sold_num']:0,
                    'title' => $value['title'],
                    'classify' => $value['classify'],
                    'selling_id' => $value['selling_id'],
                    'style' => $value['style'],
                    'goods_id'=>$value['goods_id'],
                    'quantity' => empty($value['quantity'])?'0':$value['quantity'],
                    'is_listing' => empty($value['is_listing'])?'否':$value['is_listing']==''?'否':'是',
                    'total_price'=>$value['total_price']?$value['total_price']:'0',

                );
            }
        }

        //设置文件名
        $fileName = '销售详情表';
        //设置表头
        $headArr = array('商品编号','商品标识','商品总销量','商品名称','商品分类','线上编号','商品款式','生产编号','商品库存量','是否在售','商品销售总额');

        //设置单元格格式
        $format = array(
            'align'  => array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1', 'J1','K1'),
            'length' => array('A' => 15, 'B' => 15, 'C' => 15, 'D' => 60, 'E' => 15, 'F' => 15,'G' => 15,'H' => 15 ,'I' => 15 ,'J' => 20,'K' => 20),
        );

        //导出
        if(!$exportAll){
            $sheetName = '销售详情';
            exportExcel($fileName,$headArr,$data,$sheetName,$format);
        }else{
            exportExcelPlus($fileName,$headArr,$data,$format);
        }

    }
}
