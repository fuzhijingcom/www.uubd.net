$(document).ready(function() {
    getAllPartner();

    //点击图片触发选择图片事件
    $('#code_pic').click(function(){
        $("#s_code").click();
    });
    $('#add-code_pic').click(function(){
        $("#add-s_code").click();
    });
});


//获取所有合伙人
function getAllPartner(){
    url=URL+"/getPartner";
    var html="<br><strong>暂无合伙人数据！</strong>";
    $.get(url, function (data) {
        if(data.length>0){
            html="<table class='table table-bordered style-table'>"+
                    "<thead>"+
                        "<tr>"+
                            "<th>名称</th>"+
                            "<th>类别</th>"+
                            "<th>电话</th>"+
                            "<th>提现</th>"+
                            "<th>特权码</th>"+
                            "<th>创建时间</th>"+
                            "<th>有效年份</th>"+
                            "<th>所属门店</th>"+
                            "<th class='qrcode-td'>二维码</th>"+
                        "</tr>"+
                    "</thead>"+
                    "<tbody>";
            $.each(data, function(i, item){
                html += "<tr onclick='setPartnerForm("+item.p_id+",this)'>"+
                    "<td title="+item.is_withdraw+"@"+item.effectiveyear+">"+item.p_name+"</td>"+
                    "<td promotion_type="+item.promotion_type+">"+item.promotion_type_str+"</td>"+
                    "<td>"+item.p_phone+"</td>"+
                    "<td>"+item.is_withdraw_str+"</td>"+
                    "<td>"+item.privilege_id+"</td>"+
                    "<td>"+item.partner_time+"</td>"+
                    "<td>"+item.effectiveyear+"年</td>"+
                    "<td title='"+item.promotion_store+"'>"+item.store_name+"</td>"+
                    "<td class='qrcode-td'><img src='"+item.qrcode+"' class='setting-upload-preview img-thumbnail' id='code_pic_td'></td>";
            });

            html+="</tbody>"+
                "</table>";
        }
        $("#PartnerLists").html(html);
    });
}

//点击门店触发事件
var lastThisObj = null;
function setPartnerForm(id,thisObj){
    if($('#partner-collapseTwo').attr('class')!="panel-collapse collapsing"){
        $('#partner-collapseTwo').collapse('show');
        $('#partner-collapseOne').collapse('hide');
    }
    if(lastThisObj!=null){
        lastThisObj.removeClass('selected-tr');
    }
    $("#id").val(id);
    var td = $(thisObj).children('td');
    var is_withdraw = (td.eq(0).attr("title")).split('@')[0];
    var effectiveyear = (td.eq(0).attr("title")).split('@')[1];
    $("#p_name").val(td.eq(0).text());
    $("#promotion_type").val(td.eq(1).attr('promotion_type').split('#')[0]);
    $("#promotion_store").val(td.eq(7).attr('title').split('#')[0]);
    var wdCheck = document.getElementById('is_withdraw');
    if(parseInt(is_withdraw)){
        wdCheck.checked = true;
        wdCheck.value = 1;
        console.log(wdCheck.checked);
    }else{
        wdCheck.checked = false;
        wdCheck.value = 0;
    }
    $("#effectiveyear").val(effectiveyear);
    $("#p_phone").val(td.eq(2).text());
    $("#code_pic_edit").attr("src", td.eq(8).find("img").eq(0).attr("src"));
    $("#edit-s_coordinate").val($(thisObj).attr("data-coordinate"));
    $(thisObj).addClass('selected-tr');

    lastThisObj = $(thisObj);
    addressInputChange();
}

//检查表单内容是否合法
function checkPartnerForm(){
    //判断名称是否为空
    var p_name=$("#p_name").val();
    var promotion_type = $('#promotion_type').val();
    var p_phone = $('#p_phone').val();
    var is_withdraw = $('#is_withdraw').val();
    var promotion_store = $('#promotion_store').val();
    var effectiveyear = $('#effectiveyear').val();

    if(!promotion_type || promotion_type==0){
        alert('请选择合伙人类型');
        $('#promotion_type').focus();
        return false;
    }
    if(!p_name){
        alert("名称不能为空！");
        $("#p_name").focus();
        return false;
    }
    if(!p_phone){
        alert('电话不能为空！');
        $('#p_phone').focus();
        return false;
    }
    if(!promotion_store || promotion_store==0){
        alert('请选择合伙人的所属门店');
        $('#promotion_store').focus();
        return false;
    }
    if(!effectiveyear || effectiveyear==0){
        alert('请选择合伙人有效期！');
        $('#effectiveyear').focus();
        return false;
    }
    return true;
}

