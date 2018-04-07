<?php
/**
 *
 * 订单退货模型
 * @author hp
 *
 */
namespace Model;

class OrderReturnChargeModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("OrderReturnCharge",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}