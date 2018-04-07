<?php
namespace Admin\Controller;
use Think\Controller\MasterController;
use Think\Page;
use Admin\Common\ACPopedom;

class OrderController extends MasterController {
	
	private $_Model = null;
    private $_rows = 20;
	private $_config = array();
	//private $_status_map = array("待付款","完成订单","取消订单","待发货","待收货","退货");
	private $_express=array();

    public function __construct(){
        $this->_Model = D("Order");
        //'汇通','yunda'=>'韵达','zhongtong'=>'中通','宅急送','申通','圆通','EMS','顺丰'
        $this->_express=array(
	        		'1'=>array('code'=>'yunda','name'=>'韵达'),
	        		'2'=>array('code'=>'zhongtong','name'=>'中通'),
	        		'3'=>array('code'=>'zhaijisong','name'=>'宅急送'),
	        		'4'=>array('code'=>'shentong','name'=>'申通'),
	        		'5'=>array('code'=>'yuantong','name'=>'圆通'),
	        		'6'=>array('code'=>'huitongkuaidi','name'=>'汇通'),
	        		'7'=>array('code'=>'ems','name'=>'EMS'),
	        		'8'=>array('code'=>'shunfeng','name'=>'顺丰'),
        			'9'=>array('code'=>'quanfengkuaidi','name'=>'全峰'),
        			'10'=>array('code'=>'annengwuliu','name'=>'安能小包'),
        		);
		$this->_config = get_config_cache("system@config");
		if(empty($this->_config) || $this->_config && empty($this->_config['account_name'])){
			$this->_config['account_name'] = "平台币";
		}
        parent::__construct();
    }
	
