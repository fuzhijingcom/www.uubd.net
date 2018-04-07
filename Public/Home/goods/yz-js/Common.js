var userInfo = null;//用户信息
var arrFavoriteGoodsSkuid = [];//收藏夹

function MessageBoxShow(oDom, fCallBack) {
    var doc = document,
		shadow = doc.createElement('div'),
		shadowIframe = doc.createElement('iframe'),
		$dom = $(oDom),
		$iframe = $dom.find('iframe');

    shadow.className = 'niupingjie_mask_show_bg';
    $(shadow).css({
        'background-color': '#000'
    });
    shadowIframe.className = 'niupingjie_mask_show_bg_iframe';
    shadowIframe.style.width = '100%';
    shadowIframe.style.height = '100%';
    $(shadowIframe).css({
        opacity: 0
    });
    shadowIframe.frameborder = "0";
    shadowIframe.scrolling = "no";
    shadow.appendChild(shadowIframe);
    doc.body.appendChild(shadow);
    shadow.style.display = 'block';

    $dom.css({
        left: ($(window).width() - $dom.outerWidth()) / 2,
        top: ($(window).height() - $dom.outerHeight()) / 2
    }).show();

    $iframe.data('defaultWidth', $iframe.width());
    $iframe.data('defaultHeight', $iframe.height());

    $dom
	.find('.niupingjie_mask_close')
	.click(function () {
	    oDom.style.display = 'none';
	    $('.niupingjie_mask_show_bg, .niupingjie_mask_show_bg_iframe').remove();
	});

    $dom.data('titleHeight', $dom.find('>table>tbody>tr:first').outerHeight());

    $dom.data('bottomHeight', 0);
    if ($dom.find('#queding_ok_Joe').length) {
        $dom.data('bottomHeight', $dom.find('>tr:last').outerHeight());
    }

    if ($iframe.length) {
        var resizeFun = function () {
            setTimeout(function () {
                $(shadow).unbind('resize');
                $iframe.css({
                    width: $iframe.data('defaultWidth'),
                    height: $iframe.data('defaultHeight')
                });

                var domWidth = $dom.width(),
					domHeight = $dom.height(),
					$win = $(window),
					wWidth = $win.width(),
					wHeight = $win.height();


                if (domWidth > wWidth || domHeight > wHeight) {
                    if (domWidth > wWidth) {
                        $iframe.css({
                            width: wWidth - 50
                        });
                    }

                    if (domHeight > wHeight) {
                        $iframe.css({
                            height: wHeight - $dom.data('titleHeight') - $dom.data('bottomHeight') - 20
                        });
                    }
                }

                $dom.css({
                    left: (wWidth - $dom.width()) / 2,
                    top: (wHeight - $dom.height()) / 2
                });

                $(shadow).bind('resize', function () {
                    resizeFun();
                });
            });
        };
        resizeFun();
    } else {
        $(shadow).bind('resize', function () {
            setTimeout(function () {
                $dom.css({
                    left: ($(window).width() - $(oDom).outerWidth()) / 2,
                    top: ($(window).height() - $(oDom).outerHeight()) / 2
                }).show();
            });
        }).trigger('resize');
    }
    if (fCallBack) fCallBack();
}

function Close() {
    var ua = navigator.userAgent;
    var ie = navigator.appName == "Microsoft Internet Explorer" ? true : false;
    if (ie) {
        var IEversion = parseFloat(ua.substring(ua.indexOf("MSIE ") + 5, ua.indexOf(";", ua.indexOf("MSIE "))));
        if (IEversion < 5.5) {
            var str = '<object id=noTipClose classid="clsid:ADB880A6-D8FF-11CF-9377-00AA003B7A11">';
            str += '<param name="Command" value="Close"></object>';
            document.body.insertAdjacentHTML("beforeEnd", str);
            document.all.noTipClose.Click();
        } else {
            window.opener = null;
            window.open("", '_self');
            window.close();
        }
    } else {
        window.opener = null;
        window.open("", '_self');
        window.close();
    }
}
function request(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return "";
}


