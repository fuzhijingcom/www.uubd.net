<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class CustomerController extends HomebaseController {
	//用户页
	public function customer() {
		$userinfo = session('userinfo');
		$umap['id'] = $userinfo['userid'];
		$filed = 'id,name,nickname,phone,point,user.s_id,s_name';
		$join = 'store ON user.s_id = store.s_id';
		$jointype = 'LEFT';
		$User = D('User');
		$user = $User->findUser($umap, $filed, $join, $jointype);
		if ($user) {
			if ($user['s_id'] != -1) {
				$map['s_id'] = $userinfo['users_id'];
				$cmap["SUBSTRING_INDEX(tags,',',1)"] = $user['s_name'];
			}
			$Store = D('Store');
			$stores = $Store->getStores($map);
			$this->assign('stores', $stores); //门店

			$Customer = D('Customer');
			$cmap['is_follow'] = 1;
			$count = $Customer->getCustomerCount($cmap); // 查询满足要求的总记录数
			$this->assign('count', $count);

			$this->assign('startDate', date('Y-m-d'));
			$this->assign('endDate', date('Y-m-d'));
			$this->display();
		}
	}

	//获取所有用户
	public function customertable() {
		if (IS_GET) {
			$userinfo = session('userinfo');
			$umap['id'] = $userinfo['userid'];
			$filed = 'id,name,nickname,phone,point,user.s_id,s_name';
			$join = 'store ON user.s_id = store.s_id';
			$jointype = 'LEFT';
			$User = D('User');
			$user = $User->findUser($umap, $filed, $join, $jointype);
			if ($user) {
				$Tradedetail = D('Tradedetail');
				$Withdraw = D('Withdraw');
				$leftData = array();
				if ($user['s_id'] != -1) {
					$map["SUBSTRING_INDEX(tags,',',1)"] = $user['s_name'];
				}
				if (isset($_GET['tags'])) {
					$map['tags'] = array('like', '%' . $_GET['tags'] . '%');
				}
				if (isset($_GET['is_follow'])) {
					$map['is_follow'] = $_GET['is_follow'];
				}
				if (isset($_GET['keyword'])) {
					$where['nick'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['name'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['phone'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['tags'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['_logic'] = 'or';
					$map['_complex'] = $where;
				}

				$Customer = D('Customer');
				$count = $Customer->getCustomerCount($map); // 查询满足要求的总记录数
				$Page = new \Think\Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数(25)
				$show = $Page->show(); // 分页显示输出
				// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
				$sort = "is_new desc,follow_time desc";
				$limit = $Page->firstRow . ',' . $Page->listRows;
				$cfiled = 'customer.user_id,nick,name,phone,points,tags,is_follow,follow_time,name,is_new,unfollow_time';
				$cjoin = 'optometry ON customer.user_id = optometry.user_id and optometry.is_new=1';
				$cjointype = 'LEFT';
				$list = $Customer->getCustomers($map, $sort, $limit, $cfiled, $cjoin, $cjointype);
				//print_r($list);
				$this->assign('list', $list); // 赋值数据集
				$this->assign('page', $show); // 赋值分页输出
				$this->display(); // 输出模板
			}
		}
	}

	public function exportCustomer() {
		$Customer = D('Customer');
		$list = $Customer->getCustomers();
		$filename = "客户";
		$headArr = array('订单ID', '用户ID', '商品ID', '商品名称', '商品型号', '经销商', '品牌', '商品单价', '数量', '收货人', '收货人电话', '省份', '城市', '地区', '详细地址');
		exportExcel($filename, $headArr, $list, 'customer');
	}

	//添加用户
	public function addCustomer() {
		if (IS_POST) {
			$data['name'] = $_POST['name'];
			$data['nickname'] = $_POST['nickname'];
			$data['pwd'] = $_POST['pwd'];

			if (isset($_POST['phone'])) {
				$data['phone'] = $_POST['phone'];
			}
			if (isset($_POST['s_id'])) {
				$data['s_id'] = $_POST['s_id'];
			}
			if (isset($_POST['r_id'])) {
				$data['r_id'] = $_POST['r_id'];
			}
			$data['ctime'] = date('Y-m-d H:i:s'); //添加时间
			$data['utime'] = $data['ctime']; //添加时间

			$Customer = D('Customer');
			$result = $Customer->addCustomer($data);
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//更新用户数据
	public function updateCustomer() {
		if (IS_POST) {
			$map['id'] = $_POST['id'];
			$data['name'] = $_POST['name'];
			$data['nickname'] = $_POST['nickname'];
			if ($_POST['pwd']) {
				$data['pwd'] = $_POST['pwd'];
			}
			$data['phone'] = $_POST['phone'];
			$data['s_id'] = $_POST['s_id'];
			$data['r_id'] = $_POST['r_id'];
			$data['utime'] = date('Y-m-d H:i:s'); //修改时间

			$Customer = D('Customer');
			$result = $Customer->updateCustomer($map, $data); //先更新用户表数据
			$result = ($result >= 0) ? 1 : 0;
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//删除用户
	public function deleteCustomer() {
		if (IS_POST) {
			$map['id'] = $_POST['id'];
			$Customer = D('Customer');
			$result = $Customer->deleteCustomer($map); //删除用户表的数据
			if ($result > 0) {
				$CustomerRole = D('CustomerRole');
				$rmap['u_id'] = $map['id'];
				$CustomerRole->deleteCustomerRole($rmap); //删除用户_角色表的数据
			}
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//获取用户的角色
	public function getCustomerRoles() {
		if (IS_POST) {
			$map['u_id'] = $_POST['id'];
			$field = 'r_id';
			$CustomerRole = D('CustomerRole');
			$roles = $CustomerRole->getCustomerRoles($map, '', '', $field);
			$this->ajaxReturn($roles);
		}
	}
}
