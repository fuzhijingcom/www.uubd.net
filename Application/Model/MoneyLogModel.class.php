<?php
/**
 *
 * 用户金额日志模型
 * @author hp
 *
 */
namespace Model;

class MoneyLogModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("MoneyLog",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}