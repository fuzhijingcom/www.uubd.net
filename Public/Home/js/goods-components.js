// 组件库
var component = {
    controlPart: {
        // 商品管理的搜索框
        searchBox: '<div class="form-group"><input type="text" class="form-control first-part-input" id="searchInput" placeholder="搜索商品商城编号或名称">' +
        ' <a class="btn btn-default" onclick="searchGoods()">搜索</a></div>'
    },
    tableTitle: '<tr>' +
    '<th>商品名称</th>' +
    '<th>商城编号</th>' +
    '<th>操作项</th>' +
    '</tr>'
};

var goodsTable = {
    field: ['goods_name', 'id_str']
};

var selectGoodsType = '<label class="checkbox-inline"><input type="checkbox" name="goods_type" class="select-goods-type" value="K">　　框架眼镜</label>' +
    '<label class="checkbox-inline"><input type="checkbox" name="goods_type" class="select-goods-type" value="T">　　太阳镜</label> ' +
    '<label class="checkbox-inline"><input type="checkbox" name="goods_type" class="select-goods-type" value="G">　　功能眼镜</label> ' +
    '<label class="checkbox-inline"><input type="checkbox" name="goods_type" class="select-goods-type" value="Y">　　隐形眼镜</label> ' +
    '<label class="checkbox-inline"><input type="checkbox" name="goods_type" class="select-goods-type" value="H">　镜片</label> ' +
    '<label class="checkbox-inline"><input type="checkbox" name="goods_type" class="select-goods-type" value="U">　镜腿</label>';

