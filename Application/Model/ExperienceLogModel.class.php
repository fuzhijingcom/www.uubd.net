<?php
/**
 *
 * 经验日志模型
 * @author hp
 *
 */
namespace Model;

class ExperienceLogModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("ExperienceLog",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}