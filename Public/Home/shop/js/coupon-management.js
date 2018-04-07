// 优惠券管理相关的 js

// 切换优惠券菜单后初始化内容

function initCouponList() {
    var request = $.ajax({
        url: URL + '/couponList',
        method: "POST",
        data: {search_q: ''},
        dataType: "json"
    });

    request.done(function (res) {
        if (res.sign == 1) {
            initCouponTable(res.result);
        } else {
            alert(res.msg);
        }
    });

    request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
    });
}

// 初始化表格内容
function initCouponTable(data) {
    // 表格头
    var tableTitle = '<tr>' +
        '<th style="display: none;">优惠券id</th>' +
        '<th>优惠券名称</th>' +
        '<th>适用商品类型</th>' +
        '<th>面额</th>' +
        '<th>最低消费金额</th>' +
        '<th>生效时间</th>' +
        '<th>失效时间</th>' +
        '<th>是否有效</th>' +
        '<th>可领取数量</th>' +
        '<th>能否叠加使用</th>' +
        '<th>操作</th>' +
        '</tr>';

    var couponContent = '';
    for (i in data) {
        couponContent += '<tr><td style="display: none;">' + data[i]['coupon_id'] + '</td>' +
            '<td>' + data[i]['coupon_name'] + '</td>' +
            '<td>' + data[i]['goods_type'] + '</td>' +
            '<td>' + data[i]['coupon_price'] + '</td>' +
            '<td>' + data[i]['coupon_condition'] + '</td>' +
            '<td>' + data[i]['available_time'] + '</td>' +
            '<td>' + data[i]['invalid_time'] + '</td>' +
            '<td>' + data[i]['is_valid'] + '</td>' +
            '<td>' + data[i]['num_per_user'] + '</td>' +
            '<td>' + data[i]['superimposed'] + '</td>' +
            '<td><a class="btn btn-info" href="javascript:void(0)" onclick="editCouponClick(this, \''+ data[i]['coupon_id'] +'\')">编辑</a>  <a class="btn btn-danger" href="javascript:void(0)" onclick="removeCoupon(this, \''+ data[i]['coupon_id'] +'\')">删除</a></td>' +
            '</tr>';
    }

    var html = '<table class="table table-bordered"><thead>' + tableTitle + '</thead><tbody>' + couponContent + '</tbody></table>';
    $("#coupon-table").html(html);

    $("table").tablesorter({debug: false, widgets: ['zebra']});
}

// 新增优惠券弹窗
function addCouponClick() {
    $("#coupon-modal-label").text("新增优惠券");
    $('#coupon-modal').modal();
    $('#coupon-modal').on('shown.bs.modal', function (e) {
        cleanForm();
        initAddCoupon();
    })
}

// 编辑优惠券弹窗
// 需要将已有信息填入到表单中
function editCouponClick(e, coupon_id) {
    var request = $.ajax({
        url: URL + '/getSpecCoupon',
        method: 'GET',
        data: {coupon_id: coupon_id},
        dataType: 'json'
    });

    request.done(function (res) {
        if (res.sign == 1) {
            var data = res.result;
            $("#coupon-modal-label").text("编辑优惠券");

            $('#coupon-modal').on('shown.bs.modal', function (e) {
                $('#coupon_id').val(data.coupon_id);
                $('#coupon_name').val(data.coupon_name);
                $('#coupon_price').val(data.coupon_price);
                $('#coupon_condition').val(data.coupon_condition);
                $('#available_time').val(data.available_time);
                $('#invalid_time').val(data.invalid_time);
                $('#num_per_user').val(data.num_per_user);

                $('input:checkbox[name=goods_type]').each(function () {
                    if (data.goods_type.indexOf($(this).val()) >= 0) {
                        $(this).prop("checked", true);
                    } else {
                        $(this).prop("checked", false);
                    }
                });

                var $is_valid_radios = $('input:radio[name=is_valid]');
                $is_valid_radios.filter('[value="' + data.is_valid + '"]').attr('checked', true);

                var $superimposed_radios = $('input:radio[name=superimposed]');
                $superimposed_radios.filter('[value="' + data.superimposed + '"]').attr('checked', true);
            });
            $('#coupon-modal').modal();

        } else {
            alert(res.msg);
            return false;
        }
    })

    request.fail(function (jqXHR, textStatus) {
        alert('Request failed: ' + textStatus);
    })
}

