<?php
/**
 * 用户组模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
class GroupController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("Group");
        parent::__construct();
    }

    /**
     *
     * 权限组列表
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $rows = 12;
        $where = "1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("groupid"=>"DESC"));
        $pageobj = new Page($total,$rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("grouplist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加权限组
     */
    public function add(){
        if (IS_POST){
            foreach ($_POST ['role'] as $key => $val){
                $tmp = explode("_", $val);
                if (isset($tmp[2])){
                    $_POST ['role'] [ $tmp[0] . "_" . $tmp[1] . "_" . $tmp[2] ] = $tmp [0] . "_" . $tmp[1] . "_" . $tmp[2] ;
                }
                if (isset($tmp[1])){
                    $_POST ['role'] [ $tmp[0] . "_" . $tmp[1] ]  = $tmp [0] . "_" . $tmp[1] ;
                }
                $_POST ['role'] [ $tmp[0] ] = $tmp [0] ;
            }
            $data['popedom'] = serialize($_POST['role']);
            $data['name'] = I("post.name");
            $data['description'] = I("post.description");
            if (empty($data['name'])){
                $this->error("提示：请填写权限组名称");
            }
            $rs = $this->_Model->Add($data);

            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Group/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/Group/"));
        }
        $this->assign("menulist",C("MENU"));//得到操作菜单
        $this->display();
    }

    /**
     *
     * 修改权限组
     */
    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Group/edit/"));
            }
            $data['name'] = I("post.name");
            $data['description'] = I("post.description");
            if (empty($data['name'])){
                $this->error("提示：请填写权限组名称");
            }
            $rs = $this->_Model->Edit("`groupid`={$id}",$data);
            if (!$rs){
                $this->error("提示：修改失败",U("/Admin/Group/edit/id/{$id}"));
            }
            $this->success("提示：修改成功",U("/Admin/Group/"));
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Group/"));
        }
        $rs = $this->_Model->GetInfo("`groupid`=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Group/"));
        }
        $this->assign("info",$rs);
        $this->display();
    }

    /**
     *
     * 删除权限组
     */
    public function delete(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录",U("/Admin/Group/"));
        }
        $rs = $this->_Model->GetInfo("`groupid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Group/"));
        }
        //判断是否有用户属于该用户组
        $rs = D("Admin")->GetTotal("`groupid`=".$id);
        if ($rs){
            $this->error("提示：有用户属于该用户组，无法删除",U("/Admin/Group/"));
        }
        $rs = $this->_Model->Delete("`groupid`=".$id);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/Group/"));
        }
        $this->success("提示：删除成功",U("/Admin/Group/"));
    }

    /**
     *
     * 权限操作
     */
    public function popedom(){
        if (IS_POST){
            foreach ($_POST ['role'] as $key => $val){
                $tmp = explode("_", $val);
                if (isset($tmp[2])){
                    $_POST ['role'] [ $tmp[0] . "_" . $tmp[1] . "_" . $tmp[2] ] = $tmp [0] . "_" . $tmp[1] . "_" . $tmp[2] ;
                }
                if (isset($tmp[1])){
                    $_POST ['role'] [ $tmp[0] . "_" . $tmp[1] ]  = $tmp [0] . "_" . $tmp[1] ;
                }
                $_POST ['role'] [ $tmp[0] ] = $tmp [0] ;
            }
            $data['popedom'] = serialize($_POST['role']);
            $rs = $this->_Model->Edit("`groupid`=".intval(I("get.id")),$data);
            if (!$rs){
                $this->error("提示：权限设置失败",U("/Admin/Group/popedom/id/".intval(I("get.id"))));
            }
            $this->success("提示：权限设置成功",U("/Admin/Group/"));
        }
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要操作的记录",U("/Admin/Group/"));
        }
        $rs = $this->_Model->GetInfo("`groupid`=".$id);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在",U("/Admin/Group/"));
        }
        $popedom = array_values(unserialize($rs['popedom']));
        $this->assign("menulist",C("MENU"));//得到操作菜单
        $this->assign("popedom",$popedom);
        $this->display();
    }
}