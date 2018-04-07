<?php
namespace Home\Controller;

use Common\Controller\HomebaseController;
use Think\Exception;

if (!defined('YOUZAN_FUNCTION_ROOT')) {
    define('YOUZAN_FUNCTION_ROOT', APP_PATH . '/Lib/Youzan/');
    require(YOUZAN_FUNCTION_ROOT . 'functions.php');
}

class StockController extends HomebaseController {
    private $stockObj;
    public function __construct() {
        parent::__construct();

        $this->stockObj = new \Home\Model\StockModel();
    }

    public function test() {
        $this->display();
    }

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

    /**
     * 返回数据到前端
     * @param int    $code   状态码，1表示成功，-1表示失败，也可以拓展成其他的状态码
     * @param string $msg    返回报告消息
     * @param array  $result 返回数据主体内容
     */
    private function returnJson($code, $msg, $result = array()) {
        $data = array(
            'sign' => $code,
            'msg' => $msg,
        );

        if (! empty($result)) {
            $data['result'] = $result;
        }

        $this->ajaxReturn($data, 'JSON');
	}

    /**
     * 根据指定模式获取指定字符串，用来拼凑 sku_id
     *
     * @param $str
     * @param $pattern
     * @return mixed
     */
    private function getNeedField($str, $pattern) {
        preg_match($pattern, $str, $match);

        if (isset($match[1])) {
            return $match[1];
        } else {
            return '';
        }
    }

    /**
     * 获取商品列表
     */
    public function getGoodsList() {
        $cat_id = I('post.cat_id', '', 'trim,strip_tags');
        $search_id = I('post.search_id', '', 'trim,strip_tags');
        $is_listing = I('post.is_listing', '', 'trim,strip_tags');
        $warehouse = I('post.warehouse', '', 'trim,strip_tags');

        // 根据商品分类 cat_id 从配置文件中获取需要实例化的模型
        $model_name = $this->stockObj->getGoodsModelByCat($cat_id);
        
        if ($model_name === false) {
            $this->returnJson(-1, '商品类型不明！');
        }

        // 从商品表中获取需要信息
        // 实例化商品表
        $Goods = M($model_name);

        $map = array();

        if (! empty($search_id)) {
            if ($cat_id === 'K' || $cat_id === 'T' || $cat_id === 'G') {
                $where['selling_id'] = array('like', $search_id . '%');
                $where['product_id'] = array('like', $search_id . '%');
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
            } else {
                $map['selling_id'] = array('like', $search_id . '%');
            }
        }

        $group = 'sku_id';

        if ($warehouse !== '' && $warehouse !== '汇总') {
            $map['warehouse'] = $warehouse;
            $group .= ',warehouse';
        }

        if ($is_listing !== '' && $is_listing !== '-1') {
            $map['is_listing'] = $is_listing;
        }

        $field = $this->stockObj->getStockField($cat_id);
        $order= 'is_listing desc, selling_id';
        
        $attach_field = $this->stockObj->getAttachField($cat_id);
        $order .= $attach_field;
        
        $result = $Goods->where($map)->field($field)->order($order)->group($group)->select();

        // 表格标题栏
        $table_head = $this->stockObj->getTableHead($cat_id);
        if ($table_head === false) {
            $this->returnJson(-1, '获取表格头部信息失败！');
        }

        // 弹窗表单结构信息
        $form_struct = $this->stockObj->getFormStruct($cat_id);
        if ($form_struct === false) {
            $this->returnJson(-1, '获取表单结构信息失败！');
        }

        // 获取特定类型商品的字段
        $attach_data = $this->stockObj->getDataFieldsByCat($cat_id);
        if ($attach_data === false) {
            $this->returnJson(-1, '商品分类不明！');
        }
        if ($cat_id === 'H') {
            $attach_data['attr'] = 'attr';
        }

        // 商品列表
        $goods_list = array();
        foreach ($result as $key => $value) {
            $goods_list[$key] = array(
                'sku_id' => $value['sku_id'],
                'selling_id' => $value['selling_id'],
                'goods_name' => $value['goods_name'],
                'price' => $value['price'],
                'quantity' => $value['quantity'],
                'warehouse' => $warehouse,
                'is_listing' => $value['is_listing'],
                'total_sold' => $this->stockObj->getSoldNum(array('sku_id' => $value['sku_id'])),
                'is_replenish' => $value['is_replenish'],
                'procurement_price' => $value['procurement_price'],
                'supplier' => $value['supplier'],
                'brand' => $value['brand'],
                'update_time' => $value['update_time'],
            );

            foreach ($attach_data as $k => $v) {
                $goods_list[$key][$k] = $value[$k];
            }
            unset($val);
        }
        unset($value);

        $data = array(
            'table_head' => $table_head,
            'form_struct' => $form_struct,
            'goods_list' => $goods_list,
        );

        $this->returnJson(1, '请求成功', $data);
    }

