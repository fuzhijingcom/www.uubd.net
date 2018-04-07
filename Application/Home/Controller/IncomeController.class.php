<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class IncomeController extends HomebaseController {
	//收入页
	public function income() {
		if ($_GET['m_id']) {
			session('m_id', $_GET['m_id']);
		}
		$userinfo = session('userinfo');
		$pmap['r_id'] = $userinfo['userr_id'];
		$pmap['m_id'] = session('m_id');
		$RoleMenuPackage = D('RoleMenuPackage');
		$join = 'package ON role_menu_package.p_id = package.p_id';
		$incomePackages = $RoleMenuPackage->getRoleMenuPackages($pmap, '', '', '', $join);

		$umap['id'] = $userinfo['userid'];
		$filed = 'id,name,nickname,phone,point,user.s_id,s_name';
		$join = 'store ON user.s_id = store.s_id';
		$jointype = 'LEFT';
		$User = D('User');
		$user = $User->findUser($umap, $filed, $join, $jointype);
		if ($user) {
			$Withdraw = D('Withdraw');
			if ($user['s_id'] == -1) {
				$sql = "SELECT ROUND((SUM(tpayment)-SUM(tpayment*spoint)),2) AS todaypayment,ROUND(SUM(tpayment),2) AS totalpayment,ROUND(SUM(payment*spoint),2) AS pointpayment FROM ((SELECT shop,SUM(CASE WHEN STR_TO_DATE(pay_time,'%Y-%m-%d') = '" . date('Y-m-d') . "' THEN payment ELSE 0 END) AS tpayment,SUM(payment) AS payment FROM __TABLE__ WHERE status IN ('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED') GROUP BY shop) t LEFT OUTER JOIN store s ON (t.shop=s.s_name) LEFT OUTER JOIN (SELECT s_id,SUM(point) AS spoint from user GROUP BY s_id) u ON (s.s_id=u.s_id))";

				$wmap['w_status'] = 1;
				$earnedPayment = $Withdraw->getWithdrawSum($wmap, 'w_cash');
				$earnedPayment = ($earnedPayment) ? $earnedPayment : 0;
			} else {
				$smap['s_id'] = $user['s_id'];

				$sql = "SELECT ROUND(SUM(CASE WHEN STR_TO_DATE(pay_time,'%Y-%m-%d') = '" . date('Y-m-d') . "' THEN payment ELSE 0 END)*" . $user['point'] . ",2) AS todaypayment,ROUND(SUM(CASE WHEN STR_TO_DATE(pay_time,'%Y-%m-%d') = '" . date('Y-m-d') . "' THEN payment ELSE 0 END),2) AS totalpayment,ROUND(SUM(payment)*" . $user['point'] . ",2) AS pointpayment FROM __TABLE__ WHERE STR_TO_DATE(pay_time,'%Y-%m-%d') = '" . date('Y-m-d') . "' AND status IN ('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED') AND shop='" . $user['s_name'] . "'";

				$wmap['u_id'] = $userinfo['userid'];
				$wmap['s_name'] = $user['s_name'];
				$wmap['w_status'] = 1;
				$earnedPayment = $Withdraw->getWithdrawSum($wmap, 'w_cash');
				$earnedPayment = ($earnedPayment) ? $earnedPayment : 0;
			}

			$Store = D('Store');
			$stores = $Store->getStores($smap);

			$Tradedetail = D('Tradedetail');
			$paymentSet = $Tradedetail->querySql($sql);
			//echo $Tradedetail->getLastSql();
			$todayPayment = ($paymentSet[0]['todaypayment']) ? $paymentSet[0]['todaypayment'] : '0.0';
			$totalPayment = ($paymentSet[0]['totalpayment']) ? $paymentSet[0]['totalpayment'] : '0.0';

			$this->assign('todayPayment', $todayPayment); //今日收入
			$this->assign('totalPayment', $totalPayment); //总收入

			if ($user['s_id'] == -1) {
				$pointPayment = ($paymentSet[0]['pointpayment']) ? $paymentSet[0]['pointpayment'] : 0;
				$pointPayment = ($pointPayment - $earnedPayment) ? ($pointPayment - $earnedPayment) : '0.0';
				$this->assign('pointPayment', $pointPayment); //门店分成的金额
			} else {
				$leftPayment = ($totalPayment - $earnedPayment) ? ($totalPayment - $earnedPayment) : '0.0';
				$this->assign('leftPayment', $leftPayment); //门店可提取的金额
			}

			$this->assign('startDate', date('Y-m-d'));
			$this->assign('endDate', date('Y-m-d'));
			$this->assign('stores', $stores);
			$this->assign('incomePackages', $incomePackages); // 菜单组件
			$this->display();
		} else {
			$this->error('您尚未登录，即将在跳转至登录页面！', C('NGINX_ROOT') . U('Home/Index/login'));
		}
	}

	//获取收入
	public function getIncome() {
		if (IS_POST) {
			$userinfo = session('userinfo');
			$umap['id'] = $userinfo['userid'];
			$filed = 'id,name,nickname,phone,point,user.s_id,s_name';
			$join = 'store ON user.s_id = store.s_id';
			$jointype = 'LEFT';
			$User = D('User');
			$user = $User->findUser($umap, $filed, $join, $jointype);
			if ($user) {
				$Tradedetail = D('Tradedetail');
				$paymentData = array();
				if ($user['s_id'] == -1) {
					$sql = "SELECT (SUM(payment)-SUM(payment*spoint)) AS todaypayment,SUM(payment) AS totalpayment FROM ((SELECT shop,SUM(payment) AS payment FROM __TABLE__ WHERE STR_TO_DATE(pay_time,'%Y-%m-%d') between '" . $_POST['startDate'] . "' AND '" . $_POST['endDate'] . "' AND status IN ('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED') GROUP BY shop) t LEFT OUTER JOIN store s ON (t.shop=s.s_name) LEFT OUTER JOIN (SELECT s_id,SUM(point) AS spoint from user GROUP BY s_id) u ON (s.s_id=u.s_id))";
					$paymentSet = $Tradedetail->querySql($sql);
					$todayPayment = ($paymentSet[0]['todaypayment']) ? $paymentSet[0]['todaypayment'] : '0.0';
					$totalPayment = ($paymentSet[0]['totalpayment']) ? $paymentSet[0]['totalpayment'] : '0.0';
				} else {
					$map['s_id'] = $user['s_id'];
					if (isset($_POST['startDate']) && isset($_POST['endDate'])) {
						$map["STR_TO_DATE(pay_time,'%Y-%m-%d')"] = array('between', array($_POST['startDate'], $_POST['endDate']));
					}
					if (isset($_POST['store'])) {
						$map['shop'] = $_POST['store'];
					}

					$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS', 'WAIT_BUYER_CONFIRM_GOODS', 'TRADE_BUYER_SIGNED'));
					$field = 'payment';

					$totalpayment = $Tradedetail->getTradedetailSum($map, $field);
					$todayPayment = $totalpayment * $user['point'];
				}
				$paymentData['todayPayment'] = $todayPayment;
				$paymentData['totalPayment'] = $totalPayment;
				$sql = $Tradedetail->getLastSql();
				$paymentData['sql'] = $sql;
				$this->ajaxReturn($paymentData);
			}

		}
	}

	//获取可提现金额
	public function getLeftPayment() {
		if (IS_POST) {
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
				if ($user['s_id'] == -1) {
					$sql = "SELECT SUM(payment*spoint) AS pointpayment FROM ((SELECT shop,SUM(payment) AS payment FROM __TABLE__ WHERE status IN ('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED') GROUP BY shop) t LEFT OUTER JOIN store s ON (t.shop=s.s_name) LEFT OUTER JOIN (SELECT s_id,SUM(point) AS spoint from user GROUP BY s_id) u ON (s.s_id=u.s_id))";
					$paymentSet = $Tradedetail->querySql($sql);
					$pointPayment = ($paymentSet[0]['pointpayment']) ? $paymentSet[0]['pointpayment'] : '0.0';

					$wmap['w_status'] = 1;
					$earnedPayment = $Withdraw->getWithdrawSum($wmap, 'w_cash');
					$earnedPayment = ($earnedPayment) ? $earnedPayment : 0;
					$leftData['pointPayment'] = ($pointPayment - $earnedPayment) ? ($pointPayment - $earnedPayment) : '0.0'; //剩余可分成的金额要减去已经提取的金额
				} else {
					$tmap['shop'] = $user['s_name'];
					$tmap['status'] = array('in', array('WAIT_SELLER_SEND_GOODS', 'WAIT_BUYER_CONFIRM_GOODS', 'TRADE_BUYER_SIGNED'));
					$field = 'payment';
					$totalPayment = $Tradedetail->getTradedetailSum($tmap, $field);

					$wmap['u_id'] = $userinfo['userid'];
					$wmap['s_name'] = $user['s_name'];
					$wmap['w_status'] = 1;
					$earnedPayment = $Withdraw->getWithdrawSum($wmap, 'w_cash');
					$earnedPayment = ($earnedPayment) ? $earnedPayment : 0;
					$leftData['leftPayment'] = ($totalPayment - $earnedPayment) ? ($totalPayment - $earnedPayment) : '0.0';
				}
				$sql = $Tradedetail->getLastSql();
				$leftData['sql'] = $sql;
				$this->ajaxReturn($leftData);
			}
		}
	}

	//显示提现记录
	public function withdraw() {
		if (IS_GET) {
			$userinfo = session('userinfo');
			$pmap['r_id'] = $userinfo['userr_id'];
			$pmap['m_id'] = session('m_id');
			$RoleMenuPackage = D('RoleMenuPackage');
			$join = 'package ON role_menu_package.p_id = package.p_id';
			$menuPackages = $RoleMenuPackage->getRoleMenuPackages($pmap, '', '', '', $join);

			if ($userinfo['users_id'] != -1) {
				$map['u_id'] = $userinfo['userid'];
			}
			if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
				$map["STR_TO_DATE(w_utime,'%Y-%m-%d')"] = array('between', array($_GET['startDate'], $_GET['endDate']));
			}
			if (isset($_GET['store'])) {
				$map['s_name'] = $_GET['store'];
			}

			$Withdraw = D('Withdraw');
			$count = $Withdraw->getWithdrawCount($map); // 查询满足要求的总记录数
			$Page = new \Think\Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数(25)
			$show = $Page->show(); // 分页显示输出
			// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
			$sort = 'w_utime desc';
			$join = 'user ON withdraw.u_id = user.id';
			$limit = $Page->firstRow . ',' . $Page->listRows;
			$withdraws = $Withdraw->getWithdraws($map, $sort, $limit, '', $join);

			$this->assign('menuPackages', $menuPackages); // 菜单组件
			$this->assign('withdraws', $withdraws); // 赋值数据集
			$this->assign('page', $show); // 赋值分页输出
			$this->display(); // 输出模板
		}
	}

	//添加提现记录
	public function addWithdraw() {
		if (IS_POST) {
			$userinfo = session('userinfo');
			$data['u_id'] = $userinfo['userid'];
			$data['w_cash'] = $_POST['cash'];
			$data['w_status'] = 0;
			$data['w_ctime'] = date('Y-m-d H:i:s'); //添加时间
			$data['w_utime'] = $data['w_ctime']; //添加时间

			$umap['id'] = $userinfo['userid'];
			$join = 'store ON user.s_id = store.s_id';
			$User = D('User');
			$user = $User->findUser($umap, '', $join);
			if ($user) {
				$data['s_name'] = $user['s_name'];
			}

			$Withdraw = D('Withdraw');
			$result = $Withdraw->addWithdraw($data);
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//更新提现记录
	public function updateWithdraw() {
		if (IS_POST) {
			if (isset($_POST['id'])) {
				$map['w_id'] = $_POST['id'];
			}
			if (isset($_POST['cash'])) {
				$data['w_cash'] = $_POST['cash'];
			}
			if (isset($_POST['status'])) {
				$data['w_status'] = $_POST['status'];
			}
			$data['w_utime'] = date('Y-m-d H:i:s'); //修改时间

			$Withdraw = D('Withdraw');
			$result = $Withdraw->updateWithdraw($map, $data);
			$result = ($result >= 0) ? 1 : 0;
			$this->ajaxReturn(array('result' => $result));
		}
	}

	//删除提现记录
	public function deleteWithdraw() {
		if (IS_POST) {
			$map['w_id'] = $_POST['id'];
			$Withdraw = D('Withdraw');
			$result = $Withdraw->deleteWithdraw($map);
			$this->ajaxReturn(array('result' => $result));
		}
	}
}