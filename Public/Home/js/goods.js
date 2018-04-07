var scrollFlag = 0;

$(document).ready(function () {
    // 加载商品管理的图片管理控制组件并初始化商品管理的筛选项
    renderContent('商品图片管理');

    // getPageInfo();
});

//固定显示功能模块
function fixedElement() {
    //左侧功能模块导航固定
    var mBox = document.getElementById('modules-box');
    var w = mBox.offsetWidth;
    // console.log(w);
    mBox.style.position = 'fixed';
    mBox.style.width = w + 'px';
    mBox.style.top = 50;
    mBox.style.zIndex = 999;
    //顶部控制功能盒子固定
    var cBox = document.getElementById('controlBox-father');
    var tBody = document.getElementById('bodyTable');
    var cw = cBox.offsetWidth;
    var ch = cBox.offsetHeight;

    cBox.style.position = 'fixed';
    cBox.style.width = cw + 'px';
    cBox.style.top = 50;
    cBox.style.zIndex = 999;
    tBody.style.marginTop = ch + 'px';
    //标记已经触发滚动监听条件
    scrollFlag = 1;
}

//切换功能模块
function changeGoodsModule(e) {
    var event = window.event || e;
    var target = event.target || event.srcElement;

    var activeEl = document.getElementsByClassName('active')[0];
    if (target == activeEl) {
        console.log('You click the same module!');
    } else {
        // activeEl.className = activeEl.className.replace( /(?:^|\s)active(?!\S)/g , '' );
        activeEl.className = activeEl.className.replace('active', '');
        target.parentNode.className += 'active';

        decideContent(target.innerHTML);

        // //重新渲染新模块
        // var productType = $('#product-type .active')[0];
        // var cat_id = productType.title;
        // $('#goods-table').children().remove();
        // var catClass = cat_id + '-table';
        // var newElement = $('<table cellspacing="1" class="table ' + catClass + ' table-hover table-bordered tablesorter" id="bodyTable"><thead id="tableHead"></thead><tbody id="stockTbody"></tbody></table>');
        // $('#goods-table').append(newElement);
        // renderContent(target.innerHTML);
    }
}

//重绘商品管理
function renderContent(module) {
    // console.log(module);
    //close all pop
    // $('[data-toggle="popover"]').popover('hide');
    $('.popover').popover('hide');

    var controlBox = document.getElementById('controlBox');


    // reset scroll

    var controlBoxFather = document.getElementById('controlBox-father');
    controlBoxFather.style.position = 'relative';
    $('body').scrollTop(0);


    switch (module) {
        case '商品图片管理':
            controlBox.innerHTML = component.controlPart.searchBox;
            searchGoods();
            enterBind();
            // empty table
            var bodyBox = document.getElementById('bodyTable');
            // bodyBox.style.margin = 0 + 'px';
            break;
        case '商城图片管理':
            controlBox.innerHTML = component1.controlPart.searchBox;
            initImageGroupOptions();
            multipleImgUpload();
            searchImage();
            enterBind();
            // empty table
            var bodyBox = document.getElementById('bodyTable');
            // bodyBox.style.margin = 0 + 'px';
            break;
    }
}

//重绘库存一览
function renderCategory(module) {
    // console.log(module);
    //close all pop
    //$('[data-toggle="popover"]').popover('hide');
    $('.popover').popover('hide');

    var controlBox = document.getElementById('controlBox');
    var tableHead = document.getElementById('tableHead');
    var tableBody = document.getElementById('stockTbody');

    // reset scroll
    var bodyBox = document.getElementById('bodyTable');
    var controlBoxFather = document.getElementById('controlBox-father');
    controlBoxFather.style.position = 'relative';
    $('body').scrollTop(0);
    bodyBox.style.margin = 0 + 'px';

    // empty table
    tableBody.innerHTML = '';
    var mod = $("#navbar li.active a")[0].innerHTML;

    switch (mod) {
        case '商品图片管理':
            controlBox.innerHTML = component.controlPart.searchBox;
            tableHead.title = 'goods-img';
            searchGoods();
            enterBind();
            break;
    }
}