    /**
     * 商品新增操作
     */
    public function addGoods() {
        // 此类参数为所有商品都共有的
        $cat_id = I('post.cat_id', '', 'trim,strip_tags');
        $selling_id = I('post.selling_id', '', 'trim,strip_tags');
        $goods_name = I('post.goods_name', '', 'trim,strip_tags');
        $price = I('post.price', 0.00, 'trim,strip_tags');
        $quantity = I('post.quantity', 0, 'trim,strip_tags');
        $is_listing = I('post.is_listing', 1, 'trim,strip_tags');
        $is_replenish = I('post.is_replenish', 1, 'trim,strip_tags');
        $procurement_price = I('post.procurement_price', 0.00, 'trim,strip_tags');
        $supplier = I('post.supplier', '', 'trim,strip_tags');
        $attr = I('post.attr', '', 'trim,strip_tags');
        $brand = I('post.brand', '', 'trim,strip_tags');;
        $warehouse = I('post.warehouse', '', 'trim,strip_tags');
        $update_user = I('session.userinfo')['username'];

        // 判断数据合理性
        if ($warehouse != '总仓') {
            $this->returnJson(-1, '商品新增只能添加到总仓');
        }
        if ($cat_id !== substr($selling_id, 0, 1)) {
            $this->returnJson(-1, '错误，商城编号不属于对应商品分类！');
        }
        if ($quantity < 0) {
            $this->returnJson(-1, '库存数量不能小于0');
        }
        if (floatval($price) < 0) {
            $this->returnJson(-1, '价格不能小于0');
        }
        if (floatval($procurement_price) < 0) {
            $this->returnJson(-1, '价格不能小于0');
        }
        
        $attach_data = $this->stockObj->getDataFieldsByCat($cat_id);
        if ($attach_data === false) {
            $this->returnJson(-1, '商品分类不明！');
        }

        $options = array();
        $sku_id = $selling_id;
        foreach ($attach_data as $key => $val) {
            $options[$key] = I('post.' . $key, $val['default'], 'trim,strip_tags');
            if ($val['is_need']) {
                if ($options[$key] === '') {
                    $this->returnJson(-1, '必填信息不能为空！');
                } else {
                    $prefix = isset($val['prefix']) ? $val['prefix'] : '';
                    $sku_id .= $prefix . $this->getNeedField($options[$key], $val['pattern']);
                }
            }
        }
        unset($val);
        if ($cat_id === 'H') {
            $sku_id = $selling_id . 'A' . $attr;
            $style = $attr;
        }

        // 若为泳镜（G6401），还需额外处理
        if ($selling_id == 'G6401') {
            $sku_id .= 'D' . $this->getNeedField($options['degree'], '#^([0-9]*).*$#');
        }

        // 实例化数据库模型
        $model_name = $this->stockObj->getGoodsModel($sku_id);
        $Goods = M($model_name);
        $map = array(
            'selling_id' => $selling_id,
        );

        $result = $Goods->where($map)->find();

        if (! $result) {
            $data = array(
                'sku_id' => $sku_id,
                'selling_id' => $selling_id,
                'goods_name' => $goods_name,
                'price' => $price,
                'quantity' => $quantity,
                'is_listing' => $is_listing,
                'is_replenish' => $is_replenish,
                'procurement_price' => $procurement_price,
                'supplier' => $supplier,
                'brand' => $brand,
                'warehouse' => $warehouse,
                'update_user' => $update_user,
            );

            foreach ($attach_data as $key => $val) {
                $data[$key] = $options[$key];
            }
            unset($val);
            // 其他商品特殊处理
            if ($cat_id === 'H') {
                $data['style'] = $attr;
                unset($data['attr']);
            }

            $insert_res = $Goods->add($data);

            // 保存记录到历史记录表

            $goods_info = $this->stockObj->getInfoBySkuId($sku_id);
            $log_data = array(
                'new_value' => $goods_info,
            );
            $this->saveLog($log_data, $update_user, '新增商品');

            if ($insert_res) {
                $this->returnJson(1, '提交成功！');
            } else {
                $this->returnJson(-1, '数据插入到数据库失败！');
            }
        } else {
            $this->returnJson(-1, '此商品已经存在，只能添加品类（属性）或修改商品信息！');
        }

    }

