/**
 * 图片编辑操作页初始化 JavaScript
 */

/**
 * 图像内容初始化
 */
$(document).ready(function () {

    // 初始化轮播图
    var url = URL + '/getSlideList';
    var slide_request = $.ajax({
        url: url,
        method: "GET",
        data: { selling_id : selling_id },
        dataType: "json"
    });

    slide_request.done(function(res) {
        if (res.sign == 1) {
            initSlideList(res.result);
        } else {
            console.log(res.msg)
        }
    });

    slide_request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });


    // 初始化详情页图片
    var page_url = URL + '/getPageImgList';
    var page_request = $.ajax({
        url: page_url,
        method: "GET",
        data: { selling_id: selling_id },
        dataType: "json"
    });

    page_request.done(function (res) {
        // console.log(res);
        if (res.sign == 1) {
            // 初始化详情页图片上传栏目和预览图
            initPageControl(res.result);
        } else {
            console.log(res.msg);
        }
    });

    page_request.fail(function (jqXHR, txxtStatus) {
        alert("Request failed: " + textStatus);
    })
});


/**
 * 轮播图数据处理
 * @param data
 */
function initSlideList(data) {
    var str = '';
    for (var i = 0, len = data.length; i < len; ++ i) {
        var imgStr = '';

        if (data[i].url != '') {
            imgStr = '<img src="' + data[i].url + '" height="110" width="110">';
        }

        str += '<div class="col-xs-4 col-md-2">' +
            '<h5>' + data[i].attribute + '</h5>' +
            '<div class="upload-container">' +
            '<div class="slide-img">' + imgStr + '</div>' +
            '<form role="form">' +
            '<div class="form-group" style="display: none;">' +
            '<input value="' + data[i].sku_id + '" name="sku_id" type="text" class="form-control" placeholder="商品sku_id">' +
            '</div>' +
            '<div class="form-group" style="display: none;">' +
            '<input value="' + data[i].order + '" name="order" type="text" class="form-control" placeholder="图片排序">' +
            '</div>' +
            '<input type="file" name="image" class="form-control file-box" onchange="uploadSlideImg(this)" style="display: none;">' +
            '<button type="button" class="btn btn-success" onclick="slidePretendClick(this)">上传图片</button>' +
            '<button type="button" class="btn btn-danger" onclick="removeSlideImg(this)">删除</button>' +
            '</form>' +
            '</div>' +
            '</div>';
    }

    $("#slide-panel").html(str);
}

/**
 * 详情图预览效
 * @param data
 */
function createPagePreview(data) {
    var str = '';
    for (var i = 0, len = data.length; i < len; ++ i) {
        str += '<img src="' + data[i].url + '" width="400">';
    }
    $("#page-left-show").html(str);
}

/**
 * 详情图控制面板和预览初始化
 * @param data
 */
function initPageControl(data) {
    // console.log(data);
    var str = '';
    var imgPreviewData = [];

    var cnt = 0;
    var order = 0;

    if (data != undefined) {
        for (var i = 0, len = data.length; i < len; ++ i) {
            cnt = i + 1;
            order = data[i].order;
            var imgStr = '';
            if (data[i].url != '') {
                imgStr = '<img src="' + data[i].url + '" width="110">';
            }

            str += '<div class="page-upload-container clearfix">' +
                '<div class="page-img">' + imgStr +
                '<h5 class="page-image-title">详情图 ' + cnt + '<br>' + data[i].origin_name + ' </h5>' + '</div>' +
                '<form role="form" class="page-image-form">' +
                '<div class="form-group" style="display: none;">' +
                '<input value="' + data[i].selling_id + '" name="selling_id" type="text" class="form-control" placeholder="商品selling_id">' +
                '</div>' +
                '<div class="form-group" style="display: none;">' +
                '<input value="' + order + '" name="order" type="text" class="form-control" placeholder="详情图顺序号">' +
                '</div>' +
                '<input type="file" name="image" class="form-control file-box" onchange="uploadPageImg(this)" style="display: none;">' +
                '<button type="button" class="btn btn-success" onclick="pagePretendClick(this)">更换图片</button> ' +
                '<button type="button" class="btn btn-danger" onclick="removePageImg(this)" data-toggle="confirmation">删除</button>' +
                '</form>' +
                '</div>';

            var previewTmp = { url: data[i].url };
            imgPreviewData.push(previewTmp);
        }
    }

    ++ cnt;
    ++ order;
    // 另外的上传控制表单
    str += '<div class="page-upload-container clearfix">' +
        '<h5 class="page-image-title" style="width:480px;display: block;">上传更多图片（按住 Ctrl 或 Shift 键可同时载入多张图片）</h5>' +
        '<form role="form" class="page-image-form upload-more-image">' +
        '<input type="file" name="image" id="upload-more-page-image" onchange="uploadMorePageImg(this)" class="form-control file-box" multiple style="display: none;">' +
        '<button type="button" class="btn btn-success" onclick="pretendClickUploadMoreImg(this)">上传更多图片</button> ' +
        '<div class="form-group" style="display: none;">' +
        '<input value="' + selling_id + '" name="selling_id" type="text" class="form-control" placeholder="商品selling_id">' +
        '</div>' +
        '<div class="form-group" style="display: none;">' +
        '<input value="' + order + '" name="order" type="text" class="form-control" placeholder="详情图顺序号">' +
        '</div>' +
        '</form>' +
        '</div>';


    $("#page-upload-control").html(str);
    createPagePreview(imgPreviewData);
    dragPageImg();

    lastImgUpload(selling_id);
}

function dragPageImg() {
    $('.page-upload-container').bind('mouseover', function () {
        $(this).css("cursor", "move");
    });

    var $list = $('#page-upload-control');

    var old_order = '';
    $list.find("input[name='order']:not(:last)").each(function () {
        old_order += this.value + ',';
    });

    var origin_last_val = $list.find("input[name='order']:last").val();

    $list.sortable({
        opacity: 0.6,
        revert: true,
        cursor: 'move',
        // handle: '.page-upload-container',
        update: function () {
            var new_order = '';
            $list.find("input[name='order']:not(:last)").each(function () {
                new_order += this.value + ',';
            });

            var last_flag = 0;
            var new_last_val = $list.find("input[name='order']:last").val();

            // 判断图片是否拉到了最后的位置（越过了空白上传栏）
            if (origin_last_val != new_last_val) {
                last_flag = 1;
            }

            var ajaxData = {
                selling_id: selling_id,
                origin_orders: old_order,
                new_orders: new_order,
                last_flag: last_flag
            };

            var request = $.ajax({
                method: "POST",
                url: URL + "/changeImgOrder",
                data: ajaxData,
                dataType: "json"
            });

            request.done(function( res ) {
                if (res.sign == 1) {
                    // 刷新详情图区域内容
                    initPageControl(res.result);

                } else {
                    console.log(res.msg);
                }
            });

            request.fail(function( jqXHR, textStatus ) {
                alert( "Request failed: " + textStatus );
            });
        }
    });
}
