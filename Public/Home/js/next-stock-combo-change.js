/**
 * Created by chochik on 8/4/16.
 */
//将当前combo_price赋值给弹框
function getCurrentComboPrice(tid){
    return function __getCurrentComboPrice(){
        // console.log('in current price');
        var currentComboPrice = document.getElementById(tid).innerHTML;
        var pricePop = document.getElementsByName(tid)[0];
        pricePop.value = parseFloat(currentComboPrice);
        // console.log(currentComboPrice, pricePop, pricePop.value);
    }
}


//快捷修改套餐价格方法
function submitComboPriceChange(e){
    //获取onclick函数点击元素本身
    var event = window.event || e;
    var target = event.srcElement || event.target;
    var priceInput = target.previousSibling;
    
    //获取点击行的id
    var tdId = target.title;
    var sellingId, style, price, classN;
    price = document.getElementById(tdId);
    classN = price.className;
    style = (price.previousSibling).previousSibling;
    sellingId = (style.previousSibling).previousSibling;

    if(priceInput.value==''){
        alert('变动数量不能为空');
        priceInput.focus();
        return false;
    }
    //最终发送的数据变量
    var ajaxData = {
        selling_id: sellingId.innerHTML,
        price: priceInput.value
    };
    console.log(ajaxData);

    // 发送ajax(请求）
    var url = URL+"/changeComboPrice";
    // console.log(tdid);
    $.ajax({
        type: 'post',
        url: url,
        data: ajaxData,
        success: function(res){
            // console.log(res);
            // console.log(res['selling_id'],classN);
            // console.log(res['single_price'],ajaxData['price']);
            // if(!res['error']){
            //     closeOpeningPop(null,1)();
            //     var relateTd = document.getElementsByClassName(classN);
            //     for(var i=0; i<relateTd.length; i++){
            //         relateTd[i].innerHTML = res['combo_price'];
            //         addlight(relateTd[i]);
            //     }
            // }else{
            //     alert(res['error']);
            // }
        }
    });
}



//套餐表格渲染
var tmpCId;
//加载数据渲染表格的方法
function initComboTable(stockinfo, type){
    if(stockinfo){
        var stockTable = document.getElementById('stockTbody');
        removeTableP();
        if(type == 'refresh') stockTable.innerHTML = "";
        for(var i=0; i<stockinfo.length; i++){
            // 添加每一行表单
            var TR = document.createElement('tr');
            var td1 = document.createElement('td');
            var td2 = document.createElement('td');
            var td3 = document.createElement('td');
            var td4 = document.createElement('td');
            var td5 = document.createElement('td');
            var td6 = document.createElement('td');
            td1.innerHTML = stockinfo[i]['c_info']['combo_name'];
            td2.innerHTML = stockinfo[i]['c_info']['lens_name'];
            td3.innerHTML = stockinfo[i]['c_info']['frame_classification_name'];
            td4.innerHTML = stockinfo[i]['c_info']['combo_price'];
            td4.id = 'comboPriceBtn'+stockinfo[i]['c_id'];
            td5.innerHTML = stockinfo[i]['c_info']['lens_single_price'];
            if(stockinfo[i]['c_info']['frame_min_price']!=stockinfo[i]['c_info']['frame_max_price']){
                td6.innerHTML = stockinfo[i]['c_info']['frame_min_price']+ '~' +stockinfo[i]['c_info']['frame_max_price'];
            }else{
                td6.innerHTML = stockinfo[i]['c_info']['frame_min_price'];
            }
            tmpCId = 'comboPriceBtn'+stockinfo[i]['c_id'];
            // --------------------------------------------------------------------------
            TR.id = 'tr'+i;
            TR.appendChild(td1);
            TR.appendChild(td2);
            TR.appendChild(td3);
            TR.appendChild(td4);
            TR.appendChild(td5);
            TR.appendChild(td6);

            // 给TR绑定Pop事件
            TR.setAttribute('data-placement','top');
            TR.setAttribute('data-toggle','popover');
            TR.setAttribute('data-container','body');
            TR.setAttribute('data-trigger','click');
            
            // TR.addEventListener('click', attachValue);
            if(type == 'refresh'){ // 刷新表项
                stockTable.appendChild(TR);
            }else{ // 追加表项
                stockTable.insertBefore(TR, stockTable.firstChild);
            }
            
            // 初始化套餐价格列的点击弹框事件
            var optionC = {
                content: '<label class="pop-label" title="pop">套餐价格：</label><input title="pop" type="text" class="pop-form-group form-control popAlterNum popover-control" name='+tmpCId+' onblur="checkValue(this,2)">' +
                '<a title='+tmpCId+' onclick="submitComboPriceChange(this)" class="btn btn-primary" name="pop">修改</a> ',
                html: 'true',
                viewport: { "selector": '#'+TR.id, "padding": '100px'}
            };
            
            // console.log(tmpPId);
            $(TR).popover(optionC);
            $(TR).on('shown.bs.popover', closeOpeningPop(tmpCId));
            $(TR).on('shown.bs.popover', getCurrentComboPrice(tmpCId));
            $(TR).on('shown.bs.popover', addTrLight(tmpCId));
            // console.log(i,tmpId,tmpPId,$('#'+tmpId),$('#'+tmpPId));
        }
    }else{
        console.log('welcome to stockManagement!');
    }
}

