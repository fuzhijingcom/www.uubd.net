//点击用户触发事件
var lastThisObj = null;
function setUserForm(thisObj){
    if(lastThisObj!=null){
        lastThisObj.removeClass('selected-tr');
    }
    $(thisObj).addClass('selected-tr');
    lastThisObj = $(thisObj);
}
