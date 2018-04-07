<?php
namespace Home\Model;
use Think\Model;

class StockhistoryModel extends Model {
	protected $tableName = 'stock_history'; //设置tableName属性来改变默认的规则
	protected $pk = 'h_id'; //设置pk属性改变主键名称

	//获取库存记录表数据
	public function getStockHistory($map = '', $sort = '', $limit = '', $field = '') {
		$model = M('stock_history');
		
		$stockList = $model->where($map)->limit($limit)->order($sort)->field($field)->select();

		return $stockList;
	}

	//插入库存记录表
	public function addStockHistory($data) {
		$model = M('stock_history');
		$result = $model->add($data);
		return $result;
	}

	//更新库存记录表
	public function updateStockHistory($map = '', $data) {
		$model = M('stock_history');
		$result = $model->where($map)->setField($data);
		// $sql = $model->getLastSql();
		return $result;
	}

	//删除库存记录信息
	public function deleteStockHistory($map) {
		$model = M('stock_history');
		$result = $model->where($map)->delete();
		return $result;
	}

	//查找库存信息
	public function findStock($map) {
		$stock = $this->where($map)->find();
		return $stock;
	}

	//获取查询库存的总量
	public function getStockCount($map = '', $sort = '', $limit = '') {
		$count = $this->where($map)->limit($limit)->sort($sort)->count();
		//$sql = $stock->getLastSql();
		return $count;
	}
	
	//获取某商品库存的销售总量
	public function getSalesNum($map){
		// 找出并计算销量
		$model = M('stock_history');
		$result = $model->where($map)->sum('update_number');

		return $result;
	}
}
