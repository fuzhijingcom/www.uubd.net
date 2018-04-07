  
**面包接龙接口文档**

Author:  [HP](mailto:1192797131@qq.com)

[前言](#前言)


* 1、	[登录](#登陆接口)
* 2、	[获取收货地址信息列表](#获取收货地址信息列表接口)
* 3、	[添加收货地址](#添加收货地址接口)
* 4、	[修改收货地址](#修改收货地址接口)
* 5、	[删除地址信息](#删除地址信息接口)
* 6、	[设置默认信息](#设置默认信息接口)
* 7、	[获取自取地址列表](#获取自取地址列表接口)
* 8、	[常见问题](#常见问题接口)
* 9、	[获取资讯详情](#获取资讯详情接口)
* 10、	[获取购物车信息列表](#获取购物车信息列表接口)
* 11、	[添加购物车](#添加购物车接口)
* 12、	[编辑购物车信息](#编辑购物车信息接口)暂未开发
* 13、	[删除购物车信息](#删除购物车信息接口)
* 14、	[获取购物车详情](#获取购物车详情接口)暂未开发
* 15、	[获取活动信息列表](#获取活动信息列表接口)
* 16、	[获取活动参与者信息列表](#获取活动参与者信息列表接口)
* 17、	[获取活动详情](#获取活动详情接口)
* 18、	[获取广告](#获取广告接口)
* 19、	[获取接龙列表](#获取接龙列表接口)
* 20、	[检测是否登录](#检测是否登录接口)暂未开发
* 21、	[退出系统](#退出系统接口)暂未开发
* 22、	[获取订单信息列表](#获取订单信息列表接口)
* 23、	[添加订单](#添加订单接口)
* 24、	[编辑订单信息](#编辑订单信息接口)暂未开发
* 25、	[删除订单信息](#删除订单信息接口)
* 26、	[获取订单详情](#获取订单详情接口)
* 27、	[获取个人信息](#获取个人信息接口)
* 28、	[编辑个人信息](#编辑个人信息接口)暂未开发
* 29、	[微信支付](#微信支付接口接口)
* 30、	[微信支付成功](#微信支付成功接口)
* 31、	[微信支付失败](#微信支付失败接口)
* 32、	[微信支付未知错误](#微信支付未知错误接口)
<h2>说明</h2>
版本号：V1.0  

测试接口地址：https://sh.seejiajia.com/Api/


##

正式接口地址：http://******


##

错误号说明：

		0		操作成功
		1		操作失败
		
		
##


1、<h3>登陆接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/login/wechat` 

 接口方法：POST

 POST参数：
>
	[必须] 	code				:	微信code
	[必须]	encryptedData		:	用户授权appkey，固定为htxly
	[必须]	iv					:	用户微信openid


返回结果：
>
	返回数据
    {
	    "obj": {
	        "openid": 微信openid,
			"nickname": 微信昵称,
			"headimgurl":微信头像地址,
			"unionid":微信unionid,
			"token":登录成功token
	    },
		"list":{},
	    "status_code": "0",
	    "status_msg": "授权成功"
	}


2、<h3>获取收货地址信息列表接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/address/list` 

 接口方法：GET

 HEADER参数：
>
	 [必须]	token	:	登录成功获取到的token


返回结果：
>
	返回数据
    {
	    "obj": {},
		"list":收货地址列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}


3、<h3>添加收货地址接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/address/add` 

 接口方法：POST

 HEADER参数：
>
	 [必须]	token	:	登录成功获取到的token

POST参数：
>
	 [必须]	consignee	:	收货人信息
	 [必须]	province	:	省份信息
     [必须]	city		:	城市信息
	 [必须]	district	:	地区信息
     [必须]	address		:	详细地址信息
     [必须]	mobile		:	收货人手机号码
     [选填]	is_default	:	是否默认收货地址
     [选填]	is_pickup	:	是否自提地址


返回结果：
>
	返回成功
>
    {
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "添加成功"
	}
>
	返回失败
>
    {
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "添加失败"
	}

4、<h3>修改收货地址接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/address/edit/1` 

 接口方法：PUT
>
 URL上带上需要修改的地址ID

HEADER参数：
>
	 [必须]	token	:	登录成功获取到的token

 PUT参数：
>
	 [必须]	consignee	:	收货人信息
	 [必须]	province	:	省份信息
     [必须]	city		:	城市信息
	 [必须]	district	:	地区信息
     [必须]	address		:	详细地址信息
     [必须]	mobile		:	收货人手机号码
     [选填]	is_default	:	是否默认收货地址
     [选填]	is_pickup	:	是否自提地址


返回结果：
>
	返回成功
>
    {
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "更新成功"
	}
>
	返回失败
>
    {
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "更新失败"
	}
5、<h3>删除地址信息接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/address/delete/1` 

 接口方法：DELETE
>
 URL上带上需要修改的地址ID

 HEADER参数：
>
	[必须]	token	:	登录成功获取到的token


返回结果：
>
	返回成功
>
    {
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "删除成功"
	}
>
	返回失败
>
    {
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "删除失败"
	}

6、<h3>设置默认信息接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/address/default/1` 

 接口方法：POST
>
 URL上带上需要修改的地址ID

 HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 POST参数：
>
	[必须]	is_default	:	是否默认收货地址

返回结果：
>
	{
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "设置失败"
	}
>
	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "设置成功"
	}


7、<h3>获取自取地址列表接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/address/pick_list` 

 接口方法：GET

 HEADER参数：
>
	[必须]	token	:	登录成功获取到的token



返回结果：
>
	
	{
	    "obj": {},
		"list":自提地址列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}

8、<h3>常见问题接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/article/question_info` 

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token


返回结果：
>
	{
	    "obj": 常见问题信息,
		"list":{},
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}

9、<h3>获取资讯详情接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/article/info/1` 

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token
>
 URL上带上需要修改的地址ID

 
返回结果：
>
	{
	    "obj": 资讯详情信息,
		"list":{},
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}

10、<h3>获取购物车信息列表接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/cart/list`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token


返回结果：
>
	{
	    "obj": {},
		"list":购物车列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}


11、<h3>添加购物车接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/cart/add`

 接口方法：POST

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 POST参数：
>
	[必须]	goods_id		:	商品ID
	[必须]	goods_number	:	商品购买数量
	[必须]	act_id			:	活动ID


返回结果：
>

	{
	    "obj": {
			'cart_number' 	: 购物车商品数量,
			'cart_price'	: 购物车商品价格
		},
		"list":{},
	    "status_code": "0",
	    "status_msg": "商品加入购物车成功"
	}

>

	{
	    "obj": {
			'cart_number' 	: 购物车商品数量,
			'cart_price'	: 购物车商品价格
		},
		"list":{},
	    "status_code": "1",
	    "status_msg": "商品加入购物车失败"
	}
12、<h3>编辑购物车信息接口(暂未开放)</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/cart/edit/1`

 接口方法：PUT
>
 URL上带上需要修改的购物车ID


HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 PUT参数：
>

返回结果：
>

	{
	    "obj": {
			'cart_number' 	: 购物车商品数量,
			'cart_price'	: 购物车商品价格
		},
		"list":{},
	    "status_code": "0",
	    "status_msg": "更新成功"
	}

>

	{
	    "obj": {
			'cart_number' 	: 购物车商品数量,
			'cart_price'	: 购物车商品价格
		},
		"list":{},
	    "status_code": "1",
	    "status_msg": "更新失败"
	}


13、<h3>删除购物车信息接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/cart/delete/1`

 接口方法：DELETE

>
 URL上带上需要修改的购物车ID


HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 PUT参数：
>
	无

返回结果：
>

	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "删除成功"
	}

>

	{
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "删除失败"
	}



14、<h3>获取购物车详情接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/cart/goods/1`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	[必须]	cart_id	:	购物车ID
返回结果：
>

	{
	    "obj": {},
		"list":购物车商品列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}

>

	{
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "获取信息失败"
	}

15、<h3>获取活动信息列表接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/activity/list/1`

 接口方法：GET

 GET参数：
>
	[必须]	act_id	:	活动ID


返回结果：
>
	{
	    "obj": {},
		"list":活动信息列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}


16、<h3>获取活动参与者信息列表接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/activity/member_list/1`

 接口方法：GET

 GET参数：
>
	[必须]	act_id	:	活动ID


返回结果：
>
	{
	    "obj": {},
		"list":活动参与者信息列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}


17、<h3>获取活动详情接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/activity/info/1`

 接口方法：GET

 GET参数：
>
	[必须]	act_id	:	活动ID


返回结果：
>
	{
	    "obj": 活动详情信息,
		"list":{},
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}


18、<h3>获取广告接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/ad`

 接口方法：GET

 GET参数：
>
	无

返回结果：
>
	{
	    "obj": {},
		"list":广告列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}

19、<h3>获取接龙列表接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/activity_list/1`

 接口方法：GET

 GET参数：
>
	[可选] page	: 当前页码



返回结果：
>
	{
	    "obj": {},
		"list":接龙信息列表,
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}


20、<h3>检测是否登录接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/login/check`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	无


返回结果：
>
	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "已经登录"
	}


21、<h3>退出系统接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/logout`

 接口方法：GET

 GET参数：
>
	无


返回结果：
>
	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "退出登录"
	}


22、<h3>获取订单信息列表接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/order/list/1`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token


 GET参数：
>
	[可选] page	: 当前页码

返回结果：
>
	{
	    "obj": 订单列表,
		"list":{},
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}


23、<h3>添加订单接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/order/add`

 接口方法：POST

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token


 POST参数：
>
	[必须] cart_id		: 购物车ID
	[必须] act_id		: 活动ID
	[必须] address_id	: 购物车ID


返回结果：

>
	成功返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "下单成功"
	}
>
	失败返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "下单失败"
	}

24、<h3>编辑订单信息接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/order/edit/1`

 接口方法：PUT

>
 URL上带上需要修改的订单ID

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 PUT参数：
>
	暂无


返回结果：
>
	成功返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "更新成功"
	}
>
	失败返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "更新失败"
	}


25、<h3>删除订单信息接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/order/delete/1`

 接口方法：DELETE

>
 URL上带上需要删除的订单ID

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 DELETE参数：
>
	暂无


返回结果：
>
	成功返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "删除成功"
	}
>
	失败返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "删除失败"
	}


26、<h3>获取订单详情接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/order/info/1`

 接口方法：GET

>
 URL上带上需要查看详情的订单ID

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	暂无


返回结果：
>
	成功返回
	{
	    "obj": 订单信息,
		"list":订单商品列表信息,
	    "status_code": "0",
	    "status_msg": "获取订单详情"
	}

27、<h3>获取个人信息接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/user/info`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	暂无


返回结果：
>
	成功返回
	{
	    "obj": 个人信息,
		"list":{},
	    "status_code": "0",
	    "status_msg": "获取信息成功"
	}

28、<h3>编辑个人信息接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/user/update`

 接口方法：PUT

>
 URL上带上需要删除的订单ID

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 PUT参数：
>
	暂无


返回结果：
>
	成功返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "0",
	    "status_msg": "更新成功"
	}
>
	失败返回
	{
	    "obj": {},
		"list":{},
	    "status_code": "1",
	    "status_msg": "更新失败"
	}

29、<h3>微信支付接口接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/pay/1`

 接口方法：GET

>
 URL上带上需要支付的订单ID

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	[必须]	order_id	:	订单ID


返回结果：
>
	成功返回
	{
	    "obj": {
			jsApiParameters  	: 支付JS参数
			jump_success_url 	: 支付成功跳转接口
			jump_fail_url		: 支付失败跳转接口
			jump_unknow_url		: 支付未知错误跳转接口
		},
		"list":{},
	    "status_code": "0",
	    "status_msg": "调起支付成功"
	}

30、<h3>微信支付成功接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/pay/success?order_id=1`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	[必须]	order_id	:	订单ID


返回结果：
>
	成功返回
	{
	    "obj": {
			order_charge  		: 订单金额
			order_id 			: 订单ID
			order_sn			: 订单号
		},
		"list":{},
	    "status_code": "0",
	    "status_msg": "支付成功"
	}

31、<h3>微信支付失败接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/pay/fail?order_id=1`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	[必须]	order_id	:	订单ID


返回结果：
>
	成功返回
	{
	    "obj": {
			order_charge  		: 订单金额
			order_id 			: 订单ID
			order_sn			: 订单号
		},
		"list":{},
	    "status_code": "0",
	    "status_msg": "支付失败"
	}


32、<h3>微信支付未知错误接口</h3>   

【调用方式】  

接口地址：`https://sh.seejiajia.com/Api/pay/unknow?order_id=1`

 接口方法：GET

HEADER参数：
>
	[必须]	token	:	登录成功获取到的token

 GET参数：
>
	[必须]	order_id	:	订单ID


返回结果：
>
	成功返回
	{
	    "obj": {
			order_charge  		: 订单金额
			order_id 			: 订单ID
			order_sn			: 订单号
		},
		"list":{},
	    "status_code": "0",
	    "status_msg": "未知错误"
	}


