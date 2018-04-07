<?php
/**
 *
 * 车主端微信授权
 * @author hp
 *
 */
namespace Think\Controller;

use Think\Controller;
use Api\Common\ACPopedom;
use \Tools\Wechat;
use Org\Net\Http;
use Think\App;

class MapiController extends Controller{

    protected $_token = array();

    protected $_user_id = 0;

    protected $_username = "";

    protected $_SessionCart = "c_mbjl";


    // 当前请求类型
    protected   $_method        =   '';
    // 当前请求的资源类型
    protected   $_type          =   '';
    // REST允许的请求类型列表
    protected   $allowMethod    =   array('get','post','put','delete');
    // REST默认请求类型
    protected   $defaultMethod  =   'get';
    // REST允许请求的资源类型列表
    protected   $allowType      =   array('html','xml','json','rss');
    // 默认的资源类型
    protected   $defaultType    =   'html';
    // REST允许输出的资源类型列表
    protected   $allowOutputType=   array(
        'xml' => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    );
    protected $_request = null; //接收的数据

    public function __construct(){
        $session_name = session_name();
        if (isset($_POST[$session_name])){
            session('[pause]');
            session(array('id'=>$_POST[$session_name]));
            session("[start]");
        }
        $this->setAllowOrigin();
        $this->_request = IS_GET ? $_GET : $_POST;
        // 资源类型检测
        if(''==__EXT__) { // 自动检测资源类型
            $this->_type   =  $this->getAcceptType();
        }elseif(!in_array(__EXT__,$this->allowType)) {
            // 资源类型非法 则用默认资源类型访问
            $this->_type   =  $this->defaultType;
        }else{
            $this->_type   =  __EXT__ ;
        }
        // 请求方式检测
        $method  =  strtolower(REQUEST_METHOD);
        if(!in_array($method,$this->allowMethod)) {
            // 请求方式非法 则用默认请求方法
            $method = $this->defaultMethod;
        }
        if(strtolower(REQUEST_METHOD)!="options"){
            $this->_method = $method;
            if($this->_method == "delete" || $this->_method == "put"){
                $this->_request = json_decode(file_get_contents('php://input'),true);
            }
            if(!in_array(strtolower(CONTROLLER_NAME),array("login"))) {
                if(in_array(strtolower(CONTROLLER_NAME)."_".strtolower(ACTION_NAME),array(
                    'address_get_info_list',
                    'address_add',
                    'address_edit',
                    'address_delete',
                    'address_set_default',
                    'cart_get_list',
                    'cart_add',
                    'cart_remove',
                    'cart_delete',
                    'order_get_list',
                    'order_add',
                    'order_delete',
                    'order_get_info',
                    'pay_wechat',
                    'pay_balance',
                    'pay_recharge',
                    'user_get_info',
                    'user_set_account_password',
                    'user_reset_account_password',
                    'user_find_set_password',
                ))) {
                    $http_token = $_SERVER['HTTP_TOKEN'];
                    if (!$http_token) {
                        $this->response(array("obj" => array(), "list" => array(), "status_code" => 800001, "status_msg" => "登录信息有误，请重新登录"), "json", 200);
                    }
                    if (!ACPopedom::checkToken($http_token)) {
                        ACPopedom::refreshToken($http_token);
                        //$this->response(array("obj" => array(), "list" => array(), "status_code" => 800001, "status_msg" => "请重新登录"), "json", 200);
                    }
                }
                //$_http_token = decrypt(base_decode($http_token),C("SESSION_PASSPORT_AUTH"));
                //$_http_token = json_decode($_http_token,true);
                //$this->_token = $_http_token;
                //$this->_user_id = intval($_http_token['userid']);
                //$this->_username = $_http_token['username'];
            }
        }else{
            header("status_code:90001");
        }

        //这里记录请求数据
        parent::__construct();
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args) {
        if( 0 === strcasecmp($method,ACTION_NAME.C('ACTION_SUFFIX'))) {
            if(method_exists($this,$method.'_'.$this->_method.'_'.$this->_type)) { // RESTFul方法支持
                $fun  =  $method.'_'.$this->_method.'_'.$this->_type;
                App::invokeAction($this,$fun);
            }elseif($this->_method == $this->defaultMethod && method_exists($this,$method.'_'.$this->_type) ){
                $fun  =  $method.'_'.$this->_type;
                App::invokeAction($this,$fun);
            }elseif($this->_type == $this->defaultType && method_exists($this,$method.'_'.$this->_method) ){
                $fun  =  $method.'_'.$this->_method;
                App::invokeAction($this,$fun);
            }elseif(method_exists($this,'_empty')) {
                // 如果定义了_empty操作 则调用
                $this->_empty($method,$args);
            }elseif(file_exists_case($this->view->parseTemplate())){
                // 检查是否存在默认模版 如果有直接输出模版
                $this->display();
            }else{
                E(L('_ERROR_ACTION_').':'.ACTION_NAME);
            }
        }
    }

