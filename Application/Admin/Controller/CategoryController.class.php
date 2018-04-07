<?php
/**
 * 文章资讯分类模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
class CategoryController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("ArticleCategory");
        parent::__construct();
    }

    /**
     *
     * 资讯分类列表
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "`pid`=0";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("list"=>"DESC","cateid"=>"DESC"));
        foreach ($rs as $key => $val){
            $subcate = $this->_Model->GetAll("`pid`=".$val['cateid'],"*",array("list"=>"DESC","cateid"=>"DESC"));
            $val['showname'] = $val['isshow'] ? "显示" : "隐藏";
            $result[$val['cateid']] = $val;
            foreach ($subcate as $kk => &$vv){
                $vv['name'] = "&nbsp;&nbsp;&nbsp;|-----".$vv['name'];
                $vv['showname'] = $vv['isshow'] ? "显示" : "隐藏";
                $result[$vv['cateid']] = $vv;
            }
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("catelist",$result);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加资讯分类信息
     */
    public function add(){
        if (IS_POST){
            $data['name'] = I("post.name");
            $data['pid'] = intval(I("post.pid"));
            $data['description'] = I("post.description");
            $data['list'] = intval(I('post.list'));
            $data['isshow'] = intval(I('post.isshow'));
            $data['posttime'] = time();
            $data['edittime'] = time();
            if (empty($data['name'])){
                $this->error("提示：请填写分类名称");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."'");
            if ($rs){
                $this->error("提示：该分类已经存在，请重新输入分类名称");
            }
            $rs = $this->_Model->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Category/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/Category/"));
        }
        $catelist = $this->_Model->GetAll("`pid`=0 AND `isshow`=1");
        $this->assign("catelist",$catelist);
        $this->display();
    }

    /**
     *
     * 修改分类信息
     */
    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            $where = "cateid={$id}";
            $rs = $this->_Model->GetInfo($where);
            if (empty($rs)){
                $this->error("提示：要修改的记录不存在",U("/Admin/Category/"));
            }
            $data['name'] = I("post.name");
            $data['pid'] = intval(I("post.pid"));
            $data['description'] = I("post.description");
            $data['list'] = intval(I('post.list'));
            $data['isshow'] = intval(I('post.isshow'));
            $data['edittime'] = time();
            if (empty($data['name'])){
                $this->error("提示：请填写分类名称");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."' AND `cateid`!={$id}");
            if ($rs){
                $this->error("提示：该分类已经存在，请重新输入分类名称");
            }
            $rs = $this->_Model->Edit("`cateid`={$id}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/Category/edit/id/{$id}"));
            }
            $this->success("提示：更新成功",U("/Admin/Category/"));
        }else {
            $id = intval($_GET['id']);
            if (!$id) {
                $this->error("提示：请选择要修改的记录", U("/Admin/Category/"));
            }
            $where = "cateid={$id}";
            $rs = $this->_Model->GetInfo($where);
            if (empty($rs)) {
                $this->error("提示：要修改的记录不存在", U("/Admin/Category/"));
            }
            $catelist = $this->_Model->GetAll("`pid`=0 AND `isshow`=1");
            $this->assign("catelist", $catelist);
            $this->assign("info", $rs);
            $this->display();
        }
    }

    /**
     *
     * 删除信息
     */
    public function delete(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录",U("/Admin/Category/"));
        }
        $where = "cateid={$id}";
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Category/"));
        }
        $rs = $this->_Model->Delete("`cateid`=".$id);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/Category/"));
        }
        $this->success("提示：删除成功",U("/Admin/Category/"));
    }

    /**
     *
     * 检测分类是否存在
     */
    public function CheckExists(){
        if(IS_AJAX) {
            $where = "`name` = '" . I('post.name') . "'";
            if (intval(I('post.id'))) {
                $where .= " AND `cateid` != " . intval(I('post.id'));
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