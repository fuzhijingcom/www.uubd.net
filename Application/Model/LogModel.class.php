<?php
/**
 *
 * 日志模型
 * @author hp
 *
 */
namespace Model;

class LogModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Log",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}