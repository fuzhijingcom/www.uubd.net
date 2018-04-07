$(document).ready(function() {
    $("#reservation").daterangepicker(null, function(start, end, label) {
        console.log(start.toISOString(), end.toISOString(), label);
    });
});

function searchTrades(){
    var orderurl=MODULE+"/Order/ordertable";
    var date = $('#reservation').val();
    var startDate=date.split(' - ')[0];
    if(startDate){
        orderurl+="?startDate="+startDate;
    }
    var endDate=date.split(' - ')[1];
    if(endDate){
        orderurl+="&endDate="+endDate;
    }
    var store=$('#store').val();
    if(store){
        orderurl+="&store="+store;
    }
    var status= selected;
    if(status){
        orderurl+="&status="+status;
    }
    var keyword=$('#keyword').val();
    if(keyword){
        orderurl+="&keyword="+keyword;
    }

    $("#orderTable").attr('src',orderurl);
}

var nowTid="";
function getOrderDetail(e){
    //判断是否已经存在订单详情
    if(!$(e).siblings().hasClass('content-row')){
        $(e).parent().parent().find('.content-row').hide();
        var tid= $(e).attr('data-tid');
        var shop= $(e).attr('data-shop');
        var refund_state=$(e).attr('data-refund');
        if(nowTid!=tid){
            nowTid=tid;
            var url=MODULE+"/Order/getOrderDetail";
            $.getJSON(url,{tid:tid}, function (data) {
                var status="";
                switch(data.status){
                    case "TRADE_NO_CREATE_PAY":
                        status="待付款";
                        break;
                    case "WAIT_BUYER_PAY":
                        status="待付款";
                        break;
                    case "WAIT_PAY_RETURN":
                        status="待付款";
                        break;
                    case "WAIT_SELLER_SEND_GOODS":
                        status="待发货";
                        break;
                    case "WAIT_BUYER_CONFIRM_GOODS":
                        status="已发货";
                        break;
                    case "TRADE_BUYER_SIGNED":
                        status="已完成";
                        break;
                    case "TRADE_CLOSED":
                        status="已关闭";
                        break;
                    case "TRADE_CLOSED_BY_USER":
                        status="已关闭";
                        break;
                    default:
                        status="";
                }

                var refund_status="";
                switch(refund_state){
                    case "NO_REFUND":
                        refund_status="";
                        break;
                    case "PARTIAL_REFUNDING":
                        refund_status="部分退款中";
                        break;
                    case "PARTIAL_REFUNDED":
                        refund_status="已部分退款";
                        break;
                    case "PARTIAL_REFUND_FAILED":
                        refund_status="部分退款失败";
                        break;
                    case "FULL_REFUNDING":
                        refund_status="全额退款中";
                        break;
                    case "FULL_REFUNDED":
                        refund_status="已全额退款";
                        break;
                    case "FULL_REFUND_FAILED":
                        refund_status="全额退款失败";
                        break;
                    case "SELLER_CLOSE":
                        refund_status="卖家结束维权";
                        break;
                    case "BUYER_CLOSE":
                        refund_status="卖家结束维权";
                        break;
                    default:
                        refund_status="";
                }

                var html="";
                var weixin_nick = null;
                var btn = null;
                // var deliveryInfo = null;
                if(data.orders){
                    // console.log(data.orders[0].sku_properties_name);
                    for (var i=0; i<data.orders.length; i++){
                        if(i==0){
                            // console.log(typeof data.receiver_address);
                            deliveryInfo = '?buyerid=' + data.buyer_id + '&username=' + data.receiver_name + '&phonenumber=' + data.receiver_mobile + '&deliverydate=' + data.created + '&ordernumber=' + tid + '&address=' + data.receiver_state.replace(/(\s*)/g,"") + data.receiver_city.replace(/(\s*)/g,"") + data.receiver_district.replace(/(\s*)/g,"") + data.receiver_address.replace(/(\s*)/g,"") + '&buyernick=' + data.buyer_nick + '&buyermessage=' + encodeURIComponent(data.buyer_message) + '&remark=' + data.trade_memo + '&goodstitle=' + encodeURIComponent(data.orders[i].title) + '&goodsproperty=' + encodeURIComponent(data.orders[i].sku_properties_name) + '&goodsnumber=' + data.orders[i].num + '&goodsprice=' + data.orders[i].price + '&totalprice=' + data.payment;
                            weixin_nick = data.buyer_nick ? "<p><a href='//koudaitong.com/v2/weixin/message/talk#list&amp;type=new&amp;fans_id="+data.weixin_user_id+"' class='new-window' target='_blank'>"+data.buyer_nick+"</a></p>" : "";
                            html+="<tr class='content-row'>"+
                                        "<td class='image-cell' width='25%'>"+
                                            // "<img src='"+data.orders[i].pic_thumb_path+"'>"+
                                            "<p class='goods-title'>"+data.orders[i].title+"</p>"+
                                            "<p><span class='goods-sku'>"+data.orders[i].sku_properties_name+"</span></p>"+
                                        "</td>"+
                                        // "<td class='price-cell' width='7%'>"+
                                        //     "<p>"+data.orders[i].price+"</p>"+
                                        //     "<p>("+data.orders[i].num+"件)</p>"+
                                        // "</td>"+
                                        "<td class='aftermarket-cell' rowspan='"+data.orders.length+"' width='8%'>"+
                                            "<p>"+shop+"</p>"+
                                        "</td>"+
                                        // "<td class='aftermarket-cell' rowspan='"+data.orders.length+"' width='150'>"+
                                        //     "<p>"+refund_status+"</p>"+
                                        // "</td>"+
                                        "<td class='customer-cell' rowspan='"+data.orders.length+"' width='13%' >"+ weixin_nick +
                                            "<p class='user-name'>"+data.receiver_name+"  "+data.receiver_mobile+"</p>"+
                                            // "<button id='popoverBtn' type='button' class='btn btn-primary popover-hide popoverBtn'"+
                                            // "title='验光数据' data-buyerid='"+data.buyer_id+"' data-tid='"+data.tid+"' data-container='body'"+
                                            // "data-toggle='popover' data-placement='bottom'"+
                                            // "data-content='加载中...'>验光数据" +
                                            // "</button>"+
                                        "</td>"+
                                        "<td class='pay-price-cell' rowspan='"+data.orders.length+"' width='8%'>"+
                                            "<div class='td-cont text-left'>"+
                                            "<div style='text-align:center;'>"+data.payment+"</div>"+
                                            "</div>"+
                                        "</td>"+
                                        "<td class='state-cell' rowspan='"+data.orders.length+"' width='8%'>"+
                                            "<div class='td-cont'>"+
                                            "<p class='js-order-state'>"+(refund_status?refund_status:status)+"</p>"+
                                            "</div>" +
                                        "</td>"+
                                        // "<td class='aftermarket-cell' rowspan='"+data.orders.length+"' width='150'>"+
                                        // "<p>"+data.receiver_name+"</p>"+
                                        // "</td>"+
                                        "<td class='time-cell' rowspan='"+data.orders.length+"' width='13%'>"+
                                            "<div class='td-cont'>"+data.created+"</div>"+
                                        "</td>"+

                                        "<td class='address-cell' rowspan='"+data.orders.length+"' width='25%'>"+
                                            "<div class='td-cont text-left'>"+
                                                "<div>"+(data.receiver_state?(data.receiver_state+"&nbsp;"):"")+ (data.receiver_city?(data.receiver_city+"&nbsp;"):"")+(data.receiver_district?(data.receiver_district+"<br>"):"")+data.receiver_address+"</div>"+
                                            "</div>"+
                                        "</td>"+
                                    "</tr>";
                                    $(e).find('.download-btn').attr({onclick:"getDelivery('"+deliveryInfo +"')"});
                                }else{
                                    html+="<tr class='content-row'>"+
                                        "<td class='image-cell' width='25%'>"+
                                            // "<img src='"+data.orders[i].pic_thumb_path+"'>"+
                                            "<p class='goods-title'>"+data.orders[i].title+"</p>"+
                                            "<p><span class='goods-sku'>"+data.orders[i].sku_properties_name+"</span></p>"+
                                        "</td>"+
                                        // "<td class='price-cell' width='150'>"+
                                        //     "<p>"+data.orders[i].price+"</p>"+
                                        //     "<p>("+data.orders[i].num+"件)</p>"+
                                        // "</td>"+
                                    "</tr>";
                                }
                        
                    }
                }
                if(data.buyer_message || data.trade_memo || data.other_remark){
                    html+="<tr class='buyer-remark-row'>"+
                        "<td colspan='7'>" + (data.buyer_message?("买家备注：" + data.buyer_message + ((data.trade_memo || data.other_remark)?"&nbsp;&nbsp;||&nbsp;&nbsp;":"")):"")+
                        (data.trade_memo?("卖家备注：" + data.trade_memo + (data.other_remark?"&nbsp;&nbsp;||&nbsp;&nbsp;":"")):"") +
                        (data.other_remark?("收款人：" + data.other_remark ):"") +
                        "</td>"+
                        "</tr>";
                }

                btn = "<div id='popoverBtn"+tid+"' type='button' class='btn btn-info func-btn popover-hide popoverBtn'"+
                    "title='验光数据' data-buyerid='"+data.buyer_id+"' data-tid='"+data.tid+"' data-container='body'"+
                    "data-toggle='popover' data-trigger='click' data-placement='bottom'"+
                    "data-content='加载中...'>验光数据" +
                    "</div>" ;
                $(e).find('.func-btn-box').prepend(btn);

                html+="<script>"+
                        "$('#popoverBtn"+tid+"').popover({html : true ,placement: 'bottom',content: 'content',});"+
                        "$('#popoverBtn"+tid+"').blur(function(){$('#popoverBtn"+tid+"').popover('hide')});"+
                        "$('#popoverBtn"+tid+"').on('shown.bs.popover', function (e) {"+
                            "var popoverid=$(this).attr('aria-describedby');"+
                            "var url=MODULE+'/Optometry/getLastOptometry';"+
                            "console.log(url);" +
                            "var info={buyer_id:$(this).attr('data-buyerid'),tid:$(this).attr('data-tid')};"+
                            "$.get(url, info, function (data) {"+
                                "var contentStr='...';"+
                                "if(data.optometrys.length>0){"+
                                    "var ldegree=data.optometrys[0].ldegree;"+
                                    "if(data.optometrys[0].ldegree>0){"+
                                        "ldegree='+'+data.optometrys[0].ldegree;"+
                                    "}"+
                                    "var lastigmatism=data.optometrys[0].lastigmatism;"+
                                    "if(data.optometrys[0].lastigmatism>0){"+
                                        "lastigmatism='+'+data.optometrys[0].lastigmatism;"+
                                    "}"+
                                    "var rdegree=data.optometrys[0].rdegree;"+
                                    "if(data.optometrys[0].rdegree>0){"+
                                        "rdegree='+'+data.optometrys[0].rdegree;"+
                                    "}"+
                                    "var rastigmatism=data.optometrys[0].rastigmatism;"+
                                    "if(data.optometrys[0].rastigmatism>0){"+
                                        "rastigmatism='+'+data.optometrys[0].rastigmatism;"+
                                    "}"+
                                    "contentStr='<table class=\"table table-striped table-bordered\">" +
                                    "<thead>" +
                                    "<tr><th></th>" +
                                    "<th>度数</th>" +
                                    "<th>散光</th>" +
                                    "<th>轴位</th>" +
                                    "<th>瞳距</th>" +
                                    "</tr>" +
                                    "</thead>" +
                                    "<tbody>" +
                                    "<tr>" +
                                    "<td>右眼</td>" +
                                    "<td>'+rdegree+'</td>" +
                                    "<td>'+rastigmatism+'</td>" +//r sanguang
                                    "<td>'+data.optometrys[0].raxial+'</td>" +//r zhouwei
                                    "<td>'+data.optometrys[0].rpd+'</td>" +//r 瞳距
                                    "</tr>" +
                                    "<tr>" +
                                    "<td>左眼</td>" +
                                    "<td>'+ldegree+'</td>" +
                                    "<td>'+lastigmatism+'</td>" +//l sanguang
                                    "<td>'+data.optometrys[0].laxial+'</td>" +//l zhouwei
                                    "<td>'+data.optometrys[0].lpd+'</td>" +//l 瞳距
                                    "</tr>" +
                                    "</tbody></table>';"+
                                "}"+
                                "$('#'+popoverid).children('div.popover-content').html(contentStr);"+
                                "$('#'+popoverid).children('div.arrow').css('left','60%');"+
                                "if(contentStr != '...')" +
                                "$('#'+popoverid).css('margin-left','-120px');"+
                            "});"+
                        "});"+
                    "</script>";


                $(e).after(html);


            });
        }
    }

    if($(e).parent().find('.content-row').css('display')=='none'){
        $(e).parent().parent().find('.content-row').hide();
        $(e).parent().find('.content-row').show();
        $(e).parent().find('.remark-row').show();
    }else{
        $(e).parent().find('.content-row').hide();
        $(e).parent().find('.remark-row').hide();
    }
}

