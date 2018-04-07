<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class RoleController extends HomebaseController {
	//角色页
	public function role() {
		$Menu = D('Menu');
		$menus = $Menu->getMenus($map);
		$this->assign('menus', $menus);
		$this->display();
	}

	//获取所有角色
	public function getRoles() {
		if (IS_GET) {
			$Role = D('Role');
			$Roles = $Role->getRoles();
			$this->ajaxReturn($Roles);
		}
	}

	//添加角色
	public function addRole() {
		if (IS_POST) {
			$data['r_name'] = $_POST['name'];
			$data['r_note'] = $_POST['note'];
			$data['r_ctime'] = date('Y-m-d H:i:s'); //添加时间
			$data['r_utime'] = $data['r_ctime']; //添加时间

			$Role = D('Role');
			$role_id = $Role->addRole($data); //添加成功后返回的角色id
			$result = $role_id;
			if ($result > 0) {
				$menuPackages = json_decode($_POST['menuPackages']);
				if ($menuPackages && is_array($menuPackages)) {
					foreach ($menuPackages as $mp => $menuPackage) {
						$role_menu_package[] = array('r_id' => $role_id, 'm_id' => $menuPackage[0], 'p_id' => isset($menuPackage[1]) ? $menuPackage[1] : NULL);
					}
					$RoleMenuPackage = D('RoleMenuPackage');
					$result = $RoleMenuPackage->addAllRoleMenuPackage($role_menu_package);
				}
			}

			$this->ajaxReturn(array('result' => $result));
		}
	}

	//更新角色数据
	public function updateRole() {
		if (IS_POST) {
			$map['r_id'] = $_POST['id'];
			$data['r_name'] = $_POST['name'];
			$data['r_note'] = $_POST['note'];
			$data['r_utime'] = date('Y-m-d H:i:s'); //修改时间

			$Role = D('Role');
			$result = $Role->updateRole($map, $data); //先更新角色表数据
			$result = ($result >= 0) ? 1 : 0;
			if ($result > 0) {
				$RoleMenuPackage = D('RoleMenuPackage');
				$rmpmap['r_id'] = $map['r_id'];
				$RoleMenuPackage->deleteRoleMenuPackage($rmpmap); //删除该角色用的角色_菜单_组件表的数据
				$menuPackages = json_decode($_POST['menuPackages']);
				if ($menuPackages && is_array($menuPackages)) {
					foreach ($menuPackages as $mp => $menuPackage) {
						$role_menu_package[] = array('r_id' => $map['r_id'], 'm_id' => $menuPackage[0], 'p_id' => $menuPackage[1]);
					}
					$result = $RoleMenuPackage->addAllRoleMenuPackage($role_menu_package); //批量添加角色_菜单_组件表的数据
				}
			}
			S('controller_auth_cache', null); // 删除缓存中的权限信息
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//删除角色
	public function deleteRole() {
		if (IS_POST) {
			$map['r_id'] = $_POST['id'];
			$Role = D('Role');
			$result = $Role->deleteRole($map);
			if ($result > 0) {
				$RoleMenuPackage = D('RoleMenuPackage');
				$rmpmap['r_id'] = $map['r_id'];
				$RoleMenuPackage->deleteRoleMenuPackage($rmpmap); //删除该角色用的角色_菜单_组件表的数据
			}
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//获取角色的菜单，组件
	public function getRoleMenuPackages() {
		if (IS_POST) {
			$map['r_id'] = $_POST['id'];
			$field = 'm_id,p_id';
			$RoleMenuPackage = D('RoleMenuPackage');
			$roleMenuPackages = $RoleMenuPackage->getRoleMenuPackages($map, '', '', $field);
			$menus = array();
			foreach ($roleMenuPackages as $key => $roleMenuPackage) {
				if (!in_array($roleMenuPackage['m_id'], $menus)) {
					$menus[] = $roleMenuPackage['m_id'];
				}
			}
			$this->ajaxReturn(array('menus' => $menus, 'menuPackages' => $roleMenuPackages));
		}
	}
}