    /**
     * 获取当前请求的Accept头信息
     * @return string
     */
    protected function getAcceptType(){
        $type = array(
            'xml'   =>  'application/xml,text/xml,application/x-xml',
            'json'  =>  'application/json,text/x-json,application/jsonrequest,text/json',
            'js'    =>  'text/javascript,application/javascript,application/x-javascript',
            'css'   =>  'text/css',
            'rss'   =>  'application/rss+xml',
            'yaml'  =>  'application/x-yaml,text/yaml',
            'atom'  =>  'application/atom+xml',
            'pdf'   =>  'application/pdf',
            'text'  =>  'text/plain',
            'png'   =>  'image/png',
            'jpg'   =>  'image/jpg,image/jpeg,image/pjpeg',
            'gif'   =>  'image/gif',
            'csv'   =>  'text/csv',
            'html'  =>  'text/html,application/xhtml+xml,*/*'
        );

        foreach($type as $key=>$val){
            $array   =  explode(',',$val);
            foreach($array as $k=>$v){
                if(stristr($_SERVER['HTTP_ACCEPT'], $v)) {
                    return $key;
                }
            }
        }
        return false;
    }

    // 发送Http状态信息
    protected function sendHttpStatus($code) {
        static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        if(isset($_status[$code])) {
            header('HTTP/1.1 '.$code.' '.$_status[$code]);
            // 确保FastCGI模式下正常
            header('Status:'.$code.' '.$_status[$code]);
        }
    }

    /**
     * 编码数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @return string
     */
    protected function encodeData($data,$type='') {
        if(empty($data))  return '';
        if('json' == $type) {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data);
        }elseif('xml' == $type){
            // 返回xml格式数据
            $data = xml_encode($data);
        }elseif('php'==$type){
            $data = serialize($data);
        }// 默认直接输出
        $this->setContentType($type);
        //header('Content-Length: ' . strlen($data));

        //这里记录返回数据
        return $data;
    }

    /**
     * 设置页面输出的CONTENT_TYPE和编码
     * @access public
     * @param string $type content_type 类型对应的扩展名
     * @param string $charset 页面输出编码
     * @return void
     */
    public function setContentType($type, $charset=''){
        if(headers_sent()) return;
        if(empty($charset))  $charset = C('DEFAULT_CHARSET');
        $type = strtolower($type);
        if(isset($this->allowOutputType[$type])) //过滤content_type
            header('Content-Type: '.$this->allowOutputType[$type].'; charset='.$charset);
    }

    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data,$type='',$code=200) {
        $this->sendHttpStatus($code);
        exit($this->encodeData($data, strtolower($type)));
    }

    /**
     * 允许访问的域名
     */
    protected function setAllowOrigin(){
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS,DELETE,PUT");
        header("Access-Control-Max-Age: 172800");
        header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Token');
        /*if(getenv("RUNTIME_ENVIROMENT") == "DEV"){
            header('Access-Control-Allow-Origin:*');
        }else {
            $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : '';
            static $_allow_origin = array(
                    'http://www.dongcheshixiong.com',
                    'http://sas.dongcheshixiong.com',
                    'http://wx.dongcheshixiong.com'
                );
            //if(in_array($origin,$_allow_origin)){
            //    header('Access-Control-Allow-Origin:'.$origin);
            //}
	    header('Access-Control-Allow-Origin:*');
        }*/
	    header('Access-Control-Allow-Origin:*');
    }

    /**
     * 出错处理函数
     */
    public function _empty(){
        $this->response(array("obj"=>array(),"list"=>array(),"status_code"=>10001,"status_msg"=> "缺少必要的参数"),"json",200);
    }

}
