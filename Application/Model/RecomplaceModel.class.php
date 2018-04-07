<?php
/**
 *
 * 广告模型
 * @author hp
 *
 */
namespace Model;

class RecomplaceModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("RecommendPlace",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}