function showAddress(e){
    $(e).popover();
}

function exportOrder(){
    var exporturl=MODULE+"/Order/exportOrder";
    var date = $('#reservation').val();
    var startDate=date.split(' - ')[0];
    if(startDate){
        exporturl+="?startDate="+startDate;
    }
    var endDate=date.split(' - ')[1];
    if(endDate){
        exporturl+="&endDate="+endDate;
    }
    var store=$('#store').val();
    if(store){
        exporturl+="&store="+store;
    }
    var status=$('#status').val();
    if(status){
        exporturl+="&status="+status;
    }
    var keyword=$('#keyword').val();
    if(keyword){
        exporturl+="&keyword="+keyword;
    }
    location.href=exporturl;
}

function exportSendOrder(){
    var exporturl=MODULE+"/Order/exportSendOrder";
    var date = $('#reservation').val();
    var startDate=date.split(' - ')[0];
    if(startDate){
        exporturl+="?startDate="+startDate;
    }
    var endDate=date.split(' - ')[1];
    if(endDate){
        exporturl+="&endDate="+endDate;
    }
    var store=$('#store').val();
    if(store){
        exporturl+="&store="+store;
    }
    var status=$('#status').val();
    if(status){
        exporturl+="&status="+status;
    }
    var keyword=$('#keyword').val();
    if(keyword){
        exporturl+="&keyword="+keyword;
    }
    location.href=exporturl;
}

