<?php
/**
 *
 * 地区模型
 * @author hp
 *
 */
namespace Model;

class DistrictModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("District",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}