function OpenCon(nicknameH, /*QQ WW*/type) {
    nicknameH = encodeURIComponent(decodeURIComponent(nicknameH))
    if (!$("#show_ww_qq_Joe")[0]) {
        $("body").append("<iframe src=''  id='show_ww_qq_Joe' style='height:0px;width:0px'></iframe>")
    } else {
        $("#show_ww_qq_Joe").attr("src", "");
    }

    if (type.toLowerCase() == "ww") {

        $("#show_ww_qq_Joe").attr("src", "http://amos.im.alisoft.com/msg.aw?v=2&uid=" + (nicknameH) + "&site=cntaobao&s=1&charset=utf-8&m=" + Math.random())

    } else {
        $("#show_ww_qq_Joe").attr("src", "tencent://message/?uin=" + nicknameH + "&Site=qq&Menu=yes&m=" + Math.random())
    }
    return;
}
function DisableButtom(buttom, times, flg) {
    //提醒用户
    ShowTempMessage(times + "后将禁用提交按钮", 5 * 100)
    setTimeout(function () {
        document.getElementById(buttom).disabled = !flg;
        document.getElementById(buttom).enabled = !flg;
    }, times * 1000)
}


function ShowTempMessage(msg, delaySeconds, option) {
    var windowWidth = document.documentElement.clientWidth;
    var top = parseFloat(document.documentElement.scrollTop) + parseFloat(parent.document.body.scrollTop) + parseFloat(200);
    if (msg == "") return
    var Tip = $('<p>' + msg + '</p>'),
        move = 30;
    option = {
        display: 'none',
        position: 'absolute',
        padding: '5px 10px',
        color: '#fff',
        opacity: 0,
        "line-height": "30px",
        "font-size": "12px",
        "max-width": "80%",
        'z-index': 99999,
        "border-radius": "5px",
        'background-color': '#333',
        'top': top + 'px'
    };
    Tip.appendTo(document.body).css(option);

    option = {
        display: 'block',
        'margin-left': -Tip.outerWidth() / 2,
        'left': '50%'
    };
    Tip.appendTo(document.body).css(option);

    var showTipTimer = setTimeout(function () {
        var top = Tip.offset().top;
        Tip.css({
            top: top + move / 2
        });
        Tip.animate({
            top: top - move / 2,
            opacity: 0.8
        }, function () {
            setTimeout(function () {
                var top = Tip.offset().top;
                Tip.animate({
                    top: top - move,
                    opacity: 0
                }, function () {
                    Tip.remove();
                });
            }, delaySeconds || 1000);
        });
    });
    return Tip;
}


function SetFrameCarsNum(num) {
    top.ShopCart.setNumber(num);
}


function formatDate(val) {
    var re = /-?\d+/;
    var m = re.exec(val);
    var d = new Date(parseInt(m[0]));
    // 按【2012-02-13】的格式返回日期
    return d.format("yyyy-MM-dd");
}


function formatTime(val) {
    var re = /-?\d+/;
    var m = re.exec(val);
    var d = new Date(parseInt(m[0]));
    // 按【2012-02-13 09:09:09】的格式返回日期
    return d.format("yyyy-MM-dd hh:mm:ss");
}

