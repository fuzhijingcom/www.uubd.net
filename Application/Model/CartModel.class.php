<?php
/**
 *
 * 购物车模型
 * @author hp
 *
 */
namespace Model;

class CartModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Cart",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}