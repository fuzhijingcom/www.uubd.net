<?php
//测试服务器
$config = array(
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES'=>array(
        //系统通知列表
        array('article/notice_list','Article/get_notice_list',array('ext'=>'json','method'=>'get')),
        //资讯详情
        array('article/info/:article_id','Article/get_info',array('ext'=>'json','method'=>'get')),
        //常见问题
        array('article/question','Article/get_question_info',array('ext'=>'json','method'=>'get')),
        //幻灯片列表
        array('slide/list','Slide/get_list',array('ext'=>'json','method'=>'get')),
        //商品详情
        array('goods/info/:goods_id','Goods/get_info',array('ext'=>'json','method'=>'get')),


        //获取购物车信息列表
        array('cart/list','Cart/get_list',array('ext'=>'json','method'=>'get')),
        //添加购物车
        array('cart/add','Cart/add',array('ext'=>'json','method'=>'post')),
        //编辑购物车信息
        array('cart/edit/:cart_id','Cart/edit',array('ext'=>'json','method'=>'put')),
        //删除购物车信息
        array('cart/delete/:cart_id','Cart/delete',array('ext'=>'json','method'=>'delete')),


        //优惠券列表
        array('coupon/list/','Coupon/get_list',array('ext'=>'json','method'=>'get')),
        //可用优惠券列表
        array('coupon/list/:order_id','Coupon/get_list',array('ext'=>'json','method'=>'get')),
        //领取优惠券
        array('coupon/pickup/:coupon_id','Coupon/pickup',array('ext'=>'json','method'=>'post')),


        //微信授权登录
        array('login/wechat','Login/wechat',array('ext'=>'json','method'=>'post')),
        //获取用户微信信息
        array('login/mobile','Login/mobile',array('ext'=>'json','method'=>'get')),
        //手机号+验证码登录
        array('login/mobile','Login/mobile',array('ext'=>'json','method'=>'post')),
        //发送手机验证码
        array('sms/send/:mobile','Sms/send',array('ext'=>'json','method'=>'get')),
        //检测是否登录
        //array('check','Login/check_login',array('ext'=>'json','method'=>'get')),
        //退出系统
        array('logout','Login/logout',array('ext'=>'json','method'=>'get')),

        //获取收货地址信息列表
        array('address/list','Address/get_info_list',array('ext'=>'json','method'=>'get')),
        //添加收货地址
        array('address/add','Address/add',array('ext'=>'json','method'=>'post')),
        //修改收货地址
        array('address/edit/:address_id','Address/edit',array('ext'=>'json','method'=>'put')),
        //删除地址信息
        array('address/delete/:address_id','Address/delete',array('ext'=>'json','method'=>'delete')),
        //设置默认信息
        array('address/default/:address_id','Address/set_default',array('ext'=>'json','method'=>'post')),
        //添加订单
        array('order/add','Order/add',array('ext'=>'json','method'=>'post')),
        //获取订单信息列表
        array('order/list','Order/get_list',array('ext'=>'json','method'=>'get')),
        array('order/list/:status','Order/get_list',array('ext'=>'json','method'=>'get')),
        //编辑订单信息
        //array('order/edit/:order_id','Order/edit',array('ext'=>'json','method'=>'put')),
        //删除订单信息
        array('order/delete/:order_id','Order/delete',array('ext'=>'json','method'=>'delete')),
        //获取订单详情
        array('order/info/:order_id','Order/get_info',array('ext'=>'json','method'=>'get')),
        //申请退货
        array('order/returns/:order_id','Order/returns',array('ext'=>'json','method'=>'get')),
        //申请退款
        array('order/return_charge/:order_id','Order/return_charge',array('ext'=>'json','method'=>'get')),

        //获取个人信息
        array('user/info','User/get_info',array('ext'=>'json','method'=>'get')),
        //编辑个人信息
        //array('user/update','User/edit',array('ext'=>'json','method'=>'post')),
        //设置支付密码
        array('user/account/pwd','User/set_account_password',array('ext'=>'json','method'=>'put')),
        //更改支付密码
        array('user/account/reset','User/reset_account_password',array('ext'=>'json','method'=>'put')),
        //找回支付密码
        array('user/account/find_set','User/find_set_password',array('ext'=>'json','method'=>'put')),

        //支付信息
        array('pay/wechat/notice','Pay/wechat_notice',array('ext'=>'json','method'=>'get')),
        array('pay/success/:order_id','Pay/success',array('ext'=>'json','method'=>'get')),
        array('pay/fail/:order_id','Pay/fail',array('ext'=>'json','method'=>'get')),
        array('pay/unknow/:order_id','Pay/unknow',array('ext'=>'json','method'=>'get')),
        //微信支付
        array('pay/wechat/:order_id','Pay/wechat',array('ext'=>'json','method'=>'get')),
        //余额支付
        array('pay/balance/:order_id','Pay/balance',array('ext'=>'json','method'=>'post')),
        //微信充值平台币
        array('recharge','Pay/recharge',array('ext'=>'json','method'=>'post')),
    ),
    "SESSION_PASSPORT_AUTH" => "lse@pass",
);
return $config;