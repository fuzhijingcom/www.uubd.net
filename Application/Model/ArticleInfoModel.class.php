<?php
/**
 *
 * 资讯详情模型
 * @author hp
 *
 */
namespace Model;

class ArticleInfoModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("ArticleInfo",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}