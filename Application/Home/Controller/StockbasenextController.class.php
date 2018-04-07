<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class StockbasenextController extends HomebaseController{
    /**
     * 写入历史日志方法
     * 传入的数组结构如下：
     * $array = array(
     *     'sku_id' => $sku_id,
     *     'origin_value' => $origin_value,
     *     'new_value' => $new_value,
     *     'update_type' => $update_type,
     *     'update_event' => $update_event,
     *     'update_user' => $update_user,
     * );
     * @param $array
     * @return bool|mixed
     */
    protected function writeHistory($array) {
        $data = array();

        if (isset($array['sku_id'])) {
            $data['sku_id'] = $array['sku_id'];
        } else {
            return false;
        }

        $data['origin_value'] = isset($array['origin_value']) ? $array['origin_value'] : '';
        $data['new_value'] = isset($array['new_value']) ? $array['new_value'] : '';
        $data['update_type'] = isset($array['update_type']) ? $array['update_type'] : '';
        $data['update_event'] = isset($array['update_event']) ? $array['update_event'] : '';
        $data['operate_user'] = isset($array['update_user']) ? $array['update_user'] : '';

        $model = M('stock_history_next');

        $result = $model->add($data);

        return $result;
    }

    /**
     * 根据分类编号获取库存一览所需的字段信息
     * @param $cat_id
     * @return bool|mixed
     */
    protected function getField($cat_id) {
        $arr = array(
            'K' => 'sku_id,selling_id,goods_name,price,sum(quantity) quantity,is_listing,is_replenish,supplier,procurement_price,brand,warehouse,product_id,style,update_time',
            'T' => 'sku_id,selling_id,goods_name,price,sum(quantity) quantity,is_listing,is_replenish,supplier,procurement_price,brand,warehouse,product_id,style,update_time',
            'G' => 'sku_id,selling_id,goods_name,price,sum(quantity) quantity,is_listing,is_replenish,supplier,procurement_price,brand,warehouse,product_id,style,degree,update_time',
            'Y' => 'sku_id,selling_id,goods_name,price,sum(quantity) quantity,is_listing,is_replenish,supplier,procurement_price,brand,warehouse,degree,custom,water,update_time',
            'H' => 'sku_id,selling_id,goods_name,price,sum(quantity) quantity,is_listing,is_replenish,supplier,procurement_price,brand,warehouse,update_time',
        );

        if (array_key_exists($cat_id, $arr)) {
            return $arr[$cat_id];
        } else {
            return false;
        }
    }

    /**
     * 根据仓库值获取名称
     * @param $w_id
     * @return bool|mixed
     */
    protected function getWarehouseName($w_id) {
        // TODO: 以后将写进配置文件中
        $wh_arr = array(
            '-1' => '汇总',
            '1' => '总仓',
            '2' => '新天地门店',
            '3' => '广大门店',
            '4' => '南亭门店',
            '5' => '科贸门店',
        );

        if (array_key_exists($w_id, $wh_arr)) {
            return $wh_arr[$w_id];
        } else {
            return false;
        }
    }

    /**
     * 根据商品的 sku_id 获取需要实例化的模型（数据表）
     * @param $sku_id
     * @return bool|mixed
     */
    protected function getGoodsModel($sku_id) {
        $category = strtoupper(substr($sku_id, 0, 1));

        return $this->getGoodsModelByCat($category);
    }

    /**
     * 根据商品分类（商城编号的首字母）获取需要实例化的模型
     * @param $cat_id
     * @return bool|mixed
     */
    protected function getGoodsModelByCat($cat_id) {
        // TODO: 此数组以后存储在配置文件中
        $goods_arr = array(
            'K' => 'stock_goods_frame',
            'T' => 'stock_goods_sunglasses',
            'G' => 'stock_goods_functional',
            'Y' => 'stock_goods_contact',
            'H' => 'stock_goods_liquid',
        );


        if (array_key_exists($cat_id, $goods_arr)) {
            return $goods_arr[$cat_id];
        } else {
            return false;
        }
    }

    /**
     * 根据商品分类标志获取分类名称
     * @param $cat_id
     * @return bool|mixed
     */
    protected function getCategoryByCat($cat_id) {
        // TODO: 此数组以后存储在配置文件中
        $cat_arr = array(
            'K' => '框架眼镜',
            'T' => '太阳镜',
            'G' => '功能眼镜',
            'Y' => '隐形眼镜',
            'H' => '护理相关',
        );


        if (array_key_exists($cat_id, $cat_arr)) {
            return $cat_arr[$cat_id];
        } else {
            return false;
        }
    }

    /**
     * 根据英文名称找到对应的 cat_id 编号
     * @param $str
     * @return mixed|string
     */
    protected function getCatIdByStr($str) {
        $array = array(
            'frame'             =>  'K',
            'sunglasses'        =>  'T',
            'function_glasses'  =>  'G',
            'contact_lens'      =>  'Y',
            'liquid'            =>  'H',
            'lens'              =>  'J',  // 镜片
            'others'            =>  'Q',  // 其他
        );

        if(array_key_exists($str,$array)){
            return $array[$str];
        } else {
            return 'Q';
        }
    }

    /**
     * 根据商品 sku_id 判断商品是否属于有赞上销售的商品
     * 在有赞上销售的商品有三类：框架眼镜（K）,太阳镜（T）,功能眼镜（G）
     *
     * @param $sku_id
     * @return bool
     */
    protected function GoodsOnYouzan($sku_id) {
        $category = strtoupper(strstr($sku_id, 0, 1));

        if ($category === 'K' ||
            $category === 'T' ||
            $category === 'G') {

            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断仓库是否为总仓
     * @param $warehouse
     * @return bool
     */
    protected function isMainStock($warehouse) {
        if ($warehouse === '总仓') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 根据 sku_id 获取 num_iid
     * @param $sku_id
     * @return string
     */
    protected function getNumiid($sku_id) {
        if ($this->GoodsOnYouzan($sku_id)) {
            $model = $this->getGoodsModel($sku_id);

            $map = array(
                'sg.sku_id' => $sku_id,
            );

            $field = array(
                'sy.num_iid' => 'num_iid',
            );

            $NumIid = M($model);

            $result = $NumIid->alias('sg')
                ->join('inner join stock_youzan sy sg.selling_id = sy.selling_id')
                ->where($map)->field($field)->find();

            if ($result) {
                return $result['num_iid'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 根据 sku_id 获取特定商品表的所需商品信息
     * @param $sku_id
     * @return mixed
     */
    protected function getInfoBySkuId($sku_id) {
        $model = $this->getGoodsModel($sku_id);

        if ($model !== false) {
            $Info = M($model);

            $map = array(
                'sku_id' => $sku_id,
            );

            return $Info->where($map)->find();
        } else {
            return array();
        }
    }


    /**
     * 获取指定商品的指定周期内的销量
     * 第一个参数的规格如下：
     * $array = array(
     *     'sku_id' => 'K6401',
     * )
     *
     * @param $array
     * @return bool
     * @param $array
     * @param array $period
     * @return bool
     */
    protected function getSoldNum($array, $period = array()) {
        if (isset($array['sku_id'])) {
            $sku_id = $array['sku_id'];
        } else {
            // 商品不明确
            return false;
        }

        $fields = array(
            'SUM(itl.sold_num)'  =>  'sold_num',
        );


        $where = '1';
        $where .= " and il.stock_sku_id='{$sku_id}'";

        // 获取指定周期
        if (! empty($period)) {
            $start = $period['start'];
            $end = $period['end'];

            //拼接时间范围条件
            $where .= " and td.created > '{$start}' and td.created < '{$end}'";
        }

        $ItemTradeListModel = M('ItemList');
        $result = $ItemTradeListModel ->alias('il')
            ->field($fields)
            ->join('left join item_trade_list as itl on il.id = itl.item_id')
            ->join('left join tradedetail as td on itl.tid = td.tid')
            ->where($where)
            ->group('il.stock_sku_id, il.title')
            ->select();

        if ($result) {
            return $this->getSum($result);
        } else {
            return 0;
        }
    }

    /**
     * 对 getSoldNum 里面数据库返回的数组内的值求和
     *
     * @param $arr
     * @return int
     */
    protected function getSum($arr) {
        $sum = 0;

        foreach ($arr as $value) {
            $sum += intval($value['sold_num']);
        }

        return $sum;
    }

    /**
     * 传入天数计算往前天数，来计算指定商品的销量
     *
     * 第一个参数的规格如下：
     * $array = array(
     *     'sku_id' => 'K6401',
     * )
     *
     * @param $array
     * @param $days
     * @return bool
     */
    protected function getSoldNumDays($array, $days) {
        $days = intval($days);
        $start = date('Y-m-d 00:00:00', strtotime('-' . $days . 'days'));
        $end = date('Y-m-d H:i:s');

        $period = array(
            'start' => $start,
            'end' => $end,
        );

        return $this->getSoldNum($array, $period);
    }

    /**
     * 根据商品类型获取 Excel 表头信息
     * @param $cat_id
     * @return mixed
     */
    protected function getExportExcelHeadArrByCatId($cat_id){
        $headArr['K'] = array(
            '线上编码',
            '产品型号',
            '款型',
            '款型全名',
            '库存',
            '单品价格',
            '状态',
            '总销量',
            '生产厂家',
        );
        $headArr['T'] = array(
            '线上编码',
            '产品型号',
            '款型',
            '款型全名',
            '库存',
            '单品价格',
            '状态',
            '总销量',
            '生产厂家',
        );
        //商城编号，生产编号，色系（全名），商品类型（框架眼镜，还是太阳镜等），库存数量，单品价格，上下架状态，总销量，更新时间
        $headArr['G'] = array(
            '线上编码',
            '生产编号',
            '款型全名',
            '商品类型',
            '库存',
            '单品价格',
            '状态',
            '总销量',
        );
        //商城编号，商品名称，商品品牌（产品品牌，非供货来源），商品类型（框架眼镜，还是太阳镜等），是否定制产品，含水量，价格，库存数量，单位，总销量，更新时间
        $headArr['Y'] = array(
            '线上编码',
            '商品名称',
            '品牌',
            '商品类型',
            '定制',
            '含水量',
            '价格',
            '状态',
            '库存',
            '总销量',
        );
        //商城编号，商品名称，商品品牌（产品品牌，非供货来源），商品类型（框架眼镜，还是太阳镜等），开封后更新周期，价格，库存数量，总销量，更新时间
        $headArr['H'] = array(
            '线上编码',
            '商品名称',
            '品牌',
            '商品类型',
            '单品价格',
            '状态',
            '库存',
            '总销量',
        );

        if (array_key_exists($cat_id, $headArr)) {
            return $headArr[$cat_id];
        } else {
            return false;
        }
    }
}
