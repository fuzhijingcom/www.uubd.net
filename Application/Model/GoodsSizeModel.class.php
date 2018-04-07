<?php
/**
 *
 * 用户账户日志模型
 * @author hp
 *
 */
namespace Model;

class GoodsSizeModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("GoodsSize",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}