//加载数据渲染表格的方法
function initTable(goodsinfo, type, pType){
    // console.log(pType);
    // console.log(goodsinfo);
    if(goodsinfo){
        var stockTable = document.getElementById('stockTbody');
        removeTableP();
        var tableHead = document.getElementById('tableHead');
        // console.log(tableHead);
        tableHead.innerHTML = component.tableTitle;
        tableHead.title = 'goods-img';
        if(type == 'refresh') stockTable.innerHTML = "";
        // 根据商品类型映射关系判断生成的表格需要哪些列-----映射关系存在 goods-components.js
        var headText = pType;
        for(var i=0; i<goodsinfo.length; i++){
            var TR = document.createElement('tr');
            // 根据商品类型映射关系判断生成的表格需要哪些列-----映射关系存在 goods-components.js
            for(var j=0; j<headText.length; j++){
                var td = document.createElement('td');
                td.innerHTML = goodsinfo[i][headText[j]];
                TR.appendChild(td);
            }
            var edit_url = SITE_URL + '/index.php/Home/Goods/editimage?selling_id=' + goodsinfo[i]['selling_id'];
            var preview_url = SITE_URL + '/index.php/Home/Goods/preview?selling_id=' + goodsinfo[i]['selling_id'];
            var true_url = SITE_URL + '/index.php/Weixin/Shop/item?selling_id=' + goodsinfo[i]['selling_id'];
            var optionTd = document.createElement('td');
            optionTd.innerHTML = '<a class="btn btn-info" href="' + edit_url + '" target="_blank">编辑</a> <a class="btn btn-success" href="' + preview_url + '" target="_blank">商品购买页预览</a> <a class="btn btn-primary" href="' + true_url + '" target="_blank">实际商品购买页</a>';
            TR.appendChild(optionTd);

            if(type == 'refresh'){ // 刷新表项
                stockTable.appendChild(TR);
            }else{ // 追加表项
                stockTable.insertBefore(TR, stockTable.firstChild);
            }
        }

        // call the tablesorter plugin
        $("table").tablesorter({debug: false, widgets: ['zebra']});
    }else{
        console.log('welcome to stockManagement!');
    }
}

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