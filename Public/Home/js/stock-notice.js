/**
 * Created by chochik on 8/4/16.
 */
var guideData = [];

//加载数据渲染表格的方法
function initNoticeTable(stockinfo,flag){
    if(stockinfo){
        var stockTable = document.getElementById('stockTbody');
        removeTableP();
        if(flag=='refresh')stockTable.innerHTML = "";
        for(var i=0; i<stockinfo.length; i++){
            // 添加每一行表单
            var TR = document.createElement('tr');
            var td1 = document.createElement('td');
            var td2 = document.createElement('td');
            var td3 = document.createElement('td');
            var td4 = document.createElement('td');
            var td5 = document.createElement('td');
            var td6 = document.createElement('td');
            var td7 = document.createElement('td');
            var td8 = document.createElement('td');
            var td9 = document.createElement('td');
            td1.innerHTML = stockinfo[i]['selling_id'];
            td2.innerHTML = stockinfo[i]['product_id'];
            td3.innerHTML = stockinfo[i]['goods_name'];
            td4.innerHTML = stockinfo[i]['attribute'];
            td5.innerHTML = stockinfo[i]['quantity'];
            td5.className = 'quantity-light';
            td6.innerHTML = stockinfo[i]['sold_in_3days'];
            td7.innerHTML = stockinfo[i]['sold_in_7days'];
            td8.innerHTML = stockinfo[i]['sold_in_15days'];
            var i_d = stockinfo[i]['recommend_days'];
            if(i_d==-1){
                td9.innerHTML = '暂不需要补货';
                td9.className = 'stock_none_data';
            }else if(i_d==0){
                td9.innerHTML = '请立即补货！';
                td9.className = 'stock_danger';
            }else{
                td9.innerHTML = i_d+'天后补货';
                if(i_d<=7&&i_d>3){
                    td9.className = 'stock_warning';
                }else if(i_d<=3){
                    td9.className = 'stock_danger';
                }
            }
            TR.appendChild(td1);
            TR.appendChild(td2);
            TR.appendChild(td3);
            TR.appendChild(td4);
            TR.appendChild(td5);
            TR.appendChild(td6);
            TR.appendChild(td7);
            TR.appendChild(td8);
            TR.appendChild(td9);
            
            stockTable.appendChild(TR);
        }

        $("table").tablesorter({debug: false, widgets: ['zebra']});
    }else{
        console.log('welcome to stockManagement!');
    }
}

//加载数据渲染表格的方法
function initGuideTable(stockinfo){
    // console.log(stockinfo);
    if(stockinfo){
        var stockTable = document.getElementById('guideTbody');
        removeTableP();
        stockTable.innerHTML = "";
        for(var i=0; i<stockinfo.length; i++){
            if (stockinfo[i]['recommend_days'] < 0 || stockinfo[i]['recommend_days'] > 7) {
                continue;
            }
            // 添加每一行表单
            var TR = document.createElement('tr');
            var td1 = document.createElement('td');
            var td2 = document.createElement('td');
            var td3 = document.createElement('td');
            var td4 = document.createElement('td');
            var td5 = document.createElement('td');
            var td6 = document.createElement('td');
            var td7 = document.createElement('td');
            td1.innerHTML = stockinfo[i]['selling_id'];
            td2.innerHTML = stockinfo[i]['product_id'];
            td3.innerHTML = stockinfo[i]['attribute'];
            td4.innerHTML = stockinfo[i]['quantity'];
            td5.innerHTML = stockinfo[i]['inbound_num'];
            td6.innerHTML = stockinfo[i]['supplier'];
            td7.innerHTML = stockinfo[i]['inbound_day'];
            var i_d = stockinfo[i]['recommend_days'];
            if(i_d==-1){
                td7.innerHTML = '暂不需要补货';
                td7.className = 'stock_none_data';
            }else if(i_d==0){
                td7.innerHTML = '请立即补货！';
                td7.className = 'stock_danger';
            }else{
                td7.innerHTML = i_d+'天后补货';
                if(i_d<=7&&i_d>3){
                    td7.className = 'stock_warning';
                }else if(i_d<=3){
                    td7.className = 'stock_danger';
                }
            }
            TR.appendChild(td1);
            TR.appendChild(td2);
            TR.appendChild(td3);
            TR.appendChild(td4);
            TR.appendChild(td5);
            TR.appendChild(td6);
            TR.appendChild(td7);
            
            stockTable.appendChild(TR);
        }
    }else{
        console.log('welcome to stockManagement!');
    }
}

