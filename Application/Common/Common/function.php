<?php

/**
 * 输出调试信息到指定目录
 *
 * 输出的调试信息均放到 Application/Runtime/ 目录下
 *
 * @param mixed     $data      输出的调试内容
 * @param int       $flag      运行一次生命周期内是否追加输出，0->不追加，8->追加模式
 * @param string    $file_name 输出的文件，默认文件名为 'debug.log'
 * @param string    $type      输出的数据格式类型，'var'->变量类型，'json'->输出成 JSON 格式
 * @param bool      $append    所有情况下都以追加模式写文件，仅当 $flag 值 8 时，此设置项生效
 */
function outputDebugLog($data, $flag = 0, $file_name = '', $type = 'var', $append = false)
{
    if (empty($file_name)) {
        $file_name = APP_PATH . 'Runtime/debug.log';
    } else {
        $file_name = APP_PATH . 'Runtime/' . $file_name;
    }

    $path = dirname($file_name);
    if (! is_dir($path)) {
        mkdir($path, 0777, true);
    }

    if (is_string($data)) {
        $out_result = $data;
    } else {
        switch ($type){
            case 'var':
                $out_result = var_export($data, TRUE);
                break;
            case 'json':
                $out_result = json_encode($data);
                break;
            default:
                $out_result = var_export($data, TRUE);
        }
    }

    if ($flag == 8 && $append == true) {
        if (! defined('OUTPUT_LOG_FLAG')) {
            define('OUTPUT_LOG_FLAG', true);
        }
    }

    if (defined('OUTPUT_LOG_FLAG')) {
        file_put_contents($file_name, $out_result, $flag);
    } else {
        define('OUTPUT_LOG_FLAG', true);
        file_put_contents($file_name, $out_result);
    }

    file_put_contents($file_name, PHP_EOL, FILE_APPEND);
}

/**
 * 记录错误日志
 *
 * @param string $message  错误消息
 * @param string $fileName 文件路径和文件名
 */
function errorLog($message, $fileName = 'Logs/error.log') {
    $time = date("Y-m-d H:i:s");
    $host = $_SERVER['HTTP_HOST'];
    $referrer = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $request = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['SCRIPT_FILENAME'];

    $msg = <<<EOT
$time [error] $message, host: "$host", request: "$request", referrer: "$referrer"
EOT;

    outputDebugLog($msg, 8, $fileName, 'var', true);
}


/**
 * 生成随机字符串
 * @param int $length 随机字符串长度
 * @param bool|true $letter 是否包含字母，默认包含字母
 * @return string
 */
function GeneralRandCode($length=6,$letter=true){
    $pattern='1234567890'; //字符池
    $key = "";
	if($letter){
        $pattern .= "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ";
    }
 	for($i=0;$i<$length;$i++){
  		$key.=$pattern{mt_rand(0,strlen($pattern))};//生成php随机数
 	}
 	return $key;
}

/**
 * 生成签名信息
 */
function _create_sign($appkey,$rand_str,$token,$client_id,$imei){
	$sign['appkey'] = $appkey;
	$sign['rand_str'] = $rand_str;
	$sign['token'] = $token;
	$sign['client_id'] = $client_id;
	$sign['imei'] = $imei;
	ksort($sign);
	$sign = http_build_query($sign);
	return md5(strtolower($sign));
}

/**
 * 生成token信息是否正确
 */
function _create_token($appkey,$rand_str){
	$token['appkey'] = $appkey;
	$token['rand_str'] = $rand_str;
	$token = http_build_query($token);
	$token .= "&secret=".API_AUTH;
	return md5(strtolower($token));
}

/**
 *
 * 框架跳转
 * @param unknown_type $link
 * @param unknown_type $msg
 */
function gourl($link="/",$msg=""){
    if (empty($msg)){
        echo "<script type='text/javascript'>window.top.location.href='{$link}'</script>";
    }else {
        echo "<script type='text/javascript'>alert('$msg');window.top.location.href='{$link}'</script>";
    }
}

/**
 * 
 * 写入文件
 * @param string $filename
 * @param string $data
 * @param string $method
 * @param int $iflock
 */
function write_file($filename,$data,$method="rb+",$iflock=1){
	@touch($filename);
	$handle=@fopen($filename,$method);
	if(!$handle){
		echo "此文件不可写:$filename";
	}
	if($iflock){
		@flock($handle,LOCK_EX);
	}
	@fputs($handle,$data);
	if($method=="rb+") @ftruncate($handle,strlen($data));
	@fclose($handle);
	@chmod($filename,0777);	
	if(!is_writable($filename) ){
		return false;
	}
	return true;
}

/**
 * 
 * 写入配置缓存文件
 * @param string $filename
 * @param array  $webdbs
 */
function write_config_cache($filename,$webdbs)
{
	$table = str_replace("@", "_", $filename);
	$arrModel = explode("@", $filename);
	foreach ($arrModel as $m){
		$Model .= ucfirst($m);
	}
	$Model = D($Model);
	if( is_array($webdbs) )
	{
		foreach($webdbs AS $key=>$value)
		{
			if(is_array($value))
			{
				$webdbs[$key]=$value=implode(",",$value);
			}
			$indata[] = "'".$key."'";
			//$data[$key] = $value;
			$SQL.="('$key', '$value'),";
		}
		$SQL=$SQL.";";
		$SQL=str_Replace("'),;","')",$SQL);
        $Model->Delete("`ckey` IN (".implode(",", $indata).")");
		$Model->ExecuteSql("INSERT INTO `".C("DB_MALL.db_prefix").$table."` VALUES  $SQL ");
	}
	$Cache = \Think\Cache::getInstance();
	return $Cache->set($filename,$webdbs);
}

