<?php
/**
 * 广告位模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
class AdPlaceController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("AdPlace");
        parent::__construct();
    }

    /**
     *
     * 广告位列表
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("list"=>"DESC","placeid"=>"DESC"));
        foreach ($rs as $key => $val){
            $rs [$key] ['showname'] = $val['isshow'] ? "显示" : "隐藏";
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("adplacelist",$rs);
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
            $data['label'] = I("post.label");
            $data['description'] = I("post.description");
            $data['list'] = intval(I('post.list'));
            $data['isshow'] = intval(I('post.isshow'));
            $data['edittime'] = time();
            if (empty($data['name'])){
                $this->error("提示：请填写分类名称");
            }
            $rs = $this->_Model->GetTotal("`label`='".$data['label']."'");
            if ($rs){
                $this->error("提示：该广告位位标签已经存在，请重新输入广告位标签");
            }
            $rs = $this->_Model->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/AdPlace/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/AdPlace/"));
        }
        $this->display();
    }

    /**
     *
     * 修改分类信息
     */
    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            $data['name'] = I("post.name");
            $data['label'] = I("post.label");
            $data['description'] = I("post.description");
            $data['list'] = intval(I('post.list'));
            $data['isshow'] = intval(I('post.isshow'));
            $data['edittime'] = time();
            if (empty($data['name'])){
                $this->error("提示：请填写分类名称");
            }
            $rs = $this->_Model->GetTotal("`label`='".$data['label']."' AND `placeid`<>{$id}");
            if ($rs){
                $this->error("提示：该广告位位标签已经存在，请重新输入广告位标签");
            }
            $rs = $this->_Model->Edit("`placeid`={$id}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/AdPlace/edit/id/{$id}"));
            }
            $this->success("提示：更新成功",U("/Admin/AdPlace/"));
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/AdPlace/"));
        }
        $rs = $this->_Model->GetInfo("`placeid`=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/AdPlace/"));
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
            $this->error("提示：请选择要删除的记录",U("/Admin/AdPlace/"));
        }
        $rs = $this->_Model->GetInfo("`placeid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/AdPlace/"));
        }
        //判断是否有推荐信息
        $rs = D("Ad")->GetTotal("`placeid`=".$id);
        if ($rs){
            $this->error("提示：该广告位下面有广告信息，无法删除",U("/Admin/AdPlace/"));
        }
        $rs = $this->_Model->Delete("`placeid`=".$id);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/AdPlace/"));
        }
        $this->success("提示：删除成功",U("/Admin/AdPlace/"));
    }

    /**
     *
     * 检测标签是否存在
     */
    public function CheckExists(){
        if(IS_AJAX) {
            $where = "`label` = '" . I('post.label') . "'";
            if (intval(I('post.id'))) {
                $where .= " AND `placeid` !=" . intval(I('post.id'));
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