// 提交优惠券信息
function submitCoupon() {
    var coupon_id = $('#coupon_id').val();
    var coupon_name = $('#coupon_name').val();
    var coupon_price = $('#coupon_price').val();
    var coupon_condition = $('#coupon_condition').val();
    var available_time = $('#available_time').val();
    var invalid_time = $('#invalid_time').val();
    var num_per_user = $('#num_per_user').val();

    var goods_type = '';
    $("[name = goods_type]:checkbox").each(function () {
        if ($(this).is(":checked")) {
            goods_type += $(this).val();
        }
    });

    var is_valid = $('input:radio[name=is_valid]:checked').val();
    var superimposed = $('input:radio[name=superimposed]:checked').val();


    var data = {
        coupon_id: coupon_id,
        coupon_name: coupon_name,
        goods_type: goods_type,
        coupon_price: coupon_price,
        coupon_condition: coupon_condition,
        available_time: available_time,
        invalid_time: invalid_time,
        is_valid: is_valid,
        num_per_user: num_per_user,
        superimposed: superimposed
    };

    var postUrl;
    if (coupon_id == '') {
        // 新增情况
        postUrl = URL + '/addCoupon';
    } else {
        // 编辑情况
        postUrl = URL + '/editCoupon';
    }

    var request = $.ajax({
        url: postUrl,
        method: 'POST',
        data: data,
        dataType: 'json'
    });

    request.done(function (res) {
        if (res.sign == 1) {
            cleanForm();
            initCouponList();
        } else {
            alert(res.msg);
        }
    });

    request.fail(function (jqXHR, textStatus) {
        alert("Request failed: " + textStatus);
    })
}

// 清除表单内容
function cleanForm() {
    $('#coupon_id').val('');
    $('#coupon_name').val('');
    $('#goods_type').val('');
    $('#coupon_price').val('');
    $('#coupon_condition').val('');
    $('#available_time').val('');
    $('#invalid_time').val('');
    $('#num_per_user').val('');


    $('input:checkbox[name=goods_type]').attr("checked", false);

    var $is_valid_radios = $('input:radio[name=is_valid]');
    $is_valid_radios.filter('[value="1"]').attr('checked', true);

    var $superimposed_radios = $('input:radio[name=superimposed]');
    $superimposed_radios.filter('[value="0"]').attr('checked', true);
}

// 新增表单时初始化内容
function initAddCoupon() {
    var d = new Date();
    var strTime = d.getFullYear() + "-" + ("0" + (d.getMonth() + 1)).slice(-2) + "-" + ("0" + d.getDate()).slice(-2) + 'T00:00';
    console.log(strTime);

    $('#num_per_user').val('1');
    $('#available_time').val(strTime);
    $('#invalid_time').val(strTime);

    $('input:checkbox[name=goods_type]').attr("checked", false);

    var $is_valid_radios = $('input:radio[name=is_valid]');
    $is_valid_radios.filter('[value="1"]').attr('checked', true);

    var $superimposed_radios = $('input:radio[name=superimposed]');
    $superimposed_radios.filter('[value="0"]').attr('checked', true);

}

// 删除优惠券
function removeCoupon(e, coupon_id) {
    var msg = "您真的确定要删除吗？\n\n请确认！";
    if (confirm(msg) == false){
        return false;
    }

    var request = $.ajax({
        url: URL + '/removeCoupon',
        method: 'POST',
        data: {coupon_id: coupon_id},
        dataType: 'json'
    });

    request.done(function (res) {
        if (res.sign == 1) {
            var $v = $(e);
            $v.parent().parent().remove();
        } else {
            alert(res.msg);
        }
    });

    request.fail(function (jqXHR, textStatus) {
        alert('Request failed: ' + '网络不稳定，请稍后重试...');
    })
}