/**
 * 
 * 读取配置缓存文件
 * @param string $filename
 */
function get_config_cache($filename){
	$table = str_replace("@", "_", $filename);
	$arrModel = explode("@", $filename);
	foreach ($arrModel as $m){
		$Model .= ucfirst($m);
	}
	$Model = D($Model);
	$Cache = \Think\Cache::getInstance();
	if (!$Cache->exists($filename)){
		$result = $Model->GetAll();
		foreach ($result as $key => $val){
			$rs[$val['ckey']] = $val['cvalue'];
		}
	}else {
		$rs = $Cache->get($filename);
	}
	return $rs;
}

/**
 * 获取缓存或者更新缓存
 * @param string $config_key 缓存文件名称
 * @param array $data 缓存数据  array('k1'=>'v1','k2'=>'v3')
 * @return array or string or bool
 */
function tpCache($configtable,$agentid,$config_key,$data = array()){
	$param = explode('.', $config_key);
	if(empty($data)){
		//如$config_key=shop_info则获取网站信息数组
		//如$config_key=shop_info.logo则获取网站logo字符串
		$config = F($agentid."_".$param[0],'',TEMP_PATH);//直接获取缓存文件
		if(empty($config)){
			//缓存文件不存在就读取数据库
			$res = D($configtable)->GetAll("ctype='$param[0]' AND `agentid`=".$agentid);
			if($res){
				foreach($res as $k=>$val){
					$config[$val['name']] = $val['value'];
				}
				F($agentid."_".$param[0],$config,TEMP_PATH);
			}
		}
		if(count($param)>1){
			return $config[$param[1]];
		}else{
			return $config;
		}
	}else{
		//更新缓存
		$result =  D($configtable)->GetAll("ctype='$param[0]' AND `agentid`=".$agentid);
		if($result){
			foreach($result as $val){
				$temp[$val['name']] = $val['value'];
			}
			foreach ($data as $k=>$v){
				$newArr = array('name'=>$k,'value'=>trim($v),'ctype'=>$param[0],'agentid'=>$agentid);
				if(!isset($temp[$k])){
					D($configtable)->Add($newArr);//新key数据插入数据库
				}else{
					if($v!=$temp[$k])
						D($configtable)->Edit("name='$k' AND `agentid`=".$agentid,$newArr);//缓存key存在且值有变更新此项
				}
			}
			//更新后的数据库记录
			$newRes = D($configtable)->GetAll("ctype='$param[0]' AND `agentid`=".$agentid);
			foreach ($newRes as $rs){
				$newData[$rs['name']] = $rs['value'];
			}
		}else{
			foreach($data as $k=>$v){
                D($configtable)->Add(array('name'=>$k,'value'=>trim($v),'ctype'=>$param[0],"agentid"=>$agentid));
				$newArr[] = array('name'=>$k,'value'=>trim($v),'ctype'=>$param[0],"agentid"=>$agentid);
			}
			$newData = $data;
		}
		return F($agentid."_".$param[0],$newData,TEMP_PATH);
	}
}

// 递归删除文件夹
function delFile($dir,$file_type='') {
	if(is_dir($dir)){
		$files = scandir($dir);
		//打开目录 //列出目录中的所有文件并去掉 . 和 ..
		foreach($files as $filename){
			if($filename!='.' && $filename!='..'){
				if(!is_dir($dir.'/'.$filename)){
					if(empty($file_type)){
						unlink($dir.'/'.$filename);
					}else{
						if(is_array($file_type)){
							//正则匹配指定文件
							if(preg_match($file_type[0],$filename)){
								unlink($dir.'/'.$filename);
							}
						}else{
							//指定包含某些字符串的文件
							if(false!=stristr($filename,$file_type)){
								unlink($dir.'/'.$filename);
							}
						}
					}
				}else{
					delFile($dir.'/'.$filename);
					rmdir($dir.'/'.$filename);
				}
			}
		}
	}else{
		if(file_exists($dir)) unlink($dir);
	}
}

/**
 * 面包屑导航  用于后台管理
 * 根据当前的控制器名称 和 action 方法
 */
function navigate_admin()
{
	$navigate = include APP_PATH.'Common/Conf/navigate.php';
	$location = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME);
	$arr = array(
			'后台首页'=>'javascript:void();',
			$navigate[$location]['name']=>'javascript:void();',
			$navigate[$location]['action'][ACTION_NAME]=>'javascript:void();',
	);
	return $arr;
}

