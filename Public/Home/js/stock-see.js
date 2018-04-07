/**
 * Created by chochik on 8/4/16.
 */

// 切换商品类型
function changeCategory(e) {
    var event = window.event || e;
    var target = event.target || event.srcElement;

    var activeEl = $("#modules-box a.active")[0];
    if (target != activeEl) {
        $("#product-type a.active").removeClass("active");
        target.className += ' active';

        cat_id = target.title;
        renderCategory(cat_id);
    }
}

//将当前single_price赋值给弹框
function getCurrentPrice(tid){
    return function __getCurrentPrice(){
        // console.log('in current price');
        var currentPrice = document.getElementById(tid).innerHTML;
        var pricePop = document.getElementsByName(tid)[0];
        pricePop.value = parseFloat(currentPrice);
        // console.log(currentPrice, pricePop, pricePop.value);
    }
}

var tmpId;
var tmpPId;
//加载数据渲染表格的方法
function initTable(stockinfo, type, pType, position, cat_id){
    // console.log(pType);
    // console.log(stockinfo);
    if(stockinfo){
        var stockTable = document.getElementById('stockTbody');
        removeTableP();
        var tableHead = document.getElementById('tableHead');
        // console.log(tableHead);
        tableHead.innerHTML = component.tablePart[cat_id];
        tableHead.title = 'stock-see';
        if(type == 'refresh') stockTable.innerHTML = "";
        // 根据商品类型映射关系判断生成的表格需要哪些列-----映射关系存在 stock-components.js
        var headText = pType;
        for(var i=0; i<stockinfo.length; i++){
            var TR = document.createElement('tr');
            // 根据商品类型映射关系判断生成的表格需要哪些列-----映射关系存在 stock-components.js
            for(var j=0; j<headText.length; j++){
                var td = document.createElement('td');
                td.innerHTML = stockinfo[i][headText[j].item];
                td.id = headText[j].item + stockinfo[i]['sku_id'];
                if (! headText[j].show) {
                    td.style.display = "none";
                }
                //td特殊处理函数
                specialTd(td,headText[j].item,stockinfo[i]);
                if(j==0){
                    // sku_id
                    var hid = document.createElement('input');
                    hid.type = 'hidden';
                    hid.id = 'sku_id'+stockinfo[i]['sku_id'];
                    hid.value = stockinfo[i]['sku_id'];
                    td.appendChild(hid);
                }
                TR.appendChild(td);
            }
            
            
            if(type == 'refresh'){ // 刷新表项
                stockTable.appendChild(TR);
            }else{ // 追加表项
                stockTable.insertBefore(TR, stockTable.firstChild);
            }

            // 初始化弹框事件
            tmpId = 'quantity'+stockinfo[i]['sku_id'];
            tmpPId = 'single_price'+ stockinfo[i]['sku_id'];

            if (position != '汇总') {
                var mod = $("#navbar li.active a")[0].innerHTML;

                if (mod == '库存一览') {
                    initPop(TR,tmpId,tmpPId,stockinfo[i], cat_id);
                } else {
                    // 库存调换调出框实现，非库存一览
                    initPopEdit(TR,tmpId,tmpPId,stockinfo[i], cat_id);
                }
            }
        }

        // call the tablesorter plugin
        $("table").tablesorter({debug: false, widgets: ['zebra']});
    }else{
        console.log('welcome to stockManagement!');
    }
}

