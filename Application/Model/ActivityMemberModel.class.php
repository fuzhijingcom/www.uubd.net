<?php
/**
 *
 * 活动参与者模型
 * @author hp
 *
 */
namespace Model;

class ActivityMemberModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("ActivityMember",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}