/**
 * Created by victor on 15/11/10.
 */
//页面元素加载完成后，触发事件
$(document).ready(function() {
	$("#leftNav li").click(function(){
		$("#leftNav li").removeClass('active');
		$(this).addClass('active');
	});

    $("#leftNav li:first").click();
    getNewOptometryNum();
});

//点击后切换功能模块
function selectModule(url){
    var iframeOne = document.getElementById('main-content');
    iframeOne.src = url;
}

function getNewOptometryNum(){
	var url=MODULE+"/Optometry/getOptometryCount";
	$.get(url, function (data) {
        if(data.count>0){
        	$(".red-point").show();
            $(".red-point").html(data.count);
        }else{
        	$(".red-point").hide();
        }
    });
}