//搜索符合条件的商品信息
function searchCombo(){
    var url = URL+'/searchCombo';
    $.ajax({
        type: 'POST',
        url: url,
        success: function(res){
            // console.log(res);
            if(res.length!=0){
                initComboTable(res,'refresh');
            }else{
                addNoitemInfo();
            }
        }
    });
}

// 按镜片名和镜架分组名搜索套餐信息
function searchCertainCombo(flag){
    var lensInput = document.getElementById('lensName');
    var frameCInput = document.getElementById('goodsName');
    var lPriceInput = document.getElementById('lPrice');
    var gPriceInput = document.getElementById('gPrice');
    var comboPriceInput = document.getElementById('comboPrice');
    var comboName = document.getElementById('comboName');

    var ajaxData = {
        frame_c: frameCInput.value,
        lens_name: lensInput.value
    };
    
    // console.log(ajaxData);
    var url = URL + '/searchCertainCombo';
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            // console.log(res);
            if(res.length!=0){
                // 单价Input,显示或清除价格
                // console.log(lensInput.value);
                if(lensInput.value!=''){
                    lPriceInput.value = res[0]['c_info']['lens_single_price'];
                }else{
                    lPriceInput.value = '';
                }
                if(frameCInput.value!=''){
                    if(res[0]['c_info']['frame_min_price']==res[0]['c_info']['frame_max_price']){
                        gPriceInput.value = res[0]['c_info']['frame_min_price'];
                    }else{
                        gPriceInput.value = res[0]['c_info']['frame_min_price']+ ' ~ ' +res[0]['c_info']['frame_max_price'];
                    }
                }else{
                    gPriceInput.value = '';
                }
                // 套餐价Input,显示或清除价格
                if(lensInput.value!=''&&frameCInput.value!=''){
                    comboPriceInput.value = res[0]['c_info']['combo_price'];
                    comboName.value = res[0]['c_info']['combo_name'];
                }else{
                    comboPriceInput.value = '';
                    comboName.value = '';
                }
                
                // 重新渲染表格
                initComboTable(res, 'refresh');
            }else{
                if(flag == 1){
                    lPriceInput.value = '';
                }
                if(flag == 2){
                    gPriceInput.value = '';
                }
                comboPriceInput.value = '';
                comboName.value = '';
                addNoitemInfo();
            }
        }
    })
}

// 根据套餐名搜索商品
function searchCertainComboByName(){
    var lensInput = document.getElementById('lensName');
    var frameCInput = document.getElementById('goodsName');
    var lPriceInput = document.getElementById('lPrice');
    var gPriceInput = document.getElementById('gPrice');
    var comboPriceInput = document.getElementById('comboPrice');
    var comboName = document.getElementById('comboName');

    var ajaxData = {
        combo_name: comboName.value
    };
    
    // console.log(ajaxData);
    var url = URL + '/searchCertainCombo';
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res) {
            // console.log(res);
            if(res.length==1){
                lensInput.value = res[0]['c_info']['lens_name'];
                lPriceInput.value = res[0]['c_info']['lens_single_price'];
                frameCInput.value = res[0]['c_info']['frame_classification_name'];
                gPriceInput.value = res[0]['c_info']['frame_min_price'];
                comboPriceInput.value = res[0]['c_info']['combo_price'];
                //更新表格数据，只显示查询到的
                initComboTable(res, 'refresh');
            }else if(res.length!=0){
                lensInput.value = '';
                lPriceInput.value = '';
                frameCInput.value = '';
                gPriceInput.value = '';
                comboPriceInput.value = '';
                initComboTable(res, 'refresh');
            }else{
                lensInput.value = '';
                lPriceInput.value = '';
                frameCInput.value = '';
                gPriceInput.value = '';
                comboPriceInput.value = '';
                addNoitemInfo();
            }
        }
    })
}


