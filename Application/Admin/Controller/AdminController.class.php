<?php
/**
 * 后台管理员模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
class AdminController extends MasterController {

    private $_Model = null;
    private $_GroupModel = null;
    private $_rows = 15;

    public function __construct(){
        $this->_Model = D("User");
        $this->_GroupModel = D("Group");
        parent::__construct();
    }

    /**
     * 首页
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "u.role_type=0";
        if (isset($_GET['username']) && trim($_GET['username'])){
            $where .= " AND u.`username` LIKE '%".I('get.username')."%'";
            $params['username'] = I('get.username');
        }
        $total = $this->_Model->GetJoinTotal("u",array('LEFT JOIN __GROUP__ AS g ON g.groupid = u.groupid'),$where);
        $rs = $this->_Model->GetJoinList("u",array('LEFT JOIN __GROUP__ AS g ON g.groupid = u.groupid'),$where,$page,$this->_rows,array("u.userid"=>"DESC"),"u.*,g.name as groupname");
        foreach ($rs as $key => $val){
            $rs[$key]['Labelstatus'] = $val['status']==0 ? "<span style='background:none repeat scroll 0 0 #d94a38;border:1px solid #d94a38;color:#fff;padding-left:3px;padding-right: 4px;'>锁定</span>" : "<span style='background:none repeat scroll 0 0 #35aa47;border:1px solid #359947;color:#fff;padding-left:3px;padding-right: 4px;'>正常</span>";
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("masterlist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加
     */
    public function add(){
        if ($_POST){
            $data['username'] = trim($_POST['username']);
            $data['nickname'] = trim($_POST['nickname']);
            $data['groupid'] = intval($_POST['group']);
            $data['email'] =trim($_POST['email']);
            $data['posttime'] = time();
            $data['edittime'] = time();
            $data['role_type'] = 0;
            $data['status'] = 1;
            if (empty($data['nickname'])){
                $this->error("提示：请输入管理员名称");
            }
            if (empty($data['username'])){
                $this->error("提示：请输入管理员登陆账号");
            }
            if (!trim($_POST['password'])){
                $this->error("提示：请输入登陆密码");
            }
            if (trim($_POST['password']) != trim($_POST['repassword'])){
                $this->error("提示：两次输入的密码不一致，请重新输入");
            }
            $salt = GeneralRandCode();
            $data['salt'] = $salt;
            $data['passwd'] = ACPopedom::mixPass(trim($_POST['password']).$salt);
            $rs = $this->_Model->Add($data);
            if ($rs){
                $this->success("提示：添加成功",U("/Admin/Admin/"));
            }else {
                $this->error("提示：添加失败",U("/Admin/Admin/add/"));
            }
        }
        $grouplist = $this->_GroupModel->GetAll();
        $this->assign("grouplist",$grouplist);
        $this->display();
    }

    /**
     *
     * 修改信息
     */
    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Admin/"));
            }
            $data['username'] = trim($_POST['username']);
            $data['nickname'] = trim($_POST['nickname']);
            $data['groupid'] = intval($_POST['group']);
            $data['email'] =trim($_POST['email']);
            if (empty($data['nickname'])){
                $this->error("提示：请输入管理员名称");
            }
            if (empty($data['username'])){
                $this->error("提示：请输入管理员登陆账号");
            }
            if (trim($_POST['password'])){
                if ($data['password'] != $data['repassword']){
                    $this->error("提示：两次输入的密码不一致，请重新输入");
                }
                $salt = GeneralRandCode();
                $data['passwd'] =ACPopedom::mixPass(trim($_POST['password']).$salt);
                $data['salt'] = $salt;
            }
            $rs = $this->_Model->Edit("`userid`=".$id." AND role_type=0",$data);
            if ($rs){
                $this->success("提示：修改成功",U("/Admin/Admin/"));
            }else {
                $this->error("提示：修改失败",U("/Admin/Admin/edit/id/{$id}"));
            }
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Admin/"));
        }
        $rs = $this->_Model->GetJoinInfo("u",array('LEFT JOIN __GROUP__ AS g ON g.groupid = u.groupid'),"u.userid=".$id." AND u.`role_type`=0");
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Admin/"));
        }
        $grouplist = $this->_GroupModel->GetAll();
        $this->assign("grouplist",$grouplist);
        $this->assign("info",$rs);
        $this->display();
    }

    /**
     *
     * 删除信息
     */
    public function delete(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录",U("/Admin/Admin/"));
        }
        $rs = $this->_Model->GetInfo("`userid`=".$id." AND `role_type`=0");
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Admin/"));
        }
        if(intval($rs['issuper'])==1){
            $this->error("提示：超级管理员无法删除",U("/Admin/Admin/"));
        }
        $rs = $this->_Model->Delete("`userid`=".$id." AND `role_type`=0");
        if ($rs){
            $this->success("提示：删除成功",U("/Admin/Admin/"));
        }else {
            $this->error("提示：删除失败",U("/Admin/Admin/"));
        }


    }

    /**
     *
     * 锁操作
     */
    public function lock(){
        $id = intval(I("get.id"));
        $data['status'] = intval(I("get.lock"));
        if (!$id) {
            $this->error("提示：请选择要操作的记录",U("/Admin/Admin/"));
        }
        $rs = $this->_Model->GetInfo("`userid`=".$id." AND `role_type`=0");
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在",U("/Admin/Admin/"));
        }
        $rs = $this->_Model->Edit("`userid`={$id} AND `role_type`=0",$data);
        if (!$rs) {
            $this->error("提示：操作失败",U("/Admin/Admin/"));
        }
        $this->success("提示：操作成功",U("/Admin/Admin/"));
    }

    /**
     *
     * 检测管理员是否存在
     */
    public function CheckExists(){
        if(IS_AJAX) {
            $where = "`username` = '" . I('post.username') . "' AND `role_type`=0";
            if (intval(I('post.id'))) {
                $where .= " AND `userid` != " . intval(I('post.id'));
            }
            $rs = $this->_Model->GetTotal($where);
            if ($rs) {
                exit(json_encode(false));
            }
            exit(json_encode(true));
        }else{
            $this->error("提示：无效的请求");
        }
    }
}