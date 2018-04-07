<?php
namespace Admin\Controller;
use Think\Controller\MasterController;
use Admin\Common\ACPopedom;
use Think\Page;
use \Tools\Tree;

class GoodsCategoryController extends MasterController {
	
	private $_Model = null;
    private $_rows = 20;
	private $_max_layer = 4;
	private $_nav_map = array("一级分类","二级分类","三级分类","四级分类");

    public function __construct(){
        $this->_Model = D("GoodsCategory");
        parent::__construct();
    }
	
	/**
	* 商品分类列表
	*/
    public function index(){
		$pid = intval(I("pid"));
		$where = "1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `catename` LIKE '%".I('get.name')."%'";
            $params["catename"] = I('get.name');
        }
        $order = array("list"=>"DESC","cateid"=>"DESC");
        $rs = $this->_Model->GetAll($where,$order);
        foreach ($rs as $key => $val){
            $rs[$key]['show_label'] = $val['ifshow'] == 1 ? "显示" : "隐藏";
        }
        $this->assign("infolist",$rs);
        $this->display();
    }
	
	/**
	* 添加商品分类
	*/
	public function add(){
		if(IS_POST){
            $data['catename'] = trim($_POST['catename']);
            $data['parentid'] = intval($_POST['parentid']);
            $data['list'] = intval($_POST['list']);
            $data['ifshow'] = intval($_POST['ifshow']);
            $data['storeid'] = ACPopedom::getID();
            $data['imgpath'] = "";
			$info = $this->_Model->GetTotal("`cateid`=".$data['parentid']);
			$data['level'] = intval($info['level'])+1;
            if (empty($data['catename'])){
                $this->error("提示：请输入商品分类名称");
            }
			$rs = $this->_Model->GetTotal("`catename`='".$data['catename']."'");
            if ($rs) {
                $this->error("提示：此商品分类名称已经存在");
            }
			$rs = $this->_Model->Add($data);
			if ($rs){
				$this->success("提示：添加成功",U("/Admin/GoodsCategory/index"));
			}else {
				$this->error("提示：添加失败",U("/Admin/GoodsCategory/add/"));
			}
		}
		$parentid = intval(I("id"));
		//得到分类信息
		$catelist = $this->_get_options();
		$this->assign("options",html_options(array("options"=>$catelist,"selected"=>$parentid)));
		$this->display();
	}
	
	/**
	* 修改商品分类
	*/
	public function edit(){
		if(IS_POST){
			$id = intval(I("id"));
            $data['catename'] = trim($_POST['catename']);
            $data['parentid'] = intval($_POST['parentid']);
            $data['list'] = intval($_POST['list']);
            $data['ifshow'] = intval($_POST['ifshow']);
            $data['storeid'] = ACPopedom::getID();
            $data['imgpath'] = "";
			$info = $this->_Model->GetTotal("`cateid`=".$data['parentid']);
			$data['level'] = intval($info['level'])+1;
            if (empty($data['catename'])){
                $this->error("提示：请输入商品分类名称");
            }
			$rs = $this->_Model->GetTotal("`catename`='".$data['catename']."' AND `cateid`!=".$id);
            if ($rs) {
                $this->error("提示：此商品分类名称已经存在");
            }
			$rs = $this->_Model->Edit("`cateid`=".$id,$data);
			if ($rs){
				$this->success("提示：更新成功",U("/Admin/GoodsCategory/index"));
			}else {
				$this->error("提示：更新失败",U("/Admin/GoodsCategory/edit/"));
			}
		}
		$id = intval(I("id"));
		$info = $this->_Model->GetInfo("`cateid`='".$id."'");
		if(empty($info)){
			$this->error("提示：商品分类信息不存在");
		}
		//得到分类信息
		$catelist = $this->_get_options($id);
		$this->assign("options",html_options(array("options"=>$catelist,"selected"=>$info['parentid'])));
		$this->assign("info",$info);
		$this->display();
	}
	
	/**
	* 删除商品分类
	*/
	public function delete(){
		$id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录");
        }
        $rs = $this->_Model->GetInfo("`cateid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在");
        }
        $rs = $this->_Model->Delete("`cateid`=".$id);
        if ($rs){
            $this->success("提示：删除成功");
        }else {
            $this->error("提示：删除失败");
        }
	}
	
	/**
     *
     * 检测商品分类是否存在
     */
    public function CheckExists(){
        if(IS_AJAX) {
            $where = "`catename` = '" . I('post.catename') . "'";
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
	
	/* 构造并返回树 */
    private function _tree($gcategories)
    {
        import("Vendor.Tree.Tree");
        $tree = new Tree();
        $tree->setTree($gcategories, 'cateid', 'parentid', 'catename');
        return $tree;
    }

    /* 取得可以作为上级的商品分类数据 */
    private function _get_options($except = NULL)
    {
        $gcategories = $this->_Model->GetAll("1",array("list"=>"DESC","cateid"=>"DESC"));
        $tree = $this->_tree($gcategories);
        return $tree->getOptions($this->_max_layer - 1, 0, $except);
    }
}