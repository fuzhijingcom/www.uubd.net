<?php
/**
 *
 * 广告模型
 * @author hp
 *
 */
namespace Model;

class SystemConfigModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("SystemConfig",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}