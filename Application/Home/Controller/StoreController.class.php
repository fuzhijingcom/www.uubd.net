<?php
namespace Home\Controller;
use Common\Wx;
use Common\Controller\HomebaseController;

class StoreController extends HomebaseController {
	//门店页
	public function Store() {
		$this->display();
	}

	//获取所有门店
	public function getStores() {
		if (IS_GET) {
			$Store = D('Store');
			$where['s_type'] = 0;
			$stores = $Store->getStores($where);
			$this->ajaxReturn($stores);
		}
	}

	//添加门店
	public function addStore() {
		if (IS_POST) {
			if (isset($_POST['s_type'])) {
				$data['s_type'] = $_POST['s_type'];
			}
			if (isset($_POST['s_name'])) {
				$data['s_name'] = $_POST['s_name'];
			}
			if (isset($_POST['s_addr'])) {
				$data['s_addr'] = $_POST['s_addr'];
			}
			if (isset($_POST['s_coordinate'])) {
				$data['s_coordinate'] = $_POST['s_coordinate'];
			}
			if (isset($_POST['s_phone'])) {
				$data['s_phone'] = $_POST['s_phone'];
			}
			if(isset($_POST['is_withdraw'])){
				$data['is_withdraw'] = $_POST['is_withdraw'];
			}
			if (isset($_POST['effectiveyear'])){
				$data['effectiveyear'] = $_POST['effectiveyear'];
			}else{
				if(isset($_POST['is_withdraw'])){
					exit('<script>请选择分成提现的有效期！;</script>');
				}
			}
			$time = date('Y-m-d H:i:s');
			if(isset($_POST['is_withdraw'])){
				$data['partner_time'] = $time;
			}

			$time = date('Y-m-d H:i:s');
			//如果是参与分成，则添加合伙人的时间
			if(isset($_POST['is_withdraw'])){
				$data['partner_time'] = $time;
			}

			$data['s_ctime'] = $time; //添加时间
			$data['s_utime'] = $data['s_ctime']; //添加时间
			
			$Store = D('Store');
			$lastId = $Store->addStore($data);
			if ($lastId) {
				$s_code = Wx\Weixin::apply_qrcode($lastId); // 根据 token ，获取用户信息
				if ($s_code) {
					$map['s_id'] = $lastId;
					$updata['s_code'] = $s_code;
					$result = $Store->updateStore($map, $updata); //更新门店表数据
				}
			}

			$this->ajaxReturn(array('result' => $result, 'mess' => $mess, 'ticket' => $ticket));
		}
	}

	//更新门店数据
	public function updateStore() {
		if (IS_POST) {
			$map['s_id'] = $_POST['id'];
			if (isset($_POST['s_type'])) {
				$data['s_type'] = $_POST['s_type'];
			}
			if (isset($_POST['s_name'])) {
				$data['s_name'] = $_POST['s_name'];
			}
			if (isset($_POST['s_addr'])) {
				$data['s_addr'] = $_POST['s_addr'];
			}
			if (isset($_POST['s_coordinate'])) {
				$data['s_coordinate'] = $_POST['s_coordinate'];
			}
			if (isset($_POST['s_phone'])) {
				$data['s_phone'] = $_POST['s_phone'];
			}
			
			$data['is_withdraw'] = $_POST['is_withdraw'];

			if (isset($_POST['effectiveyear'])){
				$data['effectiveyear'] = $_POST['effectiveyear'];
			}else{
				if(isset($_POST['is_withdraw'])){
					exit('<script>请选择分成提现的有效期！;</script>');
				}
			}

			$time = date('Y-m-d H:i:s');

			$Store = D('Store');
			$storeInfo = $Store->where($map)->find();

			$time = date('Y-m-d H:i:s');
			if(isset($_POST['is_withdraw'])){
				if($storeInfo['partner_time'] == '0000-00-00 00:00:00'){
					$data['partner_time'] = $time;
				}
			}else{
				$data['partner_time'] = '0000-00-00 00:00:00';
			}

			$data['s_utime'] = $time; //修改时间

			$result = $Store->updateStore($map, $data); //更新门店表数据
			$result = ($result >= 0) ? 1 : 0;

			$this->ajaxReturn(array('result' => $result, 'mess' => $mess));
		}
	}

	//删除门店
	public function deleteStore() {
		if (IS_POST) {
			$map['s_id'] = $_POST['id'];
			$Store = D('Store');
			$result = $Store->deleteStore($map);
			$this->ajaxReturn(array('result' => $result));
		}
	}
}