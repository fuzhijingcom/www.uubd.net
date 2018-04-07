<?php
namespace Home\Controller;

use Common\Controller\HomebaseController;

if (!defined('YOUZAN_FUNCTION_ROOT')) {
    define('YOUZAN_FUNCTION_ROOT', APP_PATH . '/Lib/Youzan/');
    require(YOUZAN_FUNCTION_ROOT . 'functions.php');
}

// class StocknextController extends StockbasenextController {
class StocknextController extends HomebaseController {
    protected $stockObj;

    private $insert_goods =array();

    public function __construct() {
        parent::__construct();
        $this->stockObj = new \Home\Model\StocknextModel();
    }

    /**
     * 此控制器默认方法
     */
    public function stock() {
        //获取操作人列表数据
        $User = M('User');
        $data = $User->where('r_id = 1 or r_id = 8')->select();
        $userData = array();
        foreach ($data as $k => $v) {
            $userData[$v['id']] = $v['name'];
        }
        $this->assign('name',json_encode($userData));

        // 获取所有商品分类并赋值给前端
        $categoryData = $this->stockObj->getCategoryData();
        $this->assign('categoryData', json_encode($categoryData));

        $date['startT'] = date('Y-m-d');
        $date['endT'] = date('Y-m-d', strtotime('next day'));
        $this->assign('date', $date);


        // 获取仓库数据
        $warehouseData = $this->stockObj->getWareHouseData();
        
        $this->assign('warehouseData',json_encode($warehouseData));

        $this->display();
    }

    public function searchStock() {
        if (IS_POST) {
            // 接收参数
            $cat_id = I('post.cat_id', '', 'trim,strip_tags');
            $search_id = I('post.cond', '', 'trim,strip_tags');
            $is_listing = I('product_stock_type', '', 'trim,strip_tags');
            // 仓库 id 值，要映射到具体的仓库名
            $w_id = I('position', '', 'trim,strip_tags');

            // 根据配置文件决定获取相应的模型
            $model = $this->stockObj->getGoodsModelByCat($cat_id);

            // 获取商品名称
            $category = $this->stockObj->getCategoryByCat($cat_id);
            $warehouse = $this->stockObj->getWarehouseName($w_id);

            // TODO: 根据配置文件获取返回的数据类型

            // 搜索条件
            $map = array();

            if (! empty($search_id)) {
                if (($cat_id === 'K' || $cat_id === 'T' || $cat_id === 'G')) {
                    $where['selling_id'] = array('like', $search_id . '%');
                    $where['product_id'] = array('like', $search_id . '%');
                    $where['_logic'] = 'or';
                    $map['_complex'] = $where;
                } else {
                    $map['selling_id'] = array('like', $search_id . '%');
                }
            }

            if ($warehouse !== '' && $warehouse !== '汇总') {
                $map['warehouse'] = $warehouse;
                $group = 'sku_id,warehouse';
            } else {
                $group = 'sku_id';
            }

            if ($is_listing !== '' && $is_listing !== '-1') {
                $map['is_listing'] = $is_listing;
            }

            $field = $this->stockObj->getStockField($cat_id);

            $order = 'is_listing desc,selling_id';

            if ($cat_id === 'K' || $cat_id == 'T') {
                $order .= ',style';
            }

            if ($cat_id === 'G') {
                $order .= ',style,degree';
            }

            if ($cat_id === 'Y') {
                $order .= ',degree';
            }


            $Goods = M($model);

            $result = $Goods->where($map)->field($field)->order($order)->group($group)->select();

            $data = array();
            foreach ($result as $value) {
                $s_info = array();

                // 目前这个返回数据是框架眼镜的类型
                $data[] = array(
                    'sku_id' => $value['sku_id'],
                    'warehouse' => $value['warehouse'],

                    's_info' => array(
                        'selling_id' => $value['selling_id'],
                        'goods_name' => $value['goods_name'],
                        'brand' => $value['brand'],
                        'category' => $category,
                        'single_price' => $value['price'],
                        'quantity' => $value['quantity'],
                        'product_stock_type' => $value['is_listing'],
                        'total_trade' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                        'operate_time' => $value['update_time'],
                        'location' => $warehouse,

                        'product_id' => isset($value['product_id']) ? $value['product_id'] : '',
                        'style_full' => isset($value['style']) ? $value['style'] : '',
                        'water' => isset($value['water']) ? $value['water'] : '',
                        // 泳镜 G6401 要判断是否有度数
                        'degree' => (isset($value['degree']) && $value['degree'] !== '') ? $value['degree'] : (($value['selling_id'] == 'G6401') ? '无度数' : ''),
                        'custom' => isset($value['custom']) ? $value['custom'] : '',
                    ),
                );
            }

            $this->ajaxReturn($data, 'JSON');
        } else {
            $error = array(
                'sign' => -1,
                'msg' => '数据请求发生错误！',
            );

            $this->ajaxReturn($error, 'JSON');
        }
    }

    /**
     * 采购入库和退货出库的条件搜索
     *
     * @param string $selling_id
     * @param string $cat_id
     * @return array
     */
    public function getSellingId($selling_id = '', $cat_id = '') {
        if (IS_POST) {
            $selling_id = I('post.selling_id', '', 'trim,strip_tags');
            $cat_id = I('post.cat_id', '', 'trim,strip_tags');
        }

        $category = $this->stockObj->getCategoryByCat($cat_id);

        // 采购入库及出库都仅针对 总仓 的商品进行操作
        $warehouse = '总仓';

        $map = array(
            'selling_id' => $selling_id,
            'warehouse' => $warehouse,
        );

        $model = $this->stockObj->getGoodsModelByCat($cat_id);
        $Stock = M($model);

        $result = $Stock->where($map)->select();

        $data = array();
        foreach ($result as $value) {
            $data[] = array(
                'sku_id' => $value['sku_id'],
                'warehouse' => $value['warehouse'],

                's_info' => array(
                    'selling_id' => $value['selling_id'],
                    'goods_name' => $value['goods_name'],
                    'brand' => $value['brand'],
                    'category' => $category,
                    'single_price' => $value['price'],
                    'quantity' => $value['quantity'],
                    'product_stock_type' => $value['is_listing'],
                    'total_trade' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                    'operate_time' => $value['update_time'],
                    'location' => $warehouse,

                    'product_id' => isset($value['product_id']) ? $value['product_id'] : '',
                    'style_full' => isset($value['style']) ? $value['style'] : '',
                    'water' => isset($value['water']) ? $value['water'] : '',
                    // 泳镜 G6401 要判断是否有度数
                    'degree' => (isset($value['degree']) && $value['degree'] !== '') ? $value['degree'] : (($value['selling_id'] == 'G6401') ? '无度数' : ''),
                    'custom' => isset($value['custom']) ? $value['custom'] : '',
                ),
            );
        }

        if (empty($data)) {
            $data = array(
                'sign' => -1,
                'msg' => '商品信息不存在！',
            );
        } 
        
        
        if (IS_POST) {
            $this->ajaxReturn($data, 'JSON');
        } else {
            return $data;
        }
    }

