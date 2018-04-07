<?php
namespace Home\Model;
use Think\Model;

class StocknextModel extends Model {
    private $stock_config;

    public function __construct() {
        parent::__construct();

        $this->initConfig();
    }

    /**
     * 初始化配置信息
     */
    private function initConfig() {
        $this->stock_config = $GLOBALS['stock_config'];
    }

    /**
     * 对外扣库存接口（不关联有赞）
     *
     * 传入必要参数后，此扣库存操作就可以对相应商品表的库存数量变更，
     * 并将变更记录写入到历史记录表中
     *
     * @param $sku_id           string  商品的唯一标志值 sku_id，如 K6001C1, G6401C2D300
     * @param $warehouse        string  仓库名，包括：总仓，新天地门店，南亭门店，广大门店，科贸门店 等
     * @param $delta_quantity   int     扣减数量，输入值为正整数
     * @param $event            string  扣减理由
     * @return bool|array               操作成功，返回 true，否则返回错误提示数组
     */
    public function haircutAmount($sku_id, $warehouse, $delta_quantity, $event = '正常销售') {
        // 返回值，默认为 true
        $success = true;

        $update_user = '__auto__'; // 默认操作是程序自动扣减

        $goods_info = $this->getInfoBySkuId($sku_id, $warehouse);

        if (empty($goods_info)) {
            $result = array(
                'sign' => -1,
                'msg' => '数据表中不存在该商品！'
            );

            return $result;
        }

        $quantity_old = intval($goods_info['quantity']);
        $delta_quantity = intval($delta_quantity);
        $quantity = $quantity_old - $delta_quantity;

        if ($quantity < 0) {
            $result = array(
                'sign' => -1,
                'msg' => "扣减失败，扣减后数量不能为负数！",
            );

            return $result;
        }

        // 获取需要更新的数据库
        $model = $this->getGoodsModel($sku_id);

        if ($model === false) {
            $result = array(
                'sign' => -1,
                'msg' => "{$sku_id} 此 sku_id 值找不到对应的商品数据表！",
            );

            return $result;
        }

        $Stock = M($model);

        $map = array(
            'sku_id' => $sku_id,
            'warehouse' => $warehouse,
        );

        $data = array(
            'quantity' => $quantity,
            'update_user' => $update_user,
        );

        $db_result = $Stock->where($map)->setField($data);
        if ($db_result === false) {
            $result = array(
                'sign' => -1,
                'msg' => '库存数量更新到数据库失败！'
            );

            return $result;
        }

        // 写入到操作变更记录表
        $update_type = '数量变更';
        $origin_value = "{$warehouse} 原库存数量:{$quantity_old}";
        $new_value = "{$warehouse} 新库存数量:{$quantity}";
        $comment = $event;

        $log_data = array(
            'sku_id' => $sku_id,
            'origin_value' => $origin_value,
            'new_value' => $new_value,
            'update_type' => $update_type,
            'update_event' => $comment,
            'update_user' => $update_user,
        );

        $write_result = $this->writeHistory($log_data);

        if ($write_result === false) {
            $result = array(
                'sign' => -1,
                'msg' => '写入库存操作记录数据表失败！'
            );

            return $result;
        }

        // 成功，返回 true
        return $success;
    }

