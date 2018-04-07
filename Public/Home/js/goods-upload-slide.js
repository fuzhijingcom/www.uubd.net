function uploadSlideImg(e) {
    // console.log(e);
    var $v = $(e);
    // console.log($v);
    var formData = new FormData($v.parent()[0]);
    // var form2Data = new FormData(e.parentNode);

    // console.log(formData);
    // console.log(form2Data);

    var request = $.ajax({
        url: URL+'/uploadSlideImg' ,
        method: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });

    request.done(function (res) {
        if (res.sign == 1) {
            var str = '<img src="' + res.result.thumb_url + '" height="110" width="110">';

            $v.parent().parent().find(".slide-img:first").html(str);

        }
    });

    request.fail(function( jqXHR, textStatus ) {
        alert( "Request failed: " + textStatus );
    });
}

function slidePretendClick(e) {
    $(e).prev().trigger("click");
}

function removeSlideImg(e) {
    var $v = $(e);

    // 判断图片是否已经存在
    if ($v.parent().parent().has('img').length == 0) {
        alert('没有图片，不能删除！');
        return false;
    }

    // 判断是否确实要删除
    var msg = "您真的确定要删除吗？\n\n请确认！";
    if (confirm(msg) == false){
        return false;
    }

    var formData = new FormData($v.parent()[0]);
    // 删除图片操作
    var request = $.ajax({
        url: URL + '/removeSlideImg',
        method: 'POST',
        data: formData,
        cache: false,
        contentType: false,
        processData: false
    });
    
    request.done(function (res) {
        if (res.sign == 1) {
            $v.parent().parent().find(".slide-img:first").html('');
        } else {
            alert('图片删除失败：' + res.msg);
        }
    });
    
    request.fail(function (jqXHR, textStatus) {
        alert('Request failed: ' + textStatus);
    });
}