    public function getStockDataByDb($search_id = '', $is_listing = -1, $cat_id = 'K', $position = -1, $select = 'all') {
        $map = array();
        if (! empty($search_id)) {
            if ($select === 'all') {
                $where['product_id'] = array('like', '%' . $search_id . '%');
                $where['selling_id'] = array('like', '%' . $search_id . '%');
            } elseif ($select === 'product_id') {
                $where['product_id'] = array('like', $search_id);
            } else {
                $where['selling_id'] = array('like', $search_id);
            }

            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        if ($position != -1) {
            $warehouse = $this->stockObj->getWarehouseName($position);
            if ($warehouse !== false) {
                $map['warehouse'] = $warehouse;
            }
        }

        if ($is_listing != -1) {
            $map['is_listing'] = $is_listing;
        }

        $sort = 'selling_id ASC';

        if ($cat_id === 'K' || $cat_id === 'T' || $cat_id === 'G') {
            $sort .= ', style ASC';
        }

        if ($cat_id === 'G' || $cat_id === 'Y') {
            $sort .= ', degree ASC';
        }

        $sort .= ', update_time DESC';


        // 根据配置文件动态生成
        $model = $this->stockObj->getGoodsModelByCat($cat_id);
        $Stock = M($model);

        $result = $Stock->where($map)->order($sort)->select();

        return $result;
    }

    public function getProductId() {
        $product_id = I('post.product_id', '', 'trim,strip_tags');

        $arr = $this->getStockDataByDb($product_id, -1, 'product_id');

        $this->ajaxReturn($arr, 'JSON');
    }

    /**
     * 更新库存操作，主要是各仓库之间的调拨
     */
    public function updateStock() {
        // 框架眼镜，其他眼镜暂不处理
        // 接收前端的数据
        $sku_id = I('post.sku_id', '', 'trim,strip_tags');
        $warehouse_from_num = I('post.product_from', '', 'trim,strip_tags');
        $warehouse_from = $this->stockObj->getWarehouseName($warehouse_from_num);
        $warehouse_to_num = I('post.product_go', '', 'trim,strip_tags');
        $warehouse_to = $this->stockObj->getWarehouseName($warehouse_to_num);
        $delta_num = I('post.v_num', 0, 'trim,strip_tags');
        $delta_num = intval($delta_num);
        $new_price = I('post.new_single_price', 0.00, 'trim,strip_tags');
        $new_price = floatval($new_price);
        $comment = I('post.select_comment', '', 'trim,strip_tags');
        $update_user = I('session.userinfo')['username'];

        // 更新数据库
        $model = $this->stockObj->getGoodsModel($sku_id);
        $Stock = M($model);

        // 更新（或新增）数据有两方面
        // ①、主动方要扣减的库存
        $map_from = array(
            'sku_id' => $sku_id,
            'warehouse' => $warehouse_from,
        );
        $result_from = $Stock->where($map_from)->find(false);
        $quantity_from_old = intval($result_from['quantity']);
        $quantity_from_new = $quantity_from_old - $delta_num;

        $data_from = array(
            'quantity' => $quantity_from_new,
            'price' => $new_price,
            'update_user' => $update_user,
        );
        $Stock->where($map_from)->setField($data_from);

        // ②、被接收方的库存更新（或新增）
        // 注意：有可能被接收方的没有相应的记录，若无则新增一条记录
        $map_to = array(
            'sku_id' => $sku_id,
            'warehouse' => $warehouse_to,
        );
        $result_to = $Stock->where($map_to)->find();

        if ($result_to) {
            $quantity_to_old = intval($result_to['quantity']);
            $data_to = array(
                'quantity' => $quantity_to_old + $delta_num,
                'price' => $new_price,
                'update_user' => $update_user,
            );
            $Stock->where($map_to)->setField($data_to);
        } else {
            $quantity_to_old = 0;
            $data_to = $result_from;  // 需复制一份完整数据到新记录

            // 取消部分数据
            unset($data_to['update_time']);

            $data_to['warehouse'] = $warehouse_to;
            $data_to['quantity'] = $delta_num;
            $data_to['price'] = $new_price;
            $data_to['update_user'] = $update_user;

            $Stock->add($data_to);
        }
        $quantity_to_new = $quantity_to_old + $delta_num;

        // 将此更新操作记录到历史日志表

        // 考虑变更的内容，主要是数量和价格
        $old_price = floatval($result_from['price']);
        $delta_price = $new_price - $old_price;

        // 此标记返回前端
        // $flag 有 3 种值：
        // 0->数量和价格都改动；1->只改变数量； 2->只改动价格
        $flag = 0;

        if ($delta_num !== 0 && $delta_price !== 0.00) {
            $update_type = '数量及价格变更';
            $origin_value = "原价格:{$old_price};{$warehouse_from} 原库存数量:{$quantity_from_old};{$warehouse_to} 原库存数量:{$quantity_to_old}";
            $new_value = "新价格:{$new_price};{$warehouse_from} 新库存数量:{$quantity_from_new};{$warehouse_to} 新库存数量:{$quantity_to_new}";

            $flag = 0;
        } elseif ($delta_num !== 0) {
            $update_type = '数量变更';
            $origin_value = "{$warehouse_from} 原库存数量:{$quantity_from_old};{$warehouse_to} 原库存数量:{$quantity_to_old}";
            $new_value = "{$warehouse_from} 新库存数量:{$quantity_from_new};{$warehouse_to} 新库存数量:{$quantity_to_new}";

            $flag = 1;
        } elseif ($delta_price !== 0.00) {
            $update_type = '价格变更';
            $origin_value = "原价格：{$old_price};";
            $new_value = "新价格：{$new_price}";

            $flag = 2;
        } else {
            $update_type = '其他变更';
            $origin_value = '---';
            $new_value = '---';
        }

        $log_data = array(
            'sku_id' => $sku_id,
            'origin_value' => $origin_value,
            'new_value' => $new_value,
            'update_type' => $update_type,
            'update_event' => $comment,
            'update_user' => $update_user,
        );

        $this->stockObj->writeHistory($log_data);

        // 将数据更新到有赞
        // 仅框架眼镜、太阳镜、功能眼镜，且涉及到总仓时才需要将数据同步到有赞
        if (C('YOUZAN_DEBUG') === false) {
            if ($this->stockObj->GoodsOnYouzan($sku_id)) {
                // 要传递的变更数据
                if (C('YOUZAN_DATA_TEST') === false) {
                    // 实际获取的数据
                    $num_iid = $this->stockObj->getNumiid($sku_id);
                    $style = $result_from['style'];
                } else {
                    // 此处填入测试专用商品 id
                    $num_iid = 293684019;
                    $style = 'C11';
                }


                // 若 $num_iid 不存在，则跳过此更新有赞操作
                if ($num_iid !== false) {
                    $youzan_stock = new \Lib\Youzan\Stock();

                    if ($this->stockObj->isMainStock($warehouse_from)) {
                        // 要传递负数的数量，因为是扣减的库存量
                        $youzan_result_from = $youzan_stock->updateItem($num_iid, $style, - $delta_num, $new_price);

                        if ($youzan_result_from === false) {
                            $result = array(
                                'sign' => -1,
                                'msg' => '更新失败，库存数量不能小于 0！'
                            );

                            $this->ajaxReturn($result, 'JSON');
                        }
                    }

                    if ($this->stockObj->isMainStock($warehouse_to)) {
                        $youzan_result_to = $youzan_stock->updateItem($num_iid, $style, $delta_num, $new_price);

                        if ($youzan_result_to === false) {
                            $result = array(
                                'sign' => -1,
                                'msg' => '更新失败，库存数量不能小于 0！'
                            );

                            $this->ajaxReturn($result, 'JSON');
                        }
                    }
                }
            }
        }


        // 返回数据到前端显示
        // 返回的数据结构
        // {"num":"11", "singLe_price": "22.00", "update_time": "2016-07-25 16:55:22"}
        $info_map = array(
            'sku_id' => $sku_id,
            'warehouse' => $warehouse_from,
        );
        $return_info = $Stock->where($info_map)->find();
        $return_data = array(
            'num' => $return_info['quantity'],
            'single_price' => $return_info['price'],
            'update_time' => $return_info['update_time'],
            'flag' => $flag,
        );

        $this->ajaxReturn($return_data);
    }

    /**
     * 调整特定仓库指定属性的商品的库存数量（绝对值量，非增量）
     */
    public function adjustQuantity() {
        if (IS_POST) {
            $sku_id = I('post.sku_id', '', 'trim,strip_tags');
            $warehouse_id = I('post.warehouse_id', '', 'trim,strip_tags');
            $warehouse = $this->stockObj->getWarehouseName($warehouse_id);
            $quantity_str = I('post.quantity', '', 'trim,strip_tags');
            $quantity = intval($quantity_str);

            $goods_info = $this->stockObj->getInfoBySkuId($sku_id);
            $quantity_old = $this->stockObj->getQuantityInt($sku_id, $warehouse);

            $update_user = I('session.userinfo')['username'];

            // 更新数据库
            $model = $this->stockObj->getGoodsModel($sku_id);
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

                $this->ajaxReturn($result, 'JSON');
            }

            // 写入到操作变更记录表
            $update_type = '数量变更';
            $origin_value = "{$warehouse} 原库存数量:{$quantity_old}";
            $new_value = "{$warehouse} 新库存数量:{$quantity}";
            $comment = '调整库存数量';

            $log_data = array(
                'sku_id' => $sku_id,
                'origin_value' => $origin_value,
                'new_value' => $new_value,
                'update_type' => $update_type,
                'update_event' => $comment,
                'update_user' => $update_user,
            );

            $this->stockObj->writeHistory($log_data);

            // 将数据更新到有赞
            // 仅框架眼镜、太阳镜、功能眼镜，且涉及到总仓时才需要将数据同步到有赞
            if (C('YOUZAN_DEBUG') === false) {
                if ($this->stockObj->GoodsOnYouzan($sku_id)) {
                    // 要传递的变更数据
                    if (C('YOUZAN_DATA_TEST') === false) {
                        // 实际获取的数据
                        $num_iid = $this->stockObj->getNumiid($sku_id);
                        $style = $goods_info['style'];
                    } else {
                        // 此处填入测试专用商品 id
                        $num_iid = 293684019;
                        $style = 'C11';
                    }

                    // 仅当 $num_iid 存在时进行有赞更新操作
                    if ($num_iid !== false) {
                        $youzan_stock = new \Lib\Youzan\Stock();

                        if ($this->stockObj->isMainStock($warehouse)) {
                            // 传递库存数量的绝对值
                            $youzan_result = $youzan_stock->changeItemQuantity($num_iid, $style, $quantity);

                            if ($youzan_result === false) {
                                $result = array(
                                    'sign' => -1,
                                    'msg' => '库存数量更新到有赞失败！'
                                );

                                $this->ajaxReturn($result, 'JSON');
                            }
                        }
                    }
                }
            }

            // 返回数据到前端显示
            // 返回的数据结构
            // {"num":"11", "singLe_price": "22.00", "update_time": "2016-07-25 16:55:22"}
            $info_map = array(
                'sku_id' => $sku_id,
                'warehouse' => $warehouse,
            );
            $return_info = $Stock->where($info_map)->find();
            $return_data = array(
                'num' => $return_info['quantity'],
                'single_price' => $return_info['price'],
                'update_time' => $return_info['update_time'],
                'flag' => 1,  // 只改变数量
            );

            $this->ajaxReturn($return_data);
        }
    }

