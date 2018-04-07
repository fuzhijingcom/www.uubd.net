<?php

/**
 * 商品管理模块
 */
namespace Api\Controller;
use Api\Common\ACPopedom;
use Think\Controller\MapiController;

class GoodsController extends MapiController {

    /**
     * 获取商品详情
     */
    public function get_info(){
        switch($this->_method){
            case "get":
                $info = D("Goods")->GetInfo("`goodsid`=".intval($_GET['goods_id']));
                if (empty($info)) {
                    $this->response(array("obj" => array(),"list" => array(), "status_code" => 10001, "status_msg" => "信息不存在"), "json", 200);
                }
                //获取商品图片列表
                $imagelist = D("GoodsAttachment")->GetAll("`goodsid`=".$info['goodsid']);
                foreach ($imagelist as $key => $image){
                    if($image['is_default']){
                        $info['thumb_url'] = SITE_ATTACHMENT_URL.$image['picpath'];
                    }
                    $imagelist[$key]['imageurl'] = SITE_ATTACHMENT_URL.$image['picpath'];
                }
                //得到商品颜色列表
                $array_color = json_decode($info['color'],true);
                $color_list = D("GoodsColor")->GetAll("`color_id` IN (".implode(",",$array_color).")");
                $info['color_list'] = $color_list;
                //得到商品尺寸列表
                $array_size = json_decode($info['size'],true);
                $size_list = D("GoodsSize")->GetAll("`size_id` IN (".implode(",",$array_size).")");
                $info['size_list'] = $size_list;
                //获取运费
                $rs = get_config_cache("system@config");
                $info['yunfei'] = floatval($rs['yunfei']) ? floatval($rs['yunfei']) : 0;
                $info['shipping_charge'] = floatval($rs['shipping_charge']) ? floatval($rs['shipping_charge']) : 0;
                $this->response(array("obj" => $info,"list" => $imagelist, "status_code" => 0, "status_msg" => "获取信息成功"), "json", 200);
                break;
            case "post":
                break;
            case "put":
                break;
            case "delete":
                break;
        }
    }
}