    /**
     * 商品的品类（属性，如款式，度数等）新增操作
     */
    public function addAttr() {
        // 此类参数为所有商品都共有的
        $cat_id = I('post.cat_id', '', 'trim,strip_tags');
        $selling_id = I('post.selling_id', '', 'trim,strip_tags');
        $goods_name = I('post.goods_name', '', 'trim,strip_tags');
        $price = I('post.price', 0.00, 'trim,strip_tags');
        $quantity = I('post.quantity', 0, 'trim,strip_tags');
        $is_listing = I('post.is_listing', 1, 'trim,strip_tags');
        $is_replenish = I('post.is_replenish', 1, 'trim,strip_tags');
        $procurement_price = I('post.procurement_price', 0.00, 'trim,strip_tags');
        $supplier = I('post.supplier', '', 'trim,strip_tags');
        $brand = I('post.brand', '', 'trim,strip_tags');;
        $attr = I('post.attr', '', 'trim,strip_tags');
        $warehouse = I('post.warehouse', '', 'trim,strip_tags');
        $update_user = I('session.userinfo')['username'];

        // 判断数据合理性
        if ($warehouse != '总仓') {
            $this->returnJson(-1, '新品类（属性）新增只能添加到总仓');
        }
        if ($cat_id !== substr($selling_id, 0, 1)) {
            $this->returnJson(-1, '错误，商城编号不属于对应商品分类！');
        }
        if ($quantity < 0) {
            $this->returnJson(-1, '库存数量不能小于0');
        }
        if (floatval($price) < 0) {
            $this->returnJson(-1, '价格不能小于0');
        }
        if (floatval($procurement_price) < 0) {
            $this->returnJson(-1, '价格不能小于0');
        }

        $attach_data = $this->stockObj->getDataFieldsByCat($cat_id);
        if ($attach_data === false) {
            $this->returnJson(-1, '商品分类不明！');
        }

        $options = array();
        $sku_id = $selling_id;
        foreach ($attach_data as $key => $val) {
            $options[$key] = I('post.' . $key, $val['default'], 'trim,strip_tags');
            if ($val['is_need']) {
                if ($options[$key] === '') {
                    $this->returnJson(-1, '必填信息不能为空！');
                } else {
                    $prefix = isset($val['prefix']) ? $val['prefix'] : '';
                    $sku_id .= $prefix . $this->getNeedField($options[$key], $val['pattern']);
                }
            }
        }
        unset($val);
        if ($cat_id === 'H') {
            $sku_id = $selling_id . 'A' . $attr;
        }

        // 若为泳镜（G6401），还需额外处理
        if ($selling_id == 'G6401') {
            $sku_id .= 'D' . $this->getNeedField($options['degree'], '#^([0-9]*).*$#');
        }

        // 实例化数据库模型
        $model_name = $this->stockObj->getGoodsModel($sku_id);
        $Goods = M($model_name);
        $map = array(
            'selling_id' => $selling_id,
        );

        $result = $Goods->where($map)->find();

        if ($result) {

            // 从数据库里查出 selling_id 对应的商品信息
            $sku_id_map = array(
                'sku_id' => $sku_id,
            );

            $sku_id_res = $Goods->where($sku_id_map)->find();

            if (! $sku_id_res) {
                $data = array(
                    'sku_id' => $sku_id,
                    'selling_id' => $selling_id === '' ? $result['selling_id'] : $selling_id,
                    'goods_name' => $goods_name === '' ? $result['goods_name'] : $goods_name,
                    'price' => $price === '' ? $result['price'] : $price,
                    'quantity' => $quantity,
                    'is_listing' => $is_listing === '' ? $result['is_listing'] : $is_listing,
                    'is_replenish' => $is_replenish === '' ? $result['is_replenish'] : $is_replenish,
                    'procurement_price' => $procurement_price === '' ? $result['procurement_price'] : $procurement_price,
                    'supplier' => $supplier === '' ? $result['supplier'] : $supplier,
                    'brand' => $brand === '' ? $result['supplier'] : $brand,
                    'warehouse' => $warehouse === '' ? $result['warehouse'] : $warehouse,
                    'update_user' => $update_user,
                );

                foreach ($attach_data as $key => $val) {
                    $data[$key] = $options[$key];
                    // 非必填项优先填入已有信息
                    if (! $val['is_need']) {
                        $data[$key] = $data[$key] === '' ? $result[$key] : $data[$key];
                    }
                }
                unset($val);
                if ($cat_id === 'H') {
                    $data['style'] = $attr;
                    unset($data['attr']);
                }

                $insert_res = $Goods->add($data);

                // 保存记录到历史记录表

                $goods_info = $this->stockObj->getInfoBySkuId($sku_id);
                $log_data = array(
                    'new_value' => $goods_info,
                );
                $this->saveLog($log_data, $update_user, '新增品类');

                if ($insert_res) {
                    $this->returnJson(1, '提交成功！');
                } else {
                    $this->returnJson(-1, '数据插入到数据库失败！');
                }
            } else {
                $this->returnJson(-1, '此品类（属性）已经存在，只能修改商品信息或添加其他品类（属性）！');
            }
        } else {
            $this->returnJson(-1, '商品不存在，请新增商品后再添加商品新品类（属性）！');
        }
    }

