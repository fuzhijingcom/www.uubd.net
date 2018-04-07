<?php
/**
 *
 * 资讯分类模型
 * @author hp
 *
 */
namespace Model;

class ArticleCategoryModel extends BaseModel{

	public function __construct(){
		$this->_Model = M("ArticleCategory",C("DB_MALL.db_prefix"),'DB_MALL');
	}
}