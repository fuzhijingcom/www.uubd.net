$(function(){
    getAllUsers();
    $("#nickname").val('');
    $("#pwd").val('');
});

//获取所有用户
function getAllUsers(){
	var url=URL+"/getUsers";
    var html="<br><strong>暂无用户！</strong>";
    $.get(url, function (data) {
        if(data.length>0){
            html="<table class='table table-bordered style-table'>"+
                    "<thead>"+
                        "<tr>"+
                            "<th>用户</th>"+
                            "<th>昵称</th>"+
                            "<th>手机</th>"+
                            "<th>分成</th>"+
                            "<th>门店</th>"+
                            "<th class='qrcode-td'>角色</th>"+
                        "</tr>"+
                    "</thead>"+
                    "<tbody>";
            $.each(data, function(i, item){
                var store_name=(item.s_id=='-1') ? "所有门店" : item.s_name;
                store_name = (store_name != null) ? store_name : '（未分配门店）';
                var role_name = (item.r_name != null) ? item.r_name : '（未分配角色）';
                html+="<tr onclick='setUserForm("+item.id+",this)'>"+
                        "<td>"+item.name+"</td>"+
                        "<td>"+item.nickname+"</td>"+
                        "<td>"+item.phone+"</td>"+
                        "<td>"+item.point+"</td>"+
                        "<td data-val="+item.s_id+">"+store_name+"</td>"+
                        "<td class='qrcode-td' data-val="+item.r_id+">"+role_name+"</td>"+
                    "</tr>";
            }
            );
            html+="</tbody>"+
                "</table>";
        }
        $("#userLists").html(html);
    });
}

//点击用户触发事件
var lastThisObj = null;
function setUserForm(id,thisObj){
    if(lastThisObj!=null){
        lastThisObj.removeClass('selected-tr');
    }
	$("#id").val(id);
    $("#pwd").val('');
    var td = $(thisObj).children('td');
	$("#name").val(td.eq(0).text());
    $("#nickname").val(td.eq(1).text());
    $("#phone").val(td.eq(2).text());
    $("#point").val(td.eq(3).text());
    $("#storeList").val(td.eq(4).attr('data-val'));
    $("#roleList").val(td.eq(5).attr('data-val'));
    $(thisObj).addClass('selected-tr');
    lastThisObj = $(thisObj);
}

//检查表单内容是否合法
function checkUserForm(flag){
    var name=$("#name").val();
    var nickname=$("#nickname").val();
    var pwd=$("#pwd").val();
    var phone=$("#phone").val();
    if(!name){
        alert("用户不能为空！");
        $("#name").focus();
        return false;
    }
    if(!nickname){
        alert("昵称不能为空！");
        $("#nickname").focus();
        return false;
    }
    if(flag=='0'){
        if(!pwd){  //当flag=0时代表是添加用户，密码不能为空；flag=1代表更新用户，密码可以不修改
            alert("密码不能为空！");
            $("#pwd").focus();
            return false;
        }
    }
    if(!phone){
        alert("手机不能为空！");
        $("#phone").focus();
        return false;
    }

    return true;
}

//添加用户
function addUser(){
    if(checkUserForm(0)){
        var url=URL+"/addUser"; //添加用户
        var info={};

        var name=$("#name").val();
        info['name']=name;

        var nickname=$("#nickname").val();
        info['nickname']=nickname;

        var pwd=$("#pwd").val();
        info['pwd']=pwd;

        var phone=$("#phone").val();
        if(phone){
            info['phone']=phone;
        }
        var point=$("#point").val();
        if(point){
            info['point']=point;
        }
        var s_id=$("#storeList").val();
        if(s_id){
            info['s_id']=s_id;
        }
        var r_id=$("#roleList").val();
        if(r_id){
            info['r_id']=r_id;
        }

        $.post(url,info, function (data) {
            if(data.result>0){
                $("#uform")[0].reset(); //清空表单
                getAllUsers(); //重新获取所有用户
            }else{
                alert(data.msg);
            }
        });
    }
}

//保存用户
function saveUser(){
    if(checkUserForm(1)){
        var id=$("#id").val();
        var url=URL+"/updateUser"; //更新用户
        var name=$("#name").val();
        var nickname=$("#nickname").val();
        var pwd=$("#pwd").val();
        var phone=$("#phone").val();
        var point=$("#point").val();
        var s_id=$("#storeList").val();
        var r_id=$("#roleList").val();

        var info={id:id,name:name,nickname:nickname,pwd:pwd,phone:phone,point:point,s_id:s_id,r_id:r_id};
        console.log(info);
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#uform")[0].reset(); //清空表单
                getAllUsers(); //重新获取所有用户
            }else{
                alert(data.msg);
            }
        });
    }
}

//删除用户
function deleteUser(){
    if(confirm("确认删除么？")){
        var id=$("#id").val();
        var url=URL+"/deleteUser"; //添加用户

        var info={id:id};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#id").val("");
                $("#uform")[0].reset(); //清空表单
                getAllUsers(); //重新获取所有用户
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        })
    }
}
