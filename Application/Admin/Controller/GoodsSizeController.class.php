<?php
/**
 * 商品尺寸模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
class GoodsSizeController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("GoodsSize");
        parent::__construct();
    }

    /**
     *
     * 商品颜色列表
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "`is_delete`=0";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("list"=>"DESC","size_id"=>"DESC"));
        foreach ($rs as $key => $val){
            $rs [$key] ['showname'] = $val['is_show'] ? "显示" : "隐藏";
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("infolist",$rs);
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
            $data['list'] = intval(I('post.list')) ? intval(I('post.list')) : 50;
            $data['is_show'] = intval(I('post.isshow'));
            if (empty($data['name'])){
                $this->error("提示：请填写尺寸名称");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."'");
            if ($rs){
                $this->error("提示：商品尺寸已经存在，请重新填写");
            }
            $rs = $this->_Model->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/GoodsSize/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/GoodsSize/"));
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
            $data['list'] = intval(I('post.list')) ? intval(I('post.list')) : 50;
            $data['is_show'] = intval(I('post.isshow'));
            if (empty($data['name'])){
                $this->error("提示：请填写尺寸名称");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."' AND `size_id`!={$id}");
            if ($rs){
                $this->error("提示：商品尺寸已经存在，请重新填写");
            }
            $rs = $this->_Model->Edit("`size_id`={$id}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/GoodsSize/edit/id/{$id}"));
            }
            $this->success("提示：更新成功",U("/Admin/GoodsSize/"));
        }else {
            $id = intval($_GET['id']);
            if (!$id) {
                $this->error("提示：请选择要修改的记录", U("/Admin/GoodsSize/"));
            }
            $rs = $this->_Model->GetInfo("`size_id`=" . $id);
            if (empty($rs)) {
                $this->error("提示：要修改的记录不存在", U("/Admin/GoodsSize/"));
            }
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
            $this->error("提示：请选择要删除的记录",U("/Admin/GoodsSize/"));
        }
        $rs = $this->_Model->GetInfo("`size_id`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/GoodsSize/"));
        }
        $rs = $this->_Model->Edit("`color_id`=".$id,array("is_delete"=>1));
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/GoodsSize/"));
        }
        $this->success("提示：删除成功",U("/Admin/GoodsSize/"));
    }
}