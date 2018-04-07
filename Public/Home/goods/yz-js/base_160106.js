//js样式设置
//全部页面都会调用到的函数
//为了便于修改，先全部分开写，之后再放入到一个函数中进行调用
var AllPageFunc = {
    //足迹轮播及上下切换时足迹隐藏和显示足迹
    regFootprintSlick: function () {
        if ($('.slickBox').length > 0) {
            $('.slickBox').slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: false,
                speed: 500
            });

            $(".footprint-toggle span").toggle(
                
                function () {
                    var $myself = $(this);
                    $myself.siblings(".remove-repeat").show();
                    $(".footprint").slideDown("fast");
                    $myself.removeClass("toggle");
                },
                function () {
                    var $myself = $(this);
                    $myself.addClass("toggle");
                    $(".footprint").slideUp("fast");
                    $myself.siblings(".remove-repeat").hide();
                }
            );
            $(".footprint").slideUp("1");
            
        }

    },//底部导航
    bottomNavigatorStyleChange: function () {
        //底部导航
//        if ($(".common_bottom_nav").length > 0) {
//            var dataIndex = $(".common_bottom_nav").attr("data-index");
//            $(".common_bottom_nav li").eq(dataIndex).addClass("onclick").siblings().removeClass("onclick");
//            $(".common_bottom_nav ul li a").click(function () {
//                var $myselfParent = $(this).parent();
//                $myselfParent.addClass("onclick").siblings("li").removeClass("onclick");
//            });
//        }
    },
    returnToTop: function () {
        //全网返回顶部
        $(window).scroll(function () {
            if ($(this).scrollTop() > 900) {
                $("#back_top").fadeIn();
            } else {
                $("#back_top").fadeOut();
            }
        });

        //获取页面顶部的图片路径，去掉最后的文件名，以此作为back-top的前缀
        //var firstImgPath = $("img:eq(0)").attr("src");
        //var firstImgPathSplitGroup = firstImgPath.split("/");
        //var imgPathPrefix = firstImgPath.replace(firstImgPathSplitGroup[firstImgPathSplitGroup.length - 1], "");
        //var backTopImgPath = imgPathPrefix + "back-top.svg";
        //$("body").append('<a href="#" id="back_top" class="back_top"><img src=\"' + backTopImgPath + '\"></a>');
        $("body").append('<a href="#" id="back_top" class="back_top"><img src=\"/images/back-top.svg\"></a>');
        $("#back_top").click(function () {
            $("body,html").animate({ scrollTop: 0 }, 800);
        });
    }
    ,
    regCommonTopV2Even: function () {
        if ($(".common_top_v2_moreBtn").length > 0) {
            $(".common_top_v2_moreBtn").click(function () {
                var $common_top_v2_more_details = $(".common_top_v2_more_details");
                if ($common_top_v2_more_details.hasClass("hide")) {
                    $common_top_v2_more_details.removeClass("hide");
                } else {
                    $common_top_v2_more_details.addClass("hide");
                }
            });

            $(document).click(function (e) {
                var targetElm = e.target;
                if (!$(targetElm).hasClass("common_top_v2_more_details") && !$(targetElm).hasClass("common_top_v2_moreBtn")) {
                    $(".common_top_v2_more_details").addClass("hide");
                }
            });
            if ($("body").hasClass("z_index") && $(".slideBox.IndexBannerSlide").length >= 1) {
                if (typeof window.localStorage !== undefined) {
                    var everIntoIndex = false, name = "", value = "";
                    for (var i = 0; i < localStorage.length; i++) {
                        name = localStorage.key(i);
                        if (name == "firstIntoIndex") {
                            everIntoIndex = true;
                            value = localStorage.getItem(name);
                            break;
                        }
                    }
                    if (!everIntoIndex) {
                        localStorage.setItem("firstIntoIndex", "1");
                        $(".common_top_v2_moreBtn").click();
                    }
                }
                
            }
        }

    }
    ,
    //前端统一调用函数(含以上的所有函数)
    InvokefrontEndAllFuc: function () {
        AllPageFunc.regFootprintSlick();
        //AllPageFunc.bottomNavigatorStyleChange();
        AllPageFunc.returnToTop();
        AllPageFunc.regCommonTopV2Even();
    }
};

