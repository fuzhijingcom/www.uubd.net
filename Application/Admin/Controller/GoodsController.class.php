<?php
namespace Admin\Controller;
use Think\Controller\MasterController;
use Admin\Common\ACPopedom;
use Think\Page;
use \Tools\Tree;
class GoodsController extends MasterController {
	
	private $_Model = null;
	private $_CategoryModel = null;
	private $_InfoModel = null;
	private $_GoodsAttacmentModel = null;
    private $_rows = 20;
	private $_max_layer = 4;

    public function __construct(){
        $this->_Model = D("Goods");
		$this->_CategoryModel = D("GoodsCategory");
		$this->_InfoModel = D("GoodsInfo");
		$this->_GoodsAttacmentModel = D("GoodsAttachment");
        parent::__construct();
    }
	
	/**
	* 商品列表
	*/
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
		$where = "g.is_drop = 0";
		if(!trim(I("title"))){
			$where .= " AND g.title LIKE '%".trim(I('title'))."%'";
            $params['title'] = trim(I('title'));
		}
		if(!trim(I("catename"))){
			$where .= " AND c.catename LIKE '%".trim(I('catename'))."%'";
            $params['catename'] = trim(I('catename'));
		}
        $order = array("posttime"=>"DESC");
		$total = $this->_Model->GetJoinTotal("g",array('LEFT JOIN __GOODS_CATEGORY__ AS c ON g.cateid = c.cateid'),$where);
        $rs = $this->_Model->GetJoinList("g",array('LEFT JOIN __GOODS_CATEGORY__ AS c ON g.cateid = c.cateid'),$where,$page,$this->_rows,$order,"g.*,c.catename");
		foreach($rs as $key => $val){
			$rs[$key]['show_label'] = $val['status']==1 ? "通过审核" : "待审核";
			$rs[$key]['sell_label'] = $val['is_sell']==1 ? "上架" : "下架";
			$rs[$key]['qrcode'] = SITE_ATTACHMENT_URL."/qrcode/code_".$val['goodsid'].".png";
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
	* 商品回收站列表
	*/
	public function recycle(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
		$where = "g.is_drop = 1";
        if(!trim(I("title"))){
            $where .= " AND g.title LIKE '%".trim(I('title'))."%'";
            $params['title'] = trim(I('title'));
        }
        if(!trim(I("catename"))){
            $where .= " AND c.catename LIKE '%".trim(I('catename'))."%'";
            $params['catename'] = trim(I('catename'));
        }
        $order = array("posttime"=>"DESC");
		$total = $this->_Model->GetJoinTotal("g",array('LEFT JOIN __GOODS_CATEGORY__ AS c ON g.cateid = c.cateid'),$where);
        $rs = $this->_Model->GetJoinList("g",array('LEFT JOIN __GOODS_CATEGORY__ AS c ON g.cateid = c.cateid'),$where,$page,$this->_rows,$order,"g.*,c.catename");
		foreach($rs as $key => $val){
			$rs[$key]['show_label'] = $val['status']==1 ? "通过审核" : "待审核";
			$rs[$key]['sell_label'] = $val['is_sell']==1 ? "上架" : "下架";
            $rs[$key]['qrcode'] = SITE_ATTACHMENT_URL."/qrcode/code_".$val['goodsid'].".png";
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
	* 添加商品
	*/
	public function add(){
		if(IS_POST){
		    $size = $_POST['size'];
		    $color = $_POST['color'];
		    if($size){
		        $data['size'] = json_encode($size);
            }else{
		        $data['size'] = "";
            }
            if($color){
		        $data['color'] = json_encode($color);
            }else{
                $data['color'] = "";
            }
			$data['cateid'] = intval(I('cateid'));
			$data['userid'] = ACPopedom::getID();
			$data['title'] = trim(I('title'));
			$data['description'] = trim(I('description'));
			$data['price'] = floatval(I('price'));
			$data['goods_sn'] = trim(I("goods_sn"));
            $data['goods_code'] = trim(I("goods_code"));
			//$data['market_price'] = floatval(I('mprice'));
			$data['tags'] = trim(I('tags'));
			$data['module_ids'] = empty($_POST['moduleid']) ? "" : implode(",",$_POST['moduleid']);
			$data['inventory'] = intval(I('kucun'));
			$data['list'] = intval(I('list'));
			$data['istop'] = 0;
			$data['toptime'] = 0;
			$data['isrecom'] = 0;
			$data['recomtime'] = 0;
			$data['edittime'] = time();
			$data['posttime'] = time();
			$data['checker'] = ACPopedom::getID();
			$data['checktime'] = time();
			$data['status'] = 1;
			$data['is_sell'] = intval(I('ifsell'));
			$data['is_drop'] = 0;
			$this->_Model->startTrans();
			$goodsid = $this->_Model->Add($data);
			$datainfo['goodsid'] = $goodsid;
			$datainfo['detail'] = I("content");
			$datainfo['views'] = 0;
			$datainfo['digs'] = 0;
			$datainfo['comments'] = 0;
            $SQL = "";
			foreach($_POST['img']['name'] AS $key=>$value)
			{
				if($_POST['img']['url'][$key]){
					if(intval($_POST['is_default'])==$key){
						$default = 1;
					}else{
						$default = 0;
					}
					$SQL.="(".$goodsid.",'".$value."', '".$_POST['img']['url'][$key]."',".$default."),";
				}
			}
			if($SQL){
				$SQL=substr($SQL,0,-1).";";
				$SQL=str_Replace("'),;","')",$SQL);
				$rss = $this->_Model->ExecuteSql("INSERT INTO __GOODS_ATTACHMENT__ (`goodsid`,`name`,`picpath`,`is_default`) VALUES $SQL");
			}else{
				$rss = true;
			}
            $rs = $this->_InfoModel->Add($datainfo);
			if($goodsid && $rs && $rss){
				$this->_Model->commitTrans();
                $qrcode_path_new = './Attachment/qrcode/code'.'_'.$goodsid.'.png';
                $content = $goodsid;
                $matrixPointSize = 30;
                $matrixMarginSize = 1;
                $errorCorrectionLevel = "L";
                makecode_no_pic($content,$qrcode_path_new,$matrixPointSize,$matrixMarginSize,$errorCorrectionLevel);
				$this->success("提示：添加商品成功",U("/Admin/Goods"));
			}else{
				$this->_Model->rollbackTrans();
				$this->error("提示：添加商品失败");
			}
		}else{
		    //得到商品颜色
            $colorlist = D("GoodsColor")->GetAll("`is_delete`=0 AND `is_show`=1");
            //得到商品尺寸
            $sizelist = D("GoodsSize")->GetAll("`is_delete`=0 AND `is_show`=1");
			//得到分类信息
			$catelist = $this->_get_options();
			$this->assign("picnum",0);
			$this->assign("options",html_options(array("options"=>$catelist,"selected"=>0)));
			$this->assign("colorlist",$colorlist);
			$this->assign("sizelist",$sizelist);
			$this->display();
		}
	}
	
	/**
	* 修改商品
	*/
	public function edit(){
		if(IS_POST){
			$id = intval(I('id'));
			if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Goods/"));
            }
            $info = $this->_Model->GetInfo("`goodsid`=".$id);
            if (empty($info)){
                $this->error("提示：要操作的记录不存在",U("/Goods/"));
            }
            $size = $_POST['size'];
            $color = $_POST['color'];
            if($size){
                $data['size'] = json_encode($size);
            }else{
                $data['size'] = "";
            }
            if($color){
                $data['color'] = json_encode($color);
            }else{
                $data['color'] = "";
            }
			$data['cateid'] = intval(I('cateid'));
			$data['title'] = trim(I('title'));
			$data['description'] = trim(I('description'));
			$data['price'] = floatval(I('price'));
            $data['goods_sn'] = trim(I("goods_sn"));
            $data['goods_code'] = trim(I("goods_code"));
			//$data['market_price'] = floatval(I('mprice'));
			$data['tags'] = trim(I('tags'));
			$data['module_ids'] = empty($_POST['moduleid']) ? "" : implode(",",$_POST['moduleid']);
			$data['inventory'] = intval(I('kucun'));
			$data['list'] = intval(I('list'));
			$data['edittime'] = time();
			$data['is_sell'] = intval(I('ifsell'));
			$this->_Model->startTrans();
			$rs = $this->_Model->Edit("`goodsid`=".$id,$data);
			$datainfo['detail'] = I("content");
            $SQL = "";
			foreach($_POST['img']['name'] AS $key=>$value)
			{
				if($_POST['img']['url'][$key]){
					if(intval($_POST['is_default'])==$key){
						$default = 1;
					}else{
						$default = 0;
					}
					$SQL.="(".$id.",'".$value."', '".$_POST['img']['url'][$key]."',".$default."),";
				}
			}
			$rr = $this->_GoodsAttacmentModel->Delete("`goodsid`=".$id);
			if($SQL){
				$SQL=substr($SQL,0,-1).";";
				$rss = $this->_Model->ExecuteSql("INSERT INTO __GOODS_ATTACHMENT__ (`goodsid`,`name`,`picpath`,`is_default`) VALUES $SQL");
			}else{
				$rss = true;
			}
            $rrs = $this->_InfoModel->Edit("`goodsid`=".$id,$datainfo);
			if($rs  && $rr && $rrs && $rss){
				$this->_Model->commitTrans();
                $qrcode_path_new = './Attachment/qrcode/code'.'_'.$id.'.png';
                $content = $id;
                $matrixPointSize = 30;
                $matrixMarginSize = 1;
                $errorCorrectionLevel = "L";
                makecode_no_pic($content,$qrcode_path_new,$matrixPointSize,$matrixMarginSize,$errorCorrectionLevel);
				$this->success("提示：更新商品信息成功",U("/Admin/Goods"));
			}else{
				$this->_Model->rollbackTrans();
				$this->error("提示：更新商品信息失败");
			}
		}else {
            $id = intval(I("id"));
            $info = $this->_Model->GetJoinInfo("g", array("LEFT JOIN __GOODS_INFO__ gf ON g.goodsid=gf.goodsid"), "g.`goodsid`=" . $id, "g.*,gf.*");
            if (empty($info)) {
                $this->error("提示：商品信息不存在");
            }
            //得到商品颜色
            $colorlist = D("GoodsColor")->GetAll("`is_delete`=0 AND `is_show`=1");
            $array_color = json_decode($info['color'],true);
            foreach ($colorlist as $key => $color){
                if(in_array($color['color_id'],$array_color)){
                    $colorlist[$key]['checked'] = "checked='checked'";
                }
            }
            //得到商品尺寸
            $sizelist = D("GoodsSize")->GetAll("`is_delete`=0 AND `is_show`=1");
            $array_size = json_decode($info['size'],true);
            foreach ($sizelist as $key => $size){
                if(in_array($size['size_id'],$array_size)){
                    $sizelist[$key]['checked'] = "checked='checked'";
                }
            }
            //得到商品图片信息
            $attachmentlist = $this->_GoodsAttacmentModel->GetAll("`goodsid`=" . $id, array("goodsid" => "DESC"));
            //得到分类信息
            $catelist = $this->_get_options();
            $this->assign("options", html_options(array("options" => $catelist, "selected" => $info['cateid'])));
            $this->assign("info", $info);
            $this->assign("picnum", count($attachmentlist));
            $this->assign("attachmentlist", $attachmentlist);
            $this->assign("colorlist",$colorlist);
            $this->assign("sizelist",$sizelist);
            $this->display();
        }
	}
	
	/**
	* 删除商品
	*/
	public function delete(){
		$id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录");
        }
        $rs = $this->_Model->GetInfo("`goodsid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在");
        }
        $rs = $this->_Model->Edit("`goodsid`=".$id,array("is_drop"=>1));
        if ($rs){
            $this->success("提示：删除成功，商品已进入商品回收站");
        }else {
            $this->error("提示：删除失败");
        }
	}
	
