var client = function () {
    //rendering engines
    var engine = {
        ie: 0,
        gecko: 0,
        webkit: 0,
        khtml: 0,
        opera: 0,
        //complete version
        ver: null
    };

    //browsers
    var browser = {
        //browsers
        ie: 0,
        firefox: 0,
        safari: 0,
        micromessenger: 0,
        konq: 0,
        opera: 0,
        chrome: 0,
        edge: 0,
        theworld: 0,
        baidu: 0,
        qq: 0,
        sougou: 0,
        liebao: 0,
        taobao: 0,
        aoyou: 0,
        se360: 0,
        ee360: 0,
        aoyou: 0,
        //specific version
        ver: null
    };

    //platform/device/OS
    var system = {
        win: false,
        mac: false,
        x11: false,

        //mobile devices
        iphone: false,
        ipod: false,
        ipad: false,
        ios: false,
        android: false,
        nokiaN: false,
        winMobile: false,

        //game systems
        wii: false,
        ps: false
    };

    //detect rendering engines/browsers
    var ua = navigator.userAgent;
    if (window.opera) {
        engine.ver = browser.ver = window.opera.version();
        engine.opera = browser.opera = parseFloat(engine.ver);
    } else if (/Edge\/(\S+)/.test(ua)) {
        browser.ver = RegExp["$1"];
        browser.edge = parseFloat(browser.ver);
    } else if (/THEWORLD/.test(ua)) {
        browser.ver = "1.1";
        browser.theworld = browser.ver;
    } else if (/BIDUBrowser\/(\S+)/.test(ua)) {
        browser.ver = RegExp["$1"];
        browser.baidu = parseFloat(browser.ver);
    } else if (/MicroMessenger\/(\S+)/.test(ua)) {
        browser.ver = RegExp["$1"];
        browser.micromessenger = parseFloat(browser.ver);
    } else if (/QQBrowser\/(\S+)/.test(ua)) {
        browser.ver = RegExp["$1"];
        browser.qq = parseFloat(browser.ver);
    } else if (/MetaSr (\S+)/.test(ua)) {
        browser.ver = RegExp["$1"];
        browser.sougou = parseFloat(browser.ver);
    } else if (/LBBROWSER/.test(ua)) {
        browser.ver = "1.1";
        browser.liebao = parseFloat(browser.ver);
    } else if (/TaoBrowser\/(\S+)/.test(ua)) {
        browser.ver = RegExp["$1"];
        browser.taobao = parseFloat(browser.ver);
    } else if (/Maxthon\/(\S+)/.test(ua)) {
        browser.ver = RegExp["$1"];
        browser.aoyou = parseFloat(browser.ver);
    } else if (/360se/i.test(ua)) {
        browser.ver = "1.1";
        browser.se360 = parseFloat(browser.ver);
    } else if (/360ee/i.test(ua)) {
        browser.ver = "1.1";
        browser.ee360 = parseFloat(browser.ver);
    }
    else if (/AppleWebKit\/(\S+)/.test(ua)) {
        engine.ver = RegExp["$1"];
        engine.webkit = parseFloat(engine.ver);
        //figure out if it's Chrome or Safari
        if (/Chrome\/(\S+)/.test(ua)) {
            browser.ver = RegExp["$1"];
            browser.chrome = parseFloat(browser.ver);
        } else if (/Version\/(\S+)/.test(ua)) {
            browser.ver = RegExp["$1"];
            browser.safari = parseFloat(browser.ver);
        } else {
            //approximate version
            var safariVersion = 1;
            if (engine.webkit < 100) {
                safariVersion = 1;
            } else if (engine.webkit < 312) {
                safariVersion = 1.2;
            } else if (engine.webkit < 412) {
                safariVersion = 1.3;
            } else {
                safariVersion = 2;
            }

            browser.safari = browser.ver = safariVersion;
        }
    } else if (/KHTML\/(\S+)/.test(ua) || /Konqueror\/([^;]+)/.test(ua)) {
        engine.ver = browser.ver = RegExp["$1"];
        engine.khtml = browser.konq = parseFloat(engine.ver);
    } else if (/rv:([^\)]+)\) Gecko\/\d{8}/.test(ua)) {
        engine.ver = RegExp["$1"];
        engine.gecko = parseFloat(engine.ver);

        //determine if it's Firefox
        if (/Firefox\/(\S+)/.test(ua)) {
            browser.ver = RegExp["$1"];
            browser.firefox = parseFloat(browser.ver);
        }
    } else if (/MSIE ([^;]+)/.test(ua)) {
        engine.ver = browser.ver = RegExp["$1"];
        engine.ie = browser.ie = parseFloat(engine.ver);
    } else if (/rv:([^\)]+)\) like Gecko/.test(ua)) {
        engine.ver = browser.ver = RegExp["$1"];
        engine.ie = browser.ie = parseFloat(engine.ver);
    }

    //detect browsers
    browser.ie = engine.ie;
    browser.opera = engine.opera;

    //detect platform
    var p = navigator.platform;
    system.win = p.indexOf("Win") == 0;
    //    alert(navigator.platform);
    system.mac = p.indexOf("Mac") == 0;
    system.x11 = (p == "X11") || (p.indexOf("Linux") == 0);
    //detect windows operating systems
    if (system.win) {
        if (/Win(?:dows )?([^do]{2})\s?(\d+\.\d+)?/.test(ua)) {
            if (RegExp["$1"] == "NT") {
                switch (RegExp["$2"]) {
                    case "5.0":
                        system.win = "2000";
                        break;
                    case "5.1":
                        system.win = "XP";
                        break;
                    case "6.0":
                        system.win = "Vista";
                        break;
                    case "6.1":
                        system.win = "7";
                        break;
                    case "6.2":
                        system.win = "8";
                        break;
                    case "6.3":
                        system.win = "8.1";
                        break;
                    case "6.4":
                        system.win = "10";
                        break;
                    case "10.0":
                        system.win = "10";
                        break;
                    default:
                        system.win = "NT";
                        break;
                }
            } else if (RegExp["$1"] == "9x") {
                system.win = "ME";
            } else {
                system.win = RegExp["$1"];
            }
        }
    }

    //mobile devices
    system.iphone = ua.indexOf("iPhone") > -1;
    if (system.iphone) {
        if (/CPU iPhone OS (\d+.\d+)/.test(ua)) {
            system.iphone = "IOS " + RegExp["$1"];
        }
    }

    system.ipod = ua.indexOf("iPod") > -1;
    if (system.ipod) {
        if (/ OS (\d+.\d+)/.test(ua)) {
            system.ipod = "IOS " + RegExp["$1"];
        }
    }


    system.ipad = ua.indexOf("iPad") > -1;
    if (system.ipad) {
        if (/ OS (\d+.\d+)/.test(ua)) {
            system.ipad = "IOS " + RegExp["$1"];
        }
    }

    system.nokiaN = ua.indexOf("NokiaN") > -1;

    //windows mobile
    if (system.win == "CE") {
        system.winMobile = system.win;
    } else if (system.win == "Ph") {
        if (/Windows Phone OS (\d+.\d+)/.test(ua)) {
            ;
            system.win = "Phone";
            system.winMobile = parseFloat(RegExp["$1"]);
        }
    }

    //determine iOS version
    if (system.mac && ua.indexOf("Mobile") > -1) {
        if (/CPU (?:iPhone )?OS (\d+_\d+)/.test(ua)) {
            system.ios = parseFloat(RegExp.$1.replace("_", "."));
        } else {
            system.ios = 2;  //can't really detect - so guess
        }
    }

    //determine Android version
    if (/Android (\d+\.\d+)/.test(ua)) {
        system.android = RegExp.$1;
    }

    //gaming systems
    system.wii = ua.indexOf("Wii") > -1;
    system.ps = /playstation/i.test(ua);

    return {
        engine: engine,
        browser: browser,
        system: system
    };

} ();

