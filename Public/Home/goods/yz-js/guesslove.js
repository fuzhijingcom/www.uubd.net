var currpage = 2; //设置当前页数
var pageSize = 8;

$(function () {
    var _IndexPageSize = pageSize;
    guesslove(1, _IndexPageSize, "firstLoading");
});



//猜你喜欢
function guesslove(page, pageSize, action) {
    if (isNaN(page)) {
        ShowTempMessage("请输入数字", 3000);
        return false;
    }
    if (page < 1) page = 1;

    //操作中样式
    var clickBtn_id = "#loadmore";
    var clickBtn_Value = $(clickBtn_id).html();
    var loading_id = "login_loading";

    if ($("#" + loading_id).length > 0) {
        return false;
    }
    _html(clickBtn_id, loading_id);

    $.post("/data/index.ashx", { action: 'guess_love', page: page, pageSize: pageSize, rand: Math.random() }, function (data) {
        if (data.Result == 1) {
            ShowGuessLoveStyle(data.Data);

            if (action == "ajaxLoading")
                setCookie("IndexCurrentPage", currpage);
            //下一页
            currpage = page + 1;
            //操作完成正常显示
            ShowButton(clickBtn_id, clickBtn_Value);
        }
        else {
            ShowTempMessage(data.Message, 3 * 1000);
            //操作完成正常显示
            ShowButton(clickBtn_id, clickBtn_Value);
        }
    }, 'json');
}


function ShowGuessLoveStyle(json) {
    var json = eval('(' + json + ')');
    var html = '';

    if (json.rows.length == 0)
        return;

    var LoadImgGoodsID = new Array();

    for (var i = 0; i < json.rows.length; i++) {
        var dr = json.rows[i];
        var url = "/goods/goodsdetail_" + dr.id + "_" + dr.skuid + ".html";
        //var url = "/goods/goodsdetail.aspx?id=" + dr.id + "&skuid=" + dr.skuid + "";
        var title = dr.name;

        LoadImgGoodsID.push("#GuessLove #" + dr.skuid);

        html += '<li id="' + dr.skuid + '" productId ="' + dr.skuid + '" >';
        html += '<a title="' + title + '" href="' + url + '" data-track={"btnName":"' + dr.skuid + '","Link":"' + url + '"}>';
        html += '<img src="/images/14.png" data-original="' + returnImgUrl(dr.goodimg) + '" onerror="errorImg(this);" />';
        html += '<p><span class="collection"><img src="/images/zwg_index_collection_not_btn.png"/></span>';
        html += '<span class="product-name">' + LimitTitleLen(title, 20) + '</span>';
        html += '<span class="current-pri">¥' + dr.price + '</span>';
        //html += '<del class="primary-pri">&nbsp;&nbsp;¥' + dr.marketprice + '&nbsp;&nbsp;</del>';
        html += '</p>';
        html += '<p><span>';

        if (dr.virtual == 1)
            html += '<span class="tag tag_feature">可试戴</span>';
        html += '</span></p>';
        if (dr.logo != '' && typeof (dr.logo) != "undefined")
            html += ' <img class="this-brand-logo" src="' + returnImgUrl(dr.logo) + '"/>';
        html += '</a></li>';
    }
    if (html == "" || typeof (html) == "undefined") {
        $("#GuessLove").html(html);
    }
    else {
        $("#GuessLove").append(html);
    }

    //图片延迟加载
    $(LoadImgGoodsID.join(',')).find("img").lazyload({
        effect: "fadeIn"
    });


    //绑定点击喜欢事件
    frontPageCommonFuc.notAllPageFunc.bindCollectBtnClickEvent();
    //初始化收藏过得商品
    Favorites.GetUserFavoriteGoodsSkuIdList(frontPageCommonFuc.allToolkitFunction.initCollectedProductEvent);
}
