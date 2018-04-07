<?php
namespace Home\Model;
use Think\Model;

class StoreModel extends Model {
	protected $tableName = 'store'; //设置tableName属性来改变默认的规则
	protected $pk = 's_id'; //设置pk属性改变主键名称
	

	//获取学校表数据
	public function getStores($map = '', $sort = '', $limit = '', $field = '') {
		$storeList = $this->where($map)->limit($limit)->order($sort)->field($field)->select();
		//$sql = $this->getLastSql();
		return $storeList;
	}

	//插入学校表
	public function addStore($data) {
		$result = $this->add($data);
		return $result;
	}

	//更新学校表
	public function updateStore($map = '', $data) {
		$result = $this->where($map)->save($data);
		return $result;
	}

	//删除学校
	public function deleteStore($map) {
		$result = $this->where($map)->delete();
		return $result;
	}

	//查找学校
	public function findStore($map) {
		$store = $this->where($map)->find();
		return $store;
	}


	/**
	 * 【获取门店详细信息】
	 * @param $userid
	 * @param $users_id
	 * @return mixed
	 */
	public function getStoreDataDetail($userid,$users_id,$time)
	{
		$Store = M('Store');
		$Customer = D('Customer');
		$Tradedetail = D('Tradedetail');
		$Partner = D('Partner');
		$data = array(); //保存数据
		//获取所有门店的数据
		if ($users_id == '-1') { //管理员看的
			//获取所有门店
			$storeData = $Store->where(array('s_type' => 0))->select();
		}else{
			$storeData = $Store->where(array('s_id'=>$users_id))->select();
		}
			$store_name_str = '';
			//拼接门店名称
			foreach ($storeData as $k => $v) {
				$store_name_str .= "'{$v['s_name']}',";
			}
			$store_name_str = rtrim($store_name_str, ',');
			//获取所有门店的订单
			$tradedetailData = $Tradedetail->getStoreTradedetail($store_name_str, $time);
			//获取所有被推广的用户
			$userData = $Customer->getUserData($time);
			//根据门店获取该门店下的合伙人姓名
			$partnerData = $Partner->getStorePartner($time);
			//按门店分好合伙人所属门店
			$storePartnerData = array();

			/*$data[0]下标为零的是汇总的*/
			foreach ($storeData as $k => $v) {
				$data[0]['s_name'] = '（汇总）';
				$data[$k+1]['s_name'] = $v['s_name'];
				//将门店名放入数组（方便下面统计）
				$storePartnerData[$k][] = $v['s_name'];
				foreach ($partnerData as $k1 => $v1) {
					if ($v['s_id'] == $v1['promotion_store']) {
						$storePartnerData[$k][] = $v1['p_name'];
						if ($v1['promotion_type'] == '3') {
							//校园合伙人新增数
							$data[0]['school_partner_count'] += 1;
							$data[$k+1]['school_partner_count'] += 1;
						} else if ($v1['promotion_type'] == '4') {
							//初级合伙人新增数
							$data[0]['primary_partner_count'] += 1;
							$data[$k+1]['primary_partner_count'] += 1;
						}
					}
				}
				//计算订单
				foreach ($tradedetailData as $k1 => $v1) {

					//如果门店名和订单中shop字段的门店名一致
					if ($v['s_name'] == $v1['shop']) {
						if ($v1['type'] != 'CONTACT_BUY') {
							//配镜业绩
							$data[0]['not_contact_payment'] += (float)$v1['payment'];
							$data[0]['not_contact_count'] += 1;
							$data[$k+1]['not_contact_payment'] += (float)$v1['payment'];
							//配镜单数
							$data[0]['contact_payment'] += (float)$v['payment'];
							$data[$k+1]['not_contact_count'] += 1;
						} else if($v1['type'] == 'CONTACT_BUY'){
							//隐形业绩
							$data[0]['contact_payment'] += (float)$v1['payment'];
							$data[$k+1]['contact_payment'] += (float)$v1['payment'];
							//隐形单数
							$data[0]['contact_count'] += 1;
							$data[$k+1]['contact_count'] += 1;
						}
						if ($v1['feedback'] == '600' || $v1['feedback'] == '601') {
							//售后服务金额
							$data[0]['feedback_payment'] += (float)$v1['payment'];
							$data[$k+1]['feedback_payment'] += (float)$v1['payment'];
							//售后服务单数
							$data[0]['feedback_count'] += 1;
							$data[$k+1]['feedback_count'] += 1;
						}
                        //其他支付方式
						if($v1['pay_type'] == 'others'){
                            outputDebugLog($v1,8,'others_count');
                            if((int)$v1['is_redpack'] === 0 && $v1['type'] != 'CONTACT_BUY'){
                                $redpack_price = $v1['payment'] + $v1['redpack_price'];
                                $data[0]['others_count'] += $redpack_price;
                                $data[$k+1]['others_count'] += $redpack_price;
                            }else if( ((int)$v1['is_redpack'] === 1 || (int)$v1['is_redpack']===2) && $v1['type'] != 'CONTACT_BUY'){
                                $data[0]['others_count'] += $v1['payment'];
                                $data[$k+1]['others_count'] += $v1['payment'];
                            }
                            if($v1['type'] == 'CONTACT_BUY'){
                                $data[0]['others_count'] += $v1['payment'];
                                $data[$k+1]['others_count'] +=  $v1['payment'];
                            }
                        }
					}
				}
			}
			//计算推广
			foreach ($storePartnerData as $k => $v) {
				foreach ($v as $k1 => $v1) {
					foreach ($userData as $k2 => $v2) {
						if ($v1 == $v2['tags']) {
							//关注数
							$data[0]['follow_count'] += 1;
							$data[$k+1]['follow_count'] += 1;
							if ($v2['is_follow'] == '0') {
								//取消关注数
								$data[0]['cannel_follow_count'] += 1;
								$data[$k+1]['cannel_follow_count'] += 1;
							}
						}
					};
				}
			}

		$returnData = array();
		foreach($data as $k => $v){
			$returnData[$k] = array(
				's_name'				=>  $v['s_name']				?	$v['s_name']				:  '（门店名）',
				'not_contact_payment' 	=> 	$v['not_contact_payment']	?	$v['not_contact_payment']	:	0,
				'contact_payment'		=>	$v['contact_payment']		?	$v['contact_payment']		:	0,
				'not_contact_count' 	=> 	$v['not_contact_count']		?	$v['not_contact_count']		:	0,
				'contact_count'			=> 	$v['contact_count']			?	$v['contact_count']			:	0,
				'follow_count'			=>	$v['follow_count']			?	$v['follow_count']			:	0,
				'cannel_follow_count'	=>	$v['cannel_follow_count']	?	$v['cannel_follow_count']	:	0,
				'primary_partner_count'	=>	$v['primary_partner_count']	?	$v['primary_partner_count']	:	0,
				'school_partner_count'	=>	$v['school_partner_count']	?	$v['school_partner_count']	:	0,
				'feedback_count'		=>  $v['feedback_count']		? 	$v['feedback_count']		: 	0,
				'feedback_payment'		=> 	$v['feedback_payment']		?	$v['feedback_payment']		:	0,
                'others_count'          =>  $v['others_count']          ?   $v['others_count']          :   0,
			);
		}

		return $returnData;
	}
	