    /**
     * 库存数量变更到有赞
     *
     * 且对有赞商城上库存（若商品在有赞的话）更新，
     *
     * @param $sku_id
     * @param $warehouse
     * @param $delta_quantity
     * @return bool|array               操作成功，返回 true，否则返回错误提示数组
     */
    public function haircutYouzan($sku_id, $warehouse, $delta_quantity) {
        // 返回值，默认为 true
        $success = true;

        $goods_info = $this->getInfoBySkuId($sku_id, $warehouse);

        if (empty($goods_info)) {
            $result = array(
                'sign' => -1,
                'msg' => '数据表中不存在该商品！'
            );

            return $result;
        }

        $quantity = intval($goods_info['quantity']);

        // 将数据更新到有赞
        // 仅框架眼镜、太阳镜、功能眼镜，且涉及到总仓时才需要将数据同步到有赞
        if (C('YOUZAN_DEBUG') === false) {
            if ($this->GoodsOnYouzan($sku_id)) {
                // 要传递的变更数据
                if (C('YOUZAN_DATA_TEST') === false) {
                    // 实际获取的数据
                    $num_iid = $this->getNumiid($sku_id);
                    $style = $goods_info['style'];
                } else {
                    // 此处填入测试专用商品 id
                    $num_iid = 293684019;
                    $style = 'C11';
                }

                // 先过滤掉泳镜判断
                // '255660080', // 泳镜，对应线上商品号为 66901
                if (intval($num_iid) === 255660080) {
                    return true;
                }

                // 仅当 $num_iid 存在时才对有赞进行相关操作
                if ($num_iid !== false) {
                    $youzan_stock = new \Lib\Youzan\Stock();

                    if ($this->isMainStock($warehouse)) {
                        // 传递库存数量的绝对值
                        $youzan_result = $youzan_stock->changeItemQuantity($num_iid, $style, $quantity);

                        if ($youzan_result === false) {
                            $result = array(
                                'sign' => -1,
                                'msg' => '库存数量更新到有赞失败！'
                            );

                            return $result;
                        }
                    }
                }
            }
        }

        return $success;
    }


    /**
     * 商品销售信息记录接口
     * @param & $data
     * @return mixed
     *
     * 调用方法（在 ThinkPHP 内调用）：
     *
     * $StockNext = new \Home\Model\StocknextModel();
     * $res = $StockNext->saveSoldInfo($data );
     *
     * 其中传入参数 $data 是一个数组，结构如下：
     * ```
     * $data = array(
     *     'tid' => 'E1234567', //订单号，一个数组里面一个订单号
     *
     *     // 下面记录订单号下的全部商品销售信息，
     *     // sold_list 对应的是一个二维数组，内部可以是任意个数商品信息
     *     'sold_list' => array(
     *         array(
     *             'sku_id' => 'K1001C1',
     *             'price' => 166.00, // 单品原价
     *             'payment' => 100.00, // 单品实际售价
     *             'sold_num' => 2, // 销售数量
     *             'total_payment' => 200.00, // 该订单下此商品的实际销售总价
     *         ),
     *         array(
     *             'sku_id' => 'H11001',
     *             'price' => 400.00,
     *             'payment' => 300.00,
     *             'sold_num' => 3,
     *             'total_payment' => 900.00,
     *         ),
     *     )
     * );
     *
     * 返回结果为：
     * 成功：true
     * 失败：返回一个数组，结构如下：
     * array(
     *    'sign' => -1,
     *    'msg' => '保存销售信息失败！'
     * );
     *
     */
    public function saveSoldInfo(& $data) {
        outputDebugLog($data,8);
        $flag = true; // 默认为真，若本方法全操作无报错，则保持为 true

        // 若数据格式正确，则往下操作，将数据插入到数据表中
        if (is_array($data)) {
            if (! isset($data['tid'])) {
                $err = array(
                    'sign' => -1,
                    'msg' => '传入数据规格有误，找不到订单号！',
                );

                return $err;
            }

            if (! isset($data['sold_list']) || ! is_array($data['sold_list'])) {
                $err = array(
                    'sign' => -1,
                    'msg' => '传入数据规格有误，销售信息无法读取！',
                );

                return $err;
            }

            $tid = & $data['tid'];

            // 循环将数据插入表
            foreach ($data['sold_list'] as $value) {
                $result = $this->insertTradeList($tid, $value['sku_id'], $value['sold_num'], $value['price'], $value['payment'], $value['total_payment']);

                if ($result === false) {
                    $flag = false;
                }
            }
            unset($value);

        } else {
            $err = array(
                'sign' => -1,
                'msg' => '传入数据结构有误！',
            );

            return $err;
        }

        if (! $flag) {
            $err = array(
                'sign' => -1,
                'msg' => '数据插入表时出现错误！',
            );

            return $err;
        } else {
            return true;
        }
    }

