<?php
/**
 * 文章资讯模块
 */
namespace Admin\Controller;
use Common\Validation;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
use Org\Net\Http;

class ArticleController extends MasterController {

    private $_Model = null;
    private $_CategoryModel = null;
    private $_InfoModel = null;
    private $_CompanyModel = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("Article");
        $this->_CategoryModel = D("ArticleCategory");
        $this->_InfoModel = D("ArticleInfo");
        //$this->_CompanyModel = D("Company");
        parent::__construct();
    }

    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = " 1 ";
        if (isset($_GET['title']) && trim($_GET['title'])){
            $where .= " AND a.`title` LIKE '%".I('title')."%'";
            $params['title'] = I('get.title');
        }
        $total = $this->_Model->GetJoinTotal("a",array('LEFT JOIN __USER__ AS u on u.userid =a.userid','LEFT JOIN __ARTICLE_CATEGORY__ AS c on c.cateid = a.cateid'),$where);
        $rs = $this->_Model->GetJoinList("a",array('LEFT JOIN __USER__ AS u on u.userid = a.userid','LEFT JOIN __ARTICLE_CATEGORY__ AS c on c.cateid = a.cateid'),$where,$page,$this->_rows,array("a.articleid"=>"DESC"),'a.*,u.username,c.name as category');
      
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("articlelist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加资讯
     */
    public function add(){
        if ($_POST){
            $data['cateid'] = intval($_POST['cateid']);
            $data['userid'] =ACPopedom::getID();
            $data['thumb'] = trim($_POST['thumb']);
            $data['title'] = trim($_POST['title']);
            $data['description'] = trim($_POST['description']);
            $data['tags'] = trim($_POST['tags']);
            $data['list'] = intval($_POST['list']);
            $data['fromsite'] = trim($_POST['fromsite']);
            $dataInfo['views'] = intval($_POST['views']);
            $dataInfo['digs'] = intval($_POST['digs']);
            $dataInfo['comments'] = intval($_POST['comments']);
            $dataInfo['detail'] = trim($_POST['content']);
            $data['posttime'] = time();
            $data['edittime'] = time();
            $data['checker'] = ACPopedom::getID();
            $data['checktime'] = time();
            $data['status'] = 1;
            if (empty($data['title'])){
                $this->error("提示：请填写资讯标题");
            }
            if (!($data['cateid'])){
                $this->error("提示：请选择资讯所属分类");
            }
            /*if (empty($data['thumb'])){
                $this->error("提示：请上传缩略图");
            }
            if (empty($data['description'])){
                $this->error("提示：请填写资讯简介");
            }*/
            if (empty($dataInfo['detail'])){
                $this->error("提示：请填写资讯内容");
            }
            if($data['fromsite'] && !Validation::IsUrlAdress($data['fromsite'])){
                $this->error("提示：请填正确的来源网址");
            }
            $this->_Model->startTrans();
            $rs = $this->_Model->Add($data);
            $dataInfo['articleid'] = $rs;
            $rss = $this->_InfoModel->Add($dataInfo);
            if ($rs && $rss){
                $this->_Model->commitTrans();
                $this->success("提示：添加成功",U("/Admin/Article/"));
            }else {
                $this->_Model->rollbackTrans();
                $this->error("提示：添加失败",U("/Admin/Article/add/"));
            }
        }
     
        $catelist = $this->_CategoryModel->GetAll("`isshow`=1");
        $this->assign("catelist",$catelist);
        $this->display();
    }

    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Article/"));
            }
            $where = "a.articleid=".$id;
            $rs = $this->_Model->GetJoinInfo("a",array('LEFT JOIN __ARTICLE_INFO__ AS ai ON ai.articleid = a.articleid'),$where);
            if (empty($rs)){
                $this->error("提示：要修改的记录不存在",U("/Admin/Article/"));
            }
            $data['cateid'] = intval($_POST['cateid']);
            $data['title'] = trim($_POST['title']);
            $data['thumb'] = trim($_POST['thumb']);
            $data['description'] = trim($_POST['description']);
            $data['tags'] = trim($_POST['tags']);
            $data['list'] = intval($_POST['list']);
            $data['fromsite'] = trim($_POST['fromsite']);
            $dataInfo['views'] = intval($_POST['views']);
            $dataInfo['digs'] = intval($_POST['digs']);
            $dataInfo['comments'] = intval($_POST['comments']);
            $dataInfo['detail'] = trim($_POST['content']);
            $data['edittime'] = time();
            if (empty($data['title'])){
                $this->error("提示：请填写资讯标题");
            }
            if (!($data['cateid'])){
                $this->error("提示：请选择资讯所属分类");
            }
            /*if (empty($data['thumb'])){
                $this->error("提示：请上传缩略图");
            }
            if (empty($data['description'])){
                $this->error("提示：请填写资讯简介");
            }*/
            if (empty($dataInfo['detail'])){
                $this->error("提示：请填写资讯内容");
            }
            if($data['fromsite'] && !Validation::IsUrlAdress($data['fromsite'])){
                $this->error("提示：请填正确的来源网址");
            }
            $this->_Model->startTrans();
            $rs = $this->_Model->Edit("`articleid`=".$id,$data);
            $rss = $this->_InfoModel->Edit("`articleid`=".$id,$dataInfo);
            if ($rs && $rss){
                $this->_Model->commitTrans();
                $this->success("提示：修改成功",U("/Admin/Article/"));
            }else {
                $this->_Model->rollbackTrans();
                $this->error("提示：修改失败",U("/Admin/Article/edit/id/{$id}"));
            }

        }else {
            $id = intval($_GET['id']);
            if (!$id) {
                $this->error("提示：请选择要修改的记录", U("/Admin/Article/"));
            }
            $where = "a.articleid=" . $id;
            $rs = $this->_Model->GetJoinInfo("a",array('LEFT JOIN __ARTICLE_INFO__ ai ON ai.articleid = a.articleid'), $where,"a.*,ai.detail,ai.views,ai.digs,ai.comments");
            if (empty($rs)) {
                $this->error("提示：要修改的记录不存在", U("/Admin/Article/"));
            }
       
            $catelist = $this->_CategoryModel->GetAll("`isshow`=1");
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
            $this->error("提示：请选择要删除的记录",U("/Admin/Article/"));
        }
        $where = "articleid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Article/"));
        }
        $rs = $this->_Model->Delete("`articleid`=".$id);
        $rss = $this->_InfoModel->Delete("`articleid`=".$id);
        if ($rs && $rss){
            $this->_Model->commitTrans();
            $this->success("提示：删除成功",U("/Admin/Article/"));
        }else {
            $this->_Model->rollbackTrans();
            $this->error("提示：删除失败",U("/Admin/Article/"));
        }


    }

    /**
     *
     * 审核操作
     */
    public function audit(){
        $id = intval(I("get.id"));
        $data['status'] = intval(I("get.yz"));
        $data['checktime'] = $data['status'] == 1 ? time() : 0 ;
        $data['checker'] = $data['status'] == 1 ? ACPopedom::getID() : 0 ;
        if (!$id) {
            $this->error("提示：请选择要操作的记录",U("/Admin/Article/"));
        }
        $where = "articleid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在",U("/Admin/Article/"));
        }
        $rs = $this->_Model->Edit("`articleid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败",U("/Admin/Article/"));
        }
        $this->success("提示：操作成功",U("/Admin/Article/"));
    }

    /**
     *
     * 置顶操作
     */
    public function top(){
        $id = intval(I("get.id"));
        $data['istop'] = intval(I("get.istop"));
        $data['toptime'] = $data['istop'] == 1 ? time() : 0 ;
        if (!$id) {
            $this->error("提示：请选择要操作的记录",U("/Admin/Article/"));
        }
        $where = "articleid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在",U("/Admin/Article/"));
        }
        $rs = $this->_Model->Edit("`articleid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败",U("/Admin/Article/"));
        }
        $this->success("提示：操作成功",U("/Admin/Article/"));
    }

    /**
     *
     * 推荐操作
     */
    public function recom(){
        $id = intval(I("get.id"));
        $data['isrecom'] = intval(I("get.isrecom"));
        $data['recomtime'] = $data['isrecom'] == 1 ? time() : 0 ;
        if (!$id) {
            $this->error("提示：请选择要操作的记录",U("/Admin/Article/"));
        }
        $where = "articleid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在",U("/Admin/Article/"));
        }
        $rs = $this->_Model->Edit("`articleid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败",U("/Admin/Article/"));
        }
        $this->success("提示：操作成功",U("/Admin/Article/"));
    }

    /**
     *
     * 检测资讯标题是否存在
     */
    public function CheckExists(){
        if(IS_AJAX) {
            $where = "`title` = '" . I('post.title') . "'";
            if (intval(I('post.id'))) {
                $where .= " AND `articleid` != " . intval(I('post.id'));
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