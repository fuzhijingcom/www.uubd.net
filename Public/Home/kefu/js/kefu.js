$(window).ready(function(){
    $('.layim-chat-main').scrollTop(99999999999999999999999999999999999999999999999);
    setInterval("getMessage();",5000);
    setInterval("createTime();",180000)
    createTime();
});

$('#message').keyup(function(){
    if($('#message').val().trim() == ''){
        $('#send').addClass('layui-disabled');
    }else{
        $('#send').removeClass('layui-disabled');
    }
});

$('#message').change(function(){
    if($('#message').val().trim() == ''){
        $('#send').addClass('layui-disabled');
    }else{
        $('#send').removeClass('layui-disabled');
    }
});

$('#message').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
        $('#send').click();
    }
});

$('#send').click(function(){

    if($('#send').hasClass('layui-disabled')){
        return false;
    }
    var message = ue.getContent();
    if(!message){
        layer.msg('请输入内容再发送');
        return false;
    }

    $.ajax({
        url:URL + '/SendMsg',
        type:'post',
        data:{'text':message,'openid':openid},
        dataType:'json',
        success:function(res){
            alert(res);
            if(res.sign == '-1'){
                layer.msg(res.msg,{icon:2});
                return false;
            }

            ue.setContent('');

            var html = '<li class="layim-chat-li layim-chat-mine">';
                html += '<div class="layim-chat-user">';
                html += '<img src="' + face + '">';
                html += '<cite>' + name + '</cite></div>';
                html += '<div class="layim-chat-text">';
                html += res.message;
                html += '</div></li>';

            var message_list = $('#message-list').html();
            $('#message-list').html(message_list + html);
            $('.layim-chat-main').scrollTop(99999999999999999999999999999999999999999999999);
        },
        error:function(res){
            layer.msg('与服务器通信失败，网络出现错误',{time:3000,icon:2});
        }
    });

});

$('.layim-chat-title').on('click',function (e) {

    $.ajax({
        method : 'get',
        url : URL + '/getUserOrders',
        data : {'openid' : openid},
        dataType : 'json',
        success : function (res) {
            var common_img = PUBLIC + '/Common/img/logo.jpg';
            $('.order-pop .order-contain').html('');
            $.each(res,function (key,val) {
                var title_arr = val['title'].split('@');
                var order_item =    '<div class="order-item">'+
                                        '<p class="order-tid">' +
                                            '<span class="tid">' + val['tid'] + '-' + translateStatus(val['status']) + '</span>' +
                                            '<span class="time pull-right">' + val['created'] + '</span>' +
                                            '<div class="clearfix"></div>' +
                                        '</p>' +
                                        '<div class="order-img">'+
                                            '<img src="' + (val['img_url']?val['img_url']:common_img) + '">'+
                                        '</div>'+
                                        '<div class="order-info">'+
                                            '<p class="product-name">' + title_arr[0] + '</p>'+
                                            '<p class="product-describe">款型：<span class="style">' + title_arr[1] + '</span></p>'+
                                            '<p class="product-describe">套餐：<span class="combo">' + title_arr[2] + '</span></p>'+
                                        '</div>'+
                                        '<p class="product-price">合计：<span class="price">' + val['payment'] + '</span></p>'+
                                    '</div>';


                $('.order-pop .order-contain').append(order_item);
                $('.order-pop').show();
            });
        }
    })

});

$('.pop-box .pop-bg,.pop-box .close-btn').on('click',function (e) {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    $(target).parents('.pop-box').hide();
});

function translateStatus(status) {
    var tran_status = '';
    switch(status) {
        case 'WAIT_CONFIRM_OPTO':
        case 'WAIT_CONFIRM_ADDR':
        case 'WAIT_BUYER_PAY':
        case 'WAIT_PAY_RETURN':
            tran_status = '待付款';
            break;
        case 'WAIT_SELLER_SEND_GOODS':
            tran_status = '待发货';
            break;
        case 'WAIT_BUYER_CONFIRM_GOODS':
            tran_status = '已发货';
            break;
        case 'TRADE_BUYER_SIGNED':
            tran_status = '已完成';
            break;
        case 'TRADE_CLOSED':
        case 'TRADE_CLOSED_BY_USER':
        case 'delete':
            tran_status = '已关闭';
            break;
        default:
            tran_status = '状态未明';
            break;
    }
    return tran_status;
}

