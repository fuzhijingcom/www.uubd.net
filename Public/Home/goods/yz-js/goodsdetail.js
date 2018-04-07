var zwg = {};
var params = { width: "100%", height: "68%", bottom: "0", position: "absolute", left: "0", "right": "0", "overflow": "auto", border: "none", background: "#FFF" };
var start = 1; //设置当前页数
var limit = 6; //设置页数
var total = 0; //总页数
var loadActivityFlag = false;

/*原有的代码*/
if ($("body").hasClass("zwg_gooddetail")) {
    zwg.Gooddetail = {
        //一些可能会反复被用到的JQ变量
        commonVariable: {
            //进入分享商品链接页按钮
            $shareProductLinkBtn: $(".good-details-bottom-nav .share-product-link"),
            //将商品加入到购物车按钮
            $addToShopcarBtn: $(".good-details-bottom-nav .plain_version"),
            //弹出层背景
            $popLayerBg: $("#pop-Layer-bg"),
            //进入评论页按钮
            $lookAppraiseEntrance: $(".look-appraise-entrance"),
            //弹出层_评论页父框（针对该模块，通过该元素找到该模块相关的元素）
            $appraisalPopLayer: $(".appraisal-pop-layer"),
            //和修改sku相关：添加数量、数量展示、减少数量
            $decreaseProductCountBtn: $(".modify-product-count .decrease"),
            $productCountSpan: $(".modify-product-count .product-count"),
            $increaseProductCountBtn: $(".modify-product-count .increase"),
            //颜色列表
            $colorListUl: $(".color-list"),
            //眼镜颜色所对应的图片展示
            $brandSunglassesImg: $(".brand-sunglasses-img img"),
            //商品单价
            $singleGlassesPrice: $(".details-customize-common-bottom li:eq(0) p:eq(1)"),
            //取消（关闭）sku修改
            $cancelSKU: $(".details-customize-common-bottom li:eq(1)"),
            //非弹出层颜色外层
            $selectGlassFrameColorWrap: $("#select-glass-frame-color-wrap")
        },
        //根据图片路径切换顶部轮播图片的颜色
        mainCarouselSwiper: function (operationObj) {
            if (operationObj.operation == "init") {
                zwg.Gooddetail.mainSwiper = new Swiper('.swiper-container.main-carousel', {
                    pagination: '.swiper-pagination',
                    paginationClickable: true,
                    spaceBetween: 0,
                    centeredSlides: true,
                    autoplay: 2500,
                    autoplayDisableOnInteraction: false,
                    loop: true
                });
            }
            zwg.Gooddetail.mainSwiper.removeAllSlides(); //移除全部
            for (var i = 0; i < strGoodsSkuImg.length; i++) {
                var dr = strGoodsSkuImg[i];
                if (dr.skuid == operationObj.skuid) {
                    zwg.Gooddetail.mainSwiper.appendSlide('<div class="swiper-slide"><a><img src="' + dr.bImgUrl + '"></a></div>');
                }
            }

        }
        ,
        //注册底部的收藏函数
        regGoodDetailsCollection: function () {
            $(".good-details-bottom-nav ul li.collect-product").click(function () {
                if ($(this).hasClass("collected"))
                    Favorites.DeleteUserFavoriteByGoodsSkuId(goodsSkuid, zwg.Gooddetail.deleteFavoriteStyle);
                else
                    Favorites.AddFavoritesByGoodsSkuId(goodsSkuid, zwg.Gooddetail.addFavoriteStyle);
            });
        },
        //添加收藏样式
        addFavoriteStyle: function () {
            $(".good-details-bottom-nav ul li.collect-product").addClass("collected");
            Goods_Vue.$data.dynamicGoodsSku.GoodsFavoriteNum++;
        },
        //删除收藏样式
        deleteFavoriteStyle: function () {
            $(".good-details-bottom-nav ul li.collect-product").removeClass("collected");
            Goods_Vue.$data.dynamicGoodsSku.GoodsFavoriteNum--;
        },
        //初始化收藏过得商品
        initCollectedProductEvent: function (collectedIdArr) {
            if (collectedIdArr && collectedIdArr.length > 0 && $.inArray(goodsSkuid, collectedIdArr) > -1) {
                $(".good-details-bottom-nav ul li.collect-product").addClass("collected");
            }
            else {
                $(".good-details-bottom-nav ul li.collect-product").removeClass("collected");
            }
        },
        //注册修改商品数量和修改眼镜颜色的点击事件
        regChangeProductCountEvent: function () {
            var commonVariable = zwg.Gooddetail.commonVariable;
            var $popLayerBg = commonVariable.$popLayerBg;
            //var $decreaseProductCountBtn = commonVariable.$decreaseProductCountBtn;
            var $productCountSpan = commonVariable.$productCountSpan;
            var $colorListUl = commonVariable.$colorListUl;
            //var $increaseProductCountBtn = commonVariable.$increaseProductCountBtn;
            var $brandSunglassesImg = commonVariable.$brandSunglassesImg;
            var $singleGlassesPrice = commonVariable.$singleGlassesPrice;
            var $cancelSKU = commonVariable.$cancelSKU;
            //此处的li
            var $colorListUlLis = $colorListUl.find("li:not(.no-stock)");
            var $selectGlassFrameColorWrap = commonVariable.$selectGlassFrameColorWrap;
            var $selectOptionTop = $selectGlassFrameColorWrap.find(".select-option-top");
            var $selectOptionBody = $selectGlassFrameColorWrap.find(".select-option-body");
            var $selectOptionBodyTitle = $selectGlassFrameColorWrap.find(".select-option-body-title");
            var $selectedColorSpan = $selectOptionTop.find(".selected-color");
            $(".select-color-count-pop-frame .brand-sunglasses-img img").attr("src", $(".select-color-count-pop-frame ul.color-list li.onclick").attr("data-img-src"));
            //原价、当前价
            var prevPrice = 0, nowPrice, _sku;
            $colorListUlLis.click(function () {
                var $myself = $(this);
                //判断当前点击的颜色选项是否包含在$selectGlassFrameColorWrap框中
                var isInSelectOptionWrap = $.contains($selectOptionBody.get(0), $myself.get(0));
                var selectedIndex = $myself.index();
                if (isInSelectOptionWrap) {
                    //操作本身
                    notPopLayerSelectColor($myself);
                } else {
                    //操作本身
                    notPopLayerSelectColor($("#SkuColor li").eq(selectedIndex));
                }
                //一秒钟之后合拢
                $selectOptionBodyTitle.click();
                Init();
            });
            //页面层颜色改变调用
            function notPopLayerSelectColor($clickElm) {
                $clickElm.addClass("onclick").siblings().removeClass("onclick");
                goodsSkuid = $clickElm.attr("data-id");
                zwg.Gooddetail.mainCarouselSwiper({ "operation": "reset", "skuid": goodsSkuid });
                JsGoods.ShowGoodsSkuStyleByDynamic(goodsSkuid);
            }
            $cancelSKU.click(function () {
                $popLayerBg.hide();
                $popLayerBg.children().hide();
                setBodyToOverflowOri();
            });
            //展开操作页
            $selectOptionTop.click(function () {
                $selectGlassFrameColorWrap.addClass("after-click");
            });
            //合拢操作页
            $selectOptionBodyTitle.click(function () {
                var $finallySelectedLi = $colorListUl.find("li.onclick");
                if ($finallySelectedLi.size() > 0) {
                    $selectedColorSpan.html($.trim($finallySelectedLi.html()));
                }

                $selectGlassFrameColorWrap.removeClass("after-click");
            });

        },
        //注册点击加入购物车事件
        regShopcarBtnClickEvent: function () {
            var Gooddetail = zwg.Gooddetail;
            var commonVariable = Gooddetail.commonVariable;
            var $addToShopcarBtn = commonVariable.$addToShopcarBtn;

            $addToShopcarBtn.click(function () {
                if ($(this).hasClass("no-buyer")) {
                    ShowTempMessage("商品已下架", 3 * 1000);
                    return false;
                }

                //加入购物车
                Goods_Vue.addShopCar();
                //var allPopLayer = zwg.Gooddetail.allPopLayer;
                //allPopLayer.glassesSKUSelectPopLayer();
            });

        },
        //有背景的弹出层汇总
        allPopLayer: {
            //服务说明弹出层
            serviceIllustratePopLayer: function () {
                var commonVariable = zwg.Gooddetail.commonVariable;
                var $popLayerBg = commonVariable.$popLayerBg;
                $(".wangcl-commitment").click(function () {
                    $popLayerBg.show();
                    $popLayerBg.children().eq(0).fadeIn();
                    setBodyToOverflowHidden();
                });

                $(".service-illustrate-frame-bottom p").click(function () {
                    $popLayerBg.fadeOut();
                    $popLayerBg.children().fadeOut();
                    setBodyToOverflowOri();
                });
            },
            //分享弹出层
            shareProductLinkPopLayer: function () {
                var commonVariable = zwg.Gooddetail.commonVariable;
                var $popLayerBg = commonVariable.$popLayerBg;
                var $shareProductLinkBtn = commonVariable.$shareProductLinkBtn;
                $shareProductLinkBtn.click(function () {
                    $popLayerBg.show();
                    $popLayerBg.children().eq(1).show();
                    setBodyToOverflowHidden();
                });
                $(".share-product-link-frame-bottom p").click(function () {
                    $popLayerBg.hide();
                    $popLayerBg.children().hide();
                    setBodyToOverflowOri();
                });
            },
            //选择颜色和数量弹出层_::::check
            glassesSKUSelectPopLayer: function () {
                var commonVariable = zwg.Gooddetail.commonVariable;
                var $popLayerBg = commonVariable.$popLayerBg;
                $popLayerBg.show();
                $popLayerBg.children().eq(2).show();
                setBodyToOverflowHidden();
            },
            //点击阴影部分时关闭弹框
            closePopLayerWhileClickShade: function () {
                var commonVariable = zwg.Gooddetail.commonVariable;
                var $popLayerBg = commonVariable.$popLayerBg;
                $popLayerBg.click(function (e) {
                    var $myself = $(this);
                    var target = e.target;
                    if ($(target).hasClass("pop-Layer-bg") || $(target).hasClass("pop-layer-content-wrap") || $(target).hasClass("pop-layer-content")) {
                        $popLayerBg.hide();
                        $popLayerBg.children().hide();
                        setBodyToOverflowOri();
                        return false;
                    } else if ($(target).hasClass("pop-layer-content-wrap-top-percent15")) {
                        $popLayerBg.hide();
                        $popLayerBg.children().hide();
                        setBodyToOverflowOri();
                    } else if ($(target).children(".service-illustrate-pop-layer").size() > 0) {
                        $popLayerBg.hide();
                        $popLayerBg.children().hide();
                        setBodyToOverflowOri();
                    }
                });
            },
            //查看评价
            lookAppraisalPopLayer: function () {
                var commonVariable = zwg.Gooddetail.commonVariable;
                var $popLayerBg = commonVariable.$popLayerBg;
                var $lookAppraiseEntrance = commonVariable.$lookAppraiseEntrance;
                var $appraisalPopLayer = commonVariable.$appraisalPopLayer;
                var $closeAppraisalPopLayer = $appraisalPopLayer.find(".back_prev_btn");
                $lookAppraiseEntrance.click(function () {
                    $popLayerBg.show();
                    $popLayerBg.children().eq(3).show();
                    setBodyToOverflowHidden();
                    if (!$("#commentLoad").hasClass("no-more-appraisal")) {
                        Ajax_Goods.GoodsComment(JsGoods.CommentStyle);
                    }
                });
                //从评价页面返回到商品详情页
                $closeAppraisalPopLayer.click(function () {
                    $popLayerBg.hide();
                    $popLayerBg.children().hide();
                    setBodyToOverflowOri();
                });
            }
        }
    };
}