/**
 *
 * 模板字符串截取
 *$str:要截取的字符串
 *$start=0：开始位置，默认从0开始
 *$length：截取长度
 *$charset=”utf-8″：字符编码，默认UTF－8
 *$suffix=true：是否在截取后的字符后面显示省略号，默认true显示，false为不显示
    */

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)
{
    if(function_exists("mb_substr")){
        if ($suffix && strlen($str)>$length)
            return mb_substr($str, $start, $length, $charset)."...";
        else
            return mb_substr($str, $start, $length, $charset);
    }
    elseif(function_exists('iconv_substr')) {
        if ($suffix && strlen($str)>$length)
            return iconv_substr($str,$start,$length,$charset)."...";
        else
            return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
    $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
    $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
    $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}


// 字符串解密加密
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4; // 随机密钥长度 取值 0-32;
	                  // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
	                  // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
	                  // 当此值为 0 时，则不产生随机密钥
	
	$key = md5 ( $key ? $key : UC_KEY );
	$keya = md5 ( substr ( $key, 0, 16 ) );
	$keyb = md5 ( substr ( $key, 16, 16 ) );
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr ( $string, 0, $ckey_length ) : substr ( md5 ( microtime () ), - $ckey_length )) : '';
	
	$cryptkey = $keya . md5 ( $keya . $keyc );
	$key_length = strlen ( $cryptkey );
	
	$string = $operation == 'DECODE' ? base64_decode ( substr ( $string, $ckey_length ) ) : sprintf ( '%010d', $expiry ? $expiry + time () : 0 ) . substr ( md5 ( $string . $keyb ), 0, 16 ) . $string;
	$string_length = strlen ( $string );
	
	$result = '';
	$box = range ( 0, 255 );
	
	$rndkey = array ();
	for($i = 0; $i <= 255; $i ++) {
		$rndkey [$i] = ord ( $cryptkey [$i % $key_length] );
	}
	
	for($j = $i = 0; $i < 256; $i ++) {
		$j = ($j + $box [$i] + $rndkey [$i]) % 256;
		$tmp = $box [$i];
		$box [$i] = $box [$j];
		$box [$j] = $tmp;
	}
	
	for($a = $j = $i = 0; $i < $string_length; $i ++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box [$a]) % 256;
		$tmp = $box [$a];
		$box [$a] = $box [$j];
		$box [$j] = $tmp;
		$result .= chr ( ord ( $string [$i] ) ^ ($box [($box [$a] + $box [$j]) % 256]) );
	}
	
	if ($operation == 'DECODE') {
		if ((substr ( $result, 0, 10 ) == 0 || substr ( $result, 0, 10 ) - time () > 0) && substr ( $result, 10, 16 ) == substr ( md5 ( substr ( $result, 26 ) . $keyb ), 0, 16 )) {
			return substr ( $result, 26 );
		} else {
			return '';
		}
	} else {
		return $keyc . str_replace ( '=', '', base64_encode ( $result ) );
	}
}

/**
 *计算某个经纬度的周围某段距离的正方形的四个点
 *
 *@param lng float 经度
 *@param lat float 纬度
 *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
 *@return array 正方形的四个点的经纬度坐标
 */