var frontPageCommonFuc = {
    allToolkitFunction: {
        //单屏滚动事件注册 param (string array)TouchSlideObjIDArr, 数组中的每个对象含有两个变量：轮播元素，是否自动播放
        regTouchSlide: function (TouchSlideObjIDArr) {
            for (var i = 0, len = TouchSlideObjIDArr.length; i < len; i++) {
                TouchSlide({
                    slideCell: "#" + TouchSlideObjIDArr[i].elmId,
                    titCell: ".hd ul",
                    mainCell: ".bd ul",
                    effect: "leftLoop",
                    autoPage: true,
                    autoPlay: TouchSlideObjIDArr[i].isAutoPlay
                });
                //去掉hd中的文字
                $("#" + TouchSlideObjIDArr[i].elmId + " .hd li").html("");
            }



        },
        //初始化收藏过得商品
        initCollectedProductEvent: function (collectedIdArr) {
            for (var i = 0, len = collectedIdArr.length; i < len; i++) {
                $("ul.collection-ul li[productId='" + collectedIdArr[i] + "'] span.collection img").attr("src", "/images/zwg_index_collection_done_btn.png");
            }
        }
    },
    //不是所有页面都拥有的常用jquery对象和函数
    notAllPageVariable: {
},
notAllPageFunc: {
    //当页面含有购物车时，注册购物车变量
    _initCarProductCountSpan: function () {
        frontPageCommonFuc.notAllPageVariable.$shopCarProductCountSpan = $(".shopcar-btn").find("span.product-count");
    },
    //当页面含有客服时，注册客服变量
    _initCarKeFuMegSpan: function () {
        frontPageCommonFuc.notAllPageVariable.$keFuMegSpan = $(".kefu-btn").find("span.has-message");
    },
    //购物车样式变化 param: (int)productCount
    initOrResetShopCarFuc: function (productCount) {
        frontPageCommonFuc.notAllPageFunc._initCarProductCountSpan();
        var $shopCarProductCountSpan = frontPageCommonFuc.notAllPageVariable.$shopCarProductCountSpan;
        if (productCount == false || productCount == 0) {
            $shopCarProductCountSpan.html(0).hide();
        } else {
            $shopCarProductCountSpan.html(productCount).show();
        }
    },

    //客服消息样式变化相关 param: (int)messageCount
    initOrResetKeFuRemind: function (messageCount) {
        frontPageCommonFuc.notAllPageFunc._initCarKeFuMegSpan();
        var $keFuMegSpan = frontPageCommonFuc.notAllPageVariable.$keFuMegSpan;
        if (messageCount == false || messageCount == 0) {
            $keFuMegSpan.html(0).hide();
        } else {
            $keFuMegSpan.html("").show();
        }
    },

    menu_m: function () {
        $("ul.select-options li").click(function () {
            var $myself = $(this);
            $myself.find("a").addClass("onclick");
            $myself.siblings("li").find("a").removeClass("onclick");
        });
    },
    bindCollectBtnClickEvent: function () {
        $(".collection-ul span.collection").unbind("click");
        $(".collection-ul span.collection").bind("click", function (e) {
            var event = e || event;
            if (event.stopPropagation) {
                event.stopPropagation();
                event.preventDefault();
            }
            if (event.cancelBubble) {
                event.cancelBubble = true;
            }


            var $myself = $(this);
            var $myself_img = $myself.find("img");
            var curImgSrc = $myself_img.attr("src");

            var deleteFavoCallBack = {
                success: function () {
                    $myself_img.attr("src", curImgSrc.replace("_done_btn", "_not_btn"));
                },
                failure: function () {

                }
            };
            var addFavoCallBack = {
                success: function () {
                    $myself_img.attr("src", curImgSrc.replace("_not_btn", "_done_btn"));
                },
                failure: function () {

                }
            };
            if (/done/.test(curImgSrc)) {
                var goodsSkuid = $myself.closest("li").attr("productid");
                Favorites.DeleteUserFavoriteByGoodsSkuId(goodsSkuid, deleteFavoCallBack.success);
            } else {
                var goodsSkuid = $myself.parent().parent().parent().attr("productid");
                Favorites.AddFavoritesByGoodsSkuId(goodsSkuid, addFavoCallBack.success);
            }
        });
    },

    clickForAttention: function ($pElm) {
        var $myself = $pElm;
        if ($myself.hasClass("already-attention")) {
            $myself.removeClass("already-attention");
            $myself.find("span").html("+<span class=\"plus_letterspace\"></span>关注");
        } else {
            $myself.addClass("already-attention");
            $myself.find("span").html("");
            $myself.find("span").html("已关注");
        }
    },

    clickForAppraisal: function ($discussItem) {
        $discussItem.find("table tr:eq(0) td:last").click(function () {
            var $myself = $(this);
            var $topicDiscussItemElm = $myself.closest(".discuss-item");
            //判断用户是否已经点过赞
            var appraisalCount = parseInt($topicDiscussItemElm.find(".appraisal-count").html());
            if ($myself.hasClass("already-appraisal")) {
                
                $myself.removeClass("already-appraisal");
                appraisalCount--;
                $topicDiscussItemElm.find(".appraisal-count").html(appraisalCount);
            } else {

                $myself.addClass("already-appraisal");
                appraisalCount++;
                $topicDiscussItemElm.find(".appraisal-count").html(appraisalCount);
            }
        });
    },

    //前端信息提示，可以控制消息弹出块的背景和颜色
    ShowTempMessage: function (msg, delaySeconds, option) {
        var top = parseFloat(document.documentElement.scrollTop) + parseFloat(200);
        if (msg == "") return;
        var Tip = $('<span>' + msg + '</span>'),
            move = 30;
        var defaultPara = {
            display: 'none',
            position: 'absolute',
            padding: '5px 10px',
            color: '#fff',
            left: '50%',
            top: '50%',
            opacity: 0,
            "line-height": "30px",
            "font-size": "12px",
            'z-index': 99999,
            "border-radius": "5px",
            'background-color': '#333',
            'top': top + 'px'
        };
        option = $.extend({}, defaultPara, option);
        Tip.appendTo(document.body).css(option);
        option = {
            display: 'block',
            'margin-left': -Tip.outerWidth() / 2
        };
        Tip.appendTo(document.body).css(option);

        if (Tip.width() > 300) {
            Tip.css({ width: 300 });
            Tip.css({ 'margin-left': -Tip.outerWidth() / 2 });
        }
        var showTipTimer = setTimeout(function () {
            var top = Tip.offset().top;
            Tip.css({
                top: top + move / 2
            });
            Tip.animate({
                top: top - move / 2,
                opacity: 0.8
            }, function () {
                setTimeout(function () {
                    var top = Tip.offset().top;
                    Tip.animate({
                        top: top - move,
                        opacity: 0
                    }, function () {
                        Tip.remove();
                    });
                }, delaySeconds || 1000);
            });
        });
        return Tip;
    },

    is_weixin: function(){
        var ua = navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i)=="micromessenger") {
            return true;
        } else {
            return false;
       }
}
}
};

