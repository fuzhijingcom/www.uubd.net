/**
 * Created by chochik on 8/4/16.
 */


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
function initTable(stockinfo, type, pType, position){
    // console.log(pType);
    // console.log(stockinfo);
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
            tmpId = 'quantity'+stockinfo[i]['sku_id'];
            tmpPId = 'single_price'+ stockinfo[i]['sku_id'];
            if(position != -1)
                initPop(TR,tmpId,tmpPId,stockinfo[i]['s_info']);
        }
    }else{
        console.log('welcome to stockManagement!');
    }
}

var pcome = null;
var pgo = null;
// 添加给表项初始化点击弹框事件
function initPop(TR, tmpId, tmpPId, location){
    // console.log('in Pop');
    //初始化TR的点击pop事件
    TR.setAttribute('data-placement','bottom');
    TR.setAttribute('data-toggle','popover');
    TR.setAttribute('data-container','body');
    TR.setAttribute('data-trigger','click');
    
    var ware = JSON.parse(optionWarehouse);
    var locationCome = '';
    var locationGo = '';
    // console.log(location['location']);
    for(var v in ware){
        if(ware[v]['w_name'] == location['location']){
            locationCome ='<option'+' id="comeId'+ware[v]['w_id']+'" value="'+ware[v]['w_id']+'" title="pop">'+ware[v]['w_name']+'</option>';
            locationGo +='<option disabled'+' id="goId'+ware[v]['w_id']+'" value="'+ware[v]['w_id']+'" title="pop">'+ware[v]['w_name']+'</option>';
        }else{
            // locationCome +='<option'+' id="comeId'+ware[v]['w_id']+'" value="'+ware[v]['w_id']+'" title="pop">'+ware[v]['w_name']+'</option>';
            locationGo +='<option'+' id="goId'+ware[v]['w_id']+'" value="'+ware[v]['w_id']+'" title="pop">'+ware[v]['w_name']+'</option>';
        }
        pcome = ware[1]['w_id'];
        pgo = ware[0]['w_id'];
    }
    if(location['location']=='总仓库'||location['location']=='总仓'){
        locationCome += '<option id="comeIdSpecial'+'" value="-1" title="pop">买家</option>';
        locationGo += '<option id="goIdSpecial'+'" value="-1" title="pop">买家</option>';
    }
    
    // 初始化数量列的点击弹框事件
    var numAndPriceStr = '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop" for="alterNum" >变更数量：</label><div title="pop" class="popover-control input-group" ><span title="pop" class="input-group-btn"><a class="btn btn-default nu" href="javascript:void(0)" onclick="numPlusOrMinus(0)" title="pop"><span title="pop" class="glyphicon glyphicon-minus"></span></a></span><input title="pop" type="text" class="form-control control-num-box popAlterNum" placeholder="数量" value="1" onblur="checkValue(this,1)"><span title="pop" class="input-group-btn"><a title="pop" class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(1)"><span title="pop" class="glyphicon glyphicon-plus"></span></a></span></div><div class="clear"></div></div>'+
        '<div class="pop-form-group form-group" title="pop"><label class="pop-label" title="pop">单品价格：</label><input title="pop" type="text" class="form-control popAlterNum popover-control" name='+tmpPId+' onblur="checkValue(this,2)"><div class="clear"></div></div>';
    var fromStr = '<div class="pop-form-group form-group pop-reason-box" title="pop"><label class="pop-label" title="pop">商品来源：</label><select onchange="disableBrother(0)" title="pop" class="form-control popover-control" id="pFrom">'+
        locationCome+
        '</select><div class="clear"></div></div>';
    var goStr = '<div class="pop-form-group form-group pop-reason-box" title="pop"><label class="pop-label" title="pop">商品去向：</label><select onchange="disableBrother(1)" title="pop" class="form-control popover-control" id="pGo">'+
        locationGo+
        '</select><div class="clear"></div></div>'; 
    var reasonAndBtnStr = '<div class="pop-form-group form-group pop-reason-box" title="pop"><label class="pop-label" title="pop">变更理由：</label><input id="reasonBox" title="pop" type="text" class="form-control popover-control" placeholder="请说明变更原因" onchange="popSwiftList(0)" onfocus="popSwiftList(1)"><ul title="pop" class="close-flag reason-ul"><li title="pop" onclick="fillReason()">样板调货</li></ul><div class="clear"></div></div>'+ 
        '<a title='+tmpId+' onclick="submitChangeSwift(this)" class="btn btn-primary sub-btn" name="pop">变更</a>';
    var contentStr = numAndPriceStr+fromStr+goStr+reasonAndBtnStr;
    
    var option = {
        content: contentStr,
        html: 'true'
    };

    $(TR).popover(option);
    $(TR).on('shown.bs.popover', closeOpeningPop(tmpId));
    $(TR).on('shown.bs.popover', getCurrentPrice(tmpPId));
    $(TR).on('shown.bs.popover', addTrLight(tmpId));
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
function exchangeProductStockType(n) {
    //获取onclick函数点击元素本身
    var event = window.event || e;
    event.stopPropagation(); // 停止事件冒泡
    var target = event.srcElement || event.target;
    var id = (target.parentNode.parentNode.id).split('product_stock_type')[1];
    // console.log(id);
    // console.log(target);
    var skuId = document.getElementById('sku_id'+id);
    var url = URL + '/changeStockType';
    var ajaxData = {
        sku_id: skuId.value,
        change_stock_type_to: n
    };
    // console.log(ajaxData);
    switch (n) {
        // 下架商品
        case 0:
            if (confirm('确定要下架商品吗')) {
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: ajaxData,
                    success: function (res) {
                        console.log(res);
                        if(res){
                            var updateTd = document.getElementById('product_stock_type'+res['sku_id']);
                            // updateTd.innerHTML = '';
                            updateTd.innerHTML = '<div><span class="glyphicon glyphicon-flag product-type-flag-not-on"></span> <span class="flag-text-not-on">已下架</span> <a onclick="exchangeProductStockType(1)" class="btn btn-default flag-btn" href="javascript:void(0)">上架</a></div>';
                            addlight(updateTd);
                        }else{
                            alert('下架失败，请联系技术部');
                        }
                    }
                })
            }
            break;
        // 上架商品
        case 1:
            if (confirm('确定要上架商品吗')) {
                $.ajax({
                    type: 'POST',
                    url: url,
                    data: ajaxData,
                    success: function (res) {
                        if(res){
                            var updateTd = document.getElementById('product_stock_type'+res['sku_id']);
                            updateTd.innerHTML = '<div><span class="glyphicon glyphicon-flag product-type-flag-on"></span> <span class="flag-text-on">上架中</span> <a onclick="exchangeProductStockType(0)" class="btn btn-danger flag-btn" href="javascript:void(0)">下架</a></div>';
                            addlight(updateTd);
                        }else{
                            alert('上架失败，请联系技术部');
                        }
                    }
                })
            }
            break;
    }
}

function exportStock(){
    var value = $('#stockTbody').html();
    if(value == ''){
        alert('没有数据！');
        return false;
    }
    var cond = $('#searchInput').val();
    var product_stock_type = $('#pStockType').val();
    var productType = $('#productType').attr('title');
    var w_id = $('#location').val();
    // console.log(cond);
    // console.log(product_stock_type);
    // console.log(productType);
    var url;
    if(cond!=''){
        url =CONTROLLER+'/exportStockData/cond/'+cond+'/product_stock_type/'+product_stock_type +'/productType/'+productType+'/w_id/'+w_id;

    }else{
        url =CONTROLLER+'/exportStockData/product_stock_type/'+product_stock_type+'/productType/'+productType+'/w_id/'+w_id;
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
                initPopEdit(TR,tmpIdEdit,tmpPIdEdit,stockinfo[i]['s_info']);
        }
    }else{
        console.log('welcome to stockManagement!');
    }
}

// var pcomeEdit = null;
// var pgoEdit = null;
// 添加给表项初始化点击弹框事件
function initPopEdit(TR, tmpIdEdit, tmpPIdEdit, location){
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
