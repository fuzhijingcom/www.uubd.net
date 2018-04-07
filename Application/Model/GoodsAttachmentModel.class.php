<?php
/**
 *
 * 商品模型
 * @author hp
 *
 */
namespace Model;

class GoodsAttachmentModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("GoodsAttachment",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}