function returnSquarePoint($lng, $lat,$distance = 0.5){
    define(EARTH_RADIUS, 6371);//地球半径，平均半径为6371km
    $dlng = 2 * asin(sin($distance / (2 * EARTH_RADIUS)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);
    $dlat = $distance/EARTH_RADIUS;
    $dlat = rad2deg($dlat);
    return array(
        'left-top'=>array('lat'=>$lat + $dlat,'lng'=>$lng-$dlng),
        'right-top'=>array('lat'=>$lat + $dlat, 'lng'=>$lng + $dlng),
        'left-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng - $dlng),
        'right-bottom'=>array('lat'=>$lat - $dlat, 'lng'=>$lng + $dlng)
    );
}
/**
 * @desc 根据两点间的经纬度计算距离
 * @param float $lat 纬度值
 * @param float $lng 经度值
 */
function getDistance($lat1, $lng1, $lat2, $lng2)
{
    $earthRadius = 6367000; //approximate radius of earth in meters

    /*
    Convert these degrees to radians
    to work with the formula
    */

    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;

    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;

    /*
    Using the
    Haversine formula
    calculate the distance
    */
    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;

    return round($calculatedDistance);
}

/**
 * 根据传入的时间戳，获取历史时间
 * 这个函数的另外一种用法，直接在模板{{时间戳|time}}
 * @access    传入的时间戳
 */
function beforeTime($time)
{
    $limit = time() - $time;
    if($limit<60){

        $time=$_SESSION['LANG']=='en' ? $limit.' seconds ago':$limit.' 秒前';
    }
    if($limit>=60 && $limit<3600){
        $i = floor($limit/60);
        $_i = $limit%60;
        $s = $_i;
        $time=$_SESSION['LANG']=='en' ?  $i.' minutes ago' : $i.' 分钟前 ';
    }

    if($limit>=3600 && $limit<(3600*24)){
        $h = floor($limit/3600);
        $_h = $limit%3600;
        $i = ceil($_h/60);
        $time=$_SESSION['LANG']=='en' ? $h.' hours ago' : $h.' 小时前';
    }
    if($limit>=(3600*24)){
        $time = date('Y-m-d H:i:s',$time);
    }
    return $time;
}

function base_encode($str,$base64 = false) {
    $src  = array("/","+","=");
    $dist = array("_a","_b","_c");
    if($base64==true){
        $old  = base64_encode($str);
        $new  = str_replace($src,$dist,$old);
    }else{
        $new  = str_replace($src,$dist,$str);
    }

    return $new;
}

function base_decode($str,$base64 = false) {
    $src = array("_a","_b","_c");
    $dist  = array("/","+","=");
    if($base64==true){
        $old  = str_replace($src,$dist,$str);
        $new = base64_decode($old);
    }else{
        $new  = str_replace($src,$dist,$str);
    }
    return $new;
}

/**
 * 获取字符串长度
 * @param $str
 * @param string $encoding
 * @return bool|int
 */
function mbstrlen($str,$encoding="utf8")
{

    if (($len = strlen($str)) == 0) {
        return 0;
    }

    $encoding = strtolower($encoding);

    if ($encoding == "utf8" or $encoding == "utf-8") {
        $step = 3;
    } elseif ($encoding == "gbk" or $encoding == "gb2312") {
        $step = 2;
    } else {
        return false;
    }

    $count = 0;
    for ($i=0; $i<$len; $i++) {
        $count++;
        //如果字节码大于127，则根据编码跳几个字节
        if (ord($str{$i}) >= 0x80) {
            $i = $i + $step - 1;//之所以减去1，因为for循环本身还要$i++
        }
    }
    return $count;
}

/**
*加密与解密函数
**/
function mymd5($string,$action="EN",$rand=''){ //字符串加密和解密 
	global $webdb;
	if($action=="DE"){//处理+号在URL传递过程中会异常
		$string = str_replace('QIBO|ADD','+',$string);
	}
    $secret_string = $webdb[mymd5].$rand.'5*j,.^&;?.%#@!'; //绝密字符串,可以任意设定 
	if(!is_string($string)){
		$string=strval($string);
	}
    if($string==="") return ""; 
    if($action=="EN") $md5code=substr(md5($string),8,10); 
    else{ 
        $md5code=substr($string,-10); 
        $string=substr($string,0,strlen($string)-10); 
    }
    //$key = md5($md5code.$_SERVER["HTTP_USER_AGENT"].$secret_string);
	$key = md5($md5code.$secret_string); 
    $string = ($action=="EN"?$string:base64_decode($string)); 
    $len = strlen($key); 
    $code = "";
    for($i=0; $i<strlen($string); $i++){ 
        $k = $i%$len; 
        $code .= $string[$i]^$key[$k]; 
    }
    $code = ($action == "DE" ? (substr(md5($code),8,10)==$md5code?$code:NULL) : base64_encode($code)."$md5code");
	if($action=="EN"){//处理+号在URL传递过程中会异常
		$code = str_replace('+','QIBO|ADD',$code);
	}
    return $code; 
}

function encrypt($data, $key) {
    $prep_code = serialize($data);
    $block = mcrypt_get_block_size('des', 'ecb');
    if (($pad = $block - (strlen($prep_code) % $block)) < $block) {
        $prep_code .= str_repeat(chr($pad), $pad);
    }
    $encrypt = mcrypt_encrypt(MCRYPT_DES, substr(md5($key),0,8), $prep_code, MCRYPT_MODE_ECB);
    return base64_encode($encrypt);
}

function decrypt($str, $key) {
    $str = base64_decode($str);
    $str = mcrypt_decrypt(MCRYPT_DES, substr(md5($key),0,8), $str, MCRYPT_MODE_ECB);
    $block = mcrypt_get_block_size('des', 'ecb');
    $pad = ord($str[($len = strlen($str)) - 1]);
    if ($pad && $pad < $block && preg_match('/' . chr($pad) . '{' . $pad . '}$/', $str)) {
        $str = substr($str, 0, strlen($str) - $pad);
    }
    return unserialize($str);
}

function get_id_val($arr, $key_name,$key_name2)
{
	$arr2 = array();
	foreach($arr as $key => $val){
		$arr2[$val[$key_name]] = $val[$key_name2];
	}
	return $arr2;
}

/**
 * 获取字符串中单个汉字的首字母
 * @param $str
 * @return null|string
 */
function getFirstCharter($str){
	if(empty($str))
	{
		return '';
	}
	$fchar=ord($str{0});
	if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});
	$s1=iconv('UTF-8','gb2312',$str);
	$s2=iconv('gb2312','UTF-8',$s1);
	$s=$s2==$str?$s1:$str;
	$asc=ord($s{0})*256+ord($s{1})-65536;
	if($asc>=-20319&&$asc<=-20284) return 'A';
	if($asc>=-20283&&$asc<=-19776) return 'B';
	if($asc>=-19775&&$asc<=-19219) return 'C';
	if($asc>=-19218&&$asc<=-18711) return 'D';
	if($asc>=-18710&&$asc<=-18527) return 'E';
	if($asc>=-18526&&$asc<=-18240) return 'F';
	if($asc>=-18239&&$asc<=-17923) return 'G';
	if($asc>=-17922&&$asc<=-17418) return 'H';
	if($asc>=-17417&&$asc<=-16475) return 'J';
	if($asc>=-16474&&$asc<=-16213) return 'K';
	if($asc>=-16212&&$asc<=-15641) return 'L';
	if($asc>=-15640&&$asc<=-15166) return 'M';
	if($asc>=-15165&&$asc<=-14923) return 'N';
	if($asc>=-14922&&$asc<=-14915) return 'O';
	if($asc>=-14914&&$asc<=-14631) return 'P';
	if($asc>=-14630&&$asc<=-14150) return 'Q';
	if($asc>=-14149&&$asc<=-14091) return 'R';
	if($asc>=-14090&&$asc<=-13319) return 'S';
	if($asc>=-13318&&$asc<=-12839) return 'T';
	if($asc>=-12838&&$asc<=-12557) return 'W';
	if($asc>=-12556&&$asc<=-11848) return 'X';
	if($asc>=-11847&&$asc<=-11056) return 'Y';
	if($asc>=-11055&&$asc<=-10247) return 'Z';
	return null;
}

/**
 * 将字符串自动转换成UTF-8编码
 * @param $data
 * @return string
 */
function characet($data){
	if( !empty($data) ){
		$fileType = mb_detect_encoding($data , array('UTF-8','GBK','LATIN1','BIG5')) ;
		if( $fileType != 'UTF-8'){
			$data = mb_convert_encoding($data ,'utf-8' , $fileType);
		}
	}
	return $data;
}

/**
 * 将UTF-8编码的字符串转换成单汉字的数组
 * @param $str
 * @param int $l
 * @return array
 */
