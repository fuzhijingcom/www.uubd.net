/**
 * Created by chochik on 7/21/16.
 */
var scrollFlag = 0;
//Time init
var initDate = null;
$(document).ready(function() {
    //初始化事件
    initDate = initStartT+' - '+initEndT;
    //首次加载推广数据模块 //2016-12-30 改名为业绩汇总
    renderContent('业绩汇总');
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
    var bodyTable = document.getElementById('bodyTable');
    var cw = cBox.offsetWidth;
    var ch = cBox.offsetHeight;

    cBox.style.position = 'fixed';
    cBox.style.width = cw+'px';
    cBox.style.top = 0;
    cBox.style.zIndex = 999;
    bodyTable.style.marginTop = ch+'px';
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

//搜索结果为空时说明搜索不到
function addNoitemInfo(){
    var content = document.getElementById('content');
    var tableBody = document.getElementById('tBody');
    var tableP = document.getElementById('tableP');
    if(!tableP){
        tableBody.innerHTML = '';
        var p = document.createElement('p');
        p.innerHTML = '无符合条件的数据';
        p.className = 'table-p';
        p.id = 'tableP';
        content.appendChild(p);
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

//切换功能模块
function changeModule(e){
    var event = window.event || e;
    var target = event.target || event.srcElement;

    var activeEl = document.getElementsByClassName('active')[0];

    $('#bodyChart').hide();
    $('#chartType').hide();
    $('#bodyTable').show();

    if(target == activeEl){
        // console.log('You click the same module!');
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
    var tableBody = document.getElementById('tbody');

    // reset scroll
    var bodyBox = document.getElementById('bodyTable');
    var controlBoxFather = document.getElementById('controlBox-father');
    controlBoxFather.style.position = 'relative';
    $('body').scrollTop(0);
    bodyBox.style.margin = 0+'px';

    // empty table
    tableBody.innerHTML = '';
    switch(module){
        case '业绩汇总':
            controlBox.innerHTML = component.controlPart.userExpend;
            tableHead.innerHTML = component.tablePart.userExpendTableStore;
            $("#dataTime").daterangepicker(null, function(start, end, label) {
            });
            $("#dataTime").val(initDate);
            initStoreType();
            searchData();
            break;
        case '服务评价':
            controlBox.innerHTML = component.controlPart.serviceResearch;
            tableHead.innerHTML = component.tablePart.serviceResearchTable;
            $("#dataTime").daterangepicker(null, function(start, end, label) {
            });
            $("#dataTime").val(initDate);
            initServerStoreType();
            break;
        case '订单统计':
            controlBox.innerHTML = component.controlPart.orderCount;
            tableHead.innerHTML = component.tablePart.orderCountTable;
            $("#dataTime").daterangepicker(null, function(start, end, label) {
            });
            //初始化默认日期（昨天）
            var mydate = new Date();
            var y = mydate.getFullYear();
            var m = mydate.getMonth()+1;
            var d = mydate.getDate()-1;
            $("#dataTime").val(y+'-'+m+'-'+d+' - '+y+'-'+m+'-'+d);

            initOrderCountType();
            searchOrderCountData();
            break;
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
    // console.log('in test');
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
    if(n){
        ul.style.visibility = "visible";
    }else{
        ul.style.visibility = "hidden";
    }
}

//点击快捷输入列表，填充备注input
function fillReason(){
    // alert('in');
    var even = window.event || e;
    var target = even.target || even.srcElement;
    var swiftList = target.parentNode;
    var reasonBox = target.parentNode.previousSibling;
    reasonBox.value = target.innerHTML;
    swiftList.style.visibility = 'hidden';
}

// 查找推广数据
function searchData(n){
    var date = ($("#dataTime").val()).split(' - ');
    var sType = document.getElementById('s_type').value;

    //如果合伙人额外的搜索条件不为假则调用另一个函数处理
    var promotion_type = $('#promotion_type').val();
    var search_txt = $('#search_txt').val();

    var tHeadType = null;
    switch (parseInt(sType)){
        case 0:
            tHeadType = tHeadExpandStore;
            break;
        case 1:
            tHeadType = tHeadExpandMarket;
            break;
        case 2:
            tHeadType = tHeadExpandMarket;
            break;
    }
    var flag = n;
    var ajaxData = {
        startT: date[0],
        endT: date[1],
        type: $('#s_type').val(),
        flag: flag
    };

    if((promotion_type!='0' && promotion_type!='' && promotion_type != null ) || (search_txt != '' && search_txt != null)){
        searchPartnerData(flag);
        return false;
    }

    var url = MODULE + "/Mark/getMarkData";
    // console.log(ajaxData);
    $.ajax({
        type: 'POST',
        data: ajaxData,
        url : url,
        dataType:'JSON',
        success: function(res){
            initTable(res,tHeadType);
            if(sType=='1'){
                $('#partner_num').html('当前列表的合伙人数量：'+(res.length==0?0:res.length-1));
            }
            // console.log(res);
        },
        error: function(err){
            // console.log(err);
        }
    })
}

// 查询服务数据
function searchServerData(n){
    var date = ($("#dataTime").val()).split(' - ');
    var flag = n;
    var ajaxData = {
        startT: date[0],
        endT: date[1],
        type: $('#s_type').val(),
        flag: flag
    };
    var url = MODULE + "/Mark/getServerData";
    console.log(ajaxData);
    $.ajax({
        type: 'POST',
        data: ajaxData,
        url : url,
        dataType: 'json',
        success: function(res){
            initServerTable(res,tHeadService);
            // console.log(res);
        },
        error: function(err){
            // console.log(err);
        }
    })
}

/**
 * 查询订单销售数据
 */
function searchOrderCountData(n){

    var date = ($("#dataTime").val()).split(' - ');
    var selling_id = $('#selling_id').val();
    var g_type = $('#g_type').val();
    var flag = n;
    var ajaxData = {
        startT: date[0],
        endT: date[1],
        s_name: $('#s_type').val(),
        flag: flag,
        selling_id:selling_id,
        g_type:g_type,
    };
    var url = MODULE + "/Mark/getOrderCountData";
    var _html = "<tr>";
    _html += "<td class='prompt' colspan='8' style='text-align:center'>正在检索数据...</td>";
    _html += "</tr>";
    $('#tbody').html(_html);
    $.ajax({
        type: 'POST',
        data: ajaxData,
        url : url,
        dataType: 'json',
        success: function(res){
            orderData = res;
            if(res.length==0){
                $('#tbody').html(_html);$('.prompt').html('未检索到数据');
                return false;
            }
            if($('#showType').val() == 0){
                initOrderTable(res,tHeadOrderCount);
            }else{
                initOrderChart(res,chartsShowType);
            }
        },
        error: function(err){
            // console.log(err);
        }
    })
}

function initTable(data,tHeadType){
    if(data){
        var tbody = document.getElementById('tbody');
        removeTableP();
        tbody.innerHTML = "";

        var tHead = tHeadType;

        for(var i=0; i<data.length; i++){
            var tr = document.createElement('tr');
            for(var j=0; j<tHead.length; j++){
                var td = document.createElement('td');
                td.innerHTML = data[i][tHead[j]];
                tr.appendChild(td)
            }
            tbody.appendChild(tr);
        }
    }else{
        console.log('welcome to stockManagement!');
    }
}

function initServerTable(data,tHeadType){
    var tbody = document.getElementById('tbody');
    removeTableP();
    tbody.innerHTML = "";

    var tHead = tHeadType;
    var tr = document.createElement('tr');


    for(var j=0; j<tHead.length; j++){
        var td = document.createElement('td');
        td.innerHTML = data[tHead[j]];
        tr.appendChild(td)
    }
    tbody.appendChild(tr);
}
//初始化订单表格
function initOrderTable(data,tHeadType){
    if(!data){
        data = orderData;
    }
    var tbody = document.getElementById('tbody');
    removeTableP();
    tbody.innerHTML = "";

    var tHead = tHeadType;
    var tr = document.createElement('tr');
    var _html = '';
    $.each(data.table,function(k,v){
        if(v.selling_id){
            _html += "<tr>";
            _html += "<td>"+v.data_time+"</td>";
            _html += "<td>"+v.goods_type+"</td>";
            _html += "<td>"+v.selling_id+"</td>";
            _html += "<td>"+v.style+"</td>";
            _html += "<td>"+v.single_price+"</td>";
            _html += "<td>"+v.sale_num+"</td>";
            _html += "<td>"+v.total_payment+"</td>";
            _html += "<td>"+v.shop+"</td>";
            _html += "<td>"+v.stock_num+"</td>";
            _html += "</tr>";
        }
    });
    $('#tbody').html('');
    $('#tbody').html(_html);
    _html = '';
    $('#bodyTable').show();
    $('#bodyChart').hide();
    $('#chartType').hide();
    $("#bodyTable").tablesorter();
    //更新 防止数据重复
    $(".tablesorter").trigger("update");

    return false;

    for(var j=0; j<tHead.length; j++){
        var td = document.createElement('td');
        td.innerHTML = data[tHead[j]];
        tr.appendChild(td)
    }
    tbody.appendChild(tr);
}
//初始化订单图表
function initOrderChart(data,type){

    if(!data){
        data = orderData;
    }

    $('#bodyTable').hide();
    $('#bodyChart').show();
    $('#chartType').show();
    //highcharts图表插件
    $('#bodyChart').highcharts({
        title: {
            text: '订单统计',
            x: -20 //center
        },
        subtitle: {
            text: 'www.66mjyj.com',
            x: -20
        },
        xAxis: {
            categories:eval(data[type].date),
        },
        yAxis: {
            title: {
                text: data[type].text
            },
            plotLines: [{
                value: 0,
                width: 1,
                color: '#808080'
            }]
        },
        tooltip: {
            valueSuffix: data[type].suffix
        },
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: eval(data[type].data),
    });
    chartsShowType = type;


}

function changeStoreType(){
    var tHead = document.getElementById('tableHead');
    var storeType = document.getElementById('s_type').value;
    tHead.innerHTML = '';
    //如果选择的是合伙人则显示额外的表单,负责删除额外的表单
    if(parseInt(storeType) == 1){
        insertConditionBox();
    }else{
        clearConditionBox();
    }
    switch(parseInt(storeType)){
        case 0:
            tHead.innerHTML = component.tablePart.userExpendTableStore;
            break;
        case 1:
            tHead.innerHTML = component.tablePart.userExpendTableMarket;
            break;
        case 2:
            tHead.innerHTML = component.tablePart.userExpendTableMarket;
            break;
    }
    searchData();
}




// 推广数据--初始化筛选门店选项是否可选,并切换统计表格的表头 //2016-12-30改名为业绩汇总
function initStoreType(){
    console.log(role);
    var sType = document.getElementById('s_type');
    switch(role){
        case 'admin':
            break;
        case 'store_manager':
            sType.disabled = true;
            changeStoreType();
            break;
        case 'marketing':
            // sType.disabled = true;
            sType.value = 2;
            changeStoreType();
            break;
        case 'operation':
            break;
    }
}

// 服务评价--初始化筛选门店选项是否可选
function initServerStoreType(){
    console.log(role);
    var sType = document.getElementById('s_type');
    var sOpt = JSON.parse(storeList);
    console.log(sOpt);
    for(var i in sOpt){
        console.log('in');
        var opt = document.createElement('option');
        opt.value = sOpt[i].s_id;
        opt.innerHTML = sOpt[i].s_name;
        sType.appendChild(opt);
    }
    if(role=='admin'){
        searchServerData();
    }else if(role=='store_manager'){
        sType.disabled = true;
        searchServerData();
    }else{
        sType.innerHTML = '';
        sType.onchange = '';
    }
}

// 订单统计--初始化筛选门店选项是否可选
function initOrderCountType(){
    var sType = document.getElementById('s_type');
    var sOpt = JSON.parse(storeList);
    for(var i in sOpt){
        var opt = document.createElement('option');
        if(sOpt[i].s_name == '总店'){
            opt.value = '总店';
            opt.innerHTML = '总仓';
        }else{
            // opt.value = sOpt[i].s_id;
            opt.value = sOpt[i].s_name;
            opt.innerHTML = sOpt[i].s_name;
        }
        sType.appendChild(opt);
    }
    if(role=='admin'){
        searchServerData();
    }else if(role=='store_manager'){
        sType.disabled = true;
        //获取订单统计数据
    }else{
        sType.innerHTML = '';
        sType.onchange = '';
    }
}


function exportPartnerData(){
    var date = ($("#dataTime").val()).split(' - ');
    var startT = date[0];
    var endT = date[1];
    location.href = URL+'/exportPartnerData.html?startT='+startT+'&endT='+endT;
}

function exportPartnerOrderData(){
    var date = ($("#dataTime").val()).split(' - ');
    var startT = date[0];
    var endT = date[1];
    location.href = URL+'/exportPartnerOrderData.html?startT='+startT+'&endT='+endT;
}

//当类型选择合伙人时加载合伙人的额外表单
function insertConditionBox(){
    var _html = '<select id="promotion_type" name="promotion_type" class="form-control type-select" >'+
                    '<option value="0" selected>全部</option>'+
                    '<option value="1">创业合伙人</option>'+
                    '<option value="2">全职合伙人</option>'+
                    '<option value="3">校园合伙人</option>'+
                    '<option value="4">初级合伙人</option>'+
                '</select>';
    _html += "<input name='search_txt' id='search_txt' class='form-control search-box'>";

    $('#condition_box').html(_html);

}

//当类型选择非合伙人时清除合伙人的额外表单
function clearConditionBox(){
    var _html = "";
    $('#condition_box').html(_html);
    $('#partner_num').html(_html);
}

function searchPartnerData(flag){
    var promotion_type = $('#promotion_type').val();
    var search_txt = $('#search_txt').val();
   $.ajax({
       url:URL+'/searchPartnerData',
       type:'post',
       data:{'promotion_type':promotion_type,'search_txt':search_txt,'flag':flag},
       dataType:'json',
       success:function(data){
           $('#partner_num').html('当前列表的合伙人数量：'+(data.length==0?0:data.length-1));
           initTable(data,tHeadExpandMarket);
       },
       error:function(data){
           console.log(data);
       }
   })

}


function getTimes(flag){
    $.ajax({
        'url':URL+'/getTime',
        'type':'get',
        'dataType':'json',
        'data':{'flag':flag},
        success:function(time){
            $('#dataTime').val(time.start_time+' - '+time.end_time);
            searchOrderCountData();
        }
    });
}