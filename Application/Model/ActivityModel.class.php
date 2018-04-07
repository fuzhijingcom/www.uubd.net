<?php
/**
 *
 * 活动模型
 * @author hp
 *
 */
namespace Model;

class ActivityModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Activity",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}