    /**
     * 删除商品的指定品类（属性，如款式，度数等）
     */
    public function removeAttr() {
        $sku_id = I('post.sku_id', '', 'trim,strip_tags');

        if ($sku_id === '') {
            $this->returnJson(-1, '商品信息不明确！');
        }

        $Stock = new \Home\Model\StockModel();

        $model_name = $Stock->getGoodsModel($sku_id);

        if ($model_name === false) {
            $this->returnJson(-1, '商品信息不存在！');
        } else {
            try {
                $map = [
                    'sku_id' => $sku_id,
                ];

                $res = M($model_name)->where($map)->delete();

                if ($res === false) {
                    $this->returnJson(-1, '删除属性分类失败！');
                } else {
                    $this->returnJson(1, '删除属性分类成功。');
                }
            } catch (Exception $e) {
                $file_name = 'Logs/Home/Stock/stock.error.' . date('y_m_d') . '.log';
                errorLog($e->getMessage(), $file_name);
                $this->returnJson(-1, '网络出错，请稍后重试...');
            }
        }
    }

    /**
     * 商品信息编辑操作
     */
    public function editGoods() {
        // 此类参数为所有商品都共有的
        $old_sku_id = I('post.sku_id', '', 'trim,strip_tags');
        $selling_id = I('post.selling_id', '', 'trim,strip_tags');
        $goods_name = I('post.goods_name', '', 'trim,strip_tags');
        $price = I('post.price', 0.00, 'trim,strip_tags');
        $quantity = I('post.quantity', 0, 'trim,strip_tags');
        $is_listing = I('post.is_listing', 1, 'trim,strip_tags');
        $is_replenish = I('post.is_replenish', 1, 'trim,strip_tags');
        $procurement_price = I('post.procurement_price', 0.00, 'trim,strip_tags');
        $supplier = I('post.supplier', '', 'trim,strip_tags');
        $brand = I('post.brand', '', 'trim,strip_tags');
        $attr = I('post.attr', '', 'trim,strip_tags');
        $warehouse = I('post.warehouse', '', 'trim,strip_tags');
        $update_user = I('session.userinfo')['username'];
        $comment = I('post.comment', '', 'trim,strip_tags');

        if ($comment === '') {
            $comment = '修改商品信息';
        }

        // 判断数据合理性
        $cat_id = substr($old_sku_id, 0, 1);
        if ($cat_id !== substr($selling_id, 0, 1)) {
            $this->returnJson(-1, '商城编号有问题，要对应商品类型！');
        }
        if (intval($quantity) < 0) {
            $this->returnJson(-1, '库存数量不能小于0');
        }
        if (floatval($price) < 0) {
            $this->returnJson(-1, '价格不能小于0');
        }
        if (floatval($procurement_price) < 0) {
            $this->returnJson(-1, '价格不能小于0');
        }
        if ($warehouse === '') {
            $this->returnJson(-1, '仓库信息不存在');
        }

        $attach_data = $this->stockObj->getDataFieldsByCat($cat_id);
        if ($attach_data === false) {
            $this->returnJson(-1, '商品分类不明！');
        }

        $options = array();
        $sku_id = $selling_id;
        foreach ($attach_data as $key => $val) {
            $options[$key] = I('post.' . $key, $val['default'], 'trim,strip_tags');
            if ($val['is_need']) {
                if ($options[$key] === '') {
                    $this->returnJson(-1, '必填信息不能为空！');
                } else {
                    $prefix = isset($val['prefix']) ? $val['prefix'] : '';
                    $sku_id .= $prefix . $this->getNeedField($options[$key], $val['pattern']);
                }
            }
        }
        unset($val);

        // 若为泳镜（G6401），还需额外处理
        if ($selling_id == 'G6401') {
            $sku_id .= 'D' . $this->getNeedField($options['degree'], '#^([0-9]*).*$#');
        }

        // 实例化数据库模型
        $model_name = $this->stockObj->getGoodsModel($old_sku_id);
        $Goods = M($model_name);
        $map = array(
            'sku_id' => $old_sku_id,
        );

        $result = $Goods->where($map)->find();

        if ($result) {
            $old_goods_info = $this->stockObj->getInfoBySkuId($old_sku_id, $warehouse);
            if ($cat_id === 'H') {
                if ($attr != $old_goods_info['style']) {
                    $sku_id = $selling_id . 'A' . $attr;
                } else {
                    $sku_id = $old_sku_id;
                }
            }

            $data = array(
                'sku_id' => $sku_id,
                'selling_id' => $selling_id,
                'goods_name' => $goods_name,
                'price' => $price,
                'is_listing' => $is_listing,
                'is_replenish' => $is_replenish,
                'procurement_price' => $procurement_price,
                'supplier' => $supplier,
                'brand' => $brand,
                // 'warehouse' => $warehouse, 库存信息不给修改
                'update_user' => $update_user,
            );

            foreach ($attach_data as $key => $val) {
                $data[$key] = $options[$key];
            }
            unset($val);

            if ($cat_id === 'H') {
                $data['style'] = $attr;
                unset($data['attr']);
            }

            // 修改普通属性等内容（不含库存数量）
            $update_res_common = $Goods->where($map)->setField($data);

            // 修改库存数量（具体对应实际库存）
            $data['quantity']  = $quantity;
            $map['warehouse'] = $warehouse;

            $update_res_ware = $Goods->where($map)->setField($data);

            // 保存记录到历史记录表

            $goods_info = $this->stockObj->getInfoBySkuId($sku_id, $warehouse);
            $log_data = array(
                'old_value' => $old_goods_info,
                'new_value' => $goods_info,
            );
            $this->saveLog($log_data, $update_user, $comment);

            if ($update_res_common !== false && $update_res_ware !== false) {
                $this->returnJson(1, '提交成功！');
            } else {
                $this->returnJson(-1, '数据插入到数据库失败！');
            }

        } else {
            $this->returnJson(-1, '指定商品（或品类）不存在，修改商品信息失败！');
        }
    }

