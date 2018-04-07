<?php
/**
 * Created by PhpStorm.
 * User: ADSL
 * Date: 2015/5/6
 * Time: 14:09
 */

namespace Admin\Controller;

use Admin\Common\ACPopedom;
use Think\Controller\MasterController;
use Think\Page;

class AreaController extends MasterController
{
    private $_ProvinceModel = null;
    private $_CityModel = null;
    private $_DistrictModel = null;
    private $_rows = 20;

    public function __construct()
    {
        $this->_ProvinceModel = D("Province");
        $this->_CityModel = D("City");
        $this->_DistrictModel = D("District");
        parent::__construct();
    }

    public function index(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        $where = "1";
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_ProvinceModel->GetTotal($where);
        $rs = $this->_ProvinceModel->GetList($where,$page,$this->_rows,array("list"=>"DESC"));
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("provincelist",$rs);
        $this->assign("pageshow",$pageshow);
		$this->assign("var_page",C('VAR_PAGE') ? C('VAR_PAGE') : 'p');
		$this->assign("page",$page);
        $this->display();
    }

    /**
     * 添加省
     */
    public function addProvince(){
		/*if (!ACPopedom::getPopedom("system@area@addProvince")){
            $this->error('错误!对不起，你没有足够的操作权限![code:001]',U("/Admin/Index/info/"));
        }*/
        if (IS_POST){
            $data['name'] = I("post.name","");
            $data['list'] = intval(I("post.list"));
            if (empty($data['name'])){
                $this->error("提示：请输入省名称");
            }
            $rs = $this->_ProvinceModel->GetTotal("`name`='".$data['name']."'");
            if ($rs){
                $this->error("提示：该省份已经存在，请重新填写");
            }
            $rs = $this->_ProvinceModel->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Area/addProvince/"));
            }
            $this->success("提示：添加成功",U("/Admin/Area/"));
        }
        $this->display();
    }

    /**
     * 修改省
     */
    public function editProvince(){
        if (IS_POST){
            $proid = intval(I("post.proid"));
			$page = intval($_POST[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
			$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
            $data['name'] = I("post.name","");
            $data['list'] = intval(I("post.list"));
            if (empty($data['name'])){
                $this->error("提示：请输入省名称");
            }
            $rs = $this->_ProvinceModel->GetTotal("`name`='".$data['name']."' AND `proid` != {$proid}");
            if ($rs){
                $this->error("提示：该省份已经存在，请重新填写");
            }
            $rs = $this->_ProvinceModel->Edit("`proid`={$proid}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/Area/editProvince/proid/{$proid}"));
            }
            $this->success("提示：更新成功",U("/Admin/Area/index/",array($var_page=>$page)));
        }
        $proid = intval($_GET['proid']);
		$page = intval($_GET[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
		$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        if (!$proid) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Area/index/",array($var_page=>$page)));
        }
        $rs = $this->_ProvinceModel->GetInfo("`proid`=".$proid);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Area/index/",array($var_page=>$page)));
        }
        $this->assign("info",$rs);
		$this->assign("page",$page);
		$this->assign("var_page",$var_page);
        $this->display();
    }

    /**
     * 删除省份
     */
    public function deleteProvince(){
        $proid = intval(I("get.proid"));
		$page = intval($_GET[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
		$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        if (!$proid) {
            $this->error("提示：请选择要删除的记录",U("/Admin/Area/index/",array($var_page=>$page)));
        }
        $rs = $this->_ProvinceModel->GetInfo("`proid`=".$proid);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Area/index/",array($var_page=>$page)));
        }
        //判断代理商等级下是否有代理商
        $rs = $this->_CityModel->GetTotal("`provinceid`=".$proid);
        if($rs){
            $this->error("提示：该省份下有城市信息，无法删除",U("/Admin/Area/index/",array($var_page=>$page)));
        }
        $rs = $this->_ProvinceModel->Delete("`proid`=".$proid);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/Area/index/",array($var_page=>$page)));
        }
        $this->success("提示：删除成功",U("/Admin/Area/index/",array($var_page=>$page)));
    }

    /**
     *
     * 检测省份是否存在
     */
    public function checkProvince(){
        if(IS_AJAX) {
            $where = "`name` = '" . I('post.name') . "'";
            if (intval(I('post.proid'))) {
                $where .= " AND `proid` != " . intval(I('post.proid'));
            }
            $rs = $this->_ProvinceModel->GetTotal($where);
            if ($rs) {
                exit(json_encode(false));
            }
            exit(json_encode(true));
        }else{
            $this->error("提示：无效的请求");
        }
    }

    /**
     * 管理省所辖市
     */
    public function city(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        if(!intval($_GET['proid'])){
            $this->error("提示：无效的省份信息");
        }
        $province = $this->_ProvinceModel->GetInfo("`proid`=".intval($_GET['proid']));
        $where = "`provinceid`=".intval($_GET['proid']);
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_CityModel->GetTotal($where);
        $rs = $this->_CityModel->GetList($where,$page,$this->_rows,array("list"=>"DESC"));
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("citylist",$rs);
        $this->assign("province",$province);
        $this->assign("pageshow",$pageshow);
		$this->assign("var_page",C('VAR_PAGE') ? C('VAR_PAGE') : 'p');
		$this->assign("page",$page);
        $this->display();
    }

    /**
     * 添加市
     */
    public function addCity(){
        if (IS_POST){
            $data['provinceid'] = intval(I("post.proid"));
            $data['name'] = I("post.name","");
            $data['list'] = intval(I("post.list"));
            if(!$data['provinceid']){
                $this->error("提示：无效的省份信息");
            }
            if (empty($data['name'])){
                $this->error("提示：请输入城市名称");
            }
            $rs = $this->_CityModel->GetTotal("`name`='".$data['name']."'");
            if ($rs){
                $this->error("提示：该城市已经存在，请重新填写");
            }
            $rs = $this->_CityModel->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Area/addCity/"));
            }
            $this->success("提示：添加成功",U("/Admin/Area/city",array("proid"=>$data['provinceid'])));
        }
        $province = $this->_ProvinceModel->GetInfo("`proid`=".intval($_GET['proid']));
        $this->assign("province",$province);
        $this->display();
    }

    /**
     * 修改市
     */
    public function editCity(){
        if (IS_POST){
            $cityid = intval(I("post.cityid"));
            $proid = intval(I("post.proid"));
			$page = intval($_POST[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
			$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
            $data['provinceid'] = $proid;
            $data['name'] = I("post.name","");
            $data['list'] = intval(I("post.list"));
            if (empty($data['name'])){
                $this->error("提示：请输入城市名称");
            }
            $rs = $this->_CityModel->GetTotal("`name`='".$data['name']."' AND `cityid` != {$cityid} AND `provinceid`=".$proid);
            if ($rs){
                $this->error("提示：该城市已经存在，请重新填写");
            }
            $rs = $this->_CityModel->Edit("`cityid`={$cityid}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/Area/editCity/",array("proid"=>$proid,"cityid"=>$cityid)));
            }
            $this->success("提示：更新成功",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
        }
        $cityid = intval($_GET['cityid']);
        $proid = intval($_GET['proid']);
		$page = intval($_GET[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
		$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        if (!$cityid) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
        }
        $rs = $this->_CityModel->GetInfo("`cityid`=".$cityid);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
        }
        $province = $this->_ProvinceModel->GetInfo("`proid`=".intval($_GET['proid']));
        $this->assign("province",$province);
        $this->assign("info",$rs);
		$this->assign("page",$page);
		$this->assign("var_page",$var_page);
        $this->display();
    }

    /**
     * 删除城市
     */
    public function deleteCity(){
        $cityid = intval(I("get.cityid"));
        $proid = intval(I("get.proid"));
		$page = intval($_GET[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
		$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        if (!$cityid) {
            $this->error("提示：请选择要删除的记录",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
        }
        $rs = $this->_CityModel->GetInfo("`cityid`=".$cityid);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
        }
        $rs = $this->_DistrictModel->GetTotal("`cityid`=".$cityid);
        if($rs){
            $this->error("提示：该城市下有区/县信息，无法删除",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
        }
        $rs = $this->_CityModel->Delete("`cityid`=".$cityid);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
        }
        $this->success("提示：删除成功",U("/Admin/Area/city/",array("proid"=>$proid,$var_page=>$page)));
    }

    /**
     *
     * 检测城市是否存在
     */
    public function checkCity(){
        if(IS_AJAX) {
            $where = "`name` = '" . I('post.name') . "'";
            if (intval(I('post.cityid')) && intval(I('post.proid'))) {
                $where .= " AND `cityid` != " . intval(I('post.cityid'))." AND `provinceid` = ".intval(I('post.proid'));
            }
            $rs = $this->_CityModel->GetTotal($where);
            if ($rs) {
                exit(json_encode(false));
            }
            exit(json_encode(true));
        }else{
            $this->error("提示：无效的请求");
        }
    }

    /**
     * 管理区
     */
    public function district(){
        $page = intval(I("get.".(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) ? intval(I('get.'.(C('VAR_PAGE') ? C('VAR_PAGE') : 'p'))) : 1;
        if(!intval($_GET['proid'])){
            $this->error("提示：无效的省份信息");
        }
        if(!intval($_GET['cityid'])){
            $this->error("提示：无效的城市信息");
        }
        $province = $this->_ProvinceModel->GetInfo("`proid`=".intval($_GET['proid']));
        $city = $this->_CityModel->GetInfo("`cityid`=".intval($_GET['cityid']));
        $where = "`cityid`=".intval($_GET['cityid']);
        if (isset($_GET['name']) && trim($_GET['name'])){
            $where .= " AND `name` LIKE '%".I('get.name')."%'";
            $params['name'] = I('get.name');
        }
        $total = $this->_DistrictModel->GetTotal($where);
        $rs = $this->_DistrictModel->GetList($where,$page,$this->_rows,array("list"=>"DESC"));
        $pageobj = new Page($total,$this->_rows,$params);
        $pageobj->setConfig('prev','上一页');
        $pageobj->setConfig('next','下一页');
        $pageshow = $pageobj->show();
        $this->assign("districtlist",$rs);
        $this->assign("province",$province);
        $this->assign("city",$city);
        $this->assign("pageshow",$pageshow);
		$this->assign("var_page",C('VAR_PAGE') ? C('VAR_PAGE') : 'p');
		$this->assign("page",$page);
        $this->display();
    }

    /**
     * 添加区
     */
    public function addDistrict(){
        if (IS_POST){
            $data['cityid'] = intval(I("post.cityid"));
            $data['name'] = I("post.name","");
            $data['list'] = intval(I("post.list"));
            if(!$data['cityid']){
                $this->error("提示：无效的城市信息");
            }
            if (empty($data['name'])){
                $this->error("提示：请输入区/县名称");
            }
            $rs = $this->_DistrictModel->GetTotal("`name`='".$data['name']."'");
            if ($rs){
                $this->error("提示：该区/县已经存在，请重新填写");
            }
            $rs = $this->_DistrictModel->Add($data);
            if (!$rs){
                $this->error("提示：添加失败",U("/Admin/Area/addDistrict/"));
            }
            $this->success("提示：添加成功",U("/Admin/Area/district",array("proid"=>intval(I("post.proid")),"cityid"=>$data['cityid'])));
        }
        $province = $this->_ProvinceModel->GetInfo("`proid`=".intval($_GET['proid']));
        $city = $this->_CityModel->GetInfo("`cityid`=".intval($_GET['cityid']));
        $this->assign("province",$province);
        $this->assign("city",$city);
        $this->display();
    }

    /**
     * 修改区
     */
    public function editDistrict(){
        if (IS_POST){
            $cityid = intval(I("post.cityid"));
            $proid = intval(I("post.proid"));
            $disid = intval(I("post.disid"));
			$page = intval($_POST[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
			$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
            $data['cityid'] = $cityid;
            $data['name'] = I("post.name","");
            $data['list'] = intval(I("post.list"));
            if (empty($data['name'])){
                $this->error("提示：请输入区/县名称");
            }
            $rs = $this->_DistrictModel->GetTotal("`name`='".$data['name']."' AND `disid`!= {$disid} AND `cityid`=".$cityid);
            if ($rs){
                $this->error("提示：该区/县已经存在，请重新填写");
            }
            $rs = $this->_DistrictModel->Edit("`disid`={$disid}",$data);
            if (!$rs){
                $this->error("提示：更新失败",U("/Admin/Area/editDistrict/",array("proid"=>$proid,"cityid"=>$cityid,"disid"=>$disid)));
            }
            $this->success("提示：更新成功",U("/Admin/Area/district/",array("proid"=>$proid,"cityid"=>$cityid,$var_page=>$page)));
        }
        $cityid = intval($_GET['cityid']);
        $proid = intval($_GET['proid']);
        $disid = intval($_GET['disid']);
		$page = intval($_GET[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
		$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        if (!$disid) {
            $this->error("提示：请选择要修改的记录",U("/Admin/Area/district/",array("proid"=>$proid,"cityid"=>$cityid,$var_page=>$page)));
        }
        $rs = $this->_DistrictModel->GetInfo("`disid`=".$disid);
        if (empty($rs)){
            $this->error("提示：要修改的记录不存在",U("/Admin/Area/district/",array("proid"=>$proid,"cityid"=>$cityid,$var_page=>$page)));
        }
        $province = $this->_ProvinceModel->GetInfo("`proid`=".intval($_GET['proid']));
        $city = $this->_CityModel->GetInfo("`cityid`=".intval($_GET['cityid']));
        $this->assign("province",$province);
        $this->assign("city",$city);
        $this->assign("info",$rs);
		$this->assign("page",$page);
		$this->assign("var_page",$var_page);
        $this->display();
    }

    /**
     * 删除区/县
     */
    public function deleteDistrict(){
        $cityid = intval(I("get.cityid"));
        $proid = intval(I("get.proid"));
        $disid = intval(I("get.disid"));
		$page = intval($_GET[C('VAR_PAGE') ? C('VAR_PAGE') : 'p']);
		$var_page = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        if (!$disid) {
            $this->error("提示：请选择要删除的记录",U("/Admin/Area/district/",array("proid"=>$proid,"cityid"=>$cityid,$var_page=>$page)));
        }
        $rs = $this->_DistrictModel->GetInfo("`disid`=".$disid);
        if (empty($rs)){
            $this->error("提示：要删除的记录不存在",U("/Admin/Area/district/",array("proid"=>$proid,"cityid"=>$cityid,$var_page=>$page)));
        }
        $rs = $this->_DistrictModel->Delete("`disid`=".$disid);
        if (!$rs){
            $this->error("提示：删除失败",U("/Admin/Area/district/",array("proid"=>$proid,"cityid"=>$cityid,$var_page=>$page)));
        }
        $this->success("提示：删除成功",U("/Admin/Area/district/",array("proid"=>$proid,"cityid"=>$cityid,$var_page=>$page)));
    }

    /**
     *
     * 检测区/县是否存在
     */
    public function checkDistrict(){
        if(IS_AJAX) {
            $where = "`name` = '" . I('post.name') . "'";
            if (intval(I('post.disid')) && intval(I('post.cityid'))) {
                $where .= " AND `disid` != " . intval(I('post.disid'))." AND `cityid` =".intval(I('post.cityid'));
            }
            $rs = $this->_DistrictModel->GetTotal($where);
            if ($rs) {
                exit(json_encode(false));
            }
            exit(json_encode(true));
        }else{
            $this->error("提示：无效的请求");
        }
    }

    /**
     * 获取省
     */
    public function GetProvinve(){
        if(IS_AJAX) {
            $data['data'] = D("Province")->GetAll("1",array("list"=>"DESC"));
            $data['count'] = D("Province")->GetTotal();
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
     * 获取市
     */
    public function GetCity(){
        if(IS_AJAX) {
            $proid = intval(I('get.proid', ''));
            if (!$proid) {
                $data['data'] = array();
                $data['count'] = 0;
                $data['message'] = "提示：无效的省份信息，请求失败";
                $data['status'] = false;
                $this->ajaxReturn($data);
            }
            $data['data'] = D("City")->GetAll(" provinceid = ".$proid,array("list"=>"DESC"));
            $data['count'] = D("City")->GetTotal(" provinceid = ".$proid);
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
     * 获取区
     */
    public function GetDistrict(){
        $cityid = intval(I('get.cityid'));
        if(!$cityid){
            $data['data'] = array();
            $data['count'] = 0;
            $data['message'] = "提示：无效的城市信息，请求失败";
            $data['status'] = false;
            $this->ajaxReturn($data);
        }
        if(IS_AJAX) {
            $data['data'] = D("District")->GetAll(" cityid =".$cityid,array("list"=>"DESC"));
            $data['count'] = D("District")->GetTotal(" cityid = ".$cityid);
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
}