	/**
	* 所有订单列表
	*/
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "o.`exchange_type`=0 AND o.`is_delete`=0";
		if(trim(I('get.keyword'))){
			if(I("search_type")=="buyer"){
				$where .= " AND u.`username` LIKE '".I('get.keyword')."%'";
				$params["buyer"] = I('get.keyword');
			}else if(I("search_type")=="ordersn"){
				$where .= " AND o.`order_sn`='".I('get.keyword')."%'";
				$params["ordersn"] = I('get.keyword');
			}else{
				$where .= " AND o.`consignee` = '".I('get.keyword')."'";
				$params["receiver"] = I('get.keyword');
			}
		}
		if(intval(I("order_status"))){
			$where .= "	 AND o.`status`=".(intval(I("order_status"))-1);
			$params['order_status'] = intval(I("order_status"));
		}
		if(I("begindate")){
			$where .= " AND o.`posttime`>=".strtotime(I("begindate"));
		}
		if(I("enddate")){
			$where .= " AND o.`posttime`>=".strtotime(I("enddate"));
		}
        $total = $this->_Model->GetJoinTotal("o",array('LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where);
        $rs = $this->_Model->GetJoinList("o",array('LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where,$page,$this->_rows,array("o.posttime"=>"DESC"),"o.*,u.username AS buyer");
        foreach ($rs as $key => $val){
			if($val['paytime'] && $val['pay_status']){
				$rs[$key]['pay_status_label'] = "已付款";
				if($val['status']==3){
					$rs[$key]['status_label'] = "待发货";
				}
				if($val['status']==4){
					$rs[$key]['status_label'] = "待收货";
				}
				if($val['status']==6){
					$rs[$key]['status_label'] = "已收货";
				}
				if($val['status']==1){
					$rs[$key]['status_label'] = "完成订单";
				}
				if($val['status']==2){
					$rs[$key]['status_label'] = "订单已取消";
				}
				if($val['status']==5){
					$rs[$key]['status_label'] = "已退货";
				}
			}else{
				if($val['status']==2){
					$rs[$key]['pay_status_label'] = "订单已取消";
					$rs[$key]['status_label'] = "订单已取消";
				}else{
					$rs[$key]['pay_status_label'] = "待付款";
					$rs[$key]['status_label'] = "待付款";
				}
			}
			if($val['paytype']==1){
				$rs[$key]['paytype_label'] = $this->_config['account_name']."支付";
			}elseif($val['paytype']==2){
				$rs[$key]['paytype_label'] = "微信支付";
			}else{
				$rs[$key]['paytype_label'] = "待支付";
			}
			if($val['ordertype']==1){
				$rs[$key]['ordertype_label'] = "商家订单";
			}else{
				$rs[$key]['ordertype_label'] = "平台订单";
			}
			$rs[$key]['total_charge'] = sprintf("%01.2f",($val['total_amount']-$val['coupon_charge']-$val['packet_charge']-$val['member_discount']-$val['discount']));
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("infolist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     * 取消订单
     */
    public function cancel(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要取消的记录");
        }
        $rs = $this->_Model->GetInfo("`order_id`=".$id);
        if (empty($rs)){
            $this->error("提示：要取消的记录不存在");
        }
        if($rs['pay_status']==1 AND $rs['status']!=0){
            $this->error("提示：该订单暂已付款，无法取消该订单");
        }
        $rs = $this->_Model->Edit("`order_id`=".$id,array("status"=>2));
        if ($rs){
            $this->success("提示：操作成功，订单已取消");
        }else {
            $this->error("提示：操作失败");
        }
    }

    /**
     * 删除订单
     */
    public function delete(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录");
        }
        $info = $this->_Model->GetInfo("`order_id`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在");
        }
        if(($info['pay_status']==1 && ($info['status'] != 1 || $info['status'] != 2 || $info['status'] != 5)) || ($info['pay_status']==0 && $info['status'] != 0)){
            $this->error("提示：无法删除该订单");
        }
        $rs = $this->_Model->Edit("`order_id`=".$id,array("is_delete"=>1));
        if ($rs){
            $this->success("提示：操作成功，订单已删除");
        }else {
            $this->error("提示：操作失败");
        }
    }

	/**
	* 发货
	*/
	public function delivery(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("请选择要发货的记录");
        }
        $rs = $this->_Model->GetInfo("`order_id`=".$id);
        if (empty($rs)){
            $this->error("要发货的记录不存在");
        }
        if($rs['pay_status']==0){
            $this->error("该订单暂未付款，无法发货");
        }
        if($rs['pay_status']!=1 && $rs['paytime']==0 && ($rs['status'] != 3 || $rs['status'] != 5)){
            $this->error("该订单暂未进入发货状态，该订单无法发货");
        }
        $rs = $this->_Model->Edit("`order_id`=".$id,array("status"=>4));
        if ($rs){
            $this->success("发货成功",U("/Admin/Order/"));
        }else {
            $this->error("发货失败",U("/Admin/Order/"));
        }
	}

    /**
     * 完成订单
     */
    public function finish(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要完成的记录");
        }
        $rs = $this->_Model->GetInfo("`order_id`=".$id);
        if (empty($rs)){
            $this->error("提示：要完成的记录不存在");
        }
        if($rs['pay_status']==1 AND $rs['status']!=6){
            $this->error("提示：该订单暂未收货，无法完成该订单");
        }
        $rs = $this->_Model->Edit("`order_id`=".$id,array("status"=>1));
        if ($rs){
            $this->success("提示：操作成功，订单已完成");
        }else {
            $this->error("提示：操作失败");
        }
    }

	/**
	* 订单详情
	*/
	public function detail(){
		$id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要查看的记录");
        }
        $rs = $this->_Model->GetInfo("`order_id`=".$id);
        if (empty($rs)){
            $this->error("提示：要查看的记录不存在");
        }
		//获得商品信息
		$goodslist = D("OrderGoods")->GetJoinAll("og",array("LEFT JOIN __GOODS__ g ON og.goods_id=g.goodsid"),"og.order_id=".$id,"g.*,og.price,og.goods_num");
		$rs['goodslist'] = $goodslist;

		
		//获取购买者信息
		$buyer = D("User")->GetInfo("`userid`=".$rs['buyer_id']);
		$rs['buyer'] = $buyer;
		if($rs['paytime'] && $rs['pay_status']){
			$rs['pay_status_label'] = "已付款";
			$rs['pay_time'] = date("Y-m-d H:i:s",$rs['paytime']);
			if($rs['status']==3){
				$rs['status_label'] = "待发货";
			}
			if($rs['status']==4){
				$rs['status_label'] = "待收货";
			}
			if($rs['status']==6){
				$rs['status_label'] = "已收货";
			}
			if($rs['status']==1){
				$rs['status_label'] = "完成订单";
			}
			if($rs['status']==2){
				$rs['status_label'] = "订单已取消";
			}
			if($rs['status']==5){
				$rs['status_label'] = "已退货";
			}
		}else{
			$rs['pay_time'] = "未付款";
			if($rs['status']==2){
				$rs['pay_status_label'] = "订单已取消";
				$rs['status_label'] = "订单已取消";
			}else{
				$rs['pay_status_label'] = "待付款";
				$rs['status_label'] = "待付款";
			}
		}
		if($rs['paytype']==1){
			$rs['paytype'] = $this->_config['account_name']."支付";
		}elseif($rs['paytype']==2){
			$rs['paytype'] = "微信支付";
		}else{
			$rs['paytype'] = "待支付";
		}
		if($rs['ordertype']==1){
			$rs['ordertype'] = "商家订单";
		}else{
			$rs['ordertype'] = "平台订单";
		}
		$this->assign("info",$rs);
		$this->display();
	}