    /**
     * 获取属性信息列表，或是二级联动信息列表
     *
     * 返回数据中 attr_level 标记属性的维度，数字1表示一维属性（如框架的款型）
     * 数字2表示二维属性（如泳镜先分款型，再分度数）
     * 且相应的 sku_id 值以最里层的为准
     * 注意：目前只支持 0、1、2维度，更多维度暂不支持
     */
    public function getAttrList() {
        $sku_id = I('post.sku_id', '', 'trim,strip_tags');

        // 判断 sku_id 对应的商品信息是否存在
        $goods_info = $this->stockObj->getInfoBySkuId($sku_id);
        if (! $goods_info) {
            $this->returnJson(-1, '所查商品不存在');
        }

        $cat_id = substr($sku_id, 0, 1);
        $selling_id = $goods_info['selling_id'];

        $model_name = $this->stockObj->getGoodsModelByCat($cat_id);
        $Goods = M($model_name);

        $field_data = $this->stockObj->getAttrFieldsBySkuId($sku_id);


        // 返回数组的深度
        $attr_level = count($field_data);
        $keys = array();

        foreach ($field_data as $key => $val) {
            $keys[] = $key;
        }

        if ($attr_level == 1) {
            $attr1_key = $keys[0];
            $field = $attr1_key . ',sku_id';
            $group = $attr1_key;

            $map = array(
                'selling_id' => $selling_id,
                'warehouse' => '总仓',
            );

            $db_result = $Goods->group($group)->where($map)->field($field)->select();

            $sub_data = array();
            foreach ($db_result as $res) {
                $sub_data[] = array(
                    'attr' => $res[$attr1_key],
                    'sku_id' => $res['sku_id'],
                    'sub_attr_name' => null,
                    'sub_data' => null,
                );
            }
            unset($res);

            $result = array(
                'attr_level' => 1,
                'sku_id' => '',
                'sub_attr_name' => $field_data[$attr1_key],
                'sub_data' => $sub_data,
            );
        } elseif ($attr_level == 2) {
            $attr1_key = $keys[0];
            $field = $attr1_key . ',sku_id';
            $group = $attr1_key;

            $map = array(
                'selling_id' => $selling_id,
                'warehouse' => '总仓',
            );

            $db_result1 = $Goods->group($group)->where($map)->field($field)->select();

            $sub_data1 = array();
            foreach ($db_result1 as $res1) {
                $attr2_key = $keys[1];
                $field = $attr2_key . ',sku_id';
                $group = $attr2_key;
                $map[$attr1_key] = $res1[$attr1_key];

                $db_result2 = $Goods->group($group)->where($map)->field($field)->select();

                $sub_data2 = array();
                foreach ($db_result2 as $res2) {
                    $sub_data2[] = array(
                        'attr' => $res2[$attr2_key],
                        'sku_id' => $res2['sku_id'],
                        'sub_attr_name' => null,
                        'sub_data' => null,
                    );
                }
                unset($res2);

                $sub_data1[] = array(
                    'attr' => $res1[$attr1_key],
                    'sku_id' => '',
                    'sub_attr_name' => $field_data[$attr2_key],
                    'sub_data' => $sub_data2,
                );
            }
            unset($res1);

            $result = array(
                'attr_level' => 2,
                'sku_id' => '',
                'sub_attr_name' => $field_data[$attr1_key],
                'sub_data' => $sub_data1,
            );


        } else {
            $result = array(
                'attr_level' => 0,
                'sub_attr_name' => '',
                'sub_data' => null,
            );
        }http://img.ly/xFY1

        $this->returnJson(1, '请求成功！', $result);

    }