	public function getStoreList($sid){
		$s_model = M('store');
//		$s_map['s_name'] = array('neq','总店');
		if($sid!=0){
			$s_map['s_id'] = $sid;
		}else{
			$s_map['s_type'] = 0;
		}
		$field = array(
			's_id' => 's_id',
			's_name' => 's_name',
		);
		$res = $s_model->where($s_map)->field($field)->select();
		return $res;
	}


	/**
	 * 获取门店旗下合伙人推广的订单总额
	 */
	public function getStorePartnerOrderPrice($store_id,$time){
		$Store = M('Store');
		$promotionData = $Store->field('s_name')->where(array('promotion_store'=>$store_id))->select();

		$Tradedetail = M('Tradedetail');
		$promotion_price = 0;
		foreach($promotionData as $k => $v){
			$where = "promotion = '{$v['s_name']}' and created >= '{$v['partner_time']}' and created >= '{$time['startT']}' and created <= '{$time['endT']}'";
			$where .= " and status IN ('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED') ";
			$price = $Tradedetail->field(array('sum(payment)'=>'price'))->where($where)->find();
			$promotion_price += $price['price'];
		}
		return $promotion_price;
	}

	/**
	 * 【获取合伙人推广的用户总数】
	 * @param $promotion_name
	 * @param $time
	 * @param int $status 1已关注 2取消关注
	 */
	public function getPromotionExtendUserCount($store_id,$time,$status = 1){
		$Store = M('Store');
		$promotionData = $Store->field('s_name,partner_time')->where(array('promotion_store'=>$store_id))->select();
		$Customer = M('Customer');

		$count = 0;
		foreach($promotionData as $k => $v){
			$where = " tags = '{$v['s_name']}' and follow_time >='{$v['partner_time']}' and follow_time >= '{$time['startT']}' and follow_time <= '{$time['endT']}'";
			if($status === 1){
				$where .= " and is_follow = 1";
			}else if($status === 2){
				$where .= " and is_follow = 2";
			}
			$userCount = $Customer->where($where)->count();
			$count += $userCount;
		}
		return $count;
	}


	/**
	 * 【获取合伙人的订单数】
	 * @param $store_id
	 * @param $time
	 * @return int
	 */
	public function getPromotionExtendOrderCount($store_id,$time){
		$Store = M('Store');
		$promotionData = $Store->where(array('promotion_store'=>$store_id))->select();
		$Tradedetail = M('Tradedetail');

		$count = 0;
		foreach($promotionData as $k => $v){
			$where = " promotion = '{$v['s_name']}' and created >='{$v['partner_time']}'  and  created >= '{$time['startT']}' and created <= '{$time['endT']}'";
			$where .= " and status IN ('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED') ";
			$trade_count = $Tradedetail->where($where)->count();
			$count += $trade_count;
		}
		return $count;

	}


}
