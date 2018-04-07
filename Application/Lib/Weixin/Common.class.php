<?php
/**
 * 调用微信接口的相关方法
 */
namespace Lib\Weixin;
use Org\Wechat\sendMessage;

class Common{
    /**
     * 获取有赞用户信息
     * @param $subscriber_info
     * @return array
     */
    public function getWeixinInfo($subscriber_info){
        /** 实例化微信接口类 */
        $WxClass = new \Org\Wechat\User();
        // 获取用户微信信息
        $wx_info = $WxClass->getUserInfo($subscriber_info['weixin_openid']);
        
        /** 判断返回结果是否为成功 */
        if($wx_info['sign']==1){
//            $subscriber_info['nick']=$wx_info['json_value']['nickname'];
            $subscriber_info['nick']=preg_replace('/[\x{10000}-\x{fffff}]+/u', '', $wx_info['json_value']['nickname']);
            $subscriber_info['avatar']=$wx_info['json_value']['headimgurl'];
            $subscriber_info['sex']=($wx_info['json_value']['sex']==1)?'m':'f';
            $subscriber_info['province']=$wx_info['json_value']['province'];
            $subscriber_info['city']=$wx_info['json_value']['city'];
        }else{
            // 接口返回失败
            $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'Association subscriber'.PHP_EOL.var_export($subscriber_info,true);
            $log_data .= PHP_EOL.'Error code: '.PHP_EOL.$wx_info['errcode'].PHP_EOL.'Error Msg: '.PHP_EOL.$wx_info['msg'];
            wxlogg('Wx_interface_error', __METHOD__, $log_data);
        }
        return $subscriber_info;
    }


    /**
     * 构造微信回复数据
     * @param $key_flag ,扫码关注所带参数的类型，门店、合伙人、营销活动、默认
     * @param $update_tags, 是否更新了推广人标记tags
     * @param $event_key, 扫码关注所带参数具体值
     * @param $openid, 扫码关注用户的微信openid
     * @param $SendObj, 微信消息发送类
     * @return array
     */
    public function structureMessage($key_flag, $update_tags, $event_key, $openid, $SendObj){
        // 图文消息
        $item_content = null;
        // 文本消息
        $text_content = null;
        
        /** 加载微信配置项 */
        $WXCONF = require 'Application/Weixin/Conf/config.php';
        /** 获取用户信息 */
        $CustomerModel = D('Weixin/customer');
        $customer = $CustomerModel->findCustomerByOpenid($openid);
        
        /** 判断扫码参数的类型 */
        switch($key_flag){
            case 'store':
            // 带门店标记的二维码
                // 用户商品标记中获取推送消息 */
                if($customer['g_id']!='0'){
                    // 获取用户扫码商品标记的信息，构建回复消息的内容 */
                    $item_content = $CustomerModel->getCustomerScanProduct($customer['g_id'],$event_key);
                }else{
                    // 用户没有商品标记 */
                    $text_string = '欢迎您关注66明镜~'.PHP_EOL.'掐指一算你在'.$customer['store_tags'];
                    $text_string .= PHP_EOL.'消费满166，首单立减66，次单立减50，配镜低至66！！！！！！';
                    $text_string .= PHP_EOL.'还等啥？！';
                    $text_content = $text_string;
                }
                break;
            case 'partner':
            // 合伙人标记的二维码
                // 构造带推广人信息的回复消息 */
                $text_content = $this->getPartnerText($update_tags, $event_key);
                break;
            case 'event':
            // 营销事件标记的二维码  todo
                $event_id = substr($event_key,1);
                // 从微信模块配置信息中,读取回复消息的内容 */
                $item_content = $WXCONF['RESPONSE_MSG_'.$event_id]?$WXCONF['RESPONSE_MSG_'.$event_id]:$WXCONF['RESPONSE_MSG_DEFAULT'];
                break;
            default:
            // 默认二维码或者公众号名片
                $text_string = '欢迎关注66明镜公众号';
                $text_string .= PHP_EOL.'消费满166，首单立减66，次单立减50，配镜低至66！！！！！！';
                $text_string .= PHP_EOL.'年底送福利，下单即可抽奖，1～5折返现，最高免单！！！！！！';
                $text_string .= PHP_EOL.'还等啥？！？！';
                $text_content = $text_string;
        }
        if($item_content){
            // 回复图文消息
            $item = null;
            foreach ($item_content as $key => $v){
                $item[$key]['title'] = $v['title'];
                $item[$key]['description'] = $v['description'];
                $item[$key]['pic_url'] = $v['pic_url'];
                $item[$key]['url'] = $v['url'];
            }
            $result = $SendObj->responseNews($item);    
        }else{
            // 回复文本消息
            $result = $SendObj->responseText($text_content);
        }
        return $result;
    }


