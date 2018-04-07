<?php
/**
 *
 * 广告模型
 * @author hp
 *
 */
namespace Model;

class AdModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Ad",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}