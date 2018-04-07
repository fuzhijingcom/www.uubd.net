/**
 * Created by Administrator on 2015/9/4.
 */
$(function(){
    $(".sideMenu").slide({
        titCell:"h3",
        targetCell:"ul",
        defaultIndex:0,
        effect:'slideDown',
        delayTime:'500' ,
        trigger:'click',
        triggerTime:'150',
        defaultPlay:true,
        returnDefault:false,
        easing:'easeInQuint'
    });
    /*$(window).resize(function() {
        scrollWW();
    });*/
    $(".sideMenu ul li").each(function(){
        $(this).mouseover(function(){
            $(this).css("background-color","grey");
        });
        $(this).mouseout(function(){
            $(this).css("background-color","");
        });
    });
    $(".sideMenu ul li").each(function(){
        $(this).click(function(){
            $(this).addClass("on");
            $(this).nextAll("li").removeClass("on");
            $(this).prevAll("li").removeClass("on");
            $(this).parent("ul").nextAll("ul").find("li").removeClass("on");
            $(this).parent("ul").prevAll("ul").find("li").removeClass("on");
        })

    });
});
