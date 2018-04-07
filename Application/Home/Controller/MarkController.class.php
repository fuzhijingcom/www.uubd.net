<?php
namespace Home\Controller;
use Common\Controller\HomebaseController;

class MarkController extends HomebaseController {
	//用户页
//	public function Mark() {
//		if($_GET['startT']==null&&$_GET['par']==null){
//			$time['startT'] = date('Y-m-d');
//			$time['endT'] = date('Y-m-d', strtotime('next day'));
//		}else if($_GET['par']==null){
//			$time['startT'] = $_GET['startT'];
//			$time['endT'] = $_GET['endT'];
//		}else{
//			$flag = $_GET['par'];
//			
//		}
//		
//		// 判断访问用户的身份权限
//		$type = null; // 显示的数据 0为门店，1为推广员，2为市场部
//		$role = null;
//		$userinfo = session('userinfo');
//		switch($userinfo['userr_id']){
//			case 1: // admin
//				$role = 1;
//				if($_GET['s_type']){
//                    $type = $_GET['s_type'];
//                }else{
//                    $type = 0;
//                }
//				break;
//			case 3: // store worker

//				$role = 3;
//				$type = 0;
//				break;
//			case 4: // marketing expand
//				$role = 4;
//				$type = 2;
//				break;
//		}
//
//		// 根据type判断需要获取的统计数据
//		if($type == 0){
//			$result = $this->getStoreData($time);
//		}else{
//			$result = $this->getPartnerData($type,$time);
//		}
//
//		outputDebugLog($result,8);
//		$this->assign('role', $role);
//		$this->assign('type', $type);
//		$this->assign('date', $time);
//		$this->assign('tags', $result);
//		$this->display();
//	}

	/**
	 * 新mark
	 */
	public function mark(){
		$time['startT'] = date('Y-m-d');
		$time['endT'] = date('Y-m-d', strtotime('next day'));

		$userinfo = session('userinfo');
		$role = null;
		$store = array();
		$s_model = D('store');
		switch ((int)$userinfo['userr_id']){
			case 1:
				$role = 'admin';
				$store = $s_model->getStoreList(0);
				$this->assign('storeList', json_encode($store));
				break;
			case 3:
				$role = 'store_manager';
				$store = $s_model->getStoreList($userinfo['users_id']);
				$this->assign('storeList', json_encode($store));
				break;
			case 4:
				$role = 'marketing';
				break;
			case 8:
				$role = 'operation';
				break;
		}

		$this->assign('date', $time);
		$this->assign('role', $role);
		$this->display('Mark/newmark');
	}

	/**
	 *【获取统计数据】
	 */
	public function getMarkData(){
		if(IS_POST){
			$startT = I('post.startT','','htmlspecialchars');
			$endT = I('post.endT','','htmlspecialchars');
			$type = I('post.type','','htmlspecialchars');
			$flag = I('post.flag','','htmlspecialchars');
			
			// time
			if(empty($flag)){
				$time['startT'] = $startT.' 00:00:00';
				$time['endT'] = $endT.' 23:59:59';
				$time['startT'] = $startT.' 00:00:00';
				$time['endT'] = $endT.' 23:59:59';
			}else{
                switch((int)$flag){
                    //请求返回当天的数据
                    case 0:
                        $time['startT'] = date('Y-m-d').' 00:00:00';
                        $time['endT'] = date('Y-m-d').' 23:59:59';
                        break;
                    //请求返回昨天的数据
                    case 1:
                        $time['startT'] = date('Y-m-d', strtotime('last day')).' 00:00:00';
                        $time['endT'] = date('Y-m-d', strtotime('last day')).' 23:59:59';
                        break;
                    //请求返回当月的数据
                    case 2:
                        $time['startT'] = date('Y-m-d', strtotime('first day of this month')).' 00:00:00';
                        $time['endT'] = date('Y-m-d', strtotime('last day of this month')).' 23:59:59';
                        break;
                    case 3:
                        $time['startT'] = date('Y-m-d' , strtotime('first day of last month')).' 00:00:00';
                        $time['endT'] = date('Y-m-d', strtotime('last day of last month')).' 23:59:59';
                }
			}


            // 判断访问用户的身份权限
			$result = null;
            $userinfo = session('userinfo');
            switch($userinfo['userr_id']){
                case 1: // admin
                    break;
                case 3: // store worker
					if($type!=0){
						$result['err'] = '越权访问，滚蛋！';
						echo json_encode($result);
						exit;
					}
                    break;
                case 4: // marketing expand
					if($type!=2 and $type !=1){
						$result['err'] = '越权访问，滚蛋！';
						echo json_encode($result);
						exit;
					}
                    break;
            }
            // 根据type判断需要获取的统计数据
            if((int)$type === 0){
				//获取门店数据
                $result = $this->getStoreData($time);
            }else if((int)$type === 1){
				//获取合伙人或市场部数据
                $result = $this->getPartnerData($type,$time);
            }
			exit(json_encode($result));
		}
	}
	
