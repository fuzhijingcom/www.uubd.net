
//组件库
var component = {
controlPart: {
  // 库存一览
  // searchBox:'<div class="form-group"> <input type="text" class="form-control first-part-input" id="searchInput" placeholder="生产编号/商城编号"> </div> <div class="form-group"> <select class="form-control" id="pStockType" onchange="searchStock()"> <option value="-1">商品状态(全部)</option><option value="1">上架中</option> <option value="0">已下架</option> <option value="2">新商品</option> </select> <a class="btn btn-default" onclick="searchStock()">搜索</a> <button type="button" class="btn btn-primary btn-inbound-guide"  onclick="exportStock()">导出库存表</button>                         <button type="button" class="btn btn-primary btn-inbound-guide"  onclick="exportTradeInfo()">导出销售详情表</button> <div class="sales-export-box" id="salesExport"> <div class="form-group"> <p class="checkbox-title">导出数据的时间段:</p> <div class="calendar-box calendar-select sales-date-select" style="display:inline-block;"> <fieldset> <div class="control-group"> <div class="controls"> <div class="input-prepend input-group"> <span class="add-on input-group-addon"> <i class="glyphicon glyphicon-calendar"></i> </span> <input type="text" readonly style="background: #fff; font-size:1.2rem;" name="salesDate" id="salesDate" class="form-control back-color-w" value= ""/> </div> </div> </div> </fieldset> </div> <div class="classifyBox"> <p class="checkbox-title">商品类型 <label><input type="checkbox" class="s-check-all" onchange="selectAll()"> 全部</label></p> <div class="checkbox"> <label><input type="checkbox" class="s-check" name="classify"> 框架眼镜</label> <label><input type="checkbox" class="s-check" name="classify"> 太阳眼镜</label> <label><input type="checkbox" class="s-check" name="classify"> 功能眼镜</label></br> <label><input type="checkbox" class="s-check" name="classify"> 老花眼镜</label> <label><input type="checkbox" class="s-check" name="classify"> 镜片</label> <label><input type="checkbox" class="s-check" name="classify"> 其他</label> </div> </div> <div class="classifyBox"> <p class="checkbox-title">其他筛选项</p> <div class="checkbox"> <label><input type="checkbox" class="s-check" id="onlyTrade"> 只导出交易过的商品</label></br> <select class="control s-select" id="status"> <option value="0">全部</option> <option value="1">下单未付款</option> <option value="2">已付款</option> <option value="3">付款后退款</option> </select> </div> </div> <input type="hidden" id="exportAll" value="0"/> <a class="btn btn-success salesExport-btn" onclick="submitSaleExport()" href="javascript:void(0)">确认</a> <a class="btn btn-default salesExport-btn" onclick="closeSalesBox()" href="javascript:void(0)">关闭</a> </div> </div></div>',
  searchBox:'<div class="form-group">'+
                '<select name="product-type" id="productType" class="form-control" onchange="exchangeProductType()"></select>'+
            '</div>'+
      
            '<div class="form-group">' +
                '<input type="text" class="form-control first-part-input" id="searchInput" placeholder="生产编号/商城编号">' +
            '</div>' +
      
            '<div class="form-group">' +
                '<select class="form-control" id="pStockType" onchange="searchStock()">' +
                    '<option value="-1">商品状态(全部)</option>' +
                    '<option value="1">上架中</option>' +
                    '<option value="0">已下架</option>' +
                    '<option value="2">新商品</option>' +
                '</select>' +
                '<select class="form-control" id="location" onchange="searchStock()">' +
                    '<option value="-1">所在位置(汇总)</option>' +
                '</select>' +
                '<a class="btn btn-default" onclick="searchStock()">搜索</a>' +
                '<button type="button" class="btn btn-primary btn-inbound-guide"  onclick="exportStock()">导出库存表</button>' +
                '<button type="button" class="btn btn-primary btn-inbound-guide"  onclick="exportTradeInfo()">导出销售详情表</button>' +
            '</div>' +
      
            '<div class="sales-export-box" id="salesExport">' +
                '<div class="form-group">' +
                    '<p class="checkbox-title">导出数据的时间段:</p>' +
                    '<div class="calendar-box calendar-select sales-date-select" style="display:inline-block;">' +
                        '<fieldset>' +
                            '<div class="control-group">' +
                                '<div class="controls">' +
                                    '<div class="input-prepend input-group"><span class="add-on input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>' +
                                        '<input type="text" readonly style="background: #fff; font-size:1.2rem;" name="salesDate" id="salesDate" class="form-control back-color-w" value= ""/>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                        '</fieldset>' +
                    '</div>' +
                    '<div class="classifyBox">' +
                        '<p class="checkbox-title">商品类型 <label> <input type="checkbox" class="s-check-all" onchange="selectAll()"> 全部</label></p>' +
                        '<div class="checkbox">' +
                            '<label><input type="checkbox" class="s-check" name="classify"> 框架眼镜</label>' +
                            '<label><input type="checkbox" class="s-check" name="classify"> 太阳镜</label>' +
                            '<label><input type="checkbox" class="s-check" name="classify"> 功能眼镜</label></br>' +
                            '<label><input type="checkbox" class="s-check" name="classify"> 隐形眼镜</label>' +
                            '<label><input type="checkbox" class="s-check" name="classify"> 护理相关</label>' +
                            '<label><input type="checkbox" class="s-check" name="classify"> 镜片</label>' +
                            '<label><input type="checkbox" class="s-check" name="classify"> 其他</label>' +
                        '</div>' +
                    '</div>' +
                    '<div class="classifyBox">' +
                        '<p class="checkbox-title">其他筛选项</p>' +
                        '<div class="checkbox">' +
                            '<label><input type="checkbox" class="s-check" id="onlyTrade"> 只导出交易过的商品</label></br>' +
                            '<select class="control s-select" id="status">' +
                                '<option value="0">全部</option>' +
                                '<option value="1">下单未付款</option>' +
                                '<option value="2">已付款</option>' +
                                '<option value="3">付款后退款</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<input type="hidden" id="exportAll" value="0"/>' +
                    '<a class="btn btn-success salesExport-btn" onclick="submitSaleExport()" href="javascript:void(0)">确认</a>' +
                    '<a class="btn btn-default salesExport-btn" onclick="closeSalesBox()" href="javascript:void(0)">关闭</a>' +
                '</div>' +
            '</div>',
    
  // 库存修改
    searchEditBox:'<div class="form-group">'+
                '<select name="product-type" id="productType" class="form-control" onchange="exchangeProductType()"></select>'+
            '</div>'+
      
            '<div class="form-group">' +
                '<input type="text" class="form-control first-part-input" id="searchInput" placeholder="生产编号/商城编号">' +
            '</div>' +
      
            '<div class="form-group">' +
                '<select class="form-control" id="pStockType" onchange="searchStockEdit()">' +
                    '<option value="-1">商品状态(全部)</option>' +
                    '<option value="1">上架中</option>' +
                    '<option value="0">已下架</option>' +
                    '<option value="2">新商品</option>' +
                '</select>' +
                '<select class="form-control" id="location" onchange="searchStockEdit()">' +
                    '<option value="-1">所在位置(汇总)</option>' +
                '</select>' +
                '<a class="btn btn-default" onclick="searchStockEdit()">搜索</a>' +
            '</div>', 
  // 进货提醒
  // noticeBox: '<div class="form-group"> <input type="text" class="form-control first-part-input" id="searchInput" placeholder="生产编号/商城编号"> </div> <div class="form-group"> <select class="form-control" id="inboundPeriod" onchange="searchNotice()"> <option value="3">采购耗时（3天）</option><option value="5">采购耗时（5天）</option> <option value="7">采购耗时（7天）</option></select> <a class="btn btn-default" onclick="searchNotice()">搜索</a> <button type="button" class="btn btn-primary btn-inbound-guide" data-toggle="modal" data-target=".inbound-guide-box">采购建议</button></div>',
  noticeBox:'<div class="form-group">'+
                '<select name="notice-type" id="productType" class="form-control" onchange="exchangeProductType()"></select>'+
            '</div>'+
            '<div class="form-group">' +
                '<input type="text" class="form-control first-part-input" id="searchInput" placeholder="生产编号/商城编号">' +
            '</div>' +
            '<div class="form-group">' +
                '<select class="form-control" id="inboundPeriod" onchange="searchNotice()">' +
                    '<option value="3">采购耗时（3天）</option>' +
                    '<option value="5">采购耗时（5天）</option>' +
                    '<option value="7">采购耗时（7天）</option>' +
                '</select>' +
                '<a class="btn btn-default" onclick="searchNotice()">搜索</a>' +
                '<button type="button" class="btn btn-primary btn-inbound-guide" data-toggle="modal" data-target=".inbound-guide-box">采购建议</button>' +
            '</div>',
            
  // 采购入库
  // alterBox: '<p class="control-text">新增入库</p> <div class="form-group"> <label for="sellingId">商城编号：</label> <input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()"> <label for="goodsId">生产编号：</label> <input type="text" class="group-control form-control text-input second-part-input" id="goodsId" placeholder="生产编号"> <label for="singlePrice">产品单价：</label> <input type="text" class="group-control form-control text-input second-part-input" id="singlePrice" placeholder="产品单价"></div> <div class="form-group"> <label for="style">产品款型：</label> <input type="text" class="group-control form-control text-input second-part-input" id="style" placeholder="款型"> <label for="alterNum">入库数量：</label><div class="group-control input-group text-input second-part-input"><span class="input-group-btn"><a class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(2)"><span class="glyphicon glyphicon-minus"></span></a></span><input type="text" class="form-control control-num-box" id="alterNum" placeholder="数量" onchange="checkValue(null,2)"><span class="input-group-btn"><a class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(3)"><span class="glyphicon glyphicon-plus"></span></a></span></div><label for="inboundChannel">进货渠道：</label> <input type="text" class="group-control form-control text-input second-part-input" id="inboundChannel" placeholder="进货渠道"></div></div><br> <div class="form-group"> <a type="submit" class="btn btn-default" onclick="submitChange()">提交</a></div>',
  inBox: '<div class="form-group">' +
                '<label for="sellingId">商品类型：</label>' +
                '<select name="product-type-in" id="productType" class="group-control form-control text-input second-part-input" onchange="exchangeTableIntoStock()"></select>'+
                '</div><br>' +
                '<div id="alterPart" class="form-group">' +
                '</div><br>' +
                '<div class="form-group">' +
                '<label for="goodsName">商品名称：</label>' +
                '<input id="goodsName" type="text" class="group-control form-control text-input second-part-input" placeholder="商品名称">' +
                '<label for="alterNum">入库数量：</label>' +
                '<input type="text" class="group-control form-control control-num-box text-input second-part-input" id="alterNum" placeholder="数量" onchange="checkValue(null,2)">' +
                '<label for="price">销售价格：</label>' +
                '<input type="text" class="group-control form-control control-num-box text-input second-part-input" id="price" placeholder="销售价格">' +
                '<label for="innerPrice">采购价格：</label>' +
                '<input type="text" class="group-control form-control control-num-box text-input second-part-input" id="innerPrice" placeholder="采购价格">' +
                '<input type="hidden" id="sku_id" value="">'+
                '</div><br>' +
                '<div class="form-group">' +
                '<label for="location">入库位置：</label>' +
                '<select name="location" id="location" class="group-control form-control text-input second-part-input"><option value="1">总仓</option></select>' +
                '<label for="brand">商品品牌：</label>' +
                '<input type="text" class="group-control form-control control-num-box text-input second-part-input" id="brand" placeholder="商品品牌">' +
                '<label for="supplier">供货商：</label>' +
                '<input type="text" class="group-control form-control control-num-box text-input second-part-input" id="supplier" placeholder="供货商">' +
                '<label for="isReplenish">能否补货：</label>' +
'<label class="form-check-inline"><input class="form-check-input" type="radio" name="isReplenish" id="isReplenish1" value="是" checked>能</label>' +
'<label class="form-check-inline"><input class="form-check-input" type="radio" name="isReplenish" id="isReplenish2" value="否">否</label>' +
               '&nbsp;&nbsp;&nbsp;&nbsp;<label for="isListing">上下架状态：</label>' +
               '<label class="form-check-inline"><input class="form-check-input" type="radio" name="isListing" id="isListing1" value="1" checked>上架</label>' +
               ' <label class="form-check-inline"><input class="form-check-input" type="radio" name="isListing" id="isListing2" value="0">下架</label>' +
            '</div><br>' +
            // '<div class="form-group">' +
                // '<label for="supplier">进货渠道：</label>' +
                // '<select name="supplier" id="supplier" class="group-control form-control text-input second-part-input"></select>'+
            // '</div><br>' +
            '<div class="form-group">' +
                '<a type="submit" class="btn btn-default" onclick="intoStock()">提交</a>' +
            '</div>',

  // 退货出库
  // alterBox: '<p class="control-text">新增入库</p> <div class="form-group"> <label for="sellingId">商城编号：</label> <input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()"> <label for="goodsId">生产编号：</label> <input type="text" class="group-control form-control text-input second-part-input" id="goodsId" placeholder="生产编号"> <label for="singlePrice">产品单价：</label> <input type="text" class="group-control form-control text-input second-part-input" id="singlePrice" placeholder="产品单价"></div> <div class="form-group"> <label for="style">产品款型：</label> <input type="text" class="group-control form-control text-input second-part-input" id="style" placeholder="款型"> <label for="alterNum">入库数量：</label><div class="group-control input-group text-input second-part-input"><span class="input-group-btn"><a class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(2)"><span class="glyphicon glyphicon-minus"></span></a></span><input type="text" class="form-control control-num-box" id="alterNum" placeholder="数量" onchange="checkValue(null,2)"><span class="input-group-btn"><a class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(3)"><span class="glyphicon glyphicon-plus"></span></a></span></div><label for="inboundChannel">进货渠道：</label> <input type="text" class="group-control form-control text-input second-part-input" id="inboundChannel" placeholder="进货渠道"></div></div><br> <div class="form-group"> <a type="submit" class="btn btn-default" onclick="submitChange()">提交</a></div>',
  outBox: '<div class="form-group">' +
                '<label for="sellingId">商品类型：</label>' +
                '<select name="product-type-out" id="productType" class="group-control form-control text-input second-part-input" onchange="exchangeTableOutStock()"></select>'+
            '</div><br>' +
            '<div id="alterPart" class="form-group">' +
            '</div><br>' +
            '<div class="form-group">' +
                '<label for="alterNum">出库数量：</label>' +
                '<input type="text" class="group-control form-control control-num-box text-input second-part-input" id="alterNum" placeholder="数量" onchange="checkValue(null,3)">' +
                '<label for="location">所在仓库：</label>' +
                '<select name="location" id="location" class="group-control form-control text-input second-part-input"><option value="1">总仓</option></select>'+
            '</div><br>' +
            // '<div class="form-group">' +
                // '<label for="supplier">退还厂商：</label>' +
                // '<input disabled type="text" class="group-control form-control control-num-box text-input second-part-input" id="supplier" placeholder="退还厂商">'+
               
            // '</div><br>' +
            '<div class="form-group">' +
                '<a type="submit" class="btn btn-default" onclick="outStock()">提交</a>' +
            '</div>',
  // 变更记录
  // exportBox: '<div class=\"form-group\"> <label for=\"reservation\">选择日期：</label> <div class=\"calendar-box calendar-select\" style=\"display:inline-block;\"> <fieldset> <div class=\"control-group\"> <div class=\"controls\"> <div class=\"input-prepend input-group calendar-input-box\"> <span class=\"add-on input-group-addon\"> <i class=\"glyphicon glyphicon-calendar\"></i> </span> <input type=\"text\" readonly style=\"background: #ffffff; font-size:1.2rem;\" name=\"reservation\" id=\"reservation\" class=\"form-control back-color-w\" value="0" /> </div> </div> </div> </fieldset> </div> </div><br> <div class=\"form-group\"> <label for=\"sellingId\">商城编号：</label> <input id=\"sellingId\" type=\"text\" class=\"group-control form-control text-input third-part-input\" placeholder=\"全部\"> <label for=\"goodsId\">生产编号：</label> <input type=\"text\" class=\"group-control form-control text-input third-part-input\" id=\"goodsId\" placeholder=\"全部\"> </div><br> <div class=\"form-group\"> <label for=\"style\">产品款型：</label> <input type=\"text\" class=\"group-control form-control text-input third-part-input\" id=\"style\" placeholder=\"全部\"> <label for=\"alterNum\">变更数量：</label><div class="group-control input-group text-input third-part-input"><span class="input-group-btn"><a class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(4)"><span class="glyphicon glyphicon-minus"></span></a></span><input type="text" class="form-control control-num-box" id="alterNum" placeholder="全部" onchange="checkValue(null,3)"><span class="input-group-btn"><a class="btn btn-default" href="javascript:void(0)" onclick="numPlusOrMinus(5)"><span class="glyphicon glyphicon-plus"></span></a></span></div></div><br> <div class="form-group"> <label for="operator">操作人员：</label> <select class="group-control form-control text-input control-select third-part-input" id="operator"><option value="-1">全部</option><option value="0">xucaozhi</option></select> <label for=\"comments\">变更理由：</label><input onchange="popSwiftList(0)" onfocus="popSwiftList(1)" id="comments" type="text" class="group-control form-control text-input third-part-input" placeholder="全部"/><ul title="pop" class="close-flag reason-ul-exchange"><li title="pop" onclick="fillReason()">正常入库</li><li title="pop" onclick="fillReason()">积压退换</li></ul></div><br> <div class=\"form-group\"> <a type=\"submit\" class=\"btn btn-default\" onclick=\"searchExchange()\">查找</a> </div>',
  exportBox:'<div class=\"form-group\">' +
                '<label for=\"reservation\">选择日期：</label>' +
                '<div class=\"calendar-box calendar-select\" style=\"display:inline-block;\">' +
                    '<fieldset>' +
                        '<div class=\"control-group\">' +
                            '<div class=\"controls\">' +
                                '<div class=\"input-prepend input-group calendar-input-box\">' +
                                    '<span class=\"add-on input-group-addon\">' +
                                        '<i class=\"glyphicon glyphicon-calendar\"></i>' +
                                    '</span>' +
                                    '<input type=\"text\" readonly style=\"background: #ffffff; font-size:1.2rem;\" name=\"reservation\" id=\"reservation\" class=\"form-control back-color-w\" value="0" />' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</fieldset>' +
                '</div>' +
            '</div><br>' +
            '<div class=\"form-group\">' +
                '<label for=\"sellingId\">商城编号：</label>' +
                '<input id=\"sellingId\" type=\"text\" class=\"group-control form-control text-input third-part-input\" placeholder=\"全部\">' +
                '<label for=\"goodsId\">生产编号：</label>' +
                '<input type=\"text\" class=\"group-control form-control text-input third-part-input\" id=\"goodsId\" placeholder=\"全部\">' +
            '</div><br>' +
            '<div class="form-group">' +
            '<label for=\"style\">商品属性：</label>' +
            '<input type=\"text\" class=\"group-control form-control text-input third-part-input\" id=\"style\" placeholder=\"全部\">' +
                '<label for="operator">操作人员：</label>' +
                '<select class="group-control form-control text-input control-select third-part-input" id="operator">' +
                    '<option value="-1">全部</option>' +
                    '<option value="0">xucaozhi</option>' +
                '</select>' +
                '<label for=\"comments\">变更类型：</label>' +
                '<input onchange="popSwiftList(0)" onfocus="popSwiftList(1)" id="comments" type="text" class="group-control form-control text-input third-part-input" placeholder="全部"/>' +
                    '<ul title="pop" class="close-flag reason-ul-exchange">' +
                        '<li title="pop" onclick="fillReason()">正常销售</li>' +
                        '<li title="pop" onclick="fillReason()">样板调货</li>' +
                        '<li title="pop" onclick="fillReason()">用户错拍</li>' +
                    '</ul>' +
            '</div><br>' +
            '<div class=\"form-group\">' +
                '<a type=\"submit\" class=\"btn btn-default\" onclick=\"searchExchange()\">查找</a>' +
            '</div>',
    
  // 套餐管理
  comboBox:'<p class="control-text">新增套餐</p>' +
            '<div class="form-group">' +
                '<label for="comboName">套餐名称：</label>' +
                '<input id="comboName" type="text" class="form-control text-input little-margin" placeholder="套餐名称" onchange="searchCertainComboByName()">' +
            '</div><br>' +
            '<div class="form-group">' +
                '<label for="lensName">镜片名称：</label>' +
                '<input id="lensName" type="text" class="form-control text-input little-margin" placeholder="镜片名称" onchange="searchCertainCombo(1)" >' +
                '<input disabled type="text" class="form-control text-input price-box" id="lPrice" placeholder="镜片单价">' +
                '<label for="goodsName">镜框类别：</label>' +
                '<input type="text" class="form-control text-input little-margin" id="goodsName" placeholder="镜框类别" onchange="searchCertainCombo(2)">' +
                '<input disabled type="text" class="form-control text-input price-box" id="gPrice" placeholder="镜框单价">' +
            '</div><br>' +
            '<div class="form-group">' +
                '<label for="comboPrice">套餐价格：</label>' +
                '<input type="text" class="form-control text-input" id="comboPrice" placeholder="套餐价格">' +
            '</div><br>' +
            '<div class="form-group">' +
                '<a type="submit" class="btn btn-default" onclick="submitComboChange()">提交</a>' +
            '</div>'
  },
tablePart: {
  // 框架眼镜和太阳眼镜
    frameTable:"<tr>" +
    "<td>商城编号</td>" +
    "<td>生产编号</td>" +
    "<td>款型</td>" +
    "<td>库存数量</td>" +
    "<td>单品价格</td>" +
    "<td>状态</td>" +
    "<td>总销量</td>" +
    "<td>所在位置</td>" +
    "<td>更新时间</td>" +
    "</tr>",
    sportTable:"<tr>" +
    "<td>商城编号</td>" +
    "<td>生产编号</td>" +
    "<td>款型</td>" +
    "<td>度数</td>" +
    "<td>库存数量</td>" +
    "<td>单品价格</td>" +
    "<td>状态</td>" +
    "<td>总销量</td>" +
    "<td>所在位置</td>" +
    "<td>更新时间</td>" +
    "</tr>",
    contactTable:"<tr>" +
    "<td>商城编号</td>" +
    "<td>产品名称</td>" +
    "<td>品牌</td>" +
    "<td>使用时间</td>" +
    "<td>含水量</td>" +
    "<td>度数</td>"+
    "<td>库存数量</td>" +
    "<td>单价</td>" +
    "<td>是否上架</td>" +
    "<td>总销量</td>" +
    "<td>所在位置</td>" +
    "<td>更新时间</td>" +
    "</tr>",
    leanerTable:"<tr>" +
    "<td>商城编号</td>" +
                "<td>产品名称</td>" +
                "<td>品牌</td>" +
                "<td>商品类型</td>" +
                "<td>库存数量</td>" +
                "<td>价格</td>" +
                "<td>是否上架</td>" +
                "<td>总销量</td>" +
                "<td>所在位置</td>" +
                "<td>更新时间</td>" +
             "</tr>",
  // 提醒表
  noticeTable:'<tr>' +
                '<td>商城编号</td>' +
                '<td>产品型号</td>' +
                '<td>商品名称</td>' +
                '<td>商品属性</td>' +
                '<td>现有库存量</td>' +
                '<td>近3天销量</td>' +
                '<td>近7天销量</td>' +
                '<td>近15天销量</td>' +
                '<td>建议补货日期</td>' +
              '</tr>',
  // 变更记录表
  exchangeTable:'<tr>' +
                    '<td>商城编号</td>' +
                    '<td>商品名称</td>' +
                    '<td>生产编号</td>' +
                    '<td>商品属性</td>' +
                    '<td>改前信息</td>' +
                    '<td>改后信息</td>' +
                    '<td>变更类型</td>' +
                    '<td>变更理由</td>' +
                    '<td>操作人员</td>' +
                    '<td>更新时间</td>' +
                '</tr>',
  // 套餐表
  comboTable:'<tr>' +
                '<td>套餐名称</td>' +
                '<td>镜片</td>' +
                '<td>镜框类别</td>' +
                '<td>套餐价</td>' +
                '<td>镜片单价</td>' +
                '<td>镜框单价</td>' +
             '</tr>'
  }
};

