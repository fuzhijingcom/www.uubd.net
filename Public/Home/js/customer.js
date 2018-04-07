$(document).ready(function() {
    setIframeHeight();
});

function setIframeHeight(){
    var orderIframe = document.getElementById('orderTable');
    var part1 = document.getElementsByClassName('form-box')[0].offsetHeight;
    var part2 = document.getElementsByClassName('table-head')[0].offsetHeight;
    var wholeHeight = document.body.offsetHeight;
    orderIframe.height = wholeHeight-(part1+part2);
}

function searchCustomers(){
    var customerurl=MODULE+"/Customer/customertable?type=search";
    var tags=$('#tags').val();
    if(tags){
        customerurl+="&tags="+tags;
    }
    var is_follow=$('#is_follow').val();
    if(is_follow){
        customerurl+="&is_follow="+is_follow;
    }
    var keyword=$('#keyword').val();
    if(keyword){
        customerurl+="&keyword="+keyword;
    }
    $("#orderTable").attr('src',customerurl);
}

function toOptometry(user_id,name){
    parent.parent.$("#main-content").attr("src",MODULE+"/Optometry/optometry?user_id="+user_id+"&name="+name);
}