    /**
     * 获取特定 sku_id 的商品在各仓库的库存数量
     */
    public function getQuantity() {
        $sku_id = I('post.sku_id', '', 'trim,strip_tags');

        // 判断 sku_id 对应的商品信息是否存在
        if (! $this->stockObj->getInfoBySkuId($sku_id)) {
            $this->returnJson(-1, '所查商品不存在');
        }

        $warehouse_data = $this->stockObj->getWareHouseData();

        $data = array();
        foreach ($warehouse_data as $val) {
            $data[] = array(
                'sku_id' => $sku_id,
                'warehouse' => $val['w_name'],
                'quantity' => $this->stockObj->getQuantity($sku_id, $val['w_name']),
            );
        }
        unset($val);

        $this->returnJson(1, '请求成功', array('data' => $data));
    }

    /**
     * 商品调库操作
     */
    public function transferGoods() {
        // 从前端接收的数据
        $data_json = I('post.data', '', 'trim,strip_tags');

        $update_user = I('session.userinfo')['username'];


        if (empty($data_json)) {
            $this->returnJson(-1, '提交数据为空！');
        }

        $data = $data_json['data'];


        if (is_array($data)) {
            foreach ($data as $value) {
                $sku_id = isset($value['sku_id']) ? $value['sku_id'] : $this->returnJson(-1, '数据格式有错，无法获取 sku_id 值！');
                $warehouse = isset($value['warehouse']) ? $value['warehouse'] : $this->returnJson(-1, '数据格式有错，无法获取仓库名！');
                $quantity = isset($value['quantity']) ? intval($value['quantity']) : $this->returnJson(-1, '数据格式有错，无法获取库存数量');

                if ($quantity < 0) {
                    $this->returnJson(-1, '库存数量不能小于0！');
                }
                if (! $this->stockObj->isCorrectWarehouse($warehouse)) {
                    $this->returnJson(-1, '仓库名有误！');
                }


                // 先记录下要保存的日志信息
                $old_quantity = $this->stockObj->getQuantity($sku_id, $warehouse);
                $log_data = array(
                    'old_value' => array(
                        'sku_id' => $sku_id,
                        'warehouse' => $warehouse,
                        'quantity' => $this->stockObj->getQuantity($sku_id, $warehouse),
                    ),
                    'new_value' => array(
                        'sku_id' => $sku_id,
                        'warehouse' => $warehouse,
                        'quantity' => $quantity,
                    ),
                );

                if (intval($old_quantity) != intval($quantity)) {
                    $this->saveLog($log_data, $update_user, '商品调库');

                    // 将变更的数据更新到数据库
                    $result = $this->saveQuantity($sku_id, $warehouse, $quantity);

                    if ($result === false) {
                        $this->returnJson(-1, '数据保存到数据库发生错误，前重新提交数据！');
                    }
                }
            }

            $this->returnJson(1, '数据更新成功！');

        }
    }

