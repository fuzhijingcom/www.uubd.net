<?php
/**
 * 会员模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
class MemberController extends MasterController {

    private $_Model = null;
    private $_rows = 20;
	private $_ExperienceLogModel = null;
    private $_PointLogModel = null;
	private $_MoneyLogModel = null;
	private $_AccountModel = null;
	private $_AccountLogModel = null;

    public function __construct(){
        $this->_Model = D("User");
		$this->_ExperienceLogModel = D("ExperienceLog");
        $this->_PointLogModel = D("PointLog");
		$this->_MoneyLogModel = D("MoneyLog");
		$this->_AccountModel = D("Account");
        $this->_AccountLogModel = D("AccountLog");
        parent::__construct();
    }

    /**
     *
     * 会员列表
     */
    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "u.`role_type`=1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND (u.`nickname` LIKE '%".I('get.name')."%' OR u.`username` LIKE '%".I('get.name')."%')";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetJoinTotal("u",array("LEFT JOIN __ACCOUNT__ a ON a.userid=u.userid"),$where);
        $rs = $this->_Model->GetJoinList("u",array("LEFT JOIN __ACCOUNT__ a ON a.userid=u.userid"),$where,$page,$this->_rows,array("u.posttime"=>"DESC"),"u.*,a.balance");
		foreach($rs as $key => $val){
		    if($val['openid']) {
                $rs[$key]['username'] = base_decode($val['nickname']);
            }
			$level = D("UserLevel")->GetInfo("`levelid`=".$val['groupid']);
			$rs[$key]['levelname'] = $level['name'];
			$rs[$key]['status_label'] = $val['status'] == 1 ? "有效" : "无效";
		}
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("userlist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }
	
    /**
     *
     * 锁定状态
     */
    public function lock(){
        $id = intval(I("get.id"));
        $data['status'] = intval(I("get.lock"));
        if (!$id) {
            $this->error("提示：请选择要操作的记录",U("/Admin/Member/"));
        }
        $rs = $this->_Model->GetInfo("`userid`=".$id." AND `role_type`=1");
        if (empty($rs)){
            $this->error("提示：要操作的记录不存在",U("/Admin/Member/"));
        }
        $rs = $this->_Model->Edit("`userid`={$id} AND `role_type`=1",$data);
        if (!$rs) {
            $this->error("提示：操作失败",U("/Admin/Member/"));
        }
        $this->success("提示：操作成功",U("/Admin/Member/"));
    }

	/**
     *
     * 设置积分
     */
    public function point(){
        if (IS_POST){
			$this->_Model->startTrans();
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Member/"));
            }
			$point = intval($_POST['point']);
			if(I("action")=="add"){
				$rs = $this->_Model->Exec("UPDATE __USER__ SET `point`=`point`+".abs($point)." WHERE `userid`=".$id." AND `role_type`=1");
			}else{
				$rs = $this->_Model->Exec("UPDATE __USER__ SET `point`=`point`-".abs($point)." WHERE `userid`=".$id." AND `role_type`=1");
			}
			$logdata = array(
                'userid' => ACPopedom::getID(),
                "to_userid" => $id,
                'point' => abs($point),
                'type'   => I("action")=="add" ? '增加' : '减少',
                'ip'     => get_client_ip(),
                'posttime' => time(),
                "remark" =>"管理员".ACPopedom::getMaster()."直接更改用户ID为：".$id."的积分值"
            );
			$rss = $this->_PointLogModel->Add($logdata);
            if ($rs && $rss){
				$this->_Model->commitTrans();
                $this->success("提示：修改成功",U("/Admin/Member/"));
            }else {
				$this->_Model->rollbackTrans();
                $this->error("提示：修改失败",U("/Admin/Member/point/id/{$id}"));
            }
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Member/"));
        }
        $rs = $this->_Model->GetJoinInfo("u",array('LEFT JOIN __USER_RELATION__ AS ur ON u.userid = ur.userid'),"u.userid=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Member/"));
        }
        $this->assign("info",$rs);
        $this->display();
    }
	
	/**
     *
     * 设置经验
     */
    public function experience(){
        if (IS_POST){
			$this->_Model->startTrans();
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Member/"));
            }
			$experience = intval($_POST['experience']);
			if(I("action")=="add"){
				$rs = $this->_Model->Exec("UPDATE __USER__ SET `experience`=`experience`+".abs($experience)." WHERE `userid`=".$id);
			}else{
				$rs = $this->_Model->Exec("UPDATE __USER__ SET `experience`=`experience`-".abs($experience)." WHERE `userid`=".$id);
			}
			$logdata = array(
                'userid' => ACPopedom::getID(),
                "to_userid" => $id,
                'experience' => abs($experience),
                'type'   => I("action")=="add" ? '增加' : '减少',
                'ip'     => get_client_ip(),
                'posttime' => time(),
                "remark" =>"管理员".ACPopedom::getMaster()."直接更改用户ID为：".$id."的经验值"
            );
			$rss = $this->_ExperienceLogModel->Add($logdata);
            if ($rs && $rss){
				$this->_Model->commitTrans();
                $this->success("提示：修改成功",U("/Admin/Member/"));
            }else {
				$this->_Model->rollbackTrans();
                $this->error("提示：修改失败",U("/Admin/Member/experience/id/{$id}"));
            }
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Member/"));
        }
        $rs = $this->_Model->GetJoinInfo("u",array('LEFT JOIN __USER_RELATION__ AS ur ON u.userid = ur.userid'),"u.userid=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Member/"));
        }
        $this->assign("info",$rs);
        $this->display();
    }
	
	/**
     *
     * 设置金额
     */
    public function balance(){
        if (IS_POST){
			$this->_AccountModel->startTrans();
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Member/"));
            }
			$account = $this->_AccountModel->GetInfo("`userid`=".$id);
			if(empty($account)){
				$this->_AccountModel->Add(array('userid'=>$id,"balance"=>0.00,'password'=>"",'status'=>1));
			}
			$balance = intval($_POST['balance']);
			if(I("action")=="add"){
				$rs = $this->_AccountModel->Exec("UPDATE __ACCOUNT__ SET `balance`=`balance`+".abs($balance)." WHERE `userid`=".$id);
			}else{
				$rs = $this->_AccountModel->Exec("UPDATE __ACCOUNT__ SET `balance`=`balance`-".abs($balance)." WHERE `userid`=".$id);
			}
			$logdata = array(
                'userid' => ACPopedom::getID(),
                "to_userid" => $id,
                'balance' => intval($account['balance'])+$balance,
				'money' => abs($balance),
                'type'   => I("action")=="add" ? '增加' : '减少',
                'ip'     => get_client_ip(),
                'posttime' => time(),
				'appkey' => 'htgy',
                "remark" =>"管理员".ACPopedom::getMaster()."直接更改用户ID为：".$id."的金额"
            );
			$rss = $this->_AccountLogModel->Add($logdata);
            if ($rs && $rss){
				$this->_AccountModel->commitTrans();
                $this->success("提示：修改成功",U("/Admin/Member/"));
            }else {
				$this->_AccountModel->rollbackTrans();
                $this->error("提示：修改失败",U("/Admin/Member/experience/id/{$id}"));
            }
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Member/"));
        }
		$account = $this->_AccountModel->GetInfo("`userid`=".$id);
		if(empty($account)){
			$this->_AccountModel->Add(array('userid'=>$id,"balance"=>0.00,'password'=>"",'status'=>1));
		}
        $rs = $this->_Model->GetJoinInfo("u",array('LEFT JOIN __ACCOUNT__ AS a ON u.userid = a.userid'),"u.userid=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Member/"));
        }
        $this->assign("info",$rs);
        $this->display();
    }
	
	/**
     *
     * 设置余额
     */
    public function money(){
        if (IS_POST){
			$this->_MoneyLogModel->startTrans();
            $id = intval(I("post.id"));
            if (!$id){
                $this->error("提示：修改失败,无效的ID信息",U("/Admin/Member/"));
            }
			$money = intval($_POST['money']);
			if(I("action")=="add"){
				$rs = $this->_MoneyLogModel->Exec("UPDATE __USER__ SET `money`=`money`+".abs($money)." WHERE `userid`=".$id);
			}else{
				$rs = $this->_MoneyLogModel->Exec("UPDATE __USER__ SET `money`=`money`-".abs($money)." WHERE `userid`=".$id);
			}
			$logdata = array(
                'userid' => ACPopedom::getID(),
                "to_userid" => $id,
                'money' => abs($money),
                'type'   => I("action")=="add" ? '增加' : '减少',
                'ip'     => get_client_ip(),
                'posttime' => time(),
                "remark" =>"管理员".ACPopedom::getMaster()."直接更改用户ID为：".$id."的余额"
            );
			$rss = $this->_MoneyLogModel->Add($logdata);
            if ($rs && $rss){
				$this->_MoneyLogModel->commitTrans();
                $this->success("提示：修改成功",U("/Admin/Member/"));
            }else {
				$this->_MoneyLogModel->rollbackTrans();
                $this->error("提示：修改失败",U("/Admin/Member/experience/id/{$id}"));
            }
        }
        $id = intval($_GET['id']);
        if (!$id) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Member/"));
        }
        $rs = $this->_Model->GetJoinInfo("u",array('LEFT JOIN __USER_RELATION__ AS ur ON u.userid = ur.userid'),"u.userid=".$id);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Member/"));
        }
        $this->assign("info",$rs);
        $this->display();
    }
	
	/**
     *
     * 历史订单
     */
    public function order(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "`role_type`=1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND (`nickname` LIKE '%".I('get.name')."%' OR `username` LIKE '%".I('get.name')."%')";
            $params['name'] = I('get.name');
        }
        $total = $this->_Model->GetTotal($where);
        $rs = $this->_Model->GetList($where,$page,$this->_rows,array("posttime"=>"DESC"));
        foreach($rs as $key => $val){
            //得到用户订单信息
            $order = D("Order")->GetTotal("`buyer_id`=".$val['userid']." AND `status`=1");
            $rs[$key]['order'] = $order;
        }
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("userlist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }


}