	/**
	 *【获取服务数据】
	 */
	public function getServerData(){
		if(IS_POST){
			$startT = I('post.startT','','htmlspecialchars');
			$endT = I('post.endT','','htmlspecialchars');
			$type = I('post.type','','htmlspecialchars');
			$flag = I('post.flag','','htmlspecialchars');
			
			// time
			if(empty($flag)){
				$time['startT'] = $startT.' 00:00:00';
				$time['endT'] = $endT.' 23:59:59';
				$time['startT'] = $startT.' 00:00:00';
				$time['endT'] = $endT.' 23:59:59';
			}else{
                switch((int)$flag){
                    //请求返回当天的数据
                    case 0:
                        $time['startT'] = date('Y-m-d').' 00:00:00';
                        $time['endT'] = date('Y-m-d', strtotime('next day')).' 23:59:59';
                        break;
                    //请求返回昨天的数据
                    case 1:
                        $time['startT'] = date('Y-m-d', strtotime('last day')).' 00:00:00';
                        $time['endT'] = date('Y-m-d').' 23:59:59';
                        break;
                    //请求返回当月的数据
                    case 2:
                        $time['startT'] = date('Y-m-d', strtotime('first day of this month')).' 00:00:00';
                        $time['endT'] = date('Y-m-d', strtotime('next day')).' 23:59:59';
                        break;
                }	
			}

            // 判断访问用户的身份权限
			$result = null;
            $userinfo = session('userinfo');
            switch($userinfo['userr_id']){
                case 1: // admin
                    break;
                case 3: // store worker
					if($type!=$userinfo['users_id']){
						$result['err'] = '越权访问，滚蛋！';
						echo json_encode($result);
						exit;
					}
                    break;
				default:
					$result['err'] = '越权访问，滚蛋！';
					echo json_encode($result);
					exit;
            }

            // 根据type判断需要获取的统计数据
			$r_model = D('research');
			$s_model = M('store');
			$s_map['s_id'] = $type;
			$s_res = $s_model->where($s_map)->field('s_name')->find();
			$d_res = $r_model->getServerData($type,$time);
            $result = array(
				's_name' => $s_res['s_name'],
				'great' => $d_res['g_res'],
				'good' => $d_res['n_res'],
				'bad' => $d_res['b_res']
			);
			echo json_encode($result);
		}
	}
	
	

	/**
	 * 【获取门店信息】
	 */
	public function getStoreData($time){
		// 获取当前登录的用户的s_id
		$userinfo = session('userinfo');
		$Store = D('Store');
		$data = $Store->getStoreDataDetail($userinfo['userid'],$userinfo['users_id'],$time);
		return $data;
	}


	/**
	 * 【获取 合伙人/市场部 推广信息】
	 */
	public function getPartnerData($type,$time){
		$userinfo = session('userinfo');
		if($userinfo['users_id'] != '-1' && $userinfo['users_id']!='-2'){
			echo "<script>alert('抱歉，你没有这个权限！');history.back(-1);</script>";
		}
		//获取时间范围
		$post_data = I('post.time',0,'trim,strip_tags,htmlspecialchars');
		$post_time = json_decode($post_data,true);
		$start_time = $post_time['start_time'].' 00:00:00';
		$end_time = $post_time['end_time'].' 23:59:59';

		$s_id = I('get.s_id','','trim,strip_tags');

		if($type == 2){
			//如果是市场部
			$Customer = D('Customer');
			$data = $Customer->getMarketDataDetail($type,$time,$s_id);
		}else{
			//合伙人
			$Partner = D('Partner');
			$data = $Partner->getPartnerCountDetail($time);
		}
		header('Content-Type:text/html;charset=utf8');
		return $data;
	}


	/**
	 * 【导出校园合伙人数据】
	 */
	public function exportPartnerData(){
		$this->error('该功能暂时不可用');
		$Customer = D('Customer');
		$time['startT'] = $_GET['startT'].' 00:00:00';
		$time['endT'] = $_GET['endT'].' 23:59:59';

		$data = $Customer->getPartnerDataDetail(1,$time,1);


		$Tradedetail = M('Tradedetail');
		$field = array('tid','title','receiver_name','receiver_mobile','created','total_fee','payment');
		foreach ($data as $k => $v){
			$where = array(
				'promotion' => $v['s_name'],
				'status' => 'TRADE_BUYER_SIGNED'
			);
			$data[$k]['trade'][] = $Tradedetail ->field($field)-> where($where)->select();
		}
		//删除数组的第一个元素（总和）
		array_shift($data);
		$result = array();
		foreach($data as $k => $v){
			$result[$k][] = $v['s_name'];
			$result[$k][] = $v['custoCount'];
			$result[$k][] = $v['liushiCount'];
			$result[$k][] = $v['guanzhuCount'];
			$result[$k][] = $v['jingzengCount'];
			$result[$k][] = $v['tradeCount'];
			$result[$k][] = number_format($v['priceCount'],2);
			$result[$k][] = number_format($v['incomeCount'],2);
		}

		$headArr = array('姓名','推广人数','流失人数','关注人数','有效注册人数','产生订单数','总收入金额','已提现金额');
		$format = array(
			'align'  => array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1','H1'),
			'length' => array('A' => 20, 'B' => 20, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 15,'G' => 15, 'H'=>15),
		);
		$sheetName = '校园合伙人';
		exportExcel('校园合伙人',$headArr,$result,$sheetName,$format);
	}

