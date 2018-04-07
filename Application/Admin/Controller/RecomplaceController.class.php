<?php
/**
 * 推荐位模块
 */
namespace Admin\Controller;
use Think\Controller\MasterController;
use Think\Page;

class RecomplaceController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("Recomplace");
        parent::__construct();
    }

    /**
     *
     * 推荐位列表
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $recomlist = $this->_Model->GetList($where,$page,$this->_rows,array("placeid"=>"DESC"));
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        foreach ($recomlist as $key => $val){
            $recomlist[$key]['limit'] = $val['shownum'] ? $val['shownum'] : "不限";
        }
        $this->assign('recomlist',$recomlist);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }

    /**
     *
     * 添加推荐位
     */
    public function add(){
        if (IS_POST){
            $data['name'] = I("post.name");
            $data['label'] = I("post.label");
            $data['iconpath'] = I("post.icon");
            $data['list'] = intval(I('post.list'));
            $data['shownum'] = intval(I('post.shownum'));
            $data['posttime'] = time();
            if (empty($data['name'])){
                $this->error("提示：请填写推荐位名称");
            }
            if (empty($data['label'])){
                $this->error("提示：请填写推荐位标签");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."'");
            if ($rs){
                $this->error("提示：该推荐位已经存在，请重新输入推荐位");
            }
            $rs = $this->_Model->GetTotal("`label`='".$data['label']."'");
            if ($rs){
                $this->error("提示：该推荐位标签已经存在，请重新输入推荐位标签");
            }
            $rs = $this->_Model->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Recomplace/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/Recomplace/"));
        }
        $this->display();
    }

    /**
     *
     * 修改推荐位信息
     */
    public function edit(){
        if (IS_POST){
            $id = intval(I("post.id"));
            $data['name'] = I("post.name");
            $data['label'] = I("post.label");
            $data['iconpath'] = I("post.icon");
            $data['list'] = intval(I('post.list'));
            $data['shownum'] = intval(I('post.shownum'));
            if (empty($data['name'])){
                $this->error("提示：请填写推荐位名称");
            }
            if (empty($data['label'])){
                $this->error("提示：请填写推荐位标签");
            }
            $rs = $this->_Model->GetTotal("`name`='".$data['name']."' AND `placeid`<>{$id}");
            if ($rs){
                $this->error("提示：该推荐位已经存在，请重新输入推荐位");
            }
            $rs = $this->_Model->GetTotal("`label`='".$data['label']."' AND `placeid`<>{$id}");
            if ($rs){
                $this->error("提示：该推荐位标签已经存在，请重新输入推荐位标签");
            }
            $rs = $this->_Model->Edit("`placeid`={$id}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/Recomplace/edit/id/{$id}"));
            }
            $this->success("提示：更新成功",U("/Admin/Recomplace/"));
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Recomplace/"));
        }
        $rs = $this->_Model->GetInfo("`placeid`=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Recomplace/"));
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
            $this->error("提示：请选择要删除的记录",U("/Admin/Recomplace/"));
        }
        $rs = $this->_Model->GetInfo("`placeid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Recomplace/"));
        }
        //判断是否有推荐信息
        $rs = D("Recommend")->GetTotal("`placeid`=".$id);
        if ($rs){
            $this->error("提示：该推荐位下有推荐信息，无法删除",U("/Admin/Recomplace/"));
        }
        $rs = $this->_Model->Delete("`placeid`=".$id);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/Recomplace/"));
        }
        $this->success("提示：删除成功",U("/Admin/Recomplace/"));
    }

    /**
     *
     * 检测标签是否存在
     */
    public function CheckLabel(){
        if(IS_AJAX){
            $where = "`label` = '" . I('post.label') . "'";
            if (intval(I('post.id'))){
                $where .= " AND `placeid` != " . intval(I('post.id'));
            }
            $rs = $this->_Model->GetTotal($where);
            if ($rs){
                exit(json_encode(false));
            }
            exit(json_encode(true));
        }else{
            $this->error("提示：无效的请求");
        }
    }

    /**
     *
     * 检测推荐位是否存在
     */
    public function CheckName(){
        if(IS_AJAX) {
            $where = "`name` = '" . I('post.name') . "'";
            if (intval(I('post.id'))) {
                $where .= " AND `placeid` != " . intval(I('post.id'));
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