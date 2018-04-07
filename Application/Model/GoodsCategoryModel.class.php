<?php
/**
 *
 * 商品分类模型
 * @author hp
 *
 */
namespace Model;

class GoodsCategoryModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("GoodsCategory",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}