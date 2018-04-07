<?php
/**
 *
 * 广告位模型
 * @author hp
 *
 */
namespace Model;

class AdPlaceModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("AdPlace",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}