Date.prototype.format = function (format) //author: meizz
{
    var o = {
        "M+": this.getMonth() + 1, //month
        "d+": this.getDate(),    //day
        "h+": this.getHours(),   //hour
        "m+": this.getMinutes(), //minute
        "s+": this.getSeconds(), //second
        "q+": Math.floor((this.getMonth() + 3) / 3),  //quarter
        "S": this.getMilliseconds() //millisecond
    }
    if (/(y+)/.test(format)) format = format.replace(RegExp.$1,
    (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o) if (new RegExp("(" + k + ")").test(format))
        format = format.replace(RegExp.$1,
      RegExp.$1.length == 1 ? o[k] :
        ("00" + o[k]).substr(("" + o[k]).length));
    return format;
}


Date.prototype.Format = function (fmt) {
    var o =
     {
         "M+": this.getMonth() + 1, //月份 
         "d+": this.getDate(), //日 
         "h+": this.getHours(), //小时 
         "m+": this.getMinutes(), //分 
         "s+": this.getSeconds(), //秒 
         "q+": Math.floor((this.getMonth() + 3) / 3), //季度 
         "S": this.getMilliseconds() //毫秒 
     };
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}


Date.prototype.addDays = function (d) {
    this.setDate(parseInt(this.getDate()) + parseInt(d));
};


Date.prototype.addWeeks = function (w) {
    this.addDays(parseInt(w) * parseInt(7));
};


Date.prototype.addMonths = function (m) {
    var d = this.getDate();
    this.setMonth(parseInt(this.getMonth()) + parseInt(m));

    if (this.getDate() < d)
        this.setDate(0);
};


Date.prototype.addYears = function (y) {
    var m = this.getMonth();
    this.setFullYear(parseInt(this.getFullYear()) + parseInt(y));

    if (m < this.getMonth()) {
        this.setDate(0);
    }
};


function json2str(obj) {
    var S = [];
    for (var i in obj) {
        obj[i] = typeof obj[i] == 'string' ? '"' + obj[i] + '"' : (typeof obj[i] == 'object' ? json2str(obj[i]) : obj[i]);
        S.push(i + ':' + obj[i]);
    }
    return '{' + S.join(',') + '}';
}
String.prototype.replaceAll = function (reallyDo, replaceWith, ignoreCase) {
    if (!RegExp.prototype.isPrototypeOf(reallyDo)) {
        return this.replace(new RegExp(reallyDo, (ignoreCase ? "gi" : "g")), replaceWith);
    } else {
        return this.replace(reallyDo, replaceWith);
    }
}

//JS操作cookies方法!

//写cookies

function setCookie(name, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString() + ";path=/";
}

//读取cookies
function getCookie(name) {
    var _str = "";

    switch (name) {
        case "RegCheckCode":
        case "LoginCheckCode":
            _str = Common.CookiesData(name);
            break;
        default:
            var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
            if (arr = document.cookie.match(reg))
                _str = unescape(arr[2]);
            break;
    }
    return _str;
}

//根据名称获取Cookies的值
function getCookieValue(name) {

    var allcookies = document.cookie;
    //alert(document.cookie);

    //    var offset, cookieValue;
    //    var search = name + "=";
    //    if (document.cookie.length > 0) {
    //        offset = document.cookie.indexOf(search);
    //        if (offset != -1) {
    //            offset += search.length;
    //            end = document.cookie.indexOf(";", offset);
    //            if (end == -1)
    //                end = document.cookie.length;
    //            cookieValue = unescape(document.cookie.substring(offset, end));
    //        }
    //    }
    //    return cookieValue;
}

//删除cookies
function delCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null)
        document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}
//去登录
function goToLogin() {
    setCookie("fromurl", location.href);
    location.href = "/login.aspx";
}


//顯示Dialog for iframe
function showDialog(id, url, title, height, width) {
    jQuery("<div id='" + id + "' style='overflow:hidden;'></div>").append(jQuery("<iframe frameborder='0' width='98%' height='96%'  id='frm_" + id + "' src='" + url + "' ></iframe>")).dialog({
        show: "scale",
        autoOpen: true,
        modal: true,
        height: height,
        width: width,
        resizable: true,
        title: title,
        close: function () {
            try { parent.resizeIframe(false); } catch (e) { }
        },
        open: function (event, ui) {
            AdjustDialogButton(this);
        },
        position: {
            my: 'center top+1%',
            at: 'center top',
            of: 'body'
        }
        //position: ["center", resultY]
        //    position: ["center", "top"]
        //position:[{my: 'left top', at: 'left bottom', of: window}]
    });
    try { parent.resizeIframe(true); } catch (e) { }

}


function AdjustDialogButton(oThis) {
    var dw = $(oThis.parentElement).outerWidth();
    var bw = $(oThis.parentElement).find(".ui-dialog-buttonset").outerWidth();
    $(oThis.parentElement).find(".ui-dialog-buttonset").css("float", "none");
    $(oThis.parentElement).find(".ui-dialog-buttonset").css("width", (bw + 3) + "px");
    $(oThis.parentElement).find(".ui-dialog-buttonset").css("position", "relative");
    $(oThis.parentElement).find(".ui-dialog-buttonset").css("left", parseInt((dw - bw + 1) / 2) + "px");
    $($(oThis.parentElement).find(".ui-dialog-buttonset").find(".ui-button-text")[0]).css("background-color", "#79c4cb");
    $($(oThis.parentElement).find(".ui-dialog-buttonset").find(".ui-button-text")[0]).css("color", "#fff");
}

