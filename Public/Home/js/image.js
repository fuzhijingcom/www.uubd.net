var scrollFlag = 0;

$(document).ready(function () {
    // 加载其各类图片管理模块的其他图片管理控制组件并初始化图片管理的筛选项
    renderContent('其他图片管理');
});


//重绘图片管理
function renderContent(module) {
    // console.log(module);
    //close all pop
    // $('[data-toggle="popover"]').popover('hide');
    $('.popover').popover('hide');

    var controlBox = document.getElementById('controlBox');


    // reset scroll

    var controlBoxFather = document.getElementById('controlBox-father');
    controlBoxFather.style.position = 'relative';
    $('body').scrollTop(0);


    switch (module) {
        case '其他图片管理':
            controlBox.innerHTML = component.controlPart.searchBox;
            multipleImgUpload();
            searchImage();
            enterBind();
            // empty table
            var bodyBox = document.getElementById('bodyTable');
            // bodyBox.style.margin = 0 + 'px';
            break;
    }
}

function searchImage() {
    var searchInput = document.getElementById('searchInput');

    var ajaxData = {
        search_val: searchInput.value
    };

    var request = $.ajax({
        method: "GET",
        url: URL + '/imgList',
        data: ajaxData,
        dataType: "json"
    });

    request.done(function( res ) {
        if (res.sign == 1) {
            var newElement = $('<table cellspacing="1" class="table table-hover table-bordered tablesorter" id="bodyTable"><thead id="tableHead"></thead><tbody id="imageTbody"></tbody></table>');
            $('#image-table').append(newElement);

            initTable(res.result);
        } else {
            console.log(res.msg);
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });
}

//控件回车按钮绑定事件
function enterBind() {
    $('.first-part-input').keydown(function (event) {
        if (event.keyCode == '13') {
            searchImage();
        }
    });
}

//搜索结果为空时说明搜索不到
function addNoitemInfo() {
    var tBody = document.getElementById('content');
    var tableBody = document.getElementById('stockTbody');
    var tableP = document.getElementById('tableP');
    if (!tableP) {
        if (tableBody != null) {
            tableBody.innerHTML = '';
        }
        var p = document.createElement('p');
        p.innerHTML = '无符合条件的数据';
        p.className = 'table-p';
        p.id = 'tableP';
        tBody.appendChild(p);
    } else {
        console.log('exist tableP');
    }
}

// 移除表格内容
function removeTableP() {
    var tableP = document.getElementById('tableP');
    if (tableP) {
        tableP.parentNode.removeChild(tableP);
    }
}

function uploadImg(e) {
    var $v = $(e);

    if (e.files && e.files[0]) {
        var formData = new FormData(e.parentNode);
        $.each($("input[type='file']")[0].files, function(i, file) {
            console.log(i);
            if (($("input[type='file']")[0].files.length - 1) != i) {
                formData.append('file-'+i, file);
            }
        });

        var request = $.ajax({
            url: URL + '/uploadImg',
            method: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        });

        request.done(function (res) {
            if (res.sign == 1) {
                $("#imageTbody").empty();
                searchImage();

                var controlBox = document.getElementById('controlBox');
                controlBox.innerHTML = component.controlPart.searchBox;
            } else {
                console.log(res.msg);
            }
        });

        request.fail(function (jqXHR, texStatus) {
            // alert('Request failed: ' + texStatus);
            alert('Request failed: ' + '图太大，请分开上传');
        });
    }
}

// 替换图片
function updateImg(e) {
    var $v = $(e);
    var formData = new FormData(e.parentNode);

    if (e.files && e.files[0]) {
        var request = $.ajax({
            url: URL + '/replaceImg',
            method: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        });

        request.done(function (res) {
            if (res.sign == 1) {
                updateLineInfo(e.parentNode.parentNode.parentNode, res.result);
            } else {
                console.log(res.msg);
            }
        });

        request.fail(function (jqXHR, texStatus) {
            alert('Request failed: ' + texStatus);
        });
    }
}

function updateLineInfo(e, data) {
    console.log(data);
    console.log(e);
    var $v = $(e);
    console.log($v.find("td").eq(0).html());
    $v.find("td").eq(0).html(data.img_name);
    console.log($v.find("td").eq(1).html());
    var img_html = '<img src="' + SITE_URL + '/' + data.relative_url + '?t='+Math.random() + '">';
    $v.find("td").eq(1).html(img_html);
    console.log($v.find("td").eq(3).html());
    $v.find("td").eq(3).html(data.relative_url);
    console.log($v.find("td").eq(4).html());
    $v.find("td").eq(4).html(data.absolute_url);
}

// 删除图片
function removeImg(e, id) {
    var msg = "您真的确定要删除吗？\n\n请确认！";
    if (confirm(msg) == false){
        return false;
    }

    var request = $.ajax({
        method: "POST",
        url: URL + '/removeImg',
        data: {id: id},
        dataType: "json"
    });

    request.done(function( res ) {
        if (res.sign == 1) {
            $(e).parent().parent().parent().remove();
        } else {
            console.log(res.msg);
        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });
}

// 点击替换
function pretendClick(e) {
    $(e).prev().trigger("click");
}

function pretendClickUpload(e) {
    $(e).prev().trigger("click");
}