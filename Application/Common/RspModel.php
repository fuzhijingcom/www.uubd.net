<?php
namespace Common;
require "Tlpay.class.php";
/**
 * 查询返回的model
 */
 class RspModel{
     public $appid = "";
     public $cusid = "";
     public $appkey = "";
     public $trxcode = "";
     public $timestamp = "";
     public $randomstr = "";
     public $sign = "";
     public $bizseq = "";
     public $retcode = "";
     public $retmsg = "";
     public $amount = "";
     public $trxreserve = "";

	 public function __construct($config)
	 {
         $this->appid = $config['APPID'];
         $this->cusid = $config['CUSID'];
         $this->appkey = $config['APPKEY'];
	 }

	 //初始化
	public function init($code,$msg){
		$this->retcode = $code;
		$this->retmsg = $msg;
		//$this->appid = getenv("TL_APPID");
		//$this->cusid = getenv("TL_CUSID");
		$this->trxcode = "T001";
		$this->timestamp = date("YmdHis");
		$this->randomstr = $this->timestamp;
	}
	//对对象进行签名
	public function sign(){
		$array = array();
		foreach($this as $key => $value) {
            if($key == "appkey"){
                continue;
            }
			if($value!=""){
				$array[$key] = $value;
			}
       }
       //$signStr = Tlpay::SignArray($array, getenv("TL_APPKEY"));
       $signStr = Tlpay::SignArray($array, $this->appkey);
       $this->sign = $signStr;	
	}


 }
?>