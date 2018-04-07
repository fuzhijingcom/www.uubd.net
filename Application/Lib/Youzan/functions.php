<?php

/**
 * 从 sku 中的 properties_name_json 中获取属性
 * @param $str
 * @return mixed 有符合要求款式要求的则返回款式类型（如 C1，C2，C3Y, C2-14），否则返回原字符串
 */
function getStyle($str) {
    $property = json_decode($str);

    $v_str = $property[0]->v;

    return getStyleHead($v_str);
}

/**
 * 获取款式的头部
 * 突变的格式为：
 * B/C/T+数字+字母（可省略），如 C21，C3Y, C2-14 等等
 * @param $str
 * @return mixed
 */
function getStyleHead($str) {
    $pattern = '#^([BCT]\d+(\-)?[0-9A-Za-z]*).*$#';

    preg_match($pattern, $str, $match);

    if (isset($match[1])) {
        return $match[1];
    } else {
        return $str;
    }
}

function getStyleFull($str) {
    $property = json_decode($str);
    $v_str = $property[0]->v;
    return $v_str;
}

/**
 * 获取镜片信息
 * @param $str
 * @return mixed
 */
function getLens($str) {
    $property = json_decode($str);
    $v_str = isset($property[1]->v) ? $property[1]->v : '';
    return $v_str;
}


/**
 * 从属性字符串中提取出款式信息
 * @param $property
 * @param $selling_id
 * @return mixed
 */
