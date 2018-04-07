<?php
/**
 *
 * 用户账户日志模型
 * @author hp
 *
 */
namespace Model;

class GoodsColorModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("GoodsColor",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}