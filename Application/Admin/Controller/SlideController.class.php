<?php
/**
 * 滚动图模块
 */
namespace Admin\Controller;
use Think\Controller\MasterController;
use Think\Page;
use Common\Validation;

class SlideController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("Slide");
        parent::__construct();
    }

    /**
     *
     * 幻灯片管理
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "1";
        if (isset($_GET['title']) && trim($_GET['title'])){
            $where .= " AND `title` LIKE '%".I('get.title')."%'";
            $params['title'] = I('get.title');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("slid"=>"DESC"));
        foreach($rs as $key => $val){
            $rs[$key]['showname'] = $val['isshow']==1 ? "显示" : "隐藏";
            $val['imagepath'] && $rs[$key]['imageurl'] = SITE_ATTACHMENT_URL.$val['imagepath'];
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("slidelist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加
     */
    public function add(){
        if (IS_POST){
            $data['title'] = trim(I("post.title"));
            $data['imagepath'] = trim(I("post.imagepath"));
            $data['linkurl'] = trim(I("post.linkurl"));
            $data['list'] = intval(I('post.list'));
            $data['isshow'] = intval(I('post.isshow'));
            $data['posttime'] = time();
            if (empty($data['title'])){
                $this->error("提示：请填写幻灯片标题");
            }
            if (empty($data['imagepath'])){
                $this->error("提示：请上传幻灯片图片");
            }
            if (empty($data['linkurl'])){
                $this->error("提示：请填写幻灯片链接地址");
            }
            if(!Validation::IsUrlAdress($data['linkurl'])){
                $this->error("提示：请填写正确的幻灯片链接地址");
            }
            $rs = $this->_Model->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Slide/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/Slide/"));
        }
        $this->display();
    }

    /**
     *
     * 修改非法关键词
     */
    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            $data['title'] = trim(I("post.title"));
            $data['imagepath'] = trim(I("post.imagepath"));
            $data['linkurl'] = trim(I("post.linkurl"));
            $data['list'] = intval(I('post.list'));
            $data['isshow'] = intval(I('post.isshow'));
            if (empty($data['title'])){
                $this->error("提示：请填写幻灯片标题");
            }
            if (empty($data['imagepath'])){
                $this->error("提示：请上传幻灯片图片");
            }
            if (empty($data['linkurl'])){
                $this->error("提示：请填写幻灯片链接地址");
            }
            if(!Validation::IsUrlAdress($data['linkurl'])){
                $this->error("提示：请填写正确的幻灯片链接地址");
            }
            $rs = $this->_Model->Edit("`slid`={$id}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/Slide/edit/id/{$id}"));
            }
            $this->success("提示：更新成功",U("/Admin/Slide/"));
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Slide/"));
        }
        $rs = $this->_Model->GetInfo("`slid`=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Slide/"));
        }
        $this->assign("info",$rs);
        $this->display();
    }

    /**
     *
     * 隐藏/显示操作
     */
    public function set(){
        $id = intval(I("get.id"));
        $data['isshow'] = intval(I("get.status"));
        if (!$id) {
            $this->error("提示：请选择要操作的记录",U("/Admin/Slide/"));
        }
        $rs = $this->_Model->GetInfo("`slid`=".$id);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在",U("/Admin/Slide/"));
        }
        $rs = $this->_Model->Edit("`slid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败",U("/Admin/Slide/"));
        }
        $this->success("提示：操作成功",U("/Admin/Slide/"));
    }

    /**
     *
     * 删除非法关键词
     */
    public function delete(){
        $id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录",U("/Admin/Slide/"));
        }
        $rs = $this->_Model->GetInfo("`slid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Slide/"));
        }
        $rs = $this->_Model->Delete("`slid`=".$id);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/Slide/"));
        }
        $this->success("提示：删除成功",U("/Admin/Slide/"));
    }
}