/**
 * Created by chochik on 8/4/16.
 */
//搜索特定线上编号的商品
function searchSellings(){
    var sellingId = document.getElementById('sellingId');
    var singlePrice = document.getElementById('singlePrice');
    var productType = document.getElementById('productType');
    var style = document.getElementById('style');
    var select = null;
    if(style){
        select = style;
    }else{
        select = degree;
    }
    
    var ajaxData = {
        cat_id:productType.value,
        selling_id: sellingId.value,
        product_type: productType.value
    };
    // 确定回调渲染的商品类型
    var pType = getProductType(productType.value);
    
    // console.log(ajaxData);
    var url = URL+'/getsellingid';
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            console.log(res);
            if(res) {
                initAttrSelect(res,select);
                initTable(res, 'refresh', pType);
                sellingId.title = res[0]['s_info']['sku_id'];
            }else{
                addNoitemInfo();
            }
        }
    })
}

function initAttrSelect(res,select){
    // console.log('in');
    var select = select;
    // var al = ['请选择','请选择退还厂商'];
    var al = ['请选择'];
    
    for(var i in res){
        var text = null;
        // var boundName = null;
        if(res[i]['s_info']['style_full']){
            text = res[i]['s_info']['style_full'];
            // boundName = text+res[i]['s_info']['sup_name'];
            al.push(text);
            // al.push(boundName);
        }else if(res[i]['s_info']['attr_degree']) {
            text = res[i]['s_info']['attr_degree'];
            // boundName = text+res[i]['s_info']['sup_name'];
            al.push(text);
            // al.push(boundName);
        }
    }
    // console.log(al);
    al = $.unique(al);
    // console.log(al);
    select.innerHTML = '';
    // select.onchange = fillReturnBound;
    if(al.length==1){
        select.value = '';
    }else{
        for(var v=0; v<al.length; v++){
            var opt = document.createElement('option');
            opt.value = al[v];
            opt.innerHTML = al[v];
            // opt.title = al[v+1].split(al[v])[1];
            select.appendChild(opt);
        }    
    }
}

// backSup
function fillReturnBound(){
    var event = window.event || e;
    var select = event.srcElement || event.target;
    var returnBound = document.getElementById('returnBound');
    var boundName = select.selectedOptions[0].title;
    returnBound.value = boundName;
}

function intoStock() {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    var productType = document.getElementById('productType');
    var sellingId = document.getElementById('sellingId');
    var productId = document.getElementById('productId');
    var style = document.getElementById('style');
    var goodsName = document.getElementById('goodsName');
    var degree = document.getElementById('degree');
    var alterNum = document.getElementById('alterNum');
    var price = document.getElementById('price');
    var brand = document.getElementById('brand');
    var supplier = document.getElementById('supplier');
    var location = document.getElementById('location');
    var innerPrice = document.getElementById('innerPrice');
    var isReplenish = $("input[type='radio'][name='isReplenish']:checked");
    var isListing = $("input[type='radio'][name='isListing']:checked");

    // var innerPrice =
    // 防止多次点击
    var $btn = $(target).button('loading');

    // var skuId = document.getElementById('sku_id');

    var productTypeValue = productType ? productType.value : '';
    var sellingIdValue = sellingId ? sellingId.value : '';
    var productIdValue = productId ? productId.value : '';
    var styleValue = style ? style.value : '';
    var goodsNameValue = goodsName ? goodsName.value : '';
    var degreeValue = degree ? degree.value : '';
    var alterNumValue = alterNum ? alterNum.value : '';
    var priceValue = price ? price.value : '';
    var brandValue = brand ? brand.value : '';
    var supplierValue = supplier ? supplier.value : '';
    var locationValue = location ? location.value : '';
    var innerPriceValue = innerPrice ? innerPrice.value : '';
    var isReplenishValue = isReplenish ? isReplenish.val() : '是';
    var isListingValue = isListing ? isListing.val() : 1;

    if(sellingIdValue=='') {
        alert('不能为空');
        sellingId.focus();
        $btn.button('reset');
        return false;
    }
    if(alterNumValue==''){
        alert('不能为空');
        alterNum.focus();
        $btn.button('reset');
        return false;
    }
    if(productTypeValue==''){
        alert('不能为空');
        productType.focus();
        $btn.button('reset');
        return false;
    }


    var ajaxData = {
        selling_id: sellingIdValue,
        product_id: productIdValue,
        style: styleValue,
        goods_name: goodsNameValue,
        degree: degreeValue,
        delta_quantity: alterNumValue,
        price: priceValue,
        supplier: supplierValue,
        brand: brandValue,
        cat_id : productTypeValue,
        location:locationValue,
        procurement_price: innerPriceValue,
        is_listing: isListingValue,
        is_replenish: isReplenishValue
    };

    console.log(ajaxData);
    //二次确认
    if(!operatorConfirm()){
        $btn.button('reset');
        return false;
    }

    // 确定回调渲染的商品类型
    var pType = getProductType(productType.value);
    console.log(pType);

    // console.log(ajaxData);
    var url = URL+"/intoStock";
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res) {
            // console.log(res);
            if (res['sign'] != -1) {
                // console.log('in it');
                initTable(res,'refresh',pType);
                $btn.button('reset');
            }else{
                alert('操作失败，请联系管理员');
                $btn.button('reset');
            }
        },
        error: function(err){
            console.log(err);
        }
    });
    // 添加显示项目
}