var pcome = null;
var pgo = null;
// 添加给表项初始化点击弹框事件
function initPop(TR, tmpId, tmpPId, goodsInfo, catId){
    // console.log(goodsInfo);
    // console.log('TR');
    // console.log(TR);
    // console.log('in Pop');
    //初始化TR的点击pop事件
    TR.setAttribute('data-placement','bottom');
    TR.setAttribute('data-toggle','popover');
    TR.setAttribute('data-container','body');
    TR.setAttribute('data-trigger','click');

    var skuId = goodsInfo.sku_id;
    var sellingId = goodsInfo.selling_id;
    var productId = goodsInfo.product_id || '';
    var style = goodsInfo.style || '';
    var quantity = goodsInfo.quantity;
    var price = goodsInfo.price;
    var isListing = goodsInfo.is_listing;
    var warehouse = goodsInfo.warehouse;
    var goodsName = goodsInfo.goods_name;
    var brand = goodsInfo.brand;
    var supplier = goodsInfo.supplier;
    var procurementPrice = goodsInfo.procurement_price;
    var isReplenish = goodsInfo.is_replenish;
    var degree = goodsInfo.degree || '';
    var custom = goodsInfo.custom || '';
    var water = goodsInfo.water || '';
    var attr = goodsInfo.attr || '';
    
    // 初始化数量列的点击弹框事件
    var windowTitle = '<h5 title="pop">' + sellingId + ' ' +  style + ' 商品信息' + '</h5>';

    var catIdStr = '<div class="pop-form-group form-group" title="pop" style="display: none;"><label class="pop-label" title="pop">cat_id：</label><input title="pop" type="text" class="form-control popAlterNum popover-control" name="cat_id" value="' + catId + '"><div class="clear"></div></div>';

    var sellingIdStr = '<div class="pop-form-group form-group good-item-id" title="pop"><label class="pop-label" title="pop">商城编号：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="selling_id" value="' + sellingId + '"><div class="clear"></div></div>';
    var productIdStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">生产编号：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="product_id" value="' + productId + '"><div class="clear"></div></div>';
    var nameStr = '<div class="pop-form-group form-group good-item-name" title="pop"><label class="pop-label" title="pop">商品名称：</label><input title="pop" type="text" class="goods-name form-control popAlterNum popover-control" name="goods_name" value="' + goodsName + '"><div class="clear"></div></div>';
    var styleStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">款型：</label><input title="pop" type="text" class="goods-style form-control popAlterNum popover-control" name="style" value="' + style + '"><div class="clear"></div></div>';
    var attrStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">属性：</label><input title="pop" type="text" class="goods-style form-control popAlterNum popover-control" name="attr" value="' + attr + '"><div class="clear"></div></div>';
    var warehouseStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">所在仓库：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="warehouse" value="' + warehouse + '" disabled><div class="clear"></div></div>';
    var quantityStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">库存数量：</label><a class="btn btn-default nu fl" href="javascript:void(0)" title="pop" onclick="quantityPlusorMinus(this,0)"><span title="pop" class="glyphicon glyphicon-minus"></span></a><input title="pop" type="text" class="form-control popAlterNum popover-control width-52" name="quantity" value="' + quantity + '" onblur="checkValue(this,2)"><a title="pop" class="btn btn-default fl" href="javascript:void(0)" onclick="quantityPlusorMinus(this,1)"><span title="pop" class="glyphicon glyphicon-plus"></span></a><div class="clear"></div></div>';

    var priceStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">价格：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="price" value="' + price + '" onblur="checkValue(this,2)"><div class="clear"></div></div>';

    // var isListingStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">上下架状态：</label><input title="pop" type="text" class="form-control popAlterNum popover-control" name="is_listing" value="' + isListing + '"><div class="clear"></div></div>';
    var isListingFlag = (isListing == 1) ? " checked" : '';
    var deListingFlag = (isListing == 1) ? '' : " checked";
    var isListingStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">上下架状态：</label><label title="pop" class="pop-label form-check-inline"><input title="pop" class="form-check-input" type="radio" onclick="changeRadioChecked(this);" name="is_listing" id="is-listing1" value="1"' + isListingFlag + '>上架</label> <label title="pop" class="pop-label form-check-inline"><input  title="pop" class="form-check-input" type="radio" onclick="changeRadioChecked(this);" name="is_listing" id="is-listing2" value="0"' + deListingFlag + '>下架</label><div class="clear"></div></div> ';

    var brandStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">品牌：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="brand" value="' + brand + '"><div class="clear"></div></div>';
    var supplierStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">供应商：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="supplier" value="' + supplier +'"><div class="clear"></div></div>';

    var proPriceStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">采购价：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="procurement_price" value="' + procurementPrice + '" onblur="checkValue(this,3)"><div class="clear"></div></div>';

    // var isReplenishStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">能否补货：</label><input title="pop" type="text" class="form-control popAlterNum popover-control" name="is_replenish" value="' + isReplenish + '"><div class="clear"></div></div>';

    var isRepFlag = (isReplenish == '是') ? " checked" : '';
    var unRepFlag = (isReplenish == '是') ? '' : " checked";
    var isReplenishStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">能否补货：</label><label title="pop" class="pop-label form-check-inline"><input title="pop" class="form-check-input" type="radio" onclick="changeRadioChecked(this);" name="is_replenish" id="is-replenish1" value="是"' + isRepFlag + '>能</label><label title="pop" class="pop-label form-check-inline"><input title="pop" class="form-check-input" onclick="changeRadioChecked(this);" type="radio" name="is_replenish" id="is-replenish2" value="否"' + unRepFlag + '>否</label><div class="clear"></div></div> ';

    var degreeStr = '<div class="pop-form-group form-group dushu" title="pop"><label class="pop-label" title="pop">度数：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="degree" value="' + degree + '"><div class="clear"></div></div>';

    var customStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">定制类型：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="custom" value="' + custom + '"><div class="clear"></div></div>';

    var waterStr = '<div class="pop-form-group form-group hanshui" title="pop"><label class="pop-label" title="pop">含水量：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="water" value="' + water + '"><div class="clear"></div></div>';

    var reasonStr = '<div class="pop-form-group form-group reason" title="pop"><label class="pop-label" title="pop">变更理由：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="comment" placeholder="请填写理由"><div class="clear"></div></div>';

    var removeBtn = '<a type="button" class="btn btn-danger sub-btn" onclick="removeProductAttr(this)" data-toggle="confirmation" title="' + skuId + '" name="pop">删除品类</a>';

    var btnStr = '<p title="pop" class="pop-btn"><a title="' + skuId + '" onclick="editInfo(this)" class="btn btn-primary sub-btn" name="pop">保存信息</a><a title="' + skuId + '" onclick="addInfo(this)" class="btn btn-primary sub-btn" name="pop">添加品类</a>' + removeBtn + '</p>';

    var contentStr;
    if (catId == 'K' || catId == 'T' || catId == 'U') {
        degreeStr = '';
        customStr = '';
        waterStr = '';
        attrStr = '';
        contentStr = '<div class="goods-window" id="window-' + skuId + '">' + catIdStr + nameStr + sellingIdStr + styleStr + productIdStr + priceStr + proPriceStr + quantityStr + brandStr + supplierStr + warehouseStr + degreeStr + customStr + waterStr + isListingStr + isReplenishStr + reasonStr + '</div>' + btnStr;
    }

    if (catId == 'G') {
        customStr = '';
        waterStr = '';
        attrStr = '';
        contentStr = '<div class="goods-window" id="window-' + skuId + '">' + catIdStr + nameStr + sellingIdStr + styleStr + productIdStr + priceStr + proPriceStr + quantityStr + degreeStr + brandStr + supplierStr + warehouseStr + customStr + waterStr + isListingStr + isReplenishStr + reasonStr + '</div>' + btnStr;
    }

    if (catId == 'Y') {
        productIdStr = '';
        styleStr = '';
        attrStr = '';
        contentStr = '<div class="goods-window" id="window-' + skuId + '">' + catIdStr + sellingIdStr + nameStr + styleStr + productIdStr + priceStr + proPriceStr + quantityStr + degreeStr + customStr + waterStr + brandStr + supplierStr + warehouseStr + isListingStr + isReplenishStr + reasonStr + '</div>' + btnStr;
    }

    if (catId == 'H') {
        productIdStr = '';
        degreeStr = '';
        customStr = '';
        waterStr = '';
        style = '';
        contentStr = '<div class="goods-window" id="window-' + skuId + '">' + catIdStr + sellingIdStr + nameStr + attrStr + productIdStr + priceStr + proPriceStr + quantityStr + brandStr + supplierStr + warehouseStr + degreeStr + customStr + waterStr + isListingStr + isReplenishStr + reasonStr + '</div>' + btnStr;
    }

    //var contentStr = '<div class="goods-window" id="window-' + skuId + '">' + catIdStr + sellingIdStr + nameStr + styleStr + productIdStr + priceStr + warehouseStr + quantityStr + brandStr + supplierStr + proPriceStr + degreeStr + customStr + waterStr + isListingStr + isReplenishStr + reasonStr + '</div>' + btnStr;

    var option = {
        title: windowTitle,
        content: contentStr,
        html: 'true'
    };

    $(TR).popover(option);
    $(TR).on('shown.bs.popover', closeOpeningPop(tmpId));
    // $(TR).on('shown.bs.popover', getCurrentPrice(tmpPId));
    $(TR).on('shown.bs.popover', addTrLight(tmpId));
}

//增减键改变库存数量
function quantityPlusorMinus(e,n) {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    var num = $('input[name="quantity"]').val();

    switch (n) {
        case 0 :
            if(num == 0) return;
            num--;
            $('input[name="quantity"]').val(num);
            break;
        case 1 :
            num++;
            $('input[name="quantity"]').val(num);
            break;
    }
}

//修改radio checked属性
function changeRadioChecked(e) {
    var event = window.event || e;
    var target = event.target || event.srcElement;

    var $v = $(target);
    $v.parent().parent().find('input[checked]').removeAttr('checked');
    $v.attr({checked:true});
    closeOpeningPop();
}