	/**
	 * 【导出校园合伙人订单详情表】
	 */
	public function exportPartnerOrderData(){
		$this->error('该功能暂时不可用');
		$time['startT'] = $_GET['startT']?$_GET['startT']:date('Y-m-d',time()-86400);
		$time['endT'] = $_GET['endT']?$_GET['endT']:date('Y-m-d');

		$Store = M('Store');
		$s_type = 1;//合伙人
		$data = $Store->where(array('s_type'=>$s_type))->select();
		$s_names = '';
		foreach($data as $k => $v){
			$s_names .= "$v[s_name],";
		}
		$s_names = rtrim($s_names,',');
		$Tradedetail = M('Tradedetail');
		$field = array(
			'tid'=>'tid',
			'title'=>'title',
			'receiver_city'=>'receiver_city',
			'receiver_district'=>'receiver_district',
			'receiver_address'=>'receiver_address',
			'receiver_name'=>'receiver_name',
			'receiver_mobile'=>'receiver_mobile',
			'total_fee'=>'total_fee',
			'payment'=>'payment',
			'created'=>'created',
			'shop'=>'shop',
			'promotion'=>'promotion',
			'is_first'=>'is_first',
		);
		$where = array(
			'promotion'=>array('IN',$s_names),
			'status'=>'TRADE_BUYER_SIGNED',
			'created'=>array('between',array($time['startT'],$time['endT'])),
		);
		$result = $Tradedetail->field($field)->where($where)->select();
		if(!$result) exit("<script>alert('无符合条件的数据！');history.back(-1);</script>");
		$exportData = array();
		foreach($result as $k => $v){
			$exportData[$k]['promotion'] = $v['promotion'];
			$exportData[$k]['tid'] =$v['tid'];
			$exportData[$k]['title'] =$v['title'];
			$exportData[$k]['total_fee'] = number_format($v['total_fee'],2);
			$exportData[$k]['payment'] = number_format($v['payment'],2);
			$exportData[$k]['created'] = $v['created'];
			$exportData[$k]['shop'] = $v['shop'];
			$exportData[$k]['receiver_address'] =$v['receiver_city'].$v['receiver_district'].$v['receiver_address'];
			$exportData[$k]['receiver_name'] = $v['receiver_name'];
			$exportData[$k]['receiver_mobile'] = $v['receiver_mobile'];
			$exportData[$k]['is_first'] = $v['is_first']==0?'否':'是';
		}
		$headArr = array('推广人','订单编号','商品名称','商品价格','实付金额','下单时间','所在门店','收货地址','收件人','收件人号码','是否首单');
		$format = array(
			'align'  => array('A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1','H1','I1','J1','K1'),
			'length' => array('A' => 20, 'B' => 30, 'C' => 40, 'D' => 10, 'E' => 10, 'F' => 20,'G' => 15, 'H'=>30 , 'I'=>15 , 'J'=> 20 ,'K'=> 10),
		);
		$sheetName = '校园合伙人订单详情';
		exportExcel('校园合伙人订单详情',$headArr,$exportData,$sheetName,$format);

	}

	public function searchPartnerData(){
        //获取时间信息
        $startT = I('post.startT','','htmlspecialchars');
        $endT   = I('post.endT','','htmlspecialchars');
        $flag   = I('post.flag','','htmlspecialchars');

        if(empty($flag)){
            $time['startT'] = $startT.' 00:00:00';
            $time['endT'] = $endT.' 23:59:59';
            $time['startT'] = $startT.' 00:00:00';
            $time['endT'] = $endT.' 23:59:59';
        }else{
            switch((int)$flag){
                //请求返回当天的数据
                case 0:
                    $time['startT'] = date('Y-m-d').' 00:00:00';
                    $time['endT'] = date('Y-m-d').' 23:59:59';
                    break;
                //请求返回昨天的数据
                case 1:
                    $time['startT'] = date('Y-m-d', strtotime('last day')).' 00:00:00';
                    $time['endT'] = date('Y-m-d', strtotime('last day')).' 23:59:59';
                    break;
                //请求返回当月的数据
                case 2:
                    $time['startT'] = date('Y-m-d', strtotime('first day of this month')).' 00:00:00';
                    $time['endT'] = date('Y-m-d', strtotime('last day of this month')).' 23:59:59';
                    break;
            }
        }
        //获取搜索的合伙人类型或搜索条件
        $promotion_type = I('post.promotion_type',0,'strip_tags,htmlspecialchars');
        $search_txt     = I('post.search_txt',0,'strip_tags,htmlspecialchars');

        //拼接where条件
        $map = array();
        if(isset($search_txt)) {
            $where['p_id']          = array('like', '%' . $search_txt . '%');
            $where['p_name']        = array('like', '%' . $search_txt . '%');
            $where['p_phone']       = array('like', '%' . $search_txt . '%');
            $where['privilege_id']  = array('like', '%' . $search_txt . '%');
            $where['weixin_openid'] = array('like', '%' . $search_txt . '%');
            $where['_logic'] = 'or';
            $map['_complex']        = $where;
        }
        if(isset($promotion_type) && $promotion_type != '0'){
            $map['promotion_type'] = $promotion_type;
        }

        $Partner = D('Partner');
        //查询符合条件的合伙人数据
        $partnerData = $Partner->where($map)->select();
        //获取该合伙人的统计数据
        $result = $Partner->getPartnerCountDetail($time,$partnerData);
        //返回给前端
        exit(json_encode($result));

    }


