/**
 * Created by chochik on 8/4/16.
 */

//加载数据渲染表格的方法
function initIOTable(IOinfo){
    var stockTable = document.getElementById('stockTbody');
    stockTable.innerHTML = "";
    removeTableP();
    for(var i=0; i<IOinfo.length; i++){
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
        var td10 = document.createElement('td');
        td1.innerHTML = IOinfo[i]['s_info']['selling_id'];
        td2.innerHTML = IOinfo[i]['s_info']['goods_name'];
        td3.innerHTML = IOinfo[i]['s_info']['product_id'];
        td4.innerHTML = IOinfo[i]['s_info']['goods_attr'];
        td5.innerHTML = IOinfo[i]['s_info']['origin_value'];
        td6.innerHTML = IOinfo[i]['s_info']['new_value'];
        td7.innerHTML = IOinfo[i]['s_info']['update_type'];
        td8.innerHTML = IOinfo[i]['s_info']['update_event'];
        td9.innerHTML = IOinfo[i]['s_info']['operate_user'];
        td10.innerHTML = IOinfo[i]['s_info']['operate_time'];
        TR.appendChild(td1);
        TR.appendChild(td2);
        TR.appendChild(td3);
        TR.appendChild(td4);
        TR.appendChild(td5);
        TR.appendChild(td6);
        TR.appendChild(td7);
        TR.appendChild(td8);
        TR.appendChild(td9);
        TR.appendChild(td10);
        // TR.addEventListener('click', attachValue);
        stockTable.appendChild(TR);
    }
}

//出入库查询
function searchExchange(){
    var sellingId = document.getElementById('sellingId');
    var style = document.getElementById('style');
    var goodsId = document.getElementById('goodsId');
    var comments = document.getElementById('comments');
    var operator = document.getElementById('operator');
    var time = (document.getElementById('reservation').value).split(' - ');

    var ajaxData = {
        selling_id: sellingId.value,
        goods_id: goodsId.value,
        style: style.value,
        select_comments: comments.value,
        operator: operator.value,
        startT: time[0],
        endT: time[1]
    };
    var url = URL+'/gethistory';
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            // console.log(res);
            if(res.length!=0){
                initIOTable(res);
            }else{
                addNoitemInfo();
            }
        }
    });
}