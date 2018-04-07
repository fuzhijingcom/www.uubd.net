<?php
namespace Tools;
require_once "WxPayApi.class.php";
require_once "WxPayNotify.class.php";

class WxPayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
        $order_sn = $data["out_trade_no"];
        $userid = $data['attach'];
        $array_order_sn = explode("_",$order_sn);
        $order_sn = $array_order_sn[0];
        //订单基本信息
        $orderinfo = M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->where("order_sn='".$order_sn."'")->find();
        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        if(empty($orderinfo)){
            $msg = "订单查询失败";
            return false;
        }
        $orderinfo['order_charge'] = sprintf("%.2f",($orderinfo['total_amount']-$orderinfo['coupon_charge']-$orderinfo['packet_charge']-$orderinfo['member_discount']-$orderinfo['discount']));//应支付金额
        $log = M("PayLog",C("DB_MALL.db_prefix"),"DB_MALL")->where("`transaction_id`='".$data['transaction_id']."' AND `order_sn`='".$order_sn."'")->find();
        if(empty($log)){//支付日志为空，进行支付处理
            $excute_sql = array();
            $is_execute = true;
            //更新订单状态
            $excute_sql[] = "UPDATE ".C("DB_MALL.db_prefix")."order SET `paytime`=".time().",`pay_status`=1,`status`=3,`paytype`=2 WHERE `order_sn`='".$order_sn."'";
            if(intval($orderinfo['exchange_type'])==0) {
                $ordergoods = M("OrderGoods", C("DB_MALL.db_prefix"), "DB_MALL")->where("`order_id`=" . $orderinfo['order_id'])->field("*")->select();
                foreach ($ordergoods as $goods) {
                    //更新商品销量
                    $excute_sql[] = "UPDATE " . C("DB_MALL.db_prefix") . "goods SET `sales`=`sales`+" . intval($goods['goods_number']) . " WHERE `goodsid`=" . intval($goods['goods_id']);
                }
            }

            //判断是否有优惠券
            if(intval($orderinfo['coupon_id'])){
                //更新优惠券信息
                $excute_sql[] = "UPDATE ".C("DB_MALL.db_prefix")."coupon_record SET `is_used`=1,`usetime`=".time()." WHERE `coupon_id`=".intval($orderinfo['coupon_id'])." AND `member_id`=".$orderinfo['buyer_id'];
            }
            //添加支付日志
            $excute_sql[] = "INSERT INTO ".C("DB_MALL.db_prefix")."pay_log(`transaction_id`,`order_sn`,`userid`,`paytype`,`charge`,`status`,`ip`,`posttime`,`remark`) VALUES('".$data['transaction_id']."','".$order_sn."',".$userid.",2,".$orderinfo['order_charge'].",1,'".get_client_ip()."',".time().",'支付成功')";
            M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->startTrans();
            foreach ($excute_sql as $sql){
                //write_file("/data/wwwroot/sh.seejiajia.com/mbjl-backend/Attachment/excute_sql.txt",$sql);
                $rr = M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->execute($sql);
                if(!$rr){
                    $is_execute = false;
                }
            }
            if($is_execute == true){
                M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->commit();
                return true;
            }else{
                M("Order",C("DB_MALL.db_prefix"),'DB_MALL')->rollback();
                return false;
            }
        }else{
            return true;
        }
	}
}
