<?php
/**
 *
 * 商品详情模型
 * @author hp
 *
 */
namespace Model;

class GoodsInfoModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("GoodsInfo",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}