    /**
     * [获取订单统计数据]
     */
    public function getOrderCountData(){
//	    dump(I('post.'));die;
        /*前端传递的条件*/
        $s_name             = I('post.s_name','','strip_tags,htmlspecialchars');
        $flag               = I('post.flag','','strip_tags,htmlspecialchars');
        $selling_id         = I('post.selling_id','','strip_tags,htmlspecialchars');
        $g_type             = I('post.g_type','','strip_tags,htmlspecialchars');
        //获取时间
        $time = array();
        if(empty($flag)){
            $time['start_time'] = I('post.startT','','strip_tags,htmlspecialchars').' 00:00:00';
            $time['end_time'] = I('post.endT','','strip_tags,htmlspecialchars').' 23:59:59';;
        }else{
            switch($flag){
                //请求返回当天的数据
                case 0:
                    $time['start_time'] = date('Y-m-d').' 00:00:00';
                    $time['end_time'] = date('Y-m-d').' 23:59:59';
                    break;
                //请求返回昨天的数据
                case 1:
                    $time['start_time'] = date('Y-m-d', strtotime('last day')).' 00:00:00';
                    $time['end_time'] = date('Y-m-d', strtotime('last day')).' 23:59:59';
                    break;
                //请求返回当月的数据
                case 2:
                    $time['start_time'] = date('Y-m-d', strtotime('first day of this month')).' 00:00:00';
                    $time['end_time'] = date('Y-m-d', strtotime('last day of this month')).' 23:59:59';
                    break;
                case 3:
                    $time['start_time'] = date('Y-m-d' , strtotime('first day of last month')).' 00:00:00';
                    $time['end_time'] = date('Y-m-d', strtotime('last day of last month')).' 23:59:59';
                    break;
            }
        }
        //根据线上编号首字符获取商品大分类
        $g_name = getGoodsBigCateByFirst($g_type);

        if((int)$s_name === -1){
            $s_name = '';
        }
        /*获取订单商品信息*/
        $Stock = D('Stock');
        $sourceData = $Stock->getOrderGoodsData($time,$g_name,$s_name,$selling_id);

        //遍历每条记录获取需要的数据
        $temp_data = array();
        foreach($sourceData as $k => $v){
            //如果是“特殊商品”不按门店获取库存信息(特殊商品仅在总仓有记录)
            if(strpos(strtolower($v['sku_unique_code']),'h11') !== false ||
            strpos(strtolower($v['sku_unique_code']),'h21') !== false ||
            strpos(strtolower($v['sku_unique_code']),'h91') !== false ){
                $goodsInfo = $Stock->getInfoBySkuId($v['sku_unique_code']);
            }else{
                if($s_name===''){
                    $goodsInfo = $Stock->getInfoBySkuId($v['sku_unique_code']);
                }else{
                    $goodsInfo = $Stock->getInfoBySkuId($v['sku_unique_code'],$v['shop']);
                }
            }

            //取出护理液的xxxml （格式：300+120ml || 250ml）
            $pattern = '/[0-9]*\+*[0-9]*[a-z]*$/';
            preg_match($pattern,$goodsInfo['goods_name'],$h_ml);

            $date = date('Y-m-d',strtotime($v['created']));

            $StockNext = D('Stocknext');

            if($s_name===''){
                /*开始收集数据*/

                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['data_time'] = $date;

                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['tid'] = $v['tid'];
                //商品类型
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['goods_type'] = getGoodsTypeBySellingId($v['sku_unique_code']);
                //商城编号
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['selling_id'] = $goodsInfo['selling_id'];
            }else{
                /*开始收集数据*/
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['data_time'] = $date;

                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['tid'] = $v['tid'];
                //商品类型
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['goods_type'] = getGoodsTypeBySellingId($v['sku_unique_code']);
                //商城编号
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['selling_id'] = $goodsInfo['selling_id'];
            }

            //款式/规格
            //根据不同的商品类型显示不同的属性
            $temp_sku_id = strtolower($v['sku_unique_code']);
            //隐形眼镜取度数
            if(strpos($temp_sku_id,'y') !== false){
                $attribute = $goodsInfo['degree']?$goodsInfo['degree']:'x';
            }else if(strpos($temp_sku_id,'h') !== false) { // 清洗器/润眼液/镜片/运动太阳镜片/加工/护理液全都是h
                if (strpos($temp_sku_id, 'h04') !== false) { //清洗器
                    $attribute = '';
                } else if(strpos($temp_sku_id, 'h03') !== false) { //润眼液
                    $attribute = $h_ml?$h_ml:'x';
                } else if(strpos($temp_sku_id,'h06') !== false || strpos($temp_sku_id,'h07') !== false){ //护理液
                    $attribute = $h_ml?$h_ml:'x';
                } else if(strpos($temp_sku_id,'h08') !== false){ //润滑液
                    $attribute = $h_ml?$h_ml:'x';
                } else if (strpos($temp_sku_id, 'h11') !== false) { //镜片
                    $attribute = $goodsInfo['style'];
                } else if (strpos($temp_sku_id, 'h91') !== false) { //运动/太阳镜片
                    $attribute = $goodsInfo['style'];
                } else if (strpos($temp_sku_id, 'h21') !== false) { //加工
                    $attribute = $goodsInfo['style'];
                }
            }else{
                $attribute = $goodsInfo['style']?$goodsInfo['style']:'x';
            }

            //如果是汇总
            if($s_name === ''){
                //属性
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['style'] = $attribute;
                //单价
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['single_price'] = $goodsInfo['price'];
                //销量
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['sale_num'] += $v['num'];
                //销售总价
                if(strpos($temp_sku_id,'h11') !== false){ //镜片商品特殊处理
                    $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['total_payment'] += $goodsInfo['price'];
                }else{
                    $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['total_payment'] += $v['payment'];
                }
                //门店
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['shop'] = '汇总';
                //库存
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['stock_num'] = $StockNext->getAllQuantityInt($v['sku_unique_code']);
                //下单时间
                $temp_data['table'][$v['sku_unique_code'].'_汇总_'.$date]['created'] = $v['created'];

            }else{
                //属性
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['style'] = $attribute;
                //单价
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['single_price'] = $goodsInfo['price'];
                //销量
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['sale_num'] += $v['num'];
                //销售总价
                if(strpos($temp_sku_id,'h11') !== false){ //镜片商品特殊处理
                    $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['total_payment'] += $goodsInfo['price'];
                }else{
                    $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['total_payment'] += $v['payment'];
                }
                //门店
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['shop'] = $goodsInfo['warehouse'];
                //库存
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['stock_num'] = $goodsInfo['quantity'];
                //下单时间
                $temp_data['table'][$v['sku_unique_code'].'_'.$v['shop'].'_'.$date]['created'] = $v['created'];
            }
        }
        $temp_data['charts0'] = $this->initOrderChart($temp_data['table'],0);
        $temp_data['charts1'] = $this->initOrderChart($temp_data['table'],1);
        exit(json_encode($temp_data));
	}