function getStyleFromProperty($property, $selling_id = '') {
    if ($selling_id != 'G6401') {
        if (strpos($property, '款式') !== false) {
            $pattern = '/.*款式:([^;]*).*/';

            preg_match($pattern, $property, $match);

            if (isset($match[1])) {
                $str = & $match[1];

                $pattern = '#^(C\d+).*$#';

                preg_match($pattern, $str, $match);

                if (isset($match[1])) {
                    return $match[1];
                } else {
                    return $str;
                }
            } else {
                return $property;
            }
        } else {
            return $property;
        }
    } else {
        // 处理泳镜（G6401）的情况

        $data = array();

        // 获取款式
        if (strpos($property, '颜色') !== false) {
            $pattern = '/.*颜色:([^;]*).*/';

            preg_match($pattern, $property, $match);

            if (isset($match[1])) {
                $str = & $match[1];

                $pattern = '#^([BC]\d+).*$#';

                preg_match($pattern, $str, $match);

                if (isset($match[1])) {
                    $data['style'] = $match[1];
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        // 获取度数
        if (strpos($property, '种类') !== false) {
            $pattern = '/.*种类:([^;]*).*/';

            preg_match($pattern, $property, $match);

            if (isset($match[1])) {
                $str = trim($match[1]);

                $pattern = '#^([0-9]*).*$#';

                preg_match($pattern, $str, $match);

                if (isset($match[1])) {
                    $data['degree'] = $match[1];
                } else {
                    $data['degree'] = '';
                }
            } else {
                return false;
            }
        } else {
            return false;
        }

        return $data;
    }
}

/**
 * 从 sku 中的 properties_name 中获取套餐类型
 * SKU 的套餐类型如：
 * ① 框架眼镜：带镜片的套餐，镜架单品；
 * ② 太阳镜：有度数，无度数。
 * @param $str
 * @return bool
 */
function isSingleItem($str) {
    if (strpos($str, '镜架单品') !== false
        || strpos($str, '无度数') !== false
    ) {
        return true;
    } else {
        return false;
    }
}

/**
 * 从字符串中提取尾部（或头部）的数字与标志符号字母
 * K->框架眼镜；T->太阳眼镜；G->功能眼镜；Y->隐形眼镜；H->护理液
 * 注意：护理液（H）的商城编号写在商品名的前面，而不是尾部！
 * @param string $str
 * @return string
 */
function getHeadOrTailStr($str = '') {
    $str = trim($str);
    if (empty($str)) {
        return '';
    }

    $result = '';
    // 尝试从尾部获取标志数字或字母
    for ($i = strlen($str) - 1; $i >= 0; --$i){
        if(is_numeric($str[$i]) ||
            ( stripos('KTGYH', $str[$i]) !== false && $i !== 0)
        ){
            $result = $str[$i] . $result;
        } else {
            break;
        }
    }

    // 若尾部没有标记字，则从头部读取
    if ($result === '') {
        for ($i = 0, $length = strlen($str) - 1; $i <= $length; ++$i){
            if((is_numeric($str[$i]) && $i !== 0) ||
                (stripos('H', $str[$i]) !== false && $i === 0)
            ){
                $result .= $str[$i];
            } else {
                break;
            }
        }
    }

    return $result;
}

/**
 * 从商品名字符串获取线上编号
 * @param $str
 * @return string
 */
function getSellingId($str) {
    $str = trim($str);
    $pos = strrpos($str, ' ');
    $selling_id = substr($str, $pos + 1);

    $pattern = '#^\d+$#';
    if (preg_match($pattern, $selling_id)) {
        return $selling_id;
    } else {
        $selling_id = getHeadOrTailStr($str);
        
        if (empty($selling_id)) {
            return $str;
        } else {
            return $selling_id;
        }
    }
}

/**
 * 处理数据
 * @param $item_list array 这是有赞的数据结构
 * @param $result array 结果，处理数据后，数据将存储到此数组中
 * @param bool $type bool true:上架商品，false:库存中的商品
 */
function handleItemsResult(& $item_list, & $result, $type = true) {
    foreach ($item_list as $value) {
        $items = $value['response']['items'];

        foreach ($items as $item) {
            $title = $item['title'];
            $selling_id =  getSellingId($title);
            $num_iid = $item['num_iid'];
            $single_price = $item['price'];
            $is_listing = intval($item['is_listing']);
            $share_url = $item['share_url'];
            $detail_url = $item['detail_url'];

            foreach ($item['skus'] as $sku) {
                $sku_id = $sku['sku_id'];
                $sku_unique_code = $sku['sku_unique_code'];
                $quantity = $sku['quantity'];
                $price = $sku['price'];
                $style = getStyle($sku['properties_name_json']);
                $style_full = getStyleFull($sku['properties_name_json']);
                $classify = getGoodsClassify($title);

                $result[] = array(
                    'num_iid' => $num_iid,
                    'title' => $title,
                    'sku_id' => $sku_id,
                    'sku_unique_code' => $sku_unique_code,
                    'selling_id' => $selling_id,
                    'style' => $style,
                    'style_full' => $style_full,
                    'classify' => $classify,
                    'quantity' => $quantity,
                    'price' => $price,
                    'single_price' => $single_price,
                    'type' => $type,
                    'is_listing' => $is_listing,
                    'share_url' => $share_url,
                    'detail_url' => $detail_url,
                );
            }
        }
        unset($item);
    }
    unset($items);
}

/**
 * 获取线上编号
 * @param $selling_id
 * @return string
 */
function getGoodsId($selling_id) {
    $model = M('stock_goods');

    $map['selling_id'] = $selling_id;
    $field = array(
        'goods_id' => 'goods_id',
    );

    $result = $model->where($map)->field($field)->find();

    if (isset($result['goods_id']) && !empty($result['goods_id'])) {
        return $result['goods_id'];
    } else {
        return '无';
    }
}

/**
 * 获取订单交易状态信息
 * @param $status
 * @return bool
 */
function getTradeStatusFromYouzan($status) {
    $arr = array(
        'TRADE_BUYER_SIGNED' => true,
        'WAIT_BUYER_CONFIRM_GOODS' => true,
        'WAIT_SELLER_SEND_GOODS' => true,
        'WAIT_GROUP' => true,
        
        'TRADE_CLOSED' => false,
        'TRADE_CLOSED_BY_USER' => false,
        'TRADE_NO_CREATE_PAY' => false,
        'WAIT_BUYER_PAY' => false,
        'ALL_WAIT_PAY' => false,
        'ALL_CLOSED' => false,
    );

    if (isset($arr[$status])) {
        return $arr[$status];
    } else {
        return false;
    }

}


/**
 * 根据商品名获取商品分类
 * ——2016-08-17
 * @param $name
 * @return string
 */
function getGoodsClassify($name) {
    if (strpos($name, '测试') !== false
        || stripos($name, 'TEST') !== false) {
        
        return '测试';
    } elseif (strpos($name, '太阳') !== false) {
        return '太阳镜';
    } elseif (strpos($name, '老花镜') !== false) {
        return '老花镜';
    } elseif (strpos($name, '球面') !== false
        || strpos($name, '学生渐进') !== false
        || strpos($name, '依视路') !== false) {

        return '镜片';
    } elseif (strpos($name, '泳镜') !== false
        || strpos($name, '骑行镜') !== false
        || strpos($name, '运动') !== false) {

        return '功能眼镜';
    } elseif (strpos($name, '补差价') !== false) {
        return '补差价';
    } elseif (strpos($name, '加工费') !== false) {
        return '加工费';
    } elseif (strpos($name, '框') !== false
        || strpos($name, '架') !== false
        || strpos($name, '通用') !== false) {

        return '框架眼镜';
    } elseif (strpos($name, '抛') !== false) {
        return '隐形眼镜';
    } else {
        return '其他';
    }
}