    /**
     * 商品的上下架状态变更
     */
    public function changeStockType() {
        if (IS_POST) {
            $sku_id = I('post.sku_id', '', 'trim,strip_tags');
            $change_stock_type_to = I('post.change_stock_type_to', '', 'trim,strip_tags');

            if (! empty($sku_id) &&
                $change_stock_type_to !== '') {

                $map['sku_id'] = $sku_id;

                $data = array(
                    'is_listing' => $change_stock_type_to,
                );

                // 更新数据库
                $model = $this->stockObj->getGoodsModel($sku_id);

                $Stock = M($model);

                $result = $Stock->where($map)->setField($data);

                if ($result) {
                    // 将上下架状态同步到有赞

                    if (C('YOUZAN_DEBUG') === false
                        && $this->stockObj->GoodsOnYouzan($sku_id)) {

                        $map_select = array(
                            'sku_id' => $sku_id,
                            'warehouse' => '总仓',
                        );

                        $result_select = $Stock->where($map_select)->find();
                        $style = $result_select['style'];
                        $quantity = $result_select['quantity'];

                        $num_iid = $this->stockObj->getNumiid($sku_id);

                        // 仅对 $num_iid 存在时才对有赞更新
                        if ($num_iid !== false) {
                            $youzan = new \Lib\Youzan\Stock();

                            if ($change_stock_type_to == 0) {
                                $youzan->changeListing($num_iid, $style, 0);
                            } else {
                                $youzan->changeListing($num_iid, $style, $quantity);
                            }
                        }
                    }

                    // 返回数据到前端
                    $return_data = array(
                        'sku_id' => $sku_id,
                        'product_stock_type' => $change_stock_type_to,
                    );

                    $this->ajaxReturn($return_data, 'JSON');
                } else {
                    $result = array(
                        'sign' => -1,
                        'msg' => '上下架变更操作失败！'
                    );

                    $this->ajaxReturn($result, 'JSON');
                }
            }
        }
    }

    /**
     * 查找进货提醒模块所需数据
     * 
     * $cat_id 为分类标志，如 K,T,G,Y,H 等
     */
    public function searchNotice() {
        if (IS_POST) {
            // 获取搜索条件
            $cat_id = I('post.cat_id', '', 'trim,strip_tags');
            $con = I('post.con', '', 'trim,strip_tags');
            $inbound_period_str = I('post.inbound_period', '', 'trim,strip_tags');
            $inbound_period = intval($inbound_period_str);

            if ($cat_id !== '' && $this->stockObj->getCategoryByCat($cat_id) !== false) {
                $model = $this->stockObj->getGoodsModelByCat($cat_id);
                $Goods = M($model);

                $map =array();
                if (! empty($con)) {
                    if ($cat_id === 'K' || $cat_id === 'T' || $cat_id === 'G') {
                        $where['selling_id'] = array('like', '%' . $con . '%');
                        $where['product_id'] = array('like', '%' . $con . '%');
                        $where['_logic'] = 'or';
                        $map['_complex'] = $where;
                    } else {
                        $map['selling_id'] = array('like', '%' . $con . '%');
                    }
                }

                // 只读取总仓的数据
                $map['warehouse'] = '总仓';


                $result = $Goods->where($map)->select();

                $sales_data = array();
                $guide_data = array();

                foreach ($result as $key => $value) {
                    $arr = array(
                        'sku_id' => $value['sku_id'],
                    );

                    // 获取往前的 3、7、15 天的销量
                    $three_day_count = $this->stockObj->getSoldNumDays($arr, 3);
                    $seven_day_count = $this->stockObj->getSoldNumDays($arr, 7);
                    $half_month_count = $this->stockObj->getSoldNumDays($arr, 15);

                    // 计算日均销量
                    $three_average = floatval($three_day_count) / 3.0;
                    $seven_average = floatval($seven_day_count) / 7.0;
                    $half_month_average = floatval($half_month_count) / 15.0;
                    $max_average = max($three_average, $seven_average, $half_month_average);

                    $quantity = intval($value['quantity']);
                    if ($max_average > 0) {
                        $stock_left = floor($quantity / $max_average) - $inbound_period;
                        $stock_left = ($stock_left < 0) ? 0 : $stock_left;
                    } else {
                        $stock_left = -1;
                    }

                    $attr = '';
                    $value['degree'] = isset($value['degree']) ? $value['degree'] : (($value['selling_id'] == 'G6401') ? '无度数' : '');
                    if (! empty($value['style']) && ! empty($value['degree'])) {
                        $attr = $value['style'] . '-' . $value['degree'];
                    } elseif (! empty($value['style']) && empty($value['degree'])) {
                        $attr = $value['style'];
                    } elseif (empty($value['style']) && ! empty($value['degree'])) {
                        $attr = $value['degree'];
                    }


                    // 采购建议数据，展示在页面上
                    $sales_data[] = array(
                        's_id' => $key,
                        's_info' => array(
                            'selling_id' => $value['selling_id'],
                            'product_id' => $value['product_id'],
                            'goods_name' => $value['goods_name'],
                            'attr' => $attr,
                            'quantity' => $value['quantity'],
                            'three_day_count' => $three_day_count,
                            'seven_day_count' => $seven_day_count,
                            'half_month_count' => $half_month_count,
                            'inbound_day' => $stock_left,
                        ),
                    );

                    // 构建采购指南数据
                    $inbound_num = ceil(abs($max_average * 7 - $stock_left));

                    if ($stock_left >= 0 && $stock_left <= 7) {
                        $guide_data[] = array(
                            's_id' => $key,
                            's_info' => array(
                                'selling_id' => $value['selling_id'],
                                'product_id' => $value['product_id'],
                                'goods_name' => $value['goods_name'],
                                'attr' => $attr,
                                'quantity' => $value['quantity'],
                                'inbound_num' => $inbound_num,
                                'inbound_channel' => $value['supplier'],
                                'inbound_day' => $stock_left,
                            ),
                        );
                    }
                }
                unset($value);

                $return_data = array(
                    'sales_data' => $sales_data,
                    'guide_data' => $guide_data,
                );

                $this->ajaxReturn($return_data, 'JSON');
            } else {
                $result = array(
                    'sign' => -1,
                    'msg' => '商品类型不明确！'
                );

                $this->ajaxReturn($result, 'JSON');
            }
        }
    }