// 出入库控制面板的输入控件
var cPannelIn = {
    // frame
    frame: '<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<input id="style" type="text" class="group-control form-control text-input second-part-input" placeholder="产品款型">' +
    '<label for="productId">生产编号：</label>' +
    '<input id="productId" type="text" class="group-control form-control text-input second-part-input" placeholder="生产编号">',
    sunframe:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<input id="style" type="text" class="group-control form-control text-input second-part-input" placeholder="产品款型">' +
    '<label for="productId">生产编号：</label>' +
    '<input id="productId" type="text" class="group-control form-control text-input second-part-input" placeholder="生产编号">',
    sportframe:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<input id="style" type="text" class="group-control form-control text-input second-part-input" placeholder="产品款型">' +
    '<label for="degree">眼镜度数：</label>' +
    '<input id="degree" type="text" class="group-control form-control text-input second-part-input" placeholder="眼镜度数">' +
    '<label for="productId">生产编号：</label>' +
    '<input id="productId" type="text" class="group-control form-control text-input second-part-input" placeholder="生产编号">',
    contact:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="degree">产品度数：</label>' +
    '<input id="degree" type="text" class="group-control form-control text-input second-part-input" placeholder="眼镜度数">',
    leg: '<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<input id="style" type="text" class="group-control form-control text-input second-part-input" placeholder="产品款型">' +
    '<label for="productId">生产编号：</label>' +
    '<input id="productId" type="text" class="group-control form-control text-input second-part-input" placeholder="生产编号">',
    leaner:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">商品属性：</label>' +
    '<input id="style" type="text" class="group-control form-control text-input second-part-input" placeholder="没有则留空">'
};