    /**
     * 插入数据到 item_list 和 item_trade_list 表中
     * @param $tid
     * @param $sku_id
     * @param $sold_num
     * @param int $price
     * @param int $payment
     * @param int $total_payment
     * @return bool|mixed
     */
    private function insertTradeList($tid, $sku_id, $sold_num, $price = 0, $payment = 0, $total_payment = 0) {
        if (empty($sku_id)) {
            return false;
        }

        $ItemList = M('item_list');
        $ItemTradeList = M('item_trade_list');

        $sku_id_map['stock_sku_id'] = $sku_id;
        $sku_id_result = $ItemList->where($sku_id_map)->find();

        if ($sku_id_result) {
            $item_id = $sku_id_result['id'];
        } else {
            $goods_info = $this->getInfoBySkuId($sku_id);

            if (empty($goods_info)) {
                return false;
            } else {
                $num_iid = $this->getNumiid($sku_id);
                $title = $goods_info['goods_name'];
                $category = $this->getCategoryByCat(substr($sku_id, 0, 1));
                $style = isset($goods_info['style']) ? $goods_info['style'] : '';
                $price = $goods_info['price'];

                $item_data = array(
                    'stock_sku_id' => $sku_id,
                    'num_iid' => $num_iid,
                    'sku_unique_code' => $sku_id,
                    'title' => $title,
                    'category' => $category,
                    'style' => $style,
                    'price' => $price,
                );

                $item_id = $ItemList->add($item_data);
            }

        }

        // 插入到 item_trade_list 表
        if ($item_id) {
            $trade_data = array(
                'item_id' => $item_id,
                'sku_unique_code' => $sku_id,
                'tid' => $tid,
                'price' => $price,
                'sold_num' => $sold_num,
                'total_fee' => $total_payment,
                'payment' => $payment,
            );

            return $ItemTradeList->add($trade_data);
        } else {
            return false;
        }
    }

    /**
     * 查询特定商品在特定仓库的库存数量接口
     *
     * @param $sku_id           string  商品的唯一标志值 sku_id，如 K6001C1, G6401C2D300
     * @param $warehouse        string  仓库名，包括：总仓，新天地门店，南亭门店，广大门店，科贸门店 等，默认选定总仓数量
     * @return array|int        正常情况下返回库存数量，错误情况返回提示数组
     */
    public function getQuantity($sku_id, $warehouse = '总仓') {
        $goods_info = $this->getInfoBySkuId($sku_id, $warehouse);

        if (empty($goods_info)) {
            $result = array(
                'sign' => -1,
                'msg' => '数据表中不存在该商品！'
            );

            return $result;
        }

        return intval($goods_info['quantity']);
    }

    /**
     * 查询特定商品在特定仓库的库存数量（返回数量，因为不存在的话，数量就是0）
     *
     * @param $sku_id
     * @param string $warehouse
     * @return int
     */
    public function getQuantityInt($sku_id, $warehouse = '总仓') {
        $goods_info = $this->getInfoBySkuId($sku_id, $warehouse);

        if (empty($goods_info)) {
            return 0;
        }

        return intval($goods_info['quantity']);
    }

    /**
     *  查询特定商品的所有库存数量(返回数量,如果不存在的化,数量就是0)
     */
    public function getAllQuantityInt($sku_id){
        $model = $this->getGoodsModel($sku_id);

        $Info = M($model);

        $map = array(
            'sku_id' => $sku_id,
        );

        $data = $Info->where($map)->select();

        $quantity_num = 0;
        foreach($data as $k => $v){
            $quantity_num += $v['quantity'];
        }
        return $quantity_num;

    }

    /**
     * 获取仓库数据返回到前端
     * @return array
     */
    public function getWareHouseData() {
        $arr = array();
        foreach ($this->stock_config['warehouse'] as $key => $value) {
            if (intval($key) === -1) {
                continue;
            }

            $arr[] = array(
                'w_id' => $key,
                'w_name' => $value,
                'w_info' => '',
            );
        }
        unset($value);

        return $arr;
    }