	public function initOrderChart($data,$type){
//        dump($data);die;
        $orderData = array();
        foreach($data as $k => $v){
            //将每条数据的日期"格式化成 2016-01-01的格式"
            $date = date('Y-m-d',strtotime($v['created']));

            //其他商品
            if($v['goods_type'] != '框架眼镜' && $v['goods_type'] != '隐形眼镜' && $v['goods_type'] != '功能眼镜' && $v['goods_type'] != '太阳镜' ){
                //根据不同的类型获取不同的数据
                if((int)$type===1){
                    $orderData[$date]['其它商品'] += $v['total_payment'];
                }else{
                    $orderData[$date]['其它商品'] += $v['sale_num'];
                }

            }else{
                //判断获取数据的类型 1 金额 0销量
                if((int)$type===1){
                    // $orderData[2016-10-10][框架眼镜] => 20;
                    $orderData[$date][$v['goods_type']] += $v['total_payment'];
                }else{
                    $orderData[$date][$v['goods_type']] += $v['sale_num'];
                }
            }
            //r如果没有数据的
            if(!$orderData[$date]['框架眼镜']){
                $orderData[$date]['框架眼镜'] = 0;
            }
            if(!$orderData[$date]['太阳镜']){
                $orderData[$date]['太阳镜'] = 0;
            }
            if(!$orderData[$date]['功能眼镜']){
                $orderData[$date]['功能眼镜'] = 0;
            }
            if(!$orderData[$date]['隐形眼镜']){
                $orderData[$date]['隐形眼镜'] = 0;
            }
            if(!$orderData[$date]['其它商品']){
                $orderData[$date]['其它商品'] = 0;
            }

        }
//        dump($orderData);die;
        //按数组下标[日期]排序
        ksort($orderData);
        //拼接日期字符串(给highcharts使用)
        $date_str = '[';
        $temp_data = array();
        foreach($orderData as $k => $v){
            $date_str .= "'".$k."',";
            foreach($v as $k1 => $v1){
                $temp_data[$k1] .= $v1.',';
            }
        }
        $date_str = rtrim($date_str,',');
        $date_str .= ']';


        //拼接highcharts需要的数据格式
        $temp_data1 = '[';
        foreach($temp_data as $k => $v){
            $temp_data1 .= "{name:'{$k}',data:[{$v}]},";
        }
        $temp_data1 = rtrim($temp_data1,',');
        $temp_data1 .= ']';


        $return_data['data'] = $temp_data1;
        $return_data['date'] = $date_str;

        //判断获取的类型，1 销售金额 or 销售数量 显示不同的文案
        if((int)$type===1){
            $return_data['text'] = '销售金额（单位：元）';
            $return_data['suffix'] = '元';
        }else{
            $return_data['text'] = '销售数量（单位：个）';
            $return_data['suffix'] = '个';
        }
        outputDebugLog('22222:',8);
        outputDebugLog($data,8);
        //返回给前端
        return $return_data;die;
    }


