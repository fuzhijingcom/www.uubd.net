<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class StockbaseController extends HomebaseController{
    /**
     * 无限极分类
     * @param array $data 数据源
     * @param int $pid 父ID
     * @param int $level 缩进
     * @return array
     */
    protected function _getTree($data,$pid = 0,$level = 1){
        static $list = array();
        foreach($data as $k => $v){
            if($v['parent_id'] == $pid){
                $v['level'] = $level;
                $list[] = $v;
                $this->_getTree($data,$v['cat_id'],$level+1);
            }
        }
        return $list;
    }

    protected function _getGoodsStockStock($attr_id){
        $Stock = M('Stock');
        $field = array(
            'SUM(quantity)' => 'quantity',
        );
        $stockInfo = $Stock->field($field)->where(array('attr_id'=>$attr_id))->find();
        return $stockInfo['quantity'];
    }

    public function getCatIdsByCatId($cat_id){
        $StockCategoryModel = M('StockCategory');
        $where = array('parent_id'=>$cat_id);
        $data = $StockCategoryModel -> where($where) -> select();
        if($data){
            $arr = '';
            foreach ($data as $k => $v){
                $arr .= $v['cat_id'].',';
            }
            $arr = trim($arr,',');
            return $arr;
        }else{
            return $cat_id;
        }

    }

    protected function _getGoodsIdByGoodsName($goods_name){
        $StockGoods = M('StockGoods');
        $goodsInfo = $StockGoods->where(array('goods_name'=>array('like','%'.$goods_name.'%')))->find();
        return $goodsInfo['goods_id'];
    }

    /**
     * 根据分类ID获取 该分类的表头
     * @param $cat_id
     * @return mixed
     */
    public function getFieldById($cat_id){
        /*$StockCategoryModel = D('StockCategory');
        $data = $StockCategoryModel->getTopParentNameByCatId($cat_id);
        dump($data);die;*/

        $Stock = D('Stock');
        $cat_name = $Stock->getTopParentNameByCatId($cat_id);

        $field['框架眼镜'] = array(
            //框架眼镜和太阳镜需要的字段
            's.s_id' => 's_id',
            'g.goods_id' => 'goods_id',
            'g.selling_id' => 'selling_id',
            'sga.style' => 'style',
            's.quantity' => 'quantity',
            'g.single_price' => 'single_price',
            'sga.is_listing' => 'is_listing',
            's.operate_time' => 'operate_time',
            'CONCAT(sga.style_full,s.attr_id,s.w_id)' => 'unique_id',
            'SUM(itl.sold_num)' => 'total_trade',
            'sga.style_full'=>'style_full',
            'sw.w_name'=>'position',
            'sga.attr_id'=>'attr_id',
            'ss.sup_name'=>'sup_name',
        );
        $field['太阳镜'] = array(
            //框架眼镜和太阳镜需要的字段
            's.s_id' => 's_id',
            'g.goods_id' => 'goods_id',
            'g.selling_id' => 'selling_id',
            'sga.style' => 'style',
            's.quantity' => 'quantity',
            'g.single_price' => 'single_price',
            'sga.is_listing' => 'is_listing',
            's.operate_time' => 'operate_time',
            'CONCAT(sga.style_full,s.attr_id,s.w_id)' => 'unique_id',
            'SUM(itl.sold_num)' => 'total_trade',
            'sga.style_full'=>'style_full',
            'sw.w_name'=>'position',
            'sga.attr_id'=>'attr_id',
            'ss.sup_name'=>'sup_name',
        );
        //隐形眼镜：商城编号，商品名称，商品品牌（产品品牌，非供货来源），商品类型（框架眼镜，还是太阳镜等），是否定制产品，含水量，价格，库存数量，单位，总销量，更新时间

        $field['隐形眼镜'] = array(
            //隐形眼镜所需字段
            's.s_id'=>'s_id',
            'g.selling_id'=>'selling_id',
            'g.goods_id'=>'product_name',
            'g.goods_name'=>'product_name',
            'sb.brand_name'=>'brand',
            'sc.cat_name'=>'cat_name',
            'sga.attr_custom'=>'use_time',
            'sga.attr_water'=>'water',
            'sga.attr_unit'=>'per',
            'sga.attr_degree'=>'attr_degree',
            's.quantity'=>'quantity',
            'sga.attr_price'=>'attr_price',
            'g.single_price'=>'single_price',
            'sga.is_listing'=>'product_stock_type',
            'SUM(itl.sold_num)' => 'total_trade',
            'CONCAT(sga.style_full,s.attr_id,s.w_id)' => 'unique_id',
            's.operate_time'=>'operate_time',
            'sga.style_full'=>'style_full',
            'sw.w_name'=>'position',
            'sga.attr_id'=>'attr_id',
            'ss.sup_name'=>'sup_name',
        );
        //护理液：商城编号，商品名称，商品品牌（产品品牌，非供货来源），商品类型（框架眼镜，还是太阳镜等），开封后更新周期，价格，库存数量，总销量，更新时间

        $field['护理液、润眼液、洗眼液'] = array(
            //护理液所需字段
            'g.selling_id'=>'selling_id',
            'g.goods_id'=>'goods_id',
            'g.goods_name'=>'product_name',
            'sb.brand_name'=>'brand',
            'sc.cat_name'=>'cat_name',
            'sga.attr_lifetime'=>'left_time',
            'g.single_price'=>'single_price',
            's.quantity'=>'quantity',
            'sga.is_listing'=>'product_stock_type',
            'SUM(itl.sold_num)' => 'total_trade',
            's.operate_time'=>'operate_time',
            'CONCAT(sga.style_full,s.attr_id,s.w_id)' => 'unique_id',
            'sga.style_full'=>'style_full',
            'sw.w_name'=>'position',
            'sga.attr_id'=>'attr_id',
            'ss.sup_name'=>'sup_name',
        );

        //功能眼镜：商城编号，生产编号，色系（全名），商品类型（框架眼镜，还是太阳镜等），库存数量，单品价格，上下架状态，总销量，更新时间

        $field['功能眼镜'] = array(
            'g.selling_id' => 'selling_id',
            'g.goods_id'=>'goods_id',
            'g.goods_name'=>'product_name',
            'sga.style_full'=>'style_full',
            'sc.cat_name'=>'cat_name',
            's.quantity'=>'quantity',
            'g.single_price'=>'single_price',
            'sga.is_listing'=>'product_stock_type',
            'SUM(itl.sold_num)' => 'total_trade',
            's.operate_time'=>'operate_time',
            'CONCAT(sga.style_full,s.attr_id,s.w_id)' => 'unique_id',
            'sw.w_name'=>'position',
            'sga.attr_id'=>'attr_id',
            'ss.sup_name'=>'sup_name',
        );

        if(array_key_exists($cat_name,$field)){
            return $field[$cat_name];

        }else{
            exit("找不到该分类对应的内容！");
        }

    }

    /**
     * 【根据分类ID获取表头和填充数据】
     * @param $cat_id
     * @param $value
     * @return mixed
     */
    public function getTitleById($cat_id,$value){
        $StockModel = D('Stock');
        $cat_name = $StockModel->getTopParentNameByCatId($cat_id);

        foreach ($value as $k => $v) {
            if ($cat_name == '框架眼镜') {
                $arr['框架眼镜'][] = array(
                    's_id' => $v['s_id'],
                    'attr_id'=>$v['attr_id'],
                    's_info' => array(
                        'goods_id' => $v['goods_id'],
                        'selling_id' => $v['selling_id'],
                        'style' => $v['style_full'],
                        'quantity' => $v['quantity'],
                        'single_price' => $v['single_price'],
                        'total_trade' => $v['total_trade'],
                        'product_stock_type' => $v['is_listing'],
                        'operate_time' => $v['operate_time'],
                        'sup_name' => $v['sup_name'],
                        'style_full' => $v['style_full'],
                        'location'=>$v['position'],

                    )
                );
            } else if ($cat_name == '太阳镜') {
                $arr['太阳镜'][] = array(
                    's_id' => $v['s_id'],
                    'attr_id'=>$v['attr_id'],
                    's_info' => array(
                        'goods_id' => $v['goods_id'],
                        'selling_id' => $v['selling_id'],
                        'style' => $v['style'],
                        'quantity' => $v['quantity'],
                        'single_price' => $v['single_price'],
                        'total_trade' => $v['total_trade'],
                        'product_stock_type' => $v['is_listing'],
                        'operate_time' => $v['operate_time'],
                        'sup_name' => $v['sup_name'],
                        'style_full' => $v['style_full'],
                        'location'=>$v['position'],
                    )
                );
            } else if ($cat_name == '隐形眼镜') {
                $arr['隐形眼镜'][] = array(
                    's_id' => $v['s_id'],
                    'attr_id'=>$v['attr_id'],
                    //商城编号，商品名称，商品品牌（产品品牌，非供货来源），商品类型（框架眼镜，还是太阳镜等），是否定制产品，含水量，价格，库存数量，单位，总销量，更新时间
                    's_info' => array(
                        'goods_id'=>$v['product_name'],
                        'selling_id' => $v['selling_id'],
                        'product_name' => $v['product_name'],
                        'brand' => $v['brand'],
                        'cat_name'=>$v['cat_name'],
                        'attr_custom'=>$v['use_time'],
                        'water' => $v['water'],
                        'single_price' => $v['single_price'],
                        'quantity' => $v['quantity'],
                        'per' => $v['per'],
                        'attr_degree'=>$v['attr_degree']?$v['attr_degree']:'0度',
                        'product_stock_type' => $v['product_stock_type'],
                        'total_trade' => $v['total_trade'],
                        'operate_time' => $v['operate_time'],
                        'location'=>$v['position'],
                        'sup_name' => $v['sup_name'],
                    )
                );
            } else if ($cat_name == '护理液、润眼液、洗眼液') {
                $arr['护理液、润眼液、洗眼液'][] = array(
                    's_id' => $v['s_id'],
                    'attr_id'=>$v['attr_id'],
                    //商城编号，商品名称，商品品牌（产品品牌，非供货来源），商品类型（框架眼镜，还是太阳镜等），开封后更新周期，价格，库存数量，总销量，更新时间
                    's_info' => array(
                        'goods_id'=>$v['product_name'],
                        'selling_id' => $v['selling_id'],
                        'product_name' => $v['product_name'],
                        'brand' => $v['brand'],
                        'cat_name'=>$v['cat_name'],
                        'left_time' => $v['left_time'],
                        'use_time'=>$v['use_time'],
                        'single_price' => $v['single_price'],
                        'quantity' => $v['quantity'],
                        'product_stock_type' => $v['product_stock_type'],
                        'total_trade' => $v['total_trade'],
                        'operate_time' => $v['operate_time'],
                        'location'=>$v['position'],
                        'sup_name' => $v['sup_name'],
                    )
                );
            }else if($cat_name == '功能眼镜'){
                //商城编号，生产编号，色系（全名），商品类型（框架眼镜，还是太阳镜等），库存数量，单品价格，上下架状态，总销量，更新时间
                $arr['功能眼镜'][] = array(
                    's_id' => $v['s_id'],
                    'attr_id'=>$v['attr_id'],
                    's_info' => array(
                        'selling_id' => $v['selling_id'],
                        'goods_id' => $v['goods_id'],
                        'style_full' => $v['style_full'],
                        'cat_name'=>$v['cat_name'],
                        'quantity' => $v['quantity'],
                        'single_price' => $v['single_price'],
                        'product_name' => $v['product_name'],
                        'left_time' => $v['left_time'],
                        'product_stock_type' => $v['product_stock_type'],
                        'total_trade' => $v['total_trade'],
                        'operate_time' => $v['operate_time'],
                        'location'=>$v['position'],
                        'sup_name' => $v['sup_name'],
                    )
                );
             }
        }
        return $arr[$cat_name];
    }

    /**
     * 【根据仓库Id获取仓库名】
     * @param $w_id
     * @return mixed
     */
    protected function getLogToName($w_id){
        $StockWareHouseModel = M('StockWarehouse');
        $wareInfo = $StockWareHouseModel->where(array('w_id'=>$w_id))->find();
        return $wareInfo['w_name'];
    }

    /**
     * 计算某款式的销量
     * @param $selling_id
     * @param $style_full
     * @return mixed
     */
    protected function getSoldNum($selling_id, $style_full) {
        $field = array(
            //隐形眼镜所需字段
            'SUM(itl.sold_num)' => 'sold_num',
            'CONCAT(sga.style_full,s.attr_id)' => 'unique_id',
        );

        $map = array(
            'g.selling_id' => $selling_id,
            'sga.style_full' => $style_full,
        );

        $Stock = D('Stock');
        $data = $Stock->getStockToDB($map, '', '', $field);

        return intval($data[0]['sold_num']);
    }
}
