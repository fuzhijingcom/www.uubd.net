<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class PackageController extends HomebaseController {
	//组件页
	public function package() {
		$this->display();
	}

	//获取所有组件
	public function getPackages() {
		if (IS_GET) {
			$Package = D('Package');
			$packages = $Package->getPackages();
			$this->ajaxReturn($packages);
		}
	}

	//添加组件
	public function addPackage() {
		if (IS_POST) {
			$data['p_option'] = $_POST['option'];
			$data['p_name'] = $_POST['name'];
			$data['p_type'] = $_POST['type'];
			$data['p_status'] = $_POST['status'];
			$data['p_ctime'] = date('Y-m-d H:i:s'); //添加时间
			$data['p_utime'] = $data['p_ctime']; //添加时间

			$Package = D('Package');
			$result = $Package->addPackage($data);
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//更新组件数据
	public function updatePackage() {
		if (IS_POST) {
			$map['p_id'] = $_POST['id'];
			$data['p_option'] = $_POST['option'];
			$data['p_name'] = $_POST['name'];
			$data['p_type'] = $_POST['type'];
			$data['p_status'] = $_POST['status'];
			$data['p_utime'] = date('Y-m-d H:i:s'); //修改时间

			$Package = D('Package');
			$result = $Package->updatePackage($map, $data);
			$result = ($result >= 0) ? 1 : 0;
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//删除组件
	public function deletePackage() {
		if (IS_POST) {
			$map['p_id'] = $_POST['id'];
			$Package = D('Package');
			$result = $Package->deletePackage($map);
			$this->ajaxReturn(array('result' => $result));
		}
	}
}