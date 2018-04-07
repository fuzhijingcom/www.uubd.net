<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/5
 * Time: 22:05
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;

class ConfigController extends MasterController {
    /**
     * 首页
     */
    public function index(){
        if (IS_POST){
            $rs = write_config_cache("system@config", I("post.webdbs"));
            if(true == $rs) {
                $this->success("提示：设置成功",U("/Admin/Config/"));
            }else {
                $this->error("提示：设置失败",U("/Admin/Config/"));
            }
        }
        $rs = get_config_cache("system@config");
		if(empty($rs) || $rs && empty($rs['account_name'])){
			$rs['account_name'] = "平台币";
		}
        $this->assign("rs",$rs);
        $this->display();
    }

    /**
     *
     * 修改密码
     */
    public function pwd(){
        if (IS_POST){
            if (!I("post.oldpwd")){
                $this->error("提示：请输入原始密码");
            }
            $rs = D("User")->GetInfo("`userid`=".ACPopedom::getID());
            if (empty($rs)){
                $this->error("提示：用户信息不存在");
            }
            if ($rs['passwd'] != ACPopedom::mixPass(trim(I("post.oldpwd")).$rs['salt'])){
                $this->error("提示：原始密码不正确");
            }
            if (!I("post.pwd")){
                $this->error("提示：请输入新密码");
            }
            if (trim(I("post.pwd")) != trim(I("post.repwd"))){
                $this->error("提示：两次输入的密码不一致，请重新输入");
            }
            $salt = GeneralRandCode();
            $rs = D("User")->Edit("`userid`=".$rs['userid'],array('passwd'=>ACPopedom::mixPass(trim(I("post.pwd")).$salt),"salt"=>$salt));
            if (!$rs){
                $this->error("提示：密码更新失败");
            }
            $this->success("提示：密码更新成功，请妥善保管好新密码");
        }
        $this->display();
    }

    /**
     *
     * 检测原始密码是否正确
     */
    public function CheckPwd(){
        if(IS_AJAX){
            $rs = D("User")->GetInfo("`userid`=".ACPopedom::getID());
            if (empty($rs)){
                exit(json_encode(false));
            }
            if ($rs['passwd'] != ACPopedom::mixPass(trim(I("post.oldpwd")).$rs['salt'])){
                exit(json_encode(false));
            }
            exit(json_encode(true));
        }else{
            $this->error("提示：无效的请求");
        }
    }
}