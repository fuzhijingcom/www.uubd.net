<?php
/**
 *
 * 用户账户日志模型
 * @author hp
 *
 */
namespace Model;

class CouponModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Coupon",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}