/**
 * Created by chochik on 7/21/16.
 */
var scrollFlag = 0;
//Time init
var initDate = null;
$(document).ready(function() {
    //初始化时间
    initDate = initStartT+' - '+initEndT;
    //初始化时间筛选框
    $("#salesDate").daterangepicker(null, function(start, end, label) {
    });
    $("#salesDate").val(initDate);
    //加载库存一览控制组件并初始化库存一览筛选项
    renderContent('库存一览');
    //给元素添加关闭弹框的点击事件
    $(document).on('click',closeOpeningPop(null));
    //首次加载库存数据
    searchStock();
    // searchCombo();
    //给input初始化回车按钮事件
    enterBind();

    //滚动监听 & backToTop初始化
    $('#backToTop').click(function(){
        $('html, body').animate({scrollTop:0}, 'normal');
    });
    window.onscroll = function(){
        var h = document.documentElement.scrollTop || document.body.scrollTop;
        // console.log(h);
        // var flag = 0;
        if(h>0&&scrollFlag==0){
            fixedElement();
        }else if(h==0){
            scrollFlag = 0;
            $('#backToTop').fadeOut();
        }else if(h>100){
            $('#backToTop').fadeIn();
        }
    };
    // console.log(operatorList);
});

//固定显示功能模块
function fixedElement(){
    //左侧功能模块导航固定
    var mBox = document.getElementById('modules-box');
    var w = mBox.offsetWidth;
    // console.log(w);
    mBox.style.position = 'fixed';
    mBox.style.width = w+'px';
    mBox.style.top = 0;
    mBox.style.zIndex = 999;
    //顶部控制功能盒子固定
    var cBox = document.getElementById('controlBox-father');
    var tBody = document.getElementById('bodyTable');
    var cw = cBox.offsetWidth;
    var ch = cBox.offsetHeight;

    cBox.style.position = 'fixed';
    cBox.style.width = cw+'px';
    cBox.style.top = 0;
    cBox.style.zIndex = 999;
    tBody.style.marginTop = ch+'px';
    //标记已经触发滚动监听条件
    scrollFlag = 1;
}

// 标亮点击行，同时添加“变更按钮的”loading事件监听
function addTrLight(tid) {
    return function __addTrLight(){
        var td = document.getElementById(tid);
        var tr = td.parentNode;
        tr.className = 'tr-light';
    }
}

/**
 * 初始化 仓库名 数据填充到html
 * @param data
 */
function initWareHouse(data){
    var obj = $('#location');
    var _html = '';
    $.each(JSON.parse(data),function(index,val){
        _html += "<option value='"+val.w_id+"'>"+val.w_name+"</option>";
    });
    obj.html(obj.html()+_html);
    $("#location option[value='1']").attr("selected",true); 
}


var openingPopId = null;
//关闭其余弹框方法
function closeOpeningPop(tid,flag) {
    return function __closeOpeningPop(even){
        // console.log("tmpId :"+tid);
        // console.log("openingPopId :"+openingPopId);
        var e = (even)?even:document.getElementsByName('pop')[0];
        var swiftPop = document.getElementsByClassName('close-flag')[0];
        // console.log(swiftPop);
        if(e.target.title=='pop' || e.target.className=='popover-content'||(e.target.name=='pop'&&flag!=1)){
            // console.log('you click pop element');
            if(e.target.id!='reasonBox'){
                if(swiftPop){
                    swiftPop.style.visibility = 'hidden';
                }
            }
        }else{
            if(openingPopId!=null && openingPopId!=tid){
                console.log('in close');
                var oTd = document.getElementById(openingPopId);
                console.log(oTd);
                if(oTd){
                    var oTr = oTd.parentNode;
                    // console.log(oTr);
                    oTr.className = '';
                    $(oTr).popover('hide');
                }
            }
            // console.log(e.target.id);
            if(e.target.id != 'comments'){
                if(swiftPop){
                    swiftPop.style.visibility = 'hidden';
                }
            }
            openingPopId = tid;
            // console.log(openingPopId);
            // console.log('now OpeningId :'+openingPopId);
            // console.log(e.target.parentNode);
            if($(e.target.parentNode).data('bs.popover')){
                $(e.target.parentNode).data('bs.popover').inState.click = false;
            }
        }
    }
}

//搜索结果为空时说明搜索不到
function addNoitemInfo(){
    var tBody = document.getElementById('content');
    var tableBody = document.getElementById('stockTbody');
    var tableP = document.getElementById('tableP');
    if(!tableP){
        tableBody.innerHTML = '';
        var p = document.createElement('p');
        p.innerHTML = '无符合条件的数据';
        p.className = 'table-p';
        p.id = 'tableP';
        tBody.appendChild(p);
    }else{
        console.log('exist tableP');
    }
}
function removeTableP(){
    var tableP = document.getElementById('tableP');
    if(tableP){
        tableP.parentNode.removeChild(tableP);
    }
}

