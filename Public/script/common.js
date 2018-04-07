

//作用：cookie读写函数 日期：2013-12-04
function deal_cookie(name, value, options) {
	if (typeof value != "undefined") {
	    options = options || {};
	    if (value === null) {
	        value = "";
	        options.expires = -1
	    }
	    var expires = "";
	    if (options.expires && (typeof options.expires == "number" || options.expires.toUTCString)) {
	        var date;
	        if (typeof options.expires == "number") {
	            date = new Date();
	            date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000))
	        } else {
	            date = options.expires
	        }
	        expires = "; expires=" + date.toUTCString()
	    }
	    var path = options.path ? "; path=" + (options.path) : "";
	    var domain = options.domain ? "; domain=" + (options.domain) : "";
	    var secure = options.secure ? "; secure" : "";
	    document.cookie = [name, "=", encodeURIComponent(value), expires, path, domain, secure].join("");
	} else {
	    var cookieValue = "";
	    if (document.cookie && document.cookie != "") {
	        var cookies = document.cookie.split(";");
	        for (var i = 0; i < cookies.length; i++) {
	            var cookie = cookies[i].replace(/^\s+|\s+$/g,'');
	            if (cookie.substring(0, name.length + 1) == (name + "=")) {
	                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
	                break
	            }
	        }
	    }
	    return cookieValue
	}
}
//控制文本框只输入数字和小数点
function clearNoNum(obj) {
	//先把非数字的都替换掉，除了数字和.    
    obj.value = obj.value.replace(/[^\d.]/g,"");    
    //保证只有出现一个.而没有多个.    
    obj.value = obj.value.replace(/\.{2,}/g,".");    
    //必须保证第一个为数字而不是.    
    obj.value = obj.value.replace(/^\./g,"");    
    //保证.只出现一次，而不能出现两次以上    
    obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");    
    //只能输入两个小数  
    obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3');
}
/*弹出层*/
/*
	参数解释：
	title	标题
	url		请求的url
	id		需要操作的数据id
	w		弹出层宽度（缺省调默认值）
	h		弹出层高度（缺省调默认值）
*/
function layer_show(title,url,w,h){
	if (title == null || title == '') {
		title=false;
	};
	if (url == null || url == '') {
		url="404.html";
	};
	if (w == null || w == '') {
		w=800;
	};
	if (h == null || h == '') {
		h=($(window).height() - 50);
	};
	layer.open({
		type: 2,
		area: [w+'px', h +'px'],
		fix: false, //不固定
		maxmin: false,
		shadeClose: true,
		shade: 0.8,
		title: title,
		content: url
	});
}
/**
 * 异步加速数据
 * @param callback
 * @param url
 * @param method
 * @private
 *ajax_load(function(response){
 *               //处理AJAX返回来的数据，这里根据具体的业务逻辑来处理想的数据
 *               $(response.list).each(function(i,data) {
 *                   alert(data.department_name);
 *               });
 *           },"{$Think.const.SITE_URL}/Api/department_list/19","({orgid:19})","get","html");
 *
 *
 *
 */
function ajax_request(callback,url){
	var _data = arguments[2] ? eval(arguments[2]) : '';
	var _method = arguments[3] ? arguments[3] : 'get';
	var _datatype = arguments[4] ? arguments[4] : 'json';
	var _loading_msg = arguments[5] ? arguments[5] : '数据加载中...';
	$.ajax({
		type:_method,
		dataType:_datatype,
		url: url,
		data:_data,
		async : true,
		beforeSend:function(){
			layer.load(_loading_msg);
		},
		error: function(request) {
			layer.alert("未知错误",{
               title: '提示框',				
			   icon:0,		
			});
		},
		cache: false,
		success: function(response){
			callback(response);//将返回结果当作参数返回
		},
		complete:function(){
			layer.close(layer.index);
		}
	});
}
/**
 * 操作确认处理函数
 * @param msg
 * @param url
 * @param isajax
 */
function confirm(msg,url){
    var ajax = arguments[2];
    if("undefined" ==  layer){
        if(window.confirm(msg)){
            if(parseInt(ajax)==1){

            }else{
                window.location.href=url;
            }

        }
    }else{
        layer.confirm(msg, function(){
            //删除成功
            if(parseInt(ajax)==1){

            }else{
                window.location.href=url;
            }
        });
    }
}

/**
 * 异步提交表单数据
 * @param obj
 * @param url
 */
function ajaxForm(callback,obj,url){
	var _loading_msg = arguments[3] ? arguments[3] : '数据提交中...';
	$.ajax({
		cache: true,
		type: "POST",
		url:url,
		data:obj.serialize(),// 你的formid
		async: false,
		beforeSend:function(){
			//layer.load(_loading_msg);
		},
		error: function(request) {
			layer.alert("未知错误",{
               title: '提示框',				
			   icon:0,		
			});
		},
		success: function(response){
			callback(response);//将返回结果当作参数返回
		},
		complete:function(){
			//layer.close(layer.index);
		}
	});
}
$(function(){
	//表格行，鼠标放上去变色
	$(".tr:odd").css("background", "#FFFCEA");
	$(".tr:odd").each(function(){
		$(this).hover(function(){
			$(this).css("background-color", "#FFE1FF");
		}, function(){
			$(this).css("background-color", "#FFFCEA");
		});
	});
	$(".tr:even").each(function(){
		$(this).hover(function(){
			$(this).css("background-color", "#FFE1FF");
		}, function(){
			$(this).css("background-color", "#fff");
		});
	}); 
 
});