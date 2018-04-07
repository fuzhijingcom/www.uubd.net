
$(document).ready(function () {
    initPageData(pageInfo);
    //记录原始未修改版本
    oldInfo = arrangeData();
    oldInfo['ctrlData'] = pageInfo['ctrlData'];
    // console.log(oldInfo);

    initEvent();

    $('#all_ctrl_control').sortable({
        update:function () {
            submitData(arrangeData(),1);
        },
    });
    $('.form').sortable();
});

/*
 *渲染页面部分
 */
function initPageData(data) {
    // console.log(data);
    $('#pageDisplay').attr('src',cur_show_url);
    $('.page-header h2').html(data['title'] + '<small>  ——编辑页面</small>')
    $('#page_title').val(data['title']);
    $('#page_name').val(data['name']);
    $('#page_bgcolor').val(data['bgcolor']);

    if(!data['ctrlData']) return false;
    // console.log(data);
    // var ctrl_body = $('#all_ctrl_control');
    var ctrl_body = $('#all_ctrl_control').html('');
    $.each(data['ctrlData'],function (key,val) {
            var item =  '<li class="ctrl-item" data-type="' + val['type'] + '" onmouseover="showInvisibleBtn(this,1)" onmouseout="showInvisibleBtn(this,0)" id="initCtrl'+ key +'">'+
                            ctrl_components[val['type']] +
                        '</li>';
            var item_id = '#initCtrl' + key;

            ctrl_body.append(item);

            var form = $(item_id).find('.form');
            form.html('');
            if(typeof val['value'] == 'object') {
                initDataGather(val['type'],val['value'],form);
                // $.each(val['value'],function (key1,val1) {
                //     console.log(val1);
                //     var input = '<input type="text" class="form-control" value="' + val1 + '" placeholder="填入tab选项名及链接">';
                //     form.append(input);
                // });
            }else {
                var input = '';
                if(val['type'] == 'title-item') {
                    initDataGather(val['type'],val['value'],form);
                }else {
                    input = '<input type="text" class="form-control submit-input" value="' + val['value'] + '">';
                    form.append(input);
                }

            }

    });

    //初始化弹出框事件
    $.each($('.add-ctrl-btn'),function (key,val) {
        initPopover($(val),pop_components['ctrl-board'],'请选择控件');
    });

    $.each($('.title-style-btn'),function (key,val) {
        initPopover($(val),pop_components['style-board'],'请选择样式');
    });

    //初始化所有小插件功能
    initAllSpecFunc();
}

