<?php
/**
 * 活动模块
*/
namespace Admin\Controller;
use Common\Validation;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
use Org\Net\Http;

class ActivityController extends MasterController {

	private $_Model = null;
	private $_rows = 20;

	public function __construct(){
		$this->_Model = D("Activity");
		parent::__construct();
	}

	public function index(){
		$page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `act_name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $infolist = $this->_Model->GetList($where,$page,$this->_rows,array("list"=>"DESC"));
        foreach ($infolist as $key => $info){
            $infolist[$key]['thumb_url'] = SITE_ATTACHMENT_URL.$info['thumb'];
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("infolist",$infolist);
        $this->assign("pageshow",$pageshow);
        $this->display();
	}

	/**
	 *
	 * 添加活动信息
	 */
	public function add(){
		if ($_POST){
            $data['act_name'] =trim($_POST['title']);
            $data['thumb'] =trim($_POST['thumb']);
            $data['act_desc'] = trim($_POST['description']);
            $data['act_type'] = 0;
            $data['start_time'] = strtotime(trim($_POST['begintime']));
            $data['end_time'] = strtotime(trim($_POST['endtime']));
			$data['list'] = intval($_POST['list']) ? intval($_POST['list']) : 50;
            $data['pick_time'] = strtotime(trim($_POST['picktime']));
            $data['province_id'] = intval($_POST['province']);
            $data['province'] = trim($_POST['province_name']);
            $data['city_id'] = intval($_POST['city']);
            $data['city_name'] = trim($_POST['city_name']);
            $data['district_id'] = intval($_POST['district']);
            $data['district_name'] = trim($_POST['district']);
			$data['pick_address'] = trim($_POST['pickaddress']);
			$data['edittime'] = time();
			$data['posttime'] = time();
            if(empty($data['act_name'])){
                $this->error("活动标题不能为空");
            }
            if(empty($data['thumb'])){
                $this->error("活动图片不能为空");
            }
            if(empty($_POST['begintime'])){
                $this->error("请选择活动开始时间");
            }
            if(empty($_POST['endtime'])){
                $this->error("请选择活动结束时间");
            }
            if(empty($_POST['picktime'])){
                $this->error("请选择取货时间");
            }
            if(empty($_POST['province_name']) || empty($_POST['city_name']) || empty($_POST['pickaddress'])){
                $this->error("请填写取货地址");
            }
            if(empty($data['act_desc'])){
                $this->error("请填写活动介绍");
            }
			$rs = $this->_Model->Add($data);
			if ($rs){
				$this->success("提示：添加成功",U("/Admin/Activity/"));
			}else {
				$this->error("提示：添加失败",U("/Admin/Activity/add/"));
			}
		}else{
		    //得到会员等级
            //$levellist = D("UserLevel")->GetAll("1",array("list"=>"DESC"));
            $this->assign("province",0);
            $this->assign("city",0);
            $this->assign("district",0);
            //$this->assign("levellist",$levellist);
            $this->display();
        }
	}

	public function edit(){
		if (IS_POST){
			$id = intval(I("post.id"));
			if (!$id){
				$this->error("提示：修改失败,无效的ID信息",U("/Admin/Activity/"));
			}
			$where='act_id='.$id;
			$rs = $this->_Model->GetInfo($where);
			if (empty($rs)){
				$this->error("提示：要修改的记录不存在",U("/Admin/Activity/"));
			}
            $data['act_name'] =trim($_POST['title']);
            $data['thumb'] =trim($_POST['thumb']);
            $data['act_desc'] = trim($_POST['description']);
            $data['start_time'] = strtotime(trim($_POST['begintime']));
            $data['end_time'] = strtotime(trim($_POST['endtime']));
            $data['list'] = intval($_POST['list']) ? intval($_POST['list']) : 50;
            $data['pick_time'] = strtotime(trim($_POST['picktime']));
            $data['province_id'] = intval($_POST['province']);
            $data['province'] = trim($_POST['province_name']);
            $data['city_id'] = intval($_POST['city']);
            $data['city'] = trim($_POST['city_name']);
            $data['district_id'] = intval($_POST['district']);
            $data['district'] = trim($_POST['district_name']);
            $data['pick_address'] = trim($_POST['pickaddress']);
            $data['edittime'] = time();
            if(empty($data['act_name'])){
                $this->error("活动标题不能为空");
            }
            if(empty($data['thumb'])){
                $this->error("活动图片不能为空");
            }
            if(empty($_POST['begintime'])){
                $this->error("请选择活动开始时间");
            }
            if(empty($_POST['endtime'])){
                $this->error("请选择活动结束时间");
            }
            if(empty($_POST['picktime'])){
                $this->error("请选择取货时间");
            }
            if(empty($_POST['province_name']) || empty($_POST['city_name']) || empty($_POST['pickaddress'])){
                $this->error("请填写取货地址");
            }
            if(empty($data['act_desc'])){
                $this->error("请填写活动介绍");
            }

			$rs = $this->_Model->Edit("`act_id`=".$id,$data);
		
			if ($rs){
				$this->success("提示：修改成功",U("/Admin/Activity/"));
			}else {
				$this->error("提示：修改失败",U("/Admin/Activity/edit/id/{$id}"));
			}

		}else {
			$id = intval($_GET['id']);
			if (!$id) {
				$this->error("提示：请选择要修改的记录", U("/Admin/Activity/"));
			}
			$where='act_id='.$id;
			$rs = $this->_Model->GetInfo($where);
			if (empty($rs)) {
				$this->error("提示：要修改的记录不存在", U("/Admin/Activity/"));
			}
            $this->assign("province",intval($rs['province_id']));
            $this->assign("city",intval($rs['city_id']));
            $this->assign("district",intval($rs['district_id']));
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
			$this->error("提示：请选择要删除的记录",U("/Admin/Activity/"));
		}
		$where = "act_id=".$id;
		$rs = $this->_Model->GetInfo($where);
		if (empty($rs)){
			$this->error("提示：要删除的记录不存在",U("/Admin/Activity/"));
		}
		$rs = $this->_Model->Delete("`act_id`=".$id);
		if ($rs){
			$this->success("提示：删除成功",U("/Admin/Activity/"));
		}else {
			$this->error("提示：删除失败",U("/Admin/Activity/"));
		}
	}

    /**
     * 设置商品
     */
	public function config(){
        if(IS_POST){
            $array_goods_name = $_POST['goods_name'];
            $array_limit_number = $_POST['limit_number'];
            $array_sale_price = $_POST['sale_price'];
            $array_goods_id = $_POST['goods_id'];
            $act_id = intval($_POST['act_id']);
            $where = "act_id=".$act_id;
            $rs = $this->_Model->GetInfo($where);
            if (empty($rs)){
                $this->error("提示：要设置的记录不存在",U("/Admin/Activity/"));
            }
            $error_msg = array();
            $excute_sql = array();
            $is_execute = true;
            if(count($array_goods_id) != count($array_limit_number) || count($array_goods_id) != count($array_goods_name)){
                $error_msg[] = "商品信息不匹配";
            }
            foreach ($array_goods_id as $key => $goods_id){
                $sale_price = array();
                foreach ($array_sale_price as $k => $v){
                    if(!intval($array_sale_price[$k][$key])){
                        $error_msg[] = "第".($key+1)."行商品活动价格为空";
                    }else {
                        $sale_price[$k] = $v[$key];
                    }
                }
                if(empty($array_goods_name[$key])){
                    $error_msg[] = "第".($key+1)."行商品名称为空";
                }
                if(!intval($array_limit_number[$key])){
                    $error_msg[] = "第".($key+1)."商品限购数量为0";
                }
                if(!intval($array_goods_id[$key])){
                    $error_msg[] = "第".($key+1)."商品商品信息不存在";
                }
                $excute_sql[] = " INSERT INTO " . C("DB_MALL.db_prefix") . "activity_goods(`act_id`,`goods_id`,`sale_price`,`buy_limit`,`goods_name`) VALUES(" . $act_id . "," . intval($goods_id) . ",'" . json_encode($sale_price) . "'," . intval($array_limit_number[$key]) . ",'" . $array_goods_name[$key] . "')";
            }
            if(!empty($error_msg)){
                $this->error(implode("<br/>",$error_msg));
            }
            $this->_Model->startTrans();
            //清理之前的商品信息
            $rs = D("ActivityGoods")->Delete("`act_id`=".$act_id);
            foreach ($excute_sql as $sql){
                $rr = $this->_Model->ExecuteSql($sql);
                if(!$rr){
                    $is_execute = false;
                }
            }
            if($rs && $is_execute == true){
                $this->_Model->commitTrans();
                $this->success("设置成功");
            }else{
                $this->_Model->rollbackTrans();
                $this->error("设置失败");
            }
        }else{
            $where = "act_id=".intval(I("get.id"));
            $rs = $this->_Model->GetInfo($where);
            if (empty($rs)){
                $this->error("提示：要设置的记录不存在",U("/Admin/Activity/"));
            }
            //得到商品信息
            $goodslist = D("ActivityGoods")->GetAll("`act_id`=".intval(I("get.id")));
            foreach ($goodslist as $key => $goods){
                $goodslist[$key]['sale_price'] = json_decode($goods['sale_price'],true);
            }
            //得到会员等级
            $levellist = D("UserLevel")->GetAll("1",array("list"=>"DESC"));
            $this->assign("info",$rs);
            $this->assign("goodslist",$goodslist);
            $this->assign("levellist",$levellist);
            $this->display();
        }
    }
}