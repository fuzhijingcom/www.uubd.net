var packages=new Array();

$(function(){ 
    getAllRoles();
    getAllPackages();
});

//获取所有角色
function getAllRoles(){
	url=URL+"/getRoles";
    var html="<br><strong>暂无角色！</strong>";
    $.get(url, function (data) {
        if(data.length>0){
            html="";
            $.each(data, function(i, item){
                var color=(item.m_status==1) ? "none" : "#ddd";
                html+="<li class='list-group-item' id='r"+item.r_id+"' data-note='"+item.r_note+"' onclick='setRoleForm("+item.r_id+");'>"+item.r_name+"</li>";
            }
            );
        }
        $("#roleLists").html(html);
    });
}

//点击角色触发事件
function setRoleForm(id){
	$("#id").val(id);
	$("#name").val($("#r"+id).html());
    $("#note").val($("#r"+id).attr("data-note"));

    var menuPackageAry=getPresentMenuPackages(); //选择的菜单和组件关系数组
    var menuPackages=JSON.stringify(menuPackageAry);
    $.each(menuPackageAry, function(i, item){
        removeMenu(item[0]); //移除添加的角色
    });

    var url=URL+"/getRoleMenuPackages"; //获取菜单、组件
    var info={id:id};
    $.post(url,info, function (data) {
        if(data.menus && data.menus.length>0){
            $.each(data.menus, function(i, item){
                addMenu(item.toString());
            });
            if(data.menuPackages && data.menuPackages.length>0){
                $.each(data.menuPackages, function(i, item){
                    if(item.p_id){
                        addPackage(item.m_id,item.p_id);
                    }
                });
            }
        }
    });
}

//检查表单内容是否合法
function checkRoleForm(){
    var name=$("#name").val();
    if(!name){
        alert("名称不能为空！");
        $("#name").focus();
        return false;
    }
    return true;
}

//添加角色
function addRole(){
    if(checkRoleForm()){
        var url=URL+"/addRole"; //添加角色
        var name=$("#name").val();
        var note=$("#note").val();

        var menuPackageAry=getPresentMenuPackages(); //选择的菜单和组件关系数组
        var menuPackages=JSON.stringify(menuPackageAry);

        var info={name:name,note:note,menuPackages:menuPackages};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#rform")[0].reset(); //清空表单
                $.each(menuPackageAry, function(i, item){
                    removeMenu(item[0]); //移除添加的角色
                });
                getAllRoles(); //重新获取所有角色
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        });
    }
}

//保存角色
function saveRole(){
    if(checkRoleForm()){
        var id=$("#id").val();
        var url=URL+"/updateRole"; //更新角色
        var name=$("#name").val();
        var note=$("#note").val();

        var menuPackageAry=getPresentMenuPackages(); //选择的菜单和组件关系数组
        var menuPackages=JSON.stringify(menuPackageAry);

        var info={id:id,name:name,note:note,menuPackages:menuPackages};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#rform")[0].reset(); //清空表单
                $.each(menuPackageAry, function(i, item){
                    removeMenu(item[0]); //移除添加的角色
                });
                getAllRoles(); //重新获取所有角色
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        });
    }
}

//删除角色
function deleteRole(){
    if(confirm("确认删除么？")){
        var id=$("#id").val();
        var url=URL+"/deleteRole"; //添加角色

        var menuPackageAry=getPresentMenuPackages(); //选择的菜单

        var info={id:id};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#rform")[0].reset(); //清空表单

                $.each(menuPackageAry, function(i, item){
                    removeMenu(item[0]); //移除添加的角色
                });

                getAllRoles(); //重新获取所有角色
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        })
    }
}

