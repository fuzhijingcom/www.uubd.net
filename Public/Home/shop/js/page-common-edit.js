
$(document).ready(function () {
    initPageData(commonInfo);
    //记录原始未修改版本
    oldInfo = arrangeData();
    oldInfo['ctrlData'] = commonInfo['ctrlData'];
    // console.log(oldInfo);

    initEvent();

    $('.form').sortable();
    $('.sub-nav').sortable();
});

/*
 *渲染页面部分
 */
function initPageData(data) {
    if(!data['ctrlData']) return false;

    var ctrl_body = $('#all_ctrl_control').html('');
    $.each(data['ctrlData'],function (key,val) {
            if(val['type'] == 'navigation-bar-item') {
                drawNavigationItem(val['value'],ctrl_body);
                return;
            }

            var item =  '<li class="ctrl-item" data-type="' + val['type'] + '" onmouseover="showInvisibleBtn(this,1)" onmouseout="showInvisibleBtn(this,0)" id="initCtrl'+ key +'">'+
                            ctrl_components[val['type']] +
                        '</li>';
            var item_id = '#initCtrl' + key;

            ctrl_body.append(item);

            var form = $(item_id).find('.form');
            form.html('');
            if(typeof val['value'] == 'object') {
                initDataGather(val['type'],val['value'],form);
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

    //初始化所有小插件功能
    initAllSpecFunc();
}

//渲染导航栏控件
function drawNavigationItem(val,obj) {
    var item = '<li class="ctrl-item" data-type ="navigation-bar-item">'+
                    '<p class="ctrl-item-title">'+
                    '<span style="margin-right: 10px">导航栏</span>'+
                    '</p>'+
                    '<div class="form">';

    $.each(val, function (k,v) {

        var dataGather = '<div class="dataGather">';

        //判断数据中是否有副导航数据
        if(v.hasOwnProperty('sub_nav')) {
            dataGather += '<div class="form-inline">'+
                                        '<input type="text" class="form-control main-title-type-input" style="width: 15%;margin-right: 1%" value="' + v['name'] + '" placeholder="主标题">'+
                                        '<span class="control-btns" onclick="addOrDeleteSubNav()" style="margin-right: 2%">' +
                                            '<i class="glyphicon glyphicon-plus plus-btn"></i><i class="glyphicon glyphicon-minus minus-btn"></i>' +
                                        '</span>'+
                                        '<input type="text" class="form-control link-input" placeholder="请填写外链或选择页面" style="display: none">' +
                                        '<button class="choose-pages-btn btn btn-success" style="display: none">页面</button>' +
                                        '<div class="sub-nav">';

            $.each(v['sub_nav'],function (key1,val1) {
                var form_inline = '<div class="form-inline">' +
                                    '<input type="text" class="form-control sub-title-type-input" style="width: 15%;margin-right: 5px" value="' + val1['name'] + '" placeholder="副标题">' +
                                    '<input type="text" class="form-control link-input" value="' + val1['url'] + '" placeholder="请填写外链或选择页面">' +
                                    '<button class="choose-pages-btn btn btn-success">页面</button>' +
                                '</div>';
                dataGather += form_inline;
            });
            dataGather += '</div></div>';

        }else {
            dataGather += '<div class="form-inline">' +
                            '<input type="text" class="form-control main-title-type-input" style="width: 15%;margin-right: 1%" value="' + v['name'] + '" placeholder="主标题">' +
                            '<span class="control-btns" onclick="addOrDeleteSubNav()" style="margin-right: 2%">' +
                                '<i class="glyphicon glyphicon-plus plus-btn"></i>' +
                                '<i class="glyphicon glyphicon-minus minus-btn"></i>' +
                            '</span>' +
                            '<input type="text" class="form-control link-input" value="' + v['url'] + '" placeholder="请填写外链或选择页面">' +
                            '<button class="choose-pages-btn btn btn-success">页面</button>' +
                            '<div class="sub-nav"></div>' +
                        '</div>';
        }

        dataGather += '</div>';
        item += dataGather;
    });

    item += '</div></li>';
    obj.append(item);
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

    //点击弹出框背景，弹出框消失事件
    $('.pop-bg').on('click', function (e) {
        var event = window.event || e;
        var target = event.srcElement || event.target;

        $(target).parents('.pop-box').hide();
    });

};

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
 *页面各按键功能
 */
//添加或删除选项
function addOrDeleteInput(e) {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    var inputs = $(target).parents('.ctrl-item-title').siblings('.form');
    var type = $(target).parents('.ctrl-item').attr('data-type');

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

//添加或删除副导航栏
function addOrDeleteSubNav(e) {
    var event = window.event || e;
    var target = event.srcElement || event.target;

    var dataGather = $(target).parents('.dataGather');
    var sub_nav = dataGather.find('.sub-nav');

    if($(target).hasClass('plus-btn')) {
        var input = '<div class="form-inline">' +
                        '<input type="text" class="form-control sub-title-type-input" style="width: 15%;margin-right: 5px" placeholder="副标题">' +
                        '<input type="text" class="form-control link-input" placeholder="请填写外链或选择页面">' +
                        '<button class="choose-pages-btn btn btn-success">页面</button>' +
                    '</div>';
        sub_nav.append(input);

        //初始化所有小插件功能
        initAllSpecFunc();
    }else {
        if(sub_nav.children().length != 0) {
            sub_nav.children(':last').detach();
        }
    }

    //判断是否有副导航栏，如有则隐藏主导航的链接选项
    if(sub_nav.children().length > 0){
        dataGather.find('.control-btns').siblings('.link-input').hide();
        dataGather.find('.control-btns').siblings('.choose-pages-btn').hide();
    }else {
        dataGather.find('.control-btns').siblings('.link-input').show();
        dataGather.find('.control-btns').siblings('.choose-pages-btn').show();
    }
}

/*
 *数据整理部分
 */

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

//整合所有数据
function arrangeData() {
    //根据页面内容修改pageInfo内容
    var data = new Object();
    data['name'] = $('#page_name').val();
    data['title'] = $('#page_title').val();
    data['bgcolor'] = $('#page_bgcolor').val();
    data['id'] = commonInfo['id'];

    data['content'] = '';
    $.each($('.ctrl-item'),function (key,val) {

        if($(val).attr('data-type') == 'navigation-bar-item') {
            var item = arrangeNavData(val);
        }else {
            var form = $(val).find('.form');
            var item = ' %%{ type:' + $(val).attr('data-type') + ';';
            item += 'value:[';

            $.each(form.find('input.submit-input'),function (key1,val1) {
                item += $(val1).val();
                if(key1 != (form.find('input.submit-input').length-1)) item += ',';
            });
            item += ']}%% ';
        }

        data['content'] += item;
    });

    return data;
}

//整合导航栏数据
function arrangeNavData(obj) {
    var item = ' %%{ type:navigation-bar-item; value:[';

    $.each($(obj).find('.dataGather'),function (key,val) {

        //判断是否有副导航栏
        if(!$(val).find('.sub-nav').children().length) {

            var main_title = $(val).find('.main-title-type-input').val();
            var link_url = $(val).find('.link-input').val();
            item += main_title + ':' + link_url;

        }else {

            var sub_nav = $(val).find('.sub-nav');
            var main_title = $(val).find('.main-title-type-input').val();
            item += main_title + '~';

            $.each(sub_nav.find('.form-inline'),function (key1,val1) {
                var sub_title = $(val1).find('.sub-title-type-input').val();
                var link_url1 = $(val1).find('.link-input').val();
                item += sub_title + ':' + link_url1;

                if(key1 != sub_nav.find('.form-inline').length -1) item += ',';
            });

        }

        if(key != $(obj).find('.dataGather').length -1) item += ';';
    });

    item+=']}%% ';

    return item;
}

//提交数据
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
                    $('#pageDisplay').attr('src',cur_show_url);
                }else {
                    $('#pageDisplay').attr('src',cur_show_url);
                }
            }
        }
    });
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