function exportOrderList(){
    var exporturl=MODULE+"/Order/exportOrderList";
    var date = $('#reservation').val();
    var startDate=date.split(' - ')[0];
    if(startDate){
        exporturl+="?startDate="+startDate;
    }
    var endDate=date.split(' - ')[1];
    if(endDate){
        exporturl+="&endDate="+endDate;
    }
    var store=$('#store').val();
    if(store){
        exporturl+="&store="+store;
    }
    var status=$('#status').val();
    if(status){
        exporturl+="&status="+status;
    }
    var keyword=$('#keyword').val();
    if(keyword){
        exporturl+="&keyword="+keyword;
    }
    location.href=exporturl;
}

function exportAnalysis(){
    var exporturl=MODULE+"/Order/exportAnalysis";
    var date = $('#reservation').val();
    var startDate=date.split(' - ')[0];
    if(startDate){
        exporturl+="?startDate="+startDate;
    }
    var endDate=date.split(' - ')[1];
    if(endDate){
        exporturl+="&endDate="+endDate;
    }
    var store=$('#store').val();
    if(store){
        exporturl+="&store="+store;
    }
    var status=$('#status').val();
    if(status){
        exporturl+="&status="+status;
    }
    var keyword=$('#keyword').val();
    if(keyword){
        exporturl+="&keyword="+keyword;
    }
    
    location.href=exporturl;
}