//添加菜单
function addMenu(id){
    var m_name=$("#am"+id).text();
    var menustr="<div class='panel panel-default' id='pm"+id+"'>"+
                    "<div class='panel-heading' role='tab' id='heading"+id+"'>"+
                        "<h4 class='panel-title'>"+
                            "<a class='amenuLists' role='button' data-toggle='collapse' data-parent='#accordion' href='#collapse"+id+"' aria-expanded='true' aria-controls='collapse"+id+"' menu-id='"+id+"'>"+m_name+"</a>"+
                            "<button type='button' class='close' data-dismiss='alert' aria-label='Close' onclick='removeMenu("+id+")'>"+
                                "<span aria-hidden='true'>&times;</span>"+
                            "</button>"+
                        "</h4>"+
                    "</div>"+
                    "<div id='collapse"+id+"' class='panel-collapse collapse' role='tabpanel' aria-labelledby='heading"+id+"'>"+
                        "<div class='panel-body'>"+
                            "<div class='panel panel-default'>"+
                                "<div class='panel-body packagePanel'>"+
                                    "<div class='dropdown pull-left'>"+
                                        "<button class='btn btn-default dropdown-toggle' type='button' id='packageMenu"+id+"' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>添加组件："+
                                            "<span class='caret'></span>"+
                                        "</button>"+
                                    "</div>"+
                                    "<div class='pull-left' id='packageUl'>"+
                                        "<ul class='list-inline' id='packageLists"+id+"'></ul>"+
                                    "</div>"+
                                "</div>"+
                            "</div>"+
                        "</div>"+
                    "</div>"+
                "</div>";
    $("#accordion").append(menustr);
    $("#am"+id).removeAttr("onclick"); //移除onclick事件
    $("#am"+id).css("color","#ddd"); //设置颜色
    setPackages(id);
}

//移除菜单
function removeMenu(id){
    $("#pm"+id).remove(); //移除添加的角色
    $("#am"+id).attr("onclick","addMenu("+id+")"); //添加onclick事件
    $("#am"+id).css("color","black"); //设置颜色
}


//获取所有组件
function getAllPackages(){
    var url=MODULE+"/Package/getPackages";
    $.get(url, function (data) {
        if(data.length>0){
            packages=data;
        }
    });
}


function setPackages(id){
    if(packages.length>0){
        var html="<ul class='dropdown-menu' aria-labelledby='packageMenu'>";
        $.each(packages, function(i, item){
            html+="<li id='lp"+id+item.p_id+"'><a id='ap"+id+item.p_id+"' href='javascript:' onclick='addPackage("+id+","+item.p_id+")'>"+
                        "<ol class='breadcrumb' style='margin:0;padding:0;background-color:white'>"+
                            "<li class='active' id='option"+id+item.p_id+"'>"+item.p_option+"</li>"+
                            "<li class='active' id='name"+id+item.p_id+"'>"+item.p_name+"</li>"+
                            "<li class='active' id='type"+id+item.p_id+"'>"+item.p_type+"</li>"+
                        "</ol>"+
                    "</a></li>";
        }
        );
        html+="</ul>";
    }
    $("#packageMenu"+id).after(html); //追加角色列表
}

//添加组件
function addPackage(id,pid){
    var p_option=$("#option"+id+pid).text();
    var p_name=$("#name"+id+pid).text();
    var p_type=$("#type"+id+pid).text();
    var packagestr="<li class='newroles' id='lpl"+id+pid+"'>"+
                    "<div class='alert alert-warning alert-dismissible list-inline' role='alert'>"+
                        "<button type='button' class='close' aria-label='Close' onclick='removePackage("+id+","+pid+")'>"+
                            "<span aria-hidden='true'>&times;</span>"+
                        "</button>"+
                            "<ol class='breadcrumb pbreadcrumb"+id+"' package-id='"+pid+"' style='margin:0;padding:0;width:200px;'>"+
                                "<li class='active'>"+p_option+"</li>"+
                                "<li class='active'>"+p_name+"</li>"+
                                "<li class='active'>"+p_type+"</li>"+
                            "</ol>"+
                    "</div>"+
                "</li>";
    $("#packageLists"+id).append(packagestr); //把组件添加到末尾
    $("#ap"+id+pid).removeAttr("onclick"); //移除onclick事件
    $("#ap"+id+pid).css("color","#ddd"); //设置颜色
}


//移除组件
function removePackage(id,pid){
    $("#lpl"+id+pid).remove(); //移除添加的组件
    $("#ap"+id+pid).attr("onclick","addPackage("+id+","+pid+")"); //添加onclick事件
    $("#ap"+id+pid).css("color","black"); //设置颜色
}


//获取当前选择的所有菜单
function getPresentMenuPackages(){
    var menuPackageAry=new Array();
    $(".amenuLists").each(function(){
        var mid=$(this).attr("menu-id");
        var a=[mid];
        if($('ol').is(".pbreadcrumb"+mid)){
            $(".pbreadcrumb"+mid).each(function(){
                var b=a;
                var pid=$(this).attr("package-id");
                b=[mid,pid];
                menuPackageAry.push(b);
            });
        }else{
            menuPackageAry.push(a);
        }
     });
    return menuPackageAry;
}