/**
 * Created by victor on 15-11-15.
 */
function checkAll(iframe,checkbox){
    var tbody = document.getElementById(iframe).contentWindow.document.getElementsByClassName('checkbox-tbody');
    var headCheck = document.getElementById(checkbox);

    if(!headCheck.checked){
        for(var i=0; i<tbody.length; i++){
            tbody[i].checked = false;
        }
    }else{
        for(var i=0; i<tbody.length; i++){
            tbody[i].checked = true;
        }
    }
}
function backToDormMana(){
    var dormManaIframe = parent.parent.document.getElementById('main-content');
    dormManaIframe.src = 'dormManagement.html';
}
function checkOrder(){
    var date=$("#reservation-boss").val();
    var shop=$("#shop").val();
    var statu=$("#status").val();
    var search=document.getElementById('search').value;
    var startDate=date.split(' - ')[0];
    var endDate=date.split(' - ')[1];
    var url='/new66mj/index.php/Home/Order/order?startDate='+startDate+'&endDate='+endDate+'&shop='+shop+'&statu='+statu+'&search='+search;
    location.href=url;
}
function getOrderDetail(e){
    var shop=$("#"+ e.id).attr('content');
    if(e.className.indexOf('no')>0){
        $("."+e.id).css('display','block');
        e.className='info-part1';
        var child=document.getElementById('child_product').childNodes.length;
        if(child>0){
        }else{
            var tid= e.id;
            var url='/new66mj/index.php/Home/Order/getOrderDetail?tid='+tid;
            $.getJSON(url,{tid:tid}, function (data) {
                console.log(data)
                var father=document.getElementsByClassName('o-box')[0];
                for(var i=0;i<data.orders.length;i++){
                    var div=document.createElement('div');
                    div.setAttribute('class','o-product')
                    var img=document.createElement('img');
                    img.setAttribute('src',data.orders[i].pic_path);
                    img.setAttribute('alt','');
                    img.setAttribute('class','p-pic fl');
                    var div_child=document.createElement('div');
                    div_child.setAttribute('class','p-name-style fl');
                    var a=document.createElement('a');
                    a.setAttribute('href','#');
                    a.setAttribute('class','p-name');
                    var p=document.createElement('p');
                    p.setAttribute('class','o-text');
                    p.innerText=data.orders[i].sku_properties_name;
                    a.innerText=data.orders[i].title;
                    div_child.appendChild(a);
                    div_child.appendChild(p);
                    var div_child2=document.createElement('div');
                    div_child2.setAttribute('class','num-price-box fl');
                    var p2=document.createElement('p');
                    p2.setAttribute('class','o-text');
                    p2.innerText=data.orders[i].total_fee;
                    var p3=document.createElement('p');
                    p3.setAttribute('class','o-text');
                    p3.innerText='('+data.orders[i].num+')件';
                    div_child2.appendChild(p2);
                    div_child2.appendChild(p3);
                    var div_child3=document.createElement('div');
                    div_child3.setAttribute('class','clear');
                    div.appendChild(img);
                    div.appendChild(div_child);
                    div.appendChild(div_child2);
                    div.appendChild(div_child3);
                    father.appendChild(div);
                }
                $('.o-school').text(shop);
                $('.o-person').text(data.receiver_name);
                $('.o-time').text(data.created);
                switch(data.status){
                    case 'WAIT_SELLER_SEND_GOODS':
                        $('.o-staus-box p').text('待发货');
                        break;
                    case 'WAIT_BUYER_CONFIRM_GOODS':
                        $('.o-staus-box p').text('待收货');
                        break;
                    case 'TRADE_BUYER_SIGNED':
                        $('.o-staus-box p').text('已签收');
                        break;
                    case 'TRADE_CLOSED':
                        $('.o-staus-box p').text('已退款');
                        break;
                    case 'TRADE_CLOSED_BY_USER':
                        $('.o-staus-box p').text('已关闭');
                        break;
                }
                $('.o-whole-price').text(data.payment);
            })
        }
    }else{
        $("."+e.id).css('display','none');
        e.className='info-part1 no';
    }
}
function exportOrder(){
    var date=$("#reservation-boss").val();
    var shop=$("#shop").val();
    var statu=$("#status").val();
    var search=document.getElementById('search').value;
    var startDate=date.split(' - ')[0];
    var endDate=date.split(' - ')[1];
    var url='/new66mj/index.php/Home/Order/exportOrder?startDate='+startDate+'&endDate='+endDate+'&shop='+shop+'&statu='+statu+'&search='+search;
    location.href=url;
}
function show_address(){
    $('#show_address').popover()
}
$(document).ready(function() {
    $('#reservation-boss').daterangepicker(null, function(start, end, label) {
        console.log(start.toISOString(), end.toISOString(), label);
    });
});
$(function () {
    $('[data-toggle="popover"]').popover();
});

