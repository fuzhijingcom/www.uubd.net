//侧栏滑入
$(".button-collapse").sideNav();
$("nav ul li .collapsible-header").click(function(){
	$(this).parent().addClass('active').siblings().removeClass('active')
})
//$("nav").on('mouseleave',function(){
//	$("nav ul li").removeClass('active')
//	$(".dropdown-content").hide()
//})
//登录

//banner
if ($(window).width()<800 & $(window).width() >440) {
	$(".carousel.carousel-slider .carousel-item .carousel-item-intro-txt").find('.txt').addClass('left');
	$(".carousel.carousel-slider .carousel-item-intro-txt").find('.txt-btn').addClass('right');
	$('.content4 .card .card-image').css('height','206px');
	$('.content4 .card').css('height','430px');
} else if($(window).width()>800){
	$(".carousel.carousel-slider .carousel-item-intro-txt").find('.txt').removeClass('left');
	$(".carousel.carousel-slider .carousel-item-intro-txt").find('.txt-btn').removeClass('right');
	$('.content4 .card').css('height','560px');
}else{
	$('.content1 .card').css('height','200px');
	$('.content4 .card').css('height','auto');
	$('.content4 #dropdown2-3 .card').css('height','450px');
}
$('.carousel.carousel-slider').carousel({
	full_width: true,
	indicators:true,
});
$('.carousel.carousel-slider .banner-arrows .left').on('click',function(){
	$('.carousel').carousel('prev');
})
$('.carousel.carousel-slider .banner-arrows .right').on('click',function(){
	$('.carousel').carousel('next');
})
$('.carousel.carousel-slider .carousel-fixed-item .btn').on('click',function(){
	$(this).addClass('active').siblings().removeClass('active');
})
//footer
//footer collapsible
$('.collapsible').collapsible();
if ($(window).width()<800) {
	$("footer .footer-b ul").addClass('collapsible');
	$("footer .footer-b ul p").addClass('collapsible-header');
	$("footer .footer-b ul div").addClass('collapsible-body');
} else{
	$("footer .footer-b ul").removeClass('collapsible');
	$("footer .footer-b ul p").removeClass('collapsible-header');
	$("footer .footer-b ul div").removeClass('collapsible-body');
}
$("footer .footer-b ul li .collapsible-header").on('click',function(){
	$("footer .footer-b ul li .collapsible-header").find('span').css({
			'display':'block',
			'transform':'rotate(0)'
	})
	$(this).find('span').css({
		'display':'block',
		'transform':'rotate(90deg)'
	})
})
