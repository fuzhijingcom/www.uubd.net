<?php
/**
 * 登录模块
 */
namespace Admin\Controller;
use Think\Controller;
use Think\Verify;
use Admin\Common\ACPopedom;

/**
 * 
 * 后台登陆模块
 * @author HP
 *
 */
class LoginController extends Controller{
	
	/**
	 * 
	 * 登陆页面
	 */
	public function index(){
        if(ACPopedom::isLogin()){
            $this->success("提示：已经登录，跳转中...",$_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : U("/"));
        }
		$this->assign("SystemName",SystemName);
		$this->display();
	}

    public function Captcha(){
        $verity = new Verify();
        $verity->length = 4;
        $verity->fontSize = 25;
        $verity->entry();
    }
	
	/**
	 * 
	 * 登陆处理
	 */
	public function Login(){
		$username = (I('post.username'));
		$password = (I('post.password'));
        $captcha= I('post.captcha');
        $verity = new Verify();
        if(!$verity->check($captcha)) {
            $this->error('验证码不正确', U('Admin/Login/index'), 2);
        }
		$result = ACPopedom::login($username, $password);
		if(true == $result['status']){
			$this->success($result['msg'],U('Admin/Index/index'),1);
		}else{
			$this->error($result['msg'],U('Admin/Login/index'),2);
		}
	}
	/**
	 *
	 * 退出
	 */
	public function logout(){
		ACPopedom::logout();
		gouri("/Admin/");
	}
}
?>