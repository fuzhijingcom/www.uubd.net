<?php
namespace Home\Controller;

class StocknextController extends StockbaseController{
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
        // TODO: 需要根据配置文件动态获取
        $categoryData = array(
            array(
                'cat_id' => 1,
                'cat_name' => '框架眼镜',
                'parent_id' => '0',
                'level' => 1,
            ),
        );
        $this->assign('categoryData', json_encode($categoryData));

        $date['startT'] = date('Y-m-d');
        $date['endT'] = date('Y-m-d', strtotime('next day'));
        $this->assign('date', $data);


        // 获取仓库数据
        // TODO: 需要根据配置文件动态获取
        $warehouseData = array(
            array(
                'w_id' => 1,
                'w_name' => '总仓',
                'w_info' => '',
            ),
            array(
                'w_id' => 2,
                'w_name' => '新天地',
                'w_info' => '',
            ),
            array(
                'w_id' => 3,
                'w_name' => '南亭',
                'w_info' => '',
            ),
        );
        $this->assign('warehouseData',json_encode($warehouseData));

        // 获取供货商数据
        // TODO: 需要根据配置文件动态获取
        $stockSupplierData = array(
            array(
                'sup_id' => 1,
                'sup_name' => '万丰正觉',
            ),

            array(
                'sup_id' => 2,
                'sup_name' => '供货ABC',
            ),
        );
        $this->assign('stockSupplierData',json_encode($stockSupplierData));

        $this->display();
    }

    // TODO: 有待进一步完善
    public function searchStock() {
        set_time_limit(0);
        if (IS_POST) {
            $search_id = I('post.cond', '', 'trim,strip_tags');
            $search_stock_type = I('post.product_stock_type', '', 'trim,strip_tags');
            $product_type = I('post.product_type', '', 'trim,strip_tags');
            $position = I('post.position', '', 'trim,strip_tags');

            // TODO: 类型分类的话根据搜索条件并结合配置文件动态生成
            $category = '框架眼镜';



            $Stock = D('Stocknext');
            $map = array();
            if ($position == '-1') {
                // 获取所需字段
                // TODO: 需按类型获取字段，在配置文件中实现？
                // $field = 'selljing_id,goods_name,product_id'
                //


                if (! empty($search_id)) {
                    $where['selling_id'] = array('like',$search_id.'%');
                    $where['product_id'] = array('like',$search_id.'%');
                    $where['_logic'] = 'or';
                    $map['_complex'] = $where;
                }
            }

            $data = $Stock->getStock();

            $result = array();
            foreach ($data as $value) {
                $result[] = array (
                    's_id' => 0,
                    'attr_id' => '0',
                    's_info' =>
                        array (
                            'selling_id' => $value['selling_id'],
                            'product_name' => $value['goods_name'],
                            'goods_id' => $value['product_id'],
                            'style_full' => $value['style'],
                            'brand' => $value['brand'],
                            'cat_name' => $category,
                            'attr_custom' => '',
                            'water' => '',
                            'single_price' => $value['price'],
                            'quantity' => $value['quantity'],
                            'per' => '副',
                            'attr_degree' => '0度',
                            'product_stock_type' => '0',
                            'total_trade' => '0',
                            'operate_time' => $value['update_time'],
                            'location' => '汇总',
                        )
                );
            }

            $this->ajaxReturn($result, 'JSON');
        }
    }

    // TODO: 数据接口有变，需要进一表调整
    public function getSellingId() {
        if (IS_POST) {
            $selling_id = I('post.selling_id', '', 'trim,strip_tags');

            // TODO: 根据 selling_id 的特征确定具体商品表，以及返回的字段信息
            $category = '框架眼镜';
            $warehouse = '总仓';

            $map = array(
                'selling_id' => $selling_id,
                'warehouse' => $warehouse,
            );

            $Stock = D('Stocknext');
            $data = $Stock->getStock($map);
            outputDebugLog($data);
            $result = array();
            foreach ($data as $value) {
                $result[] = array (
                    's_id' => 0,
                    'attr_id' => '0',
                    's_info' =>
                        array (
                            'selling_id' => $value['selling_id'],
                            'product_name' => $value['goods_name'],
                            'goods_id' => $value['product_id'],
                            'style_full' => $value['style'],
                            'brand' => $value['brand'],
                            'cat_name' => $category,
                            'attr_custom' => '',
                            'water' => '',
                            'single_price' => $value['price'],
                            'quantity' => $value['quantity'],
                            'per' => '副',
                            'attr_degree' => '0度',
                            'product_stock_type' => '0',
                            'total_trade' => '0',
                            'operate_time' => $value['update_time'],
                            'location' => '汇总',
                        )
                );
            }

            $this->ajaxReturn($result, 'JSON');
        }
    }

    // TODO：还待进一步完善
    public function changeStock() {
        if (IS_POST) {
            $sku_id = I('post.sku_id','','trim,strip_tags');
            $selling_id = I('post.selling_id', '', 'trim,strip_tags');
            $product_type = I('post.product_type','','trim,strip_tags');
            $product_id = I('post.goods_id', '', 'trim,strip_tags');
            $style = I('post.style', '', 'trim,strip_tags');
            $v_num = I('post.v_num', '', 'trim,strip_tags');
            $v_num = intval($v_num);
            $procurementPrice = I('post.innerPrice', '', 'trim,strip_tags');
            $procurementPrice = floatval($procurementPrice);
            $price = I('post.price', '', 'trim,strip_tags');

            $supplier = I('post.stock_supplier', '', 'trim,strip_tags');
            // change_type 改变类型，
            // '1'（字符串） -> 出库
            // 其他 -> 正常入库
            $change_type = I('post.change_type','','trim,strip_tags');
            $operate_user = I('session.userinfo')['userid'];
            $operate_time = date("Y-m-d H:i:s");
            $log_to = I('post.location','','trim,strip_tags');
            $degree = I('post.degree','','trim,strip_tags');
        }    
    }
}
