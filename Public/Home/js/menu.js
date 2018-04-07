$(function(){ 
    getAllMenus();
});

//获取所有菜单
function getAllMenus(){
	url=URL+"/getMenus";
    var html="<br><strong>暂无菜单！</strong>";
    $.get(url, function (data) {
        if(data.length>0){
            html="";
            $.each(data, function(i, item){
                var color=(item.m_status==1) ? "none" : "#ddd";
                html+="<li class='list-group-item' id='m"+item.m_id+"' data-name='"+item.m_name+"' data-flow='"+item.m_flow+"' data-icon='"+item.m_icon+"' data-red='"+item.m_red+"' data-status='"+item.m_status+"' data-order='"+item.m_order+"' data-note='"+item.m_note+"' onclick='setMenuForm("+item.m_id+");' style='color:"+color+"'><span class='glyphicon "+item.m_icon+" nav-icon'></span> "+item.m_name+"</li>";
            }
            );
        }
        $("#menuLists").html(html);
    });
}

//点击菜单触发事件
function setMenuForm(id){
	$("#id").val(id);
	$("#name").val($("#m"+id).attr("data-name"));
    $("#flow").val($("#m"+id).attr("data-flow"));
    $("#icon").val($("#m"+id).attr("data-icon"));
    $(".select-red").val($("#m"+id).attr("data-red"));
    $(".select-status").val($("#m"+id).attr("data-status"));
    $("#order").val($("#m"+id).attr("data-order"));
    $("#note").val($("#m"+id).attr("data-note"));
}

//检查表单内容是否合法
function checkMenuForm(){
    var name=$("#name").val();
    if(!name){
        alert("名称不能为空！");
        $("#name").focus();
        return false;
    }
    var flow=$("#flow").val();
    if(!flow){
        alert("地址不能为空！");
        $("#flow").focus();
        return false;
    }
    return true;
}

//添加菜单
function addMenu(){
    if(checkMenuForm()){
        var url=URL+"/addMenu"; //添加菜单
        var name=$("#name").val();
        var flow=$("#flow").val();
        var icon=$("#icon").val();
        var red=$(".select-red").val();
        var status=$(".select-status").val();
        var show=$(".select-show").val();
        var order=$("#order").val();
        var note=$("#note").val();
        var info={name:name,flow:flow,icon:icon,red:red,status:status,show:show,order:order,note:note};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#mform")[0].reset(); //清空表单
                getAllMenus(); //重新获取所有菜单
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        });
    }
}

//保存菜单
function saveMenu(){
    if(checkMenuForm()){
        var id=$("#id").val();
        var url=URL+"/updateMenu"; //更新菜单
        var name=$("#name").val();
        var flow=$("#flow").val();
        var icon=$("#icon").val();
        var red=$(".select-red").val();
        var status=$(".select-status").val();
        var show=$(".select-show").val();
        var order=$("#order").val();
        var note=$("#note").val();
        var info={id:id,name:name,flow:flow,icon:icon,red:red,status:status,show:show,order:order,note:note};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#mform")[0].reset(); //清空表单
                getAllMenus(); //重新获取所有菜单
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        });
    }
}

//删除菜单
function deleteMenu(){
    if(confirm("确认删除么？")){
        var id=$("#id").val();
        var url=URL+"/deleteMenu"; //添加菜单
        var info={id:id};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#mform")[0].reset(); //清空表单
                getAllMenus(); //重新获取所有菜单
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        })
    }
}