function Init() {
    //显示活动信息
    Ajax_Goods.GoodsActivity(JsGoods.DynamicSeetPackageCss);
}


//过滤显示类名
Vue.filter('ClassNameFormatter', function (skuid) {
    var _class_name = '';
    if (skuid == goodsSkuid) {
        _class_name = "onclick";
    }
    else
        _class_name = "";
    return _class_name;
});
//过滤显示评分
Vue.filter('SorceFormatter', function (GoodsScore) {
    var Score = parseInt(GoodsScore);
    var _html = '';
    for (var i = 1; i <= 5; i++) {
        if (Score >= i)
            _html += '<img src="../images/zwg_gooddetails_collection_done_btn.svg"/>';
        else
            _html += '<img src="../images/zwg_gooddetails_collection_no_word_btn.svg"/>';
    }
    return _html;
});
//日期格式化
Vue.filter('DateFormatter', function (date) {
    if (!Verify_Str(date)) {
        return date;
    }
    if (date.indexOf('T') > 0)
        date = date.split('T')[0];
    else if (date.indexOf(' ') > 0)
        date = date.split(' ')[0];
    return date;
});
//查看某一套餐的图片数量,并为其分配对应的类
Vue.filter('ImgCountClassFormatter', function (length) {
    length = length + 1;
    if (length == 1) {
        return "has_one";
    } else if (length == 2) {
        return "has_two";
    } else if (length == 3) {
        return "has_three";
    }
});
//判断是否是：无套餐，且只参加一种活动的情况
Vue.filter('IsNoMealButOneAct', function (isTrue) {
    if (isTrue) {
        return "no_meal_but_one_act";
    } else {
        return "";
    }
});

