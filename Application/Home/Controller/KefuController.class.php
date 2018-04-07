<?php
/**
 * Created by PhpStorm.
 * User: xingact
 * Date: 17-2-8
 * Time: 下午5:09
 */

namespace Home\Controller;
use Common\Wx\Weixin;
use Think\Controller;

class KefuController extends Controller
{
    public function __construct() {
        parent::__construct();

        //将服务端信息保存到session
        $data['service_name'] = 'uubd';
        $data['service_openid'] = '66glasses';
        $data['service_faceimg'] = 'http://ofhbim2ko.bkt.clouddn.com/zxh1.jpg';

        session('serviceInfo',$data);
    }


    /**
     * [根据openid获取用户信息]
     * @param $openid
     * @return mixed
     */
    public function getCookieUserInfo($openid){
        if(cookie($openid)){
            return cookie($openid);
        }else{
            $Customer = M('Customer');
            $userInfo = $Customer->where(array('weixin_openid'=>$openid))->find();
            cookie($openid,serialize($userInfo));
            return cookie($openid);
        }
    }

    public function index() {
        $this->display('checkInfos');
    }


    /**
     * [聊天界面]
     */
    public function kefu(){

        $open_id = I('get.openid','','htmlspecialchars');
        if(!$open_id){
            exit('缺少必要的参数：openid');
        }

        //获取用户信息
        $userInfo = unserialize($this->getCookieUserInfo($open_id));

        //获取跟当前用户的所有记录
        $CustomerService = M('customer_service');
        $msgData = $CustomerService->where(array('customer'=>$open_id))->select();


        cookie('msg_ids',null);

        //ids保存已经显示的信息id，保证下次获取新信息时没有重复的
        $ids = ',';
        $id = '';
        foreach($msgData as $k => $v){
            if((int)$v['status'] === 1){
                $id .= $v['id'].',';
            }
            $ids .= $v['id'].',';
            $msgData[$k]['faceimg'] = $userInfo['avatar'];
        }
        cookie('msg_ids',$ids);

        if($id){
            $id = rtrim($id,',');
            $sql = " UPDATE customer_service SET status = 0 where id IN($id)";
            $CustomerService->execute($sql);
        }

        //获取快捷回复语句
        $QuickReply = M('quick_reply');
        $quickReplayData = $QuickReply->where(array('service_person'=>'66glasses'))->order('id desc')->select();

        $this->assign('quickReplyData',$quickReplayData);

        $this->assign('userInfo',$userInfo);
        $this->assign('msgData',$msgData);
        $this->display();
    }


    /**
     * [发送信息]
     */
    public function SendMsg(){

        $open_id = I('post.openid','');

        $userInfoPass = $this->getCookieUserInfo($open_id);
        $userInfo = unserialize($userInfoPass);

        if(!IS_POST){
            $return_data = array(
                'sign'  => -1,
                'msg'   => '请求方式有误！',
            );
            exit(json_encode($return_data));
        }

        //接收前端传递的数据(发送内容)
        $text = I('post.text','','htmlspecialchars');
//        dump($text);die;
        //判断用户发送的内容
        if(!trim($text)){
            $return_data = array(
                'sign'  => -1,
                'msg'   => '发送内容不能为空',
            );
            exit(json_encode($return_data));
        }

        //构造需要的数据
        $insert_data = array(
            'service_person' => session('serviceInfo')['service_openid'],
            'customer'       => $userInfo['weixin_openid'],
            'customer_name'  => $userInfo['nick'],
            'is_send'        => 0,
            'message'        => htmlspecialchars_decode($text),
            'time'           => date('Y-m-d H:i:s'),
            'status'         => 1,
            'star'           => 0,
            'remark'         => '',
        );

        //实例化表
        $CustomerService = M('customer_service');
        $res = $CustomerService->add($insert_data);

        //判断结果
        if($res){
            //成功后将当前的id保存到cookie的ids，保证下次拉取数据时不会将该条数据拉出来
            $ids = cookie('msg_ids')?cookie('msg_ids'):',';
            $ids .= $res.',';
            cookie('msg_ids',$ids);

            //成功后将用户发送的数据返回（转义html字符）
            $return_data = array(
                'sign'  => 1,
                'msg'   =>'发送成功',
                'message'  =>htmlspecialchars_decode($text),
            );
        }else{
            $return_data = array(
                'sign'  => -1,
                'msg'   =>'发送失败'
            );
        }

        //返回给前端
        exit(json_encode($return_data));

    }


    /**
     * [发送新信息]
     */
    public function getMessage(){
        $open_id = I('post.openid','','htmlspecialchars');

        $CustomerService = M('customer_service');
        $sql = " select * from customer_service where customer = '{$open_id}'";
//        $sql .= " and is_send = 1";
        $sql .= " order by time desc";
        $sql .= " limit 10";

        $msgData = $CustomerService->query($sql);

        //ids保存已经显示的信息id，保证下次获取新信息时不会有重复的
        $ids = cookie('msg_ids')?cookie('msg_ids'):',';
        $id = '';
        foreach($msgData as $k => $v){
            if(strrpos(','.$ids.',',$v['id']) !== false){
                unset($msgData[$k]);
            }else{
                $msgData[$k]['faceimg'] = unserialize(cookie($open_id))['avatar'];
                $ids .= $v['id'].',';
                $id .= $v['id'].',';
            }
        }

        if($id){
            $id = rtrim($id,',');
            //将信息改为已读
            $sql = " UPDATE customer_service SET status = 0 where id in($id)";
            $CustomerService->execute($sql);
        }

        cookie('msg_ids',$ids);

        exit(json_encode($msgData));

    }


