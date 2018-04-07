<?php
/**
 *
 * 用户等级模型
 * @author hp
 *
 */
namespace Model;

class UserLevelModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("UserLevel",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}