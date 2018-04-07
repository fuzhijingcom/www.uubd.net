<?php
/**
 *
 * 省份模型
 * @author hp
 *
 */
namespace Model;

class ProvinceModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Province",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}