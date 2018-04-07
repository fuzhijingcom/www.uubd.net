<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2016/11/5
 * Time: 15:30
 */
namespace Home\Controller;
use Common\Wx;
use Common\Controller\HomebaseController;

class PartnerController extends HomebaseController{

    public function index(){
        $this->display();
    }

    public function getPartner(){
        $Partner = D('Partner');
        $partnerData = $Partner->getAllPartner();
        $this->ajaxReturn($partnerData);
    }


    public function addPartner(){
        $data['promotion_type'] = I('post.promotion_type');
        $data['p_name'] = I('post.p_name');
        $data['p_phone'] = I('post.p_phone');
        $data['is_withdraw'] = I('post.is_withdraw');
        $data['promotion_store'] = I('post.promotion_store');
        $data['effectiveyear'] = I('post.effectiveyear');
        $data['created'] = date('Y-m-d H:i:s');
        $data['partner_time'] = date('Y-m-d H:i:s');

        $Customer = M('Customer');
        $where['name']  = $data['p_name'];
        $where['phone'] = $data['p_phone'];
        $userInfo = $Customer->where($where)->find();
        if(!$userInfo){
            $return_msg = array(
                'sign'=>-1,
                'msg'=>'添加失败，可能的原因如下：'.PHP_EOL.'1.该合伙人没有关注“uubd”公众号;'.PHP_EOL.'2.该合伙人没有注册;'.PHP_EOL.'3.该合伙人的注册信息有误（注册时必须填写真实姓名和手机号码）;',
            );
            exit(json_encode($return_msg));
        }
        $data['weixin_openid'] = $userInfo['weixin_openid'];
        //如果是初级合伙人，合伙人名=名+weixin_openid
        if($data['promotion_type'] == '4'){
            $data['p_name'] .= '@'.$data['weixin_openid'];
        }
        //如果是全职合伙人，则自动创建一个不重复的特权码
        if($data['promotion_type'] == '2'){
            $data['privilege_id'] = $this->createPrivilegeId();
        }
        //初始化合伙人的收益组ID
        if($data['promotion_type'] == '2'){
            $data['default_order_income_id'] = '1001';
            $data['default_extend_income_id'] = '1001';
        }else if($data['promotion_type'] == '3'){
            $data['default_order_income_id'] = '1001';
            $data['default_extend_income_id'] = '1001';
            $data['reserve_order_income_id'] = '1011';
            $data['reserve_extend_income_id'] = '1011';
        }else if($data['promotion_type'] == '4'){
            $data['default_order_income_id'] = '1001';
        }
        $Partner = M('Partner');
        //判断合伙人是否已经存在
        $partnerInfo = $Partner->where(array('p_name'=>$data['p_name'],'p_phone'=>$data['p_phone']))->find();
        if($partnerInfo){
            $return_msg = array(
                'sign'=>-1,
                'msg'=>'该合伙人已经存在，请勿重复添加！',
            );
            exit(json_encode($return_msg));
        }
        $res = $Partner->add($data);
        if($res){
            $qrcode = Wx\Weixin::apply_qrcode($res); // 根据 token ，获取用户信息
            if ($qrcode) {
                $map['s_id'] = $res;
                $updata['qrcode'] = $qrcode;
                $result = $Partner->where(array('p_id'=>$res))->save($updata); //
            }
            $return_msg = array(
                'sign'=>1,
                'msg'=>'添加成功！',
            );
            exit(json_encode($return_msg));
        }else{
            $return_msg = array(
                'sign'=>-1,
                'msg'=>'添加失败！',
            );
            exit(json_encode($return_msg));
        }
    }


    public function updatePartner(){
        $p_id = I('post.p_id');
        $data['promotion_type'] = I('post.promotion_type');
//        $data['p_name'] = I('post.p_name');
        $data['p_phone'] = I('post.p_phone');
        $data['is_withdraw'] = I('post.is_withdraw');
        $data['promotion_store'] = I('post.promotion_store');
        $data['effectiveyear'] = I('post.effectiveyear');

        //初始化合伙人的收益组ID
        if($data['promotion_type'] == '2'){
            $data['default_order_income_id'] = '1001';
            $data['reserve_order_income_id'] = '0';
            $data['default_extend_income_id'] = '1001';
            $data['reserve_extend_income_id'] = '0';
            $data['privilege_id'] = $this->createPrivilegeId();
        }else if($data['promotion_type'] == '3'){
            $data['default_order_income_id'] = '1001';
            $data['default_extend_income_id'] = '1001';
            $data['reserve_order_income_id'] = '1011';
            $data['reserve_extend_income_id'] = '1011';
            $data['privilege_id'] = 0;
        }else if($data['promotion_type'] == '4'){
            $data['default_order_income_id'] = '1001';
            $data['default_extend_income_id'] = '0';
            $data['reserve_order_income_id'] = '0';
            $data['reserve_extend_income_id'] = '0';
            $data['privilege_id'] = 0;
        }

        $Partner = M('Partner');
        $res = $Partner->where(array('p_id'=>$p_id))->save($data);
        if(!$res){
            $return_msg = array(
                'sign'=>-1,
                'msg'=>'修改失败，可能的原因如下：'.PHP_EOL.'1.该合伙人并不存在（如果需要添加，则点击添加按钮）;'.PHP_EOL.'2.输入的数据长度或格式有误，请查证;',
            );
            exit(json_encode($return_msg));
        }else{
            $return_msg = array(
                'sign'=>1,
                'msg'=>'修改成功！',
            );
            exit(json_encode($return_msg));
        }
    }

    /**
     * 【生成不重复的特权码】
     * @return mixed
     */
    public function createPrivilegeId(){
        $privilege_id = rand(1000,9999);
        $Partner = M('Partner');
        $partnerInfo = $Partner->where(array('privilege_id'=>$privilege_id))->find();
        if(!$partnerInfo){
            return $privilege_id;
        }else{
            return $this->createPrivilegeId();
        }
    }

    public function delPartner(){
        $return_msg = array(
            'sign'=>-1,
            'msg'=>'暂未开放此权限，请联系管理员！',
        );
        exit(json_encode($return_msg));
        $p_id = I('post.p_id');
        $Partner = M('Partner');
        $res = $Partner->where(array('p_id'=>$p_id))->delete();
        if($res){
            $return_msg = array(
                'sign'=>1,
                'msg'=>'删除成功',
            );
            exit(json_encode($return_msg));
        }else{
            $return_msg = array(
                'sign'=>-1,
                'msg'=>'删除失败！',
            );
            exit(json_encode($return_msg));
        }
    }

    
    public function searchPartner(){

        $keyword = I('post.keyword');
        $p_type = I('post.p_type');
        $where['p.p_id']            = array('like', '%' . $keyword . '%');
        $where['p.p_name']          = array('like', '%' . $keyword . '%');
        $where['p.p_phone']         = array('like', '%' . $keyword . '%');
        $where['p.weixin_openid']   = array('like', '%' . $keyword . '%');
        $where['p.promotion_store'] = array('like', '%' . $keyword . '%');
        $where['_logic'] = 'or';
        $map['_complex'] = $where;
        if(!empty($p_type)){
            $map['p.promotion_type']  = $p_type;
        }
        $Partner = D('Partner');
        $partnerData = $Partner->searchPartner($map);
        exit(json_encode($partnerData));
    }
}