//搜索符合条件的商品信息
function searchStock(){
    var searchInput = document.getElementById('searchInput');
    var productStockType = document.getElementById('pStockType');
    var productType = document.getElementById('productType');
    var position = document.getElementById('location');
    var ajaxData = {
        cond: searchInput.value,
        product_stock_type: productStockType.value,
        cat_id: productType.title,
        position : position.value,
    };

    var url = URL + '/searchStock';
    // 确定回调渲染的商品类型
    var pType = getProductType(productType.value);
    // console.log(productType.value);
    
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            if(res){
                var tHead = document.getElementById('tableHead');
                if(tHead.title=='stock-see'){
                    initTable(res, 'refresh', pType, position.value);
                }
            }else{
                addNoitemInfo();
            }
        }
    })
}

//搜索符合条件的商品信息
function searchStockEdit(){
    var searchInput = document.getElementById('searchInput');
    var productStockType = document.getElementById('pStockType');
    var productType = document.getElementById('productType');
    var position = document.getElementById('location');
    var ajaxData = {
        cond: searchInput.value,
        product_stock_type: productStockType.value,
        cat_id: productType.title,
        position : position.value,
    };

    var url = URL + '/searchStock';
    // 确定回调渲染的商品类型
    var pType = getProductType(productType.value);
    // console.log(productType.value);
    
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            if(res){
                var tHead = document.getElementById('tableHead');
                if(tHead.title=='stock-edit'){
                    initTableEdit(res, 'refresh', pType, position.value);
                }
            }else{
                addNoitemInfo();
            }
        }
    })
}

//数量加减功能
function numPlusOrMinus(n){
    var event = window.event || e;
    var target = event.srcElement || event.target;
    var flag = (n==0||n==2||n==4)?0:1;
    // console.log(n);
    // console.log(target);
    if(target.tagName=='A'){
        var vNumBox = (n==0||n==2||n==4)?target.parentNode.nextSibling:target.parentNode.previousSibling;
    }else{
        var vNumBox = (n==1||n==3||n==5)?(target.parentNode).parentNode.previousSibling:(target.parentNode).parentNode.nextSibling;
    }
    // console.log(flag);
    // console.log(vNumBox);
    var num = vNumBox.value;
    if(num!=''){
        num = parseInt(num);
        switch(flag){
            // 减
            case 0:
                // console.log(num);
                if(num<=0&&n==2){
                    num=0;
                }else{
                    num = num-1;
                }
                vNumBox.value = num;
                // console.log(vNumBox.value);
                // if(n==0) checkValue(vNumBox,1);
                // if(n==4) checkValue(vNumBox,3);
                break;
            // 加
            case 1:
                // console.log(num);
                num = num+1;
                vNumBox.value = num;
                // console.log(vNumBox.value);
                // if(n==1) checkValue(vNumBox,1);
                // if(n==5) checkValue(vNumBox,3);
                break;
        }
    }else{
        console.log('Are you kidding me?');
        alert('请先输入数值');
    }
}

//判断输入值的正负
function checkValue(vTarget,flag){
    //control控件中的数量变更按钮
    if(vTarget){
        target = vTarget;
    }else{
        var event = window.event || e;
        var target = event.srcElement || event.target;
    }
    var v = target.value;
    // console.log('flag :'+flag);
    // console.log(optionPlus.length);
    if(isNaN(v)){
        alert('只能为数字');
        target.value="";
        target.focus();
        return false;
    }else if(v=='') {
        alert('请输入变更数量')
    }else if(flag==2) {
        v = parseInt(v);
        if (v < 0) {
            alert('必须大于0');
            target.value = "";
            target.focus();
            return false;
        }
    }else if(flag==3) {
        v = parseInt(v);
        if (v < 0) {
            alert('不能填负数！');
            target.value = "";
            target.focus();
            return false;
        }
    }else if(flag==1){
        v = parseInt(v);
        if(v < 1){
            alert('至少为1');
            target.value = 1;
            target.focus();
            return false;
        }
    }
}

//切换功能模块
function changeStockModule(e){
    var event = window.event || e;
    var target = event.target || event.srcElement;

    var activeEl = document.getElementsByClassName('active')[0];
    if(target == activeEl){
        console.log('You click the same module!');
    }else{
        // activeEl.className = activeEl.className.replace( /(?:^|\s)active(?!\S)/g , '' );
        activeEl.className = activeEl.className.replace( ' active', '');
        target.className += ' active';
        //重新渲染新模块
        renderContent(target.innerHTML);
    }
}

