<?php
/**
 * 财务模块
 */
namespace Admin\Controller;
use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;
use Common\Validation;
use Org\Net\Http;
class FinanceController extends MasterController {

    private $_Model = null;
    private $_rows = 20;

    public function __construct(){
        $this->_Model = D("AccountLog");
        parent::__construct();
    }

    public function recharge(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "al.type=0";
        if (isset($_GET['username']) && trim($_GET['username'])){
            $where .= " AND u.`nickname` LIKE '%".I('username')."%'";
            $params['username'] = I('get.username');
        }
        $total = $this->_Model->GetJoinTotal($where);
        $rs = $this->_Model->GetJoinList("al",array("LEFT JOIN __USER__ u ON u.userid=al.to_userid"),$where,$page,$this->_rows,array("al.posttime"=>"DESC"));
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("infolist",$rs);
        $this->assign("pageshow",$pageshow);
        $this->display();
    }
}