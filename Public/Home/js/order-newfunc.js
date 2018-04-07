var component = {
    'spec-code' : '<form class="form-inline" style="width: 150px">' +
                    '<input type="text" class="form-control spec-code-input" placeholder="特权码">' +
                    '<a class="btn btn-primary" href="javascript:void(0)" onclick="submitSpecCode(this)">提交</a>' +
                '</form>',

};

$(window).bind('click',function (e) {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    if(!$(target).parents('.popover').get(0)
        && !$(target).hasClass('popover')
        && !$(target).hasClass('seller-remark')
        && !$(target).hasClass('spec-code')
        && !$(target).hasClass('confirm-pay')
        && !$(target).hasClass('popoverBtn')) {
        $('.spec-code').popover('hide');
        $('.seller-remark').popover('hide');
        $('.confirm-pay').popover('hide');
        $('.popoverBtn').popover('hide');
    }
});

$('.spec-code').on('click',function (e) {
    $(this).popover('show');
});
$('.spec-code').popover({
    trigger : 'manual',
    title : '请填写特权码',
    html : true,
    content : component['spec-code']
});

function submitSpecCode(obj) {
    var spec_code = $(obj).siblings('.spec-code-input').val();
    var tid = $(obj).parents('.header-row').attr('data-tid');

    var ajaxData = {
        'spec_code' : spec_code,
        'tid' : tid
    }

    $.ajax({
        method : 'post',
        url : URL + '/addSpecCode',
        data : ajaxData,
        dataType : 'json',
        success : function (res) {
            if(res['sign'] == 1) {
                $(obj).parents('.popover').siblings('.c-gray').html(spec_code + '(' + res['service'].split('@')[0] + ')');
                $(obj).parents('.popover').siblings('.spec-code').popover('hide');
            }
            alert(res['msg']);
        }
    })
}
