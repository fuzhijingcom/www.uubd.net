function changepwd(){
	var oldpwd=$("#oldpwd").val();
	var newpwd=$("#newpwd").val();
	var url=URL+"/changepwd"; //更新菜单
	var info={oldpwd:oldpwd,newpwd:newpwd};
    $.post(url,info, function (data) {
        if(data.result>0){
            $("#pwdform")[0].reset(); //清空表单
            $("#oldpwd_div").removeClass("has-error");
            $("#newpwd_div").removeClass("has-success");
            $("#renewpwd_div").removeClass("has-success");
            alert("密码修改成功！");
        }else{
            $("#oldpwd_div").addClass("has-error");
            alert(data.msg);
        }
    });
}

//校验两次密码是否一样
function ifSamePwd(n){
    var renewpwd = n.value;
    var newpwd = $("#newpwd").val();
    if(renewpwd!=newpwd){
        $("#renewpwd_div").addClass("has-error"); 
        $("p").show();
        $("p").fadeOut(2000);
    }else{
        $("#newpwd_div").addClass("has-success"); 
        $("#renewpwd_div").addClass("has-success"); 
    }

}