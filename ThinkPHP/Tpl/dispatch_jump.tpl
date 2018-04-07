<?php
    if(C('LAYOUT_ON')) {
        echo '{__NOLAYOUT__}';
    }
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>跳转提示</title>
<style>
html { height:100%;}
a{text-decoration:none;}
body { height:100%; text-align:center; background:transparent; font-size:12px;}
.center_div { display:inline-block; zoom:1; *display:inline; vertical-align:middle;  width:200px; padding:10px; }
.hiddenDiv { height:50%; overflow:hidden; display:inline-block; width:1px; overflow:hidden; margin-left:-1px; zoom:1; *display:inline; *margin-top:-1px; _margin-top:0; vertical-align:middle;}
.loading_block{background:url(/Public/image/load_bg.gif) left top no-repeat; width:506px; height:207px;}
.loading_onclick{
	margin-right: 23px;
    margin-top: -33px;
    text-align: right;
}
.loading_bar{
	height: 200px;
}
.jump_notice{
	font-size: 20px;
    font-weight: bold;
    height: 81px;
    line-height: 81px;
    width: 485px;
}
</style>
</head>
<body>
<div class="center_div loading_block">
	<div class="loading_bar">
		<div class="jump_notice"><?php if(isset($message)){ echo($message);}else{ echo($error); }?></div>
		<div><img width="220" height="19" src="/Public/image/loading_page.gif"></div>
		<div><p>正在前往指定的页面，请稍后<b id="wait" style="display: none"><?php echo($waitSecond); ?></b>……</p></div>
	</div>
	<div class="loading_onclick">如果浏览器没有自动跳转，请<a id="href" href="<?php echo($jumpUrl); ?>"> 点击这里 </a></div>
</div>
<div class="hiddenDiv"></div>
<script type="text/javascript">
(function(){
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function(){
	var time = --wait.innerHTML;
	if(time <= 0) {
		location.href = href;
		clearInterval(interval);
	};
}, 1000);
})();
</script>
</body>
</html>