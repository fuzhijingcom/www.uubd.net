$(document).ready(function() {
    //初始化表格高度
    setIframeHeight();

    $("#reservation").daterangepicker(null, function(start, end, label) {
        console.log(start.toISOString(), end.toISOString(), label);
        var date = $('#reservation').val();
        var startDate=date.split(' - ')[0];
        var endDate=date.split(' - ')[1];
        var store=$("#store").val();
        searchIncome(startDate,endDate,store);
    });

    $("#addBtn").click(function(){
        $("#withdrawModel").modal('show');
    });

    $("#subBtn").click(function(){
        var url=MODULE+"/Income/addWithdraw";
        var cash=$("#applyNum").val();
        var info={cash:cash};
        var w_id=$("#id").val();
        if(w_id){
            info['id']=w_id;
            url=MODULE+"/Income/updateWithdraw";
        }
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#withdrawModel").modal('hide');
                $("#applyNum").val("");
                getLeftPayment();
            }
        });
    });

    $("#store").change(function(){
        var date = $('#reservation').val();
        var startDate=date.split(' - ')[0];
        var endDate=date.split(' - ')[1];
        var store=$("#store").val();
        searchIncome(startDate,endDate,store);
    });

});

//初始化表格高度
function setIframeHeight(){
    var tableIframe = document.getElementById('orderTable');
    var part1 = document.getElementById('control-row').offsetHeight;
    var part2 = document.getElementById('date-box').offsetHeight;
    var part3 = document.getElementById('table').offsetHeight;

    var wholeHeight = document.body.offsetHeight;
    tableIframe.height = wholeHeight-(part1+part2+part3);
}

function searchIncome(startDate,endDate,store){
    var info={};
    var incomeurl=MODULE+"/Income/getIncome";
    var withdrawurl=MODULE+"/Income/withdraw";
    if(startDate){
        info['startDate']=startDate;
        withdrawurl+="?startDate="+startDate;
    }
    if(endDate){
        info['endDate']=endDate;
        withdrawurl+="&endDate="+endDate;
    }
    if(store){
        info['store']=store;
        withdrawurl+="&store="+store;
    }

    //进入收入
    $.post(incomeurl,info, function (data) {
        if(data){
            $("#todayPayment").html("￥ "+data.todayPayment);
            $("#totalPayment").html("￥ "+data.totalPayment);
        }
    });

    //可提现金额
    getLeftPayment();

    //提现记录
    $("#orderTable").attr('src',withdrawurl);
}

function getLeftPayment(){
    var leftpaymenturl=MODULE+"/Income/getLeftPayment";
    $.post(leftpaymenturl, function (data) {
        if(data.pointPayment){
            $("#pointPayment").html("￥ "+data.pointPayment);
        }
        if(data.leftPayment){
            $("#leftPayment").html("￥ "+data.leftPayment);
            $("#leftPayment_model").html("可提金额：￥ "+data.leftPayment);
        }
    });
    var withdrawurl=MODULE+"/Income/withdraw";
    $("#orderTable").attr('src',withdrawurl);
}

//编辑提现记录
function editWithdraw(wid,wcash){
    var withdrawModel=$(window.parent.document).find("#withdrawModel");
    withdrawModel.find("#id").val(wid);
    withdrawModel.find("#applyNum").val(wcash);
    withdrawModel.modal('show');
}

//撤销
function upWithdraw(wid,status,obthis){
    var shtml="已完成";
    var mess="确定成功！";
    if(status=='2'){
        shtml="已撤销";
        mess="提现已被撤销！";
    }
    var upurl=MODULE+"/Income/updateWithdraw";
    var infoData={id:wid,status:status};
    $.post(upurl,infoData, function (data) {
        if(data.result>0){
            alert(mess);
            if(status=='1'){
                var leftpaymenturl=MODULE+"/Income/getLeftPayment";
                $.post(leftpaymenturl, function (data) {
                    if(data.pointPayment){
                        $("#pointPayment",parent.document).html("￥ "+data.pointPayment);
                    }
                });
                $(obthis).parent().parent().parent().parent().children('td').eq(4).html(shtml);
                $(obthis).parent().parent().remove();
            }else{
                $(obthis).parent().parent().parent().parent().parent().parent().children('td').eq(4).html(shtml);
                $(obthis).parent().parent().parent().parent().remove();
            }
            
        }else{
            alert("服务器繁忙，请稍后再试！");
        }
    });
}