function editInfo(e) {
    // 获取 onclick 函数点击元素本身
    var event = window.event || e;
    var target = event.srcElement || event.target;

    // 防止多次点击
    var $btn = $(target).button('loading');


    var skuId = $(target).attr("title");

    var elId = 'window-' + skuId;

    var catId = $('#' + elId + ' input[name=\'cat_id\']').val();
    var sellingId = $('#' + elId + ' input[name=\'selling_id\']').val();
    var productId = $('#' + elId + ' input[name=\'product_id\']').val();
    var goodsName = $('#' + elId + ' input[name=\'goods_name\']').val();
    var style = $('#' + elId + ' input[name=\'style\']').val();
    var warehouse = $('#' + elId + ' input[name=\'warehouse\']').val();
    var quantity = $('#' + elId + ' input[name=\'quantity\']').val();
    var price = $('#' + elId + ' input[name=\'price\']').val();
    var isListing = $('#' + elId + ' input[name=\'is_listing\']:checked').val();
    var brand = $('#' + elId + ' input[name=\'brand\']').val();
    var supplier = $('#' + elId + ' input[name=\'supplier\']').val();
    var procurementPrice = $('#' + elId + ' input[name=\'procurement_price\']').val();
    var isReplenish = $('#' + elId + ' input[name=\'is_replenish\']:checked').val();
    var degree = $('#' + elId + ' input[name=\'degree\']').val();
    var custom = $('#' + elId + ' input[name=\'custom\']').val();
    var water = $('#' + elId + ' input[name=\'water\']').val();
    var comment = $('#' + elId + ' input[name=\'comment\']').val();
    var attr = $('#' + elId + ' input[name=\'attr\']').val();

    // console.log(catId);
    // console.log(sellingId);
    // console.log(productId);
    // console.log(goodsName);
    // console.log(style);
    // console.log(warehouse);
    // console.log(quantity);
    // console.log(price);
    // console.log(isListing);
    // console.log(brand);
    // console.log(supplier);
    // console.log(procurementPrice);
    // console.log(comment);
    // console.log(catId);
    // console.log(quantity);

    var ajaxData = {
        cat_id: catId,
        sku_id: skuId,
        selling_id: sellingId,
        goods_name: goodsName,
        price: price,
        quantity: quantity,
        is_listing: isListing,
        is_replenish: isReplenish,
        procurement_price: procurementPrice,
        supplier: supplier,
        brand: brand,
        warehouse: warehouse,  // 此项内容是不给编辑的
        product_id: productId,
        style: style,
        degree: degree,
        custom: custom,
        water: water,
        attr: attr,
        comment: comment
    };
    console.log(ajaxData);
    var url = URL + "/editGoods";

    var request = $.ajax({
        method: "POST",
        url: url,
        data: ajaxData,
        dataType: "json"
    });

    request.done(function( res ) {
        $( "#log" ).html( res );
        // console.log(res);
        if (res['sign'] == 1) {
            closeOpeningPop(null, 1)();
            searchStock();
        } else {
            alert(res['msg']);
            $btn.button('reset');
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });
}

function addInfo(e) {
    // 获取 onclick 函数点击元素本身
    var event = window.event || e;
    var target = event.srcElement || event.target;

    // 防止多次点击
    var $btn = $(target).button('loading');


    var skuId = $(target).attr("title");

    var elId = 'window-' + skuId;

    var catId = $('#' + elId + ' input[name=\'cat_id\']').val();
    var sellingId = $('#' + elId + ' input[name=\'selling_id\']').val();
    var productId = $('#' + elId + ' input[name=\'product_id\']').val();
    var goodsName = $('#' + elId + ' input[name=\'goods_name\']').val();
    var style = $('#' + elId + ' input[name=\'style\']').val();
    var warehouse = $('#' + elId + ' input[name=\'warehouse\']').val();
    var quantity = $('#' + elId + ' input[name=\'quantity\']').val();
    var price = $('#' + elId + ' input[name=\'price\']').val();
    var isListing = $('#' + elId + ' input[name=\'is_listing\']:checked').val();
    var brand = $('#' + elId + ' input[name=\'brand\']').val();
    var supplier = $('#' + elId + ' input[name=\'supplier\']').val();
    var procurementPrice = $('#' + elId + ' input[name=\'procurement_price\']').val();
    var isReplenish = $('#' + elId + ' input[name=\'is_replenish\']:checked').val();
    var degree = $('#' + elId + ' input[name=\'degree\']').val();
    var custom = $('#' + elId + ' input[name=\'custom\']').val();
    var water = $('#' + elId + ' input[name=\'water\']').val();
    var comment = $('#' + elId + ' input[name=\'comment\']').val();
    var attr = $('#' + elId + ' input[name=\'attr\']').val();

    // console.log(catId);
    // console.log(sellingId);
    // console.log(productId);
    // console.log(goodsName);
    // console.log(style);
    // console.log(warehouse);
    // console.log(quantity);
    // console.log(price);
    // console.log(isListing);
    // console.log(brand);
    // console.log(supplier);
    // console.log(procurementPrice);
    // console.log(comment);
    // console.log(catId);
    // console.log(quantity);

    var ajaxData = {
        cat_id: catId,
        selling_id: sellingId,
        goods_name: goodsName,
        price: price,
        quantity: quantity,
        is_listing: isListing,
        is_replenish: isReplenish,
        procurement_price: procurementPrice,
        supplier: supplier,
        brand: brand,
        warehouse: warehouse,  // 此项内容是不给编辑的
        product_id: productId,
        style: style,
        degree: degree,
        custom: custom,
        water: water,
        attr: attr
    };
    // console.log(ajaxData);
    var url = URL + "/addAttr";

    var request = $.ajax({
        method: "POST",
        url: url,
        data: ajaxData,
        dataType: "json"
    });

    request.done(function( res ) {
        $( "#log" ).html( res );
        // console.log(res);
        if (res['sign'] == 1) {
            closeOpeningPop(null, 1)();
            searchStock();
        } else {
            alert(res['msg']);
            $btn.button('reset');
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });
}

$('.remove-product-attr').click(function (e) {
    console.log('hello-remove');
    closeOpeningPop(null, 1)();
    searchStock();
});

function removeProductAttr(e) {
    var msg = "您真的确定要删除吗？\n\n请确认！";
    if (confirm(msg) == false){
        return false;
    }

    // 获取 onclick 函数点击元素本身
    var event = window.event || e;
    var target = event.srcElement || event.target;

    // 防止多次点击
    var $btn = $(target).button('loading');


    var skuId = $(target).attr("title");

    var request = $.ajax({
        method: "POST",
        url: URL + '/removeAttr',
        data: {sku_id: skuId},
        dataType: "json"
    });

    request.done(function( res ) {
        $( "#log" ).html( res );
        // console.log(res);
        if (res['sign'] == 1) {
            closeOpeningPop(null, 1)();
            searchStock();
        } else {
            alert(res['msg']);
            $btn.button('reset');
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "网络出错，请稍后重试..." );
    });
}

function initAddGoods(el){
    // console.log('in Pop');
    //初始化TR的点击pop事件
    el.setAttribute('data-placement','bottom');
    el.setAttribute('data-toggle','popover');
    el.setAttribute('data-container','body');
    el.setAttribute('data-trigger','click');

    var catId = $('#product-type .active')[0].title;

    // 初始化数量列的点击弹框事件
    var windowTitle = '<h5 id="add-goods-id" title="pop">新增商品入库</h5>';

    var catIdStr = '<div class="pop-form-group form-group" title="pop" style="display: none;"><label class="pop-label" title="pop">cat_id：</label><input title="pop" type="text" class="form-control popAlterNum popover-control" name="cat_id" value="' + catId + '"><div class="clear"></div></div>';

    var sellingIdStr = '<div class="pop-form-group form-group good-item-id" title="pop"><label class="pop-label" title="pop">商城编号：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="selling_id"><div class="clear"></div></div>';
    var productIdStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">生产编号：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="product_id"><div class="clear"></div></div>';
    var nameStr = '<div class="pop-form-group form-group good-item-name" title="pop"><label class="pop-label" title="pop">商品名称：</label><input title="pop" type="text" class="goods-name form-control popAlterNum popover-control" name="goods_name"><div class="clear"></div></div>';
    var styleStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">款型：</label><input title="pop" type="text" class="goods-style form-control popAlterNum popover-control" name="style"><div class="clear"></div></div>';
    var attrStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">属性：</label><input title="pop" type="text" class="goods-style form-control popAlterNum popover-control" name="attr"><div class="clear"></div></div>';
    var warehouseStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">所在仓库：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="warehouse" value="总仓" disabled><div class="clear"></div></div>';
    var quantityStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">库存数量：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="quantity" value="1" onblur="checkValue(this,2)"><div class="clear"></div></div>';

    var priceStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">价格：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="price" value="0.00" onblur="checkValue(this,2)"><div class="clear"></div></div>';

    // var isListingStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">上下架状态：</label><input title="pop" type="text" class="form-control popAlterNum popover-control" name="is_listing" value="1"><div class="clear"></div></div>';
    var isListingStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">上下架状态：</label><label title="pop" class="pop-label form-check-inline"><input title="pop" class="form-check-input" type="radio" onclick="changeRadioChecked(this);" name="is_listing" id="is-listing1" value="1" checked>上架</label> <label title="pop" class="pop-label form-check-inline"><input  title="pop" class="form-check-input" onclick="changeRadioChecked(this);" type="radio" name="is_listing" id="is-listing2" value="0">下架</label><div class="clear"></div></div> ';

    var brandStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">品牌：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="brand"><div class="clear"></div></div>';
    var supplierStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">供应商：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="supplier"><div class="clear"></div></div>';

    var proPriceStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">采购价：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="procurement_price" value="0.00" onblur="checkValue(this,3)"><div class="clear"></div></div>';

    // var old_is_rep = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">能否补货：</label><input title="pop" type="text" class="form-control popAlterNum popover-control" name="is_replenish"><div class="clear"></div></div>';

    var isReplenishStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">能否补货：</label><label title="pop" class="pop-label form-check-inline"><input title="pop" class="form-check-input" type="radio" name="is_replenish" onclick="changeRadioChecked(this);" id="is-replenish1" value="是" checked>能</label><label title="pop" class="pop-label form-check-inline"><input title="pop" class="form-check-input" onclick="changeRadioChecked(this);" type="radio" name="is_replenish" id="is-replenish2" value="否">否</label><div class="clear"></div></div> ';

    var degreeStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">度数：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="degree"><div class="clear"></div></div>';

    var customStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">定制类型：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="custom"><div class="clear"></div></div>';

    var waterStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">含水量：</label><input title="pop" type="text" class="form-control popAlterNum popover-control width-132" name="water"><div class="clear"></div></div>';

    var btnStr = '<p class="pop-btn"><a title="window-add-goods" onclick="addGoods(this)" class="btn btn-primary sub-btn" name="pop">提交</a></p>';

    var contentStr;
    if (catId == 'K' || catId == 'T' || catId == 'U') {
        degreeStr = '';
        customStr = '';
        waterStr = '';
        attrStr = '';
        contentStr = '<div class="goods-window" id="window-add-goods">' + catIdStr + nameStr + sellingIdStr + styleStr + productIdStr + priceStr + proPriceStr + quantityStr + brandStr + supplierStr + warehouseStr + degreeStr + customStr + waterStr + isListingStr + isReplenishStr + '</div>' + btnStr;
    }

    if (catId == 'G') {
        customStr = '';
        waterStr = '';
        attrStr = '';
        contentStr = '<div class="goods-window" id="window-add-goods">' + catIdStr + nameStr + sellingIdStr + styleStr + productIdStr + priceStr + proPriceStr + quantityStr + degreeStr + brandStr + supplierStr + warehouseStr + customStr + waterStr + isListingStr + isReplenishStr + '</div>' + btnStr;
    }

    if (catId == 'Y') {
        productIdStr = '';
        styleStr = '';
        attrStr = '';
        contentStr = '<div class="goods-window" id="window-add-goods">' + catIdStr + sellingIdStr + nameStr + styleStr + productIdStr + priceStr + proPriceStr + quantityStr + degreeStr + customStr + waterStr + brandStr + supplierStr + warehouseStr + isListingStr + isReplenishStr + '</div>' + btnStr;
    }

    if (catId == 'H') {
        productIdStr = '';
        degreeStr = '';
        customStr = '';
        waterStr = '';
        styleStr = '';
        contentStr = '<div class="goods-window" id="window-add-goods">' + catIdStr + sellingIdStr + nameStr + attrStr + productIdStr + priceStr + proPriceStr + quantityStr + degreeStr + customStr + waterStr + brandStr + supplierStr + warehouseStr + isListingStr + isReplenishStr + '</div>' + btnStr;
    }


    // var contentStr = '<div class="goods-window" id="window-add-goods">' + catIdStr + sellingIdStr + productIdStr + nameStr + styleStr + warehouseStr + quantityStr + priceStr + isListingStr + brandStr + supplierStr + proPriceStr + isReplenishStr + degreeStr + customStr + waterStr + '</div>' + btnStr;


    //var contentStr = '<div class="goods-window" id="window-add-goods">' + catIdStr + sellingIdStr + nameStr + styleStr + productIdStr + priceStr + warehouseStr + quantityStr + brandStr + supplierStr + proPriceStr + degreeStr + customStr + waterStr + isListingStr + isReplenishStr + '</div>' + btnStr;

    var option = {
        title:  windowTitle,
        content: contentStr,
        html: 'true'
    };

    // openingPopId = 'add-goods-id';
    openingGoodsPop = 'add-goods-id';

    $(el).popover(option);
    // $(el).on('shown.bs.popover', closeOpeningPop('add-goods-id', 1));
    // $(el).on('shown.bs.popover', getCurrentPrice(tmpPId));
    $(el).on('shown.bs.popover', addTrLight(tmpId));
}

function addGoods(e) {
    // 获取 onclick 函数点击元素本身
    var event = window.event || e;
    var target = event.srcElement || event.target;

    // 防止多次点击
    var $btn = $(target).button('loading');

    var elId = $(target).attr("title");

    var catId = $('#' + elId + ' input[name=\'cat_id\']').val();
    var sellingId = $('#' + elId + ' input[name=\'selling_id\']').val();
    var productId = $('#' + elId + ' input[name=\'product_id\']').val();
    var goodsName = $('#' + elId + ' input[name=\'goods_name\']').val();
    var style = $('#' + elId + ' input[name=\'style\']').val();
    var warehouse = $('#' + elId + ' input[name=\'warehouse\']').val();
    var quantity = $('#' + elId + ' input[name=\'quantity\']').val();
    var price = $('#' + elId + ' input[name=\'price\']').val();
    var isListing = $('#' + elId + ' input[name=\'is_listing\']').val();
    var brand = $('#' + elId + ' input[name=\'brand\']').val();
    var supplier = $('#' + elId + ' input[name=\'supplier\']').val();
    var procurementPrice = $('#' + elId + ' input[name=\'procurement_price\']').val();
    var isReplenish = $('#' + elId + ' input[name=\'is_replenish\']').val();
    var degree = $('#' + elId + ' input[name=\'degree\']').val();
    var custom = $('#' + elId + ' input[name=\'custom\']').val();
    var water = $('#' + elId + ' input[name=\'water\']').val();
    var comment = $('#' + elId + ' input[name=\'comment\']').val();
    var attr = $('#' + elId + ' input[name=\'attr\']').val();

    // console.log(catId);
    // console.log(sellingId);
    // console.log(productId);
    // console.log(goodsName);
    // console.log(style);
    // console.log(warehouse);
    // console.log(quantity);
    // console.log(price);
    // console.log(isListing);
    // console.log(brand);
    // console.log(supplier);
    // console.log(procurementPrice);
    // console.log(comment);
    // console.log(catId);
    // console.log(quantity);

    var ajaxData = {
        cat_id: catId,
        selling_id: sellingId,
        goods_name: goodsName,
        price: price,
        quantity: quantity,
        is_listing: isListing,
        is_replenish: isReplenish,
        procurement_price: procurementPrice,
        supplier: supplier,
        brand: brand,
        warehouse: warehouse,  // 此项内容是不给编辑的
        product_id: productId,
        style: style,
        degree: degree,
        custom: custom,
        water: water,
        attr: attr
    };
    console.log(ajaxData);
    var url = URL + "/addGoods";

    var request = $.ajax({
        method: "POST",
        url: url,
        data: ajaxData,
        dataType: "json"
    });

    request.done(function( res ) {
        $( "#log" ).html( res );
        // console.log(res);
        if (res['sign'] == 1) {
            closeOpeningPop(null, 1)();
            searchStock();
        } else {
            alert(res['msg']);
            $btn.button('reset');
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });
}

function submitChangeSwift(e){
    //获取onclick函数点击元素本身
    var event = window.event || e;
    var target = event.srcElement || event.target;
    var comment = target.previousSibling;
    var commentValue = comment.getElementsByTagName('input')[0];
    var pGo = comment.previousSibling;
    var pGoValue = pGo.getElementsByTagName('select')[0];
    var pFrom = pGo.previousSibling;
    var pFromValue = pFrom.getElementsByTagName('select')[0];
    var singlePrice = pFrom.previousSibling;
    var singlePriceValue = singlePrice.getElementsByTagName('input')[0];
    var vNumInput = (singlePrice.parentNode).getElementsByClassName('popAlterNum')[0];
    // 防止多次点击
    var $btn = $(target).button('loading');
    
    //获取点击行的id
    var tdId = target.title;
    var sku_id, sellingId, style, num, priceBox, operationTime;
    var id= tdId.split('quantity')[1];
    // console.log(id);
    num = document.getElementById(tdId);
    var sku = document.getElementById('sku_id'+id);
    // style = document.getElementById('style_full'+id);
    // sellingId = document.getElementById('selling_id'+id);
    priceBox = document.getElementById('single_price'+id);
    operationTime = document.getElementById('operate_time'+id);
    
    if(vNumInput.value==0){
        alert('变动数量不能为零');
        vNumInput.focus();
        $btn.button('reset');
        return false;
    }
    //最终发送的数据变量
    var ajaxData = {
        sku_id: sku.value,
        // selling_id: sellingId.innerHTML,
        // style: style.innerHTML,
        v_num: vNumInput.value,
        new_single_price: singlePriceValue.value,
        product_from: pFromValue.value,
        product_go: pGoValue.value,
        select_comment: commentValue.value
    };
    // console.log(ajaxData);
    //发送ajax(请求）
    var url = URL+"/updatestock";
    // console.log(tdId);
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        dataType: 'json',
        success: function(res){
            // console.log(res);
            // console.log('ready to close');
            if(res['sign']!='-1'){
                closeOpeningPop(null,1)();
                operationTime.innerHTML = res['update_time'];
                num.innerHTML = res['num'];
                priceBox.innerHTML = res['single_price'];
                switch(res['flag']){
                    case 0:
                        var tdList = document.getElementsByClassName(priceBox.className);
                        for(var i=0; i<tdList.length; i++){
                            tdList[i].innerHTML = res['single_price'];
                            addlight(tdList[i]);
                        }
                        break;
                    case 1:
                        //只改数量
                        var TD = document.getElementById(tdId);
                        addlight(TD);
                        break;
                    case 2:
                        //只改价格
                        var tdList = document.getElementsByClassName(priceBox.className);
                        for(var i=0; i<tdList.length; i++){
                            tdList[i].innerHTML = res['single_price'];
                            addlight(tdList[i]);
                        }
                        break;
                }
                $btn.button('reset');
            }else{
                alert(res['msg']);
                $btn.button('reset');
            }
        },
        error: function(err){
            console.log(err);
        }
    });
}


//product putaway or out of stock
// function exchangeProductStockType(n) {
//     //获取onclick函数点击元素本身
//     var event = window.event || e;
//     event.stopPropagation(); // 停止事件冒泡
//     var target = event.srcElement || event.target;
//     var id = (target.parentNode.parentNode.id).split('product_stock_type')[1];
//     // console.log(target);
//     var skuId = document.getElementById('sku_id'+id);
//     var url = URL + '/changeStockType';
//     var ajaxData = {
//         sku_id: skuId.value,
//         change_stock_type_to: n
//     };
//     // console.log(id);
//     // console.log(ajaxData);
//     switch (n) {
//         // 下架商品
//         case 0:
//             if (confirm('确定要下架商品吗')) {
//                 $.ajax({
//                     type: 'POST',
//                     url: url,
//                     data: ajaxData,
//                     success: function (res) {
//                         console.log(res);
//                         if(res){
//                             var updateTd = document.getElementById('product_stock_type'+res['sku_id']);
//                             // updateTd.innerHTML = '';
//                             updateTd.innerHTML = '<div><span class="glyphicon glyphicon-flag product-type-flag-not-on"></span> <span class="flag-text-not-on">已下架</span> <a onclick="exchangeProductStockType(1)" class="btn btn-default flag-btn" href="javascript:void(0)">上架</a></div>';
//                             addlight(updateTd);
//                         }else{
//                             alert('下架失败，请联系技术部');
//                         }
//                     }
//                 })
//             }
//             break;
//         // 上架商品
//         case 1:
//             if (confirm('确定要上架商品吗')) {
//                 $.ajax({
//                     type: 'POST',
//                     url: url,
//                     data: ajaxData,
//                     success: function (res) {
//                         if(res){
//                             var updateTd = document.getElementById('product_stock_type'+res['sku_id']);
//                             updateTd.innerHTML = '<div><span class="glyphicon glyphicon-flag product-type-flag-on"></span> <span class="flag-text-on">上架中</span> <a onclick="exchangeProductStockType(0)" class="btn btn-danger flag-btn" href="javascript:void(0)">下架</a></div>';
//                             addlight(updateTd);
//                         }else{
//                             alert('上架失败，请联系技术部');
//                         }
//                     }
//                 })
//             }
//             break;
//     }
// }

function exportStock(){
    var value = $('#stockTbody').html();
    if(value == ''){
        alert('没有数据！');
        return false;
    }
    var cond = $('#searchInput').val();
    var product_stock_type = $('#pStockType').val();
    var productType = $('#product-type .active')[0].title;
    // var productType = $('#productType').attr('title');
    var warehouse = $('#location').val();
    // console.log(cond);
    // console.log(product_stock_type);
    // console.log(productType);
    var url;
    if(cond!=''){
        url =CONTROLLER+'/exportStockData/cond/'+cond+'/product_stock_type/'+product_stock_type +'/productType/'+productType+'/warehouse/'+warehouse;

    }else{
        url =CONTROLLER+'/exportStockData/product_stock_type/'+product_stock_type+'/productType/'+productType+'/warehouse/'+warehouse;
    }
    location.href = url;
}

// 切换商品类型
function exchangeProductType(){
    //close all pop
    $('[data-toggle="popover"]').popover('hide');

    var tHead = $('#tableHead');
    console.log(tHead.attr('title'));
    var tBody = $('#stockTbody');
    tBody.html('');
    var select = document.getElementById('productType');
    var selectedTitle = select.selectedOptions[0].title;
    var value = select.value;
    select.title = selectedTitle;

    if(tHead.attr('title')!="stock-notice") {
        switch (value) {
            case 'K': // 框架眼镜
                tHead.html(component.tablePart.frameTable);
                break;
            case 'T': // 太阳眼镜
                tHead.html(component.tablePart.frameTable);
                break;
            case 'G': // 功能眼镜
                tHead.html(component.tablePart.sportTable);
                break;
            case 'Y': // 隐形眼镜
                tHead.html(component.tablePart.contactTable);
                break;
            case 'H': // 清洁液
                tHead.html(component.tablePart.leanerTable);
                break;
        }
    }
    if(select.name=='product-type') {
        searchStock();
    }else if(select.name=='notice-type'){
        searchNotice();
    }else if(select.name=='stock-in')  {
        var pType = _getProductType(value);
        // console.log(pType);
        appendStockTypeControlIn(pType);
    }else if(select.name=='stock-out') {
        var pTypeOut = _getProductType(value);
        // console.log(pType);
        appendStockTypeControlOut(pTypeOut);
    }
}

function exchangeTableIntoStock() {
    //close all pop
    $('[data-toggle="popover"]').popover('hide');

    var tHead = $('#tableHead');
    // console.log(tHead.attr('title'));
    var tBody = $('#stockTbody');
    tBody.html('');
    var select = document.getElementById('productType');
    // console.log(select);
    var selectedTitle = select.selectedOptions[0].title;
    var value = select.value;
    select.title = selectedTitle;

    if(tHead.attr('title')!="stock-notice") {
        switch (value) {
            case 'K': // 框架眼镜
                tHead.html(component.tablePart.frameTable);
                break;
            case 'T': // 太阳眼镜
                tHead.html(component.tablePart.frameTable);
                break;
            case 'G': // 功能眼镜
                tHead.html(component.tablePart.sportTable);
                break;
            case 'Y': // 隐形眼镜
                tHead.html(component.tablePart.contactTable);
                break;
            case 'H': // 清洁液
                tHead.html(component.tablePart.leanerTable);
                break;
        }
    }
    if(select.name=='product-type') {
        searchStock();
    }else if(select.name=='notice-type'){
        searchNotice();
    }else if(select.name=='product-type-in'){
        var pType = _getProductType(value);
        // console.log(pType);
        appendStockTypeControlIn(pType);
    }else{
        var pTypeOut = _getProductType(value);
        // console.log(pType);
        appendStockTypeControlOut(pTypeOut);
    }
}

function exchangeTableOutStock() {
    //close all pop
    $('[data-toggle="popover"]').popover('hide');

    var tHead = $('#tableHead');
    // console.log(tHead.attr('title'));
    var tBody = $('#stockTbody');
    tBody.html('');
    var select = document.getElementById('productType');
    // console.log(select);
    var selectedTitle = select.selectedOptions[0].title;
    var value = select.value;
    select.title = selectedTitle;

    if(tHead.attr('title')!="stock-notice") {
        switch (value) {
            case 'K': // 框架眼镜
                tHead.html(component.tablePart.frameTable);
                break;
            case 'T': // 太阳眼镜
                tHead.html(component.tablePart.frameTable);
                break;
            case 'G': // 功能眼镜
                tHead.html(component.tablePart.sportTable);
                break;
            case 'Y': // 隐形眼镜
                tHead.html(component.tablePart.contactTable);
                break;
            case 'H': // 清洁液
                tHead.html(component.tablePart.leanerTable);
                break;
        }
    }
    if(select.name=='product-type') {
        searchStock();
    }else if(select.name=='notice-type'){
        searchNotice();
    }else if(select.name=='product-type-in'){
        var pType = _getProductType(value);
        // console.log(pType);
        appendStockTypeControlIn(pType);
    }else{
        var pTypeOut = _getProductType(value);
        // console.log(pType);
        appendStockTypeControlOut(pTypeOut);
    }
}

// 调货时的筛选项切换，互斥逻辑
function disableBrother(n){
    var even = window.event || e;
    var select = even.target || even.srcElement;

    switch(n){
        case 0: // come
            var goSelect = document.getElementById('pGo');
            var goSelectOpt = goSelect.children;
            // console.log(goSelectOpt);
            var oId = (select.firstChild.id).split('comeId')[1];
            var sameOpt = document.getElementById('goId'+oId);
            if(select.value ==-1){
                for(var i in goSelectOpt){
                    goSelectOpt[i].disabled = true;
                }
                sameOpt.removeAttribute('disabled');
                goSelect.value = sameOpt.value;
            }else{
                var selected = null;
                sameOpt.disabled = true;
                for(var j=0;j<goSelectOpt.length;j++){
                    if(goSelectOpt[j] != sameOpt){
                        goSelectOpt[j].removeAttribute('disabled');
                        if(selected==null){
                            goSelect.value = goSelectOpt[j].value;
                            selected = 1;
                        }
                    }
                }
            }
            break;
        case 1: // go
            var spOpt = document.getElementById('comeIdSpecial');
            if(select.value ==-1){
                spOpt.disabled = true;
            }else{
                spOpt.removeAttribute('disable');
            }
    }
}



//*******************  for search edit  **************/
var tmpIdEdit;
var tmpPIdEdit;
//加载数据渲染表格的方法
function initTableEdit(stockinfo, type, pType, position){
    // console.log('haaa'+pType);
    // console.log('ha222'+stockinfo);
    // console.log(pType);
    if(stockinfo){
        var stockTable = document.getElementById('stockTbody');
        removeTableP();
        if(type == 'refresh') stockTable.innerHTML = "";
        // 根据商品类型映射关系判断生成的表格需要哪些列-----映射关系存在 stock-components.js
        var headText = pType;
        for(var i=0; i<stockinfo.length; i++){
            var TR = document.createElement('tr');
            // 根据商品类型映射关系判断生成的表格需要哪些列-----映射关系存在 stock-components.js
            for(var j=0; j<headText.length; j++){
                var td = document.createElement('td');
                td.innerHTML = stockinfo[i]['s_info'][headText[j]];
                td.id = headText[j] + stockinfo[i]['sku_id'];
                //td特殊处理函数
                specialTd(td,headText[j],stockinfo[i]);
                if(j==0){
                    // sku_id
                    var hid = document.createElement('input');
                    hid.type = 'hidden';
                    hid.id = 'sku_id'+stockinfo[i]['sku_id'];
                    hid.value = stockinfo[i]['sku_id'];
                    td.appendChild(hid);
                }
                TR.appendChild(td);
            }
            
            
            if(type == 'refresh'){ // 刷新表项
                stockTable.appendChild(TR);
            }else{ // 追加表项
                stockTable.insertBefore(TR, stockTable.firstChild);
            }

            // 初始化弹框事件
            tmpIdEdit = 'quantity'+stockinfo[i]['sku_id'];
            tmpPIdEdit = 'single_price'+ stockinfo[i]['sku_id'];
            if(position != -1)
                initPopEditOld(TR,tmpIdEdit,tmpPIdEdit,stockinfo[i]['s_info']);
        }
    }else{
        console.log('welcome to stockManagement!');
    }
}

// var pcomeEdit = null;
// var pgoEdit = null;
// 添加给表项初始化点击弹框事件
function initPopEditOld(TR, tmpIdEdit, tmpPIdEdit, location){
    // console.log('in Pop');
    //初始化TR的点击pop事件
    TR.setAttribute('data-placement','bottom');
    TR.setAttribute('data-toggle','popover');
    TR.setAttribute('data-container','body');
    TR.setAttribute('data-trigger','click');
    // console.log(location['location']);
    // 初始化数量列的点击弹框事件
    var numAndPriceStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop" for="alterNum" >调后数量：</label><div title="pop" class="popover-control input-group" ><span title="pop" class="input-group-btn"><a class="btn btn-default nu" href="javascript:void(0)" onclick="numPlusOrMinus(0)" title="pop"><span title="pop" class="glyphicon glyphicon-minus"></span></a></span><input title="pop" type="text" class="form-control control-num-box popAlterNum" name='+tmpIdEdit+' placeholder="数量" value="1" onblur="checkValue(this,3)"><span title="pop" class="input-group-btn"><a title="pop" class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(1)"><span title="pop" class="glyphicon glyphicon-plus"></span></a></span></div><div class="clear"></div></div>';
    var reasonAndBtnStr = '<div class="pop-form-group form-group pop-reason-box" title="pop"><label class="pop-label" title="pop">变更理由：</label><input id="reasonBox" title="pop" type="text" class="form-control popover-control" placeholder="请说明变更原因" onchange="popSwiftList(0)" onfocus="popSwiftList(1)"><ul title="pop" class="close-flag reason-ul"><li title="pop" onclick="fillReason()">样板调货</li></ul><div class="clear"></div></div>'+
        '<a title='+tmpIdEdit+' onclick="submitEditNum(this)" class="btn btn-primary sub-btn" name="pop">变更</a>';
    var contentStr = numAndPriceStr+reasonAndBtnStr;
    
    var option = {
        content: contentStr,
        html: 'true'
    };

    $(TR).popover(option);
    $(TR).on('shown.bs.popover', closeOpeningPop(tmpIdEdit));
    $(TR).on('shown.bs.popover', getCurrentNum(tmpIdEdit));
    $(TR).on('shown.bs.popover', addTrLight(tmpIdEdit));
}


function submitEditNum(e){
    //获取onclick函数点击元素本身
    var event = window.event || e;
    var target = event.srcElement || event.target;
    var comment = target.previousSibling;
    var commentValue = comment.getElementsByTagName('input')[0];
    var vNumInput = (comment.parentNode).getElementsByClassName('popAlterNum')[0];
    // 防止多次点击
    var $btn = $(target).button('loading');
    
    //获取点击行的id
    var tdId = target.title;
    var sku_id, sellingId, style, num, priceBox, operationTime;
    var id= tdId.split('quantity')[1];
    // console.log(id);
    num = document.getElementById(tdId);
    var sku = document.getElementById('sku_id'+id);
    // style = document.getElementById('style_full'+id);
    // sellingId = document.getElementById('selling_id'+id);
    // priceBox = document.getElementById('single_price'+id);
    operationTime = document.getElementById('operate_time'+id);
    
    if(vNumInput.value<0){
        alert('变动数量不能小于零');
        vNumInput.focus();
        $btn.button('reset');
        return false;
    }
    //最终发送的数据变量
    var ajaxData = {
        sku_id: sku.value,
        // selling_id: sellingId.innerHTML,
        // style: style.innerHTML,
        quantity: vNumInput.value,
        warehouse_id: $('#location').val()
        // new_single_price: singlePriceValue.value,
        // product_from: pFromValue.value,
        // product_go: pGoValue.value,
        // select_comment: commentValue.value
    };
    console.log(ajaxData);
    //发送ajax(请求）
    var url = URL+"/adjustQuantity";
    console.log(url);
    // console.log(tdId);
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        dataType: 'json',
        success: function(res){
            console.log(res);
            // console.log('ready to close');
            if(res['sign']!='-1'){
                closeOpeningPop(null,1)();
                operationTime.innerHTML = res['update_time'];
                num.innerHTML = res['num'];
                //只改数量
                var TD = document.getElementById(tdId);
                addlight(TD);
                $btn.button('reset');
            }else{
                alert(res['msg']);
                $btn.button('reset');
            }
        },
        error: function(err){
            console.log(err);
        }
    });
}
//将当前数量
function getCurrentNum(tid){
    return function __getCurrentNum(){
        var currentNum = document.getElementById(tid).innerHTML;
        console.log(currentNum);
        var numPop = document.getElementsByName(tid)[0];
        numPop.value = parseFloat(currentNum);
    }
}