function searchNotice(){
    var searchInput = document.getElementById('searchInput');
    var salesBox = document.getElementById('inboundPeriod');
    //var productType = document.getElementById('productType');
    var productType = $('#product-type .active')[0];
    var position = document.getElementById('location');

    var ajaxData = {
        search_id: searchInput.value,
        period: salesBox.value,
        cat_id: productType.title,
        warehouse: position.value
    };
    
    var url = URL + '/searchNotice';
    // console.log(ajaxData);
    $.ajax({
        method: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            // console.log(res);
            if (res.sign == 1) {
                var th = document.getElementById('tableHead');
                if(th.title=='stock-notice'){
                    // console.log(res['sales_data']);
                    // 分离重新排序数据
                    var data = separateNoticeData(res['result']);
                    // console.log(data['dangerData']);
                    initNoticeTable(data['dangerData'],'refresh');
                    initNoticeTable(data['lessDangerData'],'append');
                    initNoticeTable(data['warningData'],'append');
                    initNoticeTable(data['normalData'],'append');
                    initNoticeTable(data['noneData'],'append');

                    // console.log(res);
                    if(res.length!=0){
                        var sortRes = sortData(res['result']);
                        initGuideTable(sortRes);
                        rebuildGuideData(sortRes);
                    }
                    // console.log(res['guide_data']);
                }
            } else {
                addNoitemInfo();
            }
        }
    })
}

function rebuildGuideData(originData){
    // console.log(originData);
    for(var i=0; i<originData.length; i++){
        if (originData[i]['recommend_days'] < 0 || originData[i]['recommend_days'] > 7) {
            continue;
        }
        var tmpArray = [];
        tmpArray.push(originData[i]['selling_id']);
        tmpArray.push(originData[i]['product_id']);
        tmpArray.push(originData[i]['attribute']);
        tmpArray.push(originData[i]['quantity']);
        tmpArray.push(originData[i]['inbound_num']);
        tmpArray.push(originData[i]['supplier']);
        var i_d = originData[i]['recommend_days'];
        if(i_d==0){
             originData[i]['recommend_days'] = '请立即补货！';
        }else{
             originData[i]['recommend_days'] = i_d+'天后补货';
        }
        if(i==originData.length-1){
            tmpArray.push(originData[i]['recommend_days']+'@@,');
        }else{
            tmpArray.push(originData[i]['recommend_days']+'@@');
        }
        guideData.push(tmpArray);
    }
    document.getElementById('guideData').value = guideData;
    // console.log(guideData);
}

function expGuideTable(){
    var guideForm = document.getElementById('guideForm');
    var guideData = document.getElementById('guideData');
    if(guideData.value!=''){
        guideForm.submit();
    }else{
        alert('暂无采购建议');
    }
}

// 分类处理提醒数据
function separateNoticeData(res){
    var dangerData = [];
    var lessDangerData = [];
    var warningData = [];
    var noneData = [];
    var normalData = [];
    for(var i=0;i<res.length;i++){
        var i_d = res[i]['recommend_days'];
        if(i_d==0){
            dangerData.push(res[i]);
        }else if(i_d>3&&i_d<7){
            warningData.push(res[i]);
        }else if(i_d<=3&&i_d>0){
            lessDangerData.push(res[i]);
        }else if(i_d==-1){
            noneData.push(res[i]);
        }else{
            normalData.push(res[i]);
        }
    }
    // 特定分类内按从小到大排序
    lessDangerData = sortData(lessDangerData);
    warningData = sortData(warningData);
    normalData = sortData(normalData);
    var separatedData = {
        dangerData: dangerData,
        lessDangerData: lessDangerData,
        warningData: warningData,
        normalData: normalData,
        noneData: noneData
    };
    return separatedData;
}

function sortData(res){
    // var data = [1,4,77,5,6,10,1,3,4,55,2,0,9];
    var data = res;
    // console.log(res.length);
    var minAarry = [];
    var maxAarry = [];
    while (data.length>1) {
        var minValue = data[0]['recommend_days'];
        var maxValue = data[0]['recommend_days'];
        var minIndex = 0;
        var maxIndex = 0;
        for (var i = 0; i < data.length; i++) {
            // var dataValue = data[i];
            var dataValue = data[i]['recommend_days'];
            // console.log(dataValue);
            if (dataValue <= minValue) {
                minValue = dataValue;
                minIndex = i;
            }
            if (dataValue > maxValue) {
                maxValue = dataValue;
                maxIndex = i;
            }
        }
        // 添加最小数到最小数组
        minAarry.push(data[minIndex]);
        maxAarry.unshift(data[maxIndex]);
        // 删除data数组值
        if(minIndex>maxIndex){
            data.splice(minIndex,1);
            data.splice(maxIndex,1);
        }else{
            data.splice(maxIndex,1);
            data.splice(minIndex,1);
        }
    }
    if(data.length==1){
        minAarry.push(data[0]);
        data.splice(0,1);
    }
    // console.log(data,minAarry,maxAarry);
    // 链接两个数组
    var separatedData = minAarry.concat(maxAarry);
    // console.log(separatedData);
    return separatedData;
}