    // 采购入库操作
    public function intoStock() {
        if (IS_POST) {
            $cat_id = I('post.cat_id', '', 'trim,strip_tags');
            $selling_id = I('post.selling_id', '', 'trim,strip_tags');
            $product_id = I('post.product_id', '', 'trim,strip_tags');
            $style = I('post.style', '', 'trim,strip_tags');
            $goods_name = I('post.goods_name', '', 'trim,strip_tags');
            $degree = I('post.degree', '', 'trim,strip_tags');
            $delta_quantity = I('post.delta_quantity', '', 'trim,strip_tags');
            $price = I('post.price', '', 'trim,strip_tags');
            $supplier = I('post.suppslier', '', 'trim,strip_tags');
            $brand = I('post.brand', '', 'trim,strip_tags');
            // $location = I('post.location', '', 'trim,strip_tags'); // 这是仓库名
            $procurement_price = I('procurement_price', '', 'trim,strip_tags');
            $is_listing = I('post.is_listing', '', 'trim,strip_tags');
            $is_replenish = I('post.is_replenish', '', 'trim,strip_tags');

            $update_user = I('session.userinfo')['username'];

            // 拼凑 sku_id
            $style_head = getStyleHead($style);
            switch($cat_id) {
                case 'K':
                case 'T':
                    $sku_id = $selling_id . $style_head;
                    break;
                case 'G':
                    $sku_id = $selling_id . $style_head . 'D' . $degree;
                    break;
                case 'Y':
                    $sku_id = $selling_id . 'D' . $degree;
                    break;
                case 'H':
                    $sku_id = $selling_id . 'A' . $style;
                    break;
                default:
                    $sku_id = $selling_id;
            }

            if ($sku_id === '') {
                $error = array(
                    'sign' => -1,
                    'msg' => '商品分类不明确！',
                );
                $this->ajaxReturn($error, 'JSON');
            } else {
                $model = $this->stockObj->getGoodsModelByCat($cat_id);

                $Stock = M($model);

                $map = array(
                    'sku_id' => $sku_id,
                    'warehouse' => '总仓',
                );

                $result = $Stock->where($map)->find();

                $is_new_goods_flag = false;
                if ($result) {
                    $old_quantity = intval($result['quantity']);
                    $new_quantity = $old_quantity + $delta_quantity;

                    if ($new_quantity < 0) {
                        $error = array(
                            'sign' => -1,
                            'msg' => '商品库存数量不能小于0！',
                        );

                        $this->ajaxReturn($error, 'JSON');
                    }

                    // 修改库存数量
                    $data = array(
                        'quantity' => $new_quantity,
                        'update_user' => $update_user,
                    );

                    $Stock->where($map)->setField($data);

                    // 将操作记录写入操作变更表
                    $log_data = array(
                        'sku_id' => $sku_id,
                        'origin_value' => "总仓——原来的数量：{$old_quantity}",
                        'new_value' => "总仓——入库后数量：{$new_quantity}",
                        'update_type' => '商品入库',
                        'update_event' => '商品入库',
                        'update_user' => $update_user,
                    );
                    $this->stockObj->writeHistory($log_data);
                } else {
                    $selling_id_map = array(
                        'selling_id' => $selling_id,
                    );

                    $selling_id_result = $Stock->where($selling_id_map)->find();

                    $is_new_goods_flag = true;
                    if (empty($selling_id_result)) {
                        $data = array(
                            'sku_id' => $sku_id,
                            'selling_id' => $selling_id,
                            'product_id' => $product_id,
                            'goods_name' => $goods_name,
                            'style' => $style,
                            'price' => $price,
                            'quantity' => $delta_quantity,
                            'is_listing' => $is_listing,
                            'is_replenish' => $is_replenish,
                            'supplier' => $supplier,
                            'procurement_price' => $procurement_price,
                            'brand' => $brand,
                            'warehouse' => '总仓',
                            'update_user' => $update_user,
                        );
                    } else {
                        $data = $selling_id_result;

                        $data['sku_id'] = $sku_id;
                        $data['warehouse'] = '总仓';
                        $data['update_user'] = $update_user;
                        $data['quantity'] = $delta_quantity;

                        unset($data['update_time']);
                    }

                    // 根据不同类型商品载入不同信息
                    if ($cat_id === 'K' || $cat_id === 'T' || $cat_id === 'G' || $cat_id === 'H') {
                        $data['style'] = $style;
                    }

                    if ($cat_id === 'G' || $cat_id === 'Y') {
                        $data['degree'] = $degree;
                    }


                    $Stock->add($data);

                    // 将操作记录写入操作变更表
                    $log_data = array(
                        'sku_id' => $sku_id,
                        'origin_value' => '无',
                        'new_value' => "总仓——入库数量：{$delta_quantity}；入库的商品单价：{$price}",
                        'update_type' => '新品入库',
                        'update_event' => '新品入库',
                        'update_user' => $update_user,
                    );
                    $this->stockObj->writeHistory($log_data);
                }

                // 将数据更新到有赞
                // 仅框架眼镜、太阳镜、功能眼镜，且涉及到总仓时才需要将数据同步到有赞
                // if (C('YOUZAN_DEBUG') === false) {
                if (C('YOUZAN_DEBUG') === false) {
                    if ($this->stockObj->GoodsOnYouzan($sku_id)) {
                        $goods_info = $this->stockObj->getInfoBySkuId($sku_id);
                        // 要传递的变更数据
                        if (C('YOUZAN_DATA_TEST') === false) {
                            // 实际获取的数据
                            $num_iid = $this->stockObj->getNumiid($sku_id);

                            $style = $goods_info['style'];
                        } else {
                            // 此处填入测试专用商品 id
                            $num_iid = 293684019;
                            $style = 'C11';
                        }

                        if ($num_iid !== false) {
                            $youzan_stock = new \Lib\Youzan\Stock();

                            if ($is_new_goods_flag) {
                                // 要传库存的绝对数量值
                                $youzan_result = $youzan_stock->changeItemQuantity($num_iid, $style, $delta_quantity);
                            } else {
                                // 要传递库存的增量值
                                $youzan_result = $youzan_stock->updateItem($num_iid, $style, $delta_quantity);
                            }

                            if ($youzan_result === false) {
                                $result = array(
                                    'sign' => -1,
                                    'msg' => '库存数量更新到有赞失败！'
                                );

                                $this->ajaxReturn($result, 'JSON');
                            }
                        }
                    }
                }

                // 返回数据到前端
                $return_data =  $this->getSellingId($selling_id, $cat_id);

                $this->ajaxReturn($return_data, 'JSON');

            }
        }
    }

