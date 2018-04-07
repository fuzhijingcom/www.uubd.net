<?php
namespace Home\Model;
use Home\Controller\PackageController;
use Think\Model;

class StockModel extends Model {
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
	 * 查询特定商品在特定仓库的库存数量接口
	 *
	 * @param $sku_id           string  商品的唯一标志值 sku_id，如 K6001C1, G6401C2D300
	 * @param $warehouse        string  仓库名，包括：总仓，新天地门店，南亭门店，广大门店，科贸门店 等，默认选定总仓数量
	 * @return int              返回库存数量
	 */
	public function getQuantity($sku_id, $warehouse = '总仓') {
		$goods_info = $this->getInfoBySkuId($sku_id, $warehouse);

		// 若不存在商品库存信息，则返回 0
		if (empty($goods_info)) {
			return 0;
		}

		return intval($goods_info['quantity']);
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
			'H' => 'sku_id,selling_id,goods_name,price,style attr,sum(quantity) quantity,is_listing,is_replenish,supplier,procurement_price,brand,warehouse,update_time',
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
	 * 根据商城编号获取商品名称
	 *
	 * @param $selling_id
	 * @return string|bool
	 */
	public function getGoodsName($selling_id) {
		$model_name = $this->getGoodsModelBySellingId($selling_id);

		$Goods = M($model_name);

		$map = array(
			'selling_id' => $selling_id,
		);

		$result = $Goods->where($map)->field('goods_name')->find();

		if ($result != null) {
			return $result['goods_name'];
		}  else {
			return false;
		}
	}

	/**
	 * 根据条件获取商品名
	 *
	 * 条件一般为 selling_id，但对镜腿类型则是 sku_id，
	 * 且镜腿返回的商品名为原商品名拼接上款型，其他商品则为原商品名
	 *
	 * @param $cond_id
	 * @return bool|string
	 */
	public function getGoodsNameSpec($cond_id) {
		$cat_id = substr($cond_id, 0, 1);

		if ($cat_id !== 'U') {
			return $this->getGoodsName($cond_id);
		} else {
			// 键腿类型特殊处理

			$model_name = $this->getGoodsModelByCat($cat_id);

			$Goods = M($model_name);

			$map = array(
				'sku_id' => $cond_id,
			);
			$field = 'concat(goods_name, \'-\', style) goods_name';

			$result = $Goods->where($map)->field($field)->find();

			if ($result != null) {
				return $result['goods_name'];
			}  else {
				return false;
			}
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

			if ($warehouse !== '') {
				$map['warehouse'] = $warehouse;
			}

			$goods_info = $Info->where($map)->find();

			unset($goods_info['update_time']);
			unset($goods_info['update_user']);

			return $goods_info;
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
	public function getOrderGoodsData($time,$g_name = '',$s_name = '',$selling_id = '') {


		$fields = array(
//			'SUM(itl.sold_num)'  =>  'sold_num',
        '*'
		);


		$where = '1';

		// 获取指定周期
		if (! empty($time)) {
			$start = $time['start_time'];
			$end = $time['end_time'];
			//拼接时间范围条件
			$where .= " and td.created > '{$start}' and td.created < '{$end}'";
		}
		/*查询某一个类型的记录*/
        if(! empty($g_name)){
            if($g_name == '其他商品'){ //已排除镜片
                $where .= " and (il.category = '{$g_name}' or il.category = '加工费' or il.category = '其他' or il.category = '补差价' or il.category = '镜腿') and il.sku_unique_code NOT LIKE 'h11%'";
            }else{

                $where .= " and il.category = '{$g_name}'";
            }
        /*查询全部类型的记录*/
        }else{
            $where .=" and il.sku_unique_code NOT LIKE 'h11%'";
        }
        if(! empty($s_name)){
            $where .= " and td.shop = '{$s_name}'";
        }
        if(! empty($selling_id)){
            $where .= " and il.stock_sku_id like '{$selling_id}%'";
        }

        $where .= " and td.status IN('TRADE_BUYER_SIGNED','WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS')";

		$ItemTradeListModel = M('ItemList');
		$result = $ItemTradeListModel ->alias('il')
			->field($fields)
			->join('left join item_trade_list as itl on il.id = itl.item_id')
			->join('left join tradedetail as td on itl.tid = td.tid')
			->where($where)
            ->order('td.created asc')
//			->group('il.stock_sku_id, il.title')
			->select();

		return $result;
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

		$headArr['U'] = array(
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

		if (array_key_exists($cat_id, $headArr)) {
			return $headArr[$cat_id];
		} else {
			return false;
		}
	}


	// TODO: 下面是库存Ⅱ期新增的方法
	// TODO: 这些方法目前是用 hardcode 方式实现
	// TODO: 后续将这些方法的信息抽取出来放到可配置文件中

	/**
	 * 获取所有商品的分类值数组
	 *
	 * @return array
	 */
	public function getCategoryArr() {
		$cat_arr = array('K', 'T', 'G', 'Y', 'U', 'H');

		return $cat_arr;
	}

	/**
	 * 获取特定商品类型的特定字段
	 * TODO: 根据配置信息自动生成
	 *
	 * @param $cat_id
	 * @return string
	 */
	public function getAttachField($cat_id) {
		$str = '';
		if ($cat_id === 'K' || $cat_id == 'T' || $cat_id == 'U') {
			$str .= ',style';
		}

		if ($cat_id === 'G') {
			$str .= ',style,degree';
		}

		if ($cat_id === 'Y') {
			$str .= ',degree';
		}

		return $str;
	}

	/**
	 * 获取商品列表所需的表头标题信息
	 *
	 * 此信息返回到前端，确定哪些字段需要显示，哪些不要显示
	 *
	 * TODO: 待在配置中添加此类信息，做到更灵活的方式实现这些内容
	 *
	 * @param string $cat_id 商品分类 id
	 * @return bool|mixed
	 */
	public function getTableHead($cat_id) {
		$table_head_arr = array(
			'K' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'sold_num',
					'value' => '总销量',
					'show' => true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'goods_name',
					'value' => '商品名称',
					'show' => false,
				),
				array(
					'name' => 'brand',
					'value' => '品牌',
					'show' => false,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'T' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'sold_num',
					'value' => '总销量',
					'show' => true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'goods_name',
					'value' => '商品名称',
					'show' => false,
				),
				array(
					'name' => 'brand',
					'value' => '品牌',
					'show' => false,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'G' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'degree',
					'value' => '度数',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'sold_num',
					'value' => '总销量',
					'show' => true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'goods_name',
					'value' => '商品名称',
					'show' => false,
				),
				array(
					'name' => 'brand',
					'value' => '品牌',
					'show' => false,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'Y' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'goods_name',
					'value' => '商品名称',
					'show' => true,
				),
				array(
					'name' => 'brand',
					'value' => '品牌',
					'show' => true,
				),
				array(
					'name' => 'degree',
					'value' => '度数',
					'show' => true,
				),
				array(
					'name' => 'custom',
					'value' => '定制类型',
					'show' => false,
				),
				array(
					'name' => 'water',
					'value' => '含水量',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'sold_num',
					'value' => '总销量',
					'show' => true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'H' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'attr',
					'value' => '属性',
					'show' => true,
				),
				array(
					'name' => 'goods_name',
					'value' => '商品名称',
					'show' => true,
				),
				array(
					'name' => 'brand',
					'value' => '品牌',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'sold_num',
					'value' => '总销量',
					'show' => true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'U' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'sold_num',
					'value' => '总销量',
					'show' => true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'goods_name',
					'value' => '商品名称',
					'show' => false,
				),
				array(
					'name' => 'brand',
					'value' => '品牌',
					'show' => false,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),
		);

		if (array_key_exists($cat_id, $table_head_arr)) {
			return $table_head_arr[$cat_id];
		} else {
			return false;
		}
	}

	/**
	 * 获取提交表单（新增商品，新增品类，保存信息）的结构信息
	 * @param $cat_id
	 * @return bool|mixed
	 */
	public function getFormStruct($cat_id) {
		$form_struct_arr = array(
			'K' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'T' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'G' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'degree',
					'value' => '度数',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'Y' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'degree',
					'value' => '度数',
					'show' => true,
				),
				array(
					'name' => 'custom',
					'value' => '定制类型',
					'show' => false,
				),
				array(
					'name' => 'water',
					'value' => '含水量',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'H' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'attr',
					'value' => '属性',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),

			'U' => array(
				array(
					'name' => 'sku_id',
					'value' => 'sku_id',
					'show' => false,
				),
				array(
					'name' => 'selling_id',
					'value' => '商城编号',
					'show' => true,
				),
				array(
					'name' => 'product_id',
					'value' => '生产编号',
					'show' => true,
				),
				array(
					'name' => 'style',
					'value' => '款型',
					'show' => true,
				),
				array(
					'name' => 'warehouse',
					'value' => '所在位置',
					'show' => true,
				),
				array(
					'name' => 'quantity',
					'value' => '库存数量',
					'show' => true,
				),
				array(
					'name' => 'price',
					'value' => '单品价格',
					'show' => true,
				),
				array(
					'name' => 'is_listing',
					'value' => '上下架状态',
					'show' =>  true,
				),
				array(
					'name' => 'update_time',
					'value' => '更新时间',
					'show' => true,
				),
				array(
					'name' => 'supplier',
					'value' => '供货商',
					'show' => false,
				),
				array(
					'name' => 'procurement_price',
					'value' => '采购价',
					'show' => false,
				),
				array(
					'name' => 'is_replenish',
					'value' => '能否补货',
					'show' => false,
				),
			),
		);

		if (array_key_exists($cat_id, $form_struct_arr)) {
			return $form_struct_arr[$cat_id];
		} else {
			return false;
		}
	}


	/**
	 * 根据商品类型返回需要获取的非共有属性信息
	 *
	 * 此方法在 新增商品、新增品类、商品信息修改方法 用到，
	 * 同时也在 获取商品属性 信息列表的方法中间接用到
	 *
	 * @param $cat_id
	 * @return bool|mixed
	 */
	public function getDataFieldsByCat($cat_id) {
		$fields = array(
			'K' => array(
				// 字段名 => array()
				'product_id' => array(
					'default' => '', // 默认值
					'is_need' => false, // 是否必须
				),
				'style' => array(
					'default' => '',
					'is_need' => true,
					'name' => '款型',
					'pattern' => '#^([BC]\d+(\-)?[0-9A-Za-z]*).*$#',
				),
			),
			'T' => array(
				'product_id' => array(
					'default' => '',
					'is_need' => false,
				),
				'style' => array(
					'default' => '',
					'is_need' => true,
					'name' => '款型',
					'pattern' => '#^(C\d+(\-)?[0-9A-Za-z]*).*$#',
				),
			),
			'G' => array(
				'product_id' => array(
					'default' => '',
					'is_need' => false,
				),
				'style' => array(
					'default' => '',
					'is_need' => true,
					'name' => '款型',
					'pattern' => '#^(C\d+(\-)?[0-9A-Za-z]*).*$#',
				),
				'degree' => array(
					'default' => '',
					'is_need' => false,
					'name' => '度数',
					'prefix' => 'D',
				)
			),
			'Y' => array(
				'degree' => array(
					'default' => '',
					'is_need' => true,
					'name' => '度数',
					'prefix' => 'D',
					'pattern' => '#^([0-9]*).*$#',
				),
				'custom' => array(
					'default' => '',
					'is_need' => false,
				),
				'water' => array(
					'default' => '',
					'is_need' => false,
				)
			),
			'H' => array(
				'attr' => array(
					'default' => '',
					'is_need' => false,
				)
			),
			'U' => array(
				// 字段名 => array()
				'product_id' => array(
					'default' => '', // 默认值
					'is_need' => false, // 是否必须
				),
				'style' => array(
					'default' => '',
					'is_need' => true,
					'name' => '款型',
					'pattern' => '#^(T\d+(\-)?[0-9A-Za-z]*).*$#',
				),
			),
		);

		if (array_key_exists($cat_id, $fields)) {
			return $fields[$cat_id];
		} else {
			return false;
		}
	}

	/**
	 * 根据商品的 sku_id 来获取决定库存的属性列表字段
	 * 
	 * @param $sku_id
	 * @return array|bool|mixed
	 */
	public function getAttrFieldsBySkuId($sku_id) {
		$cat_id = substr($sku_id, 0, 1);
		$selling_id = substr($sku_id, 0, 5);

		if ($selling_id === 'G6401') {
			return array(
				'style' => '款型',
				'degree' => '度数',
			);
		}

		$fields = array(
			'K' => array(
				'style' => '款型',
			),
			'T' => array(
				'style' => '款型',
			),
			'G' => array(
				'style' => '款型',
			),
			'Y' => array(
				'degree' => '度数',
			),
			'H' => array(
				'style' => '属性',
			),
			'U' => array(
				'style' => '款型',
			),
		);

		if (array_key_exists($cat_id, $fields)) {
			return $fields[$cat_id];
		} else {
			return false;
		}
	}

	/**
	 * 根据商品类型获取返回的字段（给进货提醒使用）
	 * @param $cat_id
	 * @return bool|mixed
	 */
	public function getNoticeFieldsByCatId($cat_id) {
		$fields = array(
			'K' => 'product_id,style attribute',
			'T' => 'product_id,style attribute',
			'G' => 'product_id,concat_ws(\'-\',style,degree) attribute',
			'Y' => 'degree attribute',
			'H' => 'style attribute',
			'U' => 'product_id,style attribute',
		);

		if (array_key_exists($cat_id, $fields)) {
			return $fields[$cat_id];
		} else {
			return false;
		}
	}

	/**
	 * 判断仓库的名称是否合理（防止乱填）
	 *
	 * @param $warehouse
	 * @return bool
	 */
	public function isCorrectWarehouse($warehouse) {
		$w_data = $this->getWareHouseData();

		$w_arr = array();
		foreach ($w_data as $w) {
			$w_arr[$w['w_name']] = true;
		}
		unset($w);

		if (isset($w_arr[$warehouse])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 对应更改类型
	 *
	 * @param $index
	 * @return mixed|string
	 */
	public function changeType($index) {
		$types = array(
			'sku_id' => 'sku_id值',
			'selling_id' => '商城编号',
			'product_id' => '生产编号',
			'warehouse' => '仓库名称',
			'price' => '价格',
			'goods_name' => '商品名称',
			'quantity' => '库存数量',
			'is_listing' => '上下架状态',
			'is_replenish' => '能否补货情况',
			'supplier' => '供货商',
			'brand' => '品牌',
			'style' => '款型信息',
			'degree' => '度数',
			'custom' => '定制类型',
			'water' => '含水量',
			'combo_group_id' => '套餐组合类型',
		);

		if (array_key_exists($index, $types)) {
			return $types[$index];
		} else {
			return '其他值';
		}
    }

	/**
	 * 更新信息到有赞，只更新库存数量和上下架信息
	 * @param $sku_id
	 * @param $warehouse
	 * @param $options
	 * @return bool
	 */
    public function synDataToYouzan($sku_id, $warehouse, $options) {
		if (C('YOUZAN_DEBUG') === false) {
			if ($this->GoodsOnYouzan($sku_id) && $this->isMainStock($warehouse)) {
				// 要出道的变更数据
				$goods_info = $this->getInfoBySkuId($sku_id, $warehouse);
				if (C('YOUZAN_DATA_TEST') === false) {
					// 实际获取的数据
					$num_iid = $this->getNumiid($sku_id);
					$style = $goods_info['style'];

					$degree = isset($goods_info['degree']) ? $goods_info['degree'] : '';

					$attr_arr = array();
					if (! empty($style)) {
						$attr_arr['style'] = $style;
					}

					if (! empty($degree)) {
						$attr_arr['degree'] = $degree;
					}


				} else {
					// 此处填入测试专用的商品 id
					$num_iid = 293684019;
					$style = 'C11';
					$degree = 100;

					$attr_arr = array();
					if (! empty($style)) {
						$attr_arr['style'] = $style;
					}

					if (! empty($degree)) {
						$attr_arr['degree'] = $degree;
					}

				}

				// 先过滤掉泳镜判断
				// '255660080', // 泳镜，对应线上商品号为 66901
				if (intval($num_iid) === 255660080) {
					return true;
				}

				// 仅当 $num_iid 存在时才对有赞进行相关操作
				if ($num_iid !== false) {
					$youzan_stock = new \Lib\Youzan\Stock();
					
				    return $youzan_stock->updateGoodsInfo($num_iid, $attr_arr, $options);
				}
			}
		}
	}
}