var cPannelOut = {
    // frame
    frame: '<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<select class="group-control form-control text-input second-part-input" id="style"></select>',
    sunframe:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<select class="group-control form-control text-input second-part-input" id="style"></select>',
    sportframe:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<select class="group-control form-control text-input second-part-input" id="style"></select>',
    contact:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品度数：</label>' +
    '<select class="group-control form-control text-input second-part-input" id="degree"></select>',
    leg: '<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<label for="style">产品款型：</label>' +
    '<select class="group-control form-control text-input second-part-input" id="style"></select>',
    leaner:'<label for="sellingId">商城编号：</label>' +
    '<input id="sellingId" type="text" class="group-control form-control text-input second-part-input" placeholder="商城编号" onchange="searchSellings()">' +
    '<input type="hidden" id="style">',
};


// 表格与搜索商品所需字段的映射表
var frame = ['selling_id','product_id','style_full','quantity','single_price','product_stock_type','total_trade','location','operate_time'];
var sunframe = ['selling_id','product_id','style_full','quantity','single_price','product_stock_type','total_trade','location','operate_time'];
var sportframe = ['selling_id','product_id','style_full','degree','quantity','single_price','product_stock_type','total_trade','location','operate_time'];
var contact = ['selling_id','goods_name','brand','custom','water','degree','quantity','single_price','product_stock_type','total_trade','location','operate_time'];
var leg = ['selling_id','product_id','style_full','quantity','single_price','product_stock_type','total_trade','location','operate_time'];
var leaner = ['selling_id','goods_name','brand','category','quantity','single_price','product_stock_type','total_trade','location','operate_time'];

