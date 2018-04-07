//标记当前选中套餐
var current_combo_id = null;

$(document).ready(function(){
    getEval();
    selectStyle($('#SkuColor .onclick').get(0),$('#SkuColor .onclick').attr('title'));
    current_combo_id = $('#select-combo .onclick').attr('title');
});


function selectStyle(object,sku_id){
    if($(object).hasClass('disabled')){
        return false;
    }
    $(object).siblings().removeAttr('id');
    $(object).attr('id','selectStyle');

    var select_combo = $('#select-combo');
    var _html = '<ul class="color-list clearfix">';
    var glass_price = parseInt(g_info['single_price']);
    $.each(g_info.g_detail,function(key,val){
        if(val.attr_id == sku_id){
            //获取镜片单价消息
            var singlePriceArr = val['singlePrice'];
            $.each(val.combo_detail,function(key1,val1){
                console.log(val1);
                //过滤套餐名
                var combo_name = val1[0].combo_name.split('非球面').join('');
                combo_name = combo_name.replace('蓝光膜','蓝光');
                var combo_img_url = val1[0]['img_url']?val1[0]['img_url'] : (PUBLIC + "/Weixin/shop/img/public/logo.jpg");

                //套餐内容的HTML
                var glass_img = '<div class="glass-img"><img src="' + PUBLIC + '/Weixin/shop/img/goods_com.jpg"></div>';
                var plus_item = '<span class="plus-item">+</span>';
                var combo_img = '<div class="combo-img"><img src="' + combo_img_url + '"></div>';
                var good_info = '<div class="good-info">' +
                    '<p class="style-name">' + $(object).html() + '</p>' +
                    '<p class="combo-name">' + combo_name + '</p>' +
                    '<p class="combo-price"><span class="cur-price">￥' + val1[0].combo_price + '</span><span class="ori-price">￥' + (glass_price + parseInt(singlePriceArr[key1])) +'</span></p>' +
                    '</div>';

                _html += '<li ' + ((key1 == 0) ?'class = "onclick"': 'style="display:none;"') +'onclick="selectCombo(this)" title="'+val1[0].combo_id+'" price="'+val1[0].combo_price+'">'+ glass_img + plus_item + combo_img + good_info +'</li>';
            });
            _html += '<li style="display: none;" onclick="selectCombo(this)" title="0" price="'+ g_info['single_price'] +'">' +
                '<div class="glass-img"><img src="' + PUBLIC + '/Weixin/shop/img/goods_com.jpg"></div>' +
                '<div class="good-info">' +
                    '<p class="style-name">' + $(object).html() + '</p>' +
                    '<p class="combo-name">单品</p>' +
                    '<p class="combo-price"><span class="cur-price">￥' + g_info['single_price'] + '</span></p>' +
                    '</div>'
                '</li>' ;

        }
    });
    _html += '</ul>';
    select_combo.html(_html);
    //更换款式套餐不换
    $('#select-combo .color-list li').each(
        function () {
            if(this.title == current_combo_id) {
                $('#select-combo .color-list li').removeClass('onclick').hide();
                $(this).show().addClass('onclick');
            }
        }
    );
    //保证图标显示正常
    $('span.up').hide();
    $('span.down').show();
    //更新款型图片
    changeGlassImg(sku_id);

    //选中款型时
    $('#selectStyle').attr('title',sku_id);
}

// function showOption(){
//     var sku_id = $('#selectStyle').attr('title');
//     if($('#selectCombo').length>0){
//         return false;
//     }
//     var selectCombo = $('#select-combo');
//     var _html = '<ul class="color-list clearfix">';
//     $.each(g_info.g_detail,function(key,val){
//         if(val.attr_id == sku_id){
//             $.each(val.combo_detail,function(key1,val1){
//                 if(selectCombo.hasClass('hide')){
//                     selectCombo.removeClass('hide');
//                 }
//                 var combo_name = val1[0].combo_name.split('非球面').join('');
//                 combo_name = combo_name.replace('蓝光膜','蓝光');
//                 _html += '<li onclick="selectCombo(this)" title="'+val1[0].combo_id+'" price="'+val1[0].combo_price+'">'+ combo_name +'</li>';
//             });
//         }
//
//     });
//     _html += '</ul>';
//     selectCombo.html(_html);
//
// }

