<?php
/**
 * 库存管理——调用有赞接口实现
 */
namespace Lib\Youzan;

if (!defined('YOUZAN_FUNCTION_ROOT')) {
    define('YOUZAN_FUNCTION_ROOT', dirname(__FILE__) . '/');
    require(YOUZAN_FUNCTION_ROOT . 'functions.php');
}

class Stock {
    private $_get_item_result; // 获取指定商品的结果

    // 对外接口的方法有这些：
    // updateItem()
    // changeItemQuantity()

    /**
     * 更新指定商品信息，注：目前只更新已有商品的库存 增量 数量（指定款式）和该商品的所有单品价格
     * @param $num_iid int 商品号
     * @param $style   string 款式
     * @param $delta_quantity int 变更增量
     * @param $price   int/float 价格
     * @return bool    成功->true；否则->false
     */
    public function updateItem($num_iid, $style, $delta_quantity, $price = -1) {
        $style = getStyleHead($style);

        if ($this->isExistStyle($num_iid, $style)) {
            $delta_quantity = intval($delta_quantity);
            $min_quantity = $this->updateStyleInfo($num_iid, $style, $delta_quantity);

            if (floatval($price) > 0.00) {
                $this->updateSingleItemPrice($num_iid, $price);
            }

            return $min_quantity;
        } else {
            return false;
        }
    }

    /**
     * 更新指定商品信息，注：目前只更新已有商品的库存 绝对值 数量（指定款式）和该商品的所有单品价格
     * @param $num_iid int 商品号
     * @param $style   string 款式
     * @param $quantity int 变更的绝对增量，注意这与上面的方法不一样
     * @param $price   int/float 价格
     * @return bool    成功->true；否则->false
     */
    public function updateItemByAbsolute($num_iid, $style, $quantity, $price = -1) {
        $style = getStyleHead($style);

        if ($this->isExistStyle($num_iid, $style)) {
            $result = $this->changeItemQuantity($num_iid, $style, $quantity);

            if ($result === false) {
                return false;
            }

            if (floatval($price) > 0.00) {
                $this->updateSingleItemPrice($num_iid, $price);
            }

            return $quantity;
        } else {
            return false;
        }
    }

    /**
     * 改变商品的库存数量，注意，这是绝对数量（非增量变量）
     *
     * @param $num_iid  int    商品名
     * @param $style    string 款式
     * @param $quantity int   绝对数量
     * @return bool     成功->true；失败->false
     */
    public function changeItemQuantity($num_iid, $style, $quantity) {
        if ($quantity < 0) {
            return false;
        } else {
            $quantity = intval($quantity);
        }

        $style = getStyleHead($style);

        if ($this->isExistStyle($num_iid, $style)) {
            $skus = $this->getSkus($num_iid, $style);

            foreach ($skus as $sku) {
                $sku_id = $sku['sku_id'];

                $result = $this->updateSkuInfo($num_iid, $sku_id, $quantity);

                if ($result === false) {
                    return false;
                }
            }
        }
        
        return true;
    }


    /**
     * 改变商品的信息到有赞
     *
     * 包括：库存数量和上下架状态
     * 注意，数量更新是绝对数量（非增量变量）
     *
     * @param $num_iid
     * @param $attr_arr
     * @param $options
     * @return bool
     */
    public function updateGoodsInfo($num_iid, $attr_arr, $options) {
        $return_flag = false;
        $style = isset($attr_arr['style']);
        // $degree = isset($attr_arr['degree']); 暂时用不到此属性
        if (empty($style)) {
            return false;
        }

        if (isset($options['quantity']) && is_int($options['quantity'])) {
            $quantity = intval($options['quantity']);

            if ($quantity >= 0) {
                $return_flag = $this->changeItemQuantity($num_iid, $style, $quantity);
            }
        }

        if (isset($options['is_listing'])) {
            $return_flag = $this->updateListing($num_iid, $options['is_listing']);
        }
        
        return $return_flag;
    }

    // 1、更新数量功能