//添加合伙人
function addPartner(){
    if(checkPartnerForm()){
        var promotion_type = $('#promotion_type').val();
        var p_name = $('#p_name').val();
        var p_phone = $('#p_phone').val();
        var is_withdraw = $('#is_withdraw').val();
        var promotion_store = $('#promotion_store').val();
        var effectiveyear = $('#effectiveyear').val();

        $.ajax({
            url: MODULE+"/Partner/addPartner",
            type: "POST",
            data: {'promotion_type':promotion_type,'p_name':p_name,'p_phone':p_phone,'is_withdraw':is_withdraw,'promotion_store':promotion_store,'effectiveyear':effectiveyear},
            dataType:'json',
            success: function (backdata) {
                if(backdata.sign==1){
                    // $("#sform")[0].reset();
                    // $("#edit-s_addr").attr("title","");
                    // $("#code_pic_edit").attr("src", PUBLIC+"/Home/images/brand-logo-default.jpg");
                    getAllPartner(); //重新获取所有合伙人信息
                    alert(backdata.msg);
                }else{
                    alert(backdata.msg);
                }
            }
        });
    }
}

//保存合伙人数据
function savePartner(){
    if(checkPartnerForm()){
        var p_id = $('#id').val();
        var promotion_type = $('#promotion_type').val();
        // var p_name = $('#p_name').val(); 不允许修改合伙人名称
        var p_phone = $('#p_phone').val();
        var is_withdraw = $('#is_withdraw').val();
        var promotion_store = $('#promotion_store').val();
        var effectiveyear = $('#effectiveyear').val();
        $.ajax({
            url: MODULE+"/Partner/updatePartner",
            type: "POST",
            data: {'p_id':p_id,'promotion_type':promotion_type,'p_phone':p_phone,'is_withdraw':is_withdraw,'promotion_store':promotion_store,'effectiveyear':effectiveyear},
            dataType:'json',
            success: function (backdata) {
                if(backdata.sign==1){
                    // $("#sform")[0].reset();
                    // $("#edit-s_addr").attr("title","");
                    // $("#code_pic_edit").attr("src", PUBLIC+"/Home/images/brand-logo-default.jpg");
                    getAllPartner(); //重新获取所有门店
                    alert(backdata.msg);
                }
                else{
                    alert(backdata.msg);
                }
            }
        });
    }
}

//删除门店
function deletePartner(){
    if(confirm("确认删除么？")){
        var id=$("#id").val();
        var url=URL+"/delPartner"; //添加门店
        var info={'p_id':id};
        if(!id){
            alert('貌似木有选中！');
            return false;
        }
        $.ajax({
            url:url,
            data:{p_id:id},
            type:'post',
            dataType:'json',
            success:function(data){
                if(data.sign==1){
                    $("#sform")[0].reset(); //清空表单
                    $("#code_pic_edit").attr("src", PUBLIC+"/Home/images/brand-logo-default.jpg");
                    getAllPartner(); //重新获取所有门店
                }else{
                    alert(data.msg);
                }
            },
            error:function(data){
                console.log(data);
            }
        });
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
    var selectedType = document.getElementById('promotion_type').value;
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


function searchPartner(){
    var keyword = $('#keyword').val();
    var p_type = $('#selectP').val();
    $.ajax({
        type:'post',
        url:URL+'/searchPartner',
        dataType:'json',
        data:{'keyword':keyword,'p_type':p_type},
        success:function(data){
            if(data.sign!=-1){
                html="<table class='table table-bordered style-table'>"+
                    "<thead>"+
                    "<tr>"+
                    "<th>名称</th>"+
                    "<th>类别</th>"+
                    "<th>电话</th>"+
                    "<th>提现</th>"+
                    "<th>特权码</th>"+
                    "<th>创建时间</th>"+
                    "<th>有效年份</th>"+
                    "<th>所属门店</th>"+
                    "<th class='qrcode-td'>二维码</th>"+
                    "</tr>"+
                    "</thead>"+
                    "<tbody>";
                $.each(data, function(i, item){
                    html += "<tr onclick='setPartnerForm("+item.p_id+",this)'>"+
                        "<td title="+item.is_withdraw+"@"+item.effectiveyear+">"+item.p_name+"</td>"+
                        "<td promotion_type="+item.promotion_type+">"+item.promotion_type_str+"</td>"+
                        "<td>"+item.p_phone+"</td>"+
                        "<td>"+item.is_withdraw_str+"</td>"+
                        "<td>"+item.privilege_id+"</td>"+
                        "<td>"+item.partner_time+"</td>"+
                        "<td>"+item.effectiveyear+"年</td>"+
                        "<td title='"+item.promotion_store+"'>"+item.store_name+"</td>"+
                        "<td class='qrcode-td'><img src='"+item.qrcode+"' class='setting-upload-preview img-thumbnail' id='code_pic_td'></td>";
                });

                html+="</tbody>"+
                    "</table>";
                $("#PartnerLists").html(html);
            }else{
                alert(data.msg);
            }
        },
        error:function(data){

        }

    });
}