    /**
     * 获取所有商品分类并赋值给前端
     * @return array
     */
    public function getCategoryData() {
        $arr = array();

        foreach ($this->stock_config['goods'] as $key => $value) {
            $arr[] = array(
                'cat_id' => $key,
                'category' => $value['name'],
                'parent_id' => '0',
                'level' => 1,
            );
        }
        unset($value);

        return $arr;
    }

    /**
     * 写入库存操作相关的历史日志方法
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
    public function writeHistory($array) {
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
    public function getStockField($cat_id) {
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
    public function getWarehouseName($w_id) {
        $wh_arr = $this->stock_config['warehouse'];

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
    public function getGoodsModel($sku_id) {
        $category = strtoupper(substr($sku_id, 0, 1));

        return $this->getGoodsModelByCat($category);
    }

    /**
     * 根据商品分类（商城编号的首字母）获取需要实例化的模型
     * @param $cat_id
     * @return bool|mixed
     */
    public function getGoodsModelByCat($cat_id) {
        if (array_key_exists($cat_id, $this->stock_config['goods'])) {
            return $this->stock_config['goods'][$cat_id]['table'];
        } else {
            return false;
        }
    }

    /**
     * 根据商品的商城编号（线上编号） selling_id 获取需要实例化的模型（数据表）
     * @param $selling_id
     * @return bool|mixed
     */
    public function getGoodsModelBySellingId($selling_id) {
        $category = strtoupper(substr($selling_id, 0, 1));
        return $this->getGoodsModelByCat($category);
    }

    /**
     * 根据商品分类标志获取分类名称
     * @param $cat_id
     * @return bool|mixed
     */
    public function getCategoryByCat($cat_id) {
        if (array_key_exists($cat_id, $this->stock_config['goods'])) {
            return $this->stock_config['goods'][$cat_id]['name'];
        } else {
            return false;
        }
    }

    /**
     * 根据英文名称找到对应的 cat_id 编号
     * @param $str
     * @return mixed|string
     */
    public function getCatIdByStr($str) {
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
    public function GoodsOnYouzan($sku_id) {
        $category = strtoupper(substr($sku_id, 0, 1));

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
    public function isMainStock($warehouse) {
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
    public function getNumiid($sku_id) {
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
                ->join('inner join stock_youzan sy on sg.selling_id = sy.selling_id')
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
     * @param $warehouse  string  仓库名称
     * @return mixed
     */
    public function getInfoBySkuId($sku_id, $warehouse = '') {
        $model = $this->getGoodsModel($sku_id);

        if ($model !== false) {
            $Info = M($model);

            $map = array(
                'sku_id' => $sku_id,
            );

            if (! empty($warehouse)) {
                $map['warehouse'] = $warehouse;
            }

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
    public function getSoldNum($array, $period = array()) {
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
    public function getSum($arr) {
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
    public function getSoldNumDays($array, $days) {
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
    public function getExportExcelHeadArrByCatId($cat_id){
        $headArr['K'] = array(
            '线上编码',
            '产品型号',
            '款型',
            '款型全名',
            '商品类型',
            '所在仓库',
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
            '商品类型',
            '所在仓库',
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
            '款型',
            '款型全名',
            '度数',
            '商品类型',
            '所在仓库',
            '库存',
            '单品价格',
            '状态',
            '总销量',
        );
        //商城编号，商品名称，商品品牌（产品品牌，非供货来源），商品类型（框架眼镜，还是太阳镜等），是否定制产品，含水量，价格，库存数量，单位，总销量，更新时间
        $headArr['Y'] = array(
            '线上编码',
            '商品名称',
            '度数',
            '品牌',
            '商品类型',
            '定制',
            '含水量',
            '价格',
            '状态',
            '所在仓库',
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
            '所在仓库',
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
