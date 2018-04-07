<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;
use Org\Wechat\sendMessage;

class OrderController extends HomebaseController {
	public function in() {
		Vendor('Youzan.Youzan');
		$youzan = \Youzan::getInstance();
		$params = array('tid' => 'E20160403140942068738490');
		$orders = $youzan->getTrade($params);
		dump($orders);
	}

	public function test() {
		$db = M("customer");
		$count = $db->where(1)->count();
		$pagecount = 1;
		$page = new \Think\Page($count, $pagecount);
		$page->setConfig('first', '首页');
		$page->setConfig('prev', '上一页');
		$page->setConfig('next', '下一页');
		$page->setConfig('last', '尾页');
		$page->setConfig('theme', '%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% 第 ' . I('p', 1) . ' 页/共 %TOTAL_PAGE% 页 ( ' . $pagecount . ' 条/页 共 %TOTAL_ROW% 条)');
		$show = $page->show();
		$this->assign('show', $show);
		$this->assign('Test', 'Test');
		$this->assign('test2', 'Test');
		$list = $db->where(1)->limit($page->firstRow . ',' . $page->listRows)->select();
		$this->assign('list', $list);
		$status = array('-1' => '订单状态', '0' => '已发货', '1' => '未发货', '2' => '已完成', '3' => '已取消');
		$this->assign('startDate', date('Y-m-d'));
		$this->assign('status', $status);
		$this->display();
	}
	/**方法**/
	public function exportExcel($expTitle, $expCellName, $expTableData) {
		$xlsTitle = iconv('utf-8', 'gb2312', $expTitle); //文件名称
		$fileName = $_SESSION['account'] . date('_YmdHis'); //or $xlsTitle 文件名称可根据自己情况设定
		$cellNum = count($expCellName);
		$dataNum = count($expTableData);
		vendor('PHPExcel.PHPExcel');
		$objPHPExcel = new \PHPExcel();
		$cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

		$objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1'); //合并单元格
		// $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
		for ($i = 0; $i < $cellNum; $i++) {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
		}
		// Miscellaneous glyphs, UTF-8
		for ($i = 0; $i < $dataNum; $i++) {
			for ($j = 0; $j < $cellNum; $j++) {
				$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
			}
		}

		header('pragma:public');
		header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
		header("Content-Disposition:attachment;filename=$fileName.xls"); //attachment新窗口打印inline本窗口打印
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}
	/**
	 *
	 * 导出Excel
	 */
	function expUser() {
//导出Excel
		$xlsName = "User";
		$xlsCell = array(
			array('user_id', '用户id'),
			array('weixin_openid', '微信openid'),
			array('nick', '昵称'),
			array('avatar', '头像'),
			array('follow_time', '关注时间'),
			array('sex', '性别'),
			array('province', '毕业时间'),
			array('city', '所在地'),
			array('points', '积分'),
			array('traded_num', '职称'),
			array('traded_money', '职务'),
			array('tags', '门店'),
			array('level_info', '电话'),
			array('union_id', 'qq'),
			array('is_follow', '邮箱'),
		);
		$xlsModel = M('Customer');

		$xlsData = $xlsModel->select();
		$this->exportExcel($xlsName, $xlsCell, $xlsData);

	}

	// 拉取用户信息
	public function getCustomer() {
		set_time_limit(1000);
		// http://www.66mjyj.com/index.php/home/order/getcustomer?refresh=refresh
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
			// dump('next');
			if (is_array($users['response']['users'])) {
				$this->handle_customer_data($users['response']['users']);
			}

			if ($users['response']['has_next']) {
				$after_fans_id = $users['response']['last_fans_id'];
			} else {
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

	private function handle_customer_data($handle_data) {
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
			$data['level_info'] = "";
			$data['union_id'] = $vo['union_id'];
			$map['weixin_openid'] = $vo['weixin_openid'];
			if ($Customer->findCustomer($map)) {
				// dump('old user');
				$Customer->updateCustomer($map, $data); // 已经有用户不需要再添加数据
			} else {
				$Customer->addCustomer($data);
			}
			// dump($data);
		}
	}

	// 获取订单
	public function getOrder() {
		// 更新自有订单的签收状态
		// 下面的 SQL 语句仅对本身后台的订单更新（即非有赞的订单，有赞的订单程度为 32 位，本地的长度为 16位）
		// 自取的时间大于 2 天，或者非自取的时间大于 7 天，且是等待签收的订单，则更新签收状态和签收时间
		$sql = "update `tradedetail` set `status` = 'TRADE_BUYER_SIGNED', `sign_time` = now()
where (TIMESTAMPDIFF(SECOND,update_time,now())>2*24*3600 and `status`='WAIT_BUYER_CONFIRM_GOODS' and `receiver_address` like '%自取%' and LENGTH(`tid`) < 20) or (TIMESTAMPDIFF(SECOND,update_time,now())>7*24*3600 and `status`='WAIT_BUYER_CONFIRM_GOODS' and `receiver_address` not like '%自取%' and LENGTH(`tid`) < 20)";

		$TradetailModel = M('Tradedetail');
		$TradetailModel->execute($sql);

		// http://www.66mjyj.com/index.php/home/order/getorder?refresh=refresh
		$refresh = I('get.refresh', null, 'htmlspecialchars');

		Vendor('Youzan.Youzan');
		$youzan = \Youzan::getInstance();
		$Tradetail = D('Tradedetail');

		if ($refresh === 'refresh') {
			// 被强制刷新
			S('getOrderTime', null);
		}
		$start_update = S('getOrderTime');
		$curTime = date("Y-m-d H:i:s", time() - 8);

//		S('getOrderTime', $curTime);
		$page_no = 1;

		while (1) {
			if ($start_update) {
				// 说明已经有拉取过的数据，就按最后拉取的更新时间来获取订单
				$params = array('start_update' => $start_update, 'page_no' => $page_no, 'page_size' => 100, 'use_has_next' => true);
			} else {
				$params = array('page_size' => 100, 'page_no' => $page_no, 'use_has_next' => true);
			}

			// $status_arr = array('WAIT_SELLER_SEND_GOODS', 'TRADE_CLOSED', 'TRADE_BUYER_SIGNED');
			$orders = $youzan->getTradesSold($params);
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
					} else {
						dump($arr);
						dump('new trades');
						$data = $this->handle_data($vo);

						$Tradetail->addTradedetail($data);
					}
				}
			}