    /**
     * 获取关注合伙人后,回复消息的内容
     * @param $update_tags 
     * @param $event_key
     * @return string
     */
    public function getPartnerText($update_tags, $event_key=''){
        outputDebugLog('in text structure',8);
        $PartnerModel = D('Weixin/partner');
        if($update_tags){
            $partner_info = $PartnerModel->getPartnerInfoById($event_key,true);
            $p_name = explode('@',$partner_info['p_name'])[0];
            outputDebugLog($partner_info['available']);
            if($partner_info['available']){
                // 关注了有效的合伙人
                switch ($partner_info['promotion_type']){
                    case '2':
                        // 全职合伙人
                        $text = $p_name.'为您服务'.PHP_EOL;
                        $text .= PHP_EOL;
                        $text .= '消费满166，首单立减66，次单立减50，配镜低至66！！！！！！'.PHP_EOL;
                        $text .= PHP_EOL;
                        $text .= '常见问题快速自主服务请回复「自助客服」';
                        if($event_key==92){
                            $text .= PHP_EOL;
                            $text .= PHP_EOL;
                            $text .= '点击 [发现66]->[推荐有奖]分享推文，得12元购镜优惠，还有现金红包哟！';
                        }
                        break;
                    case '3':
                    case '4':
                        // 校园合伙人和初级合伙人
                        $text = 'TA！'.$p_name.'！'.PHP_EOL;
                        $text .= '说要把你介绍给我认识！'.PHP_EOL;
                        $text .= '还往你钱包塞了22元现金券'.PHP_EOL;
                        $text .= '不信来66明镜门店看看啊！'.PHP_EOL;
                        $text .= '～～～～～～～～～～～～'.PHP_EOL;
                        $text .= '还有,还有！'.PHP_EOL;
                        $text .= '消费满166，首单立减66，次单立减50，配镜低至66！！！！！！'.PHP_EOL;
                        $text .= '还等啥？！'.PHP_EOL;
                        $text .= '现金券有效期至：'.PHP_EOL;
                        $text .= date('Y-m-d H:i:s',strtotime('+2 month'));
                        $text .= PHP_EOL;
                        $text .= PHP_EOL;
                        $text .= '常见问题快速自主服务请回复「自助客服」';
                        if($event_key==92){
                            $text .= PHP_EOL;
                            $text .= PHP_EOL;
                            $text .= '点击 [发现66]->[推荐有奖]分享推文，得12元购镜优惠，还有现金红包哟！';
                        }
                        break;
                    default:
                }
            }else{
                // 关注了无效的合伙人
                $text = '欢迎您关注66明镜！';
                $text .= PHP_EOL.'看在你喜欢我的份上～告诉你个秘密！';
                $text .= PHP_EOL.'消费满166，首单立减66，次单立减50，配镜低至66！！！！！！';
                $text .= '还等啥？！'.PHP_EOL;
                $text .= PHP_EOL;
                $text .= '常见问题快速自主服务请回复「自助客服」';
                if($event_key==92){
                    $text .= PHP_EOL;
                    $text .= PHP_EOL;
                    $text .= '点击 [发现66]->[推荐有奖]分享推文，得12元购镜优惠，还有现金红包哟！';
                }
            }
        }else{
            // 关注，但推广人没有更新，即原推广合伙人仍有效
            $text = '欢迎回来～'.PHP_EOL;
            $text .= '您还有22元现金券！'.PHP_EOL;
            $text .= '快到66明镜门店看看吧'.PHP_EOL;
            $text .= '消费满166，首单立减66，次单立减50，配镜低至66！！！！！！'.PHP_EOL;
            $text .= '现金券有效期至：'.PHP_EOL;
            $text .= date('Y-m-d H:i:s',strtotime('+2 month'));
            $text .= PHP_EOL;
            $text .= PHP_EOL;
            $text .= '常见问题快速自主服务请回复「自助客服」';
            if($event_key==92){
                $text .= PHP_EOL;
                $text .= PHP_EOL;
                $text .= '点击 [发现66]->[推荐有奖]分享推文，得12元购镜优惠，还有现金红包哟！';
            }
        }
        return $text;
    }


    /**
     * 微信模板消息，消息构造函数
     * @param $temp_item
     */
    public function sendTempMessage($temp_item){
        if($temp_item){
            /** 加载微信配置项 */
            $WXCONF = require 'Application/Weixin/Conf/config.php';
            // 从微信模块配置信息中,读取模板消息的内容 */
            $temp_message = $WXCONF['WX_TEMP_MSG'];
            $message_data = $temp_message[$temp_item['content_index']];
            
            // 补上需要动态填写的的内容
            $message_data['openid'] = $temp_item['openid'];
            $message_data['keyword1'] = $temp_item['keyword1'];
            $message_data['keyword2'] = $temp_item['keyword2'];
            $message_data['keyword3'] = $temp_item['keyword3'];
            $message_data['keyword4'] = $temp_item['keyword4'];
            $this->sendWxTempMessage($message_data);
        }
    }

    /**
     * 微信模板消息推送接口
     * @param $message_data
     */
	public function sendWxTempMessage($message_data){
		$send_control = new sendMessage();
		$openid = $message_data['openid'];
        
		// 发送内容数据结构
		$data = array(
			'touser' => $openid,
			'template_id' => $message_data['temp_id'],
			'url' => $message_data['url'],
			'data' => array(
				'first' => array(
					'value' => $message_data['msg'],
					'color' => '#173177',
				),
				'keyword1' => array(
					'value' => $message_data['keyword1'],
					'color' => '#173177',
				),
				'keyword2' => array(
					'value' => $message_data['keyword2'],
					'color' => '#173177',
				),
				'keyword3' => array(
					'value' => $message_data['keyword3'],
					'color' => '#173177',
				),
//                'keyword4' => array(
//					'value' => $message_data['keyword4'],
//					'color' => '#173177',
//				),
				'remark' => array(
					'value' => $message_data['remark'],
					'color' => '#ff501d',
				),
			)
		);
		$send_res = $send_control->sendTemplateMessage($data);
        if(!$send_res){
            $log_data = ' Happen time: '.date('Y-m-d H:i:s').PHP_EOL.'Association wxTempMessage'.PHP_EOL.var_export($send_res,true);
            wxlogg('Wx_sendTemplate_error', __METHOD__, $log_data);
        }
	}
}