//渲染数据收集的div
function initDataGather(type,val,obj) {
    if(type == 'tab-item') {
        $.each(val,function (key1,val1) {
            var val1_arr = val1.split(':',2);
            var dataGather =    '<div class="data-gather">' +
                                    '<input type="text" class="form-control title-type-input" style="width: 15%;margin-right: 10px" value="' + val1_arr[0] + '" placeholder="标题">' +
                                    '<div class="form-inline get-link-data">' +
                                        '<span>链接：</span><input type="text" class="form-control link-input" value="' + (val1_arr[1]?val1_arr[1]:'') + '" placeholder="请填写外链或选择页面">' +
                                        '<button class="choose-pages-btn btn btn-success">选择页面</button>' +
                                    '</div>' +
                                    '<input type="hidden" class="form-control submit-input" value="' + val1 + '">' +
                                '</div>';
            obj.append(dataGather);
        });
    }else if(type == 'title-item') {
        var val_arr = val.split(':',2);
        var val_arr1 = (val_arr.length > 1) ? val_arr[1].split('-',2) : ['s','left'];
        var style_type = (val_arr1[0] == 's')?'小标题' : '大标题';
        var align_way = (val_arr1[1]) ? ('.title-'+val_arr1[1]) : '.title-left';

        var dataGather = '<div class="data-gather">' +
                            '<input type="text" class="form-control title-type-input" style="width: 30%;margin-right: 10%" placeholder="标题" value="' + val_arr[0] + '">' +
                            '<div class="form-inline get-title-style">' +
                                '<a role="button" class="title-style-btn btn btn-success" style="margin-right: 1%" data-style="' + val_arr1[0] + '">' + style_type + '</a>' +
                                '<div class="btn-group choose-align" role="group" aria-label="...">' +
                                    '<button type="button" class="btn btn-default title-left" data-align="left"><i class="glyphicon glyphicon-align-left"></i></button>' +
                                    '<button type="button" class="btn btn-default title-center" data-align="center"><i class="glyphicon glyphicon-align-center"></i></button>' +
                                    '<button type="button" class="btn btn-default title-right" data-align="right"><i class="glyphicon glyphicon-align-right"></i></button>' +
                                '</div>' +
                            '</div>' +
                            '<input type="hidden" class="form-control submit-input" value="' + val + '">' +
                        '</div>';
        obj.append(dataGather);
        obj.find(align_way).addClass('active');
    }else {
        $.each(val,function (key1,val1) {
            var val1_arr = val1.split(':',2);
            var dataGather =    '<div class="data-gather">' +
                                '<img src="' + SITE_URL + '/' + val1_arr[0] + ' "data-src="' + val1_arr[0] + '" class="img-choose-btn" style="width: 34px;height: 34px;cursor: pointer">' +
                                    '<div class="form-inline get-link-data">' +
                                        '<span>链接：</span><input type="text" class="form-control link-input" value="' + (val1_arr[1]?val1_arr[1]:'') + '" placeholder="请填写外链或选择页面">' +
                                        '<button class="choose-pages-btn btn btn-success">选择页面</button>' +
                                    '</div>' +
                                    '<input type="hidden" class="form-control submit-input" value="' + val1 + '">' +
                                '</div>';
            obj.append(dataGather);
        });
    }
}

/*
 *渲染弹出框内容部分
 */
function drawPageList(data) {
    var page_list = $('.choose-page-pop .pop-list-group');
    page_list.html('');

    $.each(data,function (key,val) {
        var page_item = '<li class="page-list-item" data-name="' + val['name'] + '">' + val['title'] + '</li>';
        page_list.append(page_item);
    });

    $('.choose-page-pop').show();

    $('.page-list-item').on('click',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;
        var link = 'index.php/Weixin/Shop/index?page=' + $(target).attr('data-name');

        $('#choosing_page').siblings('.link-input').val(link);
        arrangeSubmitInput($('#choosing_page'));
        $('#choosing_page').attr('id','');
        $('.choose-page-pop').hide();
    });
}

function drawImgGroupList(data) {
    var img_group_list = $('.choose-img-pop .pop-list-group');
    img_group_list.html('');

    $.each(data,function (key,val) {
        var img_group_item = '<li class="img-group-item ' + ((key==0)?"active":"") + '" data-index="' + key + '">' + val['group_name'] + '</li>';
        img_group_list.append(img_group_item);
    });
    drawImgList(0);
    $('.choose-img-pop').show();

    $('.img-group-item').on('click',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;
        var group_index = $(target).attr('data-index');

        $(target).siblings('.active').removeClass('active');
        $(target).addClass('active');
        drawImgList(group_index);
    });
}

function drawImgList(group_index) {
    var img_list = $('.choose-img-pop .img-board');
    img_list.html('');

    $.each(imgList[group_index]['img_group'],function (key,val) {
        var img_item = '<img class="img-option" src="' + SITE_URL + '/' + val['relative_url'] + '" data-src="' + val['relative_url'] + '">';
        img_list.append(img_item);
    });

    $('.img-option').on('click',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;
        var relative_url = $(target).attr('data-src');
        var absolute_url = SITE_URL + '/' + relative_url;

        $('#choosing_img').attr('data-src',relative_url);
        $('#choosing_img').attr('src',absolute_url);
        arrangeSubmitInput($('#choosing_img'));
        $('#choosing_img').attr('id','');
        $('.choose-img-pop').hide();
    });

}

