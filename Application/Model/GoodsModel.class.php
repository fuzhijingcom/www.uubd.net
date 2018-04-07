<?php
/**
 *
 * 商品模型
 * @author hp
 *
 */
namespace Model;

class GoodsModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Goods",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}