// 库存调换调窗
var currentShowAttr = 0;
function initPopEdit(TR, tmpId, tmpPId, goodsInfo, catId){
    // console.log(goodsInfo);
    // console.log('TR');
    // console.log(TR);
    // console.log('in Pop');
    //初始化TR的点击pop事件
    TR.setAttribute('data-placement','bottom');
    TR.setAttribute('data-toggle','popover');
    TR.setAttribute('data-container','body');
    TR.setAttribute('data-trigger','click');

    var skuId = goodsInfo.sku_id;
    var sellingId = goodsInfo.selling_id;
    var productId = goodsInfo.product_id || '';
    var style = goodsInfo.style || '';
    var quantity = goodsInfo.quantity;
    var price = goodsInfo.price;
    var isListing = goodsInfo.is_listing;
    var warehouse = goodsInfo.warehouse;
    var goodsName = goodsInfo.goods_name;
    var brand = goodsInfo.brand;
    var supplier = goodsInfo.supplier;
    var procurementPrice = goodsInfo.procurement_price;
    var isReplenish = goodsInfo.is_replenish;
    var degree = goodsInfo.degree || '';
    var custom = goodsInfo.custom || '';
    var water = goodsInfo.water || '';
    var attribute = goodsInfo.attribute || '';

    var attrListId = skuId + '-attr-list';
    var attrNameId = skuId + '-attr-name';
    var subAttrNameId = skuId + '-sub-attr-name';
    var tableBodyId = skuId + '-table-body';

    var selectedStr = '<span title="pop" id="' + attrNameId + '"></span> <select  title="pop" id="' + attrListId + '"></select> <span  title="pop" id="' +  subAttrNameId +'"></span>';

    var windowTitle = '<h5 title="pop" >' + sellingId + ' 商品调库操作 ' + selectedStr + '</h5>';

    var tableStr = '<table  title="pop" class="table table-bordered">' +
        ' <thead title="pop" >' +
            '<tr title="pop" ><th title="pop" >来源仓</th><th title="pop" >仓库名</th><th title="pop" >现有数量</th><th title="pop" >变更后数量</th><th title="pop" >操作</th></tr>' +
        '</thead>' +
        '<tbody title="pop"  id="' + tableBodyId + '">' +
        '</tbody>' +
        ' </table>';

    var btnStr = '<p  title="pop" class="pop-btn"><a title="' + skuId + '" onclick="transferGoods(this)" class="btn btn-primary sub-btn" name="pop">提交</a></p>';


    var contentStr = '<div  title="pop" id="window-' + skuId + '">' + tableStr + '</div>' + btnStr;

    var option = {
        title: windowTitle,
        content: contentStr,
        html: 'true'
    };

    $(TR).popover(option);
    $(TR).on('shown.bs.popover', closeOpeningPop(tmpId, 1));
    // $(TR).on('shown.bs.popover', getCurrentPrice(tmpPId));
    $(TR).on('shown.bs.popover', function () {
        // 初始化弹框事件
        closeOpeningPop(tmpId, 1);
        addTrLight(tmpId);
        getAttrList(skuId, goodsInfo);

    });
}