function selectCombo(object){
    $(object).siblings().removeClass('onclick');
    $(object).addClass('onclick');
    $(object).siblings().removeAttr('id');
    $(object).attr('id','selectCombo');
    // var _html = '优惠价：¥ '+$(object).attr('price');
    // $('.current-pri').html(_html);
    //点击时把套餐列表折叠并滑动回点击处
    toggleOptions($('.combo-box .select-body-title').get(0));
    window.scrollTo(0,(choose_box_height-head_height));
    //保存当前选中combo_id
    current_combo_id = $(object).attr('title');
}

//改变眼镜图片
function changeGlassImg(sku_id) {

    var ajaxData = {
        'sku_id' : sku_id,
    }

    $.ajax({
        url:URL+'/getGlassImg',
        type:'post',
        data:ajaxData,
        dataType:'json',
        success:function(res){
            if(res.sign == 1) {
                $('#select-combo .glass-img img').attr('src',res['img_url']);
            }
        },
        error:function(res){
            alert('貌似网络有点小问题哦~');
        }
    })
}

//展开折叠函数
function toggleOptions(obj) {
    var option_body = $(obj).next();

    $(obj).find('.up').toggle();
    $(obj).find('.down').toggle();

    option_body.find('li').toggle();
    if(option_body.find('.onclick').get(0))
        option_body.find('.onclick').show();
    else{
        option_body.find('li').eq(0).show();
    }
}

function getValue(){
    var g_id     = $('#selectStyle').attr('title');
    var attr_id  = $('#selectStyle').attr('title');
    var combo_id = $('#select-combo .onclick').attr('title')?$('#select-combo .onclick').attr('title'):0;
    var store_id = 10;//门店
    var leg_id   = 0;//镜腿
    var num      = 1;//数量
    var type     = 1;//默认or现品
    var custom   = 0;//定制
    var coupon_id = coupon_id?coupon_id:0;//优惠券
    var source   = $('#source').val();


    ajaxData = {
        'g_id'      :   g_id,
        'attr_id'   :   attr_id,
        'combo_id'  :   combo_id,
        'store_id'  :   store_id,
        'leg_id'    :   leg_id,
        'num'       :   num,
        'type'      :   type,
        'custom'    :   custom,
        'coupon_id' :   coupon_id,
        'source'    :   source
    };

    return ajaxData;
}


function pay(){
    if(no_wechat){
        alert('请在微信中打开！');return false;
    }
    var ajaxData = getValue();

    $.ajax({
        url:URL+'/pay',
        type:'post',
        data:{'data':ajaxData},
        dataType:'json',
        success:function(res){
            if(res.sign === 1){
                location.href = res.url;
            }else{
                alert(res.msg);
            }
        },
        error:function(res){
            alert('貌似网络有点小问题哦~');
        }
    })

}


function addCart(){
    if(no_wechat){
        alert('请在微信中打开！');return false;
    }
    var ajaxData = getValue();
    console.log(URL);
    $.ajax({
        url:URL + '/addCart',
        type:'post',
        data:{'data':ajaxData},
        dataType:'json',
        success:function(res){
            alert(res.msg);
        },
        error:function(res){
            alert('貌似网络有点小问题哦~');
        }
    });
}

function evalSubmit(obj){
    var content = $('#eval-content').val();
    var sku_id = $('#selectStyle').attr('title');
    if(!content){
        alert('输入内容呀~');
        return false;
    }
    if(no_wechat){
        alert('请在微信中打开！');return false;
    }
    $(obj).attr('disabled','disabled');

    $.ajax({
        url:URL+'/evalSubmit',
        type:'post',
        data:{'sku_id':sku_id,'content':content},
        dataType:'json',
        success:function(res){
            if(res.sign==-1){
                alert(res.msg);
            }else{
                $('#eval-content').val('');
                getEval();
            }
        },
        error:function(res){
            alert('貌似网络有点小问题哦~')
        }
    });
    $(obj).removeAttr('disabled');
}

function getEval(){
    var sku_id = $('#selectStyle').attr('title');
    $.ajax({
        url:URL+'/getEval',
        type:'post',
        data:{'sku_id':sku_id},
        dataType:'json',
        success:function(res){
            if(res.sign == -1){
                var _html = '<li class="eval-null">暂无评价</li>';
            }else{
                var _html = '';
                $.each(res,function(index,val){
                    _html += '<li class="comment-item">';
                    _html += '<p class="comment-content">'+val.content+'</p>';
                    _html += '<p class="comment-info">';
                    _html += '<span class="comment-name">'+val.username+'</span>';
                    _html += '<span class="comment-time">'+val.datetime+'</span>';
                    _html += '<div class="clear"></div>';
                    _html += '</p>';
                    _html += '</li>';
                });
            }
            $('.comment-box').html(_html);
        },
        error:function(){

        },
    });
}