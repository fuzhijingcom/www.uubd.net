<?php
/**
 *
 * 城市模型
 * @author hp
 *
 */
namespace Model;

class CityModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("City",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}