function markOrder(tid,e,func) {
    console.log(typeof tid);
    var url=URL+"/markOrder";
    var info={tid:tid,func:func};
    $.post(url,info, function (data) {
        if(data.result > 0){
            switch(func) {
                case 0:
                    $(e).parent().parent().parent().attr('style','background:#EfEfEf;');
                    $(e).find('span').attr('class', 'glyphicon glyphicon-star-empty');
                    $(e).find('span').attr('title', '点击标记订单');
                    $(e).removeAttr('onclick');
                    $(e).attr({onclick:"markOrder('"+tid+"',this,1)"});
                    $(e).css("cursor","pointer");
                    break;
                case 1:
                    $(e).parent().parent().parent().attr('style','background:#fffaeb;');
                    $(e).find('span').attr('class', 'glyphicon glyphicon-star');
                    $(e).find('span').attr('title', '已标记');
                    $(e).removeAttr('onclick');
                    $(e).attr({onclick:"markOrder('"+tid+"',this,0)"});
                    $(e).css("cursor","pointer");
                    break;
            }

        }else{
            alert("服务器繁忙，请稍候再试！");
        }
    });

    // var event0 = window.event || event;
    // if(event0.stopPropagation)
    //     event0.stopPropagation();
    // else event0.cancelBubble=true;
}

function getDelivery(info) {
        url = MODULE + '/Order/exportDeliveryOrder' + info + '&randomnumber=' + Math.random();
        location.href = url;
}