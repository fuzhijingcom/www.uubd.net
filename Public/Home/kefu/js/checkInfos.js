/**
 * Created by xingact on 17-2-9.
 */

$(document).ready(function () {
    //初始化未读消息板块
    getDiffInfo('unread','unread_box')();

    //绑定选项卡事件
    $('.choose-info-type>li').on('click',changeInfoType);

    //绑定聊天框关闭按钮
    $('.chat-board .close-btn').on('click',function (e) {
        chatbox_contain = $('.chat-board').detach();
        $('.info-board').removeClass('width-50');
    });

    //每隔１０秒检查数据是否更新
    setInterval(getDiffInfo('unread',old_unread_data),5000);
});

//改变消息类型
function changeInfoType() {
    var info_type = $(this).attr('data-info-type');
    var boxId = info_type + '_box';
    $('.info-display').attr('id',boxId);

    getDiffInfo(info_type,boxId)();

    $(this).siblings('.active').removeClass('active');
    $(this).addClass('active');
}

//渲染某一类信息
function drawCertainInfo(boxId,data,len) {
    var cur_box = $('#' + boxId);
    if(boxId == 'unread_box') {
        drawNumPoint($('.unread'),len);
    }

    if(!cur_box.get(0)) return false;

    // console.log(data);
    cur_box.html('');
    if(data.length == 0) {
        cur_box.html('<p class="text-center">暂无此类消息...</p>');
        return;
    }
    $.each(data, function (key,val) {
        var info_item = '<li class="info-item" data-openid="' + val['customer'] + '" onclick="toCertainChat(this);">' +
                            ((boxId == 'unread_box')?('<div class="num-point">' + val['length'] + '</div>'):'')+
                            '<div class="user-avatar">' +
                                '<img src="' + val['avatar'] + '">' +
                            '</div>' +
                            '<div class="info-content">' +
                                '<p class="user-nickname">' + val['customer_name'] + '</p>' +
                                '<p class="info-text">' + val['message'] + '</p>' +
                            '</div>' +
                            '<p class="info-time">' + val['time'] + '</p>' +
                            ((val['remark'])?('<p class="remark">备注：' + val['remark'] + '</p>'):'') +
                            '<div class="clearfix"></div>' +
                        '</li>';

        cur_box.append(info_item);
    });
}

//渲染消息数目
function drawNumPoint(obj,length) {
    if(!length) {
        obj.find('a').html('未读消息');
        return false;
    }

    length = (length>=100)?'99+':length;
    var num_point = '<span class="num-point">' + length + '</span>';
    obj.find('a').html('未读消息' + num_point);
}

//打开聊天框
function toCertainChat(obj) {
    var info_type = $(obj).parent().attr('id');
    var openid = $(obj).attr('data-openid');
    var chat_link = URL + '/kefu?openid=' + openid;

    if(chatbox_contain) {
        $('.info-board').addClass('width-50');
        $('.info-board').after(chatbox_contain);
    }

    //检测聊天框是否已展开
    if($('.chat-board').hasClass('hide')) {
        $('.chat-board').removeClass('hide');
        $('.info-board').addClass('width-50');
    }

    if($('#chat_box').attr('src') != chat_link)
            $('#chat_box').attr('src',chat_link);

    if(info_type == 'unread_box')
        setTimeout(getDiffInfo('unread','unread_box'),500);
}

//获取不同消息的闭包
function getDiffInfo(info_type,boxId) {
    return function _getDiffInfo() {

        var ajaxData = {
            'info_type' : info_type
        }

        $.ajax({
            method : 'post',
            url : URL + '/getDiffInfo',
            data : ajaxData,
            dataType : 'json',
            success : function (res) {
                if(info_type == 'unread' && old_unread_data) {
                    checkIsChange(res);
                    return;
                }

                drawCertainInfo(boxId,res['data'],res['length']);

                if(info_type == 'unread'){
                    old_unread_data = res;
                }
            }
        });
    }
}

//检查数据是否改变
function checkIsChange(data) {

    var boxId = 'unread_box';
    drawCertainInfo(boxId,data['data'],data['length']);

    if(!Compare(data,old_unread_data)) {
        if(old_unread_data['length'] <data['length']) {
            if(!$('.chat-board').hasClass('hide') || !$('#' + boxId).get(0))
                    document.getElementById('new-warn').play();
        }
        old_unread_data = data;
    }

}