function str_split_unicode($str, $l = 0) {
	if ($l > 0) {
		$ret = array();
		$len = mb_strlen($str, "UTF-8");
		for ($i = 0; $i < $len; $i += $l) {
			$ret[] = mb_substr($str, $i, $l, "UTF-8");
		}
		return $ret;
	}
	return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

/**
 *   实现中文字串截取无乱码的方法
 */
function getSubstr($string, $start, $length) {
    if(mb_strlen($string,'utf-8')>$length){
        $str = mb_substr($string, $start, $length,'utf-8');
        return $str.'...';
    }else{
        return $string;
    }
}

/**
 * 多个数组的笛卡尔积
 *
 * @param unknown_type $data
 */
function combineDika() {
    $data = func_get_args();
    $data = current($data);
    $cnt = count($data);
    $result = array();
    $arr1 = array_shift($data);
    foreach($arr1 as $key=>$item)
    {
        $result[] = array($item);
    }

    foreach($data as $key=>$item)
    {
        $result = combineArray($result,$item);
    }
    return $result;
}

/**
 * 两个数组的笛卡尔积
 * @param unknown_type $arr1
 * @param unknown_type $arr2
 */
function combineArray($arr1,$arr2) {
    $result = array();
    foreach ($arr1 as $item1)
    {
        foreach ($arr2 as $item2)
        {
            $temp = $item1;
            $temp[] = $item2;
            $result[] = $temp;
        }
    }
    return $result;
}

/**
 * 刷新商品库存, 如果商品有设置规格库存, 则商品总库存 等于 所有规格库存相加
 * @param type $goods_id  商品id
 */
function refresh_stock($goods_id){
    $count = D("GoodsSpecPrice")->GetTotal("goods_id = $goods_id");
    if($count == 0) return false; // 没有使用规格方式 没必要更改总库存

    $store_count = D("GoodsSpecPrice")->GetSum("goods_id =".$goods_id,'store_count');
    D("Goods")->Edit("goods_id=".$goods_id,array('store_count'=>$store_count)); // 更新商品的总库存
}

/**
 * 支付完成修改订单
 * $order_sn 订单号
 * $pay_status 默认1 为已支付
 */
function update_pay_status($order_sn,$pay_status = 1)
{
	if(stripos($order_sn,'recharge_') !== false){
		//用户在线充值
		$count = D('recharge')->where("order_sn = '$order_sn' and pay_status = 0")->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
		if($count == 0) return false;
		$order = M('recharge')->where("order_sn = '$order_sn'")->find();
		M('recharge')->where("order_sn = '$order_sn'")->save(array('pay_status'=>1,'pay_time'=>time()));
		accountLog($order['user_id'],$order['account'],0,'会员在线充值');
	}else{
		// 如果这笔订单已经处理过了
		$count = M('order')->where("order_sn = '$order_sn' and pay_status = 0")->count();   // 看看有没已经处理过这笔订单  支付宝返回不重复处理操作
		if($count == 0) return false;
		// 找出对应的订单
		$order = M('order')->where("order_sn = '$order_sn'")->find();
		// 修改支付状态  已支付
		M('order')->where("order_sn = '$order_sn'")->save(array('pay_status'=>1,'pay_time'=>time()));
		// 减少对应商品的库存
		minus_stock($order['order_id']);
		// 给他升级, 根据order表查看消费记录 给他会员等级升级 修改他的折扣 和 总金额
		update_user_level($order['user_id']);
		// 记录订单操作日志
		logOrder($order['order_id'],'订单付款成功','付款成功',$order['user_id']);
		//分销设置
		M('rebate_log')->where("order_id = {$order['order_id']}")->save(array('status'=>1));
		// 成为分销商条件
		$distribut_condition = tpCache('distribut.condition');
		if($distribut_condition == 1)  // 购买商品付款才可以成为分销商
			M('users')->where("user_id = {$order['user_id']}")->save(array('is_distribut'=>1));
	}

}

/**
 * 根据 order_goods 表扣除商品库存
 * @param type $order_id  订单id
 */
function minus_stock($order_id){
	$orderGoodsArr = M('OrderGoods')->where("order_id = $order_id")->select();
	foreach($orderGoodsArr as $key => $val)
	{
		// 有选择规格的商品
		if(!empty($val['spec_key']))
		{   // 先到规格表里面扣除数量 再重新刷新一个 这件商品的总数量
			M('SpecGoodsPrice')->where("goods_id = {$val['goods_id']} and `key` = '{$val['spec_key']}'")->setDec('store_count',$val['goods_num']);
			refresh_stock($val['goods_id']);
			//更新活动商品购买量
			if($val['prom_type']==1 || $val['prom_type']==2){
				$prom = get_goods_promotion($val['goods_id']);
				if($prom['is_end']==0){
					$tb = $val['prom_type']==1 ? 'flash_sale' : 'group_buy';
					M($tb)->where("id=".$val['prom_id'])->setInc('buy_num',$val['goods_num']);
					M($tb)->where("id=".$val['prom_id'])->setInc('order_num');
				}
			}
		}else{
			M('Goods')->where("goods_id = {$val['goods_id']}")->setDec('store_count',$val['goods_num']); // 直接扣除商品总数量
		}
	}
}

/**
 * 更新会员等级,折扣，消费总额
 * @param $user_id  用户ID
 * @return boolean
 */
function update_user_level($user_id){
    $level_info = M('user_level')->order('level_id')->select();
    $total_amount = M('order')->where("user_id=$user_id AND order_status=2 or order_status=4")->sum('order_amount');
    if($level_info){
        foreach($level_info as $k=>$v){
            if($total_amount > $v['amount']){
                $level = $level_info[$k]['level_id'];
                $discount = $level_info[$k]['discount']/100;
            }
        }
        $user = session('user');
        if(isset($level) && $level>$user['level']){
            $updata = array('level'=>$level,'discount'=>$discount,'total_amount'=>$total_amount);
            M('users')->where("user_id=$user_id")->save($updata);
        }
    }
}

/**
 * 订单操作日志
 * 参数示例
 * @param type $order_id  订单id
 * @param type $action_note 操作备注
 * @param type $status_desc 操作状态  提交订单, 付款成功, 取消, 等待收货, 完成
 * @param type $user_id  用户id 默认为管理员
 * @return boolean
 */
function logOrder($order_id,$action_note,$status_desc,$user_id = 0)
{
    $status_desc_arr = array('提交订单', '付款成功', '取消', '等待收货', '完成','退货');
    // if(!in_array($status_desc, $status_desc_arr))
    // return false;

    $order = M('order')->where("order_id = $order_id")->find();
    $action_info = array(
        'order_id'        =>$order_id,
        'action_user'     =>$user_id,
        'order_status'    =>$order['order_status'],
        'shipping_status' =>$order['shipping_status'],
        'pay_status'      =>$order['pay_status'],
        'action_note'     => $action_note,
        'status_desc'     =>$status_desc, //''
        'log_time'        =>time(),
    );
    return M('order_action')->add($action_info);
}

/**
 * 记录帐户变动
 * @param   int     $user_id        用户id
 * @param   float   $user_money     可用余额变动
 * @param   int     $pay_points     消费积分变动
 * @param   string  $desc    变动说明
 * @param   float   distribut_money 分佣金额
 * @return  bool
 */
function accountLog($user_id, $user_money = 0,$pay_points = 0, $desc = '',$distribut_money = 0){
    /* 插入帐户变动记录 */
    $account_log = array(
        'user_id'       => $user_id,
        'user_money'    => $user_money,
        'pay_points'    => $pay_points,
        'change_time'   => time(),
        'desc'   => $desc,
    );
    /* 更新用户信息 */
    $sql = "UPDATE __PREFIX__users SET user_money = user_money + $user_money," .
        " pay_points = pay_points + $pay_points, distribut_money = distribut_money + $distribut_money WHERE user_id = $user_id";
    if( D('users')->execute($sql)){
        M('account_log')->add($account_log);
        return true;
    }else{
        return false;
    }
}

/**
 * 生成带前缀的流水号
 * @param string $prefix
 * @return string
 */
function GeneralRandSN($prefix="")
{
    send_http_status('310');
    mt_srand((double) microtime() * 1000000);
    return $prefix.date('YmdHi') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}


/**
 * 获得指定分类下的子分类的数组
 *
 * @access  public
 * @param   int     $cat_id     分类的ID
 * @param   int     $selected   当前选中分类的ID
 * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
 * @param   int     $level      限定返回的级数。为0时返回所有级数
 * @return  mix
 */
function article_cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0)
{
	static $res = NULL;

	if ($res === NULL)
	{
		$data = false;//read_static_cache('art_cat_pid_releate');
		if ($data === false)
		{
			$sql = "SELECT c.*, COUNT(s.cat_id) AS has_children FROM __ARTICLE_CATEGORY__ AS c".
					" LEFT JOIN __ARTICLE_CATEGORY__ AS s ON s.parent_id=c.cat_id".
					" GROUP BY c.cat_id  ORDER BY parent_id, sort_order";
			$res = D('ArticleCategory')->RunSql($sql);
			//write_static_cache('art_cat_pid_releate', $res);
		}
		else
		{
			$res = $data;
		}
	}

	if (empty($res) == true)
	{
		return $re_type ? '' : array();
	}

	$options = article_cat_options($cat_id, $res); // 获得指定分类下的子分类的数组

	/* 截取到指定的缩减级别 */
	if ($level > 0)
	{
		if ($cat_id == 0)
		{
			$end_level = $level;
		}
		else
		{
			$first_item = reset($options); // 获取第一个元素
			$end_level  = $first_item['level'] + $level;
		}

		/* 保留level小于end_level的部分 */
		foreach ($options AS $key => $val)
		{
			if ($val['level'] >= $end_level)
			{
				unset($options[$key]);
			}
		}
	}

	$pre_key = 0;
	foreach ($options AS $key => $value)
	{
		$options[$key]['has_children'] = 1;
		if ($pre_key > 0)
		{
			if ($options[$pre_key]['cat_id'] == $options[$key]['parent_id'])
			{
				$options[$pre_key]['has_children'] = 1;
			}
		}
		$pre_key = $key;
	}

	if ($re_type == true)
	{
		$select = '';
		foreach ($options AS $var)
		{
			$select .= '<option value="' . $var['cat_id'] . '" ';
			//$select .= ' cat_type="' . $var['cat_type'] . '" ';
			$select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
			$select .= '>';
			if ($var['level'] > 0)
			{
				$select .= str_repeat('&nbsp;', $var['level'] * 4);
			}
			$select .= htmlspecialchars(addslashes($var['cat_name'])) . '</option>';
		}

		return $select;
	}
	else
	{
		foreach ($options AS $key => $value)
		{
			///$options[$key]['url'] = build_uri('article_cat', array('acid' => $value['cat_id']), $value['cat_name']);
		}
		return $options;
	}
}

