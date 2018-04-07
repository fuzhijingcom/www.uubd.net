<?php
/**
 * 优惠券控制器
 */
namespace Admin\Controller;
use Think\Controller\MasterController;
use Admin\Common\ACPopedom;
use Think\Page;

class CouponController extends MasterController {
	
	private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("Coupon");
        parent::__construct();
    }
	
	/**
	* 优惠券列表
	*/
    public function index(){
		$page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
		$where = "1";
        if (isset($_GET['title']) && trim($_GET['title'])){
            $where .= " AND `title` LIKE '%".I('get.title')."%'";
            $params['title'] = I('get.title');
        }
		if (isset($_GET['coupon_sn']) && trim($_GET['coupon_sn'])){
            $where .= " AND `coupon_sn` = '".I('get.coupon_sn')."'";
            $params['coupon_sn'] = I('get.coupon_sn');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("coupon_id"=>"DESC"));
        foreach ($rs as $key => $val){
            if($val['status']==1){
                $rs[$key]['statusname'] = "上线";
            }else{
				$rs[$key]['statusname'] = "下架";
			}
            if($val['sale_type']==0){
                $rs[$key]['sale_type_name'] = "代金券";
                $rs[$key]['sale_rule'] = "直接抵扣".$val['coupon_charge']."元";
            }else{
                $rs[$key]['sale_type_name'] = "满减券";
                $rs[$key]['sale_rule'] = "满".$val['total_charge']."减".$val['coupon_charge'];
            }
            $rs[$key]['end_time'] = $val['valid'] ? strtotime("+".$val['valid']." month",$val['posttime'])+86400 : 0;
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("infolist",$rs);
        $this->assign("pageshow",$pageshow);
		$this->assign("var_page",C('VAR_PAGE') ? C('VAR_PAGE') : 'p');
		$this->assign("page",$page);
        $this->display();
    }
	
	/**
     * 添加
     */
    public function add(){
        if (IS_POST){
            $data['userid'] = ACPopedom::getID();
            $data['coupon_sn'] = GeneralRandSN();
			$data['title'] = trim($_POST['title']);
            $data['description'] = trim($_POST['description']);
            $data['sale_type'] = intval($_POST['sale_type']);
            $data['publish_time'] = strtotime($_POST['publishtime']) ? strtotime($_POST['publishtime']) : time();
            $data['valid'] = floatval($_POST['valid']);
            $data['status'] = 1;
            $data['total_number'] = intval(I("post.total"));
            $data['total_charge'] = floatval(I("post.total_charge"));
            $data['coupon_charge'] = floatval(I("post.coupon_charge"));
            $data['posttime'] = time();

			if (empty($data['title'])){
                $this->error("提示：请输入优惠券标题");
            }
            if($data['sale_type']==1 && !$data['total_charge']){
                $this->error("提示：请输入购满金额");
            }
			if (!$data['coupon_charge']){
                $this->error("提示：请输入优惠券金额");
            }
            $rs = $this->_Model->GetTotal("`title`='".$data['title']."'");
            if ($rs){
                $this->error("提示：该优惠券标题已经存在，请重新填写");
            }
            $rs = $this->_Model->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Coupon/add/"));
            }
            $this->success("提示：添加成功",U("/Admin/Coupon/"));
        }
        $this->display();
    }

    /**
     * 修改优惠券信息
     */
    public function edit(){
        if(IS_POST){
            $id = intval($_POST['id']);
            $info = D("Coupon")->GetInfo("`coupon_id`=".$id);
            if(empty($info)){
                $this->error("优惠券信息不存在");
            }
            $data['title'] = trim($_POST['title']);
            $data['description'] = trim($_POST['description']);
            $data['sale_type'] = intval($_POST['sale_type']);
            $data['publish_time'] = strtotime($_POST['publishtime']) ? strtotime($_POST['publishtime']) : time();
            $data['valid'] = floatval($_POST['valid']);
            $data['total_number'] = intval(I("post.total"));
            $data['total_charge'] = floatval(I("post.total_charge"));
            $data['coupon_charge'] = floatval(I("post.coupon_charge"));

            if (empty($data['title'])){
                $this->error("提示：请输入优惠券标题");
            }
            if($data['sale_type']==1 && !$data['total_charge']){
                $this->error("提示：请输入购满金额");
            }
            if (!$data['coupon_charge']){
                $this->error("提示：请输入优惠券金额");
            }
            $rs = $this->_Model->GetTotal("`title`='".$data['title']."' AND `coupon_id` != ".$id);
            if ($rs){
                $this->error("提示：该优惠券标题已经存在，请重新填写");
            }
            $rs = $this->_Model->Edit("`coupon_id`=".$id,$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/Coupon/edit/".$id));
            }
            $this->success("提示：更新成功",U("/Admin/Coupon/"));
        }else{
            $id = intval($_GET['id']);
            $info = D("Coupon")->GetInfo("`coupon_id`=".$id);
            if(empty($info)){
                $this->error("优惠券信息不存在");
            }
            $this->assign("info",$info);
            $this->display();
        }
    }

    /**
     * 删除优惠券
     */
	public function delete(){
        $id = intval($_GET['id']);
        $info = D("Coupon")->GetInfo("`coupon_id`=".$id);
        if(empty($info)){
            $this->error("优惠券信息不存在");
        }
        $rs = D("Coupon")->Delete("`coupon_id`=".$id);
        if(!$rs){
            $this->error("提示：删除失败");
        }
        $this->success("提示：删除成功");
    }
}