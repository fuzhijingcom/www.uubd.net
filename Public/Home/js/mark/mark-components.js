
//组件库
var component = {
controlPart: {
  // 推广数据
  userExpend:'<div class="form-group">' +
                '<select id="s_type" name="s_type" class="form-control type-select" onchange="changeStoreType()">'+
                    '<option value="0" selected>门店</option>'+
                    '<option value="1">合伙人</option>'+
                    // '<option value="2">市场部</option>'+
                '</select>'+
            '</div>' +

        '<div class="form-group">' +
            '<div class="calendar-box calendar-select">'+
                '<fieldset>'+
                    '<div class="control-group">'+
                        '<div class="controls">'+
                            '<div class="input-prepend input-group">'+
                            '<span class="add-on input-group-addon"><i class="glyphicon glyphicon-calendar"></i>'+
                            '</span>'+
                                '<input type="text" readonly style="background: #fff; font-size:1.2rem;" name="dataTime" id="dataTime" class="form-control back-color-w" value="" />'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</fieldset>'+
            '</div>'+
        '</div>' +
        '<div class="form-group" id="condition_box"></div>'+
        '<div class="form-group">' +
            '<button type="button" class="btn btn-default right-m" onclick="searchData()">查询</button>'+

            '<button type="button" class="btn btn-info" onclick="searchData(0)">今天</button>'+

            '<button type="button" class="btn btn-info" onclick="searchData(1)">昨天</button>'+

            '<button type="button" class="btn btn-info" onclick="searchData(2)">本月</button>'+

            '<button type="button" class="btn btn-info" onclick="searchData(3)">上个月</button>'+
            '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
            '&nbsp;&nbsp;&nbsp;'+
            '<span id="partner_num"></span>'+
  '</div>',

  // 服务评价数据
  serviceResearch:'<div class="form-group">' +
                '<select id="s_type" class="form-control type-select" onchange="searchServerData(null)">'+
      
                '</select>'+
            '</div>' +
        '<div class="form-group">' +
            '<div class="calendar-box calendar-select">'+
                '<fieldset>'+
                    '<div class="control-group">'+
                        '<div class="controls">'+
                            '<div class="input-prepend input-group">'+
                            '<span class="add-on input-group-addon"><i class="glyphicon glyphicon-calendar"></i>'+
                            '</span>'+
                                '<input type="text" readonly style="background: #fff; font-size:1.2rem;" name="dataTime" id="dataTime" class="form-control back-color-w" value="" />'+
                            '</div>'+
                        '</div>'+
                    '</div>'+
                '</fieldset>'+
            '</div>'+
        '</div>' +

        '<div class="form-group">' +
            '<button type="button" class="btn btn-default right-m" onclick="searchServerData(null)">查询</button>'+

            '<button type="button" class="btn btn-info" onclick="searchServerData(0)">今天</button>'+

            '<button type="button" class="btn btn-info" onclick="searchServerData(1)">昨天</button>'+

            '<button type="button" class="btn btn-info" onclick="searchServerData(2)">本月</button>'+
        '</div>' ,

    // 订单统计数据
    orderCount:'<div class="form-group">' +
    '<select id="g_type" class="form-control type-select" >'+
    '<option value="0">全部商品</option>'+
    '<option value="K" selected="selected">框架眼镜</option>'+
    '<option value="T">太阳镜</option>'+
    '<option value="G">功能眼镜</option>'+
    '<option value="Y">隐形眼镜</option>'+
    '<option value="O">其它商品</option>'+
    '</select>'+
    '<select id="s_type" class="form-control type-select" onchange="searchOrderCountData(null)" >'+
        '<option value="-1">（汇总）</option>'+
    '</select>'+
    '</div>' +
    '<div class="form-group">' +
    '<div class="calendar-box calendar-select">'+
    '<fieldset>'+
    '<div class="control-group">'+
    '<div class="controls">'+
    '<div class="input-prepend input-group">'+
    '<span class="add-on input-group-addon"><i class="glyphicon glyphicon-calendar"></i>'+
    '</span>'+
    '<input type="text" readonly style="background: #fff; font-size:1.2rem;" name="dataTime" id="dataTime" class="form-control back-color-w" value="" />'+
    '</div>'+
    '</div>'+
    '</div>'+
    '</fieldset>'+
    '</div>'+
    '</div>' +
    '<div class="form-group">' +
    '<button type="button" class="btn btn-danger" onclick="getTimes(0)">今天</button>'+
    '<button type="button" class="btn btn-warning" onclick="getTimes(1)">昨天</button>'+
    '<button type="button" class="btn btn-info" onclick="getTimes(2)">本月</button>'+
    '<button type="button" class="btn btn-success" onclick="getTimes(3)">上月</button>'+
    '&nbsp;&nbsp;&nbsp;'+
    '<input type="text" class="form-control search-box search-box-width" id="selling_id" placeholder="商城编号" style="width: 120px;">'+
    '<button type="button" class="btn btn-default right-m glyphicon glyphicon-search" onclick="searchOrderCountData(null)">查询</button>'+
    '&nbsp;&nbsp;&nbsp;&nbsp;'+
    '<label>展示选项:</label>'+
    '<select id="showType" class="form-control type-select" onchange="searchOrderCountData();">'+
    '<option value="0" selected="selected" >表格</option>'+
    '<option value="1">图表</option>'+
    '</select>'
  },
    
tablePart: {
  userExpendTableStore:"<tr>" +
                // "<td>名称</td>" +
                // "<td>总推广人数</td>" +
                // "<td>总流失人数</td>" +
                // "<td>总剩余人数</td>" +
                // "<td>总订单数</td>" +
                // "<td>旗下合伙人数量</td>" +
                // "<td>合伙人订单金额</td>" +
                // "<td>总营业额</td>" +
              "<td>门店名称</td>"+
              "<td>配镜业绩</td>"+
              "<td>隐形业绩</td>"+
              "<td>配镜单数</td>"+
              "<td>隐形单数</td>"+
              "<td>维权单数</td>"+
              "<td>维权金额</td>"+
              "<td>新增关注数</td>"+
              "<td>取消关注数</td>"+
              "<td>初级合伙人新增数</td>"+
              "<td>校园合伙人新增数</td>"+
              "<td>其他支付方式</td>"+
            "</tr>",
  userExpendTableMarket:"<tr>" +
                "<td>名称</td>" +
                "<td>所属门店</td>" +
                "<td>合伙人类型</td>" +
                "<td>当前推广数</td>" +
                "<td>当前订单数</td>" +
                "<td>历史推广数</td>" +
                "<td>历史订单数</td>" +
                "<td>历史已提现</td>" +
                "<td>历史总金额</td>" +
            "</tr>",
  serviceResearchTable:'<tr>' +
                '<td>门店名称</td>' +
                '<td>满意</td>' +
                '<td>凑合</td>' +
                '<td>不满意</td>' +
              '</tr>',
  orderCountTable:'<tr>' +
            '<th class="header">日期</th>' +
            '<th class="header">商品类型</th>' +
            '<th class="header">商城编号</th>' +
            '<th class="header">款式/规格</th>' +
            '<th class="header">商品单价</th>' +
            '<th class="header">商品销量</th>' +
            '<th class="header">累计销售金额</th>' +
            '<th class="header">所属门店</th>' +
            '<th class="header">库存</th>' +
            '</tr>',
  }
};


var tHeadExpandStore = ['s_name','not_contact_payment','contact_payment','not_contact_count','contact_count','feedback_count','feedback_payment','follow_count','cannel_follow_count','primary_partner_count','school_partner_count','others_count'];
var tHeadExpandMarket = ['p_name','s_name','p_type','temp_follow_count','temp_order_count','all_follow_count','all_order_count','withdraw_count','price_count'];
var tHeadService = ['s_name','great','good','bad'];
var tHeadOrderCount = ['date_time','goods_type','selling_id','style','single_price','sale_num','total_payment','store_name','stock_num'];
