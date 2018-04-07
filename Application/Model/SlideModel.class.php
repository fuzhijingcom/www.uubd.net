<?php
/**
 *
 * 幻灯片模型
 * @author hp
 *
 */
namespace Model;

class SlideModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Slide",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}