<?php
/**
 * 会员等级模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
class MemberLevelController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("UserLevel");
        parent::__construct();
    }

    /**
     *
     * 等级列表
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("list"=>"DESC","levelid"=>"DESC"));
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("levellist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加信息
     */
    public function add(){
        if (IS_POST){
            $data['name'] = I("post.name");
            $data['description'] = I("post.description");
            $data['list'] = intval(I('post.list'));
            $data['point'] = intval(I('post.point'));
            $data['experience'] = intval(I('post.experience'));
            if (empty($data['name'])){
                $this->error("提示：请填写等级名称");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."'");
            if ($rs){
                $this->error("提示：该等级名称已经存在，请重新输入等级名称");
            }
            $rs = $this->_Model->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/MemberLevel/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/MemberLevel/"));
        }
        $this->display();
    }

    /**
     *
     * 修改会员等级信息
     */
    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            $data['name'] = I("post.name");
            $data['description'] = I("post.description");
            $data['list'] = intval(I('post.list'));
            $data['point'] = intval(I('post.point'));
            $data['experience'] = intval(I('post.experience'));
            if (empty($data['name'])){
                $this->error("提示：请填写等级名称");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."' AND `levelid`!={$id}");
            if ($rs){
                $this->error("提示：该等级名称已经存在，请重新输入等级名称");
            }
            $rs = $this->_Model->Edit("`levelid`={$id}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/MemberLevel/edit/id/{$id}"));
            }
            $this->success("提示：更新成功",U("/Admin/MemberLevel/"));
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/MemberLevel/"));
        }
        $rs = $this->_Model->GetInfo("`levelid`=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/MemberLevel/"));
        }
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
            $this->error("提示：请选择要删除的记录",U("/Admin/MemberLevel/"));
        }
        $rs = $this->_Model->GetInfo("`levelid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/MemberLevel/"));
        }
        //判断是否会员信息
        $rs = D("UserCenter")->GetTotal("`levelid`=".$id);
        if ($rs){
            $this->error("提示：该会员等级下有会员信息，无法删除",U("/Admin/MemberLevel/"));
        }
        $rs = $this->_Model->Delete("`levelid`=".$id);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/MemberLevel/"));
        }
        $this->success("提示：删除成功",U("/Admin/MemberLevel/"));
    }

    /**
     *
     * 检测会员等级是否存在
     */
    public function CheckExists(){
        if(IS_AJAX) {
            $where = "`name` = '" . I('post.name') . "'";
            if (intval(I('post.id'))) {
                $where .= " AND `levelid` != " . intval(I('post.id'));
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