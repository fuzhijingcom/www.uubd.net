$(function(){ 
    getAllPackages();
});

//获取所有的组件
function getAllPackages(){
    url=URL+"/getPackages";
    var html="<br><strong>暂无组件！</strong>";
    $.get(url, function (data) {
        if(data.length>0){
            html="";
            $.each(data, function(i, item){
                var color=(item.p_status==1) ? "none" : "#ddd";
                html+="<ol class='breadcrumb' style='margin-bottom:5px,color:"+color+"' data-id='"+item.p_id+"' onclick='setForm("+item.p_id+")'>"+
                        "<li class='active' id='option"+item.p_id+"' style='color:"+color+"'>"+item.p_option+"</li>"+
                        "<li class='active' id='name"+item.p_id+"' style='color:"+color+"'>"+item.p_name+"</li>"+
                        "<li class='active' id='type"+item.p_id+"' style='color:"+color+"'>"+item.p_type+"</li>"+
                        "<li class='active' id='status"+item.p_id+"' value='"+item.p_status+"' style='display:none'></li>"+
                    "</ol>";
            }
            );
        }
        $("#allpackages").html(html);
    });
}

//点击组件触发事件
function setForm(id){
    $("#id").val(id);
    $("#option").val($("#option"+id).html());
    $("#name").val($("#name"+id).html());
    $("#type").val($("#type"+id).html());
    $(".select-status").val($("#status"+id).val());
}

//检查表单内容是否合法
function checkForm(){
    var option=$("#option").val();
    if(!option){
        alert("标签名称不能为空！");
        $("#option").focus();
        return false;
    }
    var name=$("#name").val();
    if(!name){
        alert("name不能为空！");
        $("#name").focus();
        return false;
    }
    return true;
}

//添加组件
function addPackage(){
    if(checkForm()){
        var url=URL+"/addPackage"; //添加组件
        var option=$("#option").val();
        var name=$("#name").val();
        var type=$("#type").val();
        var status=$(".select-status").val();
        var info={option:option,name:name,type:type,status:status};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#pform")[0].reset(); //清空表单
                getAllPackages(); //重新获取所有组件
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        });
    }
}

//保存组件
function savePackage(){
    if(checkForm()){
        var id=$("#id").val();
        var url=URL+"/updatePackage"; //更新组件
        var option=$("#option").val();
        var name=$("#name").val();
        var type=$("#type").val();
        var status=$(".select-status").val();
        var info={id:id,option:option,name:name,type:type,status:status};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#pform")[0].reset(); //清空表单
                getAllPackages(); //重新获取所有组件
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        });
    }
}

//删除组件
function deletePackage(){
    if(confirm("确认删除么？")){
        var id=$("#id").val();
        var url=URL+"/deletePackage"; //添加组件
        var info={id:id};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#pform")[0].reset(); //清空表单
                getAllPackages(); //重新获取所有组件
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        })
    }
}