// 生成弹窗标题的属性下拉项
function getAttrList(skuId, goodsInfo) {
    // console.log(skuId);
    console.log(goodsInfo);
    var attrListId = skuId + '-attr-list';
    var attrNameId = skuId + '-attr-name';
    var subAttrNameId = skuId + '-sub-attr-name';
    var tableBodyId = skuId + '-table-body';
    var request = $.ajax({
        url: URL + '/getAttrList',
        method: "POST",
        data: { sku_id : skuId },
        dataType: "json"
    });

    request.done(function( res ) {
        if (res.sign == 1) {

            // console.log(res);

            var str = '';
            var subStr = '';
            var subAttrName;
            var i;

            if (res.result.attr_level == 1) { // 只有一种库存单元属性
                if (res.result.sub_data.length == 1) {
                    str = '<option title="' + res.result.sub_data[0].sku_id + '">' + res.result.sub_data[0].attr + '</option>';
                    if (typeof(res.result.sub_data[0].attr)=="undefined" || res.result.sub_data[0].attr == '') {
                        $("#" + attrListId).css('display','none');
                    } else {
                        $("#" + attrNameId).append(res.result.sub_attr_name);
                    }
                    $("#" + attrListId).append(str);
                } else {
                    for (i in res.result.sub_data) {
                        var selected = '';
                        if (res.result.sub_attr_name == '款型') {
                            if (goodsInfo.style == res.result.sub_data[i].attr) {
                                selected = ' selected';
                            }
                        }

                        if (res.result.sub_attr_name == '度数') {
                            if (goodsInfo.degree == res.result.sub_data[i].attr) {
                                selected = ' selected';
                            }
                        }

                        if (res.result.sub_attr_name == '属性') {
                            if (goodsInfo.attr == res.result.sub_data[i].attr) {
                                selected = ' selected';
                            }
                        }

                        str += '<option title="' + res.result.sub_data[i].sku_id + '"' + selected + '>' + res.result.sub_data[i].attr + '</option>';
                    }

                    $("#" + attrNameId).append(res.result.sub_attr_name);
                    $("#" + attrListId).append(str);

                    //绑定selectchange事件
                    $("#" + attrListId).change(function(){
                        $("#" + attrListId + " option[selected]").removeAttr('selected');
                        var select_index = $("#" + attrListId).get(0).selectedIndex;
                        var change_sku_id = $("#" + attrListId).children('option').eq(select_index).attr('title');
                        changeWindowTable(skuId,change_sku_id);
                    });
                }


            } else { // 拥有两种库存单元属性

                for (i in res.result.sub_data) {

                    var selected = '';
                    if (goodsInfo.style == res.result.sub_data[i].attr) {
                        selected = ' selected';
                    }

                    str += '<option' + selected + '>' + res.result.sub_data[i].attr + '</option>';

                    subAttrName = res.result.sub_data[i].sub_attr_name;
                    var sub_i;
                    var subStrTmp = '';

                    for (sub_i in res.result.sub_data[i].sub_data) {
                         var degreeSelected = '';

                        if (goodsInfo.degree == res.result.sub_data[i].sub_data[sub_i].attr) {
                            degreeSelected = ' selected';
                        }

                        var tmp = res.result.sub_data[i].sub_data[sub_i].attr || ('无度数');
                        subStrTmp += '<option title="' + res.result.sub_data[i].sub_data[sub_i].sku_id + '"' + degreeSelected + '>' + tmp + '</option>';
                    }

                    subStr += '<select title="pop" class="sub-attr-list">' + subStrTmp + '</select>';

                }

                $("#" + attrNameId).append(res.result.sub_attr_name);
                $("#" + attrListId).append(str);

                $("#" + subAttrNameId).append(subAttrName);
                $("#" + subAttrNameId).after(subStr);

                $(".sub-attr-list").hide();
                var current_index = $("#" + attrListId).get(0).selectedIndex;
                // console.log(current_index);
                $(".sub-attr-list").eq(current_index).show();

                // 实现二级联动
                $("#" + attrListId).change(function(){
                    $(".sub-attr-list").hide();
                    $("#" + attrListId + " option[selected]").removeAttr('selected');
                    current_index = $("#" + attrListId).get(0).selectedIndex;
                    $(".sub-attr-list").eq(current_index).show();
                });
                $(".sub-attr-list").each(function () {
                        $(this).change(function () {
                            var sub_select_id = $(this).get(0).selectedIndex;
                            var change_sku_id = $(this).children('option').eq(sub_select_id).attr('title');
                            changeWindowTable(skuId,change_sku_id);
                        })
                    }

                );
            }

        } else {
            alert(res.msg);
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });

    initWindowTable(skuId);
}

function transferGoods(e) {
    var event = window.event || e;
    var target = event.target || event.srcElement;

    $v = $(target);
    var currentId = $v.attr("title");

    var $pop = $v.parents('div.popover');
    var select_index,current_select_index,skuId;

    //判断是否存在二级菜单
    if($('.sub-attr-list').get(0)) {

        //获取到当前二级菜单的索引值
        select_index = $pop.find("#" + currentId + "-attr-list").get(0).selectedIndex;
        var $sub = $pop.find("select.sub-attr-list").eq(select_index);
        current_select_index = $sub.get(0).selectedIndex;
        skuId = $sub.children('option').eq(current_select_index).attr('title');

    }else {

        current_select_index = $pop.find("#" + currentId + "-attr-list").get(0).selectedIndex;
        skuId = $pop.find("#" + currentId + "-attr-list").children('option').eq(current_select_index).attr('title');

    }


    var tbodyId = currentId + '-table-body';

    var res = {
        'data': [],
    };

    $("#" + tbodyId).find("tr").each(function(){
        var tdArr = $(this).children();

        var warehouse = tdArr.eq(0).find("input[name='warehouse']").val();
        var quantity = tdArr.find('.new-quantity').eq(0).text();

        var obj = {
            'sku_id': skuId,
            'warehouse': warehouse,
            'quantity': quantity
        };

        res['data'].push(obj);
    });

    //console.log(res);

    var request = $.ajax({
        url: URL + '/transferGoods',
        method: "POST",
        data: {data: res},
        dataType: "json"
    });

    request.done(function (res) {
        if (res.sign == 1) {
            console.log('transfer success');
            closeOpeningPop(null, 1)();
            searchStockEdit();
        } else {
            alert(res.msg);
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });
}

function initWindowTable(skuId) {
    var storelocation = $('#location').val();
    var tableBodyId = skuId + '-table-body';
    var request = $.ajax({
        url: URL + '/getQuantity',
        method: "POST",
        data: {sku_id: skuId},
        dataType: "json"
    });

    request.done(function (res) {
        if (res.sign == 1) {
            var str = '';
            var i;
            for (i in res.result.data) {
                var checkedStr = (res.result.data[i].warehouse == storelocation) ? ' checked' : '';
                var mainWarehouse = (res.result.data[i].warehouse == storelocation) ? ' class="main-warehouse"' : '';

                var disabled = (res.result.data[i].warehouse == storelocation) ? ' disabled="true"' : '';
                var minusFun = (res.result.data[i].warehouse == storelocation) ? '' : ' onclick="numPlusOrMinusEdit(this, 0)"';
                var addFun = (res.result.data[i].warehouse == storelocation) ? '' : ' onclick="numPlusOrMinusEdit(this, 1)"';


                str += '<tr title="pop" ' + mainWarehouse + '>' +
                    '<td title="pop" ><input title="pop" type="radio" name="warehouse" onclick="changeChecked(this)" value="' + res.result.data[i].warehouse + '"' + checkedStr + '></td>' +
                    '<td title="pop" >' + res.result.data[i].warehouse + '</td>' +
                    '<td title="pop" ><span  title="pop" class="old-quantity">' + res.result.data[i].quantity + '</span></td>' +
                    '<td title="pop" ><span  title="pop" class="new-quantity">' + res.result.data[i].quantity + '</span></td>' +
                    '<td title="pop" >' +
                      '<div title="pop" class="popover-control input-group">' +
                        '<span title="pop" class="input-group-btn"><a class="btn btn-default nu" href="javascript:void(0)" title="pop"' + minusFun + '><span title="pop" class="glyphicon glyphicon-minus"></span></a></span><input title="pop" type="text" class="form-control control-num-box popAlterNum" name="delta-quantity" placeholder="变更数量" value="0" onblur="checkStock(this)"' + disabled + '><span title="pop" class="input-group-btn"><a title="pop" class="btn btn-default" href="javascript:void(0)"' + addFun + '><span title="pop" class="glyphicon glyphicon-plus"></span></a></span><input title="pop" type="text" class="form-control control-num-box popAlterNum" name="hidden-quantity" value="0" style="display: none;">' +
                      '</div>' +
                    '</td>' +
                    '</tr>';

            }

            $("#" + tableBodyId).append(str);
        } else {
            alert(res.msg);
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        closeOpeningPop(null,1)();
        alert( "Request failed: " + textStatus );
    });
}

function changeWindowTable(skuId,change_sku_id) {
    var storelocation = $('#location').val();
    var tableBodyId = skuId + '-table-body';
    var request = $.ajax({
        url: URL + '/getQuantity',
        method: "POST",
        data: {sku_id: change_sku_id},
        dataType: "json"
    });

    request.done(function (res) {
        if (res.sign == 1) {
            var str = '';
            var i;
            for (i in res.result.data) {
                var checkedStr = (res.result.data[i].warehouse == storelocation) ? ' checked' : '';
                var mainWarehouse = (res.result.data[i].warehouse == storelocation) ? ' class="main-warehouse"' : '';

                var disabled = (res.result.data[i].warehouse == storelocation) ? ' disabled="true"' : '';
                var minusFun = (res.result.data[i].warehouse == storelocation) ? '' : ' onclick="numPlusOrMinusEdit(this, 0)"';
                var addFun = (res.result.data[i].warehouse == storelocation) ? '' : ' onclick="numPlusOrMinusEdit(this, 1)"';


                str += '<tr title="pop" ' + mainWarehouse + '>' +
                    '<td title="pop" ><input title="pop" type="radio" name="warehouse" onclick="changeChecked(this)" value="' + res.result.data[i].warehouse + '"' + checkedStr + '></td>' +
                    '<td title="pop" >' + res.result.data[i].warehouse + '</td>' +
                    '<td title="pop" ><span  title="pop" class="old-quantity">' + res.result.data[i].quantity + '</span></td>' +
                    '<td title="pop" ><span  title="pop" class="new-quantity">' + res.result.data[i].quantity + '</span></td>' +
                    '<td title="pop" >' +
                    '<div title="pop" class="popover-control input-group">' +
                    '<span title="pop" class="input-group-btn"><a class="btn btn-default nu" href="javascript:void(0)" title="pop"' + minusFun + '><span title="pop" class="glyphicon glyphicon-minus"></span></a></span><input title="pop" type="text" class="form-control control-num-box popAlterNum" name="delta-quantity" placeholder="变更数量" value="0" onblur="checkStock(this)"' + disabled + '><span title="pop" class="input-group-btn"><a title="pop" class="btn btn-default" href="javascript:void(0)"' + addFun + '><span title="pop" class="glyphicon glyphicon-plus"></span></a></span><input title="pop" type="text" class="form-control control-num-box popAlterNum" name="hidden-quantity" value="0" style="display: none;">' +
                    '</div>' +
                    '</td>' +
                    '</tr>';

            }

            $("#" + tableBodyId).html(str);
        } else {
            alert(res.msg);
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        closeOpeningPop(null,1)();
        alert( "Request failed: " + textStatus );
    });
}

function changeChecked(e) {
    var event = window.event || e;
    var target = event.target || event.srcElement;

    $('.main-warehouse').find('input[checked]').removeAttr('checked');
    $('.main-warehouse').find('input[name = "delta-quantity"]').removeAttr('disabled');
    $('.main-warehouse').find('.glyphicon-minus').parent().attr({onclick:"numPlusOrMinusEdit(this, 0)"});
    $('.main-warehouse').find('.glyphicon-plus').parent().attr({onclick:"numPlusOrMinusEdit(this, 1)"});
    $('.main-warehouse').removeClass('main-warehouse');

    var $v = $(target);
    $v.attr({checked:true});
    $v.parent().parent().addClass('main-warehouse');
    $v.parent().parent().find('input[name = "delta-quantity"]').attr({disabled:true});
    $v.parent().parent().find('.glyphicon-minus').parent().attr({onclick:null});
    $v.parent().parent().find('.glyphicon-plus').parent().attr({onclick:null});
}