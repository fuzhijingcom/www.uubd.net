<?php
/**
 * 广告模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
use Common\Validation;
use Org\Net\Http;
class AdController extends MasterController {

    private $_Model = null;
    private $_AdPlaceModel = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("Ad");
        $this->_AdPlaceModel = D("AdPlace");
        parent::__construct();
    }

    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "1";
        if (isset($_GET['title']) && trim($_GET['title'])){
            $where .= " AND `title` LIKE '%".I('title')."%'";
            $params['title'] = I('get.title');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("adid"=>"DESC"));
        foreach ($rs as $key => $val){
            $rs[$key]['showname'] = $val['isshow']==1 ? "显示" : "隐藏";
            $adplace = $this->_AdPlaceModel->GetInfo("`placeid`=".$val['placeid']);//所属分类信息
            $rs [$key] ['place'] = $adplace ['name'];
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("adlist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加广告
     */
    public function add(){
        if ($_POST){
            $data['placeid'] = intval($_POST['placeid']);
            $data['adtype'] = intval($_POST['adtype']);
            $data['userid'] = ACPopedom::getID();
            $data['picpath'] = $data['adtype']==0 ? I('post.picpath') : "";
            $data['title'] = trim($_POST['title']);
            $data['linkurl'] = I('post.linkurl');
            $data['content'] = $data['adtype']==1 ? trim(I('post.content')) : "";
            $data['description'] = trim($_POST['description']);
            $data['list'] = intval($_POST['list']);
            $data['isshow'] = intval(I('post.isshow'));
            $data['begintime'] = I('post.begintime') ? strtotime(I('post.begintime')) : 0;
            $data['endtime'] = I('post.endtime') ? strtotime(I('post.endtime')) : 0;
            $data['posttime'] = time();
            $data['edittime'] = time();
            if (empty($data['title'])){
                $this->error("提示：请填写广告标题");
            }
            if (!($data['placeid'])){
                $this->error("提示：请选择广告位");
            }
            if (empty($data['linkurl'])){
                $this->error("提示：请填写广告链接地址");
            }
            if (!Validation::IsUrlAdress($data['linkurl'])){
                $this->error("提示：请填写有效的链接地址");
            }
            if ($data['adtype'] && empty($data['content'])){
                $this->error("提示：请填写广告内容");
            }
            if (!$data['adtype'] && empty($data['picpath'])){
                $this->error("提示：请选择广告图片");
            }
            $rs = $this->_Model->Add($data);
            if ($rs){
                $this->success("提示：添加成功",U("/Admin/Ad/"));
            }else {
                $this->error("提示：添加失败",U("/Admin/Ad/add/"));
            }
        }
        $adplacelist = $this->_AdPlaceModel->GetAll("`isshow`=1");
        $this->assign("adplacelist",$adplacelist);
        $this->display();
    }

    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Ad/"));
            }
            $where = "`adid`=" . $id;
            $rs = $this->_Model->GetInfo($where);
            if (empty($rs)) {
                $this->error("提示：要修改的记录不存在", U("/Ad/"));
            }
            $data['title'] = trim($_POST['title']);
            $data['adtype'] = intval($_POST['adtype']);
            $data['placeid'] = intval($_POST['placeid']);
            $data['list'] = intval($_POST['list']);
            $data['description'] = trim($_POST['description']);
            $data['picpath'] = $data['adtype']==0 ? I('post.picpath') : "";
            $data['edittime'] = time();
            $data['content'] = $data['adtype']==1 ? trim(I('post.content')) : "";
            $data['begintime'] = I('post.begintime') ? strtotime(I('post.begintime')) : 0;
            $data['endtime'] = I('post.endtime') ? strtotime(I('post.endtime')) : 0;
            $data['linkurl'] = I('post.linkurl');
            $data['isshow'] = intval(I('post.isshow'));
            if (empty($data['title'])){
                $this->error("提示：请填写广告标题");
            }
            if (!($data['placeid'])){
                $this->error("提示：请选择广告位");
            }
            if (!Validation::IsUrlAdress($data['linkurl'])){
                $this->error("提示：请填写有效的链接地址");
            }
            if ($data['adtype'] && empty($data['content'])){
                $this->error("提示：请填写广告内容");
            }
            if (!$data['adtype'] && empty($data['picpath'])){
                $this->error("提示：请选择广告图片");
            }
            $rs = $this->_Model->Edit("`adid`=".$id,$data);
            if ($rs){
                $this->success("提示：修改成功",U("/Admin/Ad/"));
            }else {
                $this->error("提示：修改失败",U("/Admin/Ad/edit/id/{$id}"));
            }

        }else {
            $id = intval($_GET['id']);
            if (!$id) {
                $this->error("提示：请选择要修改的记录", U("/Admin/Ad/"));
            }
            $where = "`adid`=" . $id;
            $rs = $this->_Model->GetInfo($where);
            if (empty($rs)) {
                $this->error("提示：要修改的记录不存在", U("/Admin/Ad/"));
            }
            $adplacelist = $this->_AdPlaceModel->GetAll("`isshow`=1");
            $this->assign("adplacelist", $adplacelist);
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
            $this->error("提示：请选择要删除的记录",U("/Admin/Ad/"));
        }
        $where = "`adid`=" . $id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Ad/"));
        }
        $rs = $this->_Model->Delete("`adid`=".$id);
        if ($rs){
            $this->success("提示：删除成功",U("/Admin/Ad/"));
        }else {
            $this->error("提示：删除失败",U("/Admin/Ad/"));
        }


    }

    /**
     *
     * 检测资讯标题是否存在
     */
    public function CheckExists(){
        if(IS_AJAX) {
            $where = "`title` = '" . I('post.title') . "'";
            if (intval(I('post.id'))) {
                $where .= " AND `adid` != " . intval(I('post.id'));
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