    /**
     * 将库存变更的数量更新（或新增）到数据表
     *
     * @param $sku_id
     * @param $warehouse
     * @param $quantity
     * @return bool|mixed
     */
    private function saveQuantity($sku_id, $warehouse, $quantity) {
        $model_name = $this->stockObj->getGoodsModel($sku_id);
        $Goods = M($model_name);

        $map = array(
            'sku_id' => $sku_id,
            'warehouse' => $warehouse,
        );

        $is_exists = $Goods->where($map)->find();

        if ($is_exists == null) {
            $goods_data = $this->stockObj->getInfoBySkuId($sku_id);

            $goods_data['warehouse'] = $warehouse;
            $goods_data['quantity'] = $quantity;
            $goods_data['update_user'] = I('session.userinfo')['username'];

            $result = $Goods->add($goods_data);
        } else {
            $set_data = array(
                'quantity' => $quantity,
            );
            $result = $Goods->where($map)->setField($set_data);
        }

        return $result;
    }
    
    public function synYouzan($data) {
        if (empty($data) ||
            ! is_array($data) ||
            ! isset($data['new_value']['sku_id']) ||
            ! isset($data['new_value']['warehouse']) ||
            $data['new_value']['warehouse'] != '总仓') {

            return false;
        }

        if (isset($data['new_value']) && is_array($data['new_value'])) {
            $options = array();

            if (isset($data['new_value']['quantity'])) {
                if (! isset($data['old_value']['quantity'])
                    && $data['new_value']['quantity'] != $data['old_value']['quantity']
                ) {
                    $options['quantity'] = $data['old_value']['quantity'];
                }
            }

            if (isset($data['new_value']['is_listing'])) {
                if (! isset($data['old_value']['is_listing'])
                    && $data['new_value']['is_listing'] != $data['old_value']['is_listing']
                ) {
                    $options['is_listing'] = $data['old_value']['is_listing'];
                }
            }

            $this->stockObj->synDataToYouzan($data['new_value']['sku_id'], '总仓', $options);
        }
    }

    /**
     * 进化提醒信息输出
     */
    public function searchNotice() {
        $cat_id = I('post.cat_id', '', 'trim,strip_tags');
        $search_id = I('post.search_id', '', 'trim,strip_tags');
        $period_str = I('post.period', '', 'trim,strip_tags');
        $period = intval($period_str);
        $warehouse = I('post.warehouse', '', 'trim,strip_tags');

        $mode_name = $this->stockObj->getGoodsModelByCat($cat_id);

        if ($mode_name === false) {
            $this->returnJson(-1, '商品分类信息不明！');
        }

        $Goods = M($mode_name);
        $map = array();

        if (! empty($search_id)) {
            if (! empty($search_id)) {
                if ($cat_id === 'K' || $cat_id === 'T' || $cat_id === 'G' || $cat_id === 'U') {
                    $where['selling_id'] = array('like', '%' . $search_id . '%');
                    $where['product_id'] = array('like', '%' . $search_id  . '%');
                    $where['_logic'] = 'or';
                    $map['_complex'] = $where;
                } else {
                    $map['selling_id'] = array('like', '%' . $search_id . '%');
                }
            }
        }

        if ($warehouse !== '' && $warehouse !== '汇总') {
            if ($this->stockObj->isCorrectWarehouse($warehouse)) {
                $map['warehouse'] = $warehouse;
            } else {
                $this->returnJson(-1, '仓库名有误！');
            }
        }

        $field = "sku_id,selling_id,goods_name,warehouse,sum(quantity) quantity,supplier";

        $attach_field = $this->stockObj->getNoticeFieldsByCatId($cat_id);
        if ($attach_field === false) {
            $this->returnJson(-1, '商品分类值传入有误！');
        }
        $field .= ',' . $attach_field;

        $result = $Goods->where($map)->field($field)->group('sku_id')->select();

        $data = array();
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
                $recommend_days = floor($quantity / $max_average) - $period;
                $recommend_days = ($recommend_days < 0) ? 0 : $recommend_days;
            } else {
                $recommend_days = -1;
            }

