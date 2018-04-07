<?php
/**
 *
 * 用户账户模型
 * @author hp
 *
 */
namespace Model;

class AccountModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Account",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}