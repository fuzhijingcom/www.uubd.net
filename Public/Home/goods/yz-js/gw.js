var webIMPluginForGW = {

    options: { webIMHost: 'http:\/\/webim.wangcl.com', isFirstShowWebIM: true, smallAlertDiv: 'smallAlertDiv', alertDiv: 'alertDiv', fromType : -1 } //http:\/\/webim.wangcl.com
    //$container 图标的容器
    //type: 0 图片—+文字， 1 仅图片 2 卡通图+文字
    // isPC 是否是PC版本
    //fromType 官网0，微信1
    // userId 用户的ID
    , showWebIM: function ($container, type, fromType, userId, isMobile, userIdFromType, waiterDataType, waiterOutId) {
        var _this = this;
        //alert(waiterDataType);
        _this.options.fromType = fromType;
        $container.empty();
        $div = $('<div/>').css({ 'width': '100%', 'height': '100%', 'position': 'relative', 'text-align': 'center', 'font-size': '1.14rem' })
            .click(function () {
                _this.hideAlertNewMsg();
                _this.toWebIM(fromType, userId, isMobile, userIdFromType, waiterDataType, waiterOutId);
            });

        if (type == 1) {
            $div.append($('<img src="' + _this.options.webIMHost + '/images/Plugin/customer_icon.svg"/>').css({ 'width': '38%', 'width': '38%', 'margin': '10% auto 2% auto', 'display': 'block' }));
            $div.append($('<span>客服</span>'))
        } else if (type == 0) {
            $div.append($('<img src="' + _this.options.webIMHost + '/images/Plugin/Customer_Service_fixed.png"/>').css({ 'width': '100%' }));
        } else if (type == 2) {
            $div.append($('<img src="' + _this.options.webIMHost + '/images/Plugin/zwg_index_kefu.svg"/>').css({ 'width': '100%' }));
        } else if (type == 3) {
            $div.append($('<img src="' + _this.options.webIMHost + '/images/Plugin/btn_contact_wx.png"/>').css({ 'width': '100%' }));
        }

        var $smallAlertDiv = $('<div/>').css({ 'width': '2.5rem', 'height': '1.20rem', 'position': 'absolute', 'text-align': 'center', 'left': '0px', 'top': '0px', 'background': '#FF0000', 'display': 'none', 'line-height': '1.20rem', 'border-radius': '4px', 'color': '#fff' }).text('new').addClass(_this.options.smallAlertDiv);
        $div.append($smallAlertDiv);
        $container.append($div);
        if (_this.options.isFirstShowWebIM) {
            var $body = $('body');

            var $alertDiv = $('<div />').css({ 'width': '100%', 'height': '1.50rem', 'position': 'fixed', 'text-align': 'center', 'font-size': '1.30rem', 'left': '0px', 'top': '0px', 'background': '#FFEFDB', 'display': 'none', 'line-height': '1.50rem', 'z-index': '1000000', 'color': '#ff5f00' }).text('您有来自望客客服的新消息~').addClass(_this.options.alertDiv)
            .click(function () {
                _this.hideAlertNewMsg();
                $div.click();
            });
            $body.append($alertDiv);

            var task = setInterval(function () {
                if ($alertDiv.css('display') != 'none') { return; }
                var isTest = webIMPluginForGW.getQueryString('isTest');
                $.ajax({
                    url: _this.options.webIMHost + '/Customer/DataHandler/GetCustomerData.ashx?requestType=getCustomerNewMessageNumber&userId=' + userId + '&time=' + new Date() + '&isTest=' + isTest
                 , method: 'get'
                 , cache: false
                 , dataType: 'jsonp'
                 , data: null
                 , contentType: "application/json;utf-8"
                 , success: function (data) {
                     if (data.Data == '1') {
                         $('.' + _this.options.smallAlertDiv).show();
                     } else {
                         $('.' + _this.options.smallAlertDiv).hide();
                     }

                 }
                });
            }, 10000);
        }
        _this.options.isFirstShowWebIM = false;
    }

    , toWebIM: function (fromType, userId, isMobile, userIdFromType, waiterDataType, waiterOutId) {
        var _this = this;
        var customerFolder = 'wap';
        if (!isMobile) {
            customerFolder = 'customer';
        }
        if (!waiterDataType) { waiterDataType = '' }
        if (!waiterOutId) { waiterOutId = '' }
        var url = _this.options.webIMHost + '/' + customerFolder + '/index.aspx?UserId=' + encodeURIComponent(userId) + '&customerFromUrl=' + encodeURIComponent(location.href) + '&FromType=' + fromType + '&userIdFromType=' + userIdFromType + '&waiterDataType=' + waiterDataType + '&waiterOutId=' + waiterOutId;
        if (isMobile) {
            if (fromType == 10) { // 安卓app 跳转
                javascript: window.wangclWeb.openApp();
            } else if (fromType == 11) { // IOS app 跳转
                location.href = "#window.wangclWeb.openApp();";
            } else {
                location.href = url;
            }
        }
        else {
            window.open(url);
        }
    }
    , hideAlertNewMsg: function () {
        $('.' + this.options.smallAlertDiv).hide();
    }
    , exitChat: function () {
        COMMON.cookie.delCookie('CustomerCookieId');
    }
    ,getQueryString: function (name, str) {
        try {
            var parameter;
            if (!str || str == '') { str = location.href }
            var index = str.indexOf('?');
            if (index > -1) {
                parameter = str.substr(index);
            } else {
                parameter = str;
            }
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
            var r = parameter.substr(1).match(reg);
            if (r && r.length > 0) {
                return (r[2]);
            } else {
                return '';
            }
        } catch (e) {
            return '';
        }
    }
}