    /**
     * 更新特定 SKU 的库存数量
     * @param $num_iid
     * @param $sku_id
     * @param int $quantity 当为默认值 -1 时，则不改动数量
     * @param float $price 当为默认值 -1 时，则不改动价格
     * @return bool
     */
    public function updateSkuInfo($num_iid, $sku_id, $quantity = -1, $price = -1.00) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'num_iid'  => $num_iid,
            'sku_id'   => $sku_id,
        );

        if ($quantity !== -1) {
            if ($quantity < 0) {
                return false;
            } else {
                $params['quantity'] = $quantity;
            }
        }

        if ($price > 0) {
            $params['price'] = $price;
        }

        return $youzan->updateSku($params);
    }

    /**
     *  获取某种款式对应下的所有  Skus 项
     * @param $num_iid
     * @param $style
     * @return array
     */
    public function getSkus($num_iid, $style) {
        $arr = array();

        if (empty($this->_get_item_result)) {
            vendor('Youzan.Youzan');
            $youzan = \Youzan::getInstance();
            $params = array(
                'num_iid' => $num_iid,
                'fields' => 'skus',
            );
            $this->_get_item_result = $youzan->getItem($params);
            $item = & $this->_get_item_result;
        } else {
            $item = & $this->_get_item_result;
        }

        if (isset($item['response']['item']['skus'])) {
            $skus = & $item['response']['item']['skus'];

            foreach ($skus as $sku) {
                $property = json_decode($sku['properties_name_json']);

                $v_str = $property[0]->v;
                $pattern = '#^(C\d+).*$#';

                preg_match($pattern, $v_str, $match);

                $is_single = isSingleItem($sku['properties_name']);

                if (isset($match[1]) && $match[1] === $style) {
                    $arr[] = array(
                        'sku_id' => $sku['sku_id'],
                        'quantity' => $sku['quantity'],
                        'is_single' => $is_single,
                    );
                } elseif (empty($match) && $v_str === $style) {
                    $arr[] = array(
                        'sku_id' => $sku['sku_id'],
                        'quantity' => $sku['quantity'],
                        'is_single' => $is_single,
                    );
                }
            }
        }

        return $arr;
    }

    /**
     * 根据商品号获取商品中单品（镜框眼镜中的镜框单品，太阳镜无度数商品）的 sku 项
     * @param $num_iid
     * @return array
     */
    public function getSingleItemSkus($num_iid) {
        if (empty($this->_get_item_result)) {
            vendor('Youzan.Youzan');
            $youzan = \Youzan::getInstance();
            $params = array(
                'num_iid' => $num_iid,
                'fields' => 'skus',
            );
            $this->_get_item_result = $youzan->getItem($params);
            $item = & $this->_get_item_result;
        } else {
            $item = & $this->_get_item_result;
        }

        $arr = array();

        if (isset($item['response']['item']['skus'])) {
            $skus = & $item['response']['item']['skus'];

            foreach ($skus as $sku) {
                $is_single = isSingleItem($sku['properties_name']);

                if ($is_single) {
                    $arr[] = array(
                        'sku_id' => $sku['sku_id'],
                        'quantity' => $sku['quantity'],
                        'price' => $sku['price'],
                        'is_single' => $is_single,
                    );
                }
            }
        }

        return $arr;
    }
    
    /**
     * 更新某种款式对应的所有 sku 对应的数量（变更增量）
     * @param $num_iid int 商品号
     * @param $style   string 款式
     * @param $delta_quantity int 变更增量
     * @return mixed 返回最小的值
     */
    public function updateStyleInfo($num_iid, $style, $delta_quantity) {
        $skus = $this->getSkus($num_iid, $style);

        $min_quantity = 100000000;

        foreach ($skus as $sku) {
            $sku_id = $sku['sku_id'];
            $quantity = intval($sku['quantity']) + $delta_quantity;

            if ($quantity < 0) {
                return false;
            }

            if ($quantity < $min_quantity) {
                $min_quantity = $quantity;
            }

            // 同一款式下的 SKU 项只变更库存数量，不改价格
            $result = $this->updateSkuInfo($num_iid, $sku_id, $quantity);

            if ($result === false) {
                return false;
            }
        }

        if ($min_quantity === 100000000) {
            return false;
        } else {
            return $min_quantity;
        }
    }

    /**
     * 更新某种款式对应的所有 sku 对应的数量（绝对数量）
     * @param $num_iid
     * @param $style
     * @param $quantity
     * @return int
     */
    public function updateStyleQuantity($num_iid, $style, $quantity) {
        $style = getStyleHead($style);

        $skus = $this->getSkus($num_iid, $style);

        foreach ($skus as $sku) {
            $sku_id = $sku['sku_id'];

            @ $this->updateSkuInfo($num_iid, $sku_id, $quantity);
        }

        return $quantity;
    }

    /**
     * 修改某个商品下的单品（框架眼镜的单品，或太阳眼镜的无度数产品）的价格
     * @param $num_iid
     * @param $price
     */
    public function updateSingleItemPrice($num_iid, $price) {
        $skus = $this->getSingleItemSkus($num_iid);

        foreach ($skus as $sku) {
            $sku_id = $sku['sku_id'];
            $get_price = $sku['price'];

            if (floatval($price) != $get_price) {
                // 商品下的 SKU 项只变更单品的价格，不改库存数量
                @ $this->updateSkuInfo($num_iid, $sku_id, -1, $price);
            }
        }
    }

    // 2、新增商品功能
    public function addItem() {
        
    }

    // 3、更新商品信息


    /**
     * 检测商品是否存在于有赞商城上
     * @param $num_iid int 商品号
     * @return bool 存在->true；否->false
     */
    public function isExistNumIid($num_iid) {
        if (empty($this->_get_item_result)) {
            vendor('Youzan.Youzan');
            $youzan = \Youzan::getInstance();
            $params = array(
                'num_iid' => $num_iid,
                'fields' => 'skus',
            );
            $this->_get_item_result = $youzan->getItem($params);
            $item = & $this->_get_item_result;
        } else {
            $item = & $this->_get_item_result;
        }

        if (isset($item['response']['item']['skus'])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断商品是否存在
     * @param $num_iid  int 商品号
     * @param $style    string 商品款式
     * @return bool     存在相应款式->true；否则->false
     */
    public function isExistStyle($num_iid, $style) {
        if ($this->isExistNumIid($num_iid)) {
            if (empty($this->_get_item_result)) {
                vendor('Youzan.Youzan');
                $youzan = \Youzan::getInstance();
                $params = array(
                    'num_iid' => $num_iid,
                    'fields' => 'skus',
                );
                $this->_get_item_result = $youzan->getItem($params);
                $item = & $this->_get_item_result;
            } else {
                $item = & $this->_get_item_result;
            }

            $data = & $item['response']['item']['skus'];

            $return_result = false;

            foreach ($data as $sku) {
                $get_style = getStyle($sku['properties_name_json']);

                if ($style == $get_style) {
                    $return_result = true;
                    break;
                }
            }
            return $return_result;
        } else {
            return false;
        }
    }

    /**
     * 获取同一款式下的所有SKU的库存量的最小值
     * @param $num_iid
     * @param $style
     * @return bool
     */
    public function getMinQuantity($num_iid, $style) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();
        $params = array(
            'num_iid' => $num_iid,
            'fields' => 'skus',
        );
        $this->_get_item_result = $youzan->getItem($params);
        $item = & $this->_get_item_result;

        $data = & $item['response']['item']['skus'];

        foreach ($data as $sku) {
            $get_style = getStyle($sku['properties_name_json']);

            if ($style == $get_style) {
                if (! isset($quantity) ||
                    (isset($quantity) && $quantity > $sku['quantity'])) {
                    $quantity = $sku['quantity'];
                }
            }
        }

        if (isset($quantity)) {
            return $quantity;
        } else {
            return false;
        }
    }

    /**
     * 获取一特定SKU的商品下的所有库存量和款式
     * @param $num_iid
     * @param $sku_id
     * @return bool
     */
    public function getQuantityAndStyleFromSku($num_iid, $sku_id) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();
        $params = array(
            'num_iid' => $num_iid,
            'fields' => 'skus,is_listing',
        );
        $this->_get_item_result = $youzan->getItem($params);
        $item = & $this->_get_item_result;

        $is_listing = intval($item['response']['item']['is_listing']);
        $data = & $item['response']['item']['skus'];

        $quantity = 0;
        $style = '';

        foreach ($data as $sku) {
            if ($sku_id == $sku['sku_id']) {
                $quantity = $sku['quantity'];
                $style = getStyle($sku['properties_name_json']);

                break;
            }
        }


        $result = array(
            'quantity' => $quantity,
            'style' => $style,
            'is_listing' => $is_listing,
        );
        
        return $result;
    }

    // TODO: 判断线上编号是否存在于有赞上 
    public function getNumIidFromSellingId($selling_id) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

       exit;
    }

    // 4、获取商品信息，如库存数量，名称……

    /**
     * 从商品名中获取线上编号
     * @param $str
     * @return bool|string
     */
    public function getSellingId($str) {
        $str = trim($str);
        $pos = strrpos($str, ' ');
        $selling_id = substr($str, $pos + 1);

        $pattern = '#^\d+$#';
        if (preg_match($pattern, $selling_id)) {
            return $selling_id;
        } else {
            return $str;
        }
    }

    /**
     * 从有赞上获取所有商品列表
     * @param string $type 接收参数值只有： onsale 和 inventory 两个
     * @return array
     */
    public function getAllItems($type = 'onsale') {
        set_time_limit(0);
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'fields' => 'num_iid,title,skus,price,is_listing,sold_num,share_url,detail_url',
            'page_size' => 40,
        );

        $params['page_no'] = 1;
        $result = array();
        switch ($type) {
            case 'onsale':
                while (true) {
                    $getResult = $youzan->getOnsaleItems($params);

                    if (isset($getResult['response'])
                        && count($getResult['response']['items']) > 0) {
                        $result[] = $getResult;
                        ++ $params['page_no'];
                    } else {
                        break;
                    }
                }
                break;
            case 'inventory':
                while (true) {
                    $getResult = $youzan->getInventoryItems($params);

                    if (isset($getResult['response'])
                        && count($getResult['response']['items']) > 0) {
                        $result[] = $getResult;
                        ++ $params['page_no'];
                    } else {
                        break;
                    }
                }
                break;
            default:
                exit('异常情况！');
        }
        
        return $result;
    }

    /**
     * 返回商品列表数据
     * @return array
     */
    public function getItemsList() {
        $onsale_list = $this->getAllItems('onsale');
        $inventory_list = $this->getAllItems('inventory');

        $result = array();
        handleItemsResult($onsale_list, $result, true);
        handleItemsResult($inventory_list, $result, false);

        return $result;
    }

    /**
     * 获取单商品列表（不区分属性）
     * 即将多个属性的同一类商品，只取最大类的信息
     *
     * @param array $cat_id_list 商品分类 ID 值，其中：K->框架眼镜；T->太阳镜；G->功能眼镜。
     *                           默认为空数组表示读取全部商品，若数组为 ['K', 'T'] 则取出相应类型商品
     * @param int $is_listing    获取指定上下架状态商品， -1->全部，0->下架，1->上架
     * @return array
     */
    public function getSingleItemsList($cat_id_list = array(), $is_listing = -1) {
        $arr = $this->getItemsList();

        $flag = array();
        $result = array();



        foreach ($arr as $item) {
            $y_is_listing = intval($item['is_listing']);

            if (intval($is_listing) !== -1) {
                if (intval($is_listing) !== $y_is_listing) {
                    continue;
                }
            }

            $num_iid = $item['num_iid'];
            
            if (isset($flag[$num_iid])) {
                continue;
            } else {
                $selling_id = $item['selling_id'];
                $share_url = $item['share_url'];

                if ($this->belongCatIds($selling_id, $cat_id_list)) {
                    $flag[$num_iid] = true;
                    $result[] = array(
                        'num_iid' => $num_iid,
                        'selling_id' => $selling_id,
                        'share_url' => $share_url,
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @param $selling_id
     * @param $cat_id_list
     * @return bool
     */
    private function belongCatIds($selling_id, $cat_id_list) {
        if (empty($cat_id_list)) {
            return true;
        } else {
            $first_letter = substr($selling_id, 0, 1);

            if (in_array($first_letter, $cat_id_list)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取款式分类的商品列表
     * @return array
     */
    public function getStyleItemsList() {
        $arr = $this->getItemsList();

        $flag = array();

        $result = array();

        foreach ($arr as $item) {
            $num_iid = $item['num_iid'];
            $style = $item['style'];

            if (isset($flag[$num_iid][$style])) {
                continue;
            } else {
                $flag[$num_iid][$style] = true;

                $selling_id = $item['selling_id'];
                $goods_id = getGoodsId($selling_id);

                $result[] = array(
                    'num_iid' => $num_iid,
                    'title' => $item['title'],
                    'selling_id' => $selling_id,
                    'goods_id' => $goods_id,
                    'style' => $item['style'],
                    'style_full' => $item['style_full'],
                    'classify' => $item['classify'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'single_price' => $item['single_price'],
                    'is_listing' => $item['is_listing'],
                    'share_url' => $item['share_url'],
                    'detail_url' => $item['detail_url'],
                );
            }
        }

        return $result;
    }

    /**
     * 模拟上下架操作（实际上是更新库存数量）
     * 下架：则库存数量都变为 0；
     * 上架：则将库存具体值传入 $quantity
     * @param $num_iid
     * @param $style
     * @param int $quantity
     * @return int
     */
    public function changeListing($num_iid, $style, $quantity = 0) {
        if ($quantity > 0) {
            $this->listingItem($num_iid);
        }
        return $this->updateStyleQuantity($num_iid, $style, $quantity);
    }

    /**
     * 真实上下架操作
     * 更新时间：2016-12-02
     *
     * @param $num_iid
     * @param $is_listing 0->下架；1->上架
     * @return mixed
     */
    public function updateListing($num_iid, $is_listing) {
        if (intval($is_listing) == 1) {
            return $this->listingItem($num_iid);
        } else {
            return $this->delistingItem($num_iid);
        }
    }

    /**
     * 对有赞的商品上架操作
     * @param $num_iid
     * @return mixed
     */
    public function listingItem($num_iid) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'num_iid' => $num_iid,
        );

        return $youzan->listingItem($params);
    }

    /**
     * 对有赞的商品下架操作
     * @param $num_iid
     * @return mixed
     */
    public function delistingItem($num_iid) {
        vendor('Youzan.Youzan');
        $youzan = \Youzan::getInstance();

        $params = array(
            'num_iid' => $num_iid,
        );

        return $youzan->delistingItem($params);
    }
}