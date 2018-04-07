<?php
defined("API_URL") 				or define("API_URL", "http://api.ht-o.com.cn/");
defined("API_AUTH") 		or define("API_AUTH", "htgy@~!iNTerHT&2015");
function CurlRequst($url, $params = array(), $method = 'GET', $ssl = false,$timeout = 30){
    $opts = array(
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    );
    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url .'?'. http_build_query($params);
            break;
        case 'POST':
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
    }
    if ($ssl) {
        $pemPath = dirname(__FILE__).'/Wechat/';
        $pemCret = $pemPath.'cert.pem';
        $pemKey  = $pemPath.'key.pem';
        if (!file_exists($pemCret)) {
            //$this->error = '证书不存在';
            //return false;
            return array(false, '提示:证书不存在');
        }
        if (!file_exists($pemKey)) {
            //$this->error = '密钥不存在';
            //return false;
            return array(false, '提示:密钥不存在');
        }
        $opts[CURLOPT_SSLCERTTYPE] = 'PEM';
        $opts[CURLOPT_SSLCERT]     = $pemCret;
        $opts[CURLOPT_SSLKEYTYPE]  = 'PEM';
        $opts[CURLOPT_SSLKEY]      = $pemKey;
    }
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg = curl_error($ch);
    curl_close($ch);
    if ($err > 0) {
        //$this->error = $errmsg;
        //return false;
        return array(false, '提示:'.$errmsg);
    }else {
        return $data;
    }
}

function GeneralRandCode($length=6){
    $pattern='1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ'; //字符池
    for($i=0;$i<$length;$i++){
        $key.=$pattern{mt_rand(0,35)};//生成php随机数
    }
    return $key;
}

$tmpname = $_FILES['Filedata']['name'];
$tmpfile = $_FILES['Filedata']['tmp_name'];
$name= $_FILES['Filedata']['name'];
$size =$_FILES['Filedata']['size'];
$info = getimagesize($tmpfile);
$tmpType = $info['mime'];
$rand = GeneralRandCode();
$token = md5($rand.API_AUTH);
$response = json_decode(CurlRequst(API_URL."upload_image",array("rand"=>$rand,"timestamp"=>time(),"token"=>$token,'file'=>'@'.realpath($tmpfile).";type=".$tmpType.";filename=".$tmpname),"POST"),true);
exit($response['response']['picpath']."|".$name."|".$size);