/**
 * 过滤和排序所有文章分类，返回一个带有缩进级别的数组
 *
 * @access  private
 * @param   int     $cat_id     上级分类ID
 * @param   array   $arr        含有所有分类的数组
 * @param   int     $level      级别
 * @return  void
 */
function article_cat_options($spec_cat_id, $arr)
{
	static $cat_options = array();

	if (isset($cat_options[$spec_cat_id]))
	{
		return $cat_options[$spec_cat_id];
	}

	if (!isset($cat_options[0]))
	{
		$level = $last_cat_id = 0;
		$options = $cat_id_array = $level_array = array();
		while (!empty($arr))
		{
			foreach ($arr AS $key => $value)
			{
				$cat_id = $value['cat_id'];
				if ($level == 0 && $last_cat_id == 0)
				{
					if ($value['parent_id'] > 0)
					{
						break;
					}

					$options[$cat_id]          = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id']    = $cat_id;
					$options[$cat_id]['name']  = $value['cat_name'];
					unset($arr[$key]);

					if ($value['has_children'] == 0)
					{
						continue;
					}
					$last_cat_id  = $cat_id;
					$cat_id_array = array($cat_id);
					$level_array[$last_cat_id] = ++$level;
					continue;
				}

				if ($value['parent_id'] == $last_cat_id)
				{
					$options[$cat_id]          = $value;
					$options[$cat_id]['level'] = $level;
					$options[$cat_id]['id']    = $cat_id;
					$options[$cat_id]['name']  = $value['cat_name'];
					unset($arr[$key]);

					if ($value['has_children'] > 0)
					{
						if (end($cat_id_array) != $last_cat_id)
						{
							$cat_id_array[] = $last_cat_id;
						}
						$last_cat_id    = $cat_id;
						$cat_id_array[] = $cat_id;
						$level_array[$last_cat_id] = ++$level;
					}
				}
				elseif ($value['parent_id'] > $last_cat_id)
				{
					break;
				}
			}

			$count = count($cat_id_array);
			if ($count > 1)
			{
				$last_cat_id = array_pop($cat_id_array);
			}
			elseif ($count == 1)
			{
				if ($last_cat_id != end($cat_id_array))
				{
					$last_cat_id = end($cat_id_array);
				}
				else
				{
					$level = 0;
					$last_cat_id = 0;
					$cat_id_array = array();
					continue;
				}
			}

			if ($last_cat_id && isset($level_array[$last_cat_id]))
			{
				$level = $level_array[$last_cat_id];
			}
			else
			{
				$level = 0;
			}
		}
		$cat_options[0] = $options;
	}
	else
	{
		$options = $cat_options[0];
	}

	if (!$spec_cat_id)
	{
		return $options;
	}
	else
	{
		if (empty($options[$spec_cat_id]))
		{
			return array();
		}

		$spec_cat_id_level = $options[$spec_cat_id]['level'];

		foreach ($options AS $key => $value)
		{
			if ($key != $spec_cat_id)
			{
				unset($options[$key]);
			}
			else
			{
				break;
			}
		}

		$spec_cat_id_array = array();
		foreach ($options AS $key => $value)
		{
			if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
					($spec_cat_id_level > $value['level']))
			{
				break;
			}
			else
			{
				$spec_cat_id_array[$key] = $value;
			}
		}
		$cat_options[$spec_cat_id] = $spec_cat_id_array;

		return $spec_cat_id_array;
	}
}

