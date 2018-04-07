<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class OptometryController extends HomebaseController {
	//菜单页
	public function optometry() {
		if (isset($_GET['user_id'])) {
			$map['user_id'] = $_GET['user_id'];
		}
		if (isset($_GET['name'])) {
			$name = $_GET['name'];
		}
		$sort = 'is_new desc , time desc';
		$Optometry = D('Optometry');
		$optometrys = $Optometry->getOptometrys($map, $sort);
		$this->assign('optometrys', $optometrys);
		$this->assign('user_id', $map['user_id']);
		$this->assign('name', $name);
		$this->display();
	}

	//添加菜单
	public function addOptometry() {
		if (IS_POST) {
			$user_id = $_POST['user_id'];
			$CustomerModel = M('customer');
			$cus_info = $CustomerModel->where(array('user_id'=>$user_id))->find();
			$data['weixin_openid'] = $cus_info['weixin_openid'];
			$data['user_id'] = $user_id;
			$data['rdegree'] = $_POST['rdegree'];
			$data['ldegree'] = $_POST['ldegree'];
			$data['rastigmatism'] = $_POST['rastigmatism'];
			$data['lastigmatism'] = $_POST['lastigmatism'];
			$data['raxial'] = $_POST['raxial'];
			$data['laxial'] = $_POST['laxial'];
			$data['rpd'] = $_POST['rpd'];
			$data['lpd'] = $_POST['lpd'];
			$data['pd'] = $_POST['lpd']+$_POST['rpd'];
			$data['time'] = date('Y-m-d H:i:s');

			$Optometry = D('Optometry');
			$result = $Optometry->addOptometry($data);
			$data['o_id'] = $result;
			$this->ajaxReturn(array('result' => $result, 'optometry' => $data));
		}
	}

	//更新菜单数据
	public function updateOptometry() {
		if (IS_POST) {
			$map['o_id'] = $_POST['o_id'];
			$data['rdegree'] = $_POST['rdegree'];
			$data['ldegree'] = $_POST['ldegree'];
			$data['rastigmatism'] = $_POST['rastigmatism'];
			$data['lastigmatism'] = $_POST['lastigmatism'];
			$data['raxial'] = $_POST['raxial'];
			$data['laxial'] = $_POST['laxial'];
			$data['rpd'] = $_POST['rpd'];
			$data['lpd'] = $_POST['lpd'];
			$data['is_new'] = 0;
			$data['time'] = date('Y-m-d H:i:s');

			$Optometry = D('Optometry');
			$result = $Optometry->updateOptometry($map, $data);
			$result = ($result >= 0) ? 1 : 0;
			$data['o_id'] = $map['o_id'];
			$this->ajaxReturn(array('result' => $result, 'optometry' => $data));
		}
	}

	//删除菜单
	public function deleteOptometry() {
		if (IS_POST) {
			$map['o_id'] = $_POST['o_id'];
			$Optometry = D('Optometry');
			$result = $Optometry->deleteOptometry($map);
			$this->ajaxReturn(array('result' => $result));
		}
	}

	public function getOptometryCount() {
		if (IS_GET) {
			$map['is_new'] = 1;
			$Optometry = D('Optometry');
			$count = $Optometry->getOptometryCount($map);
			$this->ajaxReturn(array('count' => $count));
		}
	}

	public function getLastOptometry() {
        outputDebugLog('in');
		if (IS_GET) {
			//前台传递的tid存在并且长度小于14则表示是该订单属于我们自有页面扫码下单的
			if($_GET['tid'] && strlen($_GET['tid']) <= 14){
				$Tradedetail = M('Tradedetail');
				$optoInfo = $Tradedetail->where(array('tid'=>$_GET['tid']))->find();
				$map['o_id'] = $optoInfo['o_id'];
			}else{
				$map['user_id'] = $_GET['buyer_id'];
			}
			//当订单的验光数据为0时，自动关联最新数据 2016-11-29
			if((int)$map['o_id']===0){
                unset($map['o_id']);
                $map['user_id'] = $_GET['buyer_id'];
            }
			$sort = 'time desc';
			$limit = '1';
			$Optometry = D('Optometry');
			$optometrys = $Optometry->getOptometrys($map, $sort, $limit);
			$this->ajaxReturn(array('optometrys' => $optometrys));
		}
	}
}