	/**
	* 将回收站中的商品返回到商品列表
	*/
	public function restore(){
		$id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录");
        }
        $rs = $this->_Model->GetInfo("`goodsid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在");
        }
        $rs = $this->_Model->Edit("`goodsid`=".$id,array("is_drop"=>0));
        if ($rs){
            $this->success("提示：操作成功，商品已从商品回收站进入商品列表");
        }else {
            $this->error("提示：操作失败");
        }
	}
	
	/**
	* 彻底删除商品
	*/
	public function remove(){
		$id = intval(I("get.id"));
        if (!$id) {
            $this->error("提示：请选择要删除的记录");
        }
        $rs = $this->_Model->GetInfo("`goodsid`=".$id);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在");
        }
        $rs = $this->_Model->Delete("`goodsid`=".$id);
        if ($rs){
            $this->success("提示：删除成功");
        }else {
            $this->error("提示：删除失败");
        }
	}
	
	/**
     *
     * 上下架操作
     */
    public function sales(){
        $id = intval(I("get.id"));
        $data['is_sell'] = intval(I("get.sell"));
        if (!$id) {
            $this->error("提示：请选择要操作的记录");
        }
        $where = "goodsid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在");
        }
        $rs = $this->_Model->Edit("`goodsid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败");
        }
        $this->success("提示：操作成功");
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
            $this->error("提示：请选择要操作的记录");
        }
        $where = "goodsid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在");
        }
        $rs = $this->_Model->Edit("`goodsid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败");
        }
        $this->success("提示：操作成功");
    }
	
	/**
	* 推荐商品
	*/
	public function recom(){
		$id = intval(I("get.id"));
        $data['isrecom'] = intval(I("get.isrecom"));
        $data['recomtime'] = $data['isrecom'] == 1 ? time() : 0 ;
        if (!$id) {
            $this->error("提示：请选择要操作的记录");
        }
        $where = "goodsid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在");
        }
        $rs = $this->_Model->Edit("`goodsid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败");
        }
        $this->success("提示：操作成功");
	}
	
	/**
	* 置顶商品
	*/
	public function top(){
		$id = intval(I("get.id"));
        $data['istop'] = intval(I("get.istop"));
        $data['toptime'] = $data['istop'] == 1 ? time() : 0 ;
        if (!$id) {
            $this->error("提示：请选择要操作的记录");
        }
        $where = "goodsid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在");
        }
        $rs = $this->_Model->Edit("`goodsid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败");
        }
        $this->success("提示：操作成功");
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
        $gcategories = $this->_CategoryModel->GetAll("`ifshow`=1",array("list"=>"DESC","cateid"=>"DESC"));
        $tree = $this->_tree($gcategories);
        return $tree->getOptions($this->_max_layer - 1, 0, $except);
    }
	
	public function swfuploadxml(){
		header("Content-type: application/xml");
		$filetype = I("filetype");
		$string='';
		$detail=explode("|",$filetype);
		foreach($detail AS $key=>$value){
			if($value){
				$string.="<items>$value</items>\r\n";
			}
		}
		$uploadMax=intval(ini_get('upload_max_filesize')?ini_get('upload_max_filesize'):'2');
		$str=str_replace('+','%2B',mymd5(ACPopedom::getID()));
		$xml = '<?xml version="1.0" encoding="utf-8"?>';
		$xml .= '<sapload>';
		$xml .= '<config>';
		$xml .= '<upLoadUrl>http://www.mbjl.com/upload.php</upLoadUrl>';
		$xml .= '<maxNum>100</maxNum>';
		$xml .= '<upMaxbig>'.$uploadMax.'</upMaxbig>';
		$xml .= '<fileType>';
		$xml .= $string;
		$xml .= '</fileType>';
		$xml .= '<arguments>';
		$xml .= '<items atr="str">'.$str.'</items>';
		$xml .= '</arguments>';
		$xml .= '</config>';
		$xml .= '</sapload>';
		echo $xml;
	}
	
	/**
	* 获取分类模型
	*/
	public function get_module(){
		if(IS_AJAX) {
            $cateid = intval(I('post.cateid', 0));
			$goodsid = intval(I('post.goodsid', 0));
            if (!$cateid) {
                $data['data'] = array();
                $data['count'] = 0;
                $data['message'] = "提示：无效的分类信息，请求失败";
                $data['status'] = false;
                $this->ajaxReturn($data);
            }
			if($goodsid){
				$goodsinfo = $this->_Model->GetInfo("`goodsid`=".$goodsid);
				$goods_module = $goodsinfo['module_ids'] ? explode(",",$goodsinfo['module_ids']) : array();
			}
			$info = D("GoodsModule")->GetAll("`cateid`=".$cateid);
			if(!empty($info)){
				$html = "";
				foreach($info as $key => $val){
					$config = D("ModuleConfig")->GetAll("`moduleid`=".$val['moduleid']);
					if(!empty($config)){
						$html .= '<tr class="tr-module-item">';
						$html .= '<td class="td_right">'.$val['name'].'：</td>';
						$html .= '<td class="">';
						
						foreach($config as $k => $v){
							$checked = in_array($v['configid'],$goods_module) ? 'checked="checked"' : "";
							$html .= '<input type="checkbox" name="moduleid[]" '.$checked.' id="module_'.$v['configid'].'" value="'.$v['configid'].'"><label for="module_'.$v['configid'].'">'.$v['ckey'].'</label>';
						}
						$html .= '</td>';
						$html .= '</tr>';
					}
				}
			}
            $data['data'] = $html;
            $data['count'] = D("GoodsModule")->GetTotal("`cateid`=".$cateid);
            $data['message'] = "提示：请求成功";
            $data['status'] = true;
        }else{
            $data['data'] = array();
            $data['count'] = 0;
            $data['message'] = "提示：请求失败";
            $data['status'] = false;
        }
        $this->ajaxReturn($data);
	}
	
	/**
	* 设置推广销量
	*/
	public function set_sales(){
		if(IS_POST){
			$id = intval(I('id'));
			if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Goods/"));
            }
            $info = $this->_Model->GetInfo("`goodsid`=".$id);
            if (empty($info)){
                $this->error("提示：要操作的记录不存在",U("/Admin/Goods/"));
            }
			$data['tg_sales'] = intval(I('sales'));
			
			$rs = $this->_Model->Edit("`goodsid`=".$id,$data);
			
			if($rs){
				$this->success("提示：设置商品推广销量信息成功",U("/Admin/Goods"));
			}else{
				$this->error("提示：设置商品推广销量信息失败");
			}
		}else{
			$id = intval(I("get.id"));
			if (!$id) {
				$this->error("提示：请选择要操作的记录");
			}
			$info = $this->_Model->GetJoinInfo("g",array("LEFT JOIN __GOODS_INFO__ gf ON g.goodsid=gf.goodsid"),"g.`goodsid`=".$id,array("g.posttime"=>"DESC"));
			if(empty($info)){
				$this->error("提示：商品信息不存在");
			}
			$this->assign("info",$info);
			$this->display("sales");
		}
	}
	
	/**
     *
     * 开启/关闭推广销量显示
     */
    public function set_tg(){
        $id = intval(I("get.id"));
        $data['is_tg'] = intval(I("get.tg"));
        if (!$id) {
            $this->error("提示：请选择要操作的记录");
        }
        $where = "goodsid=".$id;
        $rs = $this->_Model->GetInfo($where);
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在");
        }
        $rs = $this->_Model->Edit("`goodsid`={$id}",$data);
        if (!$rs) {
            $this->error("提示：操作失败");
        }
        $this->success("提示：操作成功");
    }

    public function get_goods_list(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "g.is_drop = 0 AND g.`status`=1 AND g.`is_sell`=1";
        if(!trim(I("title"))){
            $where .= " AND g.title LIKE '%".trim(I('title'))."%'";
            $params['title'] = trim(I('title'));
        }
        if(!trim(I("catename"))){
            $where .= " AND c.catename LIKE '%".trim(I('catename'))."%'";
            $params['catename'] = trim(I('catename'));
        }
        $order = array("posttime"=>"DESC");
        $total = $this->_Model->GetJoinTotal("g",array('LEFT JOIN __GOODS_CATEGORY__ AS c ON g.cateid = c.cateid'),$where);
        $rs = $this->_Model->GetJoinList("g",array('LEFT JOIN __GOODS_CATEGORY__ AS c ON g.cateid = c.cateid'),$where,$page,$this->_rows,$order,"g.*,c.catename");
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("infolist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display("goods_list");
    }
}