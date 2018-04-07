$(document).ready(function() {
    init();
    getAllStores();

    //点击图片触发选择图片事件
    $('#code_pic').click(function(){
        $("#s_code").click();
    });
    $('#add-code_pic').click(function(){
        $("#add-s_code").click();
    });
});


//获取所有门店
function getAllStores(){
    url=URL+"/getStores";
    var html="<br><strong>暂无门店！</strong>";
    $.get(url, function (data) {
        if(data.length>0){
            html="<table class='table table-bordered style-table'>"+
                    "<thead>"+
                        "<tr>"+
                            "<th>名称</th>"+
                            "<th>类别</th>"+
                            "<th>地址</th>"+
                            "<th>电话</th>"+
                            "<th class='qrcode-td'>二维码</th>"+
                        "</tr>"+
                    "</thead>"+
                    "<tbody>";
            $.each(data, function(i, item){
                // console.log(item.s_code);
                if(item.s_type==0){
                    item.s_type_name = '门店';
                }else{
                    item.s_type_name = '推广员';
                }
                html+="<tr onclick='setStoreForm("+item.s_id+",this)' data-coordinate='"+item.s_coordinate+"'>"+ //39.89477,116.35432
                        "<td title="+item.is_withdraw+"@"+item.effectiveyear+">"+item.s_name+"</td>"+
                        "<td data-sType="+item.s_type+">"+item.s_type_name+"</td>"+
                        "<td>"+item.s_addr+"</td>"+
                        "<td>"+item.s_phone+"</td>"+
                        "<td class='qrcode-td'><img src='"+item.s_code+"' class='setting-upload-preview img-thumbnail' id='code_pic_td'/></td>"+
                        "</tr>";
            });
            html+="</tbody>"+
                "</table>";
        }
        $("#storeLists").html(html);
    });
}

//点击门店触发事件
var lastThisObj = null;
function setStoreForm(id,thisObj){
    // console.log(editBox.attr('class'));
    if($('#store-collapseTwo').attr('class')!="panel-collapse collapsing"){
        //console.log(editBox.attr('class'));
        //console.log(editBox);
        $('#store-collapseTwo').collapse('show');
        $('#store-collapseOne').collapse('hide');
        //console.log(editBox.collapse('show'));
    }
    if(lastThisObj!=null){
        lastThisObj.removeClass('selected-tr');
    }
    $("#id").val(id);
    var td = $(thisObj).children('td');
    var is_withdraw = (td.eq(0).attr("title")).split('@')[0];
    var effectiveyear = (td.eq(0).attr("title")).split('@')[1];
    console.log(is_withdraw,effectiveyear);
    $("#s_name").val(td.eq(0).text());
    $("#s_type").val(td.eq(1).attr("data-sType"));
    // $("#is_withdraw").val(is_withdraw);
    var wdCheck = document.getElementById('is_withdraw');
    console.log(is_withdraw);
    if(parseInt(is_withdraw)){
        wdCheck.checked = true;
        wdCheck.value = 1;
        console.log(wdCheck.checked);
    }else{
        wdCheck.checked = false;
        wdCheck.value = 0;
        console.log(wdCheck.checked);
    }
    $("#effectiveyear").val(effectiveyear);
    $("#edit-s_addr").val(td.eq(2).text());
    $("#s_phone").val(td.eq(3).text());
    $("#code_pic_edit").attr("src", td.eq(4).find("img").eq(0).attr("src"));
    $("#edit-s_coordinate").val($(thisObj).attr("data-coordinate"));
    $(thisObj).addClass('selected-tr');
    
    //设置地图位置
    var selectedType = document.getElementById('s_type').value;
    if(selectedType==0){
        $("#edit-s_addr").attr("title",$(thisObj).attr("data-coordinate"));
        pointSetMap($("#edit-s_addr").attr('title'));
    }
    lastThisObj = $(thisObj);
    addressInputChange();
}

//检查表单内容是否合法
function checkStoreForm(){
    //判断名称是否为空
    // console.log('in chekStoreForm');
    var s_name=$("#s_name").val();
    if(!s_name){
        alert("名称不能为空！");
        $("#s_name").focus();
        return false;
    }
    //判断添加的是门店还是推广员
    var s_type=$("#s_type").val();
    if(s_type==0){
        var s_addr=$("#edit-s_addr").val();
        if(!s_addr) {
            alert("地址不能为空！");
            $("#edit-s_addr").focus();
            return false;
        }
    }
    return true;
}