// 提交新增套餐信息
function submitComboChange(){
    // console.log('in');
    var lensName = document.getElementById('lensName');
    var lPrice = document.getElementById('lPrice');
    var frameCName = document.getElementById('goodsName');
    var gPrice = document.getElementById('gPrice');
    var comboPrice = document.getElementById('comboPrice');
    if(lensName.value=='') {
        alert('不能为空');
        lensName.focus();
        return false;
    }else if(frameCName.value=='') {
        alert('不能为空');
        frameCName.focus();
        return false;
    }else if(comboPrice.value==''){
        alert('不能为空');
        comboPrice.focus();
        return false;
    }
    var ajaxData = {
        lens_name: lensName.value,
        frame_c: frameCName.value,
        combo_price: comboPrice.value
    };
    //二次确认
    if(!operatorConfirm()){
        return false;
    }
    console.log(ajaxData);
    var url = URL+"/submitComboChange";
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res) {
            console.log(res);
            if (res) {
                if (res['operation_type'] == 'update') {
                    var comboPriceTd = document.getElementById('comboPriceBtn' + res['updated_c_id']);
                }
            }
        }
    })
}


// 提交新增套餐信息
function submitComboChange(){
    // console.log('in');
    var lensName = document.getElementById('lensName');
    var lPrice = document.getElementById('lPrice');
    var frameCName = document.getElementById('goodsName');
    var gPrice = document.getElementById('gPrice');
    var comboPrice = document.getElementById('comboPrice');
    if(lensName.value=='') {
        alert('不能为空');
        lensName.focus();
        return false;
    }else if(frameCName.value=='') {
        alert('不能为空');
        frameCName.focus();
        return false;
    }else if(comboPrice.value==''){
        alert('不能为空');
        comboPrice.focus();
        return false;
    }
    var ajaxData = {
        lens_name: lensName.value,
        frame_c: frameCName.value,
        combo_price: comboPrice.value
    };
    //二次确认
    if(!operatorConfirm()){
        return false;
    }
    console.log(ajaxData);
    var url = URL+"/submitComboChange";
    $.ajax({
        type: 'POST',
        url: url,
        data: ajaxData,
        success: function(res){
            console.log(res);
            if(res){
                if(res['operation_type']=='update'){
                    var comboPriceTd = document.getElementById('comboPriceBtn'+res['updated_c_id']);
                    comboPriceTd.innerHTML = res['updated_combo_price'];
                    addlight(comboPriceTd);
                }
            }
    //         if(res){
    //             style.value = '';
    //             alterNum.value = '';
    //             switch(res[0]['type']){
    //                 //新增商品或款型
    //                 case 0:
    //                     initTable(res, 'add');
    //                     var addId = 'quantity'+res[0]['s_id'];
    //                     var TD = document.getElementById(addId);
    //                     addlight(TD);
    //                     backToTop();
    //                     break;
    //                 //更新已有商品库存数量
    //                 case 1:
    //                     var updateId = 'quantity'+res[0]['s_id'];
    //                     var updatePId = 'single_price'+res[0]['s_id'];
    //                     // console.log(updateId);
    //                     var tBody = document.getElementById('stockTbody');
    //                     var updateTr = (document.getElementById(updateId)).parentNode;
    //                     tBody.removeChild(updateTr);
    //                     initTable(res, 'add');
    //
    //                     var TD = document.getElementById(updatePId);
    //                     // 判断是否有更新单品价格
    //                     if(res[0]['if_change_price']){
    //                         var alltd = document.getElementsByClassName(TD.className);
    //                         for(var i=0; i<alltd.length; i++){
    //                             addlight(alltd[i]);
    //                             alltd[i].innerHTML = res[0]['s_info']['single_price']; // 单价统一修改
    //                         }
    //                     }else{
    //                         addlight(TD);
    //                     }
    //                     backToTop();
    //                     break;
    //             }
    //         }else{
    //             alert('插入失败，请联系管理员');
    //         }
        }
    });
}


var testComboTd = [
    {
        'c_id': 0,
        'c_info':{
            'lens_name': 'test-lens',
            'frame_classification_name': 'test-classification',
            'lens_single_price': '200',
            'frame_single_price': '66',
            'combo_price': '166'
        }
    }, 
    {
        'c_id': 1,
        'c_info':{
            'lens_name': 'test-lens2',
            'frame_classification_name': 'test-classification2',
            'lens_single_price': '300',
            'frame_single_price': '166',
            'combo_price': '199'
        }
    }   
];