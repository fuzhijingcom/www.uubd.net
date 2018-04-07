/**
 * 更换图片
 * @param e
 */
function uploadPageImg(e) {
    var $v = $(e);
    var formData = new FormData(e.parentNode);

    var request = $.ajax({
        url: URL + '/uploadPageImg',
        method: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });

    request.done(function (res) {
        if (res.sign == 1) {
            // 刷新详情图区域内容
            initPageControl(res.result);

        } else {
            console.log(res.msg);
        }
    });

    request.fail(function (jqXHR, texStatus) {
        alert('Request failed: ' + texStatus);
    });
}

/**
 * 更换图片操作按钮
 * @param e
 */
function pagePretendClick(e) {
    $(e).prev().trigger("click");
}

/**
 * 删除图片操作
 * @param e
 * @returns {boolean}
 */
function removePageImg(e) {
    var msg = "您真的确定要删除吗？\n\n请确认！";
    if (confirm(msg) == false){
        return false;
    }

    var $v = $(e);
    var formData = new FormData(e.parentNode);

    var request = $.ajax({
        url: URL + '/removePageImg',
        method: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });

    request.done(function (res) {
        if (res.sign == 1) {
            // 刷新详情图区域内容
            initPageControl(res.result);

        } else {
            console.log(res.msg);
        }
    });

    request.fail(function (jqXHR, texStatus) {
        alert('Request failed: ' + texStatus);
    });
}

/**
 * 上传更多图片操作
 *
 * @param e
 */
function uploadMorePageImg(e) {
    var $v = $(e);
    var formData = new FormData();

    $.each($v[0].files, function(i, file) {
        formData.append('file-'+i, file);
    });

    formData.append('selling_id', selling_id);

    var request = $.ajax({
        url: URL + '/uploadMutiplePageImgVerstion2',
        method: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });

    request.done(function (res) {
        if (res.sign == 1) {
            // 刷新详情图区域内容
            initPageControl(res.result);

        } else {
            console.log(res.msg);
        }
    });

    request.fail(function (jqXHR, texStatus) {
        // alert('Request failed: ' + texStatus);
        alert('Request failed: ' + '图太大，请分开上传');
    });
}

/**
 * 上传更多图片操作实现的按钮操作
 * @param e
 */
function pretendClickUploadMoreImg(e) {
    $(e).prev().trigger("click");
}

/**
 * 上传多张图片
 *
 * @param selling_id
 */
function lastImgUpload(selling_id) {
    $('#ssi-upload2').ssi_uploader({
        url: URL + '/uploadMultiplePageImg',
        data: {selling_id: selling_id},
        locale: 'zh_CN',
        // preview: false,
        onUpload: function () {
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

            page_request.fail(function (jqXHR, textStatus) {
                alert("Request failed: " + textStatus);
            })
        }
    });
}

function removeAllPageImg() {
    var msg = "您真的确定要删除吗？\n\n请确认！";
    if (confirm(msg) == false){
        return false;
    }

    var request = $.ajax({
        url: URL + '/removeAllPageImg',
        method: 'POST',
        data: {selling_id: selling_id},
        dataType: "json"
    });

    request.done(function (res) {
        if (res.sign == 1) {
            // 刷新详情图区域内容
            initPageControl(res.result);
        } else {
            console.log(res.msg);
        }
    });

    request.fail(function (jqXHR, texStatus) {
        // alert('Request failed: ' + texStatus);
        alert('Request failed: ' + '网络不稳定，请稍后重试...');
    });
}