//获取参数
var _url = document.getElementById("wangcl_tracking");
var _src = _url.getAttribute("src");
var _parm_T = "?key=" + _src.substring(_src.indexOf('?') + 1).replace("key=", "");

var _parm = GetUrlParm("key", _parm_T);
var _box_code = GetUrlParm("box_code", _parm_T);
var _fenzhan = GetUrlParm("fenzhan", _parm_T);
var _bd_latitude = GetUrlParm("bd_latitude", _parm_T); //纬度
var _bd_longitude = GetUrlParm("bd_longitude", _parm_T); //经度

//console.log(_box_code);
//console.log(_fenzhan);
//console.log(_bd_latitude);
//console.log(_bd_longitude);


/*
该函数主要用于存储一些和用户所访问的页面相关的属性；并定义一些属性，并为其设置初始值
*/
function _wcl(cli, camp, bsU, cnt, slt, adt) {
    //_c:本对象；v:版本号； _c_jsF ??（未知）；folder：文件夹；cli：wangcl_t自身所携带的参数 ；camp：？？未知；bsU：？？未知
    var _c = this; _c.v = "4.2.0"; _c_jsF = "trackPageview2"; _c.folder = "webservice"; _c.cli = cli; _c.camp = camp; _c.bsU = bsU;

    //baseHost：域名或者ip地址
    _c.baseHost = location.hostname;
    //prot:协议，例如http、ftp等
    _c.prot = location.protocol == "https:" ? "https:" : "http:";
    //prefolder:点击事件和浏览记录事件所对应的aspx页面被访问的公共部分
    _c.prefolder = _c.prot + "//" + _c.bsU + "/" + _c.folder;
    //将浏览记录的url放入到两个属性中
    _c.trackURL = _c.prefolder + "/counter/CSTrack.aspx";
    _c.trackURL2 = _c.prefolder + "/counter/CSTrack.aspx";
    //_c.totalURL = _c.prefolder + "/counter/ad_total_open.aspx"; 
    //_c.eventURL = _c.prefolder + "/counter/wangcl_event.aspx";
    //_c.effectURL = _c.prefolder + "/counter/WebMAX_Effect.aspx"; 
    _c.addsURL = _c.prefolder + "/counter/add_s.aspx";
    //dummyURL：一个虚设的路径
    _c.dummyURL = _c.prot + "//" + _c.baseHost + "/wclDummy.htm";
    _c.cnt = cnt; _c.slt = slt; _c.adt = adt;
    //sUID：判断浏览器是否启动了cookie设置，若设置了则将sUID的值设置为W_S_UID
    _c.sUID = navigator.cookieEnabled ? "W_S_UID" : "-1";
    //from: 本页面是从哪个页面的链接跳转而来
    _c.from = document.referrer;
    //_c.URL=_c.baseURI=document.URL.replace(location.hash,"").replace(/#+$/,"");
    //URL:存放#及其后面的内容，#的内容通常是由于点击事件造成的如页面内部之间的跳转
    _c.URL = _c.baseURI = document.URL.replace(/#+$/, "");
    //title:用于存放页面的名称；aFields：未知；aImg：用于存放图片的某种信息；uli：用于存放无序列表的项；u1、u2：未知；debug：未知；cAD：未知
    _c.title = document.title; _c.aFields = []; _c.aImg = []; _c.uli; _c.u1; _c.u2; _c.debug = 0; _c.cAD = 0;
    _c.cookie3Enabled = 1; _c.pc_expired_months = 24; _c.sc_expired_mins = 30;
    //alert(_c.cookie3Enabled);
    if (_c.adt == "2") _c.adT();

}
/*
设置原型
*/
_wcl.prototype = {
    enc: function (str) {
        try { str = /%u/.test(str) ? unescape(str) : decodeURIComponent(str); } catch (err) { }
        return /[^\x00-\x7F]/.test(str) ? escape(str) : encodeURIComponent(str)
    },

    gC: function (name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    },

    //Url
    gU: function (sUrl) { sUrl = sUrl.replace(/^(.)*:\/\//, ""); return sUrl.replace(/\?.+/, ""); },
    //对url 参数排序
    gP: function (sUrl) { return /\?/.test(sUrl) ? sUrl.replace(/[^\?]+\?/, "").split("&").sort().join("&").toLowerCase() : "" },
    /*重置或者添加键值对，同时存储值得长度*/
    adF: function (iKey, sValue, nLen) {
        if (sValue == undefined) return;
        this.clF(iKey);
        this.aFields.push([iKey, sValue, nLen]);
    },
    /*删除对应的元素*/
    clF: function (sID) {
        for (var i = 0; i < this.aFields.length; i++)
            if (this.aFields[i][0] == sID) this.aFields.splice(i, 1);
    },
    gTS: function (aFields) {
        var aItems = aFields || this.aFields; var aF = [];
        for (var i = 0; i < aItems.length; i++)
            aF.push(aItems[i][0] + "=" + this.enc(String(aItems[i][1]).substring(0, isNaN(aItems[i][2]) ? 32 : aItems[i][2])));
        return (aF.join("&"));
    },
    adT: function (sAd) {
        if (sAd) return this.track(this.eventURL + "?" + sAd);
        if (this.from == this.URL) return; var _c = this;
        if (document.all) attachEvent("onload", function () { _c.track(_c.totalURL) });
        else addEventListener("load", function () { _c.track(_c.totalURL) }, false);
    },
    imgSrc: function (sUrl) {
        var oImg = new Image(1, 1); oImg.src = sUrl; oImg.onabort = function () { oImg.src = sUrl }; return oImg;
    },
    track: function (sUrl) { this.debug ? alert(unescape(sUrl)) : this.aImg.push(this.imgSrc(sUrl)) },

    trackPageview1: function (sMode, sPath, sTitle, sAttr, bCounter) {
        var _c = this; var sURL; var ev;
        _c.aFields = [];
        var sAd = _c.URL.match(/\?wclad=\w+:\w+:\w+$|&wclad=\w+:\w+:\w+|wclad=\w+:\w+:\w+&/);
        if (sAd) {
            _c.URL = _c.URL.replace(sAd, ""); _c.baseURI = _c.baseURI.replace(sAd, "");
            _c.adT(String(sAd).replace(/[\?&]/, "").split("=")[1]);
        }
        switch (sMode ? sMode.toUpperCase() : "") {
            case "S":
                sURL = _c.prot + "//" + _c.baseHost + sPath;
                break;
            case "E":
                ev = _c.cnt;
                if (bCounter) _c.adF("this_url", _c.URL == _c.dummyURL ? _c.baseURI : _c.URL, 1024);
                else _c.cnt = "0";
                sTitle = _c.title; sURL = _c.dummyURL; _c.URL = _c.from;
                var aE = sPath.split("/");
                for (var i = 1; i < aE.length; i++) {
                    if (i > 5 || !aE[i]) break;
                    _c.adF(14 + i, aE[i], 32);
                }
                break;
            case "D":
                var aP = sPath.split("/");
                var aL = [];
                for (var i = 1; i < aP.length; i++) {
                    if (i > 5 || !aP[i]) break;
                    aL.push("L" + i + "=" + aP[i]);
                }
                sURL = _c.prot + "//" + _c.baseHost + location.pathname + "?" + aL.join("&");
                break;
            default:
                break;
        }
        if (sURL) {
            _c.title = sTitle || sPath;
            _c.from = _c.URL; _c.URL = sURL;
        }
        _c.adF("cli", _c.cli); _c.adF("countertracking", _c.cnt);
        _c.adF("sltracking", _c.slt); _c.adF("uid", _c.sUID);
        _c.adF(1, _c.URL, 1024); _c.adF(3, "W_S_SC");
        _c.adF(9, _c.from, 1024); _c.adF(11, _c.camp, 32);
        _c.adF(22, navigator.userAgent, 128); _c.adF(23, screen.width);
        _c.adF(24, screen.height);
        if (_c.cnt == "1") {
            if (!_c.title) {
                _c.title = _c.URL.replace(/\?.+/, ""); _c.title = /\/$/.test(_c.title) ? _c.title : _c.title.replace(/^.+\//, "")
            }
            _c.adF("pname", _c.title);
        }
        if (ev) _c.cnt = ev;
        if (_c.slt == "1") {
            _c.adF(2, _c.sUID); _c.adF(5, "W_S");
            _c.adF(6, "W_S_DATE"); _c.adF(7, "W_S");
            _c.adF(8, "W_S"); _c.adF(10, "W_S_IP");
            _c.adF(20, _c.gU(ev ? _c.baseURI : _c.URL), 512);
            _c.adF(21, _c.gP(ev ? _c.baseURI : _c.URL), 1024);
        }
        var sAcc = _c.gC("Wangcl_Account");
        if (sAcc) _c.adF(4, sAcc, 32);
        if (_c.uli && (_c.u1 || sAcc)) {
            _c.adF("uli", _c.uli);
            _c.adF("u1", _c.u1 || sAcc);
            var sK2 = _c.gC("WMX_Key2");
            if (_c.u2 || sK2) _c.adF("u2", _c.u2 || sK2);
        }
        if (sAttr) {
            var aP = sAttr.split("/");
            if (aP[1]) _c.adF(12, aP[1], 32); if (aP[2]) _c.adF(13, aP[2], 128);
            if (aP[3]) _c.adF(15, aP[3], 32); if (aP[4]) _c.adF(16, aP[4], 32);
            if (aP[5]) _c.adF(17, aP[5], 32); if (aP[6]) _c.adF(18, aP[6], 32);
            if (aP[7]) _c.adF(19, aP[7], 32);
        }
        _c.adF(28, document.URL, 250);
        if (_c.cAD == 0 && _c.adt == "1") { _c.track(_c.totalURL); _c.cAD++; }
        var trackURL = _c.trackURL;
        _c.track(trackURL + "?" + _c.gTS());
    },

    trackEffect: function (nSno, nAmt, sUser, cli, camp) {
        if (!nSno) return; this.aFields = [];
        this.adF("mem_id", cli || this.cli); this.adF("cam_id", camp || this.camp);
        this.adF("sno", nSno); if (nSno == 7 && nAmt) this.adF("amount", nAmt);
        if (nSno == 4 && sUser) this.adF("uid", sUser, 128);
        this.track(this.effectURL + "?" + this.gTS());
    },
    trackEvent: function (sPath, sAttr, bCounter) {
        if (!sPath) return;
        this.trackPageview("E", sPath, "", sAttr, bCounter);
    },
    trackNewpage: function (sPath, sTitle, sAttr) {
        if (!sPath) return;
        this.trackPageview("S", sPath, sTitle, sAttr);
    },
    trackParams: function (sPath, sTitle, sAttr) {
        if (!sPath) return;
        this.trackPageview("D", sPath, sTitle, sAttr);
    },

    extend: function (fname, fn, objTracker) {
        var wT = objTracker || this;
        wT[fname] = fn;
    },

    wclTracking: function () { this.trackPageview() },

    gD: function (sUrl) { var myArray = sUrl.match(/\w+:\/\/([^\/:]+)/); return (myArray ? myArray[1] : "") },

    adC: function (name, value, mins) {
        var expires = ""; if (mins > 0) {
            var date = new Date(); date.setTime(date.getTime() + (mins * 60 * 1000));
            var expires = "; expires=" + date.toGMTString();
        } document.cookie = name + "=" + value + expires + "; path=/";
    },

    hashID: function () {
        var s = []; var hexDigits = "0123456789ABCDEF";
        for (var i = 0; i < 32; i++) { s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1); }
        s[12] = "4"; s[16] = hexDigits.substr((s[16] & 0x3) | 0x8, 1);
        var uuid = s.join(""); var hash = 0;
        if (uuid != null && typeof (uuid) != "undefined" && uuid != "") {
            var MAX_VALUE = 0x7fffffff;
            var MIN_VALUE = -0x80000000;
            for (var i = 0; i < uuid.length; i++) {
                hash = hash * 31 + uuid.charCodeAt(i);
                if (hash > MAX_VALUE || hash < MIN_VALUE)
                    hash &= 0xFFFFFFFF;
            }
        }
        return hash;
    },

    gCamC: function (is_session) {
        var oArray2 = new Array();
        var cname = is_session ? this.cli + "_session_wcl" : this.cli + "_wcl";
        var value = this.gC(cname);
        if (value == null || value == "") return (oArray2);
        var oArray = value.split('|');
        for (var i = 0; i < oArray.length; i++) {
            if (oArray[i].indexOf("SessionCookie=") == 0 || oArray[i].indexOf("Uid=") == 0)
                oArray2[oArray2.length] = oArray[i];
            else if (oArray[i].toLowerCase().indexOf(this.camp.toLowerCase() + "_") == 0)
                oArray2[oArray2.length] = oArray[i];
        }
        return (oArray2);
    },

    sCamC: function (is_session, oArray2) {
        if (oArray2 == null) return (true);

        var oArray = null;
        var cname = is_session ? this.cli + "_session_wcl" : this.cli + "_wcl";
        var mins = is_session ? this.sc_expired_mins : this.pc_expired_months * 30 * 60;
        var value = this.gC(cname);
        if (value == null || value == "")
            oArray = new Array();
        else
            oArray = value.split('|');
        for (var i = 0; i < oArray2.length; i++) {
            var bFind = false;
            var oArray3 = oArray2[i].split('=');
            for (var j = 0; j < oArray.length; j++) {
                if (oArray[j].toLowerCase().indexOf(oArray3[0].toLowerCase() + '=') == 0) {
                    oArray[j] = oArray2[i];
                    bFind = true;
                    break;
                }
            }
            if (bFind == false) oArray[oArray.length] = oArray2[i];
        }

        value = "";
        for (var i = 0; i < oArray.length; i++) {
            if (i > 0) value += "|";
            value += oArray[i];
        }
        this.adC(cname, value, mins);
    },

    gVarV: function (oArray, key) {
        if (oArray == null || oArray.length <= 0 || key == "") return ("");
        for (var i = 0; i < oArray.length; i++) {
            if (oArray[i].toLowerCase().indexOf(key.toLowerCase()) == 0)
                return (oArray[i].substr(key.length + 1));
        }
        return ("");
    },

    sVarV: function (oArray, key, value) {
        if (oArray == null || key == "") return (false);
        var index = -1;
        for (var i = 0; i < oArray.length; i++) {
            if (oArray[i].toLowerCase().indexOf(key.toLowerCase() + "=") == 0) {
                index = i;
                break;
            }
        }
        if (index < 0) index = oArray.length;
        oArray[index] = key + "=" + value;
        return (true);
    },

    gTimeS: function (d) {
        return (d.getFullYear() + '/' + (d.getMonth() + 1) + '/' + d.getDate() + ' ' +
            (d.getHours() < 10 ? '0' : '') + d.getHours() + ':' + (d.getMinutes() < 10 ? '0' : '') + d.getMinutes() + ':' + (d.getSeconds() < 10 ? '0' : '') + d.getSeconds());
    },
    //sMode 方式[E]  sPath路径:/会员登录/用户不存在或密码错误  sTitle 标题:会员登录 sAttr属性:用户不存在或密码错误 bCounter 统计:1
    trackPageview2: function (sMode, sPath, sTitle, sAttr, bCounter) {
        var _c = this; var sURL; var ev;
        _c.aFields = [];
        var br = "";
        var OS = "";
        var OsType = "";
        var OsVersion = "";

        var curBrowser = client.browser;
        for (var i in curBrowser) {
            if (curBrowser[i] != 0 && i != "ver") {
                br = i + "" + curBrowser["ver"];
            }
        }

        var curSystem = client.system;
        for (var i in curSystem) {
            if (curSystem[i] != 0) {
                OsType = i;
                OsVersion = curSystem[i];
            }
        }

        if (OsVersion === true) {
            OS = OsType;
        }
        else if (OsType == "iphone" || OsType == "ipod" || OsType == "ipad") {
            OS = OsVersion;
        } else {
            OS = OsType + "" + OsVersion;
        }
        //过滤变量OS
        OS = filterExtraChars(OS, "system");
        br = filterExtraChars(br, "browser");

        //判断登陆账号信息
        var sAd = _c.URL.match(/\?wclad=\w+:\w+:\w+$|&wclad=\w+:\w+:\w+|wclad=\w+:\w+:\w+&/);
        if (sAd) {
            _c.URL = _c.URL.replace(sAd, ""); _c.baseURI = _c.baseURI.replace(sAd, "");
            _c.adT(String(sAd).replace(/[\?&]/, "").split("=")[1]);
        }
        var params = "";
        switch (sMode ? sMode.toUpperCase() : "") {
            case "S":
                sURL = _c.prot + "//" + _c.baseHost + sPath;
                break;
            case "E":
                //this_url 获取路径时间的路径
                if (bCounter) _c.adF("this_url", _c.URL == _c.dummyURL ? _c.baseURI : _c.URL, 1024);
                else _c.cnt = "0";
                sTitle = _c.title; //页面标题 sURL=_c.dummyURL;_c.URL=_c.from;
                sURL = (_c.dummyURL.indexOf("/wclDummy.htm") > 0) ? _c.baseURI : _c.dummyURL; //获取路径时间的路径
                _c.adF(18, "1"); //是否属于事件
                var i = 0;
                var pathReserveCnt = 5;
                //下面将按钮传递过来的按钮名称和按钮值拆分
                if (sPath) {
                    var aP = sPath.split("/");
                    if (!sAttr || aP.length > 5)
                        pathReserveCnt = aP.length - 1;
                    for (i = 1; i <= pathReserveCnt; i++) {
                        params += "/";
                        if (i < aP.length && aP[i] != "") {
                            _c.adF('event' + i, aP[i], 32);
                            params += aP[i].substr(0, 32);
                            //if (i == 2)
                            //sTitle = aP[i];
                        }
                    }
                }
                if (sAttr && pathReserveCnt <= 5) {
                    var aE = sAttr.split("/");
                    for (var j = 1; j < aE.length; j++) {
                        if (j > 5) break;
                        params += "/";
                        if (j < aE.length && aE[j] != "") { _c.adF(30 + j, aE[j], 32); params += aE[j].substr(0, 32); }
                    }
                }
                sAttr = "";
                //sURL+= params;
                break;
            case "D":
                var aP = sPath.split("/");
                var aL = [];
                for (var i = 1; i < aP.length; i++) {
                    if (i > 5 || !aP[i]) break;
                    aL.push("L" + i + "=" + aP[i]);
                }
                sURL = _c.prot + "//" + _c.baseHost + location.pathname + "?" + aL.join("&");
                break;
            default:
                break;
        }
        if (sURL) {
            _c.title = sTitle || sPath;
            _c.from = _c.URL; _c.URL = sURL;

            _c.from = filterExtraChars(_c.from, "url");
            _c.URL = filterExtraChars(_c.URL, "url");
        }

        var uid = "W_S_UID";
        var scid = "W_S_SC";
        var pc = sc = "";
        var d = new Date();
        if (navigator.cookieEnabled && _c.cookie3Enabled == 0) {
            var newvisitor = 0;
            var newvisit = 0;
            var oPCArray = _c.gCamC(false);
            var oSCArray = _c.gCamC(true);
            uid = _c.gVarV(oPCArray, "Uid");
            if (uid == null || uid == "" || uid == "0") {
                newvisitor = 1;
                uid = _c.hashID() + d.getTime();
                _c.sVarV(oPCArray, "Uid", uid);
            }
            var curr_time = _c.gTimeS(d);
            var prev_first_time = _c.gVarV(oPCArray, _c.camp + "_First_VisitTime");
            var prev_last_time = _c.gVarV(oPCArray, _c.camp + "_Last_VisitTime");
            var prev_visitor_last_time = _c.gVarV(oPCArray, _c.camp + "_Visitor_LastTime");
            if (prev_first_time == null || prev_first_time == "")
                _c.sVarV(oPCArray, _c.camp + "_First_VisitTime", curr_time);
            _c.sVarV(oPCArray, _c.camp + "_Last_VisitTime", curr_time);
            _c.sVarV(oPCArray, _c.camp + "_Visitor_LastTime", curr_time);

            scid = _c.gVarV(oSCArray, "SessionCookie");
            var prev_visit_last_time = _c.gVarV(oSCArray, _c.camp + "_Visit_LastTime");
            if (scid == null || scid == "" || scid == "0") {
                newvisit = 1;
                scid = _c.hashID();
                _c.sVarV(oSCArray, "SessionCookie", scid);
            }
            _c.sVarV(oSCArray, _c.camp + "_Visit_LastTime", curr_time);

            var prev_vcount = _c.gVarV(oPCArray, _c.camp + "_Visit_Count");
            if (prev_vcount == null || prev_vcount == "" || isNaN(prev_vcount)) prev_vcount = 0; else prev_vcount = parseInt(prev_vcount, 10);
            if (newvisit == 1 || prev_vcount <= 0)
                _c.sVarV(oPCArray, _c.camp + "_Visit_Count", prev_vcount + 1);
            _c.sCamC(false, oPCArray);
            _c.sCamC(true, oSCArray);
            pc = newvisitor + "|" + prev_first_time + "|" + prev_last_time + "|" + prev_visitor_last_time + "|" + prev_vcount;
            sc = newvisit + "|" + prev_visit_last_time;
        }
        //拼接字符串
        _c.adF("cli", _c.cli);
        _c.adF("cam", _c.camp, 32);
        _c.adF(1, uid);
        _c.adF(2, scid);
        _c.adF(3, "W_S");
        _c.adF(5, "W_S_DATE");
        _c.adF(7, _c.gD(ev ? _c.baseURI : _c.URL), 64);
        if ((sMode ? sMode.toUpperCase() : "") == "E") {
            var u = _c.gU(ev ? _c.baseURI : _c.URL);
            if (params != "")
                u += "?wcl_e=" + params;
            _c.adF(8, u, 256);
            u = _c.gP(ev ? _c.baseURI : _c.URL);
            if (params != "") {
                if (u != "")
                    u += "&";
                u += "wcl_e=" + params;
            }
            _c.adF(9, u, 728);
        }
        else {
            _c.adF(8, _c.gU(ev ? _c.baseURI : _c.URL), 256);
            _c.adF(9, _c.gP(ev ? _c.baseURI : _c.URL), 728);
        }
        _c.adF("SourceType", GetUrlParm("SourceType", ev ? _c.baseURI : _c.URL));
        _c.adF("SourceCode", GetUrlParm("SourceCode", ev ? _c.baseURI : _c.URL));
        _c.adF("bd_latitude", GetUrlParm("bd_latitude", ev ? _c.baseURI : _c.URL));
        _c.adF("bd_longitude", GetUrlParm("bd_longitude", ev ? _c.baseURI : _c.URL));

        _c.adF(10, "W_S_IP");
        _c.adF(11, _c.gD(_c.from), 64);
        _c.adF(12, _c.gU(_c.from), 256);
        _c.adF(13, _c.gP(_c.from), 728);
        _c.adF(14, _c.camp, 32);
        _c.adF(15, "W_S");
        _c.adF(16, "W_S");
        _c.adF(17, "W_S");
        _c.adF(19, OS);
        _c.adF(21, br);
        _c.adF(23, filterExtraChars(screen.width + "x" + screen.height, "resolution"), 12); //分辨率
        _c.adF(24, navigator.cookieEnabled ? "1" : "0");
        _c.adF(26, _c.from, 1024); //新增
        if (navigator.cookieEnabled == false || _c.cookie3Enabled == 0)
            _c.adF(25, 0);
        else
            _c.adF(25, 1);
        if (!_c.title) {
            _c.title = _c.URL.replace(/\?.+/, "");
            _c.title = /\/$/.test(_c.title) ? _c.title : _c.title.replace(/^.+\//, "")
        }
        else if (_c.title.length > 64) {
            _c.title = _c.title.substr(0, 30) + "..." + _c.title.substr(_c.title.length - 30, 30);
        }
        _c.adF(6, _c.title, 64);
        var sAcc = _c.gC("Wangcl_Account");
        if (sAcc) _c.adF(4, sAcc, 128);

        var sBoxCode = _box_code == "" ? _c.gC("Wangcl_BoxCode") : _box_code;
        if (sBoxCode) _c.adF("boxcode", sBoxCode, 128);
        var sFenzhan = _fenzhan == "" ? _c.gC("Wangcl_Fenzhan") : _fenzhan;
        if (sFenzhan) _c.adF("fenzhan", sFenzhan, 128);

        var sBd_latitude = _bd_latitude == "" ? _c.gC("Wangcl_bd_latitude") : _bd_latitude;
        if (sBd_latitude) _c.adF("bd_latitude", sBd_latitude, 128);
        var sBd_longitude = _bd_longitude == "" ? _c.gC("Wangcl_bd_longitude") : _bd_longitude;
        if (sBd_longitude) _c.adF("bd_longitude", sBd_longitude, 128);

        if (sAttr) {
            var aE = sAttr.split("/");
            for (var j = 1; j < aE.length; j++) {
                if (j > 10) break;
                if (j < aE.length && aE[j] != "") { _c.adF(25 + j, aE[j], 32); }
            }
        }
        var trackURL = _c.trackURL2;
        var t = d.getTime() - (d.getTimezoneOffset() * 60 * 1000);
        if (_c.debug == 1) document.write(trackURL + "?" + _c.gTS() + "&pc=" + pc + "&sc=" + sc + "&t=" + t);

        _c.adF(27, _c.URL, 250);
        _c.adF(28, document.URL, 250);
        _c.track(trackURL + "?" + _c.gTS() + "&pc=" + pc + "&sc=" + sc + "&t=" + t);
    },

    //判断调用那段方法
    trackPageview: function (sMode, sPath, sTitle, sAttr, bCounter) {
        (_c_jsF.indexOf("2") > 0) ? this.trackPageview2(sMode, sPath, sTitle, sAttr, bCounter) : this.trackPageview1(sMode, sPath, sTitle, sAttr, bCounter);
    }
};

_wcl._getTracker = function (cli/*平台key*/, camp/*...*/, bsU/*track.wangcl.com*/, cnt, slt, adt) {
    //对_wcl构造函数添加属性cli + "_" + camp， 新建一个_wcl类型的对象， 并赋值给cli + "_" + camp，默认的cnt，slt，adt均为1
    if (bsU) _wcl[cli + "_" + camp] = new _wcl(cli, camp, bsU, cnt || "1", slt || "1", adt || "1");
    //遍历_wcl类的所有属性，在cli == undefined的情况下，找出以下划线居中的属性，并将该属性的属性值返回
    for (i in _wcl) if (cli == undefined && /\w+_\w+/.test(i)) return _wcl[i];
    //如果cli有定义，则将_wcl[cli + "_" + camp]返回，如果_wcl[cli + "_" + camp]没有值，则新建一个_wcl类的对象作为返回值
    return _wcl[cli + "_" + camp] || new _wcl();
};

function wcl_LogTrack(sPath, sTitle, sMode, sJS) {
    var aJS = sJS ? sJS.split("_") : [];
    var myTracker = _wcl._getTracker(aJS.length ? aJS[0] : undefined, aJS[1]);
    myTracker.trackPageview(sMode, sPath, sTitle);
};

if (typeof (_wclq) != "undefined" && _wclq != null && _wclq.length > 0) {
    myTracker = null;
    for (var i = 0; i < _wclq.length; i++) {
        if (_wclq[i][0] == '_getTracker') {
            if (_wclq[i].length > 3)
                myTracker = _wcl._getTracker(_wclq[i][1], _wclq[i][2], _wclq[i][3]);
            break;
        }
    }
    if (myTracker != null) {
        for (var i = 0; i < _wclq.length; i++) {
            if (_wclq[i][0] == 'debug')
                myTracker.debug = parseInt(_wclq[i][1], 10);
            else if (_wclq[i][0] == 'cookie3Enabled')
                myTracker.cookie3Enabled = parseInt(_wclq[i][1], 10);
            else if (_wclq[i][0] == 'pc_expired_months')
                myTracker.pc_expired_months = parseInt(_wclq[i][1], 10);
            else if (_wclq[i][0] == 'sc_expired_mins')
                myTracker.sc_expired_mins = parseInt(_wclq[i][1], 10);
            else if (_wclq[i][0] == 'sc_expired_mins')
                myTracker.sc_expired_mins = parseInt(_wclq[i][1], 10);
            else if (_wclq[i][0] == 'trackPageview')
                myTracker.trackPageview();
        }
        _wclq = null;
    }
};

//设置会员帐号Cookie
function wangcl_set_account(account) {
    document.cookie = "Wangcl_Account=" + escape(account) + ";path=/";
}
//设置boxcodeCookie
function wangcl_set_boxcode(account) {
    document.cookie = "Wangcl_BoxCode=" + escape(account) + ";path=/";
}
//设置分站Cookie
function wangcl_set_fenzhan(account) {
    document.cookie = "Wangcl_Fenzhan=" + escape(account) + ";path=/";
}

//设置百度纬度
function wangcl_set_bd_latitude(bd_latitude) {
    document.cookie = "Wangcl_bd_latitude=" + escape(bd_latitude) + ";path=/";
}

//设置百度经度
function wangcl_set_bd_longitude(bd_longitude) {
    document.cookie = "Wangcl_bd_longitude=" + escape(bd_longitude) + ";path=/";
}

function wangcl_pv(title, url)  // for flash tracking
{
    url = url ? url.replace(/ /g, "") : "";
    title = title ? title.replace(/ /g, "") : "";
    var tracker = _wcl._getTracker(_parm, "", "track.wangcl.com");
    if (url.substring(0, 1) != '/')
        url = '/' + url;
    tracker.trackNewpage(url, title);
}


function wangcl_trackBtn(buttonname, driveritem)  // for button click
{
    if (!buttonname && !driveritem)
        return;

    buttonname = buttonname ? buttonname.replace(/ /g, "") : "";
    driveritem = driveritem ? driveritem.replace(/ /g, "") : "";   // replace space

    var tracker = _wcl._getTracker(_parm, "", "track.wangcl.com");
    var new_path = "/" + buttonname + "/" + driveritem;
    tracker.trackPageview("E", new_path, buttonname, driveritem, "1");

    var cli = _parm;
    var cp = "";
    var li = 24;

    tracker.aFields = [];
    tracker.adF("cli", cli);
    tracker.adF("li", li);
    tracker.adF(1, "W_S_DATE");
    tracker.adF(2, cp);
    tracker.adF(4, "W_S_SC");
    tracker.adF(7, "W_S");
    tracker.adF(3, tracker.sUID);
    tracker.adF(5, buttonname, 128); //按钮名称
    tracker.adF(6, driveritem, 128); //按钮值
    tracker.adF("rnd", Math.random().toString());
    tracker.adF(8, tracker.gC("Wangcl_Account"));
    tracker.adF(9, "W_S_IP");
    tracker.adF(10, tracker.gD(tracker.baseURI), 64); //域名
    tracker.adF(11, tracker.URL, 128); //路径
    tracker.adF("SourceType", GetUrlParm("SourceType", tracker.URL));
    tracker.adF("SourceCode", GetUrlParm("SourceCode", tracker.URL));
    tracker.adF("bd_latitude", GetUrlParm("bd_latitude", tracker.URL));
    tracker.adF("bd_longitude", GetUrlParm("bd_longitude", tracker.URL));

    tracker.track(tracker.addsURL + "?" + tracker.gTS());
    return;
}

//获取url中data的参数  Url格式：...?codetype=1&code=email
function GetUrlParm(name, Url) {
    var u, g, StrBack = '';
    u = Url.split("?");
    if (u.length == 1) g = '';
    else g = u[1];
    if (g != '') {
        gg = g.split("&");
        for (j = 0; j < gg.length; j++) {
            if (gg[j].indexOf(name) > -1) {
                StrBack = gg[j].replace(name + "=", "");
                break;
            }
        }
    }
    return StrBack;
}

//过滤掉单双引号，左右箭头（移除掉左右箭头本身及其之后的内容），空格，左右括号，移除掉左右括号本身及其之后的内容
//先将%转换为空格，再将空格去除掉

/*
oriStr:原始字符串
paramType:所属类型

作用：首先去掉单引号之后的所有内容，然后去掉双引号之后的所有内容，然后去掉\>符号后的所有内容，然后去掉<符号后的所有内容，然后去掉所有)，然后移除%，然后移除空格
*/

function filterExtraChars(oriStr, paramType) {
    if (paramType != "url") {
        oriStr = oriStr.replace(/\s/g, "");
        oriStr = oriStr.replace(/%/g, "");
        oriStr = oriStr.split("\.")[0];
        oriStr = oriStr.split("_")[0];

    }

    if (paramType) {
        switch (paramType) {
            case "url":
                oriStr = filterFunSub1(oriStr);
                break;
            case "resolution":
                oriStr = filterFunSub1(oriStr);
                break;
            case "browser":
                oriStr = filterFunSub1(oriStr);
                break;
            case "system":
                oriStr = filterFunSub1(oriStr);
                break;
            default:
                break;
        }
    }
    return oriStr;
}

function filterFunSub1(str) {
    return str.split("'")[0].split("\"")[0].split(">")[0].split("<")[0].replace(new RegExp("\\(\|\\)", "g"), "");
}

//filterExtraChars('http://m.wangcl.com/weixin/optometry/index.aspx\><iframe onload=alert()>', "url");
//filterExtraChars('qq ))', "browser");
//filterExtraChars('IOS%208_4', "system");


var myTracker = _wcl._getTracker(_parm, "", "track.wangcl.com");
myTracker.trackPageview();