var func_component = {
    'good_img_manage' : '<div class="row">' +
                            '<div class="width-12" id="product-type">' +
                                '<div class="list-group" id="modules-box">' +
                                    '<a href="javascript:void(0)" class="list-group-item active" title="" onclick="changeCategory(this)">全部商品</a>' +
                                    '<a href="javascript:void(0)" class="list-group-item" title="K" onclick="changeCategory(this)">框架眼镜</a>' +
                                    '<a href="javascript:void(0)" class="list-group-item" title="T" onclick="changeCategory(this)">太阳镜</a>' +
                                    '<a href="javascript:void(0)" class="list-group-item" title="G" onclick="changeCategory(this)">功能眼镜</a>' +
                                    '<a href="javascript:void(0)" class="list-group-item" title="Y" onclick="changeCategory(this)">隐形眼镜</a>' +
                                    '<a href="javascript:void(0)" class="list-group-item" title="U" onclick="changeCategory(this)">镜腿</a>' +
                                    '<a href="javascript:void(0)" class="list-group-item" title="H" onclick="changeCategory(this)">其他商品</a>' +
                                '</div>' +
                                '<div style="height:5px;"></div>' +
                            '</div>' +
                            '<div class="width-88" id="content">' +
                                '<div id="controlBox-father">' +
                                    '<form class="form-inline control-box" style="width: 100%!important;" id="controlBox" onsubmit="return false;"></form>' +
                                '</div>' +
                                '<div id="goods-table"></div>' +
                            '</div>' +
                        '</div>',


    'pages_manage' :    '<div class="other-content">' +
                            '<div class="page-manage">' +
                                '<div class="control-btns">' +
                                    '<button class="btn btn-success add-page" style="margin-right: 10px" onclick="createNewPage(\'create\')();">新增微页面</button>' +
                                    '<a class="btn btn-success edit-bot-nav" href="' + URL + '/editCommonPart" target="_blank">编辑导航栏</a>' +
                                '</div>' +
                                '<table class="table table-bordered table-hover" id="pageTable">' +
                                    '<thead>' +
                                        '<tr>' +
                                        '<th title="页面标题">页面标题</th>' +
                                        '<th title="链接后缀，每个页面特定链接">页面地址</th>' +
                                        '<th>操作项</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody></tbody>' +
                                '</table>' +
                            '</div>' +
                        '</div>',

    
    'all_img_manage' :  '<div class="row">' +
                            '<div class="col-md-12" id="content">' +
                                '<div id="controlBox-father">' +
                                    '<form class="form-inline control-box" style="width: 100%!important;" id="controlBox" onsubmit="return false;"></form>' +
                                '</div>' +
                                '<div id="image-table"></div>' +
                            '</div>' +
                        '</div>',
    
    
    'coupon_manage' : '<div class="row" id="coupon-management">' +
    '<div class="width-12">' +
    '<div class="list-group" id="coupon-modules-box">' +
    '<a href="javascript:void(0)" class="list-group-item active" title="" onclick="changeCategory(this)">优惠券列表</a>' +
    '</div>' +
    '<div style="height:5px;"></div>' +
    '</div>' +

    '<div class="width-88" id="coupon-content">' +
    '<div id="coupon-controlBox-father">' +
    '<form class="form-inline control-box" style="width: 100%!important;" id="coupon-controlBox" onsubmit="return false;">' +
    '<a class="btn btn-success" href="javascript:void(0)" onclick="addCouponClick()">新增优惠券</a>' +
    '</form>' +
    '</div>' +
    '<br>' +
    '<div id="coupon-table">' +
    '</div>' +
    '<div class="modal fade" id="coupon-modal" tabindex="-1" role="dialog" aria-labelledby="coupon-modal-label">' +
    '<div class="modal-dialog" role="document">' +
    '<div class="modal-content">' +
    '<form>' +
    '<div class="modal-header">' +
    '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
'<h4 class="modal-title" id="coupon-modal-label">新增</h4>' +
    '</div>' +
    '<div class="modal-body">' +
    '<div class="form-group clearfix" hidden>' +
'<label for="coupon_id">优惠券id</label>' +
    '<input type="text" name="coupon_id" class="form-control" id="coupon_id" placeholder="优惠券id">' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="coupon_name">优惠券名称</label>' +
    '<input type="text" name="coupon_name" class="form-control" id="coupon_name" placeholder="优惠券名称">' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="goods_type">适用商品类型</label>' +
    selectGoodsType +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="coupon_price">面额</label>' +
    '<input type="text" name="coupon_price" class="form-control" id="coupon_price" placeholder="面额">' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="coupon_condition">最低消费金额</label>' +
    '<input type="text" name="coupon_condition" class="form-control" id="coupon_condition" placeholder="最低消费金额">' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="available_time">生效时间</label>' +
    '<input type="datetime-local" name="available_time" class="form-control" id="available_time" placeholder="生效时间">' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="invalid_time">失效时间</label>' +
    '<input type="datetime-local" name="invalid_time" class="form-control" id="invalid_time" placeholder="失效时间">' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="is_valid" class="is_valid">是否有效</label>' +
    '<label class="radio-inline"> <input type="radio" checked name="is_valid" value="1">&nbsp;&nbsp;有效</label>' +
    '<label class="radio-inline"> <input type="radio" name="is_valid" value="0">&nbsp;&nbsp;无效</label>' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="num_per_user">可领取数量</label>' +
    '<input type="number" name="num_per_user" class="form-control" id="num_per_user" value="1" placeholder="可领取数量">' +
    '</div>' +
    '<div class="form-group clearfix">' +
    '<label for="superimposed" class="superimposed">能否叠加使用</label>' +
    '<label class="radio-inline"> <input type="radio" name="superimposed" value="1">&nbsp;&nbsp;能</label>' +
    '<label class="radio-inline"> <input type="radio" checked name="superimposed" value="0">&nbsp;&nbsp;否</label>' +
    '</div>' +
    '</div>' +
    '<div class="modal-footer">' +
    '<button type="button" class="btn btn-default" data-dismiss="modal"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span>关闭</button>' +
    '<button type="button" id="btn_submit" class="btn btn-primary" data-dismiss="modal" onclick="submitCoupon()"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>保存</button>' +
    '</div>' +
    '</form>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>' +
    '</div>'
};