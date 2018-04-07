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
            td1.innerHTML = stockinfo[i]['s_info']['selling_id'];
            td2.innerHTML = stockinfo[i]['s_info']['product_id'];
            td3.innerHTML = stockinfo[i]['s_info']['goods_name'];
            td4.innerHTML = stockinfo[i]['s_info']['attr'];
            td5.innerHTML = stockinfo[i]['s_info']['quantity'];
            td5.className = 'quantity-light';
            td6.innerHTML = stockinfo[i]['s_info']['three_day_count'];
            td7.innerHTML = stockinfo[i]['s_info']['seven_day_count'];
            td8.innerHTML = stockinfo[i]['s_info']['half_month_count'];
            var i_d = stockinfo[i]['s_info']['inbound_day'];
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
    }else{
        console.log('welcome to stockManagement!');
    }
}

//加载数据渲染表格的方法
function initGuideTable(stockinfo){
    if(stockinfo){
        var stockTable = document.getElementById('guideTbody');
        removeTableP();
        stockTable.innerHTML = "";
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
            td1.innerHTML = stockinfo[i]['s_info']['selling_id'];
            td2.innerHTML = stockinfo[i]['s_info']['product_id'];
            td3.innerHTML = stockinfo[i]['s_info']['attr'];
            td4.innerHTML = stockinfo[i]['s_info']['quantity'];
            td5.innerHTML = stockinfo[i]['s_info']['inbound_num'];
            td6.innerHTML = stockinfo[i]['s_info']['inbound_channel'];
            td7.innerHTML = stockinfo[i]['s_info']['inbound_day'];
            var i_d = stockinfo[i]['s_info']['inbound_day'];
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
    var productType = document.getElementById('productType');
    
    var ajaxData = {
        'con': searchInput.value,
        'inbound_period': salesBox.value,
        'cat_id' : productType.title
    };
    
    var url = URL + '/searchNotice';
    console.log(ajaxData);
    $.ajax({
        method: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            // console.log(res);
            var th = document.getElementById('tableHead');
            if(th.title=='stock-notice'){
                // console.log(res['sales_data']);
                // 分离重新排序数据
                var data = separateNoticeData(res['sales_data']);
                // console.log(data['dangerData']);
                initNoticeTable(data['dangerData'],'refresh');
                initNoticeTable(data['lessDangerData'],'append');
                initNoticeTable(data['warningData'],'append');
                initNoticeTable(data['normalData'],'append');
                initNoticeTable(data['noneData'],'append');

                // console.log(res);
                if(res.length!=0){
                    res['guide_data'] = sortData(res['guide_data']);
                    initGuideTable(res['guide_data']);
                    rebuildGuideData(res['guide_data']);
                }
                // console.log(res['guide_data']);    
            }
        }
    })
}

function rebuildGuideData(originData){
    // console.log(originData);
    for(var i=0; i<originData.length; i++){
        var tmpArray = [];
        tmpArray.push(originData[i]['s_info']['selling_id']);
        tmpArray.push(originData[i]['s_info']['product_id']);
        tmpArray.push(originData[i]['s_info']['attr']);
        tmpArray.push(originData[i]['s_info']['quantity']);
        tmpArray.push(originData[i]['s_info']['inbound_num']);
        tmpArray.push(originData[i]['s_info']['inbound_channel']);
        var i_d = originData[i]['s_info']['inbound_day'];
        if(i_d==0){
             originData[i]['s_info']['inbound_day'] = '请立即补货！';
        }else{
             originData[i]['s_info']['inbound_day'] = i_d+'天后补货';
        }
        if(i==originData.length-1){
            tmpArray.push(originData[i]['s_info']['inbound_day']+'@@,');
        }else{
            tmpArray.push(originData[i]['s_info']['inbound_day']+'@@');
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
        var i_d = res[i]['s_info']['inbound_day'];
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
        var minValue = data[0]['s_info']['inbound_day'];
        var maxValue = data[0]['s_info']['inbound_day'];
        var minIndex = 0;
        var maxIndex = 0;
        for (var i = 0; i < data.length; i++) {
            // var dataValue = data[i];
            var dataValue = data[i]['s_info']['inbound_day'];
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