    // 出库操作
    public function outStock() {
        if (IS_POST) {
            $cat_id = I('post.cat_id', '', 'trim,strip_tags');
            $sku_id = I('post.sku_id', '', 'trim,strip_tags');
            $delta_quantity_str = I('post.delta_quantity', '', 'trim,strip_tags');
            $delta_quantity = intval($delta_quantity_str);

            // 本来不应该传入这几个数据
            $selling_id = I('post.selling_id', '', 'trim,strip_tags');
            $style = I('post.style', '', 'trim,strip_tags');
            $degree = I('post.degree', '', 'trim,strip_tags');

            if ($sku_id === '') {
                // 拼凑 sku_id
                $style_head = getStyleHead($style);
                switch($cat_id) {
                    case 'K':
                    case 'T':
                        $sku_id = $selling_id . $style_head;
                        break;
                    case 'G':
                        $sku_id = $selling_id . $style_head . 'D' . $degree;
                        break;
                    case 'Y':
                        $sku_id = $selling_id . 'D' . $degree;
                        break;
                    case 'H':
                        $sku_id = $selling_id;
                        break;
                    default:
                        $sku_id = $selling_id;
                }
            }

            $model = $this->stockObj->getGoodsModelByCat($cat_id);

            $Stock = M($model);

            $map = array(
                'sku_id' => $sku_id,
                'warehouse' => '总仓',
            );

            $result = $Stock->where($map)->find();

            if ($result) {
                $old_quantity = intval($result['quantity']);
                $new_quantity = $old_quantity - $delta_quantity;
                $update_user = I('session.userinfo')['username'];

                if ($new_quantity < 0) {
                    $error = array(
                        'sign' => -1,
                        'msg' => '商品库存数量不能小于0！',
                    );

                    $this->ajaxReturn($error, 'JSON');
                }

                // 修改库存数量
                $data = array(
                    'quantity' => $new_quantity,
                    'update_user' => $update_user,
                );

                $Stock->where($map)->setField($data);

                // 将操作记录写入操作变更表
                $log_data = array(
                    'sku_id' => $sku_id,
                    'origin_value' => "总仓——原来的数量：{$old_quantity}",
                    'new_value' => "总仓——出库后数量：{$new_quantity}",
                    'update_type' => '商品出库',
                    'update_event' => '商品出库',
                    'update_user' => $update_user,
                );
                $this->stockObj->writeHistory($log_data);


                // 将数据更新到有赞
                // 仅框架眼镜、太阳镜、功能眼镜，且涉及到总仓时才需要将数据同步到有赞
                if (C('YOUZAN_DEBUG') === false) {
                    if ($this->stockObj->GoodsOnYouzan($sku_id)) {
                        $goods_info = $this->stockObj->getInfoBySkuId($sku_id);
                        // 要传递的变更数据
                        if (C('YOUZAN_DATA_TEST') === false) {
                            // 实际获取的数据
                            $num_iid = $this->stockObj->getNumiid($sku_id);
                            $style = $goods_info['style'];
                        } else {
                            // 此处填入测试专用商品 id
                            $num_iid = 293684019;
                            $style = 'C11';
                        }

                        // 仅当 $num_iid 存在才对有赞进行相关操作
                        if ($num_iid !== false) {
                            $youzan_stock = new \Lib\Youzan\Stock();

                            // 要传递库存的增量值，此处因为是出库操作，所以为负数
                            $youzan_result = $youzan_stock->updateItem($num_iid, $style, - $delta_quantity);

                            if ($youzan_result === false) {
                                $result = array(
                                    'sign' => -1,
                                    'msg' => '库存数量更新到有赞失败！'
                                );

                                $this->ajaxReturn($result, 'JSON');
                            }
                        }
                    }
                }


                // 将数据返回到前端
                $return_data = $this->getSellingId($selling_id, $cat_id);

                $this->ajaxReturn($return_data, 'JSON');
            } else {
                $error = array(
                    'sign' => -1,
                    'msg' => '商品信息不存在！',
                );

                $this->ajaxReturn($error, 'JSON');
            }
        }
    }