    public function getTime(){
        $flag = I('get.flag',0,'htmlspecialchars');
        switch($flag){
            //请求返回当天的数据
            case 0:
                $time['start_time'] = date('Y-m-d');
                $time['end_time'] = date('Y-m-d');
                break;
            //请求返回昨天的数据
            case 1:
                $time['start_time'] = date('Y-m-d', strtotime('last day'));
                $time['end_time'] = date('Y-m-d', strtotime('last day'));
                break;
            //请求返回当月的数据
            case 2:
                $time['start_time'] = date('Y-m-d', strtotime('first day of this month'));
                $time['end_time'] = date('Y-m-d', strtotime('last day of this month'));
                break;
            case 3:
                $time['start_time'] = date('Y-m-d' , strtotime('first day of last month'));
                $time['end_time'] = date('Y-m-d', strtotime('last day of last month'));
                break;
        }
        exit(json_encode($time));
    }


    /**
     * [获取页面统计所有数据]
     * @return mixed
     */
    public function getCountData($time = 0){
        $CountPageLog = M('count_page_log');
        if($time){
            $where = " time like '{$time} %'";
            return $CountPageLog->where($where)->select();
        }else{
            return $CountPageLog->select();
        }
    }


    /**
     * [获取排名前10的页面数据]
     * @return array
     */
    public function getPageBrowseData($time){
        $data = $this->getCountData($time);

        $temp_data = array();
        foreach($data as $k => $v){
            $temp_data[$v['mca']] += 1;
        }

        arsort($temp_data);

        $show_num = C('TOP_BROWSE_PAGE_NUM');
        $temp_num = $show_num;
        $result_data = array();
        foreach($temp_data as $k => $v){
            if($temp_num > 0){
                $result_data[$k] = $v;
            }
            $temp_num --;
        }

        $return_data['text'] = 'Top'.$show_num.'访问页';
        $return_data['data'] = $result_data;

        return $return_data;

    }


    /**
     * [统计概况页面]
     */
    public function Count(){

        $CountController = new \Tools\Controller\CountController();
        $array = array('page','goods','search_goods');
        foreach($array as $k => $v){
            $CountController->saveLogToDB($v);
        }

        //今天日期
        $today = date('Y-m-d');
        //昨天日期
        $yesterday = date('Y-m-d',strtotime('yesterday'));
        //获取所有数据
        $temp_data = $this->getCountData();

        $data = array();
        $data['yesterday']['pv_count'] = $this->getPVCount($temp_data,$yesterday);
        //UV值
        $data['today']['uv_count'] = $this->getUVCount($temp_data,$today);
        //pv值
        $data['today']['pv_count'] = $this->getPVCount($temp_data,$today);
        $data['yesterday']['uv_count'] = $this->getUVCount($temp_data,$yesterday);
        //今日订单数、金额
        $data['today']['order_num'] = $this->getOrderResult(0,0,'num');
        $data['today']['order_price'] = $this->getOrderResult(0,0,'price');

        $yesterday_date = array(
            'start_time' => date('Y-m-d',strtotime('-1 day')).' 00:00:00',
            'end_time'  => date('Y-m-d',strtotime('-1 day')).' 23:59:59',
        );
        //昨日订单数、金额
        $data['yesterday']['order_num'] = $this->getOrderResult($yesterday_date,0,'num');;
        $data['yesterday']['order_price'] = $this->getOrderResult($yesterday_date,0,'price');;

        //商品搜索记录
        $data['search_goods'] = $this->getSearchGoods(date('Y-m-d'));
        //访问的页面
        $data['page_browse'] = $this->getPageBrowseData(date('Y-m-d'));


        $this->assign('data',$data);
        $this->display('count');
    }


