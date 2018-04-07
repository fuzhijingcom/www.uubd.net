<?php
/**
 *
 * 用户收货地址模型
 * @author hp
 *
 */
namespace Model;

class UserAddressModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("UserAddress",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}