			if (!$orders['response']['has_next']) {
				break;
			}
			$page_no++;
		}

		// 此次成功处理数据后，返回
		S('getOrderTime', $curTime);
		// 将每次执行时间保存到日志记录中
		define('OUTPUT_LOG_FLAG', true);
		$file_name = 'Youzan/getorder-' . date('Ymd') . '.log';
		outputDebugLog(S('getOrderTime'), 8, $file_name);
	}

	private function handle_data($vo) {
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
		$data['state_str'] = $vo['orders'][0]['state_str'];

		$map['user_id'] = $vo['buyer_id'];
		if ($person = $Customer->findCustomer($map)) {
			if ($person['store_tags'] != '') {
				$data['shop'] = $person['store_tags'];
			} else {
				$data['shop'] = '总店';
			}

			if ($person['tags'] != '') {
				$data['promotion'] = $person['tags'];
			} else {
				$data['promotion'] = '总店';
			}
		} else {
			$data['shop'] = '总店';
			$data['promotion'] = '总店';
		}

		// 处理订单付款类型
		// 若是支付宝支付类型，则将门店改成 “其他支付方式”
		if ($data['pay_type'] == 'ALIPAY'||($data['type']=='QRCODE'&&$data['pay_type']=='WEIXIN')) {
			$data['shop'] = '其他支付方式';
			$data['promotion'] = '其他支付方式';
		}

		return $data;

	}

	//订单页
	public function order() {
		//$Store = D('Store');
		$Store = new \Home\Model\StoreModel();

        $userinfo = session('userinfo');
        if ($userinfo['users_id'] != -1) {
            $map['s_id'] = $userinfo['users_id'];

            //找出当前登录用户的所属门店名
            $storeInfo = $Store->where(array('s_id' => $map['s_id']))->find();
        }

        $map['s_type'] = 0;
		$stores = $Store->getStores($map);


		$Tradedetail =  M('Tradedetail');

        if($userinfo['users_id'] != -1){
            //将门店名称赋值给where条件，（只找出当前登录用户所属门店的相关数据）
            $where['shop'] = $storeInfo['s_name'];
        }

        $where['status'] = 'WAIT_SELLER_SEND_GOODS';
		$wait_send_order_count = $Tradedetail->where($where)->count();
        $where['status'] = 'WAIT_BUYER_PAY';
        $wait_pay_order_count = $Tradedetail->where($where)->count();
        $where['status'] = 'WAIT_BUYER_CONFIRM_GOODS';
		$send_order_count = $Tradedetail->where($where)->count();
        $where['status'] = 'TRADE_BUYER_SIGNED';
		$success_order_count = $Tradedetail->where($where)->count();

        unset($where['status']);

        $where['feedback'] = '1';
		$wait_rightsprotection_count = $Tradedetail->where($where)->count();
		$where['feedback'] = array('in','600,601,602,603');
		$rightsprotection_end_count = $Tradedetail->where($where)->count();

		$this->assign('wait_send_order_count',$wait_send_order_count);
        $this->assign('wait_pay_order_count',$wait_pay_order_count);
		$this->assign('send_order_count',$send_order_count);
		$this->assign('success_order_count',$success_order_count);
		$this->assign('wait_rightprotection_count',$wait_rightsprotection_count);
		$this->assign('rightprotection_end_count',$rightsprotection_end_count);

		$this->assign('stores', $stores); //门店
		$this->assign('startDate', date('Y-m-d', strtotime('-1 day')));
		$this->assign('endDate', date('Y-m-d'));
		$this->display();
	}

	//订单表格
	public function ordertable() {
		outputDebugLog('in ordertable',8);
		if (IS_GET) {
			$userinfo = session('userinfo');
			$umap['user_id'] = $userinfo['user_id'];
			$filed = 'user_id,name,nickname,phone,point,user.s_id,s_name';
			$join = 'store ON user.s_id = store.s_id';
			$jointype = 'LEFT';
			//$User = D('User');
			$UserModel = new \Home\Model\UserModel();
			$user = $UserModel->findUser($umap, $filed, $join, $jointype);
			
			if ($user) {
				if ($user['s_id'] != -1) {
					$map['shop'] = $user['s_name'];
				}
				if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
					$map["STR_TO_DATE(created,'%Y-%m-%d')"] = array('between', array($_GET['startDate'], $_GET['endDate']));
				} /*else {
					$map["STR_TO_DATE(created,'%Y-%m-%d')"] = array('between', array(date('Y-m-d', strtotime('-1 day')), date('Y-m-d')));
				}*/
				if (isset($_GET['store'])) {
					$map['shop'] = $_GET['store'];
				}

				$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS'));

				if (isset($_GET['status'])) {
                    $orderstatus = $_GET['status'];
					switch ($_GET['status']) {
					case "topay"://待付款
						$map['status'] = array('in', array( 'WAIT_BUYER_PAY'));
						break;
					case "tosend"://待发货（已付款）
						$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS'));
						break;
					case "send"://已发货（待确认收货）
						$map['status'] = array('in', array('WAIT_BUYER_CONFIRM_GOODS'));
						break;
					case "success"://已签收
						$map['status'] = array('in', array('TRADE_BUYER_SIGNED'));
						break;
					case "cancel"://订单关闭
						$map['status'] = array('in', array('TRADE_CLOSED', 'TRADE_CLOSED_BY_USER'));
						break;
					case "safeguard"://申请售后服务
						$map['feedback'] = 1;
						$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED'));
						break;
                    case "safeguardend"://售后服务关闭
                        $map['feedback'] = array('in','600,601,602,603');
                        $map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED','TRADE_CLOSED'));
                        break;
					default:
						break;
					}
					//$map['status'] = $_GET['status'];
				}
//                outputDebugLog($map['status'][1],8);
				if (isset($_GET['keyword'])) {
					$where['tid'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['title'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['buyer_id'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['buyer_nick'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['receiver_mobile'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['receiver_name'] = array('like', '%' . $_GET['keyword'] . '%');
					$where['_logic'] = 'or';
					$map['_complex'] = $where;
				}

				$Partner = M('Partner');
				$Tradedetail = new \Home\Model\TradedetailModel();

				$count = $Tradedetail->getTradedetailCount($map); // 查询满足要求的总记录数
				$Page = new \Think\Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数(25)
				$show = $Page->show(); // 分页显示输出

				// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
				$sort = "`status` = 'WAIT_SELLER_SEND_GOODS' desc ,created desc";
				$limit = $Page->firstRow . ',' . $Page->listRows;
				$filed = 'weixin_user_id,tid,price,pay_type,refund_state,outer_tid,shop,receiver_state,receiver_city,receiver_district,receiver_address,SUBSTRING(tid,2) AS water,mark,feedback,privilege_id';
				//先获取
//				$map['status'] = 'WAIT_SELLER_SEND_GOODS';
//				$trades_send_goods = $Tradedetail->getTradedetails($map, $sort, $limit, $filed);
//				//先获取
//				$map['feedback'] = 1;
//				$trades_feedback = $Tradedetail->getTradedetails($map, $sort, $limit, $filed);
//
//				$trades_send_goods_feedback = array_merge($trades_feedback,$trades_send_goods);
//
//				$map['feedback'] = array('NEQ',1);
				//$trades_nofeedback = $Tradedetail->getTradedetails($map, $sort, $limit, $filed);
				$trades = $Tradedetail->getTradedetails($map, $sort, $limit, $filed);
                foreach($trades as $k => $v) {
                    $trades[$k]['status'] = $orderstatus?$orderstatus:'tosend';

                    if($v['privilege_id']){
                        $where = array(
                            'privilege_id' => $v['privilege_id'],
                        );
                        $partnerInfo = $Partner->where($where)->find();
                        $trades[$k]['service'] = $partnerInfo['p_name'];
                        $trades[$k]['service'] = mb_substr($trades[$k]['service'],0,9);

                    }
                }
//                $trades['status'] = $map['status'][1];

//				$trades = array_merge($trades_send_goods_feedback,$trades_nofeedback);

				//echo $Tradedetail->getLastSql();
				//print_r($trades);
                outputDebugLog($trades);
//                $this->assign('status',$orderstatus);
				$this->assign('trades', $trades); // 赋值数据集
				$this->assign('page', $show); // 赋值分页输出
				$this->display(); // 输出模板
			} else {
				$this->error('您尚未登录，即将在跳转至登录页面！', C('NGINX_ROOT') . U('Home/Index/login'));
			}
		}
	}

	public function getOrderDetail() {
		$tid = $_GET['tid'];

		// tid （订单号） 长度大于 14 的是在有赞上的，而等于 14 的订单号是在 66 后台生成的订单号，后者从数据库直接读取相关数据
		if (strlen($tid) > 14) {
			$field = 'tid,payment,status,refund_state,weixin_user_id,buyer_id,buyer_nick,buyer_message,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,created,update_time,trade_memo,orders';
			Vendor('Youzan.Youzan');
			$youzan = \Youzan::getInstance();
			$params = array('fields' => $field, 'tid' => $tid);
			$order = $youzan->getTrade($params);
			echo json_encode($order['response']['trade']);
		} else {
			$map = array(
				'tid' => $tid,
			);
			$TradeModel = M('tradedetail');
            $TradeDetailModel = M('trade_submit_temp');
			$result = $TradeModel->where($map)->find();
            $sku_id_result = $TradeDetailModel->where($map)->field('g_id')->find();


			if($result['pay_type']=='SCAN_QRCODE'||$result['pay_type']=='Weixin_pay'||$result['pay_type']=='others'){
				$info = explode('@',$result['title']);
                $result['title'] = $info[0];
                $result['sku_properties_name'] = $info[1]?'款式:'.$info[1].';':'';
                $result['sku_properties_name'] .= $info[2]?'套餐:'.$info[2]:'';
				if(strpos($result['sku_properties_name'],'套餐:')===false){
					if($result['o_id'] == 0){
						$result['sku_properties_name'] .= '种类:无度数';
					}else{
						$result['sku_properties_name'] .= '种类:度数定制版';
					}
				}
			}

			$trade = array(
				'status' => isset($result['status']) ? $result['status'] : '',
				'refund_state' => isset($result['refund_state']) ? $result['refund_state'] : '',
				'payment' => isset($result['payment']) ? $result['payment'] : '0.00',
				'created' => isset($result['created']) ? $result['created'] : '0000-00-00 00:00:00',
				'update_time' => isset($result['update_time']) ? $result['update_time'] : '0000-00-00 00:00:00',
				'buyer_message' => isset($result['buyer_message']) ? $result['buyer_message'] : '',
				'orders' =>
					array (
						0 =>
							array (
								'oid' => isset($result['oid']) ? $result['oid'] : '',
								'outer_sku_id' => isset($result['outer_sku_id']) ? $result['outer_sku_id'] : '',
								'outer_item_id' => isset($result['outer_item_id']) ? $result['outer_item_id'] : '',
								'title' => isset($result['title']) ? $result['title'] : '',
								'seller_nick' => 'uubd商城',
								'fenxiao_price' => isset($result['fenxiao_price']) ? $result['fenxiao_price'] : '0.00',
								'fenxiao_payment' => isset($result['fenxiao_payment']) ? $result['fenxiao_payment'] : '0.00',
								'price' => isset($result['price']) ? $result['price'] : '0.00',
								'total_fee' => isset($result['total_fee']) ? $result['total_fee'] : '0.00',
								'payment' => isset($result['payment']) ? $result['payment'] : '0.00',
								'discount_fee' => isset($result['discount_fee']) ? $result['discount_fee'] : '0.00',
								'sku_id' => isset($result['sku_id']) ? $result['sku_id'] : 0,
								'sku_unique_code' => isset($result['sku_unique_code']) ? $result['sku_unique_code'] : '',
								'sku_properties_name' => isset($result['sku_properties_name']) ? $result['sku_properties_name'] : '',
//                                'sku_properties_combo' => isset($result['sku_properties_combo']) ? $result['sku_properties_combo'] : '',
								'pic_path' => isset($result['pic_path']) ? $result['pic_path'] : '#',
								'pic_thumb_path' => isset($result['pic_thumb_path']) ? $result['pic_thumb_path'] : '#',
								'item_type' => isset($result['item_type']) ? $result['item_type'] : '',
								'buyer_messages' => isset($result['buyer_message']) ? $result['buyer_message'] : '',
								'order_promotion_details' => isset($result['order_promotion_details']) ? $result['order_promotion_details'] : '',
								'state_str' => isset($result['state_str']) ? $result['state_str'] : '',
								'allow_send' => isset($result['allow_send']) ? $result['allow_send'] : '',
								'is_send' => isset($result['is_send']) ? $result['is_send'] : '',
								'item_refund_state' => isset($result['item_refund_state']) ? $result['item_refund_state'] : '',
								'is_virtual' => isset($result['is_virtual']) ? $result['is_virtual'] : 0,
								'refunded_fee' => isset($result['refunded_fee']) ? $result['refunded_fee'] : '0.00',
								'num_iid' => isset($result['num_iid']) ? $result['num_iid'] : 0,
								'num' => isset($result['num']) ? $result['num'] : '1',
							),
					),
				'weixin_user_id' => isset($result['weixin_user_id']) ? $result['weixin_user_id'] : '',
				'trade_memo' => isset($result['trade_memo']) ? $result['trade_memo'] : '',
                'other_remark' => isset($result['other_remark']) ? $result['other_remark'] : '',
				'buyer_nick' => isset($result['buyer_nick']) ? $result['buyer_nick'] : '',
				'tid' => isset($result['tid']) ? $result['tid'] : $tid,
				'buyer_id' => isset($result['buyer_id']) ? $result['buyer_id'] : '',
				'receiver_city' => isset($result['receiver_city']) ? $result['receiver_city'] : '',
				'receiver_district' => isset($result['receiver_district']) ? $result['receiver_district'] : '',
				'receiver_name' => isset($result['receiver_name']) ? $result['receiver_name'] : '',
				'receiver_state' => isset($result['receiver_state']) ? $result['receiver_state'] : '',
				'receiver_address' => isset($result['receiver_address']) ? $result['receiver_address'] : '',
				'receiver_mobile' => isset($result['receiver_mobile']) ? $result['receiver_mobile'] : '',
			);
			echo json_encode($trade);
		}
	}

	public function exportOrder() {
		if (IS_GET) {
			if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
				$map["STR_TO_DATE(created,'%Y-%m-%d')"] = array('between', array($_GET['startDate'], $_GET['endDate']));
			}
			if (isset($_GET['store'])) {
				$map['shop'] = $_GET['store'];
			}
			if (isset($_GET['status'])) {
				switch ($_GET['status']) {
				case "topay"://待付款
					$map['status'] = array('in', array('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN'));
					break;
				case "tosend"://待发货
					$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS'));
					break;
				case "send"://已发货（待确认收货）
					$map['status'] = array('in', array('WAIT_BUYER_CONFIRM_GOODS'));
					break;
				case "success"://已签收（已完成）
					$map['status'] = array('in', array('TRADE_BUYER_SIGNED'));
					break;
				case "cancel"://订单关闭
					$map['status'] = array('in', array('TRADE_CLOSED'));
					break;
				case "refunding"://退款成功
					$map['refund_state'] = array('in', array('PARTIAL_REFUNDING', 'FULL_REFUNDING'));
					break;
				default:
					break;
				}
			}
			if (isset($_GET['keyword'])) {
				$where['tid'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['title'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['buyer_id'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['buyer_nick'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['receiver_mobile'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['receiver_name'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['_logic'] = 'or';
				$map['_complex'] = $where;
			}
			$Tradedetail = D('Tradedetail');
			$sort = "created desc";
			$filed = "tid,shop,case when status in ('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN') then '待付款' when status in ('WAIT_SELLER_SEND_GOODS') then '待发货' when status in ('WAIT_BUYER_CONFIRM_GOODS') then '已发货' when status in ('TRADE_BUYER_SIGNED') then '已完成' when status in ('TRADE_CLOSED') then '已关闭' else '其他' end as status,num,weixin_user_id,buyer_nick,buyer_area,post_fee,total_fee,payment,case when refund_state='PARTIAL_REFUNDING' then '部分退款中' when refund_state='PARTIAL_REFUNDED' then '已部分退款' when refund_state='PARTIAL_REFUND_FAILED' then '部分退款失败' when refund_state='FULL_REFUNDING' then '全额退款中' when refund_state='FULL_REFUNDED' then '已全额退款' when refund_state='FULL_REFUND_FAILED' then '全额退款失败' else '' end as refund_state,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,receiver_zip,case when shipping_type='express' then '快递' when shipping_type='fetch' then '到店自提' else '其他' end as shipping_type,created,pay_time,buyer_message,seller_flag,trade_memo";
			$trades = $Tradedetail->getTradedetails($map, $sort, '', $filed);
			$Optometry = D('Optometry');
			foreach ($trades as $key => $trade) {
				$omap['user_id'] = $trade['weixin_user_id'];
				$osort = 'time desc';
				$olimit = '1';
				$optometry = $Optometry->getOptometrys($omap, $osort, $olimit);
				if ($optometry) {
					$trades[$key]['optometry'] = "右眼：" . $optometry[0]['rdegree'] . " / " . $optometry[0]['rastigmatism'] . " / " . $optometry[0]['raxial'] . " ;\n左眼：" . $optometry[0]['ldegree'] . " / " . $optometry[0]['lastigmatism'] . " / " . $optometry[0]['laxial'] . " ;" . "\n瞳距：" . ($optometry[0]['rpd'] + $optometry[0]['lpd']);
				}
			}

			$filename = "订单表";
			$headArr = array('订单编号', '门店名称', '订单状态', '商品总数量', '买家ID', '买家昵称', '买家地区', '邮费', '总金额', '实付金额', '售后信息', '收货人姓名', '收货人电话', '收货人省份', '收货人城市', '收货人地区', '收货地址', '邮政编码', '运送方式', '订单创建时间', '订单付款时间', '买家附言', '星级', '订单备注', '验光数据');
			//print_r($trades);
			exportExcel($filename, $headArr, $trades, 'sendOrder');
		}
	}

	public function exportSendOrder() {
		if (IS_GET) {
			if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
				$map["STR_TO_DATE(update_time,'%Y-%m-%d')"] = array('between', array($_GET['startDate'], $_GET['endDate']));
			}
			if (isset($_GET['store'])) {
				$map['shop'] = $_GET['store'];
			}
			if (isset($_GET['status'])) {
				switch ($_GET['status']) {
					case "topay"://待付款
						$map['status'] = array('in', array('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN'));
						break;
					case "tosend"://待发货（已付款）
						$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS'));
						break;
					case "send"://已发货（待确认收货）
						$map['status'] = array('in', array('WAIT_BUYER_CONFIRM_GOODS'));
						break;
					case "success"://已完成
						$map['status'] = array('in', array('TRADE_BUYER_SIGNED'));
						break;
					case "cancel"://已关闭
						$map['status'] = array('in', array('TRADE_CLOSED'));
						break;
					case "refunding"://
						$map['refund_state'] = array('in', array('PARTIAL_REFUNDING', 'FULL_REFUNDING'));
						break;
					default:
						break;
				}
			}
			if (isset($_GET['keyword'])) {
				$where['tid'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['title'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['buyer_id'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['buyer_nick'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['receiver_mobile'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['receiver_name'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['_logic'] = 'or';
				$map['_complex'] = $where;
			}
			$Tradedetail = D('Tradedetail');
			$sort = "created desc";
			$filed = "tid,'' as title,shop,case when status in ('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN') then '待付款' when status in ('WAIT_SELLER_SEND_GOODS') then '待发货' when status in ('WAIT_BUYER_CONFIRM_GOODS') then '已发货' when status in ('TRADE_BUYER_SIGNED') then '已完成' when status in ('TRADE_CLOSED') then '已关闭' else '其他' end as status,weixin_user_id,buyer_nick,total_fee,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,buyer_area,seller_flag,trade_memo";
			$trades = $Tradedetail->getTradedetails($map, $sort, '', $filed);
			$Optometry = D('Optometry');
			foreach ($trades as $key => $trade) {
				$omap['user_id'] = $trade['weixin_user_id'];
				$osort = 'time desc';
				$olimit = '1';
				$optometry = $Optometry->getOptometrys($omap, $osort, $olimit);
				if ($optometry) {
					$trades[$key]['optometry'] = "右眼：" . $optometry[0]['rdegree'] . " / " . $optometry[0]['rastigmatism'] . " / " . $optometry[0]['raxial'] . " ;\n左眼：" . $optometry[0]['ldegree'] . " / " . $optometry[0]['lastigmatism'] . " / " . $optometry[0]['laxial'] . " ;" . "\n瞳距：" . ($optometry[0]['rpd'] + $optometry[0]['lpd']);
				}

				$goods = $this->getGoods($trade['tid']);
				if ($goods) {
					foreach ($goods['orders'] as $gk => $good) {
						$trades[$key]['title'] = $good['title'] . " \n" . $good['sku_properties_name'];
					}
				}
			}

			$filename = "订单发货表";
			$headArr = array('订单编号', '商品', '门店名称', '订单状态', '买家ID', '买家昵称', '总金额', '收货人姓名', '收货人电话', '收货人省份', '收货人城市', '收货人地区', '收货地址', '下单地址', '星级', '订单备注', '验光数据');
			exportExcel($filename, $headArr, $trades, 'sendOrder');
		}
	}
	
	public function exportOrderList() {
		set_time_limit(0);
        //起始时间
		if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
			$map["STR_TO_DATE(update_time,'%Y-%m-%d')"] = array('between', array($_GET['startDate'], $_GET['endDate']));
		}
		//门店
		if (isset($_GET['store'])) {
			$map['shop'] = $_GET['store'];
		}
		//订单状态
		if (isset($_GET['status'])) {
			switch ($_GET['status']) {
				case "topay":
					$map['status'] = array('in', array('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN'));
					break;
				case "tosend":
					$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS'));
					break;
				case "send":
					$map['status'] = array('in', array('WAIT_BUYER_CONFIRM_GOODS'));
					break;
				case "success":
					$map['status'] = array('in', array('TRADE_BUYER_SIGNED'));
					break;
				case "cancel":
					$map['status'] = array('in', array('TRADE_CLOSED'));
					break;
				case "refunding":
					$map['refund_state'] = array('in', array('PARTIAL_REFUNDING', 'FULL_REFUNDING'));
					break;
				default:
					break;
			}
		}
		if (isset($_GET['keyword'])) {
			$where['tid'] = array('like', '%' . $_GET['keyword'] . '%');
			$where['title'] = array('like', '%' . $_GET['keyword'] . '%');
			$where['buyer_id'] = array('like', '%' . $_GET['keyword'] . '%');
			$where['buyer_nick'] = array('like', '%' . $_GET['keyword'] . '%');
			$where['receiver_mobile'] = array('like', '%' . $_GET['keyword'] . '%');
			$where['receiver_name'] = array('like', '%' . $_GET['keyword'] . '%');
			$where['_logic'] = 'or';
			$map['_complex'] = $where;
		}
		$Tradedetail = D('Tradedetail');
		$sort = "created desc";
		$filed = "tid,'' as title,shop,case when status in ('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN') then '待付款' when status in ('WAIT_SELLER_SEND_GOODS') then '待发货' when status in ('WAIT_BUYER_CONFIRM_GOODS') then '已发货' when status in ('TRADE_BUYER_SIGNED') then '已完成' when status in ('TRADE_CLOSED') then '已关闭' else '其他' end as status,weixin_user_id,buyer_nick,total_fee,receiver_name,receiver_mobile,receiver_state,receiver_city,receiver_district,receiver_address,buyer_area,seller_flag,trade_memo";

		$trades = $Tradedetail->getTradedetails($map, $sort, '', $filed);

		$data = array();
		foreach ($trades as $key => $trade) {
			$receiver_name = $trade['receiver_name'];
			$tid = $trade['tid'];
			$receiver_mobile = $trade['receiver_mobile'];

			$receiver_state = $trade['receiver_state'];
			$receiver_city = $trade['receiver_city'];
			$receiver_district = $trade['receiver_district'];
			$receiver_address = $trade['receiver_address'];
			$address = "{$receiver_state} {$receiver_city} {$receiver_district} {$receiver_address}";
            //获取加工编号
			$process_id = $this->getFinishedProcessId($tid);

			$data[$key] = array(
				'process_id' => $process_id,
				'receiver_name' => $receiver_name,
				'tid' => $tid,
				'receiver_mobile' => $receiver_mobile,
				'address' => $address,
			);
		}

		$file_name = '发货清单表';
		$head_arr = array('加工编号', '收件人', '订单号', '手机', '地址');
		$format = array(
			'align' => array('A1', 'B1', 'C1', 'D1', 'E1'),
			'length' => array('A' => 12, 'B' => 10, 'C' => 26, 'D' => 13, 'E' => 70),
		);

		exportExcel($file_name, $head_arr, $data, 'sendOrder', $format);
	}

	public function exportAnalysis() {
		if (IS_GET) {
			set_time_limit(0);

			if (isset($_GET['store'])) {
				$map['shop'] = $_GET['store'];
			}
			if (isset($_GET['status'])) {
				switch ($_GET['status']) {
					case "topay":
						$map['status'] = array('in', array('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN'));
						break;
					case "tosend":
						$map['status'] = array('in', array('WAIT_SELLER_SEND_GOODS'));
						break;
					case "send":
						$map['status'] = array('in', array('WAIT_BUYER_CONFIRM_GOODS'));
						break;
					case "success":
						$map['status'] = array('in', array('TRADE_BUYER_SIGNED'));
						break;
					case "cancel":
						$map['status'] = array('in', array('TRADE_CLOSED'));
						break;
					case "refunding":
						$map['refund_state'] = array('in', array('PARTIAL_REFUNDING', 'FULL_REFUNDING'));
						break;
					default:
						break;
				}
			}
			if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
				$map["STR_TO_DATE(update_time,'%Y-%m-%d')"] = array('between', array($_GET['startDate'], $_GET['endDate']));
			}
			if (isset($_GET['keyword'])) {
				// $where['tid'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['title'] = array('like', '%' . $_GET['keyword'] . '%');
				// $where['buyer_id'] = array('like', '%' . $_GET['keyword'] . '%');
				// $where['buyer_nick'] = array('like', '%' . $_GET['keyword'] . '%');
				// $where['receiver_mobile'] = array('like', '%' . $_GET['keyword'] . '%');
				// $where['receiver_name'] = array('like', '%' . $_GET['keyword'] . '%');
				$where['_logic'] = 'or';
				$map['_complex'] = $where;
			}
			$Tradedetail = D('Tradedetail');
			$sort = "created desc";
			$filed = "tid,shop,case when status in ('TRADE_NO_CREATE_PAY', 'WAIT_BUYER_PAY', 'WAIT_PAY_RETURN') then '待付款' when status in ('WAIT_SELLER_SEND_GOODS') then '待发货' when status in ('WAIT_BUYER_CONFIRM_GOODS') then '已发货' when status in ('TRADE_BUYER_SIGNED') then '已完成' when status in ('TRADE_CLOSED') then '已关闭' else '其他' end as status,total_fee,payment,pay_time,weixin_user_id";
			$trades = $Tradedetail->getTradedetails($map, $sort, '', $filed);
			$Optometry = D('Optometry');

			$data = array();
			foreach ($trades as $key => $trade) {
				$omap['user_id'] = $trade['weixin_user_id'];
				$osort = 'time desc';
				$olimit = '1';
				$optometry = $Optometry->getOptometrys($omap, $osort, $olimit);

				$goods = $this->getGoods($trade['tid']);

				if ($goods) {
					foreach ($goods['orders'] as $gk => $good) {
						$data[$key]['title'] = $good['title'];

						if (mb_strpos($good['sku_properties_name'], '现片') !== false) {
							$data[$key]['style'] = '现片';
							$data[$key]['set'] = '';
						} else if (mb_strpos($good['sku_properties_name'], '种类:定制片') !== false) {
							$data[$key]['style'] = '定制片';
							$data[$key]['set'] = '';
						} else {
							if (mb_strpos($good['sku_properties_name'], '款式:') === 0) {
								$arrExp = explode('款式:', $good['sku_properties_name']);
								$arrGet = explode(';套餐:', $arrExp[1]);

								if (mb_strpos($arrGet[0], ';种类:') !== false) {
									$arrGet = explode(';种类:', $arrGet[0]);
								}

								$data[$key]['style'] = $arrGet[0];
								$data[$key]['set'] = $arrGet[1];
							} else if (mb_strpos($good['sku_properties_name'], '套餐:') === 0) {
								$arrExp = explode('套餐:', $good['sku_properties_name']);
								$arrGet = explode('款式:', $arrExp[1]);

								if (mb_strpos($arrGet[1], ';种类:') !== false) {
									$arrGet = explode(';种类:', $arrGet[1]);
								}

								$data[$key]['style'] = $arrGet[1];
								$data[$key]['set'] = $arrGet[0];
							} else {
								// 判断运动眼镜，比如眼镜
								$arrExp = explode('颜色:', $good['sku_properties_name']);
								$arrGet = explode(';种类:', $arrExp[1]);

								$data[$key]['style'] = $arrGet[0];
								$data[$key]['set'] = $arrGet[1];
							}
						}
					}
				}


				$data[$key]['shop'] = $trade['shop'];
				$data[$key]['status'] = $trade['status'];
				$data[$key]['total_fee'] = $trade['total_fee'];
				$data[$key]['payment'] = $trade['payment'];
				$data[$key]['pay_time'] = $trade['pay_time'];

				$data[$key]['optometry'] = '';
				if ($optometry) {
					$data[$key]['optometry'] = "右眼：" . $optometry[0]['rdegree'] . " / " . $optometry[0]['rastigmatism'] . " / " . $optometry[0]['raxial'] . " ;\n左眼：" . $optometry[0]['ldegree'] . " / " . $optometry[0]['lastigmatism'] . " / " . $optometry[0]['laxial'] . " ;" . "\n瞳距：" . ($optometry[0]['rpd'] + $optometry[0]['lpd']);
				}

				$data[$key]['tid'] = $trade['tid'];
			}

			$filename = "订单统计信息表";
			$headArr = array('商品', '款式', '套餐（种类）', '门店名称', '订单状态', '总金额', '实付金额', '付款时间', '验光数据', '订单号');
			$format = array(
				'align'  => array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1', 'I1'),
				'length' => array('A' => 50, 'B' => 18, 'C' => 28, 'D' => 15, 'H' => '24', 'I' => 60),
			);
			exportExcel($filename, $headArr, $data, 'sendOrder', $format);
		}
	}

    /**
     * 获取价格列表
     */
	private function getPriceList($tid) {
        $OrderDetail = M('order_detail');
        $StockNextModel = $StockNext = new \Home\Model\StocknextModel();
        $arr = $OrderDetail->where(array('tid'=>$tid))->field('sku_id')->select();

//        outputDebugLog($tid,8);
//        outputDebugLog($arr,8);
        $count = 0;
        $has_KGT = 0;
        $goods_tmp = array();
        foreach ($arr as $value) {
            $sku_id = $value['sku_id'];

            if (substr($sku_id, 0, 1) === 'K'
                || substr($sku_id, 0, 1) === 'G'
                || substr($sku_id, 0, 1) === 'T') {
                $has_KGT = 1;
            }
        }
        foreach ($arr as $value) {
            $sku_id = $value['sku_id'];

            if($has_KGT) {
                if (substr($sku_id, 0, 1) === 'K'
                    || substr($sku_id, 0, 1) === 'G'
                    || substr($sku_id, 0, 1) === 'T') {
                    $goods_tmp[0] = $sku_id;

                } else {
                    ++ $count;
                    $goods_tmp[$count] = $sku_id;
                }
            }else {
                $goods_tmp[$count] = $sku_id;
                ++ $count;
            }
        }

//        outputDebugLog($goods_tmp, 8);
        $price_arr = array();

        for ($i = 0, $len = count($goods_tmp); $i < $len; ++$i) {
            if(substr($goods_tmp[$i], 0, 1) === 'U') {
                $price_arr[0] += $this->getPriceBySkuId($goods_tmp[$i]);
                continue;
            }
            $price_arr[] = $this->getPriceBySkuId($goods_tmp[$i]);
        }

        return $price_arr;
    }

    /**
     * 获取商品单价
     */
    private function getPriceBySkuId($sku_id) {
        //根据selling_id获取需要实例化的表名
        $StockNextModel = $StockNext = new \Home\Model\StocknextModel();
        $goods_info = $StockNextModel->getInfoBySkuId($sku_id);
        return $goods_info['price'];
    }

    /**
     * 获取套餐
     */
    private function getComboPrice($tid) {
        $TradeDetail = M('tradedetail');
        $ComboPrice = $TradeDetail->where(array('tid' => $tid))->field('total_fee,attach_fee')->find();
//        $total_combo = $ComboPrice['total_fee'] + $ComboPrice['total_fee'];
        return number_format((float)array_sum($ComboPrice), 2, '.', '');
    }

	public function exportDeliveryOrder() {
        $tid = I('get.ordernumber');

		// 接收表单数据，并整理表格数据
		$data['A2'] = 'uubd发货单';

		$data['A3'] = '收货人姓名：' . I('get.username');
		$data['C3'] = '电话：' . I('get.phonenumber');
		$data['E3'] = '下单日期';
		$data['F3'] = date("Y.m.d", strtotime(I('get.deliverydate')));
		$data['I3'] = '订单编号：' . $tid;

		$data['A4'] = '发货地址：' . I('get.address');
		$data['I4'] = '买家昵称：' . I('get.buyernick');

		$data['A5'] = '序号';
		$data['B5'] = '商品名称';
		$data['C5'] = '型号';
		$data['F5'] = '数量';
		$data['I5'] = '单价';
		$data['J5'] = '套餐价';

		// 处理商品
		// TODO:
		// 1. 判断商品是否为单品（目前已完成简单判断——20160630）
		// 2. 确定商品具体数量（情况有几类，一为单镜框，二可能为太阳镜）来确定行数
		$goodsTitle = trim(I('get.goodstitle'));
		$pos = mb_strrpos($goodsTitle, ' ');
		$goodsName = ($pos !== false) ? trim(mb_substr($goodsTitle, $pos + 1)) : $goodsTitle;

		$goodsProperty = trim(I('get.goodsproperty'));

		$posDesign = mb_strpos($goodsProperty, ':');
		$pos = mb_strpos($goodsProperty, ';');
		$posClassify = mb_strrpos($goodsProperty, ':');

		$design = ($pos !== false && $posDesign !== false) ? mb_substr($goodsProperty, $posDesign + 1, $pos - $posDesign - 1) : $goodsProperty;
		$classify = ($posClassify !== false) ? mb_substr($goodsProperty, $posClassify + 1) : $goodsProperty;

		$goodsPrice = I('get.goodsprice');
		$totalPrice = I('get.totalprice');
		$number = I('get.goodsnumber');

        $single_price = $this->getPriceList($tid);
        $comble_price = $this->getComboPrice($tid);
		// 单价确定
		// 1. 镜框有 66、99、166 元这三种价格，将 99、166 价格的镜框号写到数组中
		// 2. 若镜框在相应数组中，则将价格调为相应的值，否则设为 66 元
		// TODO:
		// 将商品类型存到数据库中，后台实现镜框分类的录入与修改调整
		$ninety_nine = array('7025', '5573', '2817', '2814', '5618', '2824', '2875', '1550', '2807', '2868', '2835', '2816');
		$hundred_sixty_six = array('6623', '6624', '6625', '6626', '6629', '6630', '6631', '6632', '6634', '6635', '6636', '6637', '6638', '6639', '66001','66002','66003','66004','66005','66006','66007','66008','66009','66010','66011','66012','66013',
		);

		if (in_array($goodsName, $hundred_sixty_six)) {
			$unitPrice = '166.00';
		} else if (in_array($goodsName, $ninety_nine)) {
			$unitPrice = '99.00';
		} else {
			$unitPrice = '66.00';
		}

		// 添加产品型号
		$goodsType = '';

		$serialNumber = getSerialNumber($goodsTitle);

		if (! empty($serialNumber)) {
			$goodsName = $serialNumber;
			$Type = D('Type');

			$typeStr = $Type->getGoodsType($serialNumber);
			$goodsType = empty($typeStr) ? '' : ('（' . $typeStr . '）');
		}

		if (mb_strpos($goodsProperty, '镜腿款式') !== false) {
			$data['A6'] = '1';
			$data['B6'] = $goodsName;

			$classify = handleClassify($classify);
			$data['C6'] = $goodsProperty;
			$data['F6'] = $number;
			$data['I6'] = $single_price[0];
			$data['J6'] = $data['I6'];

			// 这是单品内容，表格置空，只是为了控制表格的格式
			$data['A7'] = '2';
			$data['B7'] = '';
			$data['F7'] = '';
			$data['I7'] = '';
		} elseif (! empty($classify)
			&& mb_strpos($classify, '单品') === false
			&& mb_strpos($classify, '现片') === false
			&& mb_strpos($goodsTitle, '太阳镜') === false
			&& mb_strpos($goodsProperty, '无度数') === false) {
			$data['A6'] = '1';
			$data['B6'] = $goodsName;
			$data['C6'] = $design . $goodsType;
			$data['F6'] = $number;

			$data['I6'] = $single_price[0];

			$data['A7'] = '2';

			$str = trim($classify);
			$arr = explode(" ", $str);
			$data['C7'] = $arr[0];
			$data['F7'] = $number;
			$data['I7'] = $single_price[1];

			$data['J6'] = $comble_price;
		} else if (mb_strpos($classify, '现片') === false
			&& mb_strpos($goodsProperty, '无度数') !== false) {
			$data['A6'] = '1';
			$data['B6'] = $goodsName;
			$data['C6'] = $design . ' ' . $classify . $goodsType;
			$data['F6'] = $number;
			$data['I6'] = $single_price[0];
			$data['J6'] = $data['I6'];

			// 这是单品内容，表格置空，只是为了控制表格的格式
			$data['A7'] = '2';
			$data['B7'] = '';
			$data['F7'] = '';
			$data['I7'] = '';
		} else if (mb_strpos($classify, '现片') !== false) {
			$data['A6'] = '1';
			$startPos = strpos($goodsName, '-');
			$str1 = ($startPos !== false) ? mb_substr($goodsName, $startPos + 1, 4) : '';
			$endPos = mb_strrpos($goodsName, 'ASP');
			$str2 = ($endPos !== false) ? mb_substr($goodsName, $endPos + 3) : '明镜镜片';
			$name = $str1 . $str2;
			$data['B6'] = $name;
			$data['C6'] = '';
			$data['F6'] = $number;
			$data['I6'] = $single_price[0]; // number_format((float)$goodsPrice, 2, '.', '');
			// 目前优惠券为 100 元
			// TODO：
			// 实现自动调整价格
			$data['J6'] = $totalPrice + 100.00;

			// 这是单品内容，表格置空，只是为了控制表格的格式
			$data['A7'] = '2';
			$data['B7'] = '';
			$data['F7'] = '';
			$data['I7'] = '';
		} else {
			$data['A6'] = '1';
			$data['B6'] = $goodsName;

            $classify = handleClassify($classify);
			$data['C6'] = "$design $classify" . $goodsType;
			$data['F6'] = $number;
			$data['I6'] = $single_price[0]; // number_format((float)$goodsPrice, 2, '.', '');
			$data['J6'] = $data['I6'];

			// 这是单品内容，表格置空，只是为了控制表格的格式
			$data['A7'] = '2';
			$data['B7'] = '';
			$data['F7'] = '';
			$data['I7'] = '';
		}

		$data['A8'] = '3';
		$data['B8'] = '';
		$data['I8'] = '';

		$data['A9'] = '';
		$data['B9'] = '度数（球镜SPH）';
		$data['C9'] = '散光（柱镜CYL）';
		$data['D9'] = '轴位（AXIS）';

        $remark = I('get.remark');
        $data['E9'] = '服务热线：400-877-8853';
        
		$data['I9'] = '实付';
		$data['J9'] = ($totalPrice == 0) ? '优惠全额兑换' : $totalPrice;

		if (mb_strpos($goodsProperty, '无度数') === false
			&& mb_strpos($classify, '镜架单品') === false) {
			// 获取验光数据
			$optometry = D('Home/Optometry');
			if(I('get.ordernumber') && strlen(I('get.ordernumber')) <= 14){
				$Tradedetail = M('Tradedetail');
				$trade_info = $Tradedetail->where(array('tid'=>I('get.ordernumber')))->find();
				$map['o_id'] = $trade_info['o_id'];
			}else{
				$map['user_id'] = I('get.buyerid');
			}
            //当订单的验光数据为0时，自动关联最新数据 2016-11-29
            if((int)$map['o_id']===0){
                unset($map['o_id']);
                $map['user_id'] = I('get.buyerid');
            }
			// $map['user_id'] = 844729072;
			$sort = 'time desc';
			$limit = '1';

			$optometrys = $optometry->getOptometrys($map, $sort, $limit)[0];
			$data['A10'] = '右眼（R）';

			$data['B10'] = stringAddSign($optometrys['rdegree']); // 右眼度数
			$data['C10'] = stringAddSign($optometrys['rastigmatism']); // 右眼散光
			$data['D10'] = $optometrys['raxial']; // 右眼轴位

			$buyer_message = I('get.buyermessage');
			$data['E10'] = '买家留言：' . (empty($buyer_message) ? '无' : $buyer_message);

			$data['A11'] = '左眼（L）';
			$data['B11'] = stringAddSign($optometrys['ldegree']); // 左眼度数
			$data['C11'] = stringAddSign($optometrys['lastigmatism']); // 左眼散光
			$data['D11'] = $optometrys['laxial']; // 左眼轴位
			$data['E11'] = '卖家备注：' . (empty($remark) ? '无' : $remark);

			$data['A12'] = '瞳距（右左）';
			$data['C12'] = "R: {$optometrys['rpd']}mm";
			$data['D12'] = "L: {$optometrys['lpd']}mm";
			$data['E12'] = 'uubd-做年轻人最信赖的眼镜品牌';
		} else {
			$data['A10'] = '右眼（R）';
			$data['B10'] = ''; // 右眼度数
			$data['C10'] = ''; // 右眼散光
			$data['D10'] = ''; // 右眼轴位

            $buyer_message = I('get.buyermessage');
            $data['E10'] = '买家留言：' . (empty($buyer_message) ? '无' : $buyer_message);

			$data['A11'] = '左眼（L）';
			$data['B11'] = ''; // 左眼度数
			$data['C11'] = ''; // 左眼散光
			$data['D11'] = ''; // 左眼轴位
			$data['E11'] = '卖家备注：' . (empty($remark) ? '无' : $remark);

			$data['A12'] = '瞳距（右左）';
			$data['C12'] = '';
			$data['D12'] = '';
			$data['E12'] = 'uubd-做年轻人最信赖的眼镜品牌';
		}

		// 根据用户留言或买家备注来分析用途是否为近用还是远用
		// 用途默认为 远用，除非备注或留言中有标明 近用
		$usage = '远用';
		if (strpos($data['E10'], '近用') !== false ||
			strpos($data['E11'], '近用') !== false
		) {
			$usage = '近用';
		}
		$data['A13'] = '用途：' . $usage;

		// 加工编号
		$process_id = $this->getProcessId(I('get.ordernumber'));
		$data['B13'] = '加工编号：' . $process_id;

		$data['C13'] = '加工师';
		$data['D13'] = '张少崧';
		$data['E13'] = '检验员：沈雪敏';
		$data['G13'] = '检验结果：';
		$data['J13'] = '执行标准：GB13511.1-2011';

		$fileName = '发货单 ' . date("Y-m-d His", strtotime(I('get.deliverydate'))) . ' ' . I('get.username') . '.xls';

		// 过滤乱七八糟的文字，保证　Excel 正常显示
		$data['I4'] = preg_replace('/[\x{10000}-\x{fffff}]+/u', '', $data['I4']);

		exportDeliveryExcel($fileName, $data);
	}

	/**
	 * 获取加工编号
	 * 加工编号计算
	 * 加工编号规则：
	 * 月(2位)+日(2位)+当天序数(3位)，如 9 月 22 日打的第 20 个单为： 0922020
	 *
	 * @param $tid
	 * @return mixed
	 */
	private function getProcessId($tid) {
		$process_model = M('trade_process');

		$process_map = array(
			'tid' => $tid,
		);

		$process_field = 'process_id';

		$process_result = $process_model->where($process_map)->field($process_field)->find();

		if ($process_result) {
			$process_id = $process_result['process_id'];
		} else {
			$trade_model = M('tradedetail');

			$trade_map = array(
				'tid' => $tid,
			);

			$trade_field = 'tid,created';

			$trade_result = $trade_model->where($trade_map)->field($trade_field)->find();

			if ($trade_result) {
				$created = $trade_result['created'];

				$date_num = date('md', strtotime($created));
				$ymd = intval(date('Ymd', strtotime($created)));

				$ymd_map = array(
					'ymd' => $ymd,
				);
				$max_sequence = intval($process_model->where($ymd_map)->max('sequence'));

				$sequence = $max_sequence + 1;

				$sequence_pad = str_pad($sequence, 3, '0', STR_PAD_LEFT);

				$process_id = $date_num . $sequence_pad;

				$data = array(
					'tid' => $tid,
					'created' => $created,
					'ymd' => $ymd,
					'sequence' => $sequence,
					'process_id' => $process_id,
				);

				$process_model->add($data);
			} else {
				return '66mingjing';
			}
		}

		return $process_id;
	}

	/**
	 * 获取加工编号，若未打单的则编号为空
	 * @param $tid
	 * @return string
	 */
	private function getFinishedProcessId($tid) {
		$process_model = M('trade_process');

		$process_map = array(
			'tid' => $tid,
		);

		$process_field = 'process_id';

		$process_result = $process_model->where($process_map)->field($process_field)->find();

		if ($process_result) {
			$process_id = $process_result['process_id'];
		} else {
			$process_id = '';
		}

		return $process_id;
	}

	public function getGoods($tid = '') {
		$field = 'orders';
		Vendor('Youzan.Youzan');
		$youzan = \Youzan::getInstance();
		$params = array('fields' => $field, 'tid' => $tid);
		$order = $youzan->getTrade($params);
		return $order['response']['trade'];
	}

	public function markOrder() {
		if (IS_POST) {
			$mmap['tid'] = $_POST['tid'];
            $func = $_POST['func'];
//            outputDebugLog($func);
			$mdata['mark'] = $func;
//            outputDebugLog($mdata);
			$Tradedetail = D('Tradedetail');
			$result = $Tradedetail->updateTradedetail($mmap, $mdata); //先更新用户表数据
//            outputDebugLog($result,8);
			$this->ajaxReturn(array('result' => $result));
		}
	}


	/**
	 * 订单发货
	 */
	public function orderDelivery($tid = false,$express_id = false,$sendType = false){
		if($tid === false){
			$tid = I('post.tid');
		}
		if($express_id === false){
			$express_id = I('post.express_id');
		}
		if($sendType === false){
			$sendType = I('post.send_type');
		}

		//保存批量发货失败的订单号session
		if(session('fail_tid')){
			$fail_tid = session('fail_tid');
		}else{
			$fail_tid = array();

		}

		//1.将订单状态改为已发货
		$Tradedetail = M('Tradedetail');
		$where = array('tid'=>$tid);
		$trade_data['status'] = 'WAIT_BUYER_CONFIRM_GOODS';
		$trade_data['consign_time'] = date('Y-m-d H:i:s');
		$trade_data['update_time'] = date('Y-m-d H:i:s');

		//根据订单编号获取该订单的数据
		$tradeInfo = $Tradedetail->where($where)->find();

		//如果订单状态不为待发货
		if($tradeInfo){
			if($tradeInfo['status'] != 'WAIT_SELLER_SEND_GOODS'){
				$str = '订单：'.$tid.' 失败，非待发货状态！';
				array_push($fail_tid,$str);
			}
		}else{
			$str = '订单：'.$tid.' 失败，订单号错误或该订单不存在！';
			array_push($fail_tid,$str);
		}

		// 判断是自提还是发货
		if($sendType=='express'){
			$express_name = '申通快递';
			$url = 'http://m.kuaidi100.com/index_all.html?type=shentongkuaidi&postid='.$express_id;
			$trade_data['shipping_type'] = 'express';
			$trade_data['express_id'] = $express_id;
		}else{
			$express_name = '66专业跑腿小哥';
			$url = C('SITE_URL').__APP__.'/Weixin/Product/myOrder';
			$express_id = '666';
			$trade_data['shipping_type'] = 'fetch';
		}

		$res = $Tradedetail->where($where)->save($trade_data);
		if($res){
			//2.调用微信模板消息接口告诉用户已发货
			$mess_data = array(
				'open_id' => $tradeInfo['weixin_openid'],
				'temp_id'=>'gNPzk4JfOVi5SYuj1onDPddTD3SkWMwxCA7V7zwFm0E',
//			'temp_id'=>'cpsh3FUhYKg9DPn9DsziKcZ1I5_rAdX7To4Je8twav8', // 测试专用
				'url'=>$url,
				'goods_name'=>$tradeInfo['title'],
				'express_name'=>$express_name,
				'express_id'=>$express_id,
				'address'=>$tradeInfo['receiver_state'].$tradeInfo['receiver_city'].$tradeInfo['receiver_district'].$tradeInfo['receiver_address'],
			);

			$this->OrderSuccessSendWxTempMessage($mess_data);
		}else{
			if(!in_array('订单：'.$tid.' 失败，非待发货状态！',$fail_tid) && !in_array('订单：'.$tid.' 失败，订单号错误或该订单不存在！',$fail_tid)){
				//dump($fail_tid);array_push($fail_tid,$str);
				$str = '订单：'.$tid.' 发货失败！';
				array_push($fail_tid,$str);
			}
		}

		session('fail_tid',$fail_tid);

		$return_msg = array(
			'sign'=>1,
			'msg'=>'success',
		);
	}

	/**
	 * 订单退款
	 */
	public function orderExitPrice(){
		$tid = I('post.tid');
		$fb_price = I('post.fb_price');
		$Tradedetail = M('Tradedetail');
		$where = array('tid'=>$tid);
		$tradeInfo = $Tradedetail->where($where)->find();

		//1.调用微信支付接口退款
		// todo 待调用微信企业支付接口退款
		$pay_status = TRUE;

		if($pay_status === false){
			$return_msg = array(
				'sign'=>-1,
				'msg'=>'退款失败！',
			);
			exit(json_encode($return_msg));
		}

		if($fb_price==$tradeInfo['payment']){
			$fb_type = 600;
			$fb_status = 'FULL_REFUNDED';   // 全额退款
		}else{
			$fb_type = 601;
			$fb_status = 'PARTIAL_REFUNDED';   // 部分退款
		}
		//2.将订单改为退款成功
		$update_data = array(
			'feedback'=>$fb_type,
			'refund_state'=>$fb_status,
			'state_str'=>'退款成功',
			'refunded_fee'=>$fb_price,
			'update_time'=>date('Y-m-d H:i:s'),
			'status'=>'TRADE_CLOSED',
		);
		$Tradedetail->where($where)->save($update_data);

		
		// 推送短信，告知用户退款成功
//        $tradeInfo['weixin_openid'];
//		$send_control = new sendMessage();
//		$res = $send_control->responseText('已经退款！！');
		//3.调用模板消息接口告诉用户退款成功-------todo
		$msg_data = array(
			'open_id'=>$tradeInfo['weixin_openid'],
			'temp_id'=>'LsXiB4G4fJ_5IVQtTxkQA2aXI-tDk2bIpUsYw2drnrQ',
			'url'=>C('SITE_URL').__APP__.'/Weixin/Product/myOrder',
			'tid'=>$tradeInfo['tid'],
			'goods_name'=>$tradeInfo['title'],
			'exit_msg'=>'用户申请退款',
			'exit_price'=>$fb_price,
		);
		$this->ExitPriceSendWxTempMessage($msg_data);

		$return_msg = array(
			'sign'=>1,
			'msg'=>'success',
		);
		exit(json_encode($return_msg));

	}

	

	// 下单成功 微信模板消息接口
	public function OrderSuccessSendWxTempMessage($result){
		$send_control = new sendMessage();
		$openid = $result['open_id'];

		// 发送内容数据结构
		$data = array(
			'touser' => $openid,
//			'template_id' => 'frZB8tNBxzAyrGoe7Cfu7sjrbrVHbjyRD2w66G6pGIo',
			'template_id' => $result['temp_id'],
			'url' => $result['url'],
			'data' => array(
				'keyword1' => array(
					'value' => $result['goods_name'],
					'color' => '#173177',
				),
				'keyword2' => array(
					'value' => $result['express_name'],
					'color' => '#173177',
				),
				'keyword3' => array(
					'value' => $result['express_id'],
					'color' => '#173177',
				),
				'keyword4' => array(
					'value' => $result['address'],
					'color' => '#173177',
				),
			)
		);

		$send_res = $send_control->sendTemplateMessage($data);
	}

	// 退款成功 微信模板消息接口
	public function ExitPriceSendWxTempMessage($result){
		$send_control = new sendMessage();
		$openid = $result['open_id'];

		/*'keyword1'  订单号
		'keyword2'  商品名称
		‘keyword3’  退款原因
		‘keyword4’  退款金额
		*/
		// 发送内容数据结构
		$data = array(
			'touser' => $openid,
//			'template_id' => 'LsXiB4G4fJ_5IVQtTxkQA2aXI-tDk2bIpUsYw2drnrQ',
			'template_id' => $result['temp_id'],
			'url' => $result['url'],
			'data' => array(
				'keyword1' => array(
					'value' => $result['tid'],
					'color' => '#173177',
				),
				'keyword2' => array(
					'value' => $result['goods_name'],
					'color' => '#173177',
				),
				'keyword3' => array(
					'value' => $result['exit_msg'],
					'color' => '#173177',
				),
				'keyword4' => array(
					'value' => $result['exit_price'],
					'color' => '#173177',
				),
			)
		);

		$send_res = $send_control->sendTemplateMessage($data);
	}


	/**
	 * 添加卖家备注
	 */
	public function sellerRemark(){
		$tid = I('post.tid');
		$content = I('post.remark_content');
        $btn_func = I('post.remark_func');
		$Tradedetail = M('Tradedetail');
		outputDebugLog($tid,8);

		$map_tid = array('tid'=>$tid);
        outputDebugLog($btn_func,8);
        if($btn_func) {
            $time = date('Y-m-d H:i:s');
            $update['other_remark'] = I('post.name','','htmlspecialchars') . ' || 操作者：'.session('userinfo')['usernickname'];
            $update['status'] = 'WAIT_SELLER_SEND_GOODS';
            $update['created'] = $time;
            $update['pay_type'] = 'others';
            $update['is_redpack'] = 1;
            $update['pay_time'] = $time;
            $update['outer_tid'] = '4006666666666666666666666666';
            $update['update_time'] = $time;

            //** 判断订单购买商品类型 */

            /** 扣减库存 */
            //获取下单临时数据
            $TradeTempModel = M('TradeSubmitTemp');
            $trade_temp_info = $TradeTempModel->where(array('tid'=>$tid))->find();
            outputDebugLog($trade_temp_info,8);

            /** 判断是否隐形订单，$trade_temp_info为null则是*/
            if(!$trade_temp_info) {
				$update['status'] = 'TRADE_BUYER_SIGNED';

                $OrderDetailModel = M('OrderDetail');
                $order_detail_info = $OrderDetailModel->where(array('tid'=>$tid))->select();
                outputDebugLog($order_detail_info);

                /** 扣减主商品库存 */
                $StockNextModel = $StockNext = new \Home\Model\StocknextModel();
                foreach ($order_detail_info as $v) {
//                    outputDebugLog($v['sku_id'],8);
                    $res = $StockNextModel->haircutAmount($v['sku_id'],$v['warehouse'],$v['num']);
                    outputDebugLog('main_goods stock change',8);
                    outputDebugLog($res,8);
                    if($res['sign']==-1){
                        // 扣减主商品库存失败
                        // 日志记录错误数据
                        $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'main_goods stock change fail';
                        $log_data .= PHP_EOL.var_export($v,true);
                        wxlogg('wxpay_success_order_update_log', __METHOD__, $log_data);
                    }else{
                        // 扣减主商品库存成功
                    }

                }

            } else {
                /** 获取商品信息 */
                $StockModel = D('Weixin/Stock');
                $goods_info = $StockModel->getGoodsInfo($trade_temp_info);
                if($goods_info['sign']==-1){
                    // 获取商品信息失败
                    // 日志记录错误数据
                    $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'get goods_info fail';
                    $log_data .= PHP_EOL.var_export($goods_info,true);
                    wxlogg('wxpay_success_order_update_log', __METHOD__, $log_data);
                }else{
                    // 获取商品信息成功
                }
                outputDebugLog($goods_info,8);
                /** 扣减主商品库存 */
                $StockNextModel = $StockNext = new \Home\Model\StocknextModel();
                $res = $StockNextModel->haircutAmount($goods_info['main_goods']['sku_id'],$goods_info['main_goods']['warehouse'],$trade_temp_info['num']);
                outputDebugLog('main_goods stock change',8);
                outputDebugLog($res,8);
                if($res['sign']==-1){
                    // 扣减主商品库存失败
                    // 日志记录错误数据
                    $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'main_goods stock change fail';
                    $log_data .= PHP_EOL.var_export($goods_info['main_goods'],true);
                    wxlogg('wxpay_success_order_update_log', __METHOD__, $log_data);
                }else{
                    // 扣减主商品库存成功
                }
                /** 扣减镜腿商品库存 */
                if(!$goods_info['leg_goods']){
                    //非UI商品
                }else{
                    $res = $StockNextModel->haircutAmount($goods_info['leg_goods']['sku_id'],$goods_info['leg_goods']['warehouse'],$trade_temp_info['num']);
                    outputDebugLog('leg_goods stock change',8);
                    outputDebugLog($res,8);
                    if($res['sign']==-1){
                        // 扣减套餐商品库存失败
                        // 日志记录错误数据
                        $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'legs_goods stock change fail';
                        $log_data .= PHP_EOL.var_export($goods_info['leg_goods'],true);
                        wxlogg('wxpay_success_order_update_log', __METHOD__, $log_data);
                    }else{
                        // 扣减镜腿商品库存成功
                    }
                }
                /** 扣减套餐商品库存 */
                if(!$goods_info['combo_info']){
                    //无套餐商品
                }else{
                    $res = $StockNextModel->haircutAmount($goods_info['combo_goods']['sku_id'],$goods_info['combo_goods']['warehouse'],$trade_temp_info['num']);
                    outputDebugLog('combo_goods stock change',8);
                    outputDebugLog($res,8);
                    if($res['sign']==-1){
                        // 扣减套餐商品库存失败
                        // 日志记录错误数据
                        $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'combo_goods stock change fail';
                        $log_data .= PHP_EOL.var_export($goods_info['combo_goods'],true);
                        wxlogg('wxpay_success_order_update_log', __METHOD__, $log_data);
                    }else{
                        // 扣减套餐商品库存成功
                    }
                }

                outputDebugLog('coupon log:',8);
                /** 添加优惠券使用记录 2016-11-30起更改为减优惠券数量*/
                $time = date('Y-m-d H:i:s');
                //1.往customer_coupon表插入一条用户刚使用的优惠券记录
                outputDebugLog('ready in coupon',8);
                if($trade_temp_info['coupon_id']!='' && $trade_temp_info['coupon_id'] !== '0'){
                    $CouponModel = D('Weixin/Coupon');
                    $CustomerCouponNew = M('CustomerCouponNew');
                    $coupon_info = $CouponModel->getCouponInfo($trade_temp_info['coupon_id']);
                    $where = array(
                        'weixin_openid'=>$trade_temp_info['weixin_openid'],
                        'coupon_id'=>$coupon_info['coupon_id'],
                        'coupon_num'=>array('GT',0),
                    );
                    $userCouponInfo = $CustomerCouponNew->where($where)->find();
                    outputDebugLog('userCouponInfo',8);
                    outputDebugLog($userCouponInfo,8);
                    if($userCouponInfo){
                        $sql = " update `customer_coupon_new` set `coupon_num` = `coupon_num` - 1 where";
                        $sql.= " weixin_openid = '{$trade_temp_info['weixin_openid']}'";
                        $sql.= " and coupon_id = '{$coupon_info['coupon_id']}'";
                        outputDebugLog($sql,8);
                        $add_coupon_res = $CustomerCouponNew->execute($sql);

                    }else{
                        //将有赞优惠券的使用记录添加上
                        $coupon_id = 0;
                        if($coupon_info['coupon_id'] == 'c03'){
                            $coupon_id = '1518540';
                        }else if($coupon_info['coupon_id'] == 'c02'){
                            $coupon_id = '1356084';
                        }
                        $save_data = array(
                            'use_time'=>date('Y-m-d H:i:s'),
                        );
                        $where = array(
                            'coupon_id'=>$coupon_id,
                            'weixin_openid'=>$trade_temp_info['weixin_openid'],
                        );
                        $add_coupon_res = $CustomerCouponNew->where($where)->save($save_data);
                    }

                    if(!$add_coupon_res){
                        // 添加优惠券使用记录失败
                        // 日志记录错误数据
                        $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'add customer_coupon log fail';
                        $log_data .= PHP_EOL.var_export($sql,true);
                        wxlogg('wxpay_success_order_update_log', __METHOD__, $log_data);
                    }else{
                        // 添加优惠券使用记录成功
                    }
                }


                /** 注销用户推广人优惠标记 */
                outputDebugLog($trade_temp_info['partner_code_id'],8);
                if($trade_temp_info['partner_code_id']!=0){
                    outputDebugLog('in cancel partner',8);
                    $CustomerModel = M('Customer');
                    $c_data['partner_code_id'] = 0;
                    outputDebugLog($c_data,8);
                    $update_res = $CustomerModel->where(array('weixin_openid'=>$trade_temp_info['weixin_openid']))->save($c_data);
                    outputDebugLog($update_res,8);
                    if(!$update_res){
                        // 注销标记失败
                        // 日志记录错误数据
                        $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'cancel partner_code fail';
                        $log_data .= PHP_EOL.var_export($trade_temp_info['weixin_openid'],true);
                        wxlogg('wxpay_success_order_update_log', __METHOD__, $log_data);
                    }else{
                        // 注销标记成功
                    }
                }else{
                    // 没有使用合伙人推广优惠
                }

                /** 给用户推送微信模板消息 */ //可进一步优化 todo
                $TradeModel = D('Weixin/Tradedetail');
                $trade_info = $TradeModel->getTradeInfoById($trade_temp_info['tid']);
                $trade_temp = explode('@',$trade_info['title']);
                outputDebugLog($trade_temp,8);
                $title = '型号：'.$goods_info['main_goods']['selling_id'].'-'.$goods_info['main_goods']['style'];
                $title .= $goods_info['leg_goods']?'-镜腿:'.$goods_info['leg_goods']['style']:'';
                $mess_data = array(
                    'temp_id'=>'sAngqEVfddFwnDw2FZEeIAlKAzR7ixrQdn3jCQb69Xs',
//						'temp_id'=>'sOdmYzbyOFr_Gi7bwEqqYd8r5ke76C_NUnPwa9kJv8I', // 测试专用
                    'open_id'=>$trade_temp_info['weixin_openid'],
                    'msg'=>'下单成功！',
                    'url'=>C('SITE_URL').__APP__.'/Weixin/Product/myOrder',
                    'goods_name'=>$title,
                    'lens_name'=>$trade_temp[2]?$trade_temp[2]:'无',
                    'goods_price'=>$trade_info['payment'],
                    'pay_time'=>$time,
                    'remark' => 'uubd，做年轻人最信赖的眼镜品牌！',
                );
                outputDebugLog($mess_data,8);
                $WeixinProduct = A('Weixin/Product');
                $res = $WeixinProduct->sendWxTempMessage($mess_data);
                outputDebugLog($res,8);

//            5.若有合伙人标记则推送铁城消息给校园合伙人 todo
                $c_model = D('Weixin/customer');
                $c_info = $c_model->getCustomerInfoByOpenId($trade_temp_info['weixin_openid']);
                $s_model = D('Weixin/partner');
                $p_info = $s_model->where(array('p_name'=>$c_info['tags']))->find();
                if($p_info['promotion_type']==4){
                    $primary_mess_data = array(
                        'temp_id'=>'dBtff5H7RTSd6UVVE0hAlVASrH8ggT-_21CpRinn5l8',
                        'open_id'=>$p_info['weixin_openid'],
                        'msg'=>'童鞋！牛B呀！有朋友下单啦！！',
                        'url'=>C('SITE_URL').__APP__.'/Weixin/Partner/primary',
                        'tid'=>substr($trade_info['tid'],0,7).'******',
                        'user'=>$trade_info['receiver_name'],
                        'income_price'=>8.8.'元',
                        'pay_time'=>$time,
                        'remark' => '红包拿好，再接再厉哈！感恩您对66的助攻！',
                    );
                    $WeixinProduct->sendWxTempMessageP($primary_mess_data);
                }

                // 本地数据同步有赞
//            $res = $StockNext->haircutYouzan($trade_temp_info['attr_id'],$goods_info['main_goods']['warehouse'],$trade_temp_info['num']);
//            outputDebugLog($res,8);
//            if($res['sign']==-1){
//                wxlogg('update_youzan_quantity_log', __METHOD__, '***(1)update youzan fail****'.PHP_EOL.'sku_id：'.$trade_temp_info['attr_id'].PHP_EOL.'s_name：'.$trade_temp_info['main_goods']['warehouse'].PHP_EOL.'num：'.$trade_temp_info['num']);
//            }

                $YEActivity = M('ye_activity');
                $insert_data = array(
                    'weixin_openid'=>$trade_temp_info['weixin_openid'],
                    'tid'=>$tid,
                    'number'=>1,
                    'datetime'=>date('Y-m-d H:i:s'),
                    'is_share'=>0,
                );
                $YEActivity->add($insert_data);

            }

        } else {
            $update['trade_memo'] = $content;
        }
        outputDebugLog($update,8);
		$tradeInfo = $Tradedetail->where($map_tid)->setField($update);
		if($tradeInfo){
            $return_msg = array(
                'sign'=>1,
                'msg'=>'success',
            );	
		}else{
			$return_msg = array(
                'sign'=>-1,
                'msg'=>'fail',
            );
		}
		exit(json_encode($return_msg));	
	}
	
	public function rightProtectionClose(){
        $tid = I('post.tid');
		$Tradedetail = M('Tradedetail');
		$where = array('tid'=>$tid);
		$update['feedback'] = 602;
		$update['refund_status'] = 'SELLER_CLOSE';
		$tradeInfo = $Tradedetail->where($where)->setField($update);
		if($tradeInfo){
            $return_msg = array(
                'sign'=>1,
                'msg'=>'success',
            );	
		}else{
			$return_msg = array(
                'sign'=>-1,
                'msg'=>'fail',
            );
		}
		exit(json_encode($return_msg));		
	}

	/**
	 * [批量发货Execl上传]
	 */
	public function uploadSendExcel(){
		$upload = new \Think\Upload();// 实例化上传类
		$upload->maxSize   =     3145728 ;// 设置附件上传大小
		$upload->exts      =     array('xlsx', 'xls');// 设置附件上传类型
		$upload->savePath  =      './sendExcel/';// 设置附件上传目录
		// 上传单个文件
		$info   =   $upload->uploadOne($_FILES['tids']);
		if(!$info) {// 上传错误提示错误信息
			$this->error($upload->getError());
		}else{// 上传成功 获取上传文件信息
			$file = $info['savepath'].$info['savename'];
			//调用批量发货
			$this->batchSendGoods($file);
		}
	}

	/**
	 * 【批量发货】
	 * @param string $filepath
	 */
	public function batchSendGoods($filepath = ''){
		//测试文件
//		$filePath = './Uploads/123456.xlsx';
		$filePath ='./Uploads/'.$filepath;

		Vendor('PHPExcel.PHPExcel');
		$PHPExcel = new \PHPExcel();

		/**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
		$PHPReader = new \PHPExcel_Reader_Excel2007();
		if(!$PHPReader->canRead($filePath)){
			$PHPReader = new \PHPExcel_Reader_Excel5();
			if(!$PHPReader->canRead($filePath)){
				echo 'no Excel';
				return ;
			}
		}

		$PHPExcel = $PHPReader->load($filePath);
		/**读取excel文件中的第一个工作表*/
		$currentSheet = $PHPExcel->getSheet(0);
		/**取得最大的列号*/
		$allColumn = $currentSheet->getHighestColumn();
		/**取得一共有多少行*/
		$allRow = $currentSheet->getHighestRow();
		/**从第二行开始输出，因为excel表中第一行为列名*/

		$data = array();
		for($currentRow = 2;$currentRow <= $allRow;$currentRow++){
			/**从第A列开始输出*/
			for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){
				$val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/

				if($currentColumn == 'A'){
					//保存A列的订单号
					$data[$currentColumn][] = $val;
				}else if($currentColumn == 'B'){
					//保存B列的快递名
					$data[$currentColumn][] = $val;

				}else if($currentColumn == 'C'){
					//保存C列的快递单号
					$data[$currentColumn][] = $val;
				}else{
					/**如果输出汉字有乱码，则需将输出内容用iconv函数进行编码转换，如下将gb2312编码转为utf-8编码输出*/
//					echo iconv('utf-8','utf-8', $val)."\t";
				}
			}
		}
		$excelData = array();
		foreach($data['A'] as $k => $v){
			$excelData[$k][] = $v;
		}
		foreach($data['B'] as $k => $v){
			$excelData[$k][] = $v;
		}
		foreach($data['C'] as $k => $v){
			$excelData[$k][] = $v;
		}

		foreach($excelData as $k => $v){
			$tid = $v['0'];
			if($v['2']){
				$express_id = $v['2'];
				$send_type = 'express';
			}else{
				$express_id = '0';
				$send_type = 'store';
			}
			//调用发货接口
			$this->orderDelivery($tid,$express_id,$send_type);
		}
		//取出保存在session的批量发货失败的订单号
		$fail_tid_arr = session('fail_tid');
		$fail_tid_str = '';
		//拼接成字符串格式后返回给前台
		foreach($fail_tid_arr as $v){
			$fail_tid_str .= $v.PHP_EOL;
		}
		echo ($fail_tid_str);
		//销毁session
		session('fail_tid',null);
	}

	public function addSpecCode() {
        $spec_code = I('post.spec_code','','htmlspecialchars');
        $tid = I('post.tid','','htmlspecialchars');

        $Partner = M('partner');
        $partnerInfo = $Partner->where(array('privilege_id' => $spec_code))->find();

        if(!$partnerInfo) {
            $res = [
                'sign' => -1,
                'msg' => '未找到此特权码，请确定后重试',
            ];
            $this->ajaxReturn($res);
        }

        $TradeModel = M('tradedetail');
        $data['privilege_id'] = $spec_code;
        $result = $TradeModel->where(array('tid' => $tid))->save($data);
        if($result) {
            $res = [
                'sign' => 1,
                'service' => $partnerInfo['p_name'],
                'msg' => '修改成功!',
            ];
            $this->ajaxReturn($res);
        }
    }

}