    /**
     * [获取uv值]
     * @param $data 数据
     * @param bool $date 时间
     * @return int
     */
    public function getUVCount($data,$date = false){
        $uv = array();
        //如果没有时间条件，则取出数据中所有的以IP为准的UV数
        foreach($data as $k => $v){
            if($date === false){
                if(!$uv[$v['user_ip'].$v['weixin_openid']]){
                    $uv[$v['user_ip'].$v['weixin_openid']] += 1;
                }
            }else{
                if(date('Y-m-d',strtotime($v['time'])) == $date){
                    if(!$uv[$v['user_ip'].$v['weixin_openid']]){
                        $uv[$v['user_ip'].$v['weixin_openid']] += 1;
                    }
                }
            }
        }

        $uv_num = 0;
        foreach($uv as $k => $v) {
            $uv_num += 1;
        }

        return $uv_num;
    }


    /**
     * [获取pv值]
     * @param $data
     * @param bool $date
     * @return int
     */
    public function getPVCount($data,$date = false){
        $pv = 0;
        foreach($data as $k => $v){
            if($date === false){
                $pv += 1;
            }else{
                if(date('Y-m-d',strtotime($v['time'])) == $date){
                    $pv += 1;
                }
            }
        }
        return $pv;
    }


    /**
     * [获取用户浏览商品后有动作的数据]（加入购物车/购买）
     * @param $action  0 购买 1 加入购物车
     * @param int $date 日期时间：2017-02-06
     * @return mixed
     */
    public function getGoodsActionData($action,$date = 0){
        if(!$date){
            $date = date('Y-m-d');
        }

        if($action == 0){
            $action = 'TO_PAY';
        }else if($action == 1){
            $action = 'TO_CART';
        }

        $where = array(
            'time'  =>  array('LIKE',$date.'%'),
            'action'=>  $action
        );
        $CountGoodsLog = M('count_goods_log');
        return $CountGoodsLog->where($where)->select();
    }


    /**
     *[获取用户浏览商品后有动作的数据图表]
     */
    public function getGoodsActionDataCharts(){

        //获取前端传递的动作（0购买，1加入购物车）
        $action = I('post.action',0,'htmlspecialchars');

        if($action != 0 && $action != 1){
            $return_data = array(
                'sign'=>-1,
                'msg'=>'非法请求'
            );
            exit(json_encode($return_data));
        }

        //获取有购买动作的商品
        $data = $this->getGoodsActionData($action);

        //如果没有数据
        if(!$data){
            $return_data = array(
                'sign'=>-1,
                'msg'=>'暂无数据'
            );
            exit(json_encode($return_data));
        }

        //计算出当天内每个款型有动作的次数
        $temp_data = array();
        foreach($data as $k => $v){
            $temp_data[$v['sku_id']] += 1;
        }

        //按照次数从高到低排序
        arsort($temp_data);

        //获取展示数据的条数
        if($action == 0){
            $action = 'TO_PAY';
        }
        if($action == 1){
            $action = 'TO_CART';
        }
        $show_num = C('SHOW_'.$action.'_NUM');
        $temp_num = $show_num;
        $result_data = array();
        foreach($temp_data as $k => $v){
            if($temp_num > 0){
                $result_data[$k] = $v;
            }
            $temp_num --;
        }

        //拼接highcharts需要的数据格式
//        [{
//            type: 'pie',
//            name: 'Browser share',
//            data: [
//                ['框架眼镜',   45.0],
//                ['隐形眼镜',       26.8],
//                ['太阳镜',    8.5],
//                ['护理液',     6.2],
//                ['我',   0.7]
//            ]
//        }]

        $return_data = array();
        //展示的标题
        if($action == 'TO_PAY'){
            $return_data['text'] = 'Top'.$show_num.'被购买的商品';
        }else if($action == 'TO_CART'){
            $return_data['text'] = 'Top'.$show_num.'被加入购物车的商品';
        }

        //展示的数据
        $return_data['data'] = "[{ type:'pie',name: '欲购买率',data:[";
        foreach($result_data as $k => $v){
            $return_data['data'] .= "['{$k}',$v],";
        }

        $return_data['data'] .= "]}]";

        exit(json_encode($return_data));

    }

    /**
     * [按日期获取每个小时的数据]
     * @param $date 日期
     * @param $type 类型（0pv，1uv）
     * @return array
     */
    public function getHourData($date,$type = 0){
        $data = $this->getCountData();
        //趋势折线图 00:00 - 23:59,筛选出今日的数据
        $today_data = array();
        foreach($data as $k => $v){
            if(date('Y-m-d',strtotime($v['time'])) == $date){
                $today_data[] = $v;
            }
        }

        //收集好有数据的点
        $today_hour_time = array();
        foreach($today_data as $k => $v){
            $hour = date('H',strtotime($v['time']));
            $today_hour_time[$hour] = $hour;
        }

        //按照时间点计算数据
        $today_hour_data = array();
        foreach($today_data as $k => $v){
            foreach($today_hour_time as $k1 => $v1){
                if(date('H',strtotime($v['time'])) == $v1){
                    if($type === 0){
                        //pv
                        $today_hour_data[(int)$v1] += 1;
                    }else{
                        //uv
                        if(!$today_hour_data[(int)$v1][$v['user_ip'].$v['weixin_openid']]){
                            $today_hour_data[(int)$v1][$v['user_ip'].$v['weixin_openid']] += 1;
                        }
                    }
                }
            }
        }
        //点数不存在的默认赋0
        $hour_time = range(0,24);

        foreach($hour_time as $k => $v){
            if(!$today_hour_data[$v]){
                $today_hour_data[$v] = 0;
            }
        }

        //排序
        ksort($today_hour_data);

        //如果是取uv值则需要再做处理（因为结构不同）
        if($type != 0){
            $temp_hour_data = array();
            foreach($today_hour_data as $k => $v){
                foreach($v as $k1 => $v1){
                    $temp_hour_data[$k] += $v1;
                }
            }

            //点数不存在的默认赋0
            $hour_time = range(0,24);
            foreach($hour_time as $k => $v){
                if(!$temp_hour_data[$v]){
                    $temp_hour_data[$v] = 0;
                }
            }

            return $temp_hour_data;
        }

        //返回
        return $today_hour_data;
    }


