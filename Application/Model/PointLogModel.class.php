<?php
/**
 *
 * 积分日志模型
 * @author hp
 *
 */
namespace Model;

class PointLogModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("PointLog",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}