var Goods_Vue = new Vue({
    el: '#vueAction',
    data: {
        goods: strGoods,
        sku: strGoodsSku,
        skuImg: strGoodsSkuImg,
        couponsList: [],
        activity: {},
        dynamicGoodsSku: {},
        product_count: 1,
        prodcutStock: 0,
        goodsCommentList: [],
    },
    methods: {
        //显示验光单
        couponsClickDialog: function () {
            openIframe2("/pickCoupon.aspx", false, params, "领取优惠券");
        },
        //加价购链接
        priceAddLink: function () {
            location.href = 'select_plus_product.aspx?skuId=' + goodsSkuid;
        },
        //配镜页面链接
        glassPageLink: function () {
            location.href = "/goods/glass.aspx?id=" + goodsid + "&skuid=" + goodsSkuid;
        },
        //套餐链接
        packageinfo: function (packageItem) {
            location.href = "selsec_meals.aspx?skuId=" + goodsSkuid + "&activeId=" + packageItem.activeId;
        },
        //数量减
        decrease_count: function () {
            this.product_count > 1 ? this.product_count-- : this.product_count;
        },
        //数量加
        increase_count: function () {
            if (this.product_count < this.prodcutStock) {
                this.product_count++;
            }
            else {
                this.product_count;
                ShowTempMessage("该商品只能购买" + this.product_count + "件", 3 * 1000);
            }
        },
        //加入购物车
        addShopCar: function () {
            if (!Verify_Num(goodsSkuid)) {
                ShowTempMessage("请选择镜架颜色", 3 * 1000);
                return false;
            }
            //如果不是数字 则返回
            if (!Verify_Num(this.product_count)) {
                ShowTempMessage("请选择镜架数量", 3 * 1000);
                return false;
            }

            var postData = {
                BaseSkuId: goodsSkuid,
                Num: this.product_count,
                OptometryId: 0,
                SkuType: 0,
            }
            Ajax_Goods.AddShopCar(postData);
        },
        getPriceOneInfo: function (activeId) {
            var list = this.activity.packageAloneInfo;
            console.log(list);
            for (var i = 0; i < list.length; i++) {
                if (list[i].activeId == activeId)
                    return list[i];
            }
            return null;
        },
        //单件套餐加入购物车
        priceOneAddtoShopCar: function (activeId) {
            if (activeId < 1) {
                ShowTempMessage("参数错误", 3000);
                return false;
            }
            var packageAloneInfo = this.getPriceOneInfo(activeId);
            if (packageAloneInfo == null) {
                ShowTempMessage("参数错误", 3000);
                return false;
            }

            var list = packageAloneInfo.packageOneList;
            for (var i = 0; i < list.length; i++) {
                if (list[i].type == 1)
                    break;
            }
            if (i == list.length)       //没有镜片
            {
                var postData = {
                    BaseSkuId: goodsSkuid,
                    Num: 1,
                    OptometryId: 0,
                    SkuType: 0,
                    ActiveId: packageAloneInfo.activeId,
                    SkuInfo: packageAloneInfo.packagePrice,
                };
                Common.SubmitBeforeStyle();
                $.post("../../data/goods/ShopCar.ashx", { action: "package", data: JSON.stringify(postData) },
                function (data) {
                    if (data.Result == 1) {
                        Common.SubmitScuessStyle();
                        ShowTempMessage("加入成功", 3 * 1000);
                        location.href = "../../shoppingcar.aspx";
                    }
                    else {
                        Common.SubmitFailStyle();
                        ShowTempMessage(data.Message, 3);
                    }
                }, 'json')
            }
            else {                      //有镜片
                var params = { width: "100%", height: "85%", bottom: "0", position: "absolute", left: "0", "right": "0", "overflow": "auto", border: "none", background: "#FFF" };
                var mainSku = goodsSkuid;
                var activeId = packageAloneInfo.activeId;
                var gAppId = packageAloneInfo.packageOneList[0].skuId;

                var url = "/goods/meals_customize_type.aspx?mainSku=" + mainSku + "&gAppId=" + gAppId + "&activeId=" + activeId;
                location.href = url;
                //openIframe2(url, true, params, "");
            }
        },
        //显示评价
        showComment: function () {
            if (!$("#commentLoad").hasClass("no-more-appraisal")) {
                Ajax_Goods.GoodsComment(JsGoods.CommentStyle);
            }
        },
    },
});


