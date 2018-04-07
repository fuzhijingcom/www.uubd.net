<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class TorController extends HomebaseController {
	public function tt(){
		Vendor('Youzan.Youzan');
		$youzan = \Youzan::getInstance();
		$params = array('user_id' => '1231741910');
		$orders = $youzan->getOneWeixinFollower($params);
		dump($orders);
	}

	public function getcustomer(){
		$refresh = I('get.refresh', null, 'htmlspecialchars');
		
		if ($refresh === 'refresh') {
			// 被强制刷新
			S('after_fans_id', null);
		}
		$after_fans_id = S('after_fans_id');
		
		Vendor('Youzan.Youzan');
		$youzan = \Youzan::getInstance();

		while (1) {
			if ($after_fans_id) {
				// 说明已经有拉取过的数据，就按最后拉取的更新时间来获取订单
				$params = array('page_size' => 50, 'after_fans_id' => $after_fans_id);
			} else {
				$params = array('page_size' => 50, 'after_fans_id' => 0);
			}
			$users = $youzan->pullWeixinFollowers($params);
			
			if (is_array($users['response']['users'])) {
				$this->handle_customer_data($users['response']['users']);
			}

			if ($users['response']['has_next']) {
				$after_fans_id = $users['response']['last_fans_id'];
			}else{
				break;
			}
		}

		if ($users['response']['last_fans_id']) {
			$after_fans_id = $users['response']['last_fans_id'];

			if ($after_fans_id > 0) {
				S('after_fans_id', $after_fans_id);
			}
		}
	}

	public function getOrder() {
		Vendor('Youzan.Youzan');
		$youzan = \Youzan::getInstance();
		$Tradetail = D('Tradedetail');
		
		$start_update = S('getOrderTime', null);
		$start_update = S('getOrderTime');
		$curTime = date("Y-m-d H:i:s", time() - 8);
		
		dump($start_update);
		S('getOrderTime', $curTime);

		if ($start_update) {
			// 说明已经有拉取过的数据，就按最后拉取的更新时间来获取订单
			$params = array('start_update' => $start_update, 'page_size' => 100);
		}else{
			$params = array('page_size' => 100);
		}
		
		// $status_arr = array('WAIT_SELLER_SEND_GOODS', 'TRADE_CLOSED', 'TRADE_BUYER_SIGNED');
		$orders = $youzan->getTradesSold($params);
		// dump($orders);
		if (is_array($orders['response']['trades'])) {

			foreach ($orders['response']['trades'] as $vo) {
				$arr['tid'] = $vo['tid'];
				$exi = $Tradetail->findTradedetail($arr);

				if ($exi) {
					dump('has data');
					if ($exi['status'] != $vo['status']) {
						dump('need update');
						$data = $this->handle_data($vo);

						$Tradetail->updateTradedetail($arr, $data);
					}
				}else{
					dump($arr);
					dump('new trades');
					$data = $this->handle_data($vo);

					$Tradetail->addTradedetail($data);
				}
			}
			
		}
	}

	private function handle_customer_data($handle_data){
		$Customer = D('Customer');

		foreach ($handle_data as $vo) {
			$data['user_id'] = $vo['user_id'];
			$data['weixin_openid'] = $vo['weixin_openid'];
			$data['nick'] = $vo['nick'];
			$data['avatar'] = $vo['avatar'];
			$data['follow_time'] = $vo['follow_time'];
			$data['sex'] = $vo['sex'];
			$data['province'] = $vo['province'];
			$data['city'] = $vo['city'];
			$data['points'] = $vo['points'];
			$data['traded_num'] = $vo['traded_num'];
			$data['traded_money'] = $vo['traded_money'];
			if (count($vo['tags']) > 0) {
				if (is_array($vo['tags'])) {
					$data['tags'] = $vo['tags'][0]['name'];
				}
			} else {
				$data['tags'] = '';
			}
			$data['level_info'] = "";
			$data['union_id'] = $vo['union_id'];
			if ($vo['is_follow']) {
				$data['is_follow'] = 1;
			} else {
				$data['is_follow'] = 0;
			}

			$map['user_id'] = $vo['user_id'];
			if ($Customer->findCustomer($map)) {
				// dump('old user');
				// $Customer->updateCustomer($map, $data);  // 已经有用户不需要再添加数据
			}else{
				$Customer->addCustomer($data);
			}
			// dump($data);
		}
	}

	private function handle_data($vo){
		$Customer = D('Customer');

		$data['tid'] = $vo['tid'];
		$data['num'] = $vo['num'];
		$data['num_iid'] = $vo['num_iid'];
		$data['price'] = $vo['price'];
		$data['pic_path'] = $vo['pic_path'];
		$data['pic_thumb_path'] = $vo['pic_thumb_path'];
		$data['title'] = $vo['title'];
		$data['type'] = $vo['type'];
		$data['weixin_user_id'] = $vo['weixin_user_id'];
		$data['buyer_type'] = $vo['buyer_type'];
		$data['buyer_id'] = $vo['buyer_id'];
		$data['buyer_nick'] = $vo['buyer_nick'];
		$data['buyer_message'] = $vo['buyer_message'];
		$data['seller_flag'] = $vo['seller_flag'];
		$data['trade_memo'] = $vo['trade_memo'];
		$data['receiver_city'] = $vo['receiver_city'];
		$data['receiver_district'] = $vo['receiver_district'];
		$data['receiver_state'] = $vo['receiver_state'];
		$data['receiver_address'] = $vo['receiver_address'];
		$data['receiver_zip'] = $vo['receiver_zip'];
		$data['receiver_mobile'] = $vo['receiver_mobile'];
		$data['receiver_name'] = $vo['receiver_name'];
		$data['feedback'] = $vo['feedback'];
		$data['refund_state'] = $vo['refund_state'];
		$data['outer_tid'] = $vo['outer_tid'];
		$data['status'] = $vo['status'];
		$data['shipping_type'] = $vo['shipping_type'];
		$data['post_fee'] = $vo['post_fee'];
		$data['total_fee'] = $vo['total_fee'];
		$data['refunded_fee'] = $vo['refunded_fee'];
		$data['discount_fee'] = $vo['discount_fee'];
		$data['payment'] = $vo['payment'];
		$data['created'] = $vo['created'];
		$data['update_time'] = $vo['update_time'];
		$data['pay_time'] = $vo['pay_time'];
		$data['pay_type'] = $vo['pay_type'];
		$data['consign_time'] = $vo['consign_time'];
		$data['sign_time'] = $vo['sign_time'];
		$data['buyer_area'] = $vo['buyer_area'];
		$data['adjust_fee'] = $vo['adjust_fee'];

		$map['user_id'] = $vo['buyer_id'];
		if ($person = $Customer->findCustomer($map)) {
			if ($person['tags'] != '') {
				$data['shop'] = $person['tags'];
			} else {
				$data['shop'] = '总店';
			}
		} else {
			$data['shop'] = '总店';
		}

		return $data;			
			
	}

	
}