    /**
     * 导出采购建议指导表格
     */
    public function expGuideTable() {
        if(IS_POST){
            $guide_data = I('post.guide_data','','trim,strip_tags,htmlspecialchars');
            $guide_data = explode('@@,',$guide_data);
            $guide_data_array = array();
            foreach($guide_data as $key => $v){
                if(empty($v)){
                    continue;
                }
                $guide_data_array[$key] = explode(',',$v);
            }

            $expTitle = date('Y-m-d').'采购指导';
            $xlsTitle = iconv('utf-8', 'gb2312', $expTitle); //文件名称
            $fileName = '采购指南--'. $_SESSION['account'] . date('_YmdHis'); //or $xlsTitle 文件名称可根据自己情况设定
            $expCellName = array('商城编号','产品型号','商品属性','当前库存','建议采购数量','进货渠道','建议补货日期');

            $cellNum = count($expCellName);
            $dataNum = count($guide_data);
            vendor('PHPExcel.PHPExcel');
            $objPHPExcel = new \PHPExcel();
            $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'E', 'G');

            for ($i = 0; $i < $cellNum; $i++) {
                $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i]);
            }
            // Miscellaneous glyphs, UTF-8
            for ($i = 0; $i < $dataNum; $i++) {
                for ($j = 0; $j < $cellNum; $j++) {
                    $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $guide_data_array[$i][$j]);
                }
            }
            header('pragma:public');
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
            header("Content-Disposition:attachment;filename=$fileName.xls"); //attachment新窗口打印inline本窗口打印
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
    }

    /**
     * 获取历史记录
     *
     * 记录表的相关商品信息通过 sku_id 关联各类商品表
     * 因商品表分散在多张表中，为了展示具体信息，经以分两个步骤：
     * 1、根据检索条件将历史表的信息查询出来
     * 2、对 1 中查询的数据遍历，根据 sku_id 具体指向的商品表补充完整信息
     *
     * 经以上两个步骤后，输出合适的格式的数据到前端
     */
    public function getHistory() {
        // 获取检索条件
        $selling_id = I('post.selling_id', '', 'trim,strip_tags');
        $product_id_search = I('post.goods_id', '', 'trim,strip_tags');
        $attr_search = I('post.style', '', 'trim,strip_tags');
        $update_type = I('post.select_comments', '', 'trim,strip_tags');
        $operate_user = I('post.operator', '', 'trim,strip_tags');
        $start_time = I('post.startT', '', 'trim,strip_tags');
        $end_time = I('post.endT', '', 'trim,strip_tags');

        $map = array();

        if (! empty($selling_id)) {
            $map['sku_id'] = array('like', $selling_id . '%');
        }

        if (! empty($update_type)) {
            $where['update_type'] = array('like', '%' . $update_type . '%');
            $where['update_event'] = array('like', '%' . $update_type . '%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        if (! empty($operate_user) && $operate_user != -1) {
            $map['operate_user'] = array('like', '%' . $operate_user . '%');
        }

        if (! empty($start_time)) {
            $start_time = date('Y-m-d H:i:s', strtotime($start_time));

            if (! empty($end_time)) {
                $end_time = date('Y-m-d H:i:s', strtotime($end_time) + 24 * 3600);
            } else {
                $end_time = date('Y-m-d H:i:s');
            }

            $map['operate_time'] = array('between', array($start_time, $end_time));
        }

        $field = 'sku_id,origin_value,new_value,update_type,update_event,operate_user,operate_time';

        $sort = 'operate_time desc';

        // 从记录表查询数据
        $History = M('stock_history_next');
        $result = $History->where($map)->field($field)->order($sort)->select();

        // 检查是否输入生产编号信息
        // 若有，则只输出匹配生产编号的记录
        // 否则，返回全部
        $product_id_search_flag = true;
        if (! empty($product_id_search)) {
            $product_id_search_flag = false;
        }

        // 检查是否输入属性信息
        // 若有，则只输出匹配属性的记录
        // 否则，返回全部
        $attr_search_flag = true;
        if (! empty($attr_search)) {
            $attr_search_flag = false;
        }

        // 将数据补充完整并输出给前端
        $data = array();

        foreach ($result as $key => $value) {
            $info = $this->stockObj->getInfoBySkuId($value['sku_id']);

            $selling_id = $info['selling_id'];
            $goods_name = $info['goods_name'];
            $style = isset($info['style']) ? $info['style'] : '';
            $product_id = isset($info['product_id']) ? $info['product_id'] : '';
            $degree = isset($info['degree']) ? $info['degree'] : '';

            if ($style !== '' && $degree !== '') {
                $goods_attr = $style . '-' . $degree;
            } elseif ($style !== '' && $degree === '') {
                $goods_attr = $style;
            } elseif ($style === '' && $degree !== '') {
                $goods_attr = $degree;
            } else {
                $goods_attr = '---';
            }

            if (! $product_id_search_flag) {
                if (strpos($product_id, $product_id_search) === false) {
                    continue;
                }
            }

            if (! $attr_search_flag) {
                if (strpos($goods_attr, $attr_search) === false) {
                    continue;
                }
            }

            $arr = array(
                'selling_id' => $selling_id,
                'goods_name' => $goods_name,
                // 'style' => $style,
                'product_id' => $product_id,
                // 'degree' => $degree,
                'goods_attr' => $goods_attr,
                'origin_value' => $value['origin_value'],
                'new_value' => $value['new_value'],
                'update_type' => $value['update_type'],
                'update_event' => $value['update_event'],
                'operate_user' => $value['operate_user'],
                'operate_time' => $value['operate_time'],
            );

            $data[] = array(
                's_id' => $key,
                's_info' => $arr,
            );
        }

        $this->ajaxReturn($data, 'JSON');
    }

    /**
     * 库存一览表
     * 导出当前数据到 excel
     */
    public function exportStockData() {
        if (IS_GET) {
            $search_id = I('get.cond', '', 'trim,strip_tags');
            $is_listing = I('get.product_stock_type', '', 'trim,strip_tags');
            $cat_id = I('get.productType', '', 'trim,strip_tags');
            $w_id = I('get.w_id', -1, 'trim,strip_tags');

            if ($cat_id === '') {
                $error = array(
                    'sign' => -1,
                    'msg' => '商品类型不明确！',
                );

                $this->ajaxReturn($error, 'JSON');
            } else {
                $result = $this->getStockDataByDb($search_id, $is_listing, $cat_id, $w_id);

                if (empty($result)) {
                    exit("<script>alert('无符合条件的数据！');history.back(-1);</script>");
                } else {
                    $file_name = $search_id . ' 库存一览表';
                    $head_arr = $this->stockObj->getExportExcelHeadArrByCatId($cat_id);
                    $data = array();

                    foreach ($result as $value) {
                        $sku_id = $value['sku_id'];
                        $selling_id = $value['selling_id'];
                        $product_id = $value['product_id'];
                        $goods_name = $value['goods_name'];
                        $style_full = $value['style'];
                        $style = getStyleHead($style_full);
                        $degree = isset($value['degree']) ? $value['degree'] : '';
                        $quantity = $value['quantity'];
                        $price = $value['price'];
                        $is_listing = ($value['is_listing'] == 1) ? '上架中' : '已下架';
                        $brand = $value['brand'];
                        $supplier = $value['supplier'];
                        $warehouse = $value['warehouse'];


                        $data['K'][] = array(
                            'selling_id' => $selling_id,
                            'product_id' => $product_id,
                            'style' => $style,
                            'style_full' => $style_full,
                            'cat_name' => '框架眼镜',
                            'warehouse' => $warehouse,
                            'quantity' => $quantity,
                            'single_price' => $price,
                            'is_listing' => $is_listing,
                            'total_sold' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                            'supplier' => $supplier,
                        );
                        $data['T'][] = array(
                            'selling_id' => $selling_id,
                            'product_id' => $product_id,
                            'style' => $style,
                            'style_full' => $style_full,
                            'cat_name' => '太阳镜',
                            'warehouse' => $warehouse,
                            'quantity' => $quantity,
                            'single_price' => $price,
                            'is_listing' => $is_listing,
                            'total_sold' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                            'supplier' => $supplier,
                        );
                        $data['G'][] = array(
                            'selling_id' => $selling_id,
                            'product_id' => $product_id,
                            'style' => $style,
                            'style_full' => $style_full,
                            'degree' => $degree,
                            'cat_name' => '功能眼镜',
                            'warehouse' => $warehouse,
                            'quantity' => $quantity,
                            'single_price' => $price,
                            'is_listing' => $is_listing,
                            'total_sold' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                            'supplier' => $supplier,
                        );
                        $data['Y'][] = array(
                            'selling_id' => $selling_id,
                            'goods_name' => $goods_name,
                            'degree' => $degree,
                            'brand' => $brand,
                            'cat_name' => '隐形眼镜',
                            'attr_custom' => '',
                            'water' => '',
                            'single_price' => $price,
                            'is_listing' => $is_listing,
                            'warehouse' => $warehouse,
                            'quantity' => $quantity,
                            'total_sold' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                        );
                        $data['H'][] = array(
                            'selling_id' => $selling_id,
                            'goods_name' => $goods_name,
                            'brand' => $brand,
                            'cat_name' => '其他商品',
                            'single_price' => $price,
                            'is_listing' => $is_listing,
                            'warehouse' => $warehouse,
                            'quantity' => $quantity,
                            'total_sold' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                        );
                    }

                    $sheet_name = $this->stockObj->getCategoryByCat($cat_id);
                    $format = array(
                        'align'  => array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1','H1','I1','J1'),
                        'length' => array('A' => 20, 'B' => 20, 'C' => 30, 'D' => 30, 'E' => 15, 'F' => 20,'G' => 15, 'H'=>15 ,'I'=>15 ,'J'=>30),
                    );
                    exportExcel($file_name, $head_arr, $data[$cat_id], $sheet_name,$format);
                }
            }
        }
    }

    /**
     * 导出销售详情表格
     */
    public function exportTradeInfo() {
        header('Content-Type:text/html;charset=utf8');
        $data = I('exportParams', '', 'trim,strip_tags');
        $array = json_decode($data,true);

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

        if($status === false) {
            echo "<script>alert('订单状态超出范围！');history.back(-1)</script>";
            return false;
        }

        $cat_arr = array();
        $cat_names = $array;
        if ($array['category'] != '') {
            foreach ($array['category'] as $key => $value) {
                if ($value == 1) {
                    //获取查询的眼镜类别
                    $cat_arr[] = $this->stockObj->getCatIdByStr($key);
                    $key_str = getGoodsNameByVariableNext($key);
                    $cat_names[$key_str] = $value;
                }
            }
            unset($value);
        }

        if(empty($cat_arr)){
            echo "<script>alert('请选择要导出的商品类型！');history.back(-1);</script>";
            return false;
        }

        $where = '1';
        //拼接时间范围条件
        $where .= " and td.created > '{$start_time}' and td.created < '{$end_time}'";

        if($status !== true){
            //拼接订单状态条件
            $where .= " and td.status in ('{$status}')";
        }else{
            $where .= " and td.status != '{$status[0]}' and td.status != '{$status[1]}'";
        }

        $classify_txt = '';
        foreach ($cat_names as $key => $value) {
            $classify_txt .= "'{$key}',";
        }
        unset($value);
        $classify_txt = rtrim($classify_txt, ',');

        if ($classify_txt !== '') {
            $where .= " and il.category in ({$classify_txt})";
        }

        $fields = array(
            'il.title'      =>  'title',
            'il.category'   =>  'category',
            'il.style'      =>  'style',
            'il.stock_sku_id' => 'stock_sku_id',
            // sg 是动态载入的商品表
            // 'sg.selling_id' =>  'selling_id',
            // 'sg.style' => 'style_full',
            // 'sg.quantity'    =>  'quantity',
            // 'sg.is_listing' =>  'is_listing',
            // 'sg.product_id' => 'product_id',
            'SUM(itl.sold_num)'  =>  'sold_num',
            'SUM(itl.total_fee)' =>  'total_price',

        );

        //查询交易过的商品
        $ItemTradeListModel = M('ItemList');
        $result = $ItemTradeListModel ->alias('il')
            ->field($fields)
            ->join('left join item_trade_list as itl on il.id = itl.item_id')
            ->join('left join tradedetail as td on itl.tid = td.tid')
            // ->join('left join ' . $goods_db . ' as sg on sg.sku_id = il.stock_sku_id')
            ->where($where)
            ->group('il.stock_sku_id, il.title')
            ->select();

        if(!$result){
            echo "<script>alert('无符合条件的数据！');history.back(-1);</script>";
            return false;
        }

//        if(!$only_trade){
//            $where = "il.category != '测试' and il.category != '补差价' and il.category != '加工费'";
//
//            $classify = array();
//            foreach($array['classify'] as $key => $value){
//                if($value==1){
//                    //获取查询的眼镜类别
//                    $key = getGoodsNameByVariableNext($key);
//                    $classify[$key] = $value;
//                }
//            }
//            $classify_txt = '';
//            foreach($classify as $key => $value){
//                $classify_txt .= "'".$key . "',";
//            }
//
//            $classify_txt = rtrim($classify_txt,',');
//
//            if($classify_txt != ''){
//                //拼接 分类 条件
//                $where .= " and il.category in ($classify_txt)";
//            }
//
//            $fields = array(
//                'il.title'      =>  'title',
//                'il.category'   =>  'classify',
//                'il.style'      =>  'style',
//                'il.stock_sku_id' => 'stock_sku_id',
//                // sg 是动态载入的商品表
//                // 'sg.selling_id' =>  'selling_id',
//                // 'sg.style' => 'style_full',
//                // 'sg.quantity'    =>  'quantity',
//                // 'sg.is_listing' =>  'is_listing',
//                // 'sg.product_id' => 'product_id',
//                'SUM(itl.sold_num)'  =>  'sold_num',
//                'SUM(itl.total_fee)' =>  'total_price',
//
//            );
//
//            $fields1 = array(
//                'il.title'      =>  'title',
//                'il.category'   =>  'classify',
//                'il.style'      =>  'style',
//            );
//            //查询所有商品
//            $result1 = $ItemTradeListModel ->alias('il')
//                ->field($fields1)
//                ->join('left join stock_goods_attr as sga on il.attr_id = sga.attr_id')
//                ->where($where)
//                ->group('sga.attr_id')
//                ->select();
//
//            foreach($result as $key => $value){
//                foreach ($result1 as $key1 => $value1){
//                    if($value['selling_id'].$value['style'] == $value1['selling_id'] .$value1['style']){
//                        unset($result1[$key1]);
//                        $result1[] = $value;
//                    }
//                }
//            }
//            unset($result);
//            $result = $result1;
//        }

        $data = array();
        if(!$exportAll){
            //导出状态为 true ，导出集成在一张表不分工作区
            foreach ($result as $key => $value){
                $stock_sku_id = $value['stock_sku_id'];
                $goods_info = $this->stockObj->getInfoBySkuId($stock_sku_id);

                $selling_id = isset($goods_info['selling_id']) ? $goods_info['selling_id'] : '';
                $title = $value['title'];
                $stock_sku_id = empty($value['stock_sku_id']) ? $title : $value['stock_sku_id'];
                $category = $value['category'];
                $style = $value['style'];
                $product_id = isset($goods_info['product_id']) ? $goods_info['product_id'] : '';
                $quantity = isset($goods_info['quantity']) ? $goods_info['quantity'] : 0;

                if (isset($goods_info['is_listing'])) {
                    if ($goods_info['is_listing'] == 1) {
                        $is_listing = '上架';
                    } else {
                        $is_listing = '下架';
                    }
                } else {
                    $is_listing = '不确定';
                }

                $sold_num = $value['sold_num'];
                $total_price = $value['total_price'];
                
                $data[] = array(
                    'num_iid' => $selling_id,
                    'stock_sku_id' => $stock_sku_id,
                    'sold_num' => $sold_num,
                    'title' => $title,
                    'category' => $category,
                    'selling_id' => $selling_id,
                    'style' => $style,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'is_listing' => $is_listing,
                    'total_price' => $total_price,

                );
            }
        }else{
            //导出状态为 false ，导出在一张表，按分类区分工作区
            foreach ($result as $key => $value){
                $stock_sku_id = $value['stock_sku_id'];
                $goods_info = $this->stockObj->getInfoBySkuId($stock_sku_id);

                $selling_id = isset($goods_info['selling_id']) ? $goods_info['selling_id'] : '';
                $title = $value['title'];
                $stock_sku_id = empty($value['stock_sku_id']) ? $title : $value['stock_sku_id'];
                $category = $value['category'];
                $style = $value['style'];
                $product_id = isset($goods_info['product_id']) ? $goods_info['product_id'] : '';
                $quantity = isset($goods_info['quantity']) ? $goods_info['quantity'] : 0;
                $is_listing = (isset($goods_info['is_listing']) && $goods_info['is_listing'] == 1) ? "上架" : "下架";
                $sold_num = $value['sold_num'];
                $total_price = $value['total_price'];

                $data[] = array(
                    'num_iid' => $selling_id,
                    'stock_sku_id' => $stock_sku_id,
                    'sold_num' => $sold_num,
                    'title' => $title,
                    'category' => $category,
                    'selling_id' => $selling_id,
                    'style' => $style,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'is_listing' => $is_listing,
                    'total_price' => $total_price,

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

    /**
     * 将本地的库存数据（商品的库存数量和上下架状态）更新到有赞
     *
     * 仅对库存在总仓的框架眼镜、太阳镜和功能眼镜才需要更新到有赞
     */
    public function synYouzan() {
        set_time_limit(0);

        $YouzanModel = M('stock_youzan');

        $result = $YouzanModel->select();


        // 从有赞获取最新商品列表（不带款式）
        $Youzan = new \Lib\Youzan\Stock();
        $y_result = $Youzan->getSingleItemsList(['K', 'T', 'G']); // 只获取框架眼镜、太阳镜和功能眼镜

        // 将新增的商品 num_iid 插入到数据库中
        foreach ($y_result as $value) {
            $selling_id = $value['selling_id'];
            $num_iid = $value['num_iid'];
            $goods_url = $value['share_url'];

            $this->updateOneMark($selling_id, $num_iid, $goods_url);
        }

        // 将本地的库存数量数据同步到有赞，并更新商品的上下架状态
        foreach ($this->insert_goods as $i_selling_id => $i_num_iid) {
            $model_name = $this->stockObj->getGoodsModelBySellingId($i_selling_id);
            $GoodsModel = M($model_name);

            $map = array(
                'selling_id' => $i_selling_id,
                'warehouse' => '总仓',
            );

            $field = 'style,quantity';

            $i_result = $GoodsModel->where($map)->field($field)->select();

            if (C('YOUZAN_DEBUG') === false) {

                // 更新商品的订单状态
                foreach ($i_result as $value) {

                    // 要传递的变更数据
                    if (C('YOUZAN_DATA_TEST') === false) {
                        // 实际获取的数据
                        $num_iid = $i_num_iid;
                        $style = $value['style'];
                        $quantity = $value['quantity'];
                    } else {
                        // 此处填入测试专用商品 id
                        $num_iid = 293684019;
                        $style = 'C10';
                        $quantity = 88;
                    }

                    $Youzan->updateItemByAbsolute($num_iid, $style, $quantity);
                }

                // 更新商品的上下架状态
                // 要传递的变更数据
                if (C('YOUZAN_DATA_TEST') === false) {
                    // 实际获取的数据
                    $num_iid = $i_num_iid;
                } else {
                    // 此处填入测试专用商品 id
                    $num_iid = 293684019;
                }

                $Youzan->listingItem($num_iid);

            }

        }
    }

    /**
     * 对 stock_youzan 表更新操作（操作一次为一条记录）
     * @param $selling_id
     * @param $num_iid
     * @param $goods_url
     */
    public function updateOneMark($selling_id, $num_iid, $goods_url) {
        $YouzanModel = M('stock_youzan');

        $map['selling_id'] = $selling_id;

        $result = $YouzanModel->where($map)->find();

        if ($result) {
            echo 'already inserted.', '<br>';
        } else {
            echo 'inserting ...', '<br>';
            $this->insert_goods[$selling_id] = $num_iid;
            $data = array(
                'selling_id' => $selling_id,
                'num_iid' => $num_iid,
                'goods_url' => $goods_url,
            );

            $YouzanModel->add($data);
        }
    }

    /**
     * 对 item_list 和 item_trade_list 表的数据填充完整（主要缺 10 月份的数据）
     */
    public function operateItemList() {
        // 判断此断期间的订单是否落在 item_trade_list 表中
        // 仅在此表不存在的时候，才将数据插入到 item_trade_list 和 item_list 表

        set_time_limit(0);

        $ItemTrade = M('item_trade_list');

        $item_result = $ItemTrade->field('tid')->group('tid')->select();

        $tid_arr = array();

        foreach ($item_result as $item) {
            $tid_arr[$item['tid']] = true;
        }
        unset($item);


        // 分析 2016-9-1 之后的订单
        $Trades = M('tradedetail');

        $map['created'] = array('GT', '2016-9-1');

        $trade_result = $Trades->where($map)->select();

        foreach ($trade_result as $trade) {
            $tid = $trade['tid'];

            // 仅处理订单在 item_trade_list 表中不存在的订单
            if (! isset($tid_arr[$tid])) {
                $trade_data = $this->getTradeInfo($trade);

                $result = $this->stockObj->saveSoldInfo($trade_data);

                if (isset($result['sign']) && $result['sign'] === -1) {
                    outputDebugLog(date('Y-m-d H:i:s'), FILE_APPEND, 'operate_item.log');
                    outputDebugLog($trade_data, FILE_APPEND, 'operate_item.log');
                    outputDebugLog($result['msg'], FILE_APPEND, 'operate_item.log');
                }
            }
        }
        unset($trade);

        echo 'success!';
    }

    /**
     * 从订单中获取销售的商品具体信息
     * @param $trade
     * @return array
     */
    private function getTradeInfo(& $trade) {
        $tid = $trade['tid'];

        if (strlen($tid) < 20) {
            // 处理自有扫码订单的商品信息

            $list = explode('@', $trade['title']);

            // 扫码有 5 类商品信息
            // K->框架眼镜，T->太阳眼镜，G->功能眼镜, Y->隐形眼镜，H->其他商品

            $goods_name = $list[0];
            $selling_id = getSellingId($goods_name);

            if ($selling_id == 'G6401') {
                // 泳镜

                // 款型头部（字母加数字）
                $style_head = getStyleHead($list[1]);
                // 度数
                $pattern = '#^([0-9]*).*$#';

                preg_match($pattern, $list[2], $match);

                if (isset($match[1])) {
                    $degree = $match[1];
                } else {
                    $degree = '';
                }

                $sku_id = $selling_id . $style_head . 'D' . $degree;
            } else {
                // 非泳镜


                if (substr($selling_id, 0, 1) === 'Y') {
                    // 隐形眼镜
                    // 取得度数
                    // 度数
                    $pattern = '#^([0-9]*).*$#';

                    preg_match($pattern, $list[1], $match);

                    if (isset($match[1])) {
                        $degree = $match[1];
                    } else {
                        $degree = '';
                    }

                    $sku_id = $selling_id . 'D' . $degree;

                } else {
                    // 非隐形眼镜
                    $style_head = getStyleHead($list[1]);
                    $sku_id = $selling_id . $style_head;
                }
            }

            $goods_info = $this->stockObj->getInfoBySkuId($sku_id);

            if (empty($goods_info)) {
                $data = array();
            } else {
                $data = array(
                    'tid' => $tid,
                    'sold_list' => array(
                        array(
                            'sku_id' => $sku_id,
                            'price' => $goods_info['price'],
                            'payment' => $trade['price'],
                            'sold_num' => $trade['num'],
                            'total_payment' => $trade['total_fee'],
                        )
                    ),
                );
            }

            return $data;

        } else {
            // 处理有赞的商品信息
            $YouzanTrade = new \Lib\Youzan\Trades();

            $youzan_result = $YouzanTrade->getTrade($tid, 'orders');

            if (isset($youzan_result['response']['trade']['orders'])) {
                $sold_list = array();
                foreach ($youzan_result['response']['trade']['orders'] as $order) {
                    $goods_name = $order['title'];

                    $selling_id = getSellingId($goods_name);
                    $style = getStyleFromProperty($order['sku_properties_name'], $selling_id);

                    if (is_array($style)) {
                        // 若返回的是数组，则说明是泳镜，要特殊处理
                        $sku_id = $selling_id . $style['style'] . 'D' . $style['degree'];
                    } else {
                        $sku_id = $selling_id . $style;
                    }

                    $sold_list[] = array(
                        'sku_id' => $sku_id,
                        'price' => $order['price'],
                        'payment' => $order['payment'],
                        'sold_num' => $order['num'],
                        'total_payment' => $order['total_fee'],
                    );
                }

                $data = array(
                    'tid' => $tid,
                    'sold_list' => $sold_list,
                );

                return $data;
            }
        }
    }
}
