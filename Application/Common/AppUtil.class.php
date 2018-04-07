<?php
namespace Common;
class AppUtil{

	/**
	 * 将参数数组签名
	 * @param array $array
	 * @param $appkey
	 * @return string
	 */
	public static function SignArray(array $array,$appkey){
		$array['key'] = $appkey;// 将key放到数组中一起进行排序和组装
		ksort($array);
		$blankStr = self::ToUrlParams($array);
		$sign = md5($blankStr);
		return $sign;
	}

	/**
	 * 生成URL参数
	 * @param array $array
	 * @return string
	 */
	public static function ToUrlParams(array $array)
	{
		$buff = "";
		foreach ($array as $k => $v)
		{
			if($v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}

	/**
	 * 校验签名
	 * @param array $array
	 * @param $appkey
	 * @return bool
	 */
	public static function ValidSign(array $array,$appkey){
		$sign = $array['sign'];
		unset($array['sign']);
		$array['key'] = $appkey;
		$mySign = self::SignArray($array, $appkey);
		return strtolower($sign) == strtolower($mySign);
	}

	/**
	 * 发送请求操作
	 * @param $url
	 * @param $params
	 * @return mixed
	 */
	public static function request($url,$params){
		$ch = curl_init();
		$this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
		curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//如果不加验证,就设false,商户自行处理
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		$output = curl_exec($ch);
		curl_close($ch);
		return  $output;
	}

	/**
	 * 验证签名是否正确
	 * @param $array
	 * @return bool
	 */
	public static function checkSign($array,$appkey){
		if("SUCCESS"==$array["retcode"]){
			$signRsp = strtolower($array["sign"]);
			$array["sign"] = "";
			$sign =  strtolower(self::SignArray($array, $appkey));
			if($sign==$signRsp){
				return array("status_code"=>0,"status_msg"=>"验签成功");
			}
			else {
				return array("status_code"=>1,"status_msg"=>"验签失败:".$signRsp."--".$sign);
			}
		}
		return array("status_code"=>2,"status_msg"=>$array["retmsg"]);
	}
}
?>