var Ajax_Goods = {
    //商品活动信息
    GoodsActivity: function (callBack) {
        $.post("/data/goods/activity.ashx", { action: "GetAllActivityBySku", data: goodsSkuid, rand: Math.random() }, function (data) {
            if (data.Result == 1) {
                Goods_Vue.$data.activity = data.Data;
                if (Goods_Vue.$data.activity.activityCount) {
                    delete Goods_Vue.$data.activity.activityCount;
                }
                var activityCount = 0;
                for (var actItem in data.Data) {
                    if (data.Data.hasOwnProperty(actItem) === true) {
                        if (data.Data[actItem] && actItem != "skuId") {
                            activityCount++;
                        }
                    }
                }
                Goods_Vue.$data.activity.activityCount = activityCount;
                Goods_Vue.$nextTick(function () {
                    callBack();
                    $("#mainPackageImg img,#mainOnePackage img").attr("src", JsGoods.ShowDialogImg(goodsSkuid));
                });
            }
        }, 'json')
    },
    //动态获取商品和SKU信息
    GetDynamicGoodsSku: function () {
        loadActivityFlag = false;
        $.post("/data/goodsdetail.ashx", { action: "GetDynamicGoodsSku", goodsid: goodsid, rand: Math.random() }, function (data) {
            if (data.Result == 1) {
                Goods_Vue.$data.dynamicGoodsSku = data.Data;
                loadActivityFlag = true;
            }
        }, 'json')
    },
    //优惠券列表
    CouponsList: function () {
        $.post("/data/mem/Coupons.ashx", { action: "AllCouponsShortInfo", rand: Math.random() }, function (data) {
            if (data.Result == 1) {
                Goods_Vue.$data.couponsList = data.Data;
            }
        }, 'json')
    },
    //浏览记录
    BrowsingRecord: function () {
        var url = location.href;
        $.post("/data/ProductHistoryBrowsing.ashx", { action: "saveGoodsidSkuid", url: url, rand: Math.random() }, function (data) {
        }, 'json');
    },
    //加入购物车
    AddShopCar: function (postData) {
        Common.SubmitBeforeStyle();
        $.post("/data/goods/ShopCar.ashx", { action: "ordinary", data: JSON.stringify(postData), rand: Math.random() }, function (data) {
            if (data.Result == 1) {
                Common.SubmitScuessStyle();
                ShowTempMessage("加入购物车成功", 3000);
                location.href = "/shoppingcar.aspx";
            }
            else {
                Common.SubmitFailStyle();
                if (data.Message == "未登陆") {
                    location.href = "/login.aspx?strUrl=" + encodeURI(location.href);
                }
                else {
                    ShowTempMessage(data.Message, 3000);
                }
            }
        }, 'json');
    },
    //商品评价
    GoodsComment: function (callback) {
        if (!Verify_Num(goodsid)) {
            ShowTempMessage("参数不正确", 3 * 1000)
            return false;
        }
        $.post("/data/goodsdetail.ashx", { action: "GetGoodsComment", goodsId: goodsid, start: start, limit: limit, rand: Math.random() }, function (data) {
            if (data.Result == 1) {
                start++;
                total = data.Data.totalProperty;
                for (var i = 0; i < data.Data.root.length; i++) {
                    var dr = data.Data.root[i];
                    Goods_Vue.$data.goodsCommentList.push(dr);
                }
                callback();
            }
        }, 'json');
    },
}

