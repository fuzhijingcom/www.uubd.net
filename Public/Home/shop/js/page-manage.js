/**
 * 页面管理的js
 */

//获取页面信息
function getPageInfo() {

    var url = URL + '/getPageInfo';

    $.ajax({
        method : "GET",
        url : url,
        success : function (res) {
            res = JSON.parse(res);
            drawPageInfo(res);

        }
    });

}

//渲染页面信息
function drawPageInfo(data) {
    $('#pageTable tbody').html('');

    if(data) {
        $.each(data,function (key,val) {
            var tr = document.createElement('tr');

            tr.innerHTML = '<td class="width-40 page-title">' + val['title'] + '</td>'+
                            '<td class="width-30 page-name">' + val['name'] + '</td>'+
                            '<td class="width-30 text-center page-btns" title = "' + val['id'] + '">'+
                                '<a class="btn btn-success edit-btn" href="' + URL + '/editPage?id=' + val['id'] + '" target="_blank">编辑</a>'+
                                '<button class="btn btn-info copy-btn">复制</button>'+
                                '<button class="btn btn-danger del-btn">删除</button>'+
                            '</td>';

            $('#pageTable tbody').append(tr);

            //为按键绑定事件
            $(tr).find('.copy-btn').bind('click',createNewPage('copy'));
            $(tr).find('.del-btn').bind('click',deletePage);
        });
    }

}

//创建新页面或复制页面
function createNewPage(func) {
    return function _createNewPage(e) {
        var event = window.event || e;
        var target = event.target || event.srcElement;

        var ajaxData = {
            'func' : func,
            'id' : ($(target).parent().attr('title') || 0)
        };

        $.ajax({
            method : 'POST',
            url : URL + '/CreateOrCopy',
            data : ajaxData,
            dataType : 'json',
            success : function (res) {
                // console.log(res);
                if(res['sign']) {
                    //重新渲染
                    getPageInfo();
                }else {
                    alert(res['msg']);
                }
            }
        });
    }
}

//删除页面
function deletePage(e) {
    var event = window.event || e;
    var target = event.target || event.srcElement;

    if(confirm('确定要删除此页面吗？')) {
        $.ajax({
            method : 'POST',
            url : URL + '/deletePage',
            data : {'id':$(target).parent().attr('title')},
            dataType : 'json',
            success : function (res) {
                if(res) {
                    getPageInfo();
                }else {
                    alert('删除失败！不知为什么！');
                }
            }
        });
    }
}