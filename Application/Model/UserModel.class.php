<?php
/**
 *
 * 用户模型
 * @author hp
 *
 */
namespace Model;

class UserModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("User",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}