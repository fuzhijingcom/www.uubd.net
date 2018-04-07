<?php
/**
 *
 * 后台权限组模型
 * @author hp
 *
 */
namespace Model;

class GroupModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Group",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}