//添加门店
function addStore(){
    if(checkStoreForm()){
        var formData = new FormData($( "#sform" )[0]);
        $.ajax({
            url: MODULE+"/Store/addStore",
            type: "POST",
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (backdata) {
                if(backdata.result>0){
                    $("#sform")[0].reset();
                    $("#edit-s_addr").attr("title","");
                    $("#code_pic_edit").attr("src", PUBLIC+"/Home/images/brand-logo-default.jpg");
                    getAllStores(); //重新获取所有门店
                }
                else{
                    alert(backdata.mess);
                }
            }
        });
    }
}

//保存门店
function saveStore(){
    if(checkStoreForm()){
        var formData = new FormData($( "#sform" )[0]);
        $.ajax({
            url: MODULE+"/Store/updateStore",
            type: "POST",
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (backdata) {
                if(backdata.result>0){
                    $("#sform")[0].reset();
                    $("#edit-s_addr").attr("title","");
                    $("#code_pic_edit").attr("src", PUBLIC+"/Home/images/brand-logo-default.jpg");
                    getAllStores(); //重新获取所有门店
                }
                else{
                    alert(backdata.mess);
                }
            }
        });
    }
}

//删除门店
function deleteStore(){
    if(confirm("确认删除么？")){
        var id=$("#id").val();
        var url=URL+"/deleteStore"; //添加门店
        var info={id:id};
        $.post(url,info, function (data) {
            if(data.result>0){
                $("#sform")[0].reset(); //清空表单
                $("#code_pic_edit").attr("src", PUBLIC+"/Home/images/brand-logo-default.jpg");
                getAllStores(); //重新获取所有门店
            }else{
                alert("服务器繁忙，请稍候再试！");
            }
        })
    }
}


//活动图片上传及时预览
function selectIcon(string) {
    // console.log(string);
    if(string=='add'){
        var pic = document.getElementById("add-code_pic");
        var file = document.getElementById("add-s_code");
        var jqueryPic =$("#add-code_pic");
    }else{
        var pic = document.getElementById("code_pic");
        var file = document.getElementById("s_code");
        var jqueryPic =$("#code_pic");
    }
    var ext=file.value.substring(file.value.lastIndexOf(".")+1).toLowerCase();
    // gif在IE浏览器暂时无法显示
    if(ext!='png'&&ext!='jpg'&&ext!='jpeg'){
        alert("文件必须为图片！");
        jqueryPic.attr("src", PUBLIC+"/Home/images/brand-logo-default.jpg");
        file.after(file.clone().val(""));
        file.remove();
        return;
    }
    // IE浏览器
    if (document.all) {
        file.select();
        var reallocalpath = document.selection.createRange().text;
        var ie6 = /msie 6/i.test(navigator.userAgent);
        // IE6浏览器设置img的src为本地路径可以直接显示图片
        if (ie6) pic.src = reallocalpath;
        else {
            // 非IE6版本的IE由于安全问题直接设置img的src无法显示本地图片，但是可以通过滤镜来实现
            pic.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='image',src=\"" + reallocalpath + "\")";
            // 设置img的src为base64编码的透明图片 取消显示浏览器默认图片
            pic.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        }
    }else{
        html5Reader2(pic,file);
    }
}

function html5Reader2(pic,file){
    var file = file.files[0];
    var reader = new FileReader();

    reader.readAsDataURL(file);
    reader.onload = function(e){
        pic.src=this.result;
    }
}

function addressInputChange(){
    var selectedType = document.getElementById('s_type').value;
    var addressBox = document.getElementById('addressBox');
    if(selectedType == 1){
        addressBox.style.display = 'none';
    }else{
        addressBox.style.display = 'block';
    }
}

function changeCheckValue(){
    var now = document.getElementById('is_withdraw');
    console.log(now.checked);
    if(now.checked){
        now.value = 1;
        $('#effectiveyear').val(1);
    }else{
        now.value = 0;
        $('#effectiveyear').val(0);
    }
}
