<?php
/**
 *
 * 订单模型
 * @author hp
 *
 */
namespace Model;

class OrderModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Order",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}