// td特殊处理函数
function specialTd(td, th, si){
    switch(th){
        case 'selling_id':
            td.title = si['sku_id'];
            break;
        case 'single_price':
            td.className += ' kind'+ si['s_info']['selling_id'];
            break;
        case 'product_stock_type':
            switch(parseInt(si['s_info']['product_stock_type'])){
                case 2:
                    td.innerHTML = '<div><span class="glyphicon glyphicon-flag product-type-flag-new"></span> <span class="flag-text-new">新商品</span> <a onclick="exchangeProductStockType(1)" class="btn btn-default flag-btn" href="javascript:void(0)">上架</a></div>';
                    break;
                case 0:
                    td.innerHTML = '<div><span class="glyphicon glyphicon-flag product-type-flag-not-on"></span> <span class="flag-text-not-on">已下架</span> <a onclick="exchangeProductStockType(1)" class="btn btn-default flag-btn" href="javascript:void(0)">上架</a></div>';
                    break;
                case 1:
                    td.innerHTML = '<div><span class="glyphicon glyphicon-flag product-type-flag-on"></span> <span class="flag-text-on">上架中</span> <a onclick="exchangeProductStockType(0)" class="btn btn-danger flag-btn" href="javascript:void(0)">下架</a></div>';
                    break;
            }
            td.width = 200;
            break;
        case 'total_trade':
            if(si['s_info']['total_trade']==null){
                td.innerHTML = 0;
            }else{
                td.innerHTML = si['s_info']['total_trade'];
            }
            break;
        case 'quantity':
            td.className = 'quantity-light';
            break;
    }
}