// 初始化商品管理筛选项
function initViewSelect(n) {
    // console.log('in parse');
    var oc = JSON.parse(optionCategory);
    var viewSelect = $('#productType');
    var block = '&nbsp&nbsp';
    viewSelect.html('');
    // 默认为框架眼镜
    viewSelect.attr('title', 'K');
    var haveSon = [];
    var father_id = null;
    for (var v in oc) {
        var opt = document.createElement('option');
        if (oc[v]['parent_id'] != 0) {
            opt.value = oc[v]['parent_id'];
        } else {
            opt.value = oc[v]['cat_id'];
        }
        if (n == 'inout') { // 采购入库和退货出库需要用到的分类组件初始化
            opt.title = oc[v]['category'];
            opt.id = 'cat_id' + oc[v]['cat_id'];
            // 将有子类的父类挑选出来
            if (oc[v]['parent_id'] == father_id) {
                haveSon.push(father_id);
            }
            father_id = oc[v]['cat_id'];
        } else {
            opt.title = oc[v]['cat_id'];
        }
        opt.innerHTML = block.repeat(oc[v]['level'] * 2) + oc[v]['category'];
        viewSelect.append(opt);
    }
    // 采购入库和退货出库，禁选父级选项
    if (n == 'inout') {
        for (var v in haveSon) {
            var opt = document.getElementById('cat_id' + haveSon[v]);
            opt.disabled = 'true';
        }
    }
}

//搜索符合条件的商品信息
function searchGoods() {
    var searchInput = document.getElementById('searchInput');
    // var productType = document.getElementById('product-type');
    var productType = $('#product-type .active')[0];
    var cat_id = productType.title;
    var ajaxData = {
        search_value: searchInput.value,
        cat_id: cat_id
    };

    var url = URL + '/goodsList';
    // 确定回调渲染的商品类型
    var pType = getProductType(productType.title);

    $.ajax({
        method: "GET",
        url: url,
        data: ajaxData,
        success: function (res) {
            if (res.sign == 1) {
                $('#goods-table').children().remove();
                var catClass = cat_id + '-table';
                var newElement = $('<table cellspacing="1" class="table ' + catClass + ' table-hover table-bordered tablesorter" id="bodyTable"><thead id="tableHead"></thead><tbody id="stockTbody"></tbody></table>');
                $('#goods-table').append(newElement);
                var tHead = document.getElementById('tableHead');

                initTable(res.result, 'refresh', pType);
            } else {
                addNoitemInfo();
            }
        }
    })
}

//返回选中的商品类型
function getProductType(n) {
    var pType = null;
    switch (n) {
        case 'K': // frame
            pType = goodsTable.field;
            break;
        case 'T': // sunframe
            pType = goodsTable.field;
            break;
        case 'G': // sport
            pType = goodsTable.field;
            break;
        case 'Y': // contact
            pType = goodsTable.field;
            break;
        case 'U': // leg
            pType = goodsTable.field;
            break;
        case 'H': // leaner
            pType = goodsTable.field;
            break;
        default:
            pType = goodsTable.field;
    }
    return pType;
}

//控件回车按钮绑定事件
function enterBind() {
    $('.first-part-input').keydown(function (event) {
        if (event.keyCode == '13') {
            searchGoods();
        }
    });
}

//搜索结果为空时说明搜索不到
function addNoitemInfo() {
    var tBody = document.getElementById('content');
    var tableBody = document.getElementById('stockTbody');
    var tableP = document.getElementById('tableP');
    if (!tableP) {
        if (tableBody != null) {
            tableBody.innerHTML = '';
        }
        var p = document.createElement('p');
        p.innerHTML = '无符合条件的数据';
        p.className = 'table-p';
        p.id = 'tableP';
        tBody.appendChild(p);
    } else {
        console.log('exist tableP');
    }
}

// 移除表格内容
function removeTableP() {
    var tableP = document.getElementById('tableP');
    if (tableP) {
        tableP.parentNode.removeChild(tableP);
    }
}

// 锦星加：用于判断tab选项功能，显示不同内容
function decideContent(tab_name) {

    if (tab_name == '商城页面管理') {
        $('.container-fluid').html(func_component['pages_manage']);
        //渲染所有商城页面信息
        getPageInfo();
    } else if(tab_name == '商城图片管理') {
        $('.container-fluid').html(func_component['all_img_manage']);
        renderContent('商城图片管理');
    } else if (tab_name == '商品图片管理')  {
        $('.container-fluid').html(func_component['good_img_manage']);
        //重新渲染新模块
        var productType = $('#product-type .active')[0];
        var cat_id = productType.title;
        $('#goods-table').children().remove();
        var catClass = cat_id + '-table';
        var newElement = $('<table cellspacing="1" class="table ' + catClass + ' table-hover table-bordered tablesorter" id="bodyTable"><thead id="tableHead"></thead><tbody id="stockTbody"></tbody></table>');
        $('#goods-table').append(newElement);
        renderContent(tab_name);
    } else if (tab_name == '优惠券管理') {
        $('.container-fluid').html(func_component['coupon_manage']);
        initCouponList();
    }
}