<?php
/**
 *
 * 订单详情模型
 * @author hp
 *
 */
namespace Model;

class OrderGoodsModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("OrderGoods",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}