//重绘库存一览
function renderContent(module){
    //close all pop
    $('[data-toggle="popover"]').popover('hide');

    var controlBox = document.getElementById('controlBox');
    var tableHead = document.getElementById('tableHead');
    var tableBody = document.getElementById('stockTbody');

    // reset scroll
    var bodyBox = document.getElementById('bodyTable');
    var controlBoxFather = document.getElementById('controlBox-father');
    controlBoxFather.style.position = 'relative';
    $('body').scrollTop(0);
    bodyBox.style.margin = 0+'px';

    // empty table
    tableBody.innerHTML = '';
    switch(module){
        case '库存一览':
            controlBox.innerHTML = component.controlPart.searchBox;
            tableHead.innerHTML = component.tablePart.frameTable;
            tableHead.title = 'stock-see';
            $("#salesDate").daterangepicker(null, function(start, end, label) {
            });
            $("#salesDate").val(initDate);
            initViewSelect();
            //初始化仓库名
            initWareHouse(optionWarehouse);
            searchStock();
            enterBind();
            break;
        case '库存修改':
            controlBox.innerHTML = component.controlPart.searchEditBox;
            tableHead.innerHTML = component.tablePart.frameTable;
            tableHead.title = 'stock-edit';
            $("#salesDate").daterangepicker(null, function(start, end, label) {
            });
            $("#salesDate").val(initDate);
            initViewSelect();
            //初始化仓库名
            initWareHouse(optionWarehouse);
            searchStockEdit();
            enterBind();
            break;
        case '进货提醒':
            controlBox.innerHTML = component.controlPart.noticeBox;
            tableHead.innerHTML = component.tablePart.noticeTable;
            tableHead.title = 'stock-notice';
            initViewSelect();
            searchNotice();
            enterBind();
            break;
        case '采购入库':
            controlBox.innerHTML = component.controlPart.inBox;
            tableHead.innerHTML = component.tablePart.frameTable;
            tableHead.title = 'stock-in';
            appendStockTypeControlIn('frame'); //默认加载框架眼镜的input
            initViewSelect('inout');
            // initSupplier('in'); // 厂商select初始化 
            // initWare(); // 仓库列表select初始化
            enterBind();
            break;
        case '退货出库':
            controlBox.innerHTML = component.controlPart.outBox;
            tableHead.innerHTML = component.tablePart.frameTable;
            tableHead.title = 'stock-out';
            appendStockTypeControlOut('frame'); //默认加载框架眼镜的input
            initViewSelect('inout');
            // initSupplier(); // 厂商select初始化 
            // initWare(); // 仓库列表select初始化
            enterBind();
            break;
        case '变更记录':
            controlBox.innerHTML = component.controlPart.exportBox;
            tableHead.innerHTML = component.tablePart.exchangeTable;
            tableHead.title = 'stock-log';
            $("#reservation").daterangepicker(null, function(start, end, label) {
            });
            $("#reservation").val(initDate);
            fillOption();
            searchExchange();
            enterBind();
            // initWare('log'); // 仓库列表select初始化
            break;
        
        case '套餐变更':
            controlBox.innerHTML = component.controlPart.comboBox;
            tableHead.innerHTML = component.tablePart.comboTable;
            searchCombo();
            enterBind();
            break;
    }
}


//控件回车按钮绑定事件
function enterBind(){
    $('.first-part-input').keydown(function(event){
        if(event.keyCode == '13'){
            searchStock();
        }
    });
    $('.second-part-input').keydown(function(event){
        if(event.keyCode == '13'){
            submitChange();
        }
    });
    $('.third-part-input').keydown(function(event){
        if(event.keyCode == '13'){
            searchExchange();
        }
    });
}

//二次确认
function operatorConfirm(){
    if(confirm('确定要提交吗？')){
        console.log('press yes')
        return true;
    }else{
        console.log('press no');
        return false;
    }
}

//back to top
function backToTop(){
    var h = document.documentElement.scrollHeight || document.body.scrollTop;
    if(h>0){
        $('html, body').animate({scrollTop:0}, 'normal');
    }
}

//add light class
function addlight(td){
    console.log('in test');
    var tr = td.parentNode;
    if(tr.className){
        tr.removeAttribute('class');
    }
    setTimeout(function(){
        tr.className = 'light';
    }, 100);
}


//备注点击输入框后，弹出快捷输入列表
function popSwiftList(n){
    var even = window.event || e;
    var target = even.target || even.srcElement;
    var ul = target.nextSibling;
    console.log(n);
    if(n){
        ul.style.visibility = "visible";
    }else{
        ul.style.visibility = "hidden";
    }
}