function IsDataTypeInt(value) {
    value = GetPositiveNumber(value);
    return (IsNumber(value));
}

function IsDataTypeFloat(value) {
    value = GetPositiveNumber(value);

    if (value.split(".").length > 2) {
        //不是正確格式的Float
        return (false);
    }
    else {
        for (var i = 0; i < value.split(".").length; i++) {
            if (!IsNumber(value.split(".")[i]))
                return (false);
        }
    }
    return (true);
}


//轉換Json格式(特殊字)
function string2Json(sourceStr) {
    if (typeof (sourceStr) != "undefined") {
        sourceStr = sourceStr.replace(/\\/g, "\\\\");
        //sourceStr = sourceStr.replace(/\b/g, "\\\b");
        sourceStr = sourceStr.replace(/\t/g, "\\\t");
        sourceStr = sourceStr.replace(/\n/g, "\\\n");
        sourceStr = sourceStr.replace(/\f/g, "\\\f");
        sourceStr = sourceStr.replace(/\r/g, "\\\r");
        return sourceStr.replace(/\"/g, "\\\"");
    }
    return "";
}
function Json2string(sourceStr) {
    if (typeof (sourceStr) != "undefined") {
        sourceStr = sourceStr.replace(/\\\\/g, "\\");
        //sourceStr = sourceStr.replace(/\\\b/g, "\b");
        sourceStr = sourceStr.replace(/\\\t/g, "\t");
        sourceStr = sourceStr.replace(/\\\n/g, "\n");
        sourceStr = sourceStr.replace(/\\\f/g, "\f");
        sourceStr = sourceStr.replace(/\\\r/g, "\r");
        return sourceStr.replace(/\\\"/g, "\"");
    }
    return "";
}
function close_iframe(_id) {
    if (jQuery("#" + _id + "").length == 1) {
        jQuery("#" + _id + "").remove();
    }
}

//获取url中data的参数 
function GetQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return (r[2]); return "";
}

//获取url中data的参数 
function GetSplitString(name, parm) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = parm.substr(1).match(reg);
    if (r != null) return (r[2]); return null;
}
//创建拼接参数
function UrlParm(arr_parm, arr_value) {
    if ((arr_parm.length != arr_value.length) || arr_parm.length == 0) {
        ShowTempMessage("参数错误", 3000);
        return false;
    }
    var url_parm = '';
    for (var i = 0; i < arr_parm.length; i++) {
        url_parm += i == 0 ? "?" : "&";
        url_parm += arr_parm[i] + "=" + arr_value[i];
    }
    return url_parm;
}



//判断是否是微信浏览器
function is_weixin() {
    var ua = navigator.userAgent.toLowerCase();
    if (ua.match(/MicroMessenger/i) == "micromessenger") {
        return true;
    } else {
        return false;
    }
}
//图片验证码
function setCode(type) {
    if (type == "reg")
        $('#img_yzm').attr('src', '/Code.aspx?action=' + type + '&t=' + new Date().getTime());
    else if (type == "login")
        $('#img_yzm').attr('src', '/Code.aspx?action=' + type + '&t=' + new Date().getTime());
    else
        $('#img_yzm').attr('src', '/Code.aspx?action=' + type + '&t=' + new Date().getTime());
}

//欢迎语提示
function WelcomeTip() {
    var _str = "您好！";
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();

    if (hours > 0)
        _str = "凌晨好！";
    if (hours >= 8)
        _str = "上午好！";
    if (hours >= 12)
        _str = "下午好！";
    if (hours >= 18)
        _str = "傍晚好！";
    if (hours >= 20)
        _str = "晚上好！";
    if (hours >= 22)
        _str = "深夜好！";

    return _str;
}