var JsGoods = {
    //动态设置套餐样式
    DynamicSeetPackageCss: function () {
        //
        if ($(".all_meals").length > 0) {
            zwg.Gooddetail.mealsSwiper = new Swiper('.all_meals .swiper-container', {
                slidesPerView: 'auto',
                centeredSlides: false,
                paginationClickable: true,
                spaceBetween: 25
            });
        }
    },
    //获取销售价格
    GetSalePrice: function (goodsSkuid) {
        var salePrice = 0;
        if (Goods_Vue.$data.dynamicGoodsSku == null || Goods_Vue.$data.dynamicGoodsSku.GoodsSku == null || goodsSkuid < 1)
            return salePrice;

        for (var i = 0; i < Goods_Vue.$data.dynamicGoodsSku.GoodsSku.length; i++) {
            var dr = Goods_Vue.$data.dynamicGoodsSku.GoodsSku[i];
            if (dr.SkuId == goodsSkuid) {
                salePrice = dr.SkuPrice;
                break;
            }
        }

        if (Goods_Vue.$data.activity != null && Goods_Vue.$data.activity.downPrice != null) {
            salePrice = Goods_Vue.$data.activity.downPrice.skuActivityPrice;
        }
        if (Goods_Vue.$data.activity != null && Goods_Vue.$data.activity.vip != null) {
            salePrice = Goods_Vue.$data.activity.vip.actprice;
        }
        return salePrice;
    },
    //获取SKU库存、活动商品库存、购买数量
    GetSaleStock: function (goodsSkuid) {
        var saleStock = 1;
        if (Goods_Vue.$data.dynamicGoodsSku == null || Goods_Vue.$data.dynamicGoodsSku.GoodsSku == null || goodsSkuid < 1)
            return salePrice;

        for (var i = 0; i < Goods_Vue.$data.dynamicGoodsSku.GoodsSku.length; i++) {
            var dr = Goods_Vue.$data.dynamicGoodsSku.GoodsSku[i];
            if (dr.SkuId == goodsSkuid) {
                saleStock = dr.SkuStock;
                break;
            }
        }

        var activitySaleStock = 1;
        if (Goods_Vue.$data.activity != null && Goods_Vue.$data.activity.downPrice != null) {
            var activityStock = Goods_Vue.$data.activity.downPrice.goodsSkuStock;
            var activitySaleNum = Goods_Vue.$data.activity.downPrice.saleNum;
            var activityMinPerson = Goods_Vue.$data.activity.downPrice.minPerson;

            activitySaleStock = (activityStock - activitySaleNum) > activityMinPerson ? activityMinPerson : (activityStock - activitySaleNum);
        }

        saleStock = (saleStock > activityMinPerson) ? activityMinPerson : saleStock;

        Goods_Vue.$data.prodcutStock = saleStock;
        return saleStock;
    },
    //获取市场价格、库存、状态
    GetMarketPriceState: function (goodsSkuid) {
        var obj = {};
        if (Goods_Vue.$data.dynamicGoodsSku == null || Goods_Vue.$data.dynamicGoodsSku.GoodsSku == null || goodsSkuid < 1)
            return obj;

        obj.GoodsState = Goods_Vue.$data.dynamicGoodsSku.GoodsState;
        for (var i = 0; i < Goods_Vue.$data.dynamicGoodsSku.GoodsSku.length; i++) {
            var dr = Goods_Vue.$data.dynamicGoodsSku.GoodsSku[i];
            if (dr.SkuId == goodsSkuid) {
                obj.marketPrice = dr.SkuMarkerPrice;
                obj.skuStock = dr.SkuStock;
                obj.skuState = dr.SkuState;
                obj.skuName = dr.SkuName;
                break;
            }
        }
        return obj
    },
    //获取折扣信息
    GetDiscount: function (marketPrice, salePrice) {
        return ((salePrice / marketPrice) * 10).toFixed(1);
    },
    //显示弹出框图片 优先获取正面 半侧面 侧面图片
    ShowDialogImg: function (goodsSkuid) {
        var dataImgSrc = "";
        for (var i = 0; i < strGoodsSkuImg.length; i++) {
            var dr = strGoodsSkuImg[i];
            if (dr.skuid == goodsSkuid) {
                if (dr.imgAngle == 0)
                    dataImgSrc = dr.bImgUrl;
            }
        }
        if (dataImgSrc != "")
            return dataImgSrc;
        for (var i = 0; i < strGoodsSkuImg.length; i++) {
            var dr = strGoodsSkuImg[i];
            if (dr.skuid == goodsSkuid) {
                if (dr.imgAngle == 1)
                    dataImgSrc = dr.bImgUrl;
            }
        }
        if (dataImgSrc != "")
            return dataImgSrc;
        for (var i = 0; i < strGoodsSkuImg.length; i++) {
            var dr = strGoodsSkuImg[i];
            if (dr.skuid == goodsSkuid) {
                if (dr.imgAngle == 2)
                    dataImgSrc = dr.bImgUrl;
            }
        }
        return dataImgSrc;
    },
    //根据是否可配镜显示按钮
    ShowBuyButtonByHasGlass: function (goodsSkuid) {
        var flag = false;
        if (strGoodsSku == null)
            return;

        for (var i = 0; i < strGoodsSku.length; i++) {
            var dr = strGoodsSku[i];
            if (dr.skuId == goodsSkuid) {
                if (dr.skuHasGlass == 0) {
                    return flag;
                }
            }
        }
        flag = true;
        return flag;
    },
    //根据SKU动态信息显示样式
    ShowGoodsSkuStyleByDynamic: function (goodsSkuid) {
        var goodsSkuObj = JsGoods.GetMarketPriceState(goodsSkuid);
        var hasGlass = JsGoods.ShowBuyButtonByHasGlass(goodsSkuid);

        var saleStock = JsGoods.GetSaleStock(goodsSkuid);
        //显示虚拟试戴
        JsGoods.ShowVirtual(goodsSkuid)

        if (goodsSkuObj != null) {
            //修改url地址
            if (location.href.indexOf("/goods/goodsdetail.aspx?") > 0)
                window.history.replaceState({}, "", "/goods/goodsdetail.aspx?id=" + goodsid + "&skuid=" + goodsSkuid);
            else
                window.history.replaceState({}, "", "/goods/goodsdetail_" + goodsid + "_" + goodsSkuid + ".html");

            //销售价格通过ajax取的 这里通过属性定时获取
            var salePrice = 0;
            var referAjaxGetSalePrice = setInterval(function () {
                if (loadActivityFlag) {
                    salePrice = JsGoods.GetSalePrice(goodsSkuid);
                    var discount = JsGoods.GetDiscount(goodsSkuObj.marketPrice, salePrice);

                    $(".pri_box .current-pri").html("¥ " + salePrice);
                    $(".pri_box .discount").html("" + discount + "折");
                    $(".pri_box .primary-pri").html("官方指导价：¥ " + goodsSkuObj.marketPrice);
                    $(".selected-color").html(goodsSkuObj.skuName);

                    $(".brand-sunglasses-img img").attr("src", JsGoods.ShowDialogImg(goodsSkuid));
                    $(".details-customize-common-bottom li:first p:eq(1)").html(salePrice);

                    if (goodsSkuObj.GoodsState == 0 || saleStock < 1 || goodsSkuObj.skuState == 0) {
                        $("#footButtom").addClass("only_plain_glass");
                        $(".has_degree_version").addClass("no-buyer").removeClass("hidden");
                        $(".has_degree_version p span").html("已下架");
                        $(".plain_version").addClass("no-buyer").removeClass("hidden");
                        $(".plain_version p span").html("已下架");
                    }
                    else {
                        if (hasGlass) {
                            //可以配镜
                            $("#footButtom").removeClass("only_plain_glass");
                            $(".plain_version").removeClass("no-buyer").removeClass("hidden");
                            $(".plain_version p span").html("购买平光镜");
                            $(".has_degree_version").removeClass("no-buyer").removeClass("hidden");
                            $(".has_degree_version p span").html("配近视镜片");
                        }
                        else {
                            //不可配镜
                            $("#footButtom").addClass("only_plain_glass");
                            $(".plain_version").removeClass("no-buyer").removeClass("hidden");
                            $(".plain_version p span").html("购买平光镜");
                            $(".has_degree_version").removeClass("no-buyer").removeClass("hidden");
                            $(".has_degree_version p span").html("配近视镜片");
                        }
                    }
                    window.clearInterval(referAjaxGetSalePrice);
                }
            }, 500);
        }
    },
    //商品评价样式
    CommentStyle: function () {
        $("#Comment .count span").html(total);
        if (total > (start * limit)) {
            $("#commentLoad").html("<span>MORE&nbsp;加载更多</span>");
            $("#commentLoad").removeClass("no-more-appraisal");
        }
        else {
            $("#commentLoad").html("<span>- 暂无更多评价 -</span>");
            $("#commentLoad").addClass("no-more-appraisal");
        }
    },
    //显示虚拟试戴
    ShowVirtual: function (goodsSkuid) {
        if (strGoodsSku == null)
            return;
        $("a.go-to-vitual-test").hide();
        for (var i = 0; i < strGoodsSku.length; i++) {
            var dr = strGoodsSku[i];
            if (dr.skuId == goodsSkuid) {
                if (dr.skuVirtual == 1) {
                    $("a.go-to-vitual-test").show();
                    JsGoods.VirtualStyle(goodsSkuid, dr.androidPack);
                }
            }
        }
    },
    //虚拟试戴样式
    VirtualStyle: function (goodsSkuid, androidPackage) {
        if (Wechat.wxBrowers()) {
            //微信浏览器
            var $weixinPage_tips_wrap = $('<div id="weixinPage_tips_wrap" style="display:none;background: rgba(0,0,0,0.5);position: fixed;z-index:999;top:0;left:0;bottom:0;right:0;"><img style="position: absolute;top:3%;width:12%;right:5%;z-index:1;" class="right_top_ff5f00_arrow" src="/images/test/top_right_icon.png"/>' +
           '<div id="weixinPage_tips" style="width:84%;left:border:1px solid #A4A4A4;background: #eee;margin-left:auto;margin-right:auto;margin-top:25%;padding-top:5%;padding-bottom:5%;border-radius: 5px;overflow:hidden;z-index:2;position: relative;" class="clearfix">' +
           '<div class="VR_tips_left" style="float:left;width:25%;"><img style="width:80%;margin-left:10%;display: block;" src="/images/test/see-me.png"></div><div class="VR_tips_right" style="float:right;width:75%;">' +
           '<p style="font-size:1.4rem;width:95%;margin-left:0;margin-top:2%;line-height:1.4;">请点击右上角按钮选择<span style="color: #ff5f00;font-size:1.4rem;">「在浏览器中打开」</span>，体验试戴需打开See Me应用</p>' +
           '</div></div></div>');
            $("body").append($weixinPage_tips_wrap);
            $("#weixinPage_tips_wrap").click(function (e) {
                if ($(e.target).attr("id") == "weixinPage_tips_wrap") {
                    $("#weixinPage_tips_wrap").hide();
                }
            });
            $("a.go-to-vitual-test").bind("click", function () {
                $("#weixinPage_tips_wrap").show();
                return false;
            });
        }
        else {
            //当应用没有安装时，提供下载地址
            var iosUploadUrl = "http://itunes.apple.com/us/app/id1072257966";
            var androidUploadUrl = "http://app.wangcl.com/apk/望客 See Me.apk";
            //打开应用事例（这两个值是变化的，点击切换颜色时，这两个值也在变化）
            var iosHref = "arglass://?guid=" + goodsid + "&skuid=" + goodsSkuid + "&name=glass&img=" + JsGoods.ShowDialogImg(goodsSkuid);
            var androidHref = "myapp://jp.app/openwith?id=" + goodsid + "&skuid=" + goodsSkuid + "&glass_packge=http://app.wangcl.com/glasses_packages/Android/" + androidPackage;

            function openInApple() {
                window.location = iosHref;
                window.setTimeout(function () {
                    window.location = iosUploadUrl;
                }, 3000);
            }
            //安卓
            function openInAndroid(androidHref) {
                setTimeout(function () {
                    window.location = androidHref;
                    $("a.go-to-vitual-test").attr("href", "javascript:void(0);");
                }, 100);
            }

            function bindGoToVitualWear() {
                var isIOS = navigator.userAgent.match(/(iPhone|iPod|iPad);?/i),
                        isAndroid = navigator.userAgent.match(/Android/),
                        isDesktop = !isIOS && !isAndroid;
                if (isAndroid) {
                    $("a.go-to-vitual-test").attr("href", androidUploadUrl);
                }
                if (isIOS) {
                    $("a.go-to-vitual-test").attr("href", "javascript:void(0);");
                }

                $(".go-to-vitual-test").bind("click", function () {
                    if (isIOS) {
                        openInApple();
                    } else if (isAndroid) {
                        openInAndroid(androidHref);
                    }
                });
            }
            bindGoToVitualWear();
        }
    },
};