function getMessage(){
    $.ajax({
        url:URL+'/getMessage',
        type:'post',
        data:{'appkey':'66glasses','openid':openid},
        dataType:'json',
        success:function(res){
            if(res.sign == -1){
                return false;
            }

            $.each(res,function(key,val){
                if(val.is_send == '1'){
                    var html = '<li class="layim-chat-li">';
                    html += '<div class="layim-chat-user">';
                    html += '<img src="'+val.faceimg+'">';
                    html += '<cite>'+val.customer_name+'</cite></div>';
                    html += '<div class="layim-chat-text">';
                    html += val.message;
                    if(val.star == 0){
                        html += '(&nbsp;<a href="#" onclick="changeStar(this)">加星</a> | ';
                    }else{
                        html += '<a href="#" onclick="changeStar(this)">去星</a> | ';
                    }
                    if(val.remark == ''){
                        html += '<a href="#" onclick="changeRemark(this)">备注</a>&nbsp;)';
                    }else{
                        html += '<a href="#" onclick="changeRemark(this)">取消备注</a>&nbsp;)';
                    }
                    html += '</div></li>';

                }else{
                    var html = '<li class="layim-chat-li layim-chat-mine">';
                    html += '<div class="layim-chat-user">';
                    html += '<img src="' + face + '">';
                    html += '<cite>' + name + '</cite></div>';
                    html += '<div class="layim-chat-text">';
                    html += val.message;
                    html += '</div></li>';
                }

                var message_list = $('#message-list').html();
                $('#message-list').html(message_list + html);
                $('.layim-chat-main').scrollTop(99999999999999999999999999999999999999999999999);
            });

            if(res[0]){
                if(res[0].message){

                    $('#noticeMsg').html('');
                    if($.browser.msie && $.browser.version=='8.0'){
                        //本来这里用的是<bgsound src="system.wav"/>,结果IE8不播放声音,于是换成了embed
                        $('#noticeMsg').html('<embed src="'+PUBLIC+'/Common/music/notice.mp3"/>');
                    }else{
                        //IE9+,Firefox,Chrome均支持<audio/>
                        $('#noticeMsg').html('<audio autoplay="autoplay"><source src="system.wav"'
                            + 'type="audio/wav"/><source src="'+PUBLIC+'/Common/music/notice.mp3" type="audio/mpeg"/></audio>');
                    }
                }

            }

        }
    });
}

function changeStar(obj){
    var id = $(obj).parent().attr('id');
    $.ajax({
        url:URL + '/changeStar',
        type:'post',
        data:{'id':id},
        dataType:'json',
        success:function(res){
            if(res.sign == -1){
                layer.msg(res.msg,{time:3000,icon:2});
            }else{
                $('#'+id).find('a:first').html(res.text);
            }

        },error:function(res){
            layer.msg('与服务器通信失败，网络出现错误',{time:3000,icon:2});
        }
    });
}

function changeRemark(obj) {
    if($(obj).html() == '备注'){
        layer.prompt({title: '添加备注信息', formType: 2}, function(text, index){
            layer.close(index);
            var id = $(obj).parent().attr('id');
            $.ajax({
                url: URL + '/changeRemark',
                type: 'post',
                data: {'id': id,'text':text},
                dataType: 'json',
                success: function (res) {
                    if (res.sign == -1) {
                        layer.msg(res.msg, {time: 3000, icon: 2});
                    } else {
                        $('#' + id).find('a:last').html(res.text);
                    }

                }, error: function (res) {
                    layer.msg('与服务器通信失败，网络出现错误', {time: 3000, icon: 2});
                }
            });
        });
    }else{
        var id = $(obj).parent().attr('id');
        $.ajax({
            url: URL + '/changeRemark',
            type: 'post',
            data: {'id': id,'text':''},
            dataType: 'json',
            success: function (res) {
                if (res.sign == -1) {
                    layer.msg(res.msg, {time: 3000, icon: 2});
                } else {
                    $('#' + id).find('a:last').html(res.text);
                }

            }, error: function (res) {
                layer.msg('与服务器通信失败，网络出现错误', {time: 3000, icon: 2});
            }
        });
    }


}

function createTime(){
    var date = new Date();
    s = date.getFullYear() + "-" +
        (date.getMonth()+1) + "-" +
        date.getDate() + " " +
        date.getHours() + ":" +
        date.getMinutes() + ":" +
        date.getSeconds();

    time = '<li class="layim-chat-system">';
    time += '<span>'+time+'</span>';
    time += '</li>';
}



function addQuickReply(){
    layer.prompt({title: '添加快捷回复', formType: 3}, function(text, index){
        layer.close(index);
        $.ajax({
            url : URL + '/addQuickReply',
            type:'post',
            data:{'text':text},
            dataType:'json',
            success:function(res){
                if(res.sign == -1){
                    layer.msg(res.msg ,{time: 3000, icon: 2});
                }else{
                    var _html = $('.dropdown-menu').html();
                    var html = '<li><a href="#" onclick="quickReplay(this)">'+text+'</a></li>';
                    $('.dropdown-menu').html(html + _html);
                }
            },
            error:function(res){
                layer.msg('与服务器通信失败，网络出现错误', {time: 3000, icon: 2});
            }
        });
    });

}


function quickReplay(obj){
    var message = $(obj).html();
    if(message == ''){
        layer.msg('请输入内容再发送');
        return false;
    }

    $.ajax({
        url:URL + '/SendMsg',
        type:'post',
        data:{'text':message,'openid':openid},
        dataType:'json',
        success:function(res){
            if(res.sign == '-1'){
                layer.msg(res.msg,{icon:2});
                return false;
            }

            var html = '<li class="layim-chat-li layim-chat-mine">';
            html += '<div class="layim-chat-user">';
            html += '<img src="' + face + '">';
            html += '<cite>' + name + '</cite></div>';
            html += '<div class="layim-chat-text">';
            html += res.message;
            html += '</div></li>';

            var message_list = $('#message-list').html();
            $('#message-list').html(message_list + html);
            $('.layim-chat-main').scrollTop(99999999999999999999999999999999999999999999999);
        },
        error:function(res){
            layer.msg('与服务器通信失败，网络出现错误',{time:3000,icon:2});
        }
    });
}

