<?php
/**
 * 后台操作菜单
 */
return array(
	"MENU" => array(	
		//系统管理模块
		'system'	=> array(
			'role'	=> 0,
			'url' 	=> "#",
			'key' 	=> 'system',
			'name' 	=> "系统管理",
			'sub' 	=> array(
				'config'		=> array(//商城设置
					'role'	=> 0,
					'url'	=> '/Admin/Config/',
					'key'	=> 'config',
					'name'	=> '系统设置',
					'sub'	=> "",
				),
				'chpwd'	=> array(//修改密码
                    'role'	=> 0,
                    'url'	=> '/Admin/Config/pwd',
                    'key'	=> 'chpwd',
                    'name'	=> '修改密码',
                    'sub'	=> ''
                ),
				'admin'	=> array(//管理员列表
					'role'	=> 0,
					'url'	=> '/Admin/Admin/',
					'key'	=> 'admin',
					'name'	=> '管理员管理',
					'sub'	=> array(
						'add'	=> array(//添加管理员信息
							'role'	=> 0,
							'url'	=> '/Admin/Admin/add/',
							'key'	=> 'add',
							'name'	=> '添加',
							'sub'	=> '',
						),
						'edit'	=> array(//修改管理员信息
							'role'	=> 0,
							'url'	=> '/Admin/Admin/edit/',
							'key'	=> 'edit',
							'name'	=> '修改',
							'sub'	=> '',
						),
						'delete'	=> array(//删除管理员信息
							'role'	=> 0,
							'url'	=> '/Admin/Admin/delete/',
							'key'	=> 'delete',
							'name'	=> '删除',
							'sub'	=> '',
						),
						'lock'	=> array(//锁定管理员信息
							'role'	=> 0,
							'url'	=> '/Admin/Admin/lock/',
							'key'	=> 'lock',
							'name'	=> '锁定',
							'sub'	=> '',
						),
					),
				),
				'group'		=> array(//用户组管理
					'role'	=> 0,
					'url'	=> '/Admin/Group/',
					'key'	=> 'group',
					'name'	=> '用户组管理',
					'sub'	=> array(
						'add'	=> array(//添加用户组信息
							'role'	=> 0,
							'url'	=> '/Admin/Group/add/',
							'key'	=> 'add',
							'name'	=> '添加',
							'sub'	=> '',
						),
						'edit'	=> array(//修改用户组信息
							'role'	=> 0,
							'url'	=> '/Admin/Group/edit/',
							'key'	=> 'edit',
							'name'	=> '修改',
							'sub'	=> '',
						),
						'delete'	=> array(//删除用户组信息
							'role'	=> 0,
							'url'	=> '/Admin/Group/delete/',
							'key'	=> 'delete',
							'name'	=> '删除',
							'sub'	=> '',
						),
						'popedom'	=> array(//设置用户组权限信息
							'role'	=> 0,
							'url'	=> '/Admin/Group/popedom/',
							'key'	=> 'popedom',
							'name'	=> '设置权限',
							'sub'	=> '',
						),
					),
				),
				'slide'		=> array(//首页幻灯片设置
					'role'	=> 0,
					'url'	=> '/Admin/Slide/',
					'key'	=> 'slide',
					'name'	=> '幻灯片设置',
					'sub'	=> array(
						'add'	=> array(//添加
							'role'	=> 0,
							'url'	=> '/Admin/Slide/add/',
							'key'	=> 'add',
							'name'	=> '添加',
							'sub'	=> '',
						),
						'edit'	=> array(//修改
							'role'	=> 0,
							'url'	=> '/Admin/Slide/edit/',
							'key'	=> 'edit',
							'name'	=> '修改',
							'sub'	=> '',
						),
						'delete'	=> array(//删除
							'role'	=> 0,
							'url'	=> '/Admin/Slide/delete/',
							'key'	=> 'delete',
							'name'	=> '删除',
							'sub'	=> '',
						),
					),
				),
			)
		),
		'member'	=> array(
			'role'	=> 0,
			'url' 	=> "#",
			'key' 	=> 'member',
			'name' 	=> "客户管理",
			'sub' 	=> array(
				/*'member_level'	=> array(//分类管理信息
                    'role'	=> 0,
                    'url'	=> '/Admin/MemberLevel/',
                    'key'	=> 'member_level',
                    'name'	=> '等级列表',
                    'sub'	=> array(
                        'add'	=> array(//添加
                            'role'	=> 0,
                            'url'	=> '/Admin/MemberLevel/add/',
                            'key'	=> 'add',
                            'name'	=> '添加',
                            'sub'	=> '',
                        ),
                        'edit'	=> array(//修改
                            'role'	=> 0,
                            'url'	=> '/Admin/MemberLevel/edit/',
                            'key'	=> 'edit',
                            'name'	=> '修改',
                            'sub'	=> '',
                        ),
                        'delete'	=> array(//删除
                            'role'	=> 0,
                            'url'	=> '/Admin/MemberLevel/delete/',
                            'key'	=> 'delete',
                            'name'	=> '删除',
                            'sub'	=> '',
                        ),
                    ),
                ),*/
				'member'	=> array(//资讯管理信息
					'role'	=> 0,
					'url'	=> '/Admin/Member/',
					'key'	=> 'member',
					'name'	=> '客户列表',
					'sub'	=> array(
						'lock'	=> array(//锁定
							'role'	=> 0,
							'url'	=> '/Admin/Member/lock/',
							'key'	=> 'lock',
							'name'	=> '锁定',
							'sub'	=> '',
						),
						'point'	=> array(//设置积分
							'role'	=> 0,
							'url'	=> '/Admin/Member/point/',
							'key'	=> 'point',
							'name'	=> '设置积分',
							'sub'	=> '',
						),
						'experience'	=> array(//设置经验
							'role'	=> 0,
							'url'	=> '/Admin/Member/experience/',
							'key'	=> 'experience',
							'name'	=> '设置经验',
							'sub'	=> '',
						),
						'balance'	=> array(//设置金额
							'role'	=> 0,
							'url'	=> '/Admin/Member/balance/',
							'key'	=> 'balance',
							'name'	=> '设置金额',
							'sub'	=> '',
						),
						'money'	=> array(//设置余额
							'role'	=> 0,
							'url'	=> '/Admin/Member/money/',
							'key'	=> 'money',
							'name'	=> '设置余额',
							'sub'	=> '',
						),
						'level'	=> array(//设置等级
							'role'	=> 0,
							'url'	=> '/Admin/Member/level/',
							'key'	=> 'level',
							'name'	=> '设置等级',
							'sub'	=> '',
						),
                        'delete'	=> array(//删除
                            'role'	=> 0,
                            'url'	=> '/Admin/Member/delete/',
                            'key'	=> 'delete',
                            'name'	=> '删除',
                            'sub'	=> '',
                        ),
                        'edit'	=> array(//编辑
                            'role'	=> 0,
                            'url'	=> '/Admin/Member/edit/',
                            'key'	=> 'edit',
                            'name'	=> '编辑',
                            'sub'	=> '',
                        ),
					),
				),
                'order' => array(
                    'role'	=> 0,
                    'url'	=> '/Admin/Member/order',
                    'key'	=> 'order',
                    'name'	=> '历史订单列表',
                    'sub'	=> array(
                        'orderlist'	=> array(//订单详情
                            'role'	=> 0,
                            'url'	=> '/Admin/Member/orderlist/',
                            'key'	=> 'orderlist',
                            'name'	=> '订单详情',
                            'sub'	=> '',
                        ),
                    ),
                ),
                'recharge' => array(
                    'role'	=> 0,
                    'url'	=> '/Admin/Finance/recharge',
                    'key'	=> 'recharge',
                    'name'	=> '充值记录',
                    'sub'	=> "",
                )
			),
		),
		'article'	=> array(
			'role'	=> 0,
			'url' 	=> "#",
			'key' 	=> 'article',
			'name' 	=> "资讯管理",
			'sub' 	=> array(
				'category'	=> array(//分类管理信息
                    'role'	=> 0,
                    'url'	=> '/Admin/Category/',
                    'key'	=> 'category',
                    'name'	=> '分类列表',
                    'sub'	=> array(
                        'add'	=> array(//添加
                            'role'	=> 0,
                            'url'	=> '/Admin/Category/add/',
                            'key'	=> 'add',
                            'name'	=> '添加',
                            'sub'	=> '',
                        ),
                        'edit'	=> array(//修改
                            'role'	=> 0,
                            'url'	=> '/Admin/Category/edit/',
                            'key'	=> 'edit',
                            'name'	=> '修改',
                            'sub'	=> '',
                        ),
                        'delete'	=> array(//删除
                            'role'	=> 0,
                            'url'	=> '/Admin/Category/delete/',
                            'key'	=> 'delete',
                            'name'	=> '删除',
                            'sub'	=> '',
                        ),
                    ),
                ),
				'article'	=> array(//资讯管理信息
					'role'	=> 0,
					'url'	=> '/Admin/Article/',
					'key'	=> 'article',
					'name'	=> '资讯列表',
					'sub'	=> array(
						'add'	=> array(//添加
							'role'	=> 0,
							'url'	=> '/Admin/Article/add/',
							'key'	=> 'add',
							'name'	=> '添加',
							'sub'	=> '',
						),
						'edit'	=> array(//修改
							'role'	=> 0,
							'url'	=> '/Admin/Article/edit/',
							'key'	=> 'edit',
							'name'	=> '修改',
							'sub'	=> '',
						),
						'delete'	=> array(//删除
							'role'	=> 0,
							'url'	=> '/Admin/Article/delete/',
							'key'	=> 'delete',
							'name'	=> '删除',
							'sub'	=> '',
						),
						'recomm'	=> array(//推荐
							'role'	=> 0,
							'url'	=> '/Admin/Article/recomm/',
							'key'	=> 'recomm',
							'name'	=> '推荐',
							'sub'	=> '',
						),
						'top'	=> array(//置顶
							'role'	=> 0,
							'url'	=> '/Admin/Article/top/',
							'key'	=> 'top',
							'name'	=> '置顶',
							'sub'	=> '',
						),
					),
				),
			),
		),
		//商品管理模块
        'goods'	=> array(
			'role'	=> 0,
			'url' 	=> "#",
			'key' 	=> 'goods',
			'name' 	=> "商品管理",
			'sub' 	=> array(
				'category'	=> array(//商品分类管理
					'role'	=> 0,
					'url'	=> '/Admin/GoodsCategory/',
					'key'	=> 'category',
					'name'	=> '分类列表',
					'sub'	=> array(
						'add'	=> array(//添加
								'role'	=> 0,
								'url'	=> '/Admin/GoodsCategory/add/',
								'key'	=> 'add',
								'name'	=> '添加',
								'sub'	=> '',
						),
						'edit'	=> array(//修改
								'role'	=> 0,
								'url'	=> '/Admin/GoodsCategory/edit/',
								'key'	=> 'edit',
								'name'	=> '修改',
								'sub'	=> '',
						),
						'delete'	=> array(//删除
								'role'	=> 0,
								'url'	=> '/Admin/GoodsCategory/delete/',
								'key'	=> 'delete',
								'name'	=> '删除',
								'sub'	=> '',
						),
					),
				),
				'goods'	=> array(//商品管理信息
					'role'	=> 0,
					'url'	=> '/Admin/Goods/',
					'key'	=> 'goods',
					'name'	=> '商品列表',
					'sub'	=> array(
						'add'	=> array(//添加
								'role'	=> 0,
								'url'	=> '/Admin/Goods/add/',
								'key'	=> 'add',
								'name'	=> '添加',
								'sub'	=> '',
						),
						'edit'	=> array(//修改
								'role'	=> 0,
								'url'	=> '/Admin/Goods/edit/',
								'key'	=> 'edit',
								'name'	=> '修改',
								'sub'	=> '',
						),
						'delete'	=> array(//删除
								'role'	=> 0,
								'url'	=> '/Admin/Goods/delete/',
								'key'	=> 'delete',
								'name'	=> '删除',
								'sub'	=> '',
						),
						'recom'	=> array(//推荐
								'role'	=> 0,
								'url'	=> '/Admin/Goods/recom/',
								'key'	=> 'recomm',
								'name'	=> '推荐',
								'sub'	=> '',
						),
						'top'	=> array(//置顶
								'role'	=> 0,
								'url'	=> '/Admin/Goods/top/',
								'key'	=> 'top',
								'name'	=> '置顶',
								'sub'	=> '',
						),
					),
				),
                'goodscolor'	=> array(//商品颜色管理
                    'role'	=> 0,
                    'url'	=> '/Admin/GoodsColor/',
                    'key'	=> 'goodscolor',
                    'name'	=> '商品颜色',
                    'sub'	=> array(
                        'add'	=> array(//添加
                            'role'	=> 0,
                            'url'	=> '/Admin/GoodsColor/add/',
                            'key'	=> 'add',
                            'name'	=> '添加',
                            'sub'	=> '',
                        ),
                        'edit'	=> array(//修改
                            'role'	=> 0,
                            'url'	=> '/Admin/GoodsColor/edit/',
                            'key'	=> 'edit',
                            'name'	=> '修改',
                            'sub'	=> '',
                        ),
                        'delete'	=> array(//删除
                            'role'	=> 0,
                            'url'	=> '/Admin/GoodsColor/delete/',
                            'key'	=> 'delete',
                            'name'	=> '删除',
                            'sub'	=> '',
                        ),
                    ),
                ),
                'goodssize'	=> array(//商品尺寸管理
                    'role'	=> 0,
                    'url'	=> '/Admin/GoodsSize/',
                    'key'	=> 'goodssize',
                    'name'	=> '商品尺寸',
                    'sub'	=> array(
                        'add'	=> array(//添加
                            'role'	=> 0,
                            'url'	=> '/Admin/GoodsSize/add/',
                            'key'	=> 'add',
                            'name'	=> '添加',
                            'sub'	=> '',
                        ),
                        'edit'	=> array(//修改
                            'role'	=> 0,
                            'url'	=> '/Admin/GoodsSize/edit/',
                            'key'	=> 'edit',
                            'name'	=> '修改',
                            'sub'	=> '',
                        ),
                        'delete'	=> array(//删除
                            'role'	=> 0,
                            'url'	=> '/Admin/GoodsSize/delete/',
                            'key'	=> 'delete',
                            'name'	=> '删除',
                            'sub'	=> '',
                        ),
                    ),
                ),
				'recycle'	=> array(//商品回收站管理信息
					'role'	=> 0,
					'url'	=> '/Admin/Goods/recycle',
					'key'	=> 'recycle',
					'name'	=> '商品回收站',
					'sub'	=> array(
						'remove'	=> array(//删除
								'role'	=> 0,
								'url'	=> '/Admin/Goods/remove/',
								'key'	=> 'remove',
								'name'	=> '删除',
								'sub'	=> '',
						),
						'restore'	=> array(//回收
								'role'	=> 0,
								'url'	=> '/Admin/Goods/restore/',
								'key'	=> 'restore',
								'name'	=> '回收',
								'sub'	=> '',
						),
					),
				),
			),
		),
		//订单管理
        'order'	=> array(
			'role'	=> 0,
            'url' 	=> "#",
           	'key' 	=> 'order',
            'name' 	=> "订单管理",
            'sub' 	=> array(
				'order'	=> array(//订单信息
					'role'	=> 0,
					'url'	=> '/Admin/Order/',
					'key'	=> 'order',
					'name'	=> '订单列表',
					'sub'	=> array(
						'delivery'	=> array(//发货
							'role'	=> 0,
							'url'	=> '/Admin/Order/delivery/',
							'key'	=> 'delivery',
							'name'	=> '发货',
							'sub'	=> '',
						),
						'cancel'	=> array(//取消订单
							'role'	=> 0,
							'url'	=> '/Admin/Order/cancel/',
							'key'	=> 'cancel',
							'name'	=> '取消订单',
							'sub'	=> '',
						),
						'finish'	=> array(//完成订单
							'role'	=> 0,
							'url'	=> '/Admin/Order/finish/',
							'key'	=> 'finish',
							'name'	=> '完成订单',
							'sub'	=> '',
						),
						'delete'	=> array(//删除订单
							'role'	=> 0,
							'url'	=> '/Admin/Order/delete/',
							'key'	=> 'delete',
							'name'	=> '删除订单',
							'sub'	=> '',
						),
						
					),
				),
                'returns'   => array(
                    'role'	=> 0,
                    'url'	=> '/Admin/Order/returns',
                    'key'	=> 'returns',
                    'name'	=> '退款订单列表',
                    'sub'	=> array(
                        'pass'	=> array(//通过退款
                            'role'	=> 0,
                            'url'	=> '/Admin/Order/pass/',
                            'key'	=> 'pass',
                            'name'	=> '审核通过',
                            'sub'	=> '',
                        ),
                        'refuse'	=> array(//拒绝退款
                            'role'	=> 0,
                            'url'	=> '/Admin/Order/refuse/',
                            'key'	=> 'refuse',
                            'name'	=> '拒绝',
                            'sub'	=> '',
                        ),
                    ),
                )
			),
		),
        'storage'   => array(
            'role'	=> 0,
            'url'	=> '/Admin//Storage',
            'key'	=> 'storage',
            'name'	=> '库存管理',
            'sub'	=> array(
                'storage'	=> array(//通过退款
                    'role'	=> 0,
                    'url'	=> '/Admin/Storage/index/',
                    'key'	=> 'storage',
                    'name'	=> '库存列表',
                    'sub'	=> array(
                        'detail'	=> array(//库存详情
                            'role'	=> 0,
                            'url'	=> '/Admin/Storage/detail/',
                            'key'	=> 'detail',
                            'name'	=> '库存详情',
                            'sub'	=> '',
                        ),
                    ),
                ),
            ),
        ),
        'promotion'		=> array(
            'role'	=> 0,
            'url' 	=> "#",
            'key' 	=> 'promotion',
            'name' 	=> "促销管理",
            'sub'	=> array(
                "coupon"	=> array(
                    'role'  => 0,
                    'url'   => '/Admin/Coupon/',
                    'key'   => 'coupon',
                    'act'	=> 'index',
                    'name'  => '优惠券管理',
                    'sub'	=> array(
                        'coupon_list'	=> array(//优惠券列表
                            'role'	=> 0,
                            'url'	=> '/Admin/Coupon/',
                            'key'	=> 'coupon_list',
                            'act'	=> 'Coupon_index',
                            'name'	=> '优惠券列表',
                            'sub'	=> array(
                                "add"	=> array(
                                    'role'  => 0,
                                    'url'   => '/Admin/Coupon/add/',
                                    'key'   => 'add',
                                    'act'	=> 'Coupon_add',
                                    'name'  => '创建优惠券',
                                    "sub" 	=> "",
                                ),
                                "edit"	=> array(
                                    'role'  => 0,
                                    'url'   => '/Admin/Coupon/edit/',
                                    'key'   => 'edit',
                                    'act'	=> 'Coupon_edit',
                                    'name'  => '编辑优惠券',
                                    "sub" 	=> "",
                                ),
                                'delete' => array(
                                    'role'  => 0,
                                    'url'   => '/Admin/Coupon/delete/',
                                    'key'   => 'delete',
                                    'act'	=> 'Coupon_delete',
                                    'name'  => '删除',
                                    'sub'   => "",
                                ),
                            ),
                        ),
                    ),
                ),
            )
        ),

	),
);
?>