/*
 *页面各按键功能
 */
//添加或删除选项
function addOrDeleteInput(e) {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    var inputs = $(target).parents('.ctrl-item-title').siblings('.form');
    var type = $(target).parents('.ctrl-item').attr('data-type');
    console.log(type);

    if($(target).hasClass('plus-btn')) {
        var input = (type == 'tab-item') ? other_components['title-data-gather'] : other_components['img-data-gather'];
        // var input = '<input type="text" class="form-control">';
        inputs.append(input);

        //初始化所有小插件功能
        initAllSpecFunc();
    }else {
        if(inputs.children().length == 1) return false;
        inputs.children(':last').detach();
    }
}

//插入空白控件
function addBlankCtrl(ctrl_name,obj) {
    var cur_ctrl =  '<li id="new_ctrl" class="ctrl-item" data-type="' + ctrl_name + '" onmouseover="showInvisibleBtn(this,1)" onmouseout="showInvisibleBtn(this,0)">'+
                        ctrl_components[ctrl_name] +
                    '</li>';;

    if(!obj){
        $('#all_ctrl_control').append(cur_ctrl);
    }else {
        obj.after(cur_ctrl);
    }

    //初始化弹出框事件
    initPopover($('#new_ctrl>.add-ctrl-btn'),pop_components['ctrl-board'],'请选择控件');
    initPopover($('#new_ctrl .title-style-btn'),pop_components['style-board'],'请选择样式');

    $('#new_ctrl').attr('id','');

    //初始化所有小插件功能
    initAllSpecFunc();
}

//删除当前控件
function deleteCurItem(e) {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    $(target).parents('.ctrl-item').detach();
}

//显示隐藏按钮
function showInvisibleBtn(obj,func) {
    if(func) {
        $(obj).find('.invisible-btn').show();
    }else {
        $(obj).find('.invisible-btn').hide();
    }
}

/*
 *绑定事件
 */
function initEvent() {
    $('#btn_submit').bind('click',function () {
        submitData(arrangeData());
    });
    $('#btn_cancel').bind('click',function () {
        if(!confirm('确定要取消？取消会还原至编辑前的数据！')) return false;
        submitData(oldInfo);
    });

    $('.pop-bg').on('click',function (e) {
        var event = window.event || e;
        var target = event.srcElement || target;

        $(target).parents('.pop-box').hide();
    });

    $(window).bind('click',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;
        target = $(target);

        if (!target.hasClass('popover')
            && !target.hasClass('add-ctrl-btn')
            && !target.hasClass('title-style-btn')
            && !target.hasClass('popover-content')
            && !target.hasClass('popover-title')
            && !target.hasClass('arrow')) {
            $('.add-ctrl-btn').popover('hide');
            $('.title-style-btn').popover('hide');// 当点击body的非弹出框相关的内容的时候，关闭所有popover
        }
    });

};

//绑定弹出框，公用函数
function initPopover($obj,content,title) {
    $obj.popover({
        trigger:'manual',
        placement:'bottom',
        title:title,
        html:true,
        content:content
    });

    $obj.bind('click',function (e) {
        $('.add-ctrl-btn').popover('hide');
        $('.title-style-btn').popover('hide');
        $(this).popover('toggle');

        if($obj.hasClass('add-ctrl-btn')) {
            $('.ctrl-board>li').bind('click',function (e) {
                var event = window.event || e;
                var target = event.srcElement || event.target;
                var ctrl_name = $(target).attr('data-type');
                var obj = $(target).parents('.ctrl-item');

                addBlankCtrl(ctrl_name,obj);
                $('.invisible-btn').hide();
            });
        }else {
            $('.style-board>li').bind('click',function (e) {
                var event = window.event || e;
                var target = event.srcElement || event.target;
                var style_name = $(target).html();
                var style_type = $(target).attr('data-style');

                $obj.html(style_name);
                $obj.attr('data-style',style_type);
                arrangeSubmitInput($obj);
                $('.invisible-btn').hide();
            });
        }

    });
}

