<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class MenuController extends HomebaseController {
	//菜单页
	public function menu() {
		$this->display();
	}

	//获取所有菜单
	public function getMenus() {
		if (IS_GET) {
			$Menu = D('Menu');
			$Menus = $Menu->getMenus();
			$this->ajaxReturn($Menus);
		}
	}

	//添加菜单
	public function addMenu() {
		if (IS_POST) {
			$data['m_name'] = $_POST['name'];
			$data['m_flow'] = $_POST['flow'];
			$data['m_icon'] = $_POST['icon'];
			$data['m_red'] = $_POST['red'];
			$data['m_status'] = $_POST['status'];
			$data['m_show'] = $_POST['show'];
			$data['m_order'] = $_POST['order'];
			$data['m_note'] = $_POST['note'];
			$data['m_ctime'] = date('Y-m-d H:i:s'); //添加时间
			$data['m_utime'] = $data['m_ctime']; //添加时间

			$Menu = D('Menu');
			$result = $Menu->addMenu($data);
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//更新菜单数据
	public function updateMenu() {
		if (IS_POST) {
			$map['m_id'] = $_POST['id'];
			$data['m_name'] = $_POST['name'];
			$data['m_flow'] = $_POST['flow'];
			$data['m_icon'] = $_POST['icon'];
			$data['m_red'] = $_POST['red'];
			$data['m_status'] = $_POST['status'];
			$data['m_show'] = $_POST['show'];
			$data['m_order'] = $_POST['order'];
			$data['m_note'] = $_POST['note'];
			$data['m_utime'] = date('Y-m-d H:i:s'); //修改时间

			$Menu = D('Menu');
			$result = $Menu->updateMenu($map, $data);
			$result = ($result >= 0) ? 1 : 0;
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//删除菜单
	public function deleteMenu() {
		if (IS_POST) {
			$map['m_id'] = $_POST['id'];
			$Menu = D('Menu');
			$result = $Menu->deleteMenu($map);
			$this->ajaxReturn(array('result' => $result));
		}
	}
}