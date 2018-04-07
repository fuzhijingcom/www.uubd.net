<?php
/**
 *
 * 资讯模型
 * @author hp
 *
 */
namespace Model;

class ArticleModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("Article",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}