    /**
     * [获取今日/昨日的折线图]
     */
    public function getTodayZheXianCharts(){
        //获取前端传递的数据类型（PV/UV）
        $type = I('post.type','','htmlspecialchars');

        if(!$type && (int)$type !== 0){
            exit('error.');
        }
        //判断前端需要的数据（PV/UV）
        if((int)$type === 0){
            $today_data = $this->getHourData(date('Y-m-d'));
            $yesterday_data = $this->getHourData(date('Y-m-d',strtotime('yesterday')));
        }else{
            $today_data = $this->getHourData(date('Y-m-d'),1);
            $yesterday_data = $this->getHourData(date('Y-m-d',strtotime('yesterday')),1);
        }

        ksort($today_data);ksort($yesterday_data);

        $str = '[';
        $str .= "{name:'今天',data:[";
        foreach($today_data as $k => $v){
            $str .= $v .', ';
        }
        rtrim($str,',');
        $str .= ']}';

        $str .= ",{name:'昨天',data:[";
        foreach($yesterday_data as $k => $v){
            $str .= $v .', ';
        }
        rtrim($str,',');
        $str .= ']}';
        $str .= ']';

        exit(json_encode($str));

    }


    /**
     * [获取搜索商品的全部数据]
     * @return mixed
     */
    public function getSearchGoodsData($time = 0){
        $SearchGoodsLog = M('count_search_goods_log');
        if($time){
            $where = " time like '{$time}%'";
            return $SearchGoodsLog->where($where)->select();
        }else{
            return $SearchGoodsLog->select();
        }
    }


    /**
     * [获取TOP10搜索热词]
     */
    public function getSearchGoods($time = 0){
        $data = $this->getSearchGoodsData($time);

        $temp_data = array();
        foreach($data as $k => $v){
            $temp_data[$v['value']] += 1;
        }

        arsort($temp_data);

        $show_num = C('TOP_SEARCH_GOODS_NUM');
        $temp_num = $show_num;
        $result_data = array();
        foreach($temp_data as $k => $v){
            if($temp_num > 0){
                $result_data[$k] = $v;
            }
            $temp_num --;
        }

        $return_data['text'] = 'Top'.$show_num.'搜索词';
        $return_data['data'] = $result_data;

        return $return_data;
    }



    /**
     * [获取订单数据]
     * @param $date
     * @param $status
     * @return mixed
     */
    public function getOrderData($date,$status,$condition = ''){
        $Tradedetail = M('Tradedetail');

        $where = " created >= '{$date['start_time']}' and created <= '{$date['end_time']}'";
        $where .= " and status IN({$status})";

        if(!$condition){
            $where .= $condition;
        }

        $data = $Tradedetail->where($where)->select();

        return $data;
    }


    /**
     * [获取订单数据结果]（订单数/订单金额）
     * @param int $date
     * @param int $status
     * @param string $type
     * @return mixed
     */
    public function getOrderResult($date = 0,$status = 0,$type = 'num'){
        if(!$date){
            unset($date);
            $date = array(
                'start_time' => date('Y-m-d').' 00:00:00',
                'end_time'  => date('Y-m-d') .' 23:59:59',
            );
        }

        if(!$status){
            //待发货 待确认收货 已签收
            $status = "'WAIT_SELLER_SEND_GOODS','WAIT_BUYER_CONFIRM_GOODS','TRADE_BUYER_SIGNED'";
        }

        $condition = " and feedback = 0";

        $data = $this->getOrderData($date,$status,$condition);

        $num = 0;
        $price = 0;

        foreach($data as $k => $v){
            if($type == 'num'){
                $num ++;
            }else if($type == 'price'){
                $price += $v['payment'];
            }
        }

        return $$type;
    }


    public function test(){
        $count_page_log = M('count_page_log');
        $data = $count_page_log->select();
        foreach($data as $k => $v){
            $data[$k]['user_ip'] = long2ip($v['user_ip']);
        }
        dump($data);
    }


}