function outStock() {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    var productType = document.getElementById('productType');
    var alterNum = document.getElementById('alterNum');
    var location = document.getElementById('location');
    var sellingId = document.getElementById('sellingId');
    var style = document.getElementById('style');
    var degree = document.getElementById('degree');

    // 防止多次点击
    var $btn = $(target).button('loading');

    var skuId = document.getElementById('sku_id');

    var productTypeValue = productType ? productType.value : '';
    var alterNumValue = alterNum ? alterNum.value : '';
    var locationValue = location ? location.value : '';
    var sellingIdValue = sellingId ? sellingId.value : '';
    var styleValue = style ? style.value : '';
    var degreeValue = degree ? degree.value : '';

    if(alterNumValue==''){
        alert('不能为空');
        alterNum.focus();
        $btn.button('reset');
        return false;
    }
    if(productTypeValue==''){
        alert('不能为空');
        productType.focus();
        $btn.button('reset');
        return false;
    }


    var ajaxData = {
        sku_id: skuId,
        delta_quantity: alterNumValue,
        cat_id : productTypeValue,
        location:locationValue,
        selling_id: sellingIdValue,
        style: styleValue,
        degree: degreeValue
    };

    console.log(ajaxData);
    //二次确认
    if(!operatorConfirm()){
        $btn.button('reset');
        return false;
    }

    // 确定回调渲染的商品类型
    var pType = getProductType(productType.value);

    // console.log(ajaxData);
    var url = URL+"/outStock";
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            console.log(res);
            if(res['sign']!=-1){
                initTable(res,'refresh',pType);
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
    // 添加显示项目
}

//库存变更功能---出（入）库
function submitChange(changeType){
    var event = window.event || e;
    var target = event.srcElement || event.target;
    var productType = document.getElementById('productType');
    var sellingId = document.getElementById('sellingId');
    var productId = document.getElementById('productId');
    var style = document.getElementById('style');
    var degree = document.getElementById('degree');
    var alterNum = document.getElementById('alterNum');
    // var singlePrice = document.getElementById('singlePrice');
    // var supplier = document.getElementById('supplier');
    var location = document.getElementById('location');
    var innerPrice = document.getElementById('innerPrice');
    // 防止多次点击
    var $btn = $(target).button('loading');

    var attrId = document.getElementById('attr_id');
    
    var styleValue = '';
    var degreeValue = '';
    if(style){
        styleValue = style.value;
    }else{
        degreeValue = degree.value;
    }
    if(sellingId.value=='') {
        alert('不能为空');
        sellingId.focus();
        $btn.button('reset');
        return false;
    }else if(alterNum.value==''){
        alert('不能为空');
        alterNum.focus();
        $btn.button('reset');
        return false;
    }else if(productType.value==''){
        alert('不能为空');
        productType.focus();
        $btn.button('reset');
        return false;
    }

    
    var ajaxData = {
        selling_id: sellingId.value,
        product_id: sellingId.title,
        style: styleValue,
        degree: degreeValue,
        v_num: alterNum.value,
        // single_price: singlePrice.value,
        // stock_supplier: supplier.value,
        change_type: changeType, //出库还是入库的标记 0入库，1出库
        product_type : productType.value,
        location:location.value,
        innerPrice: innerPrice?innerPrice.value:'',
        attr_id:attrId?attrId.value:''
    };

    console.log(ajaxData);
    //二次确认
    if(!operatorConfirm()){
        $btn.button('reset');
        return false;
    }

    // 确定回调渲染的商品类型
    var pType = getProductType(parseInt(productType.value));

    // console.log(ajaxData);
    var url = URL+"/changestock";
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            console.log(res);
            if(res['sign']!=-1){
                alterNum.value = '';
                switch(res[0]['type']){
                    //新增商品或款型
                    case 0:
                        initTable(res, pType, 'add');
                        var updatePId = 'single_price'+res[0]['attr_id'];
                        var TD = document.getElementById(updatePId);
                        // 判断是否有更新单品价格
                        if(res[0]['if_change_price']||res[0]['if_change_goods_id']){
                            var alltd = document.getElementsByClassName(TD.className);
                            for(var i=0; i<alltd.length; i++){
                                addlight(alltd[i]);
                                alltd[i].innerHTML = res[0]['s_info']['single_price']; // 单价统一修改
                                alltd[i].previousSibling.previousSibling.previousSibling.innerHTML = res[0]['s_info']['product_id']; // 型号统一修改
                            }
                        }else{
                            addlight(TD);
                        }
                        backToTop();
                        break;
                    //更新已有商品库存数量
                    case 1:
                        var updateId = 'quantity'+res[0]['attr_id'];
                        var updatePId = 'single_price'+res[0]['attr_id'];
                        // console.log(updateId);
                        // var tBody = document.getElementById('stockTbody');
                        // var updateTr = (document.getElementById(updateId)).parentNode;
                        // tBody.removeChild(updateTr);
                        refreshTr(res);
                        // initTable(res,'add',pType);

                        var TD = document.getElementById(updatePId);
                        // 判断是否有更新单品价格
                        if(res[0]['if_change_price']||res[0]['if_change_goods_id']){
                            var alltd = document.getElementsByClassName(TD.className);
                            for(var i=0; i<alltd.length; i++){
                                addlight(alltd[i]);
                                alltd[i].innerHTML = res[0]['s_info']['single_price']; // 单价统一修改
                                alltd[i].previousSibling.previousSibling.previousSibling.innerHTML = res[0]['s_info']['product_id']; // 型号统一修改
                            }
                        }else{
                            addlight(TD);
                        }
                        backToTop();
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
    // 添加显示项目
}

function refreshTr(res){
    var numId = 'quantity'+res[0]['attr_id'];
    var numTd = document.getElementById(numId);
    console.log(numTd);
    numTd.innerHTML = res[0]['s_info']['quantity'];
    // td添加到最前
    var tr = numTd.parentNode;
    var table = tr.parentNode;
    var trClone = tr.cloneNode(true);
    table.removeChild(tr);
    var trfirst = table.getElementsByTagName('tr')[0];
    if(trfirst){
        table.insertBefore(trClone,trfirst);
    }else{
        table.appendChild(trfirst);
    }
}
