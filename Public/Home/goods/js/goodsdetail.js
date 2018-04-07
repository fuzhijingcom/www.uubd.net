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
        //注册修改商品数量和修改眼镜颜色的点击事件
        regChangeProductCountEvent: function () {
            var commonVariable = zwg.Gooddetail.commonVariable;
            var $popLayerBg = commonVariable.$popLayerBg;
            //var $decreaseProductCountBtn = commonVariable.$decreaseProductCountBtn;
            var $colorListUl = commonVariable.$colorListUl;
            //var $increaseProductCountBtn = commonVariable.$increaseProductCountBtn;
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
                if($(this).hasClass('disableds')){
                    return false;
                }
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
                zwg.Gooddetail.mainCarouselSwiper({"operation": "reset", "skuid": goodsSkuid});
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

        }
    }

    zwg.Gooddetail.regChangeProductCountEvent();
}

