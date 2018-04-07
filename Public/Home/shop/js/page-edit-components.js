/**
 * Created by xingact on 17-2-4.
 */
var ctrl_components = {
            'img-item': '<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                        '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">图片导航</span>'+
                            '<span class="control-btns" onclick="addOrDeleteInput()">' +
                                '<i class="glyphicon glyphicon-plus plus-btn"></i><i class="glyphicon glyphicon-minus minus-btn"></i>' +
                            '</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<div class="data-gather">' +
                                '<img src="' + PUBLIC + '/Home/shop/img/img_icon.png" class="img-choose-btn" style="width: 34px;height: 34px;cursor: pointer">' +
                                '<div class="form-inline get-link-data">' +
                                    '<span>链接：</span><input type="text" class="form-control link-input" placeholder="请填写外链或选择页面">' +
                                    '<button class="choose-pages-btn btn btn-success">选择页面</button>' +
                                '</div>' +
                                '<input type="hidden" class="form-control submit-input">' +
                            '</div>'+
                        '</div>',
        'swiper-item':  '<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                        '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">轮播图</span>'+
                            '<span class="control-btns" onclick="addOrDeleteInput()">' +
                                '<i class="glyphicon glyphicon-plus plus-btn"></i><i class="glyphicon glyphicon-minus minus-btn"></i>' +
                            '</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<div class="data-gather">' +
                                '<img src="' + PUBLIC + '/Home/shop/img/img_icon.png" class="img-choose-btn" style="width: 34px;height: 34px;cursor: pointer">' +
                                '<div class="form-inline get-link-data">' +
                                    '<span>链接：</span><input type="text" class="form-control link-input" placeholder="请填写外链或选择页面">' +
                                    '<button class="choose-pages-btn btn btn-success">选择页面</button>' +
                                '</div>' +
                                '<input type="hidden" class="form-control submit-input">' +
                            '</div>'+
                        '</div>',
            'tab-item': '<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                        '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">tab导航栏</span>'+
                            '<span class="control-btns" onclick="addOrDeleteInput()">' +
                                '<i class="glyphicon glyphicon-plus plus-btn"></i><i class="glyphicon glyphicon-minus minus-btn"></i>' +
                            '</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<div class="data-gather">' +
                                '<input type="text" class="form-control title-type-input" style="width: 15%;margin-right: 10px" placeholder="标题">' +
                                '<div class="form-inline get-link-data">' +
                                    '<span>链接：</span><input type="text" class="form-control link-input" placeholder="请填写外链或选择页面">' +
                                    '<button class="choose-pages-btn btn btn-success">选择页面</button>' +
                                '</div>' +
                                '<input type="hidden" class="form-control submit-input">' +
                            '</div>'+
                        '</div>',
    'single-col-list':  '<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                        '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">单列商品</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<input type="text" class="form-control submit-input" placeholder="填入初始显示商品skuId，如:K1002C14, K1002C15, K9011C147">'+
                        '</div>',
        'two-col-list': '<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                        '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">双列商品</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<input type="text" class="form-control submit-input" placeholder="填入初始显示商品skuId，如:K1002C14, K1002C15, K9011C147">'+
                        '</div>',
        'more-good-list':'<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                         '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">更多商品</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<input type="text" class="form-control submit-input" placeholder="填入初始显示商品skuId，如:K1002C14, K1002C15, K9011C147">'+
                        '</div>',
        'title-item' :  '<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                        '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">标题</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<div class="data-gather">' +
                                '<input type="text" class="form-control title-type-input" style="width: 30%;margin-right: 10%" placeholder="标题">' +
                                '<div class="form-inline get-title-style">' +
                                    '<a role="button" class="title-style-btn btn btn-success" style="margin-right: 1%" data-style="s">小标题</a>' +
                                    '<div class="btn-group choose-align" role="group" aria-label="...">' +
                                        '<button type="button" class="btn btn-default active" data-align="left"><i class="glyphicon glyphicon-align-left"></i></button>' +
                                        '<button type="button" class="btn btn-default" data-align="center"><i class="glyphicon glyphicon-align-center"></i></button>' +
                                        '<button type="button" class="btn btn-default" data-align="right"><i class="glyphicon glyphicon-align-right"></i></button>' +
                                    '</div>' +
                                '</div>' +
                                '<input type="hidden" class="form-control submit-input">' +
                            '</div>'+
                        '</div>',
        'empty-item' :  '<i class="glyphicon glyphicon-remove-sign del-ctrl-btn invisible-btn" onclick="deleteCurItem()"></i>'+
                        '<i class="glyphicon glyphicon-plus-sign add-ctrl-btn invisible-btn"></i>'+
                        '<p class="ctrl-item-title">'+
                            '<span style="margin-right: 10px">占位空白</span>'+
                        '</p>'+
                        '<div class="form">'+
                            '<input type="text" class="form-control submit-input" placeholder="填入占位空白高度，格式：数字">'+
                        '</div>'
};

var pop_components = {
    'ctrl-board' : '<ul class="pop-board ctrl-board">' +
                        '<li data-type ="title-item">标题</li>'+
                        '<li data-type ="img-item">图片导航</li>'+
                        '<li data-type ="swiper-item">轮播图</li>'+
                        '<li data-type ="single-col-list">单列商品</li>'+
                        '<li data-type ="two-col-list">双列商品</li>'+
                        '<li data-type ="more-good-list">更多商品</li>'+
                        '<li data-type ="empty-item">占位空白</li>'+
                        '<div class="clearfix"></div>'+
                    '</ul>',
    'style-board' : '<ul class="pop-board style-board">' +
                        '<li data-style="s">小标题</li>'+
                        '<li data-style="b">大标题</li>'+
                        '<div class="clearfix"></div>'+
                    '</ul>'
}

var other_components = {
    'img-data-gather' : '<div class="data-gather">' +
                            '<img src="' + PUBLIC + '/Home/shop/img/img_icon.png" class="img-choose-btn" style="width: 34px;height: 34px;cursor: pointer">' +
                            '<div class="form-inline get-link-data">' +
                                '<span>链接：</span><input type="text" class="form-control link-input" placeholder="请填写外链或选择页面">' +
                                '<button class="choose-pages-btn btn btn-success">选择页面</button>' +
                            '</div>' +
                            '<input type="hidden" class="form-control submit-input">' +
                        '</div>',
    'title-data-gather' : '<div class="data-gather">' +
                            '<input type="text" class="form-control title-type-input" style="width: 15%;margin-right: 10px" placeholder="标题">' +
                            '<div class="form-inline get-link-data">' +
                                '<span>链接：</span><input type="text" class="form-control link-input" placeholder="请填写外链或选择页面">' +
                                '<button class="choose-pages-btn btn btn-success">选择页面</button>' +
                            '</div>' +
                            '<input type="hidden" class="form-control submit-input">' +
                        '</div>'
};