//倒数时间
function ShowCountDown(date, divid) {
    var now = new Date();
    var endDate = new Date((date));
    var leave_second_total = parseInt((endDate.getTime() - now.getTime()) / 1000);
    var leave_day = Math.floor(leave_second_total / (60 * 60 * 24));
    var leave_hour = Math.floor((leave_second_total - leave_day * 24 * 60 * 60) / 3600);
    var leave_minute = Math.floor((leave_second_total - leave_day * 24 * 60 * 60 - leave_hour * 60 * 60) / 60);
    var leave_second = Math.floor(leave_second_total - leave_day * 24 * 60 * 60 - leave_hour * 60 * 60 - leave_minute * 60);


    var number1, number2, number3, number4, number5, number6;
    number1 = Math.floor(leave_hour / 10);
    number2 = Math.floor(leave_hour % 10);
    number1 = number1 > 9 ? 9 : number1;
    number2 = number2 > 9 ? 9 : number2;
    number3 = leave_minute > 10 ? Math.floor(leave_minute / 10) : 0;
    number4 = leave_minute % 10;
    number5 = leave_second > 10 ? Math.floor(leave_second / 10) : 0;
    number6 = leave_second % 10;
    //时间的样式
    var html = '';
    if (leave_day > 0)
        html = "<span>" + leave_day + "</span>" + "<span>" + number1 + "</span><span>" + number2 + "</span>:<span>" + number3 + "</span><span>" + number4 + "</span>:<span>" + number5 + "</span><span>" + number6 + "</span>";
    else
        html += "<span>" + number1 + "</span><span>" + number2 + "</span>:<span>" + number3 + "</span><span>" + number4 + "</span>:<span>" + number5 + "</span><span>" + number6 + "</span>";

    if (leave_second_total > 0)
        $("#" + divid).html(html);
}

//图片前缀
function ImgUrl() {
    var imgurl = 'http://img.wangcl.com/';
    return imgurl;
}

//返回图片路径
function returnImgUrl(str) {
    if (str == "" || typeof (str) == "undefined" || typeof (str) != "string")
        return "";

    if (str.indexOf("http://") > -1)
        return str;
    else
        return ImgUrl() + str;
}

//商品详情图片替换
function ImgUrlReplace(str) {
    if (str == "" || typeof (str) == "undefined")
        return "";

    var strUrl = ImgUrl();

    if (str.indexOf("WAPDetailImg") > -1)
        str = str.Replace("WAPDetailImg", strUrl + "WAPDetailImg");

    if (str.indexOf("PCDetailImg") > -1)
        str = str.Replace("PCDetailImg", strUrl + "PCDetailImg");

    if (str.indexOf("/userfiles") > -1)
        str = str.Replace("/userfiles", strUrl + "/userfiles");

    if (str.indexOf("http://admin.wangcl.com/") > -1)
        str = str.Replace("http://admin.wangcl.com/", strUrl);


    return str;
}

//js参数过滤
function Filter_Js(str) {
    if (str == "" || typeof (str) == "undefined" || !str)
        return "";

    str = str.replace("<", "");
    str = str.replace(">", "");
    str = str.replace(";", "");
    str = str.replace("'", "");
    str = str.replace("/", "");
    str = str.replace("?", "");
    return str;
}

//判断字符串是否非空
function Verify_Str(str) {
    str = Filter_Js(str);
    if (str == "" || typeof (str) == "undefined" || !str)
        return false;

    return true;
}

//验证字符串是否为正数
function Verify_Num(str) {
    if (isNaN(str))
        return false;

    if (str < 1) return false;

    return true;
}

//限定标题长度
function LimitTitleLen(str, ilenth) {
    if (str.length > ilenth)
        str = str.substring(0, ilenth) + '...';

    return str;
}

//判断手机号码是否正确
function VerifyMobile(str) {
    var reg = /(^0*(13|14|15|17|18)\d{9}$)/;
    if (!reg.test(str)) {
        ShowTempMessage("请输入正确的手机号码", 3000);
        return false;
    }
    return true;
}

//验证邮箱是否正确
function VerifyEmail(str) {
    var reg = /(^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$)/;
    if (!reg.test(str)) {
        ShowTempMessage("请输入正确的邮箱", 3000);
        return false;
    }
    return true;
}

//浏览器版本
function getBrowser() {
    return browser = {
        versions: function () {
            var u = navigator.userAgent, app = navigator.appVersion;
            return {//移动终端浏览器版本信息 
                trident: u.indexOf('Trident') > -1, //IE内核
                presto: u.indexOf('Presto') > -1, //opera内核
                webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
                gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
                mobile: !!u.match(/AppleWebKit.*Mobile.*/) || !!u.match(/AppleWebKit/), //是否为移动终端
                ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
                android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或者uc浏览器
                iPhone: u.indexOf('iPhone') > -1 || u.indexOf('Mac') > -1, //是否为iPhone或者QQHD浏览器
                iPad: u.indexOf('iPad') > -1, //是否iPad
                webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
            };
        }(),
        language: (navigator.browserLanguage || navigator.language).toLowerCase()
    }
}

