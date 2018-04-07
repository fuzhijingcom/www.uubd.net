//点击验光数据触发事件
var lastObjectThis = null;
function setOptometryForm(id,objectThis){
    if(lastObjectThis!=null){
        lastObjectThis.removeClass('table-box-selected');
    }
    $("#o_id").val(id);
    var tr = $(objectThis).children('table').find("tr");

    $("#rdegree").val($.trim(tr.eq(1).find("td").eq(1).text()).replace("+",""));
    $("#ldegree").val($.trim(tr.eq(2).find("td").eq(1).text()).replace("+",""));
    $("#rastigmatism").val($.trim(tr.eq(1).find("td").eq(2).text()).replace("+",""));
    $("#lastigmatism").val($.trim(tr.eq(2).find("td").eq(2).text()).replace("+",""));
    $("#raxial").val(tr.eq(1).find("td").eq(3).text());
    $("#laxial").val(tr.eq(2).find("td").eq(3).text());
    $("#rpd").val(tr.eq(1).find("td").eq(4).text());
    $("#lpd").val(tr.eq(2).find("td").eq(4).text());
    $(objectThis).addClass('table-box-selected');
    lastObjectThis = $(objectThis);
    $(".thumbnail").remove();
    var ourl=$(objectThis).attr('data-url');
    if(ourl){
        $("#oform").append("<div class='thumbnail'><img id='o_url' data-src='holder.js/100%x200' alt='100%x200' src='"+PUBLIC+ourl+"' data-holder-rendered='true' style='height: auto; width: 100%; display: block;'></div>");
    }
    $("#addBtn").attr('disabled',true);
    $("#editBtn").attr('disabled',false);
}

//添加验光数据
function addOptometry(){
    var formData = new FormData($( "#oform" )[0]);
    $.ajax({
        url: MODULE+"/Optometry/addOptometry",
        type: "POST",
        data: formData,
        async: false,
        cache: false,
        contentType: false,
        processData: false,
        success: function (backdata) {
            if(backdata.result>0){
                $("#oform")[0].reset();
                makeOneOptometry(backdata.optometry);
            }
            else{
                alert("服务器繁忙，请稍候再试！");
            }
        }
    });
}

function makeOneOptometry(object){
    var o_url="";
    if(object.o_url){
        o_url=object.o_url;
    }
    rdegree=object.rdegree;
    if (rdegree>0){
        rdegree="+"+rdegree;
    }
    rastigmatism=object.rastigmatism;
    if (rastigmatism>0){
        rastigmatism="+"+rastigmatism;
    }
    ldegree=object.ldegree;
    if (ldegree>0){
        ldegree="+"+ldegree;
    }
    lastigmatism=object.lastigmatism;
    if (lastigmatism>0){
        lastigmatism="+"+lastigmatism;
    }
    var html="<div class='table-box' id='op"+object.o_id+"' data-url='"+o_url+"' onclick='setOptometryForm("+object.o_id+",this)'>"+
                 "<div class='panel-body optometry-item'><p class='optometry-time-text'>"+object.time+"</p></div>"+
                 "<table class='table'>"+
                     "<thead><tr><th>#</th><th>度数</th><th>散光</th><th>轴位</th><th>瞳距</th></tr></thead>"+
                     "<tbody>"+
                         "<tr>"+
                             "<td>右眼</td>"+
                             "<td>"+rdegree+"</td>"+
                             "<td>"+rastigmatism+"</td>"+
                             "<td>"+object.raxial+"</td>"+
                             "<td>"+object.rpd+"</td>"+
                         "</tr>"+
                         "<tr>"+
                             "<td>左眼</td>"+
                             "<td>"+ldegree+"</td>"+
                             "<td>"+lastigmatism+"</td>"+
                             "<td>"+object.laxial+"</td>"+
                             "<td>"+object.lpd+"</td>"+
                         "</tr>"+
                     "</tbody>"+
                 "</table>"+
             "</div>";
    $("#optometryList").after(html);
}

//保存验光数据
function saveOptometry(){
    if(confirm("确认保存修改？")){
        var o_id=$("#o_id").val();
        var o_url=$("#o_url").attr("src");
        var formData = new FormData($( "#oform" )[0]);
        $.ajax({
            url: MODULE+"/Optometry/updateOptometry",
            type: "POST",
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (backdata) {
                if(backdata.result>0){
                    $("#o_id").val("");
                    $("#oform")[0].reset();
                    $("#op"+o_id).remove();
                    $(".thumbnail").remove();
                    backdata.optometry['o_url']=o_url;
                    makeOneOptometry(backdata.optometry);
                }
                else{
                    alert("服务器繁忙，请稍候再试！");
                }
            }
        });
    }
}

//删除验光数据
function deleteOptometry(){
    if(confirm("确认删除么？")){
        var o_id=$("#o_id").val();
        var url=MODULE+"/Optometry/deleteOptometry"; //删除验光数据记录
        var info={o_id:o_id};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#o_id").val("");
                $("#oform")[0].reset(); //清空表单
                $("#op"+o_id).remove();
                $(".thumbnail").remove();
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        })
    }
}