    /**
     * 退款单列表
     */
    public function returns(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "o.`exchange_type`=0 AND o.`is_delete`=0 AND o.`pay_status`=1";
        if(trim(I('get.keyword'))){
            if(I("search_type")=="buyer"){
                $where .= " AND u.`username` LIKE '".I('get.keyword')."%'";
                $params["buyer"] = I('get.keyword');
            }else if(I("search_type")=="ordersn"){
                $where .= " AND o.`order_sn`='".I('get.keyword')."%'";
                $params["ordersn"] = I('get.keyword');
            }else{
                $where .= " AND o.`consignee` = '".I('get.keyword')."'";
                $params["receiver"] = I('get.keyword');
            }
        }
        if(I("begindate")){
            $where .= " AND ors.`posttime`>=".strtotime(I("begindate"));
        }
        if(I("enddate")){
            $where .= " AND ors.`posttime`>=".strtotime(I("enddate"));
        }
        $total = D("OrderReturnCharge")->GetJoinTotal("ors",array('LEFT JOIN __ORDER__ o ON o.order_id=ors.order_id','LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where);
        $rs = D("OrderReturnCharge")->GetJoinList("ors",array('LEFT JOIN __ORDER__ o ON o.order_id=ors.order_id','LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where,$page,$this->_rows,array("ors.posttime"=>"DESC"),"ors.*,o.*,u.username AS buyer");
        foreach ($rs as $key => $val) {
            if ($val['return_status'] == 2) {
                $rs[$key]['return_status_label'] = "拒绝";
            } elseif ($val['return_status'] == 1) {
                $rs[$key]['return_status_label'] = "通过";
            } else {
                $rs[$key]['return_status_label'] = "待审核";
            }
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("infolist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     * 通过审核
     */
    public function pass(){
        if(IS_POST){
            $data['return_status'] = 1;
            $data['check_remark'] = trim($_POST['remark']);
            $data['check_userid'] = ACPopedom::getID();
            $data['check_posttime'] = time();
            if(empty($data['check_remark'])){
                $this->error("请输入备注信息");
            }
            $id = intval($_POST['id']);
            $where = "o.`exchange_type`=0 AND o.`is_delete`=0 AND o.`pay_status`=1 AND ors.`order_id`=".$id;
            $info = D("OrderReturnCharge")->GetJoinInfo("ors",array('LEFT JOIN __ORDER__ o ON o.order_id=ors.order_id','LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where);
            if(empty($info)){
                $this->error("记录不存在");
            }
            $rs = D("OrderReturnCharge")->Edit("`order_id`=".$id,$data);
            if(!$rs){
                $this->error("操作失败");
            }
            $this->success("操作成功");
        }else{
            $id = intval($_GET['id']);
            $where = "o.`exchange_type`=0 AND o.`is_delete`=0 AND o.`pay_status`=1 AND ors.`order_id`=".$id;
            $info = D("OrderReturnCharge")->GetJoinInfo("ors",array('LEFT JOIN __ORDER__ o ON o.order_id=ors.order_id','LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where);
            if(empty($info)){
                $this->error("记录不存在");
            }
            $this->assign("info",$info);
            $this->display();
        }
    }

    /**
     * 拒绝审核
     */
    public function refuse(){
        if(IS_POST){
            $data['return_status'] = 2;
            $data['check_remark'] = trim($_POST['remark']);
            $data['check_userid'] = ACPopedom::getID();
            $data['check_posttime'] = time();
            if(empty($data['check_remark'])){
                $this->error("请输入备注信息");
            }
            $id = intval($_POST['id']);
            $where = "o.`exchange_type`=0 AND o.`is_delete`=0 AND o.`pay_status`=1 AND ors.`order_id`=".$id;
            $info = D("OrderReturnCharge")->GetJoinInfo("ors",array('LEFT JOIN __ORDER__ o ON o.order_id=ors.order_id','LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where);
            if(empty($info)){
                $this->error("记录不存在");
            }
            $rs = D("OrderReturnCharge")->Edit("`order_id`=".$id,$data);
            if(!$rs){
                $this->error("操作失败");
            }
            $this->success("操作成功");
        }else{
            $id = intval($_GET['id']);
            $where = "o.`exchange_type`=0 AND o.`is_delete`=0 AND o.`pay_status`=1 AND ors.`order_id`=".$id;
            $info = D("OrderReturnCharge")->GetJoinInfo("ors",array('LEFT JOIN __ORDER__ o ON o.order_id=ors.order_id','LEFT JOIN __USER__ AS u on u.userid =o.buyer_id'),$where);
            if(empty($info)){
                $this->error("记录不存在");
            }
            $this->assign("info",$info);
            $this->display();
        }
    }

}