//获取终端类型
function getTerminal() {
    var terminal = getCookie("platform");
    if (terminal == "" || typeof (terminal) == "platform")
        terminal = "wap";

    return terminal;
}







//添加loadinghtml效果
function _html(_this, id) {
    var _html = "";
    _html += '<div id="' + id + '" class="spinner_l">';
    _html += '<div class="spinner-container container1">';
    _html += '<div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div>';
    _html += '</div>';
    _html += '<div class="spinner-container container2">';
    _html += '<div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div>';
    _html += '</div>';
    _html += '<div class="spinner-container container3">';
    _html += '<div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div>';
    _html += '</div>';
    _html += '</div>';

    $(_this).html(_html);
}
//还原加载效果
function ShowButton(_this, _value) {
    if (_value == '' || typeof (_value) == "undefined")
        return "";

    $(_this).html(_value);
}

//添加loadinghtml效果
function dialog_html() {
    var _html = "";
    if ($(".spinner_l_wrap").length > 0) {
        return false;
    }
    _html += '<div class="spinner_l_wrap"><div class="spinner_l">';
    _html += '<div class="spinner-container container1">';
    _html += '<div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div>';
    _html += '</div>';
    _html += '<div class="spinner-container container2">';
    _html += '<div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div>';
    _html += '</div>';
    _html += '<div class="spinner-container container3">';
    _html += '<div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div>';
    _html += '</div>';
    _html += '</div></div>';
    $("body").append(_html);
}

//删除loadinghtml效果
function del_dialog() {
    $(".spinner_l_wrap").remove();
}


//登陆按钮的效果样式
function LoginScrollHtml(id) {
    var _html = '<div class="spinner_l"><div class="spinner-container container1"><div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div></div><div class="spinner-container container2"><div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div></div><div class="spinner-container container3"><div class="circle1"></div><div class="circle2"></div><div class="circle3"></div><div class="circle4"></div></div></div>';

    $("#" + id).append(_html);
}
//删除按钮效果样式
function DelLoginScrollHtml(id) {
    $("#" + id).find(".spinner_l").remove();
}

function errorImg(img) {
    img.src = "/images/14.png";
    img.onerror = null;
}

//showtype 为图片或者文字 2 卡通图片 1 图片+文字 0图片 
//$container容器对象
function showWebIM($container, showtype) {
    try {
        var type = 0; //gw
        var platform = getCookie("platform"); //登陆平台
        var data_type = '', out_id = '';
        if ($.type(userInfo) == "object") {
            data_type = userInfo.data_type;
            out_id = userInfo.out_id;
        }

        switch (data_type.toUpperCase()) {
            case "WX":
                type = 1;
                break;
        }
        if (platform == "ios") {
            type = 11;
        }
        else if (platform == "AndroidApp") {
            type = 10;
        }
        webIMPluginForGW.showWebIM($container, showtype, type, out_id, true, data_type);
    }
    catch (err) { }
}




//start-----------------------设置APP片段--------------------------------
//androidAPP相关事件注册
function myHeadPhoto() {
    javascript: window.wangcl.skipToMyHeadPhoto();
}
function returnBack() {
    javascript: window.wangcl.skipToReturnBack();
}
//end-----------------------设置APP片段--------------------------------
//测试路径
function test_url(type) {
    var url = "";
    //        if (type == "debug")
    //           url = "/wapWeb";
    return url;
}

//.iframe_pop_wrap用于显示弹出层的全屏半透明背景
//.layer_top用于显示顶部文字信息，通过布尔变量isHideHead进行控制
//.close_iframe用于控制弹框的关闭