    /**
     * [加星/去星]
     */
    public function changeStar(){
        $message_id = I('post.id',0,'htmlspecialchars_decode');
        $CustomerService = M('customer_service');
        $info = $CustomerService->where(array('id'=>$message_id))->find();
        if(!$info){
            $return_data = array(
                'sign'=>-1,
                'msg'=>'该信息不存在',
            );
            exit(json_encode($return_data));
        }

        $flag = 0;
        if((int)$info['star'] === 0){
            $flag = 0;
            $res = $CustomerService->where(array('id'=>$message_id))->save(array('star'=>1));
        }else{
            $flag = 1;
            $res = $CustomerService->where(array('id'=>$message_id))->save(array('star'=>0));
        }

        if($res){
            $return_data = array(
                'sign'  => 1,
                'msg'   => '操作成功',
                'text'  => $flag==1?'加星':'去星',
            );
        }else{
            $return_data = array(
                'sign'  => -1,
                'msg'   => '加星失败',
            );
        }

        exit(json_encode($return_data));
    }

    /**
     * [添加/取消 备注]
     */
    public function changeRemark(){
        $message_id = I('post.id',0,'htmlspecialchars_decode');
        $message_remark = I('post.text','','htmlspecialchars_decode');

        $CustomerService = M('customer_service');
        $info = $CustomerService->where(array('id'=>$message_id))->find();
        if(!$info){
            $return_data = array(
                'sign'=>-1,
                'msg'=>'该信息不存在',
            );
            exit(json_encode($return_data));
        }

        $flag = 0;
        if($info['remark'] === ''){
            $flag = 0;
            $res = $CustomerService->where(array('id'=>$message_id))->save(array('remark'=>$message_remark));
        }else{
            $flag = 1;
            $res = $CustomerService->where(array('id'=>$message_id))->save(array('remark'=>''));
        }

        if($res){
            $return_data = array(
                'sign'  => 1,
                'msg'   => '操作成功',
                'text'  => $flag==1?'备注':'取消备注',
            );
        }else{
            $return_data = array(
                'sign'  => -1,
                'msg'   => '操作失败',
            );
        }

        exit(json_encode($return_data));
    }

    /*
     * 获取用户订单信息
     */
    public function getUserOrders() {
        $openid = I('get.openid','','htmlspecialchars');

        $Tradetail = new \Weixin\Model\TradedetailModel();
        $field = 'title,status,payment,tid,created,express_id';

        $orderInfo = $Tradetail->getOrderInfoByOpenId($openid,$field);

        foreach ($orderInfo as $k => $v) {
            $otherInfo = $Tradetail->getGoodInfoByTid($v['tid']);
            $orderInfo[$k]['img_url'] = $otherInfo['img_url'];
        }

        $this->ajaxReturn($orderInfo);
    }

    /*
     * 获取各种类型信息
     */
    public function getDiffInfo() {
        $info_type = I('post.info_type','','htmlspecialchars');

        $CustomerService = M('customer_service');
        $map['is_send'] = 1;

        switch ($info_type) {
            case 'unread' :
                $map['status'] = 1;
                $res_data = $CustomerService->where($map)->order('time desc')->select();
                $length = count($res_data);
                $res_data = $this->groupData($res_data);
                break;
            case 'star' :
                $map['star'] = 1;
                $res_data = $CustomerService->where($map)->order('time desc')->select();
                $length = count($res_data);
                break;
            case 'remark' :
                $map['remark'] = ['neq',''];
                $res_data = $CustomerService->where($map)->order('time desc')->select();
                $length = count($res_data);
                break;
            case 'read' :
                $map['status'] = 0;
                $res_data = $CustomerService->where($map)->order('time desc')->select();
                $length = count($res_data);
                $res_data = $this->groupData($res_data);
                break;
        }

        /*获取头像*/
        $Customer = M('customer');
        foreach ($res_data as $k => $v) {
            $avatar = $Customer->where(array('weixin_openid' => $v['customer']))->field('avatar')->find();
            $res_data[$k]['avatar'] = $avatar['avatar'];
        }

        $res_data = [
            'data' => $res_data,
            'length' => $length,
        ];

        outputDebugLog($res_data,8);

        $this->ajaxReturn($res_data);
    }

    public function groupData($data) {
        $arr = [];
        $return_arr = [];
        foreach ($data as $key => $value) {
            if (array_key_exists($value['customer'], $arr)) {
                $arr[$value['customer']] += 1;
                continue;
            } else {
                $return_arr[] = $value;

                $arr[$value['customer']] = 1;
            }
        }

        foreach ($return_arr as $key => $value) {
            $return_arr[$key]['length'] = $arr[$value['customer']];
        }

        return $return_arr;
    }


    public function addQuickReply(){
        $text = I('post.text','','htmlspecialchars');
        if(!$text){
            $return_data = array(
                'sign' => -1,
                'msg'  => '不能为空！',
            );
            exit(json_encode($text));
        }

        $insert_data = array(
            'service_person'    => '66glasses',
            'text'              => $text,
        );

        $QuickReply = M('quick_reply');
        $res = $QuickReply->add($insert_data);

        if($res){
            $return_data = array(
                'sign' => 1,
                'msg'  => '添加成功',
            );
        }else{
            $return_data = array(
                'sign' => -1,
                'msg'  => '添加失败！',
            );
        }
        exit(json_encode($return_data));
    }
}