//绑定所有小插件功能
function initAllSpecFunc() {
    //初始化input改变事件
    initInputChangeEvent();
    //初始化选择对齐方式事件
    initChooseAlignEvent();
    //初始化选择页面事件
    initChoosePageEvent();
    //初始化选择图片事件
    initChooseImgEvent();

}

function initInputChangeEvent() {
    $('input.title-type-input').on('change',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;

        arrangeSubmitInput($(target));
    });

    $('input.link-input').on('change',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;

        arrangeSubmitInput($(target));
    });
}

function initChooseAlignEvent() {
    $('.choose-align>button').on('click',function (e) {
        $(this).siblings('.active').removeClass('active');
        $(this).addClass('active')

        arrangeSubmitInput($(this));
    });
}

function initChoosePageEvent() {
    $('button.choose-pages-btn').on('click',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;
        $('#choosing_page').attr('id','');
        target.id = 'choosing_page';

        if(!pageList) {
            getPageOrImgList('getPageInfo');
            return;
        }

        $('.choose-page-pop').show();
    });
}

function initChooseImgEvent() {
    $('.img-choose-btn').on('click',function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;
        $('#choosing_img').attr('id','');
        target.id = 'choosing_img';

        if(!imgList) {
            getPageOrImgList('getImgList');
            return;
        }

        $('.choose-img-pop').show();
    });
}

/*
 *数据整理部分
 */
function arrangeData() {
    //根据页面内容修改pageInfo内容
    var data = new Object();
    data['name'] = $('#page_name').val();
    data['title'] = $('#page_title').val();
    data['bgcolor'] = $('#page_bgcolor').val();
    data['id'] = pageInfo['id'];

    data['content'] = '';
    $.each($('.ctrl-item'),function (key,val) {
        var form = $(val).find('.form');
        var item = ' %%{ type:' + $(val).attr('data-type') + ';';
        item += 'value:[';

        $.each(form.find('input.submit-input'),function (key1,val1) {
            item += $(val1).val();
            if(key1 != (form.find('input.submit-input').length-1)) item += ',';
        });
        item += ']}%% ';
        data['content'] += item;
    });

    return data;
}

//获取页面或者图片列表
function getPageOrImgList(method_name) {
    $.ajax({
        method:'GET',
        url: URL + '/' + method_name,
        success:function (res) {
            if(method_name == 'getPageInfo') {
                pageList = JSON.parse(res);
                drawPageList(pageList);
            }else {
                imgList = JSON.parse(res);
                console.log(imgList);
                drawImgGroupList(imgList);
            }
        }
    });
}

//选项改变时修改对应的submitInput的值
function arrangeSubmitInput($obj) {
    var dataGather = $obj.parents('.data-gather');
    var first_part = dataGather.find('.img-choose-btn').attr('data-src') || dataGather.find('.title-type-input').val();
    var second_part = (dataGather.find('.link-input').val())? (':' + dataGather.find('.link-input').val()) : '';
    //标题样式和对齐方式
    var third_part = '';
    if(dataGather.find('.title-style-btn').get(0)) {
        third_part = ':' + dataGather.find('.title-style-btn').attr('data-style') + '-' + dataGather.find('.choose-align>.active').attr('data-align');
    }

    console.log(third_part);

    dataGather.find('.submit-input').val(first_part + second_part + third_part);
};

function submitData(data,flag) {

    $.ajax({
        method:'post',
        url: URL + '/changeContent',
        data:data,
        dataType:'json',
        success: function (res) {
            if(res.sign == 1) {
                // console.log('sasa');
                if(!flag) {
                    initPageData(data);
                }else {
                    $('#pageDisplay').attr('src',cur_show_url);
                }
            }
        }
    });
}