//将 xml数据转换为数组格式。
function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}

/**
 *数字金额转换成中文大写金额的函数
 *String Int  $num  要转换的小写数字或小写字符串
 *return 大写字母
 *小数位为两位
 **/
function num_to_rmb($num){
	$c1 = "零壹贰叁肆伍陆柒捌玖";
	$c2 = "分角元拾佰仟万拾佰仟亿";
	//精确到分后面就不要了，所以只留两个小数位
	$num = round($num, 2);
	//将数字转化为整数
	$num = $num * 100;
	if (strlen($num) > 10) {
		return "金额太大，请检查";
	}
	$i = 0;
	$c = "";
	while (1) {
		if ($i == 0) {
			//获取最后一位数字
			$n = substr($num, strlen($num)-1, 1);
		} else {
			$n = $num % 10;
		}
		//每次将最后一位数字转化为中文
		$p1 = substr($c1, 3 * $n, 3);
		$p2 = substr($c2, 3 * $i, 3);
		if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
			$c = $p1 . $p2 . $c;
		} else {
			$c = $p1 . $c;
		}
		$i = $i + 1;
		//去掉数字最后一位了
		$num = $num / 10;
		$num = (int)$num;
		//结束循环
		if ($num == 0) {
			break;
		}
	}
	$j = 0;
	$slen = strlen($c);
	while ($j < $slen) {
		//utf8一个汉字相当3个字符
		$m = substr($c, $j, 6);
		//处理数字中很多0的情况,每次循环去掉一个汉字“零”
		if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
			$left = substr($c, 0, $j);
			$right = substr($c, $j + 3);
			$c = $left . $right;
			$j = $j-3;
			$slen = $slen-3;
		}
		$j = $j + 3;
	}
	//这个是为了去掉类似23.0中最后一个“零”字
	if (substr($c, strlen($c)-3, 3) == '零') {
		$c = substr($c, 0, strlen($c)-3);
	}
	//将处理的汉字加上“整”
	if (empty($c)) {
		return "零元整";
	}else{
		return $c . "整";
	}
}

/**
 *
 * 框架跳转
 * @param unknown_type $link
 * @param unknown_type $msg
 */
function gouri($link="/",$msg=""){
    if (empty($msg)){
        echo "<script type='text/javascript'>window.top.location.href='{$link}'</script>";
    }else {
        echo "<script type='text/javascript'>alert('$msg');window.top.location.href='{$link}'</script>";
    }
}

/**
 *
 * 上传图片到本地服务器
 * @param $subName
 * @param $rootPath
 * @param $savePath
 * @param $maxSize
 * @param $exts
 */