//点击快捷输入列表，填充备注input
function fillReason(){
    var even = window.event || e;
    var target = even.target || even.srcElement;
    var swiftList = target.parentNode;
    var reasonBox = target.parentNode.previousSibling;
    reasonBox.value = target.innerHTML;
    swiftList.style.visibility = 'hidden';
}


// 填充变更记录中，操作人select的option值
function fillOption(){
    // console.log('in');
    var select = document.getElementById('operator');
    select.innerHTML = '';
    var optAll = document.createElement('option');
    optAll.value = -1;
    optAll.innerHTML = '全部';
    select.appendChild(optAll);
    for(var index in operatorList){
        // console.log(index);
        // console.log(operatorList[index]);
        var opt = document.createElement('option');
        opt.value = operatorList[index];
        opt.innerHTML = operatorList[index];
        select.appendChild(opt);
    }
}

//初始化库存一览筛选项
function initViewSelect(n){
    // console.log('in parse');
    var oc = JSON.parse(optionCategory);
    var viewSelect = $('#productType');
    var block = '&nbsp&nbsp';
    viewSelect.html('');
    // 默认为框架眼镜
    viewSelect.attr('title','K');
    var haveSon = [];
    var father_id = null;
    for(var v in oc){
        var opt = document.createElement('option');
        if(oc[v]['parent_id']!=0){
            opt.value = oc[v]['parent_id'];
        }else{
            opt.value = oc[v]['cat_id'];
        }
        if(n=='inout'){ // 采购入库和退货出库需要用到的分类组件初始化
            opt.title = oc[v]['category'];
            opt.id = 'cat_id'+oc[v]['cat_id'];
            // 将有子类的父类挑选出来
            if(oc[v]['parent_id']==father_id){
                haveSon.push(father_id);
            }
            father_id = oc[v]['cat_id'];
        }else{
            opt.title = oc[v]['cat_id'];
        }
        opt.innerHTML = block.repeat(oc[v]['level']*2)+oc[v]['category'];
        viewSelect.append(opt);
    }
    // 采购入库和退货出库，禁选父级选项
    if(n=='inout'){
        for(var v in haveSon){
            var opt = document.getElementById('cat_id'+haveSon[v]);
            opt.disabled = 'true';
        }
    }
}

// 出入库控制面板，切入
function appendStockTypeControlIn(part){
    var control = cPannelIn[part];
    var alterCtrl = document.getElementById('alterPart');
    alterCtrl.innerHTML = control;
}

function appendStockTypeControlOut(part){
    var control = cPannelOut[part];
    var alterCtrl = document.getElementById('alterPart');
    alterCtrl.innerHTML = control;
}

//初始化厂商筛选框
function initSupplier(f){
    var para = JSON.parse(optionStocksupplier);
    var supSelect = document.getElementById('supplier');
    var fc = null;
    for(var v in para){
        var opt = document.createElement('option');
        opt.value = para[v]['sup_id'];
        opt.innerHTML = para[v]['sup_name'];
        supSelect.appendChild(opt);
        if(v==0){
            fc=opt;
        }
    }
    if(f=='in'){
        var allOpt = document.createElement('option');
        allOpt.value = '0';
        allOpt.innerHTML = '无';
        allOpt.selected = true;
        supSelect.insertBefore(allOpt,fc);    
    }
    
}
//初始化仓库筛选框
function initWare(f){
    var para = JSON.parse(optionWarehouse);
    var wareSelect = document.getElementById('location');
    var fc = null;
    for(var v in para){
        var opt = document.createElement('option');
        opt.value = para[v]['w_id'];
        opt.innerHTML = para[v]['w_name'];
        wareSelect.appendChild(opt);
        if(v==0){
            fc=opt;
        }
    }
    if(f=='log') {
        var allOpt = document.createElement('option');
        allOpt.value = '0';
        allOpt.innerHTML = '全部';
        allOpt.selected = true;
        wareSelect.insertBefore(allOpt, fc);
    }
}

//返回选中的商品类型
function getProductType(n){
    var pType = null;
    switch(n){
        case 'K': // frame
            pType = frame;
            break;
        case 'T': // sunframe
            pType = sunframe;
            break;
        case 'G': // sport
            pType = sportframe;
            break;
        case 'Y': // leg
            pType = contact;
            break;
        case 'U': // contact
            pType = leg;
            break;
        case 'H': // leaner
            pType = leaner;
            break;
    }
    return pType;
}

//返回选中的商品类型
function _getProductType(n){
    var pType = null;
    switch(n){
        case 'K': // frame
            pType = 'frame';
            break;
        case 'T': // sunframe
            pType = 'sunframe';
            break;
        case 'G': // sport
            pType = 'sportframe';
            break;
        case 'Y': // contact
            pType = 'contact';
            break;
        case 'H': // leaner
            pType = 'leaner';
            break;
    }
    return pType;
}
