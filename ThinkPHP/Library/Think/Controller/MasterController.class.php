<?php
/**
 * 
 * 后台总控制端
 * @author hp
 *
 */
namespace Think\Controller;

use Think\Controller;
use Admin\Common\ACPopedom;

class MasterController extends Controller{
    public function __construct(){
        $session_name = session_name();
        if (isset($_POST[$session_name])){
            session('[pause]');
            session(array('id'=>$_POST[$session_name]));
            session("[start]");
        }
        if("login" != strtolower(CONTROLLER_NAME)){
            ACPopedom::check();
        }
        parent::__construct();
    }
}