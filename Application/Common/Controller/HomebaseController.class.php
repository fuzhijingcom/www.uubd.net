<?php
namespace Common\Controller;
use Common\Controller\AppframeController;

class HomebaseController extends AppframeController {
	public function __construct() {
		parent::__construct();
	}

	//控制器初始化
	public function _initialize() {
		parent::_initialize();
		$forbid_arr = array('getorder', 'getcustomer', 'login');
		if (!in_array(ACTION_NAME, $forbid_arr)) {
			$this->validateLogin();
		}
	}

	public function validateLogin() {
		$userinfo = session('userinfo');
		if ($userinfo) {
			// 检查后台人员菜单操作权限
			$this->checkAuthorization();
		} else {
			redirect(C('NGINX_ROOT') . U('Home/Index/login'), 0, '正在跳转登录中...');
		}
	}

	public function checkAuthorization() {
		$controller_name = CONTROLLER_NAME;
		if ($controller_name === 'Index') {
			// 后台首页控制器不需要验证权限
			return;
		} else {
/*
			$role_id = session('userinfo.userr_id');
			$ctl_list = \Home\Logic\AuthLogic::getControllerList($role_id);
			if ($role_id == null) {
				//$this->error('无此操作权限', C('NGINX_ROOT') . U('Home/Index/login'));
				$this->error('无此操作权限');
			}
			// 检查是否拥有此操作权限
			if (in_array($controller_name, $ctl_list)) {
				return;
			} else {
				$this->error('无此操作权限');
				//$this->error('无此操作权限', C('NGINX_ROOT') . U('Home/Index/login'));
			}
*/
		}
	}
}
