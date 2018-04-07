<?php
/**
 *
 * 用户微信关联模型
 * @author hp
 *
 */
namespace Model;

class UserRelationModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("UserRelation",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}