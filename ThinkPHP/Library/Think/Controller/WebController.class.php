<?php
/**
 * 
 * 后台总控制端
 * @author hp
 *
 */
namespace Think\Controller;

use Think\Controller;
use Store\Common\ACPopedom;

class WebController extends Controller{
    public function __construct(){
        $session_name = session_name();
        if (isset($_POST[$session_name])){
            session('[pause]');
            session(array('id'=>$_POST[$session_name]));
            session("[start]");
        }
        parent::__construct();
    }
}