            // 构建采购指南数据
            $inbound_num = ceil(abs($max_average * 7));

            $data[] = array(
                'sku_id' => $value['sku_id'],
                'selling_id' => $value['selling_id'],
                'product_id' => isset($value['product_id']) ? $value['product_id'] : '',
                'goods_name' => $value['goods_name'],
                'attribute' => $value['attribute'],
                'quantity' => $value['quantity'],
                'warehouse' => $warehouse,
                'sold_in_3days' => $three_day_count,
                'sold_in_7days' => $seven_day_count,
                'sold_in_15days' => $half_month_count,
                'recommend_days' => $recommend_days,
                'inbound_num' => $inbound_num,
                'supplier' => $value['supplier'],
            );
        }
        unset($value);

        $this->returnJson(1, '获取数据成功！', $data);
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
     * 库存相关的操作信息预处理
     *
     * $data 的数据结构如下：
     * $data = array(
     *     // 即相当数据库里的一条记录
     *     'old_value' => array('sku_id' => 'K1001C1', 'selling_id' => 'K1001', 'quantity' => 12),
     *     'new_value' => array('sku_id' => 'K1001C1', 'selling_id' => 'K1001', 'quantity' => 14),
     * )
     *
     * @param array  $data         主体数据
     * @param string $update_user  操作用户
     * @param string $update_event 变更缘由
     * @return bool
     */
    private function saveLog($data, $update_user, $update_event = '') {
        if (empty($data) ||
            ! is_array($data) ||
            ! isset($data['new_value']['sku_id']) ||
            ! isset($data['new_value']['warehouse'])) {
            return false;
        }

        if (isset($data['new_value']) && is_array($data['new_value'])) {

            $origin_value = "{$data['new_value']['warehouse']} 的商品——";
            $new_value = "{$data['new_value']['warehouse']} 的商品——";
            $update_type = '';
            $change_flag = false;

            foreach ($data['new_value'] as $key => $value) {
                if (! isset($data['old_value'][$key]) ||
                    $data['old_value'][$key] != $value) {
                    $type = $this->stockObj->changeType($key);

                    $origin_value .= $type . '：' . (isset($data['old_value'][$key]) ? $data['old_value'][$key] : '无') . '；';
                    $new_value .= $type . '：' . $value . '；';
                    $update_type .= '更新' . $type . '；';

                    $change_flag = true;
                }
            }
            unset($value);

            if ($change_flag) {
                // $origin_value = rtrim($origin_value, '；');
                // $new_value = rtrim($new_value, '；');
                // $update_type = rtrim($update_type, '；');
                $origin_value = mb_substr($origin_value, 0, -1, 'utf-8');
                $new_value = mb_substr($new_value, 0, -1, 'utf-8');
                $update_type = mb_substr($update_type, 0, -1, 'utf-8');

                $log_data = array(
                    'sku_id' => $data['new_value']['sku_id'],
                    'origin_value' => $origin_value,
                    'new_value' => $new_value,
                    'update_type' => $update_type,
                    'update_event' => $update_event,
                    'update_user' => $update_user,
                );
                return $this->stockObj->writeHistory($log_data);
            } else {
                return false;
            }

        }
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
            $warehouse = I('get.warehouse', '', 'trim,strip_tags');

            if ($cat_id === '') {
                $error = array(
                    'sign' => -1,
                    'msg' => '商品类型不明确！',
                );

                $this->ajaxReturn($error, 'JSON');
            } else {
                $result = $this->getStockDataByDb($search_id, $is_listing, $cat_id, $warehouse);

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
                        $is_listing = ($value['is_listing'] == 1) ? '上架中' : '已下架';;
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

    public function getStockDataByDb($search_id = '', $is_listing = -1, $cat_id = 'K', $position = '汇总', $select = 'all') {
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

        if ($position !== '' && $position != '汇总') {
            $map['warehouse'] = $position;
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

}