function openIframe(url, isHideHead, cssParams, title) {
    //先判断当前页面是否含有带#pop_iframe的iframe
    var $popIframeWrap = $(".iframe_pop_wrap");
    //在已经创建了的情况下
    if ($popIframeWrap.length <= 0) {
        $popIframeWrap = $('<div class="iframe_pop_wrap" style="position: fixed;top:0;left:0;width:100%;bottom:0;background: rgba(0,0,0,.5);z-index:1000;display: none;"><p class="layer_top" style="width:94%;text-align: center;color:#717171;background:#f5f5f5;font-size: 1.75rem; height:49px;line-height: 49px;position: fixed;bottom:68%;left:0;z-index:2;padding-left:3%;padding-right:3%;"><span></span><span class="close_iframe" style="position: absolute;display: block;right:0;top:0;height:100%;padding:0 1.7%;width:3.4%;cursor: pointer;background-repeat: no-repeat;background-position: center center;background-size: 50%;' + 'background-image: url("/images/closebtn_slector.png");' + '"></span></p><iframe id="pop_iframe" src="" ></iframe></div>');
        $("body").append($popIframeWrap);
        //注册关闭事件

        var $closeIframeBtn = $popIframeWrap.find(".close_iframe");
        $closeIframeBtn.bind("click", function () {
            $popIframeWrap.hide();
        });
    }

    var $pop_iframe = $popIframeWrap.find("iframe");
    if (!cssParams) {
        cssParams = { width: "100%", height: "100%", bottom: "0", position: "absolute", left: "0", "right": "0", "overflow": "auto", border: "none" };
    }
    $pop_iframe.css(cssParams);

    /*取出iframe之前的src，两者进行对比,如果该src已经打开过，则无需再次打开*/
    var iframePrevSrc = $pop_iframe.attr("src");
    var reg = new RegExp(url);
    if (!reg.test(iframePrevSrc)) {
        $pop_iframe.attr("src", url);
        if (isHideHead) {
            $popIframeWrap.find(".layer_top").hide();
        } else {
            $popIframeWrap.find(".layer_top").show().find("span:first").html(title);
        }
    }
    $popIframeWrap.show();
    setBodyToOverflowHidden();
}

function openIframe2(url, isHideHead, cssParams, title) {
    //先判断当前页面是否含有带#pop_iframe的iframe
    var $popIframeWrap = $(".iframe_pop_wrap");
    var $popIframe = $("#pop_iframe");
    $popIframe.attr("src", url);
    if (!cssParams) {
        cssParams = { width: "100%", height: "100%", bottom: "0", position: "absolute", left: "0", "right": "0", "overflow": "auto", border: "none" };
    }
    if (isHideHead) {
        $popIframeWrap.find(".layer_top").hide();
    } else {
        $popIframeWrap.find(".layer_top").show().find("span:first").html(title);
    }
    $popIframe.css(cssParams);
    $popIframeWrap.show();
    setBodyToOverflowHidden();
}


//  end----------2016-7-11新增脚本

//点击背景，关闭iframe弹框
function regModalCloseEvent() {
    /*使用委托进行事件注册*/
    $("body").bind("click", function (e) {
        var $target = $(e.target);
        if ($target.hasClass("iframe_pop_wrap")) {
            $target.hide();
            setBodyToOverflowOri();
            return false;
        }
    });

    var $popIframeWrap = $(".iframe_pop_wrap");
    var $closeIframeBtn = $popIframeWrap.find(".close_iframe");
    $closeIframeBtn.bind("click", function () {
        $popIframeWrap.hide();
        setBodyToOverflowOri();
    });
}

//关闭iframe页面中关闭弹框
function closePopWindowInIframePage() {
    var $popIframe = $('#pop_iframe', parent.document);
    var $parentPageBody = $('body', parent.document);
    $popIframe.parent().hide();
    $parentPageBody.removeClass("body_overflow_hidden");
}

//调出弹出层之前执行
function setBodyToOverflowHidden() {
    $("body").addClass("body_overflow_hidden");
}
//调出弹出层之后执行
function setBodyToOverflowOri() {
    $("body").removeClass("body_overflow_hidden");
}
function startAjax() {
    $("#ajax_loading_bg,#ajax_loading").show();
}

function endAjax() {
    $("#ajax_loading_bg,#ajax_loading").hide();
}

//给body添加类名和id
function addClassAndIdToBody(classname, idname) {
    if (Verify_Str(classname))
        $("body").addClass(classname);
    if (Verify_Str(idname))
        $("body").attr("id", idname);
}
