<?php
namespace Home\Model;
use Think\Model;

class TradedetailModel extends Model {
	protected $tableName = 'tradedetail'; //设置tableName属性来改变默认的规则
	protected $pk = 'tid'; //设置pk属性改变主键名称

	//获取订单表数据
	public function getTradedetails($map = '', $sort = '', $limit = '', $field = '') {
		$orderList = $this->where($map)->limit($limit)->order($sort)->field($field)->select();
//		$sql = $this->getLastSql();
//		echo $sql;exit;
		return $orderList;
	}
	//分页获取订单数据
	public function get($map = '', $page = 1, $field = '', $sort = '') {
		$order = $this->page($page, 1)->where($map)->order($sort)->field($field)->select();
		return $order;
	}

	//插入订单表
	public function addTradedetail($data) {
		$result = $this->add($data);
		return $result;
	}

	//更新订单表
	public function updateTradedetail($map = '', $data) {
		$result = $this->where($map)->save($data);
		return $result;
	}

	//删除订单
	public function deleteTradedetail($map) {
		$result = $this->where($map)->delete();
		return $result;
	}

	//查找订单
	public function findTradedetail($map) {
		$order = $this->where($map)->find();
		return $order;
	}

	//获取查询订单的总量
	public function getTradedetailCount($map = '', $sort = '', $limit = '') {
		$count = $this->where($map)->limit($limit)->order($sort)->count();
		//$sql = $this->getLastSql();
		return $count;
	}

	//获取查询订单的支付金额
	public function getTradedetailSum($map = '', $field = 0) {
		$sum = $this->where($map)->sum($field);
		//$sql = $this->getLastSql();
		return $sum;
	}

	//原生sql查询
	public function querySql($sql = '') {
		$resultData = $this->query($sql);
		return $resultData;
	}

	public function getStoreTradedetail($store_name_str,$time){
		$where  = " shop IN ($store_name_str)";
		$where .= " and `status` IN ('TRADE_BUYER_SIGNED','WAIT_BUYER_CONFIRM_GOODS','WAIT_SELLER_SEND_GOODS')";
		$where .= " and created >= '{$time['startT']}'";
		$where .= " and created <= '{$time['endT']}'";
		$field  = array('tid','price','type','title','feedback','refund_state','status','payment','created','shop','promotion','privilege_id','partner_code_id','pay_type','is_redpack','redpack_price');
		$tradedetailData = $this -> field($field)->where($where)->select();
		return $tradedetailData;
	}

	public function getPartnerTradedetail($time){
		$where  = " `status` IN ('TRADE_BUYER_SIGNED','WAIT_BUYER_CONFIRM_GOODS','WAIT_SELLER_SEND_GOODS')";
		$where .= " and created >= '{$time['startT']}'";
		$where .= " and created <= '{$time['endT']}'";
        $where .= " and type != 'CONTACT_BUY'";
		$field = array('tid','price','type','title','feedback','refund_state','status','payment','created','shop','promotion','privilege_id','partner_code_id');
		$tradedetailData = $this -> field($field)->where($where)->select();
		return $tradedetailData;
	}

	public function getHistoryTradedetail(){
		$where['created']   = array('EGT','2016-10-01 00:00:00');
		$where['status']    = array('IN','TRADE_BUYER_SIGNED,WAIT_BUYER_CONFIRM_GOODS,WAIT_SELLER_SEND_GOODS');
        $where['type']      = array('NEQ','CONTACT_BUY');
		return $this->where($where)->select();
	}



}