function UploadLocalImage($subName,$rootPath="/Attachment/",$savePath="/face/",$files="",$maxSize=20480000,$exts=array("jpg","gif","png","jpeg","mp4","wav")){
    if(substr($rootPath,0,1) != "/"){
        $rootPath = "/".$rootPath;
    }
    if(substr($rootPath,-1) != "/"){
        $rootPath .= "/";
    }
    if(substr($savePath,0,1) != "/"){
        $savePath = "/".$savePath;
    }
    if(substr($savePath,-1) != "/"){
        $savePath .= "/";
    }
    //上传到本地
    $upload = new \Think\Upload();
    $upload->maxSize   	=  $maxSize ;// 设置附件上传大小
    $upload->exts      	=  $exts;// 设置附件上传类型
    $upload->rootPath 	= ATTACHMENT_PATH;
    $upload->savePath 	= $savePath; // 设置附件上传目录
    $upload->subName 	= $subName;
    $rs = $upload->upload($files);
    if(!empty($rs)) {
        $key = array_keys($rs);//得到上传文件名称
        if(file_exists(ini_get("upload_tmp_dir").DIRECTORY_SEPARATOR . $rs[$key[0]]['savename'])){
            unlink(ini_get("upload_tmp_dir").DIRECTORY_SEPARATOR . $rs[$key[0]]['savename']);
        }
        $return['thumburl'] = SITE_ATTACHMENT_URL . $rs[$key[0]]['savepath'] . $rs[$key[0]]['savename']."?t=".time();
        $return['url'] = SITE_ATTACHMENT_URL . $rs[$key[0]]['savepath'] . $rs[$key[0]]['savename']."?t=".time();
        $return['error'] = false;
        $return['picpath'] = $rs[$key[0]]['savepath'] . $rs[$key[0]]['savename'];
    }else{
        $return['thumburl'] = SITE_ATTACHMENT_URL."/face/default.jpg";
        $return['url'] = SITE_ATTACHMENT_URL."/face/default.jpg";
        $return['picpath'] = "/face/default.jpg";
        $return['error'] = true;
        $return['message'] = $upload->getError();
    }
    //header('Content-Type:application/json; charset=utf-8');
    return $return;
    //exit(json_encode($return));
}

function html_options($arr)
{
    $selected = $arr['selected'];

    if ($arr['options'])
    {
        $options = (array)$arr['options'];
    }
    elseif ($arr['output'])
    {
        if ($arr['values'])
        {
            foreach ($arr['output'] AS $key => $val)
            {
                $options["{$arr[values][$key]}"] = $val;
            }
        }
        else
        {
            $options = array_values((array)$arr['output']);
        }
    }
    if ($options)
    {
        foreach ($options AS $key => $val)
        {
            $key = htmlspecialchars($key);
            $val = strip_tags($val);
            $out .= $key == $selected ? "<option value=\"$key\" selected>$val</option>" : "<option value=\"$key\">$val</option>";
        }
    }

    return $out;
}

/*
 查询购物车中商品的个数
*/
function getNum($sessionName){
    if ($this->getCnt() == 0) {
        //种数为0，个数也为0
        return 0;
    }

    $sum = 0;
    $data = $_SESSION[$sessionName];
    foreach ($data as $item) {
        $sum += $item['num'];
    }
    return $sum;
}

/*
 查询购物车中商品的种类
*/
function getCnt($sessionName) {
    return count($_SESSION[$sessionName]);
}


/*
 查询购物车中商品的总数量
*/
function getCount($sessionName){
    if(count($_SESSION[$sessionName])==0){
        return 0;
    }else{
        $sum = 0;
        $data = $_SESSION[$sessionName];
        foreach ($data as $item) {
            $sum += $item['num'];
        }
        return $sum;
    }
}

/*
 获取单个商品
*/
function getItem($sessionName,$id) {
    return $_SESSION[$sessionName][$id];
}

/*
 清空购物车
*/
function clearCard($sessionName) {
    $_SESSION[$sessionName] = array();
}

/**     参数详情：
 *      $qrcode_path:logo地址
 *      $content:需要生成二维码的内容
 *      $matrixPointSize:二维码尺寸大小
 *      $matrixMarginSize:生成二维码的边距
 *      $errorCorrectionLevel:容错级别
 *      $url:生成的带logo的二维码地址
 * */
function makecode($qrcode_path,$content,$matrixPointSize,$matrixMarginSize,$errorCorrectionLevel,$url){

    ob_clean ();
    Vendor('phpqrcode.phpqrcode');
    $object = new \QRcode();
    $qrcode_path_new = './Public/Home/images/code'.'_'.date("Ymdhis").'.png';//定义生成二维码的路径及名称
    $object::png($content,$qrcode_path_new, $errorCorrectionLevel, $matrixPointSize, $matrixMarginSize);
    $QR = imagecreatefromstring(file_get_contents($qrcode_path_new));//imagecreatefromstring:创建一个图像资源从字符串中的图像流
    $logo = imagecreatefromstring(file_get_contents($qrcode_path));
    $QR_width = imagesx($QR);// 获取图像宽度函数
    $QR_height = imagesy($QR);//获取图像高度函数
    $logo_width = imagesx($logo);// 获取图像宽度函数
    $logo_height = imagesy($logo);//获取图像高度函数
    $logo_qr_width = $QR_width / 4;//logo的宽度
    $scale = $logo_width / $logo_qr_width;//计算比例
    $logo_qr_height = $logo_height / $scale;//计算logo高度
    $from_width = ($QR_width - $logo_qr_width) / 2;//规定logo的坐标位置
    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
    /**     imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
     *      参数详情：
     *      $dst_image:目标图象连接资源。
     *      $src_image:源图象连接资源。
     *      $dst_x:目标 X 坐标点。
     *      $dst_y:目标 Y 坐标点。
     *      $src_x:源的 X 坐标点。
     *      $src_y:源的 Y 坐标点。
     *      $dst_w:目标宽度。
     *      $dst_h:目标高度。
     *      $src_w:源图象的宽度。
     *      $src_h:源图象的高度。
     * */
    Header("Content-type: image/png");
    //$url:定义生成带logo的二维码的地址及名称
    imagepng($QR,$url);
}
function makecode_no_pic($content,$qrcode_path_new,$matrixPointSize,$matrixMarginSize,$errorCorrectionLevel){
    ob_clean ();
    Vendor('phpqrcode.phpqrcode');
    $object = new \QRcode();
    $object::png($content,$qrcode_path_new, $errorCorrectionLevel, $matrixPointSize, $matrixMarginSize);
}