//购物车，客服相关样式
var frontPageCommonFucV16420 = {
    notAllPageVariable: {
    },
    notAllPageFunc: {
        //当页面含有购物车时，注册购物车变量
        _initCarProductCountSpan: function () {
            frontPageCommonFucV16420.notAllPageVariable.$shopCarProductCountSpan = $(".common_top_v2_more_details ul li:eq(0) a span:last");
        },
        //当页面含有客服时，注册客服变量
        _initCarKeFuMegSpan: function () {
            frontPageCommonFucV16420.notAllPageVariable.$keFuMegSpan = $(".common_top_v2_more_details ul li:eq(1) a span:last");
        },
        //购物车样式变化 param: (int)productCount
        initOrResetShopCarFuc: function (productCount) {
            frontPageCommonFucV16420.notAllPageFunc._initCarProductCountSpan();
            var $shopCarProductCountSpan = frontPageCommonFucV16420.notAllPageVariable.$shopCarProductCountSpan;
            if (productCount == false || productCount == 0) {
                $shopCarProductCountSpan.html(0).hide();
            } else {
                $shopCarProductCountSpan.html(productCount).show();
            }
        },

        //客服消息样式变化相关 param: (int)messageCount
        initOrResetKeFuRemind: function (messageCount) {
            frontPageCommonFucV16420.notAllPageFunc._initCarKeFuMegSpan();
            var $keFuMegSpan = frontPageCommonFucV16420.notAllPageVariable.$keFuMegSpan;
            if (messageCount == false || messageCount == 0) {
                $keFuMegSpan.html(0).hide();
            } else {
                $keFuMegSpan.html("").show();
            }
        }

    }
};



$(document).ready(function () {
    //全局loading加载
    //if (!$("body").hasClass("z_index")) {
    //    $(".spinner").remove();
    //    $(".wrap").fadeIn().css("visibility", "visible");
    //}
    if ($(".spinner").length > 0) {
        $(".spinner").remove();
        $(".wrap").fadeIn().css("visibility", "visible");
    }
    AllPageFunc.InvokefrontEndAllFuc();
});

