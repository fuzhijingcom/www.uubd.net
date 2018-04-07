<?php
/**
 *
 * 活动商品模型
 * @author hp
 *
 */
namespace Model;

class ActivityGoodsModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("ActivityGoods",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}