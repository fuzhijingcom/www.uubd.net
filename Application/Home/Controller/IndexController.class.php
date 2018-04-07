<?php
namespace Home\Controller;
use Think\Controller;


class IndexController extends Controller {
    //主页
    /**
     *
     */
    public function index()
    {
        $userinfo = session('userinfo');
        $map['user_id'] = $userinfo['user_id'];
        $User = new \Home\Model\UserModel();
        $userinfo = $User -> getUserInfo($map);

        if(!$userinfo){
            //不存在
            redirect("/home/index/login");
        }

        $rmpmap['r_id'] = array('in', array($userinfo['user_id']));
        $RoleMenuPackage = new \Home\Model\RoleMenuPackageModel();
        $roleMenuPackages = $RoleMenuPackage->getRoleMenuPackages($rmpmap);

        if ($roleMenuPackages) {
            $mids = Array();
            foreach ($roleMenuPackages as $rmp => $roleMenuPackage) {
                $mids[] = $roleMenuPackage['m_id'];
            }
            $mmap['m_id'] = array('in', $mids);
            $sort = 'm_order asc';
            $Menu = new \Home\Model\MenuModel();
            $menus = $Menu->getMenus($mmap, $sort);
        }
       
        $this->assign('userinfo', $userinfo);
        $this->assign('menus', $menus);
        $this->display();
    }

    public function login(){
        $userinfo = session('userinfo');
        $map['user_id'] = $userinfo['user_id'];
        $User = new \Home\Model\UserModel();
        $userinfo = $User -> getUserInfo($map);

        if($userinfo){
            //存在
            redirect('/Home/Index/index', 0, '登录成功,页面跳转中...');
        }

        if(IS_POST){
            $name = I('post.name','','trim');
            $pwd = I('post.pwd','','trim');

            if($name === "" || $pwd === ""){
                $this->error("账号或密码不能为空",'/Home/Index/login');
            }
            //获取用户密码
            $hash_pwd = $User->hasUser($name);
            if(!$hash_pwd){
                $this->error("用户不存在",'/Home/Index/login');
            }
            if(password_verify($pwd,$hash_pwd)){
                $userinfo = $User->getUser($name);
                session('userinfo',$userinfo);
                redirect('/Home/Index/index', 0, '登录成功,页面跳转中...');
            }else{
                $this->error("账号或密码错误",'/Home/Index/login');
            }

        }


        $this->display();
    }

    public function logout(){
        session(null);
        $this->success("退出成功",'/Home/Index/login');
    }
}